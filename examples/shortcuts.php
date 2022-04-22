<?php

	/* 
	* EXAMPLE FILE FOR HELPER FUNCTIONS FOR THE LIBRARY COMPONENTS
	* ALL EXAMPLES HAVE BEEN TAKEN FROM THE COMPONENTS EXAMPLE FILES
	* HM.PHP, DEBUG.PHP, EVENT.PHP AND THE AUTOLOADER EXAMPLE FILES FOLDER 
	* ARE REQUIRED FOR THESE EXAMPLES
	*/
	
	use phptoolcase\HandyMan;
	use phptoolcase\Debug;
	
	session_start( );				// start session for persistent debugging and code highlighter popup

	require dirname(__FILE__) . '/../vendor/autoload.php';
	
	declare(ticks=1);				// declare globally for the code coverage and function calls trace
	
	/* REGISTER THE AUTOLOADER */
	HandyMan::register( );			// will auto include the ptc-helpers.php file and all other classes
	
	
	/* START THE DEBUGGER & LOGGER COMPONENT */
	$_GET[ 'debug' ] = true;       		// turn on the debug
	//$_GET['debug_off']=true;    		// turn off debug
	$options=array				// add some options before class initialization
	(
		'url_key'				=>	'debug',
		'url_pass'				=>	'true',
		'die_on_error'			=>	false,	// continue if fatal error
		'debug_console'		=>	true , 	// send messages to console, chrome only with php-console extension
		'exclude_categories'	=>	null		// don't exclude categories from the output
	);
	Debug::load( $options );		// initialize the class

	
	/*** DEBUGGER & LOGGER HELPERPS ****************************************************/
	
		/* START CODE COVERAGE ANALYSIS TO CHECK WHICH LINES HAVE BEEN EXECUTED */
		ptc_start_coverage( );				// Debug::startCoverage( )
	
	
		/* START FUNCTION CALLS TRACING  */
		ptc_start_trace( );					// Debug::startTrace( )
	
	
		/* LOGGING A MESSAGE */
		ptc_log( 'Using phptoolcase helper functions to type less!' ); // Debug::bufferLog( )
		
		
		/* LOGGING SQL QUERIES AND TIMING EXECUTION */
		$sql = 'select from where something';	// some sql query, will be used as reference
		ptc_log_sql( '' , $sql  );				// Debug::bufferSql( )
		$sql_result = array( 'key' => 'value' , 'key1' => 'value1' ); // this should be the sql result of the sql query
		ptc_stop_timer( $sql );				// Debug::stopTimer( )
		ptc_attach( $sql , $sql_result );		// Debug::addToBuffer( )
	
	
		/* WATCHING A VARIABLE */	
		declare(ticks=1)					// declaring code block it is more precise for watching vars
		{
			$var = 'some test';
			ptc_watch( 'var' );				// Debug::watch( )
			$var = 'some new value';		// the variable changed
		}
		
		
		/* STOP CODE COVERAGE ANALYSIS */
		ptc_stop_coverage( );				// Debug::stopCoverage( )
		
		
		/* STOP FUNCTION CALLS TRACING */
		ptc_stop_trace( );					// Debug::stopTrace( )
	
	
	/*** HANDYMAN HELPERPS ****************************************************/
	
		/* ADDING APPLICATION PATHS FOR LATER USAGE ( HandyMan::addAppPath( ) ) */
		ptc_add_path( array
		( 
			'lib' => dirname( __FILE__ ) . '/autoloader-example-files' 	// creating an entry in the application paths array
		) );


		/* ADDING CLASS FILES ( HandyMan::addFile( ) ) */
		ptc_add_file( array
		(
			'HmTestClassFile' => ptc_path( 'lib' ) . '/class-file.php' , // HandyMan::getAppPath( )
			'ns\HmTestClassFile'=> ptc_path( 'lib' ) . '/ns-class-file.php' , // HandyMan::getAppPath( )
		) );


		/* ADDING DIRECTORIES WITH CLASSES TO THE AUTOLOADER ( HandyMan::addDir( ) ) */
		ptc_add_dir( ptc_path( 'lib' ) );	 					// HandyMan::getAppPath(
		
		
		/* ADDING A NAMESPACED DIRECTORY WITH CLASSES TO THE AUTOLOADER */
		ptc_add_dir( array
		( 
			'nsTest' => ptc_path( 'lib' )  . '/namespaceTest'   // HandyMan::getAppPath( )
		));
			
		
		/* GETTING THE DIRECTORIES OF THE AUTOLOADER ( HandyMan::getDirs( ) )*/
		$dirs = ptc_dir( );			// HandyMan::getDirs( ) params: ( files , directories , ns )
		ptc_log( $dirs , 'getting all directories and files to be autoloaded' ); //Debug::bufferLog( );
		
		
		/* RETRIEVE VALUES FROM A MULTIDIMENSIONAL ARRAY */
		$array = array
		( 
			'depth1'	=>	array( 'first value' , 'second value' , 'third value' )
		);
		print 'Getting a value inside a multidimensional array: ';
		print ptc_array_get( $array , 'depth1.0' );	// HandyMan::arrayGet( )
		
		
		/* SETTING VALUES IN A MULTIDIMENSIONAL ARRAY */
		print '<br><br>Setting a value inside a multidimensional array: ';
		ptc_array_set( $array , 'depth1.3' , 'some new value' );	// HandyMan::arraySet( )
		ptc_array_set( $array , 'depth1.4' , array( 'some new value' , 'some other value' ) ); // setting an array as value
		print ptc_array_get( $array , 'depth1.4.0' );			// HandyMan::arrayGet( )
		ptc_array_set( $array , 'depth1.3' , 'forced new value' , true ); // force to change a value that is already set	
		

		/* COUNT VALUES OF ELEMENT INSIDE MULTIDIMENSIONAL ARRAY */
		print '<br><br>Counting values of an element inside a multidimensional array: ';
		print ptc_array_count( $array , 'depth1.4' );		// HandyMan::arrayCount( )
		
		
		/* REMOVE ELEMENTS FROM MULTIDIMENSIONAL ARRAY */
		ptc_array_del( $array , 'depth1.2' );			// HandyMan::arrayDel( )
		
		
		/* WORKING WITH SESSIONS */
		print '<br><br><br><b>WORKING WITH SESSION VARIABLES:</b><br><br>';
		
		
		/* STARTING A SESSION WITH THE SESSION MANAGER */
		ptc_session( 'start' );						// HandyMan::session( )
		
		
		/* SET AND RETRIEVE SESSION VALUES */
		ptc_session_set( 'val' , 'some value' );			// HandyMan::sessionSet( )
		ptc_session_set( 'key' , array( 'some stuff' ) );		// HandyMan::sessionSet( )
		ptc_session_set( 'key.1' , 'some other value' );	// HandyMan::sessionSet( )
		print 'retrieve session values: ';
		print ptc_session_get( 'key.1' );				// HandyMan::sessionGet( )
		ptc_session_set( 'key.1' , 'some new value' , true ); // force to change a value that is already set	
		
		
		/* DELETING SESSION VALUES */
		ptc_session_del( 'key.0' ); 					// HandyMan::sessionDel( )
		
		
		/* DESTROYING AND  CLOSING A SESSION WITH THE SESSION MANAGER */
		ptc_session( 'destroy' );						// HandyMan::session( )
		ptc_session( 'close' );						// HandyMan::session( )
		
		
		/* CONVERT  ARRAY TO JSON AND SEND HEADER RESPONSE */
		print '<br><br><br><b>CONVERTING ARRAYS TO JSON:</b><br><br>';
		print ptc_json( $array , null , false ); 			// HandyMan::json( )
		//print '<br><br><br><b>CONVERTING ARRAYS TO JSONP:</b><br><br>';
		//print ptc_json( $array , 'jsonp_function' , false );  	// HandyMan::json( )


	/*** EVENT HELPERS ****************************************************/

		/* ADDING EVENT LISTENERS ( Event::listen( ) ) */
		ptc_listen( 'some.event' , function( $data ) 
		{
			// do some stuff
			ptc_log( $data , 'Called event with closure as call back' );  // Debug::bufferLog( )
		} );
		
		
		/* FIRING EVENTS ( Event::fire( ) ) */
		ptc_fire( 'some.event' , array( 'some data' ) );