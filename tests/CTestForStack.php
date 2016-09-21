<?php

@ ini_set( 'date.timezone', 'UTC' );
@ date_default_timezone_set( 'UTC' );

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
require_once( dirname( __DIR__ ) . "/src/CQueueStorage.php" );
require_once( dirname( __DIR__ ) . "/src/CStackStorage.php" );


use dekuan\destorage\CStackStorage;



class CTestForStack extends PHPUnit_Framework_TestCase
{
	var $m_sDataFullFilename;

	public function __construct( $name = null, array $data = array(), $dataName = '' )
	{
		parent::__construct( $name, $data, $dataName );

		//	...
		$this->m_sDataFullFilename = sprintf( "%s/data/stack.json", __DIR__ );
	}


	public function testStack()
	{
		$this->_testStack();
	}



	////////////////////////////////////////////////////////////////////////////////
	//	Private
	//


	private function _testStack()
	{
		$cStack	= CStackStorage::GetInstance();

		//	...
		$cStack->SetDepth( 10 );
		$cStack->Push
		(
			sprintf( "---%d---", time() ),
			function()
			{
				return $this->_LoadStringData();
			},
			function( $sString )
			{
				return $this->_SaveStringData( $sString );
			}
		);

		$arrAll	= $cStack->GetAllItems
		(
			function()
			{
				return $this->_LoadStringData();
			}
		);
		print_r( $arrAll );


		//	...
		$nCount	= $cStack->GetCount
		(
			function()
			{
				return $this->_LoadStringData();
			}
		);

		if ( $nCount > 10 )
		{
			$cStack->Pop
			(
				function()
				{
					return $this->_LoadStringData();
				},
				function( $sString )
				{
					return $this->_SaveStringData( $sString );
				}
			);
		}
	}


	private function _LoadStringData()
	{
		$sRet	= '';

		//	...
		$sData	= @ file_get_contents( $this->m_sDataFullFilename );
		if ( is_string( $sData ) && strlen( $sData ) > 0 )
		{
			$sRet = $sData;
		}

		return $sRet;
	}
	private function _SaveStringData( $sString )
	{
		if ( ! is_string( $sString ) || 0 == strlen( $sString ) )
		{
			return false;
		}

		//	...
		$bRet = false;

		//	...
		$sString = trim( $sString );
		if ( strlen( $sString ) > 0 )
		{
			if ( false !== file_put_contents( $this->m_sDataFullFilename, $sString ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}


}
