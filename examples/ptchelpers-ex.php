<?php

	/* 
	* EXAMPLE FILE FOR HELPER FUNCTIONS FOR THE LIBRARY COMPONENTS
	* ALL EXAMPLES HAVE BEEN TAKEN FROM THE COMPONENTS EXAMPLE FILES
	* PTCHM.PHP AND PTCDEBUG.PHP AND THE AUTOLOADER EXAMPLE FILES FOLDER 
	* ARE REQUIRED FOR THESE EXAMPLES
	*/
	
	session_start( );				// start session for persistent debugging and code highlighter popup

	require_once( '../PtcHm.php' );	// require the handyman component
	
	declare(ticks=1);				// declare globally for the code coverage and function calls trace
	
	/* REGISTER THE AUTOLOADER */
	PtcHandyMan::register( );		// will auto include the ptc-helpers.php file
	
	
	/* START THE DEBUGGER & LOGGER COMPONENT */
	$_GET[ 'debug' ] = true;       		// turn on the debug
	//$_GET['debug_off']=true;    		// turn off debug
	$options=array				// add some options before class initialization
	(
		'url_key'		=>	'debug',
		'url_pass'		=>	'true',
		'die_on_error'	=>	false,	// continue if fatal error
	);
	PtcDebug::load( $options );		// initialize the class

	
	/*** PTC DEBUGGER & LOGGER HELPERPS ****************************************************/
	
		/* START CODE COVERAGE ANALYSIS TO CHECK WHICH LINES HAVE BEEN EXECUTED */
		ptc_start_coverage( );				// PtcDebug::startCoverage( )
	
	
		/* START FUNCTION CALLS TRACING  */
		ptc_start_trace( );					// PtcDebug::startTrace( )
	
	
		/* LOGGING A MESSAGE */
		ptc_log( 'Using phptoolcase helper functions to type less!' ); // PtcDebug::bufferLog( )
		
		
		/* LOGGING SQL QUERIES AND TIMING EXECUTION */
		$sql = 'select from where something';	// some sql query, will be used as reference
		ptc_log_sql( '' , $sql  );				// PtcDebug::bufferSql( )
		$sql_result = array( 'key' => 'value' , 'key1' => 'value1' ); // this should be the sql result of the sql query
		ptc_stop_timer( $sql );				// PtcDebug::stopTimer( )
		ptc_attach( $sql , $sql_result );		// PtcDebug::addToBuffer( )
	
	
		/* WATCHING A VARIABLE */	
		declare(ticks=1)					// declaring code block it is more precise for watching vars
		{
			$var = 'some test';
			ptc_watch( 'var' );				// PtcDebug::watch( )
			$var = 'some new value';			// the variable changed
		}
		
		
		/* STOP CODE COVERAGE ANALYSIS */
		ptc_stop_coverage( );				// PtcDebug::stopCoverage( )
		
		
		/* STOP FUNCTION CALLS TRACING */
		ptc_stop_trace( );					// PtcDebug::stopTrace( )
	
	
	/*** PTC HANDYMAN HELPERPS ****************************************************/
	
		/* ADDING APPLICATION PATHS FOR LATER USAGE ( PtcHandMan::addAppPath( ) ) */
		ptc_add_path( array
		( 
			'lib' => dirname( __FILE__ ) . '/autoloader-example-files' 	// creating an entry in the application paths array
		) );


		/* ADDING CLASS FILES ( PtcHandMan::addFile( ) ) */
		ptc_add_file( array
		(
			'HmTestClassFile' => ptc_path( 'lib' ) . '/class-file.php' , // PtcHandMan::getAppPath( )
			'ns\HmTestClassFile'=> ptc_path( 'lib' ) . '/ns-class-file.php' , // PtcHandMan::getAppPath( )
		) );


		/* ADDING DIRECTORIES WITH CLASSES TO THE AUTOLOADER ( PtcHandMan::addDir( ) ) */
		ptc_add_dir( ptc_path( 'lib' ) );	 					// PtcHandMan::getAppPath(
		
		
		/* ADDING A NAMESPACED DIRECTORY WITH CLASSES TO THE AUTOLOADER */
		ptc_add_dir( array
		( 
			'nsTest' => ptc_path( 'lib' )  . '/namespaceTest'   // PtcHandMan::getAppPath( )
		));
			
		
		/* GETTING THE DIRECTORIES OF THE AUTOLOADER ( PtcHandyMan::getDirs( ) )*/
		$dirs = ptc_dir( );			// PtcHandyMan::getDirs( ) params: ( files , directories , ns )
		ptc_log( $dirs , 'getting all directories and files to be autoloaded' ); //PtcDebug::bufferLog( );
	
