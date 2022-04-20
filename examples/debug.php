<?php

	/* 
	* EXAMPLE FILE FOR DEBUG CLASS
	*/
	
	use phptoolcase\Debug;

	session_start( );				// start session for persistent debugging and code highlighter popup

	declare(ticks=1);				// declare globally for the code coverage and function calls trace

	$_GET[ 'debug' ] = true;       		// turn on the debug

	//$_GET[ 'debug_off' ] = true;    	// turn off debug

	require dirname(__FILE__) . '/../vendor/autoload.php';

	$options = array
	(
		'url_key'			=>	'debug' ,
		'url_pass'			=>	'true' ,
		'die_on_error'		=>	false ,// continue if fatal error
		'debug_console'	=>	true , // send messages to console, chrome only with php-console extension
	);

	Debug::load( $options );		// initialize the class
	
	
	/* START CODE COVERAGE ANALYSIS TO CHECK WHICH LINES HAVE BEEN EXECUTED */
	Debug::startCoverage( );	// set option['code_coverage'] to "full" to check the hole application
	
	
	/* START TRACING FUNCTION CALLS */
	Debug::startTrace( );	// set option['trace_functions'] to "full" to check the hole application


	/* LOGGING A MESSAGE */
	Debug::bufferLog( 'just a message' );


	/* LOGGING A VARIABLE WITH A STATEMENT */
	$var = 'just a string';
	Debug::bufferLog( $var, 'testing a variable' );


	/* LOGGING AN ARRAY TO THE MESSAGE PANEL WITH A DIFFERENT CATEGORY */
	$array = array( 'key' => 'value' , 'key1' => 'value1' );
	Debug::bufferLog( $array , 'testing an array' , 'new category' );
	
	
	/* LOGGING AN OBJECT */
	Debug::bufferLog( ( object ) $array , 'testing an object' );


	/* THROWING A NOTICE */
	trigger_error( 'some notice' , E_USER_NOTICE );


	/* THROWING A WARNING */
	trigger_error( 'some warning' , E_USER_WARNING );


	/* THROWING AN ERROR */
	trigger_error( 'some error' , E_USER_ERROR );	// continue execution with the options "die_on_error" set to false


	/* TESTING AN ERROR WITHIN A FUNCTION */
	function some_func( ){ fopen( ); }
	echo some_func( );						// will throw an error


	/* LOGGING SQL QUERIES AND TIMING EXECUTION */
	$sql = 'select from where something';		// some sql query, will be used as reference
	Debug::bufferSql( '' , $sql  );			// leaving the first parameter empty, can be added later with the query result
	$sql_result = array( 'key' => 'value' , 'key1' => 'value1' ); // this should be the sql result of the sql query
	Debug::stopTimer( $sql );				// time execution, the query is used as reference
	Debug::addToBuffer( $sql , $sql_result );	// attaching the result to the message based on the reference


	/* WATCHING A VARIABLE */	
	declare(ticks=1)						// declaring code block it is more precise for watching vars
	{
		$var = 'some test';
		Debug::watch( 'var' );				// passing the variable without the "$" symbol
		$var = 'some new value';				// the variable changed
	}
	
	
	/* TIMING A LOOP */
	Debug::bufferLog( '' , 'timing a loop' );	// leaving the first parameter empty
	for ( $i = 0; $i < 100; $i++ ){ @$a[ ] = $i; }
	Debug::stopTimer( 'timing a loop' );		// using the reference to attach the execution time to the buffer
	
	
	/* STOP CODE COVERAGE ANALYSIS */
	Debug::stopCoverage( );	// we could start it again later, if stopCoverage( ) is not used it will be stopped at shutdown
	
	
	/* STOT TRACING FUNCTION CALLS */
	Debug::stopTrace( );		// we could start it again later, if stopTrace( ) is not used it will be stopped at shutdown
	
	
	/* DOWLOAD PHP-CONSOLE FOR CHROME TO SEE MESSAGES IN CONSOLE */
	Debug::bufferLog( '' , '<span style="color:red;">**For Chrome Browser:</span> 
						<a target="_blank" href="https://chrome.google.com/webstore/detail/php-console/nfhmhhlpfleoednkpnnnkolmclajemef">
							Download php-console</a> chrome extension to see debug output in console');
	
	
	/* CATCHING AN EXCEPTION */
	throw new Exception( 'Uncaught Exception' );