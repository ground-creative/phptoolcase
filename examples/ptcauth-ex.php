<?php


	session_start( );				// start session for persistent debugging and code highlighter popup

	declare(ticks=1);				// declare globally for the code coverage and function calls trace

	$_GET[ 'debug' ] = true;       		// turn on the debug

	//$_GET[ 'debug_off' ] = true;    	// turn off debug

	require_once( '../PtcDebug.php' );	// include the PtcDebug class

	$options = array				// add some options before class initialization
	(
		'url_key'			=>	'debug' ,
		'url_pass'			=>	'true' ,
		'die_on_error'		=>	false ,// continue if fatal error
		'debug_console'	=>	true , // send messages to console, chrome only with php-console extension
	);

	PtcDebug::load( $options );		// initialize the class

	/* 
	* EXAMPLE FILE FOR THE PTCAUTH CLASS
	*/	

	require_once( '../PtcAuth.php' );

	
	/*** DB DETAILS NEEDED FOR THE EXAMPLE TO WORK ***/
	$db[ 'user' ] = 'root';				// mysql user
	$db[ 'pass' ] = '';					// mysql pass
	$db[ 'database' ] = 'test';			// mysql database name
	/*************************************************************/

	require_once( '../PtcDb.php' ); // including the PtcDb class
	require_once( '../PtcQueryBuilder.php' ); // including the Query Builder class


	/* ADDING A CONNECTION, THIS WILL BE THE DEFAULT CONNECTION */
	PtcDb::add( array
	(
		'user'			=>	$db[ 'user' ] ,
		'pass'			=>	$db[ 'pass' ] ,
		'db'				=>	$db[ 'database' ] ,
		'query_builder'		=>	true ,	// initialize the query builder as it is needed
	) );
	
	
	/* MINIMAL CONFIGURATION FOR THE AUTHENTICATION INTERFACE */
	PtcAuth::configure( );
	
	
	/* SETUP THE DATABASE TABLES */
	//PtcAuth::setUp( );	// should be removed after first run
	
	
	/* ADDING A NEW USER TO THE DATABASE */
	PtcAuth::create( 'some@email.com' , 'some_pass' );	// extra data and an isAdmin flag can also be added
	
	
	/* ATTEMPTING A LOGIN */
	PtcAuth::login( 'some@email.com' , 'some_pass' );	// returns 1 if values match database record
	
	
	/* CHECKING IF USER IS LOGGED IN */
	PtcAuth::check( ); // checks if we have a session user_id set
	
	
	/* RETRIEVE LOGGED IN USER DATA */
	//var_dump( PtcAuth::user( ) );
	
	
	/* LOGOUT USER */
	PtcAuth::logout( ); // destroys session user_id variable
	
	
	/* SET NEW PASSWORD FOR USER */
	PtcAuth::password( 'some@email.com'  , 'some_new_pass' ); 
	
	
	/* SETTING ENCRYPTED COOKIES TO PREVENT FORGERY  AND MANIPUALTION */
	PtcAuth::setCookie( 'some_cookie' , 'some val' , '30 days' , '/' );
	
	
	/* GETTING ENCRYPTED COOKIES VALUES */ 
	PtcAuth::getCookie( 'some_cookie'  );
	
	
	
	