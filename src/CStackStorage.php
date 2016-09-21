<?php

namespace dekuan\destorage;


/**
 *	Class of CStackStorage
 */
class CStackStorage extends CQueueStorage
{
	protected static $g_cStaticInstance;

	public function __construct( $nDepth = self::CONST_DEFAULT_DEPTH )
	{
		return parent::__construct( $nDepth );
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
	//	remove the last item from queue and return it
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
			$vLstKey	= $this->GetLastKey( $pfnQuery );
			$arrLstItem	= $this->GetLastItem( $pfnQuery );

			//
			//	remove the first element
			//
			if ( is_string( $vLstKey ) || is_numeric( $vLstKey ) )
			{
				$arrOpt	= [ $vLstKey => CObjectStorage::CONST_OPERATE_DELETE ];
				if ( $this->m_oObjStorage->EncodeWithDriver( $arrOpt, $pfnQuery, $pfnSave ) )
				{
					$vRet = $arrLstItem;
				}
			}
		}

		return $vRet;
	}
}