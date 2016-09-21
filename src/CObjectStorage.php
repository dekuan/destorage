<?php

namespace dekuan\destorage;

use dekuan\delib\CLib;


/**
 *	Class of CObjectStorage
 */
class CObjectStorage
{
	protected static $g_cStaticInstance;

	//	...
	const CONST_OPERATE_DELETE	= '--OPERATE-DELETE--';


	public function __construct()
	{
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
	//	merge the new data to an existed data and encode them into a json encoded string
	//
	public function Encode( $arrExistedData, $arrNewData = null )
	{
		//
		//	arrExistedData	- [in] array,	exists data
		//	arrNewData	- [in] array,	data to be stored
		//	RETURN		- string of encoded array/object
		//

		//	...
		$sRet	= '';

		try
		{
			//	...
			if ( ! CLib::IsArrayWithKeys( $arrExistedData ) )
			{
				$arrExistedData = [];
			}
			if ( ! CLib::IsArrayWithKeys( $arrNewData ) )
			{
				$arrNewData = [];
			}

			//
			//	set all values to exists
			//
			if ( CLib::IsArrayWithKeys( $arrNewData ) )
			{
				foreach ( $arrNewData as $vKey => $vValue )
				{
					//	...
					$bContinue = false;

					//	...
					if ( is_string( $vKey ) )
					{
						$vKey = trim( $vKey );
						if ( strlen( $vKey ) )
						{
							$bContinue = true;
						}
					}
					else if ( is_numeric( $vKey ) )
					{
						$vKey = intval( trim( $vKey ) );
						if ( $vKey >= 0 )
						{
							$bContinue = true;
						}
					}

					if ( $bContinue )
					{
						if ( is_string( $vValue ) &&
							strlen( $vValue ) > 0 &&
							0 == strcmp( $vValue, self::CONST_OPERATE_DELETE ) )
						{
							//
							//	remove this item
							//
							$arrExistedData[ $vKey ] = null;
							unset( $arrExistedData[ $vKey ] );
						}
						else
						{
							//
							//	set value by key
							//
							$arrExistedData[ $vKey ] = $vValue;
						}
					}
				}
			}

			//	...
			if ( is_array( $arrExistedData ) )
			{
				$sString = @ json_encode( $arrExistedData );
				if ( is_string( $sString ) )
				{
					$sRet = $sString;
				}
			}
		}
		catch ( \Exception $e )
		{
			//	exception => $e->getMessage(), $e->getCode();
			//	throw $e;
		}

		return $sRet;
	}

	//
	//	@ Public
	//	decode an json encoded string to array
	//
	public function Decode( $sEncodedString, $arrDefault = null )
	{
		//
		//	sEncodedString	- [in] string,	string to decode
		//	arrDefault	- [in] virtual,	default value
		//	RETURN		- decoded array or null
		//
		if ( ! is_string( $sEncodedString ) || 0 == strlen( $sEncodedString ) )
		{
			return $arrDefault;
		}

		//	...
		$arrRet = $arrDefault;

		//	...
		try
		{
			$arrInfo = @ json_decode( $sEncodedString, true );
			if ( is_array( $arrInfo ) )
			{
				$arrRet = $arrInfo;
			}
		}
		catch ( \Exception $e )
		{
			//	exception => $e->getMessage(), $e->getCode();
			//	throw $e;
		}

		return $arrRet;
	}



	//
	//	@ Public
	//	merge a new data to an existed data by calling callable function pfnQuery
	// 	and encode them into a json encoded string that will be saved by calling callable function pfnSave
	//
	public function EncodeWithDriver( $arrNewData, callable $pfnQuery, callable $pfnSave )
	{
		//
		//	arrNewData	- [in] array,		data to be stored
		//	pfnQuery	- [in] function,	function address that we obtain encoded string from
		//						function(){}
		//	pfnSave		- [in] function,	function address that we save encoded string to
		//						bool function( String sEncodedString ){ ... }
		//	RETURN		- true / false
		//
		if ( ! is_callable( $pfnQuery ) || ! is_callable( $pfnSave ) )
		{
			return false;
		}

		//	...
		$bRet	= false;

		try
		{
			//	...
			$sEncodedString	= $pfnQuery();
			$arrDefault	= [];
			$arrExistedData	= $this->Decode( $sEncodedString, $arrDefault );

			//	...
			$sNewString	= $this->Encode( $arrExistedData, ( is_array( $arrNewData ) ? $arrNewData : [] ) );
			if ( is_string( $sNewString ) )
			{
				$bRet = $pfnSave( $sNewString );
			}
		}
		catch ( \Exception $e )
		{
			//	exception => $e->getMessage(), $e->getCode();
			//	throw $e;
		}

		return $bRet;
	}


	//
	//	@ Public
	//	decode a json encoded string by calling callable function pfnQuery to array
	//
	public function DecodeWithDriver( callable $pfnQuery, callable $pfnDump, $arrDefault = null )
	{
		//
		//	pfnQuery	- [in] function,	function address that we obtain encoded string from
		//						function(){}
		//	pfnDump		- [in] function,	bool function( arrData ){ receive decoded array or null }
		//	arrDefault	- [in] virtual,		default value
		//	RETURN		- true / false
		//
		if ( ! is_callable( $pfnQuery ) )
		{
			return false;
		}
		if ( ! is_callable( $pfnDump ) )
		{
			return false;
		}

		//	...
		$bRet		= false;
		$arrDumpData	= $arrDefault;

		//	...
		try
		{
			$arrDumpData = $this->Decode( $pfnQuery(), $arrDefault );
			if ( is_array( $arrDumpData ) )
			{
				//	...
				$bRet = $pfnDump( $arrDumpData );
			}
			else
			{
				$pfnDump( null );
			}
		}
		catch ( \Exception $e )
		{
			//	exception => $e->getMessage(), $e->getCode();
			//	throw $e;
		}

		return $bRet;
	}
}