<?php

	/**
	* PHPTOOLCASE SHORTCUT FUNCTIONS
	* @category 	Library
	* @version	v1.1.4
	* @author   	Carlo Pietrobattista <carlo@ground-creative.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	/*
	| -----------------------------------------------------------------------------------------------
	| DEBUGGER & LOGGER COMPONENT HELPERS
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Writes data to the messages panel. See Debug::bufferLog( )
	* @param 	mixed 		$string		the string to pass
	* @param 	mixed 		$statement		some statement if required
	* @param	string		$category		a category for the messages panel
	*/
	function ptc_log( $string , $statement = null , $category = null ) 
	{	
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'bufferLog' ] , [ $string , $statement , $category ] );
	}
	/**
	* Writes data to the sql panel. See Debug::bufferSql()
	* @param 	mixed 		$string		the string to pass
	* @param 	mixed 		$statement	some statement if required
	* @param		string		$category	a category for the sql panel
	*/
	function ptc_log_sql( $string , $statement = null , $category = null )
	{
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'bufferSql' ] , [ $string , $statement , $category ] );
	}
	/**
	* Monitors the execution of php code, or sql queries based on a reference. See Debug::stopTimer( ) 
	* @param		string			$reference	a reference to look for ("$statement")
	* @param 	string|numeric 	$precision	sec/ms
	*/
	function ptc_stop_timer( $reference = null , $precision = 1 )
	{ 
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ ,'stopTimer' ] , [ $reference , $precision ] );
	}
	/**
	* Attaches data to the buffer array based on a reference. See Debug::addToBuffer( )
	* @param	string	$reference	a reference to look for ("$statement")
	* @param	mixed	$string		the message to show
	* @param	string	$statement	a new statement if required
	*/
	function ptc_attach( $reference , $string , $statement = null )
	{
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'addToBuffer' ] , [ $reference , $string , $statement ] );
	}
	/**
	* Watches a variable that is in a declare(ticks=n){ code block }, for changes. See Debug::watch( ) 
	* @param 	string 	$variableName		the name of the variable to watch
	* @param 	string 	$callback			a callback to retrieve the variable
	*/
	function ptc_watch( $variableName , $callback = null ) 
	{ 
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'watch' ] , [ $variableName , $callback ] );
	}
	/**
	* Starts the code coverage analysis utility to find executed lines. See Debug::startCoverage( )
	*/
	function ptc_start_coverage( )
	{ 
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'startCoverage' ] ); 
	}
	/**
	* Stop the code coverage analysis utility. See Debug::stopCoverage( )
	*/
	function ptc_stop_coverage( ) 
	{ 
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'stopCoverage' ] ); 
	}
	/**
	* Starts the function calls trace utility. See Debug::startTrace( )
	*/
	function ptc_start_trace( ) 
	{ 
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'startTrace' ] ); 
	}
	/**
	* Stop the function calls trace utility. See Debug::stopTrace( )
	*/
	function ptc_stop_trace( ) 
	{ 
		return ptc_run( [ @_PTCDEBUG_NAMESPACE_ , 'stopTrace' ] ); 
	}

	/*
	| -----------------------------------------------------------------------------------------------
	| HANDYMAN COMPONENT HELPERS
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Retrieves the application paths. See HandyMan::getAppPath( )
	* @param	string		$type		the path type
	*/
	function ptc_path( $type = null )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'getAppPaths' ] , [ $type ] );
	}
	/**
	* Adds application paths to the HandyMan::$_appPaths array. See HandyMan::addAppPath( )
	* @param		array | string		$paths	the application paths to add	
	*/
	function ptc_add_path( $paths )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'addAppPath' ] , [ $paths ] );
	}
	/**
	* Retrieves the directories the autoloader uses to load classes. See HandyMan::getDirs( )
	* @param		string	$type	the directory type
	*/
	function ptc_dir( $type = null )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'getDirs' ] , [ $type ] );
	}
	/**
	* Adds directories to the autoloader to load classes. See HandyMan::addDirs( ) 
	* @param		array|string	$directories	the full path to the directories holding the classes
	*/
	function ptc_add_dir( $directories )
	{	
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'addDirs' ] , [ $directories ] );
	}
	/**
	* Adds files to the class autoloader. See HandyMan::addFiles( )
	* @param		array	$files	the full path to the class file(s)
	*/
	function ptc_add_file( $files )
	{	
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'addFiles' ] , [ $files ] );
	}
	/**
	* Gets protected and private properties. See HandyMan::getProperty( )
	* @param		mixed	$class			the name or the initialized class object	
	* @param		string	$propertyName	the name of the property
	*/
	function ptc_get_prop( $class , $propertyName )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'getProperty' ] , [ $class , $propertyName ] );
	}
	/**
	* Retrieves an element of the given array using dot-notation
	* @param		array	$array			the array where to look in
	* @param		string	$path			the search path
	* @param		string	$defaultValue		default value returned if key is not found
	* @param		string	$delimiter		the delimiter to use
	* @return	the value if the array key is found, the defaultValue otherwise
	*/
	function ptc_array_get( array &$array , $path , $defaultValue = null , $delimiter = '.' )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'arrayGet' ] , [ &$array , $path , $defaultValue , $delimiter ] );
	}
	/**
	* Sets an element of the given array using dot-notation. See HandyMan::arraySet( )
	* @param		array	$array		the array where to look in
	* @param		string	$path		the search path
	* @param		string	$value		the value to set
	* @param		string	$force		overwrite value if set already
	* @param		string	$delimiter	the delimiter to use
	* @return	true if value has been set, false if already exists
	*/
	function ptc_array_set( array &$array , $path , $value , $force = false , $delimiter = '.' ) 
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'arraySet' ] , [ &$array , $path , $value , $force , $delimiter ] );
	}
	/**
	* Counts elements of the given array using dot-notation See HandyMan::arrayCount( )
	* @param		array	$array		the array where to look in
	* @param		string	$path		the search path
	* @param		string	$delimiter	the delimiter to use
	* @return	the number of values inside the array element
	*/
	function ptc_array_count( array &$array , $path , $delimiter = '.' )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'arrayCount' ] , [ &$array , $path , $delimiter ] );
	}
	/**
	* Removes an the element of the given array using dot-notation. See HandyMan::arrayDel( ) 
	* @param		array	$array		the array where to look in
	* @param		string	$path		the search path
	* @param		string	$delimiter	the delimiter to use
	* @return	true if element has been unset, false otherwise
	*/
	function ptc_array_del( array &$array , $path , $delimiter = '.' )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'arrayDel' ] , [ &$array , $path , $delimiter ] );
	}
	/**
	* Retrieves a session variable. See HandyMan::sessionGet( )
	* @param		string	$path			the session key
	* @param		mixed	$defaultValue		default value to return if key is not found
	* @return	the session key if found, the default value otherwise
	*/
	function ptc_session_get( $path = null , $defaultValue = null ) 
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'sessionGet' ] , [ $path , $defaultValue ] );
	}
	/**
	* Sets a session variable. See HandyMan::sessionSet( )
	* @param		string	$path	the session key
	* @param		mixed	$value	the value to set
	* @param		mixed	$force	overwrites previous value if set
	*/
	function ptc_session_set( $path , $value , $force = false )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'sessionSet' ] , [ $path , $value , $force ] );
	}
	/**
	* Removes a session key. See HandyMan::sessionDel( )
	* @param		string	$path	the session key to remove
	*/
	function ptc_session_del( $path )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'sessionDel' ] , [ $path ] );
	}
	/**
	* Interact with the session functions. See HandyMan::session( )
	* @param		string	$type	the name of the function
	*/
	function ptc_session( $type )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'session' ] , [ $type ] );
	}
	/**
	* Creates a json or a jsonp response with the passed data. See HandyMan::json( )
	* @param		array	$data		the data to convert to a json
	* @param		mixed	$callback		the name of the callback parameter
	* @param		mixed	$sendHeader	send the response header for the json
	* @return	the jsonp if $callback parameter is set, the json otherwise
	*/
	function ptc_json( array $data , $callback = null , $sendHeader = true )
	{
		return ptc_run( [ @constant( '_PTCHANDYMAN_' ) , 'json' ] , [ $data , $callback , $sendHeader ] );
	}
	
	/*
	| -----------------------------------------------------------------------------------------------
	| EVENT DISPATCHER COMPONENT HELPERS
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Adds a listener to an event. See Event::listen( )
	* @param		string		$event		the event name, example: "event.sub_event"
	* @param		mixed		$callback		a valid callback ( closure , function , class )
	* @param		numeric		$priority		a numeric value, higher values will execute first
	*/
	function ptc_listen( $event , $callback , $priority = 0 )
	{
		return ptc_run( [ @constant( '_PTCEVENT_' ) , 'listen' ] , [ $event , $callback , $priority ] );
	}
	/**
	* Fires an event See Event::fire( )
	* @param		string	$event	the event name to fire
	* @param		array	$data	an array with the data you wish to pass to the listeners
	*/
	function ptc_fire( $event , $data )
	{
		return ptc_run( [ @constant( '_PTCEVENT_' ) , 'fire' ] , [ $event , $data ] );
	}
	
	/*
	| -----------------------------------------------------------------------------------------------
	| PHPTOOLCASE RUNNER UTILITY
	| -----------------------------------------------------------------------------------------------
	*/
	
	/**
	* Runs class methods 
	* @param		string	$callback		a valid callback
	* @param		array	$args		an array with arguments for the callback
	* @return	the result of the call, or false if the callback was not callable
	*/
	function ptc_run( $callback , $args = array( ) )
	{
		if ( @is_callable( $callback ) ){ return call_user_func_array( $callback , $args ); }
		return false;
	}