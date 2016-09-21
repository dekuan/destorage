<?php

@ ini_set( 'date.timezone', 'Etc/GMT＋0' );
@ date_default_timezone_set( 'Etc/GMT＋0' );

@ ini_set( 'display_errors',	'on' );
@ ini_set( 'max_execution_time',	'60' );
@ ini_set( 'max_input_time',	'0' );
@ ini_set( 'memory_limit',	'512M' );

//	mb 环境定义
mb_internal_encoding( "UTF-8" );

//	Turn on output buffering
ob_start();


require_once( dirname( __DIR__ ) . "/vendor/autoload.php" );
require_once( dirname( __DIR__ ) . "/vendor/dekuan/delib/src/CLib.php" );
require_once( dirname( __DIR__ ) . "/src/CObjectStorage.php" );


use dekuan\destorage\CObjectStorage;



class CTestForObjectStorageWithDriver extends PHPUnit_Framework_TestCase
{
	var $m_arrTestData;

	public function __construct()
	{
		parent::__construct();

		$this->m_arrTestData =
			[
				[
					true,
					[ 'key1' => 'value1' ],
					[ 'key2' => 'value2' ],
					'{"key1":"value1","key2":"value2"}',
					[ 'key1' => 'value1', 'key2' => 'value2' ]
				],
				[
					true,
					[ 'key1' => 'value1', 'key2' => 'value2' ],
					[ 'key2' => CObjectStorage::CONST_OPERATE_DELETE ],
					'{"key1":"value1"}',
					[ 'key1' => 'value1' ]
				],
				[
					true,
					[],
					[ 'key2' => 'value2' ],
					'{"key2":"value2"}',
					[ 'key2' => 'value2' ]
				],
				[
					true,
					[],
					[ 'key2' => CObjectStorage::CONST_OPERATE_DELETE ],
					'[]',
					[]
				],
				[
					true,
					null,
					[ 'key2' => CObjectStorage::CONST_OPERATE_DELETE ],
					'[]',
					[]
				],
				[
					true,
					null,
					[],
					'[]',
					[]
				],
				[
					true,
					null,
					[],
					'[]',
					[]
				],
				[
					true,
					null,
					null,
					'[]',
					[]
				],
			];
	}


	public function testEncodeWithDriver()
	{
		$oOS	= CObjectStorage::GetInstance();
		global $sEncodedStrT;

		foreach ( $this->m_arrTestData as $nIndex => $arrItem )
		{
			$bGoal			= $arrItem[ 0 ];
			$arrExistsV		= $arrItem[ 1 ];
			$arrNewV		= $arrItem[ 2 ];
			$sExpectedResult	= $arrItem[ 3 ];
			$sEncodedStrT		= '';

			//	...
			$bCall = $oOS->EncodeWithDriver
			(
				$arrNewV,
				function() use ( $arrExistsV, $oOS )
				{
					return $oOS->Encode( $arrExistsV );
				},
				function( $sEncodedString ) use ( $sEncodedStrT )
				{
					global $sEncodedStrT;

					$sEncodedStrT = $sEncodedString;
					return true;
				}
			);

			$this->_OutputResultEncode( $nIndex, $bCall, $bGoal, $arrExistsV, $arrNewV, $sExpectedResult, $sEncodedStrT );
		}
	}

	public function testDecodeWithDriver()
	{
		$oOS	= CObjectStorage::GetInstance();
		global $arrDumpDataT;

		foreach ( $this->m_arrTestData as $nIndex => $arrItem )
		{
			$bGoal		= $arrItem[ 0 ];
	//		$arrExistsV	= $arrItem[ 1 ];
	//		$arrNewV	= $arrItem[ 2 ];
			$sEncodedStr	= $arrItem[ 3 ];
			$arrExpected	= $arrItem[ 4 ];
			$arrDumpDataT	= null;

			//	...
			$bCall = $oOS->DecodeWithDriver
			(
				function() use ( $sEncodedStr )
				{
					return $sEncodedStr;
				},
				function( $arrDumpData ) use ( $arrDumpDataT )
				{
					global $arrDumpDataT;

					$arrDumpDataT = $arrDumpData;
					return true;
				},
				null
			);

			$this->_OutputResultDecode( $nIndex, $bCall, $bGoal, $sEncodedStr, $arrExpected, $arrDumpDataT );
		}
	}


	protected function _OutputResultDecode( $nIndex, $bCall, $bGoal, $sEncodedStr, $arrExpected, $arrDecoded )
	{
		$bResult	= false;
		$arrDiff	= null;
		if ( is_array( $arrExpected ) && is_array( $arrDecoded ) )
		{
			$arrDiff = array_diff( $arrExpected, $arrDecoded );
			if ( is_array( $arrDiff ) && 0 == count( $arrDiff ) )
			{
				$bResult = true;
			}
		}

		$bSuccess	= ( $bCall && ( $bGoal == $bResult ) );

		echo "[$nIndex] " . __CLASS__ . "::DecodeWithDriver\r\n";
		echo "bCall\t\t: " . ( $bCall ? "TRUE" : "FALSE" ) . "\r\n";
		echo "Encode\t\t: $sEncodedStr\r\n";
		echo "arrDecoded\t: ";
		if ( is_array( $arrDecoded ) )
		{
			print_r( $arrDecoded );
		}
		else
		{
			echo "\r\n";
		}
		echo "arrDiff\t: ";
		if ( is_array( $arrDiff ) )
		{
			print_r( $arrDiff );
		}
		else
		{
			echo "\r\n";
		}


		if ( $bSuccess )
		{
			echo "--------------------------------------------------------------------------------\r\n";
		}
		else
		{
			echo "################################################################################\r\n";
		}

		echo "\r\n";
		echo "\r\n";

		$this->assertTrue( $bSuccess );
	}

	protected function _OutputResultEncode( $nIndex, $bCall, $bGoal, $arrExistsV, $arrNewV, $sExpectedResult, $sEncodedStr )
	{
		//
		//	...
		//
		$bResult	= ( 0 == strcmp( $sEncodedStr, $sExpectedResult ) );
		$bSuccess	= ( $bCall && ( $bGoal == $bResult ) );

		echo "[$nIndex] " . __CLASS__ . "::EncodeWithDriver\r\n";
		echo "bCall\t\t: " . ( $bCall ? "TRUE" : "FALSE" ) . "\r\n";
		echo "Exists\t\t: ";
		if ( is_array( $arrExistsV ) )
		{
			print_r(  $arrExistsV );
		}
		else
		{
			echo "\r\n";
		}

		echo "New\t\t: ";
		if ( is_array( $arrNewV ) )
		{
			print_r( $arrNewV );
		}
		else
		{
			echo "\r\n";
		}

		echo "Expected\t: $sExpectedResult\r\n";
		echo "Result\t\t: $sEncodedStr\r\n";
		echo "Success\t\t: " . ( $bSuccess ? "OK" : "ERROR" );
		echo "\r\n";
		if ( $bSuccess )
		{
			echo "--------------------------------------------------------------------------------\r\n";
		}
		else
		{
			echo "################################################################################\r\n";
		}

		echo "\r\n";
		echo "\r\n";

		$this->assertTrue( $bSuccess );
	}
}