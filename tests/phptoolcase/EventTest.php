<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

	final class EventTest extends TestCase
	{
		public function testAddSimpleListener( )
		{
			Event::listen( 'test.event1' , function( $obj , $var )
			{
				$obj->assertTrue( $var );
			} );
			$events = Event::get( 'test' );
			$this->assertTrue( is_array( $events ) );
			$this->assertArrayHasKey( 'event1' , $events );
		}
		
		public function testAddDeclaredFunctionAsListener( )
		{
			Event::listen( 'test.event2' , '\phptoolcase\event_callback' ); 
			$events = Event::get( 'test' );
			$this->assertTrue( is_array( $events ) );
			$this->assertArrayHasKey( 'event2' , $events );
		}
		
		public function testAddClassAsListener( )
		{
			Event::listen( 'test.event3' , '\phptoolcase\MyObserver' ); 
			$events = Event::get( 'test' );
			$this->assertTrue( is_array( $events ) );
			$this->assertArrayHasKey( 'event3' , $events );
			$this->assertInstanceOf( '\phptoolcase\MyObserver' , $events[ 'event3' ][ 0 ][ 'callback' ][ 0 ] );
		}
		
		public function testAddClassAsListenerWithCustomMethod( )
		{
			Event::listen( 'test.event4' , '\phptoolcase\MyObserver@myMethod' ); 
			$events = Event::get( 'test' );
			$this->assertTrue( is_array( $events ) );
			$this->assertArrayHasKey( 'event4' , $events );
			$this->assertInstanceOf( '\phptoolcase\MyObserver' , $events[ 'event4' ][ 0 ][ 'callback' ][ 0 ] );
			$this->assertEquals( 'myMethod' , $events[ 'event4' ][ 0 ][ 'callback' ][ 1 ] );
		}
		
		public function testAddWildCardListener( )
		{
			Event::listen( 'test.*' , function( $data , $event )
			{
				$data[ 0 ]->assertTrue( $data[ 1 ] );
				$data[ 0 ]->assertStringStartsWith( 'test' , $event );
			} );
		}
		
		public function testManipulateData( )
		{
			Event::listen( 'test.event6' , function( $obj , &$var )
			{
			    $var = false;
			} );
			$var = true;
			Event::fire( 'test.event6' , [ $this , &$var ] );
			$this->assertFalse( $var );
		}
		
		public function testAssignPriority( )
		{
			Event::listen( 'test.event7' , function( $obj , &$var )
			{
				$var = true;
			}  , 20 );
			Event::listen( 'test.event7' , function( $obj  , &$var )
			{
				$var = false;
			}  , 10 );
			$var = true;
			Event::fire( 'test.event7' , [ $this , &$var ] );
			$this->assertFalse( $var );
		}
		/**
		* @depends testAddWildCardListener
		* @depends testAddSimpleListener
		*/	
		public function testFireEvent( )
		{
			Event::fire( 'test.event1' , [ $this , true ] );
		}
		/*
		* @depends testAddSimpleListener
		*/
		public function testGetEvents( )
		{
			$events = Event::get( 'test' );
			$this->assertArrayHasKey( 'event1' , $events );
		}
		
		public function testPreventEventPropagation( )
		{
			Event::listen( 'prevent.propagation' , function( $obj , $var )
			{
				$obj->assertTrue( $var );
				return false;	// preventing event propagation
			} , 20 );
			Event::listen( 'prevent.propagation' , function( $obj , $var )
			{
				$obj->assertCount( 0 , [ 'foo' ] );
			} , 10 );
			Event::fire( 'prevent.propagation' , [ $this , true ] );
		}
		
		/*
		* @depends testAddSimpleListener
		*/
		public function testRemoveLastAddedListener( )
		{
			Event::listen( 'test.event1' , function( $obj , $var )
			{
				$this->assertCount( 0 , [ 'foo' ] );
			} );
			Event::remove( 'test.event1' );
			$events = Event::get( 'test' );
			$this->assertCount( 1 ,  $events[ 'event1' ] );
		}
		/*
		* @depends testAddSimpleListener
		*/
		public function testRemoveAddedListenerByKey( )
		{
			Event::listen( 'test.event1' , function( $obj , $var )
			{
				$obj->assertCount( 0 , [ 'foo' ] );
			} );
			Event::remove( 'test.event1' , 1 );
			$events = Event::get( 'test' );
			$this->assertCount( 1 , $events[ 'event1' ] );
			Event::fire( 'test.event1' , [ $this , true ] );
		}
		
		public function testRegisterClassNameConstant( )
		{
			Event::register( );
			$this->assertTrue( defined( '_PTCEVENT_' ) );
		}
	}