<?php

namespace dekuan\destorage;


/**
 *	Class of CQueueStorage
 */
class CQueueStorage
{
	protected static $g_cStaticInstance;

	const CONST_DEFAULT_DEPTH	= 0;

	protected $m_nDepth;
	protected $m_oObjStorage;


	public function __construct( $nDepth = self::CONST_DEFAULT_DEPTH )
	{
		//	...
		$this->m_oObjStorage	= new CObjectStorage();

		//	...
		$this->SetDepth( $nDepth );
	}
	public function __destruct()
	{
	}
	static function GetInstance()
	{
		if ( is_null( self::$g_cStaticInstance ) || ! isset( self::$g_cStaticInstance ) )
		{
			self::$g_cStaticInstance = new self();
		}
		return self::$g_cStaticInstance;
	}


	//
	//	@ Public
	//	push an new item to tail of queue
	//
	public function SetDepth( $nDepth )
	{
		if ( is_numeric( $nDepth ) && $nDepth >= 0 )
		{
			$this->m_nDepth = intval( $nDepth );
		}
		else
		{
			$this->m_nDepth = self::CONST_DEFAULT_DEPTH;
		}
	}

	//
	//	@ Public
	//	push an new item to tail of queue
	//
	public function Push( $vNewItem, callable $pfnQuery, callable $pfnSave )
	{
		//
		//	vNewItem	- [in] any,	array, string, null, or others
		//	pfnQuery	- [in] function	function(){}
		//	pfnSave		- [in] function	function( $sEncodedString ){}
		//	RETURN		- bool		true / false
		//
		if ( ! is_callable( $pfnQuery ) || ! is_callable( $pfnSave ) )
		{
			return false;
		}

		//	...
		$bRet	= false;

		//
		//	put the new item at the end of the queue
		//
		$nMaxKey = $this->GetKeyWithMaxValue( $pfnQuery );
		if ( is_numeric( $nMaxKey ) )
		{
			$nMaxKey = intval( $nMaxKey );
			if ( $nMaxKey >= 0 )
			{
				$nMaxKey ++;
				$arrOpt	= [ $nMaxKey => $vNewItem ];
				$bRet	= $this->m_oObjStorage->EncodeWithDriver( $arrOpt, $pfnQuery, $pfnSave );
			}
		}

		//
		//	Pop if necessarily
		//
		if ( $this->m_nDepth > 0 )
		{
			if ( $this->GetCount( $pfnQuery ) > $this->m_nDepth )
			{
				$this->Pop( $pfnQuery, $pfnSave );
			}
		}

		return $bRet;
	}

	//
	//	@ Public
	//	remove the first item from queue and return it
	//
	public function Pop( callable $pfnQuery, callable $pfnSave )
	{
		//
		//	pfnQuery	- [in] function	function(){}
		//	pfnSave		- [in] function	function( $sEncodedString ){}
		//	RETURN		- the first item or null if not exists
		//
		if ( ! is_callable( $pfnQuery ) || ! is_callable( $pfnSave ) )
		{
			return null;
		}

		//	...
		$vRet	= null;

		//	...
		$nCount	= $this->GetCount( $pfnQuery );
		if ( $nCount > 0 )
		{
			//
			//	get the first element as pre-return value
			//
			$vFstKey	= $this->GetFirstKey( $pfnQuery );
			$arrFstItem	= $this->GetFirstItem( $pfnQuery );

			//
			//	remove the first element
			//
			if ( is_string( $vFstKey ) || is_numeric( $vFstKey ) )
			{
				$arrOpt	= [ $vFstKey => CObjectStorage::CONST_OPERATE_DELETE ];
				if ( $this->m_oObjStorage->EncodeWithDriver( $arrOpt, $pfnQuery, $pfnSave ) )
				{
					$vRet = $arrFstItem;
				}
			}
		}

		return $vRet;
	}

	//
	//	@ Public
	//	get the last item from queue
	//
	public function GetCount( callable $pfnQuery )
	{
		if ( ! is_callable( $pfnQuery ) )
		{
			return 0;
		}

		//	...
		$nRet	= 0;
		$arrAll	= $this->GetAllItems( $pfnQuery );
		if ( is_array( $arrAll ) )
		{
			$nRet = count( $arrAll );
		}

		return $nRet;
	}



	//
	//	@ Public
	//	get the key with the maximum value
	//
	public function GetKeyWithMaxValue( callable $pfnQuery )
	{
		if ( ! is_callable( $pfnQuery ) )
		{
			return 0;
		}

		//	...
		$nRet	= 0;
		$arrAll	= $this->GetAllItems( $pfnQuery );

		if ( is_array( $arrAll ) && count( $arrAll ) > 0 )
		{
			$arrKeys = array_keys( $arrAll );
			if ( is_array( $arrKeys ) && count( $arrKeys ) > 0 )
			{
				$nRet = max( $arrKeys );
			}
		}

		return $nRet;
	}



	//
	//	@ Public
	//	get the first item from queue
	//
	public function GetFirstKey( callable $pfnQuery )
	{
		if ( ! is_callable( $pfnQuery ) )
		{
			return null;
		}

		//	...
		$vRet	= null;
		$arrAll	= $this->GetAllItems( $pfnQuery );

		if ( is_array( $arrAll ) && count( $arrAll ) > 0 )
		{
			reset( $arrAll );
			$vRet = key( $arrAll );
		}

		return $vRet;
	}
	public function GetFirstItem( callable $pfnQuery )
	{
		if ( ! is_callable( $pfnQuery ) )
		{
			return null;
		}

		//	...
		$vRet	= null;
		$arrAll	= $this->GetAllItems( $pfnQuery );

		if ( is_array( $arrAll ) && count( $arrAll ) > 0 )
		{
			reset( $arrAll );
			$vRet = current( $arrAll );
		}

		return $vRet;
	}


	//
	//	@ Public
	//	get the last item from queue
	//
	public function GetLastKey( callable $pfnQuery )
	{
		if ( ! is_callable( $pfnQuery ) )
		{
			return null;
		}

		//	...
		$vRet	= null;
		$arrAll	= $this->GetAllItems( $pfnQuery );

		if ( is_array( $arrAll ) && count( $arrAll ) > 0 )
		{
			end( $arrAll );
			$vRet = key( $arrAll );
		}

		return $vRet;
	}
	public function GetLastItem( callable $pfnQuery )
	{
		if ( ! is_callable( $pfnQuery ) )
		{
			return null;
		}

		//	...
		$vRet	= null;
		$arrAll	= $this->GetAllItems( $pfnQuery );

		if ( is_array( $arrAll ) && count( $arrAll ) > 0 )
		{
			$vRet = end( $arrAll );
		}

		return $vRet;
	}

	//
	//	@ Public
	//	get all of the item from queue
	//
	public function GetAllItems( callable $pfnQuery )
	{
		if ( ! is_callable( $pfnQuery ) )
		{
			return null;
		}

		//	...
		$vRet	= null;
		$bCall	= $this->m_oObjStorage->DecodeWithDriver
		(
			$pfnQuery,
			function( $arrData ) use ( & $vRet )
			{
				$vRet = $arrData;

				//	...
				return true;
			},
			null
		);

		return $vRet;
	}
}