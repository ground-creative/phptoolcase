<?php

	/**
	* PHPTOOLCASE HELPER FUNCTIONS FILE
	* <br>All helper functions work only if in the same namespace of the library, 
	* with the exception of the PtcDebug class which is only loaded once.
	* PHP version 5.3
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.8.4b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	/** DEBUGGER & LOGGER COMPONENT HELPERS *******************************************/

	/**
	* Writes data to the messages panel
	* @param 	mixed 	$string		the string to pass
	* @param 	mixed 	$statement	some statement if required
	* @param	string	$category	a category for the messages panel
	* @see PtcDebug::bufferLog()
	* @tutorial	PtcDebug.cls#logging.log_msg
	*/
	function ptc_log( $string , $statement = null , $category = null ) 
	{	
		return ptc_run( 'bufferLog' , @_PTCDEBUG_NAMESPACE_ , array( $string , $statement , $category ) );
	}
	/**
	* Writes data to the messages panel
	* @deprecated	use {@link ptc_log()} instead
	*/
	function log_msg($string,$statement=null,$category=null)
	{
		return ptc_run( 'bufferLog' , @_PTCDEBUG_NAMESPACE_ , array( $string , $statement , $category ) );
	}
	/**
	* Writes data to the sql panel
	* @param 	mixed 	$string		the string to pass
	* @param 	mixed 	$statement	some statement if required
	* @param	string	$category	a category for the sql panel
	* @see PtcDebug::bufferSql()
	* @tutorial	PtcDebug.cls#logging.log_sql
	*/
	function ptc_log_sql( $string , $statement = null , $category = null )
	{
		return ptc_run( 'bufferSql' , @_PTCDEBUG_NAMESPACE_ , array( $string , $statement , $category ) );
	}
	/**
	* Writes data to the sql panel
	* @deprecated	use {@link ptc_log_sql()} instead
	*/
	function log_sql($string,$statement=null,$category=null)
	{
		return ptc_run( 'bufferSql' , @_PTCDEBUG_NAMESPACE_ , array( $string , $statement , $category ) );
	}
	/**
	* Monitors the execution of php code, or sql queries based on a reference 
	* @param	string			$reference	a reference to look for ("$statement")
	* @param 	string|numeric 	$precision	sec/ms
	* @see PtcDebug::stopTimer()
	* @tutorial	PtcDebug.cls#stopTimer
	*/
	function ptc_stop_timer( $reference = null , $precision = 1 )
	{ 
		return ptc_run( 'stopTimer' , @_PTCDEBUG_NAMESPACE_ , array( $reference , $precision ) );
	}
	/**
	* Monitors the execution of php code, or sql queries based on a reference 
	* @deprecated	use {@link ptc_stop_timer()} instead
	*/
	function stop_timer( $reference = null , $precision = 1 )
	{ 
		return ptc_run( 'stopTimer' , @_PTCDEBUG_NAMESPACE_ , array( $reference , $precision ) );
	}
	/**
	* Attaches data to the buffer array based on a reference 
	* @param	string	$reference		a reference to look for ("$statement")
	* @param	mixed	$string		the message to show
	* @param	string	$statement		a new statement if required
	* @see PtcDebug::addToBuffer()
	* @tutorial	PtcDebug.cls#addToLog
	*/
	function ptc_attach( $reference , $string , $statement = null )
	{
		return ptc_run( 'addToBuffer' , @_PTCDEBUG_NAMESPACE_ , array( $reference , $string , $statement ) );
	}
	/**
	* Attaches data to the buffer array based on a reference 
	* @deprecated	use {@link ptc_attach()} instead
	*/
	function add_to_log( $reference , $string , $statement = null )
	{
		return ptc_run( 'addToBuffer' , @_PTCDEBUG_NAMESPACE_ , array( $reference , $string , $statement ) );
	}
	/**
	* Watches a variable that is in a declare(ticks=n){ code block }, for changes 
	* @param 	string 	$variableName		the name of the variable to watch
	* @see PtcDebug::watch()
	* @tutorial	PtcDebug.cls#watchVar
	*/
	function ptc_watch( $variableName ) 
	{ 
		return ptc_run( 'watch' , @_PTCDEBUG_NAMESPACE_ , array( $variableName ) );
	}
	/**
	* Watches a variable that is in a declare(ticks=n){ code block }, for changes 
	* @deprecated	use {@link ptc_watch()} instead
	*/
	function watch_var( $variableName ) 
	{ 	
		return ptc_run( 'watch' , @_PTCDEBUG_NAMESPACE_ , array( $variableName ) );
	}
	/*
	* Starts the code coverage analysis utility to find executed lines
	* @see PtcDebug::startCoverage()
	*/
	function ptc_start_coverage( ) { return ptc_run( 'startCoverage' , @_PTCDEBUG_NAMESPACE_ ); }
	/*
	* Stop the code coverage analysis utility
	* @see PtcDebug::stopCoverage()
	*/
	function ptc_stop_coverage( ) { return ptc_run( 'stopCoverage' ,@_PTCDEBUG_NAMESPACE_ ); }
	/*
	* Starts the function calls trace utility
	* @see PtcDebug::starTrace()
	*/
	function ptc_start_trace( ) { return ptc_run( 'startTrace' , @_PTCDEBUG_NAMESPACE_ ); }
	/*
	* Stop the function calls trace utility
	* @see PtcDebug::stopTrace()
	*/
	function ptc_stop_trace( ) { return ptc_run( 'stopTrace' ,@_PTCDEBUG_NAMESPACE_ ); }

	
	/** HANDYMAN COMPONENT HELPERS **************************************************/
	
	/**
	* Retrieves the application paths stored in the $_appPaths array
	* @param	string	$type	the path type
	* @see PtcHandyMan::getAppPath()
	*/
	function ptc_path( $type = null )
	{
		$handyman = '_PTCHANDYMAN_' . @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
		return ptc_run( 'getAppPaths' , @constant( $handyman ) , array( $type ) );
	}
	/**
	* Adds application paths to the $_appPaths array
	* @param	array | string	$paths	the applcation paths to add	
	* @see PtcHandyMan::addAppPath()
	*/
	function ptc_add_path( $paths )
	{
		$handyman = '_PTCHANDYMAN_' . @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
		return ptc_run( 'addAppPath' , @constant( $handyman ) , array( $paths ) );
	}
	/**
	* Retrieves the directories the autoloader uses to load classes
	* @see PtcHandyMan::getDirs()
	* @param	string	$type	the directory type
	*/
	function ptc_dir( $type = null )
	{
		$handyman = '_PTCHANDYMAN_' . @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
		return ptc_run( 'getDirs' , @constant( $handyman ) , array( $type ) );
	}
	/**
	* Adds directories to the autoloader to load classes
	* @see PtcHandyMan::addDirs()
	* @param	array | strng	$directories	the full path to the directories holding the classes
	*/
	function ptc_add_dir( $directories )
	{	
		$handyman = '_PTCHANDYMAN_' . @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
		return ptc_run( 'addDirs' , @constant( $handyman ) , array( $directories ) );
	}
	/**
	* Adds files to the autoloader to load classes
	* @see PtcHandyMan::addFiles()
	* @param	array	$directories	the full path to the directories holding the classes
	*/
	function ptc_add_file( $files )
	{	
		$handyman = '_PTCHANDYMAN_' . @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
		return ptc_run( 'addFiles' , @constant( $handyman ) , array( $files ) );
	}
	
	
	/**** PHPTOOLCASE RUNNER UTILITY ********************************************************/
	
	/**
	* Runs class methods 
	* @param	string	$class		the class name with it's full namespace
	* @param	string	$function		the method to run
	* @param	array	$args		an array with arguments for the method
	* @return	returns  the result of the call , or false if the method is not callable
	*/
	function ptc_run( $function , $class = null , $args = array( ) )
	{
		$call = ( $class ) ? array( '\\' . $class , $function ) : $function;
		if ( @is_callable( $call ) )
		{		
			return call_user_func_array( $call ,  $args  );
		}
		//trigger_error( 'Could not run method "' . $function .'" for class "' . $class . '"' , E_USER_ERROR );
		return false;
	}
