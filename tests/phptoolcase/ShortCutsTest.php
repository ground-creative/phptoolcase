<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use phptoolcase\Debug;

	final class ShortCutsTest extends TestCase
	{
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_log( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			ptc_log( 'just a message' ); 
			$result = Debug::getBuffer( );
			$this->assertEquals( 'just a message' , $result[ 1 ][ 'console_string' ] );
		}
	}