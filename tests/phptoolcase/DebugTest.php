<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

	final class DebugTest extends TestCase
	{
		
		public function testDumpVar( )
		{
			$var = 'some string';
			ob_start( );
			Debug::dumpVar( $var );
			$data = ob_get_clean( );
			$this->assertEquals( 82 , strlen( $data ) );
		}
		
		public function testPrintVar( )
		{
			$var = 'some string';
			ob_start( );
			Debug::dumpVar( $var );
			$data = ob_get_clean( );
			$this->assertEquals( 82 , strlen( $data ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLoad( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( );
			$this->assertTrue( Debug::isLoaded( ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testIsAlreadyLoaded( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( );
			Debug::load( );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'Debug already loaded!' , $result[ 1 ][ 'errstr' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogString( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			Debug::bufferLog( 'just a message' );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'just a message' , $result[ 1 ][ 'console_string' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogStringWithStatement( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$var = 'just a string';
			Debug::bufferLog( $var, 'testing a variable' );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'testing a variable' , $result[ 1 ][ 'console_statement' ] );
			$this->assertEquals( 'just a string' , $result[ 1 ][ 'console_string' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogStringWithCustomCategory( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			Debug::bufferLog( 'just a message' , '' , 'some category' );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'just a message' , $result[ 1 ][ 'console_string' ] );
			$this->assertEquals( 'some category' , $result[ 1 ][ 'console_category' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogArray( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$array = [ 'key' => 'value' , 'key1' => 'value1' , 'key2' => [ 'some value' ] ];
			Debug::bufferLog( $array );
			$result = Debug::getBuffer( );
			$this->assertTrue( is_array( $result[ 1 ][ 'console_string' ] ) );
			$this->assertEquals( 'value' , $result[ 1 ][ 'console_string' ][ 'key' ] );
			$this->assertEquals( 'value1' , $result[ 1 ][ 'console_string' ][ 'key1' ] );
			$this->assertEquals( 'some value' , $result[ 1 ][ 'console_string' ][ 'key2' ][ 0 ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogObject( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$array = [ 'key' => 'value' , 'key1' => 'value1' ];
			Debug::bufferLog( ( object ) $array );
			$result = Debug::getBuffer( );
			$this->assertTrue( is_object( $result[ 1 ][ 'console_string' ] ) );
			$this->assertEquals( 'value' , $result[ 1 ][ 'console_string' ]->key );
			$this->assertEquals( 'value1' , $result[ 1 ][ 'console_string' ]->key1 );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogSimpleXmlObject( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$xml = new \SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"utf-8\"?><root>some test</root>" );
			Debug::bufferLog( $xml );
			$result = Debug::getBuffer( );
			$this->assertTrue( is_object( $result[ 1 ][ 'console_string' ] ) );
			$this->assertInstanceOf( \SimpleXMLElement::class , $result[ 1 ][ 'console_string' ] );
			$this->assertEquals( 62 , strlen( $result[ 1 ][ 'console_string' ]->asXML( ) ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogReflectionClass( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$o_reflection_class = new \ReflectionClass( 'phptoolcase\Db' );
			Debug::bufferLog( $o_reflection_class );
			$result = Debug::getBuffer( );
			$this->assertInstanceOf( \ReflectionClass::class , $result[ 1 ][ 'console_string' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogReflectionFunction( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$ref_function = new \ReflectionFunction( 'ptc_path' );
			Debug::bufferLog( $ref_function );
			$result = Debug::getBuffer( );
			$this->assertInstanceOf( \ReflectionFunction::class , $result[ 1 ][ 'console_string' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogClosure( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$closure = function( ){ return true; };
			Debug::bufferLog( $closure );
			$result = Debug::getBuffer( );
			$this->assertInstanceOf( \Closure::class , $result[ 1 ][ 'console_string' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLogSql( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			$sql = 'select from where something';
			Debug::bufferSql( '' , $sql );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'sql' , $result[ 1 ][ 'type' ] );
			$this->assertEquals( 'select from where something' , $result[ 1 ][ 'console_statement' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testAddToBuffer( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			Debug::bufferLog( '' , 'some message' );
			Debug::addToBuffer( 'some message' , [ 'some array value' ] );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'some array value' , $result[ 1 ][ 'console_string' ][ 0 ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testThrowPhpNotice( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'replace_error_handler' =>  true ] );
			trigger_error( 'some notice' , E_USER_NOTICE );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'Php Notice' , $result[ 1 ][ 'console_string' ][ 'errno' ] );
			$this->assertEquals( 'some notice' , $result[ 1 ][ 'console_string' ][ 'errstr' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testThrowPhpWarning( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'replace_error_handler' =>  true ] );
			trigger_error( 'some warning' , E_USER_WARNING );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'Php Warning' , $result[ 1 ][ 'console_string' ][ 'errno' ] );
			$this->assertEquals( 'some warning' , $result[ 1 ][ 'console_string' ][ 'errstr' ] );
		}
		/**
		* @runInSeparateProcess
		*/
		public function testCatchPhpError( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'replace_error_handler' =>  true , 'die_on_error'=> false ] );
			trigger_error( 'some error' , E_USER_ERROR );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'Php Error' , $result[ 1 ][ 'console_string' ][ 'errno' ] );
			$this->assertEquals( 'some error' , $result[ 1 ][ 'console_string' ][ 'errstr' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testCatchPhpInternalError( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'replace_error_handler' =>  true ] );
			fopen( );
			$result = Debug::getBuffer( );
			$this->assertEquals( 'Php Warning' , $result[ 1 ][ 'console_string' ][ 'errno' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testDisableReplaceErrorHandler( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'replace_error_handler' =>  false ] );
			@trigger_error( 'some notice' , E_USER_NOTICE );
			$result = Debug::getBuffer( );
			$this->assertCount( 1 , $result );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testCatchException( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'catch_exceptions' =>  true ] );
			//throw new \Exception( 'Uncaught Exception' );
			//$result = Debug::getBuffer( );
			$lastHandler = set_exception_handler( null );
			$this->assertEquals( 'phptoolcase\Debug' , $lastHandler[ 0 ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		/*public function testWatchVariableChanges( )
		{
			// ticks are not working with phpunit
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'enable_inspector' => true ] );
			declare(ticks=1)	
			{
				$var = 'some test';
				Debug::watch( 'var' );
				$var = 'some new value';
			}
			$result = Debug::getBuffer( );
			//var_dump( $result );
		}*/
		/**
		* @runInSeparateProcess
		*/	
		public function testCheckReferer( )
		{
			$_SERVER[ 'HTTP_REFERER' ] = 'https://127.0.0.1/?debug=true';
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'check_referer' =>  true ] );
			$this->assertTrue( Debug::isLoaded( ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testGetClientIp( )
		{
			$this->assertEquals( '127.0.0.1' , Debug::getClientIP( ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testCustomUrlkeyAndPass( )
		{
			$_GET[ 'custom' ] = 'pass';
			Debug::load( [ 'url_key' => 'custom' , 'url_pass' => 'pass' ] );
			$this->assertTrue( Debug::isLoaded( ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testTimer( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			Debug::bufferLog( '' , 'timing a loop' );
			for ( $i = 0; $i < 100; $i++ ){ @$a[ ] = $i; }
			Debug::stopTimer( 'timing a loop' );
			$result = Debug::getBuffer( );
			$this->assertStringMatchesFormat( '%f ms' , $result[ 1 ][ 'console_time' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testLimitPhpTrace( )
		{
			$_GET[ 'debug' ] = true;
			Debug::load( [ 'show_interface' => false , 'debug_console' => true , 'trace_depth' => 5 ] );
			Debug::bufferLog( 'just a message' );
			$result = Debug::getBuffer( );
			$this->assertCount( 5 , $result[ 1 ][ 'errfile' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		/*public function testLimitDump( )
		{
			
		}*/
		/**
		* @runInSeparateProcess
		*/	
		/*public function testGetCodeCoverageData( )
		{
			
		}*/
	}