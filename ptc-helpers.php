<?php

	/**
	* PHPTOOLCASE HELPER FUNCTIONS FILE
	* PHP version 5.3
	* @category 	Library
	* @version	0.9.3b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	/*
	| -----------------------------------------------------------------------------------------------
	| DEBUGGER & LOGGER COMPONENT HELPERS
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Writes data to the messages panel. See PtcDebug::bufferLog( )
	* @param 	mixed 		$string		the string to pass
	* @param 	mixed 		$statement		some statement if required
	* @param	string		$category		a category for the messages panel
	*/
	function ptc_log( $string , $statement = null , $category = null ) 
	{	
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 
			'bufferLog' ) , array( $string , $statement , $category ) );
	}
	/**
	* Writes data to the sql panel. See PtcDebug::bufferSql()
	* @param 	mixed 		$string		the string to pass
	* @param 	mixed 		$statement		some statement if required
	* @param	string		$category		a category for the sql panel
	*/
	function ptc_log_sql( $string , $statement = null , $category = null )
	{
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 
			'bufferSql' ) , array( $string , $statement , $category ) );
	}
	/**
	* Monitors the execution of php code, or sql queries based on a reference. See PtcDebug::stopTimer( ) 
	* @param	string			$reference		a reference to look for ("$statement")
	* @param 	string|numeric 	$precision		sec/ms
	*/
	function ptc_stop_timer( $reference = null , $precision = 1 )
	{ 
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ ,
			'stopTimer' ) , array( $reference , $precision ) );
	}
	/**
	* Attaches data to the buffer array based on a reference. See PtcDebug::addToBuffer( )
	* @param	string		$reference		a reference to look for ("$statement")
	* @param	mixed		$string		the message to show
	* @param	string		$statement		a new statement if required
	*/
	function ptc_attach( $reference , $string , $statement = null )
	{
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 
			'addToBuffer' ) , array( $reference , $string , $statement ) );
	}
	/**
	* Watches a variable that is in a declare(ticks=n){ code block }, for changes. See PtcDebug::watch( ) 
	* @param 	string 	$variableName		the name of the variable to watch
	* @param 	string 	$callback			a callback to retrieve the variable
	*/
	function ptc_watch( $variableName , $callback = null ) 
	{ 
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 'watch' ) , array( $variableName , $callback ) );
	}
	/**
	* Starts the code coverage analysis utility to find executed lines. See PtcDebug::startCoverage( )
	*/
	function ptc_start_coverage( )
	{ 
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 'startCoverage' ) ); 
	}
	/**
	* Stop the code coverage analysis utility. See PtcDebug::stopCoverage( )
	*/
	function ptc_stop_coverage( ) 
	{ 
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 'stopCoverage' ) ); 
	}
	/**
	* Starts the function calls trace utility. See PtcDebug::startTrace( )
	*/
	function ptc_start_trace( ) 
	{ 
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 'startTrace' ) ); 
	}
	/**
	* Stop the function calls trace utility. See PtcDebug::stopTrace( )
	*/
	function ptc_stop_trace( ) 
	{ 
		return ptc_run( array( @_PTCDEBUG_NAMESPACE_ , 'stopTrace' ) ); 
	}

	/*
	| -----------------------------------------------------------------------------------------------
	| HANDYMAN COMPONENT HELPERS
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Retrieves the application paths. See PtcHandyMan::getAppPath( )
	* @param	string		$type		the path type
	*/
	function ptc_path( $type = null )
	{
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 'getAppPaths' ) , array( $type ) );
	}
	/**
	* Adds application paths to the PtcHandyMan::$_appPaths array. See PtcHandyMan::addAppPath( )
	* @param	array | string		$paths	the application paths to add	
	*/
	function ptc_add_path( $paths )
	{
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 'addAppPath' ) , array( $paths ) );
	}
	/**
	* Retrieves the directories the autoloader uses to load classes. See PtcHandyMan::getDirs( )
	* @param	string		$type		the directory type
	*/
	function ptc_dir( $type = null )
	{
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 'getDirs' ) , array( $type ) );
	}
	/**
	* Adds directories to the autoloader to load classes. See PtcHandyMan::addDirs( ) 
	* @param	array|string	$directories	the full path to the directories holding the classes
	*/
	function ptc_add_dir( $directories )
	{	
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 'addDirs' ) , array( $directories ) );
	}
	/**
	* Adds files to the class autoloader. See PtcHandyMan::addFiles( )
	* @param	array	$files	the full path to the class file(s)
	*/
	function ptc_add_file( $files )
	{	
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 'addFiles' ) , array( $files ) );
	}
	/**
	* Gets protected and private properties. See PtcHandyMan::getProperty( )
	* @param	mixed	$class			the name or the initialized class object	
	* @param	string	$propertyName	the name of the property
	*/
	function ptc_get_prop( $class , $propertyName )
	{
		return ptc_run( array( @constant( 
			'_PTCHANDYMAN_' ) , 'getProperty' ) , array( $class , $propertyName ) );
	}
	/**
	* Retrieves an element of the given array using dot-notation
	* @param	array	$array			the array where to look in
	* @param	string	$path			the search path
	* @param	string	$defaultValue		default value returned if key is not found
	* @param	string	$delimiter		the delimiter to use
	* @return	the value if the array key is found, the defaultValue otherwise
	*/
	function ptc_array_get( array &$array , $path , $defaultValue = null , $delimiter = '.' )
	{
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 'arrayGet' ) , 
						array( &$array , $path , $defaultValue , $delimiter ) );
	}
	/**
	* Sets an element of the given array using dot-notation. See PtcHandyMan::arraySet( )
	* @param	array	$array		the array where to look in
	* @param	string	$path		the search path
	* @param	string	$value		the value to set
	* @param	string	$force		overwrite value if set already
	* @param	string	$delimiter	the delimiter to use
	* @return	true if value has been set, false if already exists
	*/
	function ptc_array_set( array &$array , $path , $value , $force = false , $delimiter = '.' ) 
	{
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 
			'arraySet' ) , array( &$array , $path , $value , $force , $delimiter ) );
	}
	/**
	* Counts elements of the given array using dot-notation See PtcHandyMan::arrayCount( )
	* @param	array	$array		the array where to look in
	* @param	string	$path		the search path
	* @param	string	$delimiter	the delimiter to use
	* @return		the number of values inside the array element
	*/
	function ptc_array_count( array &$array , $path , $delimiter = '.' )
	{
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 
			'arrayCount' ) , array( &$array , $path , $delimiter ) );
	}
	/**
	* Removes an the element of the given array using dot-notation. See PtcHandyMan::arrayDel( ) 
	* @param	array	$array		the array where to look in
	* @param	string	$path		the search path
	* @param	string	$delimiter	the delimiter to use
	* @return	true if element has been unset, false otherwise
	*/
	function ptc_array_del( array &$array , $path , $delimiter = '.' )
	{
		return ptc_run( array( @constant( '_PTCHANDYMAN_' ) , 
			'arrayCount' ) , array( &$array , $path , $delimiter ) );
	}
	/**
	* Retrieves a session variable. See PtcHandyMan::sessionGet( )
	* @param	string	$path			the session key
	* @param	mixed	$defaultValue		default value to return if key is not found
	* @return	the session key if found, the default value otherwise
	*/
	function ptc_session_get( $path = null , $defaultValue = null ) 
	{
		return ptc_run( array( @constant( 
			'_PTCHANDYMAN_' ) , 'sessionGet' ) , array( $path , $defaultValue ) );
	}
	/**
	* Sets a session variable. See PtcHandyMan::sessionSet( )
	* @param	string	$path	the session key
	* @param	mixed	$value	the value to set
	* @param	mixed	$force	overwrites previous value if set
	*/
	function ptc_session_set( $path , $value , $force = false )
	{
		return ptc_run( array( @constant( 
			'_PTCHANDYMAN_' ) , 'sessionSet' ) , array( $path , $value , $force ) );
	}
	/**
	* Removes a session key. See PtcHandyMan::sessionDel( )
	* @param	string	$path	the session key to remove
	*/
	function ptc_session_del( $path )
	{
		return ptc_run( array( @constant( 
			'_PTCHANDYMAN_' ) , 'sessionDel' ) , array( $path ) );
	}
	/**
	* Interact with the session functions. See PtcHandyMan::session( )
	* @param	string	$type	the name of the function
	*/
	function ptc_session( $type )
	{
		return ptc_run( array( @constant( 
			'_PTCHANDYMAN_' ) , 'session' ) , array( $type ) );
	}
	/**
	* Creates a json or a jsonp response with the passed data. See PtcHandyMan::json( )
	* @param	array	$data		the data to convert to a json
	* @param	mixed	$callback		the name of the callback parameter
	* @param	mixed	$sendHeader	send the response header for the json
	* @return	the jsonp if $callback parameter is set, the json otherwise
	*/
	function ptc_json( array $data , $callback = null , $sendHeader = true )
	{
		return ptc_run( array( @constant( 
			'_PTCHANDYMAN_' ) , 'json' ) , array( $data , $callback , $sendHeader ) );
	}
	
	/*
	| -----------------------------------------------------------------------------------------------
	| EVENT DISPATCHER COMPONENT HELPERS
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Adds a listener to an event. See PtcEvent::listen( )
	* @param	string		$event		the event name, example: "event.sub_event"
	* @param	mixed		$callback		a valid callback ( closure , function , class )
	* @param	numeric		$priority		a numeric value, higher values will execute first
	*/
	function ptc_listen( $event , $callback , $priority = 0 )
	{
		return ptc_run( array( @constant( '_PTCEVENT_' ) , 
			'listen' ) , array( $event , $callback , $priority ) );
	}
	/**
	* Fires an event See PtcEvent::fire( )
	* @param	string		$event	the event name to fire
	* @param	array		$data	an array with the data you wish to pass to the listeners
	*/
	function ptc_fire( $event , $data )
	{
		return ptc_run( array( @constant( '_PTCEVENT_' ) , 'fire' ) , array( $event , $data ) );
	}
	
	/*
	| -----------------------------------------------------------------------------------------------
	| PHPTOOLCASE RUNNER UTILITY
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Runs class methods 
	* @param	string	$callback		a valid callback
	* @param	array	$args		an array with arguments for the callback
	* @return	the result of the call, or false if the callback was not callable
	*/
	function ptc_run( $callback , $args = array( ) )
	{
		if ( @is_callable( $callback ) ){ return call_user_func_array( $callback , $args ); }
		return false;
	}