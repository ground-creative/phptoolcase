<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

	final class EventTest extends TestCase
	{
		public function testAddSimpleListener( )
		{
			Event::listen( 'test.event1' , function( $data = null )
			{
				print "called event with closure as callback!<br><br>";
			} );
		}
		
		public function testAddDeclaredFunctionAsListener( )
		{
			Event::listen( 'test.event2' , '\phptoolcase\event_callback' ); 
		}
		
		public function testAddClassAsListener( )
		{
			Event::listen( 'test.event3' , '\phptoolcase\MyObserver' ); 
		}
		
		public function testAddClassAsListenerWithCustomMethod( )
		{
			Event::listen( 'test.event4' , '\phptoolcase\MyObserver@myMethod' ); 
		}
		
		public function testAddWildCardListener( )
		{
			Event::listen( 'test.*' , function( $data = null , $event )
			{
				// do some stuff
				print "wildcard called on event " . $event;// . ':<pre>';
				//print print_r( $data , true ) . "</pre><br><br>";
			} );
		}
		
		public function testAssignPriority( )
		{
			Event::listen( 'test.event' , function( $data = null )
			{
			      print "method with low priority has been called<br><br>";
			}  , 10 );
		}
	}
	
	class MyObserver 
	{
		public function handle( $data = null )
		{
			print "default handle( ) method called<br><br>";
		}
		
		public function myMethod( $data = null )
		{
		      print "custom method has been called<br><br>";
		}  
	}
			
	function event_callback( $data = null )
	{
		print "event_callback( ) function called!<br><br>";
	}