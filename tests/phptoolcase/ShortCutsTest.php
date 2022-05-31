<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use phptoolcase\Debug;
	use phptoolcase\HandyMan as HM;

	final class ShortCutsTest extends TestCase
	{
	
		public function test_ptc_log( )
		{
			$_GET[ 'debug' ] = true;
			//Debug::load( [ 'show_interface' => false , 'debug_console' => true ] );
			ptc_log( 'just a message' ); 
			$result = Debug::getBuffer( );
			$this->assertEquals( 'just a message' , $result[ 1 ][ 'console_string' ] );
		}
		
		/**
		* @runInSeparateProcess
		*/	
		/*public function test_ptc_watch( )
		{
			
		}*/
		/**
		* @runInSeparateProcess
		*/	
		/*public function test_ptc_start_coverage( )
		{
			
		}*/
		/**
		* @runInSeparateProcess
		*/	
		/*public function test_ptc_stop_coverage( )
		{
			
		}*/
		/**
		* @runInSeparateProcess
		*/	
		/*public function test_start_trace( )
		{
			
		}*/
		/**
		* @runInSeparateProcess
		*/	
		/*public function test_stop_trace( )
		{
			
		}*/
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_add_path( )
		{
			Hm::register( );
			ptc_add_path( [ 'test' => dirname( __FILE__ ) . '/../../examples/autoloader' ] );
			$app_path = HM::getAppPath( 'test' );
			$this->assertNotNull( $app_path );
			$this->assertDirectoryExists( $app_path );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_dir( )
		{
			Hm::register( );
			HM::addDir( [ 'test' => dirname( __FILE__ ) . '/../../examples/autoloader' ] );
			$this->assertCount( 1 , ptc_dir( 'directories' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_add_dir( )
		{
			Hm::register( );
			ptc_add_dir( [ 'test' => dirname( __FILE__ ) . '/../../examples/autoloader' ] );
			$this->assertCount( 1 , HM::getDirs( 'directories' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_add_file( )
		{
			Hm::register( );
			ptc_add_path( [ 'test' => dirname( __FILE__ ) . '/../../examples/autoloader' ] );
			ptc_add_file( array
			(
				'HmTestClassFile' => HM::getAppPath( 'test' ) . '/class-file.php' ,
				'ns\HmTestClassFile' => HM::getAppPath( 'test' ) . '/ns-class-file.php'
			) );
			$this->assertCount( 2 , HM::getDirs( 'files' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_get_prop( )
		{
			Hm::register( );
			$protected = ptc_get_prop( new TestProtectedProperty( ) , '_test_protected' );
			$this->assertEquals( 'protected property' , $protected );
			$private = HM::getProperty( new TestProtectedProperty( ) , '_test_private' );
			$this->assertEquals( 'private property' , $private );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_array_get( )
		{
			Hm::register( );
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			HM::arraySet( $array , 'depth1.3' , 'some new value' );
			$this->assertEquals( 'some new value' , ptc_array_get( $array , 'depth1.3' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_array_set( )
		{
			Hm::register( );
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			ptc_array_set( $array , 'depth1.3' , 'some new value' );
			$this->assertEquals( 'some new value' , HM::arrayGet( $array , 'depth1.3' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_array_count( )
		{
			Hm::register( );
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			HM::arraySet( $array , 'depth1.3' , [ 'some new value' , 'some other value' ] );
			$this->assertEquals( 2 , ptc_array_count( $array , 'depth1.3' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_array_del( )
		{
			Hm::register( );
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			ptc_array_del( $array , 'depth1.2' );
			$this->assertCount( 2 , $array[ 'depth1' ] );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_session_get( )
		{
			Hm::register( );
			HM::sessionSet( 'string' , 'some value' );
			$this->assertEquals( 'some value' , ptc_session_get( 'string' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_session_set( )
		{
			Hm::register( );
			ptc_session_set( 'string' , 'some value' );
			$this->assertEquals( 'some value' , HM::sessionGet( 'string' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_session_del( )
		{
			Hm::register( );
			HM::sessionSet( 'string' , 'some value' );
			ptc_session_del( 'string' );
			$this->assertNull( HM::sessionGet( 'string' ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_session( )
		{
			Hm::register( );
			ptc_session( 'start' );
			$this->assertFalse( empty( session_id( ) ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_json( )
		{
			Hm::register( );
			$array = [ 'depth1' => [ 'first value' , 'second value' , 'third value' ] ];
			$json = ptc_json( $array , null , false );
			$this->assertEquals( 55 , strlen( $json ) );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_listen( )
		{
			Event::register( );
			ptc_listen( 'test.event1' , function( $obj , $var )
			{
				$obj->assertTrue( $var );
			} );
			$events = Event::get( 'test' );
			$this->assertTrue( is_array( $events ) );
			$this->assertArrayHasKey( 'event1' , $events );
		}
		/**
		* @runInSeparateProcess
		*/	
		public function test_ptc_fire( )
		{
			Hm::register( );
			ptc_listen( 'test.event1' , function( $obj , $var )
			{
				$obj->assertTrue( $var );
			} );
			ptc_fire( 'test.event1' , [ $this , true ] );
		}
	}