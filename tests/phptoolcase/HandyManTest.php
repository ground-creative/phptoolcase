<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use phptoolcase\HandyMan as HM;
	
	/**
	* @requires extension xdebug
	*/
	final class HandyManTest extends TestCase
	{
		public function testAddAppPath( )
		{
			HM::addAppPath( array
			( 
				'test' => dirname( __FILE__ ) . '/../../examples/autoloader'
			) );
			$app_path = HM::getAppPath( 'test' );
			$this->assertNotNull( $app_path );
			$this->assertDirectoryExists( $app_path );
		}
		/**
		* @depends testAddAppPath
		*/	
		public function testAddDirectory( )
		{
			HM::addDir( HM::getAppPath( 'test' ) );
			$this->assertCount( 1 , HM::getDirs( 'directories' ) );
		}
		/**
		* @depends testAddAppPath
		*/	
		public function testAddNamespaceDirectory( )
		{
			HM::addDir(
			[
				'nsTest' => HM::getAppPath( 'test' ) . '/namespaceTest'
			] );
			$this->assertCount( 1 , HM::getDirs( 'ns' ) );
		}
		/**
		* @depends testAddAppPath
		*/		
		public function testAddFile( )
		{
			HM::addFiles( array
			(
				'HmTestClassFile' => HM::getAppPath( 'test' ) . '/class-file.php' ,
				'ns\HmTestClassFile' => HM::getAppPath( 'test' ) . '/ns-class-file.php'
			) );
			$this->assertCount( 2 , HM::getDirs( 'files' ) );
		}
		
		public function testAddSeparator( )
		{
			HM::addSeparator( '-' );
			$sep = HM::getSeparators( );
			$this->assertCount( 1 , $sep );
			$this->assertEquals( '-' , $sep[ 0 ] );
		}
		
		public function testAddConvention( )
		{
			HM::addConvention( '{CLASS}' );
			$conv = HM::getConventions( );
			$this->assertCount( 1 , $conv );
			$this->assertEquals( '{CLASS}' , $conv[ 0 ] );
		}
		
		public function testArraySetValueByNumericKey( )
		{
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			HM::arraySet( $array , 'depth1.3' , 'some new value' );
			$this->assertEquals( 'some new value' , HM::arrayGet( $array , 'depth1.3' ) );
			HM::arraySet( $array , 'depth1.3.0' , 'another new value' );
			$this->assertEquals( 'another new value' , HM::arrayGet( $array , 'depth1.3.0' ) );
			HM::arraySet( $array , 'depth1.3.0' , 'forced new value' , true );
			$this->assertEquals( 'forced new value' , HM::arrayGet( $array , 'depth1.3.0' ) );
		}
		
		public function testArraySetValueByAlphanumericKey( )
		{
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			HM::arraySet( $array , 'depth1.some_key' , 'some new value' );
			$this->assertEquals( 'some new value' , HM::arrayGet( $array , 'depth1.some_key' ) );
			HM::arraySet( $array , 'depth1.some_key.another_key' , 'another new value' );
			$this->assertEquals( 'another new value' , HM::arrayGet( $array , 'depth1.some_key.another_key' ) );
		}
	
		public function testArraySetMultiDimensionalValue( )
		{
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			HM::arraySet( $array , 'depth1.3' , [ 'some new value' , 'some other value' ] );
			$array = HM::arrayGet( $array , 'depth1.3' );
			$this->assertTrue( is_array( $array ) );
			$this->assertCount( 2 , $array );
		}
		
		public function testArrayCount( )
		{
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			HM::arraySet( $array , 'depth1.3' , [ 'some new value' , 'some other value' ] );
			$this->assertEquals( 2 , HM::arrayCount( $array , 'depth1.3' ) );
		}
		
		public function testArrayDel( )
		{
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			HM::arrayDel( $array , 'depth1.2' );
			$this->assertCount( 2 , $array[ 'depth1' ] );
			HM::arraySet( $array , 'depth1.3' , [ 'some new value' , 'some other value' ] );
			HM::arraySet( $array , 'depth1.3.2' , 'another new value' );
			HM::arrayDel( $array , 'depth1.3.2' );
			$this->assertNull( HM::arrayGet( $array , 'depth1.3.2' ) );
			HM::arrayDel( $array , 'depth1.3' );
			$this->assertNull( HM::arrayGet( $array , 'depth1.3' ) );
		}
		/**
		* @runInSeparateProcess
		*/		
		public function testSessionTypes( )
		{
			HM::session( 'start' );
			$this->assertFalse( empty( session_id( ) ) );
			//HM::session( 'close' ); // how to test this one ?
			HM::session( 'destroy' );
			$this->assertTrue( empty( session_id( ) ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testSessionVariables( )
		{
			HM::sessionSet( 'string' , 'some value' );
			$this->assertEquals( 'some value' , HM::sessionGet( 'string' ) );
			HM::sessionSet( 'array' , [ 'some stuff' ] );
			$this->assertCount( 1 , HM::sessionGet( 'array' ) );
			HM::sessionSet( 'array.1' , 'some other value' );
			$this->assertCount( 2 , HM::sessionGet( 'array' ) );
			// this overwrites the key 'array.1' needs to be fixed 
			//HM::sessionSet( 'array.1.some_key' , 'some custom key value' );
			//$this->assertEquals( 'some custom key value' , HM::sessionGet( 'array.1.some_key' ) );
			HM::sessionSet( 'array.2.some_key' , 'some custom key value' );
			$this->assertEquals( 'some custom key value' , HM::sessionGet( 'array.2.some_key' ) );
			HM::sessionSet( 'array.2.some_key' , 'forced custom key value' , true );
			$this->assertEquals( 'forced custom key value' , HM::sessionGet( 'array.2.some_key' ) );
			HM::sessionDel( 'string' );
			$this->assertNull( HM::sessionGet( 'string' ) );
			HM::sessionDel( 'array.2.some_key' );
			$this->assertNull( HM::sessionGet( 'array.2.some_key') );
			HM::sessionDel( 'array.1' );
			$this->assertNull( HM::sessionGet( 'array.1') );
		}
		public function testJsonEncode( )
		{	
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			$json = HM::json( $array , null , false );
			$this->assertEquals( 55 , strlen( $json ) );
		}
		/**
		* @runInSeparateProcess
		*/
		public function testJsonEncodeWithContentTypeHeader( )
		{	
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			$json = HM::json( $array );
			$this->assertEquals( 55 , strlen( $json ) );
			$json_header = xdebug_get_headers( );
			$this->assertEquals( 'Content-Type: application/json' , $json_header[ 0 ] );
		}
		/**
		* @runInSeparateProcess
		*/
		public function testJsonpEncode( )
		{	
			$_GET[ 'some_js_function' ] = 'some_js_callback_function';
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			$json_p = HM::json( $array , 'some_js_function' , false );
			$this->assertEquals( 82 , strlen( $json_p ) );
		}
		/**
		* @runInSeparateProcess
		*/
		public function testJsonpEncodeWithContentTypeHeader( )
		{	
			$_GET[ 'some_js_function' ] = 'some_js_callback_function';
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			$json_p = HM::json( $array , 'some_js_function' );
			$json_header = xdebug_get_headers( );
			$this->assertEquals( 'Content-Type: application/javascript' , $json_header[ 0 ] );
		}
		
		public function testGetInaccessibleObjectProperty( )
		{
			$protected = HM::getProperty( new TestProtectedProperty( ) , '_test_protected' );
			$this->assertEquals( 'protected property' , $protected );
			$private = HM::getProperty( new TestProtectedProperty( ) , '_test_private' );
			$this->assertEquals( 'private property' , $private );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testRegisterAutoloader( )
		{
			HM::register( false , false , true );
			$this->assertCount( 0 , HM::getDirs( ) );
			$spl_functions = spl_autoload_functions( );
			$found = false;
			foreach ( $spl_functions as $autoloader )
			{
				if ( $autoloader[ 0 ] == 'phptoolcase\HandyMan' )
				{
					$found = true;
					break;
				}
			}
			$this->assertTrue( $found );
			$this->assertTrue( defined( '_PTCEVENT_' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function testRegisterAutoloaderWithBaseDir( )
		{
			HM::register( true , false , true );
			$this->assertCount( 1 , HM::getDirs( ) );
			$spl_functions = spl_autoload_functions( );
			$found = false;
			foreach ( $spl_functions as $autoloader )
			{
				if ( $autoloader[ 0 ] = 'phptoolcase\HandyMan' )
				{
					$found = true;
					break;
				}
			}
			$this->assertTrue( $found );
			$this->assertTrue( defined( '_PTCEVENT_' ) );
		}
		/**
		* @depends testAddDirectory
		* @depends testAddFile
		*/
		public function testAutoloadClass( )
		{
			HM::register( );
			ob_start( );
			$class = new \HmTestClass( );
			$file = new \HmTestClassFile( );
			ob_get_clean( );
			$this->assertInstanceOf( '\HmTestClass' , $class );
			$this->assertInstanceOf( '\HmTestClassFile' , $file );
		}
		/**
		* @depends testAddDirectory
		* @depends testAutoloadClass
		*/	
		public function testAutoloadClassFromAddedFiles( )
		{
			ob_start( );
			$class = new \HmTestClassLs( );	
			$file = new \ns\HmTestClassFile( );
			ob_get_clean( );
			$this->assertInstanceOf( '\HmTestClassLs' , $class );
			$this->assertInstanceOf( '\ns\HmTestClassFile' , $file );
		}
		/**
		* @depends testAddNamespaceDirectory
		* @depends testAutoloadClass
		*/	
		public function testAutoloadClassWithNameSpace( )
		{
			ob_start( );
			$class_ns = new \nsTest\HmTestNs( );
			$class_ns_deep = new \nsTest\hmNsDeep\HmTestNsDeep( );
			ob_get_clean( );
			$this->assertInstanceOf( '\nsTest\HmTestNs' , $class_ns );
			$this->assertInstanceOf( '\nsTest\hmNsDeep\HmTestNsDeep' , $class_ns_deep );
		}
		/**
		* @depends testAddAppPath
		* @depends testAddSeparator
		* @depends testAddConvention
		*/		
		public function testAutoloadClassWithSeperatorAndNamingConventions( )
		{
			HM::addDir( HM::getAppPath( 'test' ) . '/test-separators' );
			ob_start( );
			$sep_class = new \Hm_Test_Sep( );
			HM::addConvention( 'class.{CLASS}' );
			$sep_class1 = new \Hm_Test_Sep1( ); 
			ob_get_clean( );
			$this->assertInstanceOf( '\Hm_Test_Sep' , $sep_class );
			$this->assertInstanceOf( '\Hm_Test_Sep1' , $sep_class1 );
		}
		/**
		* @depends testAutoloadClass
		*/	
		public function testAddAlias( )
		{
			HM::addAlias( [ 'aliasTest' => 'HmTestClass' ] );
			ob_start( );
			$alias = new \aliasTest( );
			ob_get_clean( );
			$this->assertInstanceOf( '\aliasTest' , $alias );
		}
		/**
		* @depends testAddAlias
		*/
		public function testGetAlias( )
		{
			$this->assertCount( 1 , HM::getAlias( ) );
			$this->assertEquals( 'HmTestClass' , HM::getAlias( 'aliasTest' ) );
		}
	}