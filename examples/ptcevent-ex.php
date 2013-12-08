<?php

	/**
	* PTCEVENT DISPATCHER CLASS EXAMPLE FILE
	*/

	require_once( '../PtcEvent.php' );	// Require the component
	
	
	/* ADD AN EVENT LISTENER WITH A CLOSURE AS CALLBACK */
	PtcEvent::listen( 'form.submit' , function( $data )
	{
		// do some stuff
		print "called event with closure as callback!<br><br>";
	} );
	

	/* ADD AN EVENT LISTENER THAT WIL MANIPULATE DATA */
	PtcEvent::listen( 'ptc.query' , function( &$data )
	{
		// do some stuff
		print "called event to change data!<br>";
		print "previous value: " . $data . " - ";
		$data = 'some new data';
		print "new value: ". $data . "<br><br>";
	} );

	
	/* ADDING A FUNCTION AS CALLBACK WITH PRIORITY */
	function event_callback( $data )
	{
		// do some stuff
		print "event_callback( ) function called!<br><br>";
	}
	PtcEvent::listen( 'form.error' , 'event_callback' , 10 ); // higher priority will execute first


	/* PREVENTING EVENT PROPAGATION */
	PtcEvent::listen( 'form.error' , function( $data )
	{
		// do some stuff
		print "event propagation has been stoped!<br><br>";
		return false; // return false to prevent propagation
	} );
	
	
	/* USING WILDCARDS , EVENT NAME IS PASSED AS ARGUMENT */
	PtcEvent::listen( 'form.*' , function( $data , $event )
	{
		// do some stuff
		print "wildcard called on event " . $event . ':<pre>';
		print print_r( $data , true ) . "</pre><br><br>";
	} );
	
	/* REGISTERING CLASSES AS LISTENERS */
	class TableModel 
	{
		public function handle( $data )
		{
			// default method that will be called
			// do some stuff
			print "default handle( ) method called<br><br>";
		}
		
		public function myMethod( $data )
		{
			// do some stuff
			print "custom method has been called<br><br>";
		}	

	}
	/* REGISTERING THE CLASS WITH THE METHOD HANDLE( ) */
	PtcEvent::listen( 'form.success' , 'TableModel' );
	/* REGISTERING THE CLASS WITH A CUSTOM METHOD */
	PtcEvent::listen( 'form.error' , 'TableModel@myMethod' );


	/* FIRING THE EVENTS */
	PtcEvent::fire( 'form.submit' , array( 'form data' ) );
	PtcEvent::fire( 'form.error' , array( 'form data' ) );
	
	
	/* FIRING EVENT WITH THE DATA REFERENCED */
	$data = 'form data'; 
	print "The data value: " . $data . '<br>';
	PtcEvent::fire( 'ptc.query' , array( &$data ) ); // adding "&" references to manipulate the data
	print 'Data changed thanks to the "&" reference: ' . $data . '<br><br>';
	

	/* GETTING THE CURRENT EVENT LISTENERS */
	print "<b>The current event listeners:</b><pre>";
	print print_r( PtcEvent::getEvents( ) , true ) . "</pre>";
	

	/* REMOVING LISTENERS */
	PtcEvent::remove( 'form.error' ); // removing the last added event
	PtcEvent::remove( 'form.error' , 0 ); // removing the first event by key
	
