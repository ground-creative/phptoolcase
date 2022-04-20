<?php

	/* 
	* EXAMPLE FILE FOR THE AUTH CLASS
	*/	

	use phptoolcase\Debug;
	use phptoolcase\Db as DB;
	use phptoolcase\Auth;

	session_start( );				// start session for persistent debugging and code highlighter popup
	
	require dirname(__FILE__) . '/../vendor/autoload.php';

	declare(ticks=1);				// declare globally for the code coverage and function calls trace

	$_GET[ 'debug' ] = true;       		// turn on the debug

	//$_GET[ 'debug_off' ] = true;    	// turn off debug
	
	$options = array				// add some options before class initialization
	(
		'url_key'			=>	'debug' ,
		'url_pass'			=>	'true' ,
		'die_on_error'		=>	false ,// continue if fatal error
		'debug_console'	=>	true , // send messages to console, chrome only with php-console extension
	);

	Debug::load( $options );		// initialize the class

	
	/*** DB DETAILS NEEDED FOR THE EXAMPLE TO WORK ***/
	$db[ 'host' ] = 'localhost';				// mysql host
	$db[ 'user' ] = 'root';					// mysql user
	$db[ 'pass' ] = '';						// mysql pass
	$db[ 'database' ] = 'testtoolcase';			// mysql database name
	/*************************************************************/


	/* ADDING A CONNECTION, THIS WILL BE THE DEFAULT CONNECTION */
	Db::add( array
	(
		'user'			=>	$db[ 'user' ] ,
		'pass'			=>	$db[ 'pass' ] ,
		'db'				=>	$db[ 'database' ] ,
		'query_builder'		=>	true ,	// initialize the query builder as it is needed
	) );
	
	
	/* MINIMAL CONFIGURATION FOR THE AUTHENTICATION INTERFACE */
	Auth::configure( );
	
	
	/* SETUP THE DATABASE TABLES */
	Auth::setUp( );								// should be removed after first run
	
	
	/* ADDING A NEW USER TO THE DATABASE */
	Auth::create( 'some@email.com' , 'some_pass' );	// extra data and an isAdmin flag can also be added
	
	
	/* ATTEMPTING A LOGIN */
	Auth::login( 'some@email.com' , 'some_pass' );	// returns 1 if values match database record
	
	
	/* CHECKING IF USER IS LOGGED IN */
	Auth::check( ); 							// checks if we have a session user_id set
	
	
	/* RETRIEVE LOGGED IN USER DATA */
	//var_dump( Auth::user( ) );
	
	
	/* LOGOUT USER */
	Auth::logout( ); 							// destroys session user_id variable
	
	
	/* SET NEW PASSWORD FOR USER */
	Auth::password( 'some@email.com'  , 'some_new_pass' ); 
	
	
	/* SETTING ENCRYPTED COOKIES TO PREVENT FORGERY  AND MANIPUALTION */
	Auth::setCookie( 'some_cookie' , 'some val' , '30 days' , '/' );
	
	
	/* GETTING ENCRYPTED COOKIES VALUES */ 
	Auth::getCookie( 'some_cookie'  );