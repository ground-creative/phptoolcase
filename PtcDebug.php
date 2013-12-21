<?php

	/**
	* DEBUGGER & LOGGER CLASS
	* <br>All class properties and methods are static because it's required 
	* to let them work on script shutdown when FATAL error occurs.
	* PHP version 5.3
	* @category 	Library
	* @version	0.9.2
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	//declare( ticks = 1 ); // used by the watch var , function calls trace and code coverage utilities

	class PtcDebug
	{
		/**
		* Returns the buffer array
		* @return	the buffer array
		*/
		public static function getBuffer( ) { return static::$_buffer; }	
		/** 
		* Retrieves the code coverage analysis data stored in the PtcDebug::$_finalCoverageData property
		*/
		public static function getCoverage( ) { return static::$_finalCoverageData; }
		/**
		* Adds style properties to the floating panel styles array
		* @param	string		$css		some css to add
		*/
		public static function addCss( $css ){ static::$_panelCss = static::$_panelCss . "\n" . $css; }
		/**
		* Checks if  the debug "url_key" and "url_pass" are set on the referer url. See @ref check_referer
		* @return	true if "url_key" and "url_pass" are in the referer url, otherwise false
		*/
		public static function checkReferer( )
		{
			if ( @array_key_exists( 'HTTP_REFERER' , @$_SERVER ) )
			{ 
				$query = parse_url( $_SERVER[ 'HTTP_REFERER' ] , PHP_URL_QUERY );
				$params = array( );
				parse_str( $query , $params );
				if ( @$params[ static::$_options[ 'url_key' ] ] == static::$_options[ 'url_pass' ] )
				{
					$_GET[ static::$_options[ 'url_key' ] ] = $params[ static::$_options[ 'url_key' ] ];
					return true;
				}
			}
			return false;
		}													
		/**
		* Sets the error handler to be the debug class. good for production with "$dieOnFatal" set to false.
		* See @ref replaceErrorHandler
		* @param	string		$dieOnFatal		die if fatal error occurs
		*/
		public static function setErrorHandler( $dieOnFatal = true )
		{
			ini_set( 'display_errors' , false );
			ini_set( 'html_errors' , false );	
			if ( !@static::$_options[ 'error_reporting' ] )
			{
				@static::$_options[ 'error_reporting' ] = static::$_defaultOptions[ 'error_reporting' ];
			}			
			ini_set( 'error_reporting' , static::$_options[ 'error_reporting' ] );
			@static::$_options[ 'die_on_error' ] = $dieOnFatal;
			set_error_handler( array( get_called_class( ) , 'errorHandler' ) ); 
		}
		/**
		* Loads the debug interface and/or the console class if requested. See @ref dbg_getting_started
		* @param 	array 		$options		array of options, see PtcDebug::$ _defaultOptions
		*/
		public static function load( $options = null )
		{
			$now = microtime( true );
			if ( defined( '_PTCDEBUG_NAMESPACE_' ) )	// check if the debug class is already loaded
			{
				$err = array( 'errno' => static::_msgType( E_USER_NOTICE ),
							'errstr' => 'Debug already loaded!','errfile' => 'trace' );
				static::_buildBuffer( 'log' , '{errorHandler}' , $err );
				return; 
			}
			$called_class = get_called_class( );
			/* if error handler was called previously */
			if ( @isset( static::$_options[ 'die_on_error' ] ) )
			{ 
				static::$_defaultOptions[ 'die_on_error' ] = static::$_options[ 'die_on_error' ]; 
			}
			if ( @isset( static::$_options[ 'error_reporting' ] ) )
			{ 
				static::$_defaultOptions[ 'error_reporting' ] = static::$_options[ 'error_reporting' ]; 
			}
			static::$_options = ( is_array( $options ) ) ? 
				array_merge( static::$_defaultOptions , $options ) : static::$_defaultOptions;
			if ( !$has_access = static::_checkAccess( ) ){ return; }		// check access with ips
			$buffer = 'Debug Info:';
			if ( static::$_options[ 'check_referer' ] ){ static::checkReferer( ); }// check if referer has debug vars
			if ( static::$_options[ 'session_start' ] )					// start session on request
			{
				if ( session_id( ) === '' ) 					// check if session is already active
				{ 
					session_start( ); 
					$buffer .= '<br>Initialized browser session with session_start( )';
				}
				else{ $buffer .= '<br>Session id is ' . session_id( ); }
			}
			if ( !@$_SESSION ){ $_SESSION = array( ); }
			if ( !@$_SESSION[ 'ptcdebug' ] ){ $_SESSION[ 'ptcdebug' ] = array( ); }
			if ( @$_GET[ static::$_options[ 'url_key' ] ] == static::$_options[ 'url_pass' ] )
			{
				$_SESSION[ 'ptcdebug' ][ static::$_options[ 'url_key' ] ] = true;
				$_SESSION[ 'ptcdebug' ][ 'code_highlighter' ]	= true;
				$_SESSION[ 'ptcdebug' ][ 'search_files' ] = true;
				//$buffer .= '<br>PtcDebug turned on!';
			}
			else if ( @$_GET[ static::$_options[ 'url_key' ] . '_off' ] == static::$_options[ 'url_pass' ] )
			{
				$_SESSION[ 'ptcdebug' ][ static::$_options[ 'url_key' ] ] = false;
				$_SESSION[ 'ptcdebug' ][ 'code_highlighter' ]	= false;
				$_SESSION[ 'ptcdebug' ][ 'search_files' ] = false;
			}
			if ( static::_getSessionVars( static::$_options[ 'url_key' ] ) )
			{ 
				static::$_startTime = microtime( true );
				if ( static::$_options[ 'set_time_limit' ] )
				{ 
					set_time_limit( static::$_options[ 'set_time_limit' ] ); 
				}
				if ( static::$_options[ 'memory_limit' ] )
				{ 
					ini_set( 'memory_limit' , static::$_options[ 'memory_limit' ] ); 
				}
				if ( static::$_options[  'show_interface' ] || static::$_options[ 'debug_console' ] )
				{	
					register_shutdown_function( array( $called_class , 'processBuffer' ) ); 
				}
				if ( static::$_options[ 'replace_error_handler' ] )	// replace error handler
				{
					static::setErrorHandler( static::$_options[ 'die_on_error' ] );
					$buffer .= '<br>Error handler has been overridden!';
				}
				if ( static::$_options[ 'catch_exceptions' ] )	// set exception handler
				{
					set_exception_handler( array( $called_class , 'exceptionHandler' ) );
					$buffer .= "<br>Exception Handler turned on!";
				}
				if ( static::$_options[ 'debug_console' ] )	// try to laod the console class
				{
					static::$_consoleStarted = false;
					$buffer.='<br>Console debug turned on';
					if ( file_exists( dirname( __FILE__ ) . '/PhpConsole/__autoload.php' ) )
					{
						require_once( dirname(__FILE__).'/PhpConsole/__autoload.php' );
						static::$_consoleStarted = true;
						\PhpConsole\Helper::register( );
						$buffer .= ", phpConsole class started!";
					}
					else{ $buffer .= ', but could not find phpConsole class!'; }
				}
				if ( static::$_options[ 'enable_inspector' ] || static::$_options[ 'code_coverage' ] || 
													static::$_options[ 'trace_functions' ] )
				{ 
					register_tick_function( array( $called_class , 'tickHandler' ) ); 
					//if ( static::$_options[ 'declare_ticks' ] ) { declare( ticks = 1 ); }
					$buffer .= "<br>Variables inspector enabled!";
				}
				if ( static::$_options[ 'code_coverage' ] === 'full' ) 
				{ 
					static::startCoverage( );
					$buffer .= "<br>Code coverage analysis for all scripts enabled!";					
				}
				if ( static::$_options[ 'trace_functions' ] === 'full' ) 
				{ 
					static::startTrace( );
					$buffer .= "<br>Function calls tracing for all scripts enabled!";					
				}
				if ( !static::_getSessionVars( 'show_messages' ) ){ static::_setSessionVars( ); }
				if ( @$_GET[ 'hidepanels' ] ){ static::_disablePanels( ); }
				else
				{
					static::$_options[ 'show_messages' ] = static::_getSessionVars( 'show_messages' );
					static::$_options[ 'show_globals' ] = static::_getSessionVars( 'show_globals' );
					static::$_options[ 'show_sql' ] = static::_getSessionVars( 'show_sql' );
					static::$_options[ 'show_w3c' ] = static::_getSessionVars( 'show_w3c' );
				}
				@define( '_PTCDEBUG_NAMESPACE_' , $called_class ); 
				static::$_tickTime = ( ( microtime( true ) - $now ) + static::$_tickTime );
				static::bufferLog( '' , '<span>' . $buffer . '<span>' , 'Debug Loader' );
			}
		}
		/**
		* The ticks handler to execute all tickable functions
		*/
		public static function tickHandler( )
		{
			//$now = microtime( true );
			if ( static::$_codeCoverage || static::$_functionTrace ) { $bt = debug_backtrace( ); }
			if ( static::$_disableOpcode ) // try to disable opcode cache
			{ 
				static::_disableOpcodeCache( );
				static::$_disableOpcode = false;
			}
			if ( static::$_options[ 'enable_inspector' ] && count( static::$_watchedVars ) ) 
			{ 
				static::_watchCallback( ); 
			}
			if ( static::$_codeCoverage ) { static::_codeCoverageAnalysis( $bt ); }
			if ( static::$_functionTrace ) { static::_traceFunctionCalls( $bt ); }
			//if ( static::$_options[ 'profiler' ] ) { }
			unset( $bt );
			// FIXME: the timer goes to minus here
			//static::$_tickTime = ( ( microtime( true ) - $now ) + static::$_tickTime ); 
		}
		/**
		* Starts the code coverage analysis utility to find executed lines. See @ref codeCoverage
		*/
		public static function startCoverage( )
		{
			if ( @static::$_options[ 'code_coverage' ] )
			{
				if ( static::$_codeCoverage && static::$_options[ 'code_coverage' ] !== 'full' )
				{
					static::bufferLog( 'Coverage already started, please use stopCoverage( ) 
										before starting a new one!', '' , 'Debugger Notice' );
					return false;
				}
				static::$_codeCoverage = true;
			}
		}
		/**
		* Stops the code coverage analysis utility. See @ref codeCoverage
		*/
		public static function stopCoverage( )
		{
			if ( static::$_options[ 'code_coverage' ] !== 'full' )
			{
				static::$_codeCoverage = false;
				if( static::$_coverageData )
				{
					static::$_finalCoverageData[ ] = static::$_coverageData;
					static::$_coverageData = null;
				}
			}
		}
		/**
		* Starts the function calls trace utility. See @ref traceFunctions
		*/
		public static function startTrace( )
		{
			if ( @static::$_options[ 'trace_functions' ] )
			{
				if ( static::$_functionTrace && static::$_options[ 'trace_functions' ] !== 'full' )
				{
					static::bufferLog( 'Function calls tracing has been already started, please use stopTrace( ) 
											before starting a new one!' , '' , 'Debugger Notice' );
					return false;
				}
				static::$_functionTrace = true;
			}
		}
		/**
		* Stops the function calls trace utility. See @ref traceFunctions
		*/
		public static function stopTrace( )
		{
			if ( static::$_options[ 'trace_functions' ] !== 'full' )
			{
				static::$_functionTrace = false;
				if ( static::$_traceData )
				{
					static::$_finalTraceData[ ] = static::$_traceData;
					static::$_traceData = null;
				}
			}		
		}
		/**
		* Excludes functions from the function calls tracing engine
		* @param	array | string		$functions	the function the exclude by their name	
		*/
		public static function excludeFromTrace( $functions )
		{
			$functions = ( is_array( $functions) ) ? $functions : array( $functions );
			static::$_excludeFromTrace = array_merge( static::$_excludeFromTrace , $functions );
		}
		/**
		* Watches a variable that is in a declare(ticks=n); code block, for changes. See @ref variableInspector 
		* @param 	string 	$variableName	the name of the variable to watch
		* @param 	string 	$callback		a callback function that retrieves the variable
		*/
		public static function watch( $variableName , $callback = null )
		{
			if ( @static::$_options[ 'enable_inspector' ] )
			{
				$var = ( $callback ) ? array( 'value' => $callback( ) , 'callback' => $callback ) : 
														static::_findWatchVar( $variableName );
				static::$_watchedVars[ $variableName ] = $var;
				$value = ( $callback ) ? call_user_func( $callback ) : static::$_watchedVars[ $variableName ];		
				static::bufferLog( $value , 'Watching variable <span style="font-weight:bold;">$' . 
														$variableName . '</span> = ' , 'Inspector' );
			}
			else
			{
				$err = array( 'errno' => static::_msgType( E_USER_NOTICE ) , 'errfile' => 'trace' , 
					'errstr' => 'Please set to true [\'enable_inspector\'] option to be able to watch a variable' );
				static::_buildBuffer( 'log' , '{errorHandler}' , $err );
			}
		}
		/**
		* Writes data to the messages panel. See @ref logging_data
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement		some statement if required
		* @param	string		$category		a category for the messages panel
		*/
		public static function bufferLog( $string , $statement = null , $category = null )
		{ 
			static::_buildBuffer( 'log' , $string , $statement , $category );
		}
		/**
		* Writes data to the sql panel. See @ref log_sql
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement		some statement if required
		* @param	string		$category		a category for the sql panel
		*/
		public static function bufferSql( $string , $statement = null , $category = null )
		{ 
			static::_buildBuffer( 'sql' , $string , $statement , $category ); 
		}
		/**
		* Monitors the execution of php code, or sql queries based on a reference. See @ref execution_timing
		* @param	string			$reference	a reference to look for ("$statement")
		* @param 	string|numeric 	$precision	sec/ms
		* @return	true if a given reference is found, otherwise false
		*/
		public static function stopTimer( $reference = null , $precision = 1 )
		{
			$now = microtime( true );
			$last = static::_findReference( $reference , 1 );
			if ( !$last ){ return false; }
			$time = ( $now - @$last[ 'data' ][ 'start_time' ] );
			switch( $precision )
			{
				case 0 :		// seconds
				case 'sec' :	// seconds
					static::$_buffer[ $last[ 'key' ] ][ 'time' ] = round( $time , 3 ) . ' sec';
				break;
				case 1 :		// millisecons
				case 'ms' :		// millisecons
				default :
					static::$_buffer[ $last[ 'key' ] ][ 'time' ] = round( $time * 1000 , 3 ) . ' ms';
				break;
			}
			if ( static::$_options[ 'debug_console' ] )
			{
				static::$_buffer[ $last[ 'key' ] ][ 'console_time' ] = 
							static::$_buffer[ $last[ 'key' ] ][ 'time' ];
			}
			return true;
		}
		/**
		* Convert memory_usage( ) into a readable format
		* @param	float		$val			The value to convert
		* @param	int		$precision		the decimal points
		*/
		public static function convertMemUsage( $val , $precision = 2)
		{
			$ram = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
			return $usage = ( $val ) ? @round( $val / pow( 1024 , 
					($i = floor( log( $val , 1024) ) ) ) , $precision ) . $ram[ $i ] : '0 Bytes';
		}
		/**
		* Handles php errors. See @ref replaceErrorHandler
		* @param 	string 	$errno	error number (php standards)
		* @param 	string 	$errstr	error string
		* @param 	string 	$errfile	error file
		* @param 	string 	$errline	error line
		@return	true to prevent php default error handler to fire
		*/
		public static function errorHandler( $errno , $errstr , $errfile , $errline ) 
		{
			if ( error_reporting( ) == 0 ){ return; }	// if error has been supressed with an @
			$err = array( 'errno' => static::_msgType( $errno ) , 'errstr' => $errstr ,
										'errfile' => $errfile , 'errline' => $errline );
			static::_buildBuffer( 'log' , '{errorHandler}' , $err );
			// stop if fatal error occurs
			if ( static::$_options[ 'die_on_error' ] && static::_msgType( $errno ) == 'Php Error') { die( ); }
			return true;	// don't execute php error handler
		}
		/**
		* Exception handler, catches exceptions that are not in a try/catch block
		* @param 	object 	$exception		the exception object
		*/
		public static function exceptionHandler( $exception )
		{
			$err = array( 'errno' => static::_msgType( 'exception' ) , 'errstr' => $exception->getMessage( ) ,
								'errfile' => $exception->getFile( ) , 'errline' => $exception->getLine( ) );
			static::_buildBuffer( 'log' , '{errorHandler}' , $err );
		}
		/**
		* Attaches a message to the end of the buffer array to add data based on a reference. See @ref add_to_log 
		* @param	string		$reference		a reference to look for ("$statement")
		* @param	mixed		$string		the message to show
		* @param	string		$statement		a new statement if required
		* @return	true if the given reference is found, false otherwise
		*/
		public static function addToBuffer( $reference , $string , $statement = null )
		{
			$raw_buffer = static::_findReference( $reference , 2 );
			if ( !$raw_buffer ){ return false; }
			$last = $raw_buffer[ 'data' ];
			if ( @$string )
			{	
				$last[ 'var_type' ] = gettype( $string );
				$last[ 'errstr' ] = $string; 
			}
			if ( $statement ){ $last[ 'errmsg' ] = $statement; }
			if ( static::$_options[ 'debug_console' ] )
			{
				$last[ 'console_string' ] = ( !@$string ) ? $last[ 'errstr' ] : $string;
				$last[ 'console_statement' ] = ( !@$statement ) ? $last[ 'errmsg' ] : $statement;
			}
			@static::$_buffer[ $raw_buffer[ 'key' ] ] = $last;
			return true;
		}
		/**
		* Processes the buffer to show the interface and/or the console messages
		*/
		public static function processBuffer( )
		{
			@unregister_tick_function( 'tickHandler' );
			static::$_countTime = false;
			if ( static::$_codeCoverage ) 
			{ 
				$trace_o = static::$_options[ 'code_coverage' ];
				static::$_options[ 'code_coverage' ] = true;
				static::stopCoverage( );
				static::$_options[ 'code_coverage' ] = $trace_o;
			}
			if ( static::$_functionTrace ) 
			{
				$trace_o = static::$_options[ 'trace_functions' ];
				static::$_options[ 'trace_functions' ] = true;
				static::stopTrace( ); 
				static::$_options[ 'trace_functions' ] = $trace_o;
			}
			static::$_endTime = microtime( true );
			if( static::$_consoleStarted ){ static::_debugConsole(); }
			if( static::$_options[ 'show_interface' ] )
			{
				static::_lastError( );					// get last php fatal error
				$interface = static::_buildInterface( );	// build the interface
				print $interface;
			}
		}
		/**
		* Searches files for a string inside a given folder recursively. See @ref search_string
		* @param	string		$query	the string to lok for
		* @param	string		$path		a starting path to search recursively
		* @param	int		$last		check last result
		* @return	an html table with all results
		*/
		public static function findString( $query , $path = null , $last = 1 )
		{
			$path = ( $path ) ? $path : dirname(__FILE__);
			$real_path = realpath( $path );
			if ( !$real_path )	// need to trigger error if path is not found
			{
				trigger_error( 'The path "' . $path . 
					'" does not exists or is not accessible!' , E_USER_WARNING );
				return false;
			}
			$fp = @opendir( $real_path );
			if ( !$fp )		// need to trigger error if path is not found
			{
				trigger_error( 'The path "' . $path .
					'" does not exists or is not accessible!' , E_USER_WARNING );
				return false;
			}
			while ( $f = readdir( $fp ) )
			{
				if ( preg_match( '#^\.+$#' , $f ) ){ continue; } // ignore symbolic links
				$file_full_path = $real_path . DIRECTORY_SEPARATOR . $f;
				if ( is_dir( $file_full_path ) )
				{ 
					@$result .=  static::findString( $query , $file_full_path , $last ); 
				} 
				else
				{
					$file_lines = @file( $file_full_path );
					if ( $file_lines )
					{
					$line_number = 1; 
					foreach ( $file_lines as $line )
					{ 
						$search_count = substr_count( $line , $query ); 
						if ( $search_count > 0 ) // we found matches 
						{ 
							$line = preg_replace( '|' . $query . '|', 
							'<span style="color:yellow;font-weight:bold;">' . $query . 
														'</span>' , htmlentities( $line ) );
							@$result .= '<tr>';
							$result .= '<td style="white-space: nowrap;"># ' . $last . '</td>';
							$result .= '<td><div style="color:blue;">' . $file_full_path . '</div></td>';
							$result .= '<td><div style="font-weight:bold;color:black;">' . 
														$line_number . '</div></td>';
							$result .= '<td><div style="color:darkred;font-weight:bold;"">' . $line . '</div></td>';
							$result .= '<td><div style="font-weight:bold;color:red;">
														' . $search_count . '</div></td>';
							$result .= '</tr>';
							$last++;
						} 
						$line_number++; 
					}}
				}	
			}
			return @$result;
		}
		/**
		* File highlighter that opens a popup window inspect source code. See @ref file_inspector
		* @param 	string 	$file		the full path for the file
		* @param 	string 	$line		the line to be highlighted
		* @return	the html output of the source code
		*/
		public static function highlightFile( $file , $line = null )
		{
			$lines = implode( range( 1 , count( file ( $file ) ) ) , '<br />' ); 
			$content = highlight_file( $file , true ); 
			if ( $line )
			{
				$line = $line - 1;
				$l = explode( '<br />' , $content );
				$l[ $line ] = '<div id="line" style="display:inline;background-color:yellow;">' . $l[ $line ] . '</div>';
				$content = implode( '<br />' , $l );
			}
			$html = ' 
			    <style type="text/css"> 
				.num{float:left;color:gray;font-size:13px;
				font-family:monospace;text-align:right;margin-right:6pt;
				padding-right:6pt;border-right:1px solid gray;} 
				body{margin:0px;margin-left:5px;}
				td{vertical-align:top;}code{white-space:nowrap;} 
			    </style>
			    <script>
				window.onload=function() 
				{
					var SupportDiv = document.getElementById("line");
					window.scroll(0,findPos(SupportDiv));
					return false;
				};	
				function findPos(obj)
				{
					var curtop=0;
					if(obj.offsetParent)
					{
						do{
							curtop+=(obj.offsetTop-40);
						} while (obj = obj.offsetParent);
						return [curtop];
					}
				};
			</script>';
			$html .= "<table><tr><td class=\"num\">\n$lines\n</td>";
			$html .= "<td>\n$content\n</td></tr></table>"; 
			return $html;
		}
		/**
		* Default options for the debug class. See @ref dbg_class_options
		*/
		protected static $_defaultOptions = array
		(
			'url_key'			=>	'debug' , // the key to pass to the url to turn on debug
			'url_pass'			=>	'true' , // the pass to turn on debug
			'replace_error_handler'	=>	true , // replace default php error handler
			'error_reporting'		=>	E_ALL , // error reporting flag
			'catch_exceptions'	=>	true , // sets exception handler to be this class method
			'check_referer'		=>   	false , // check referer for key and pass ( good for ajax debugging )
			'die_on_error'		=>	true , // die if fatal error occurs ( with this class error handler )
			'debug_console'		=>	false , // only for Chrome,show messages in console ( phpConsole needed )
			'allowed_ips'		=>	null , // restrict access with ip's
			'session_start'		=>	false , // start session for persistent debugging
			'show_interface'		=>	true , // show the interface ( false to debug in console only )
			'set_time_limit'		=>	null , // set php execution time limit
			'memory_limit'		=>	null , // set php memory size	
			'show_messages'		=>	true , // show messages panel
			'show_globals'		=>	true , // show global variables in vars panel
			'show_sql'			=>	true , // show sql panel
			'show_w3c'			=>	true, // show the w3c panel
			'minified_html'		=>	true , // compress html for a lighter output
			'trace_depth'		=>	10 , // maximum depth for the backtrace
			'max_dump_depth'	=>	6 , // maximum depth for the dump function	
			'panel_top'			=>	'0px' , // panel top position
			'panel_right'		=>	'0px' , // panel right position
			'default_category'	=>	'General' , // default category for the messages
			'enable_inspector'	=>	true , // enable variables inspector, use declare(ticks=n); in code block
			'code_coverage'		=>	true, // enable code coverage analysis, use "full" to start globally
			'trace_functions'		=>	true, // enable function calls tracing, use "full" to start globally
			'exclude_categories'	=>	array( 'Event Manager' , 'Autoloader' ) // exclude categories from the output
		);
		/**
		* Array of methods excluded from the backtrace
		*/
		protected static $_excludeMethods=array( );
		/**
		* Code coverage analysis storage
		*/
		protected static $_coverageData = null;
		/**
		* Final data array for the code coveage
		*/
		protected static $_finalCoverageData = array( );
		/**
		* Function calls tracing storage property
		*/
		protected static $_traceData = null;
		/**
		* Final data array for the function calls trace
		*/
		protected static $_finalTraceData = array( );
		/**
		* Array with all options
		*/
		protected static $_options = array( );
		/**
		* The debug buffer
		*/	
		protected static $_buffer = array( );
		/**
		* Application start time
		*/
		protected static $_startTime = null;		
		/**
		* Application end time
		*/
		protected static $_endTime = null;
		/**
		* Decides if we should send the buffer to the PhpConsole class
		*/
		protected static $_consoleStarted = false;
		/**
		* Array of watched variables declared
		*/
		protected static $_watchedVars = array();
		/**
		* Tick execution time property
		*/
		protected static $_tickTime = 0;
		/**
		* Exclude PtcDebug::$_buildBuffer from execution timing property
		*/	
		protected static $_countTime = true;
		/**
		* Code coverage analysis property to start coverage
		*/
		protected static $_codeCoverage = false;
		/**
		* Function calls trace property to start the analysis
		*/
		protected static $_functionTrace = false;
		/**
		* Controlls when to disable opcode cache
		*/
		protected static $_disableOpcode = true;
		/**
		* Exclude functions from the function calls trace array 
		*/
		protected static $_excludeFromTrace = array( );
		/**
		* Property that holds the css for the floating panel
		*/
		protected static $_panelCss = '#ptcDebugPanel{font-family:Arial,sant-serif;
				position:fixed;top:{PANEL_TOP};right:{PANEL_RIGHT};
				background:#eee;color:#333;z-index:10000;line-height:1.3em;
				text-align:left;padding:0px;margin:0px;height:25px;}
				#ptcDebugPanel ul.tabs li{background-color:#ddd;border-color:#999;margin:0 -3px -1px 0;
				padding:3px 6px;border-width:1px;list-style:none;display:inline-block;border-style:solid;}
				#ptcDebugPanel ul.tabs li.active{background-color:#fff;border-bottom-color:transparent;
				text-decoration:}#ptcDebugPanel ul.tabs li:hover{background-color:#eee;}
				#ptcDebugPanel ul.tabs li.active:hover{background-color:#fff;}
				#ptcDebugPanel ul.tabs.merge-up{margin-top:-24px;}
				#ptcDebugPanel ul.tabs.right{padding:0 0 0 0;text-align:right;}
				#ptcDebugPanel ul.tabs{border-bottom-color:#999;border-bottom-width:1px;font-size:14px;
				list-style:none;margin:0;padding:0;z-index:100000;position:relative;
				background-color:#EEE}#ptcDebugPanel ul.tabs a{color:purple;font-size:10px;
				text-decoration:none;}#ptcDebugPanel .tabs a:hover{color:red;}
				#ptcDebugPanel ul.tabs a.active{color:black;background-color:yellow;}
				.msgTable{padding:0;margin:0;border:1px solid #999;font-family:Arial;
				font-size:11px;text-align:left;border-collapse:separate;border-spacing:2px;}
				.msgTable th{margin:0;border:0;padding:3px 5px;vertical-align:top;
				background-color:#999;color:#EEE;white-space:nowrap;}
				.msgTable td{margin:0;border:0;padding:3px 3px 3px 3px;vertical-align:top;}
				.msgTable tr td{background-color:#ddd;color:#333}
				.msgTable tr.php-notice td{background-color:lightblue;}
				.msgTable tr.exception td{background-color:greenyellow;}
				.msgTable tr.php-warning td{background-color:yellow;}
				.msgTable tr.php-error td{background-color:orange;}
				.msgTable tr.inspector td{background-color:lightgreen;}
				.innerTable a.php-notice{color:lightblue;}
				.innerTable a.exception{color:greenyellow;}.innerTable a.php-warning{color:yellow;}
				.innerTable a.php-error{color:orange;}.innerTable a.inspector{color:lightgreen;}
				.innerTable a.general{color:darkgrey;}.innerTable a.show-all{color:red;}
				#ptcDebugFilterBar{background-color:black;margin-bottom:8px;padding:4px;
				font-size:13px;}.innerTable{z-index:10000;position:relative;background:#eee;
				height:300px;padding:30px 10px 0 10px;overflow:auto;clear:both;}
				.innerTable a{color:dodgerBlue;font-size:bold;text-decoration:none}
				.innerTable p{font-size:12px;color:#333;text-align:left;line-height:12px;}
				.innerPanel h1{font-size:16px;font-weight:bold;margin-bottom:20px;
				padding:0;border:0px;background-color:#EEE;}
				#ptcDebugPanelTitle{height:25px;float:left;z-index:1000000;position:relative;}
				#ptcDebugPanelTitle h1{font-size:16px;font-weight:bold;margin-bottom:20px;
				margin-left:10px;padding:0 0 0 0;border:0px;background-color:#EEE;
				color:#669;margin-top:5px;;height:20px;}
				#analysisPanel h2{font-size:14px;font-weight:bold;margin-bottom:20px;
				padding:0 0 0 0;border:0px;background-color:#EEE;
				color:#669;margin-top:5px;;height:20px;}
				.vars-config, .vars-config span{font-weight:bold;}
				.msgTable pre span, .vars-config span{padding:2px;}
				.msgTable pre, span, .vars{font-size:11px;line-height:15px;
				font-family:"andale mono","monotype.com","courier new",courier,monospace;}
				.msgTable pre,.msgTable span{font-weight:bold;}
				#varsPanel a{text-decoration:none;font-size:14px;font-weight:bold;color:#669;
				line-height:25px;}.count_vars{font-size:11px;color:purple;padding:0;margin:0;}
				.fixed{width:1%;white-space:nowrap;}.fixed1{width:5%;white-space:nowrap;}
				#ptcDebugStatusBar{height:2px;background-color:#999;}' ;
		/**
		* Sends the buffer to the PhpConsole class. See @ref ajax_env
		*/
		protected static function _debugConsole()
		{
			$handler = \PhpConsole\Handler::getInstance( );
			$handler->setHandleErrors( false );
			$handler->setHandleExceptions( false );
			$handler->start( );
			foreach ( static::$_buffer as $k => $arr )
			{
				if ( @$arr[ 'console_string' ] || @$arr[ 'console_statement' ] )
				{
					if ( !@$arr )
					{
						$php_trace = static::_debugTrace( 1 );
						$arr=array( 'errline' => $php_trace[ 'line' ] , 'errfile' => $php_trace[ 'file' ] ); 
					}
					$statement = ( @$arr[ 'console_statement' ] ) ? 
							strip_tags( preg_replace( "=<br */?>=i" , "\n" , 
									@$arr[ 'console_statement' ] ) ) : null;
					$statement .= ( @$arr[ 'console_time' ] ) ? ' [time: ' . $arr[ 'console_time' ] . ']' : '';
					$console_type = '[' . @end( @explode( '/' , $arr[ 'errfile' ][ 0 ] ) ) . ':';
					$console_type .= $arr[ 'errline' ][ 0 ] . ']';
					$key=(@$arr['type']=='log') ? 'messages' : 'sql';
					if(static::$_options['show_'.$key])
					{ 
						if ( 'error' === $arr['console_statement'] )
						{
							$handler->handleError( $arr[ 'console_category' ] , $arr[ 'errstr' ] , 
											$arr[ 'errfile' ][ 0 ] , $arr[ 'errline' ][ 0 ] , null , 2 );
						}
						else
						{
							\PC::debug( $console_type , $arr[ 'console_category' ] . '.file' ); 
							if ( $statement )
							{ 
								\PC::debug( $statement , $arr[ 'console_category' ] . '.message' ); 
							}
							if ( @$arr[ 'console_string' ] )
							{ 	
								\PC::debug( $arr[ 'console_string' ] , $arr[ 'console_category' ] . '.result' ); 
							}
							if ( @$arr[ 'errfile' ] )
							{
								unset( $arr[ 'errfile' ][ 0 ] );
								if ( !empty( $arr[ 'errfile' ] ) )
								{ 
									\PC::debug( $arr[ 'errfile' ] , $arr[ 'console_category' ] . '.trace' ); 
								}
							}
							//\PC::debug( $arr , $arr[ 'console_category' ] . '[full]' ); 
						}
					}
				}
			}
			if ( !static::$_options[ 'show_interface' ] )
			{
				static::_buildCoverageData( );
				static::_buildTraceData( ); 
			}
			$time = ( ( static::$_endTime - static::$_startTime ) - static::$_tickTime );
			$console_final = 'Seconds: ' . round( $time , 3 ) . ' | Milliseconds: ' . round( $time * 1000 , 3 );
			\PC::debug( array( @get_included_files( ) ) , static::$_options[ 'default_category' ] . '.includedFiles' );
			\PC::debug( 'Global Execution Time ' . $console_final , static::$_options[ 'default_category' ] );
		}
		/**
		* Checks if a given ip has access
		* @param 	string|array		$allowedIps		the ip's that are allowed
		*/
		protected static function _checkAccess($allowedIps=null)
		{
			static::$_options['allowed_ips']=(!$allowedIps) ? static::$_options['allowed_ips'] : $allowedIps;
			if(static::$_options['allowed_ips'])
			{
				static::$_options['allowed_ips']=(is_array(static::$_options['allowed_ips'])) ? 
									static::$_options['allowed_ips'] : array(static::$_options['allowed_ips']);
				if(@in_array(@$_SERVER['REMOTE_ADDR'],static::$_options['allowed_ips'])){ return true; }
				return false;
			}
			return true;
		}
		/**
		* Sets session vars to control which panels will be shown
		*/
		protected static function _setSessionVars()
		{
			$_SESSION[ 'ptcdebug' ]['show_messages']=static::$_options['show_messages'];
			$_SESSION[ 'ptcdebug' ]['show_globals']=static::$_options['show_globals'];
			$_SESSION[ 'ptcdebug' ]['show_sql']=static::$_options['show_sql'];
			$_SESSION[ 'ptcdebug' ]['show_w3c']=static::$_options['show_w3c'];
		}
		/**
		* Controls which panels will be shown with $_GET variable "hidepanels"
		*/
		protected static function _disablePanels( )
		{
			$hide = @explode( ',' , $_GET[ 'hidepanels' ] );
			if ( !@empty( $hide ) )
			{
				$_SESSION[ 'ptcdebug' ][ 'show_messages' ] = true;
				$_SESSION[ 'ptcdebug' ][ 'show_globals' ] = true;
				$_SESSION[ 'ptcdebug' ][ 'show_sql' ] = true;
				$_SESSION[ 'ptcdebug' ][ 'show_w3c' ] = true;
				foreach ( $hide as $k => $v )
				{
					if ( $v == 'msg' || $v == 'all' )
					{ 
						$_SESSION[ 'ptcdebug' ][ 'show_messages' ] = false; 
					}
					if ( $v == 'globals' || $v == 'all' )
					{ 
						$_SESSION[ 'ptcdebug' ][ 'show_globals' ] = false; 
					}
					if ( $v == 'sql' || $v == 'all' )
					{ 
						$_SESSION[ 'ptcdebug' ][ 'show_sql' ] = false; 
					}
					if ( $v == 'w3c' || $v == 'all' )
					{ 
						$_SESSION[ 'ptcdebug' ][ 'show_w3c' ] = false; 
					}
				}
			}			
			static::$_options[ 'show_messages' ] = static::_getSessionVars( 'show_messages' );
			static::$_options[ 'show_globals' ] = static::_getSessionVars( 'show_globals' );
			static::$_options[ 'show_sql' ] = static::_getSessionVars( 'show_sql' );
			static::$_options[ 'show_w3c' ] = static::_getSessionVars( 'show_w3c' );
		}
		/**
		* Builds the buffer
		* @param 	string		$type			log/sql
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement		some statement preceding the string
		* @param	string		$category		a category for the message
		*/
		protected static function _buildBuffer( $type , $string , $statement = null , $category = null )
		{
			if ( @in_array( $category , static::$_options[ 'exclude_categories' ] ) ){ return; }
			if ( defined( '_PTCDEBUG_NAMESPACE_' ) && 
				@static::_getSessionVars( static::$_options[ 'url_key' ] ) && 
					( static::$_options[ 'show_interface' ] || static::$_options[ 'debug_console' ] ) ) 
			{
				$buffer = array( 'start_time' => microtime( true ) , 'type' => $type );
				$php_trace = static::_debugTrace( static::$_options[ 'trace_depth' ] );
				$buffer[ 'errline' ] = @$php_trace[ 'line' ];
				$buffer[ 'errfile' ] = @$php_trace[ 'file' ];
				$buffer[ 'function' ] = @$php_trace[ 'function' ];
				$buffer[ 'class' ] = @$php_trace[ 'class' ];
				if ( $string === '{errorHandler}' )
				{
					$buffer[ 'errno' ] = $statement[ 'errno' ];
					$buffer[ 'errstr' ] = $statement[ 'errstr' ];
					if ( $statement[ 'errfile' ] == 'trace' )
					{
						$params = @explode( ':' , @str_replace( ':\\' , '{win-patch}' , 
												@$buffer[ 'errfile' ][ 0 ] ) ); // windows patch
						@$buffer[ 'errfile' ][ 0 ] = @str_replace( '{win-patch}' , ':\\' , @$params[ 0 ] );
					}
					else	// if static::errorHandler() called the function
					{
						if ( !@is_array( $buffer[ 'errline' ] ) ){ $buffer[ 'errline' ] = array( ); }
						if ( !@is_array( $buffer[ 'errfile' ] ) ){ $buffer[ 'errfile' ] = array( ); }
						if ( !@is_array( $buffer[ 'function' ] ) ){ $buffer[ 'function' ] = array( ); }
						if ( !@is_array( $buffer[ 'class' ] ) ){ $buffer[ 'class' ] = array( ); }
						@array_unshift( $buffer[ 'errline' ] , $statement[ 'errline' ] );
						@array_unshift( $buffer[ 'errfile' ] , $statement[ 'errfile' ] );
						@array_unshift( $buffer[ 'function' ] , '' );
						@array_unshift( $buffer[ 'class' ] , '' );
					}
					if ( static::$_options[ 'debug_console' ] )
					{
						//var_dump($buffer  );
						$buffer[ 'console_string' ] = $buffer;
						$buffer[ 'console_statement' ] = 'error';
						$buffer[ 'console_category' ] = $statement[ 'errno' ];
					}
				}
				else
				{
					$params = @explode( ':' , @str_replace( ':\\' , '{win-patch}' , 
												@$buffer[ 'errfile' ][ 0 ] ) ); // windows patch
					@$buffer[ 'errfile' ][ 0 ] = @str_replace( '{win-patch}' , ':\\' , @$params[ 0 ] );
					$buffer[ 'var_type' ] = gettype( $string );
					if ( !$category ){ $category = static::$_options[ 'default_category' ]; } 
					$buffer[ 'errno' ] = $category;
					$buffer[ 'errstr' ] = $string;
					$buffer[ 'errmsg' ] = $statement;						
					if ( static::$_options[ 'debug_console' ] )
					{
						$buffer[ 'console_string' ] = $string;
						$buffer[ 'console_statement' ] = $statement;
						$buffer[ 'console_category' ] = $category;
					}
				}
				@static::$_buffer[ ] = $buffer;
				if ( static::$_countTime )
				{ 					
					static::$_tickTime = ( ( microtime( true ) - $buffer[ 'start_time' ] ) + static::$_tickTime );
				}
			}
		}
		/**
		* Callback function that checks if a given variable has changed
		*/
		protected static function _watchCallback( )
		{
			if ( count( static::$_watchedVars ) ) 
			{
				foreach ( static::$_watchedVars as $variableName => $variableValue ) 
				{
					if ( is_array( $variableValue ) )
					{
						$var = $variableValue[ 'callback' ]( );
						if ( @$var !== @$variableValue[ 'value' ] ) 
						{
							$info=array
							(
								'variable'			=>	'$' . $variableName ,
								'previous_value'	=>	$variableValue[ 'value' ] ,
								'new_value'		=>	$var
							);			
							static::$_watchedVars[ $variableName ] = 
								array( 'value' => $var , 'callback' => $variableValue[ 'callback' ] );
						}
					}
					else
					{
						$var = static::_findWatchVar( $variableName );
						if ( @$var !== @$variableValue ) 
						{
							$info=array
							(
								'variable'			=>	'$' . $variableName ,
								'previous_value'	=> 	$variableValue ,
								'new_value'		=>	$var
							);			
							static::$_watchedVars[ $variableName ] = $var;
						}
					}
				}
				if ( @$info )
				{ 
					static::bufferLog( $info ,' Watched variable changed  <span style="font-weight:bold;">$'.
															$variableName . '</span> = ','Inspector'); 
				}
			}
		}
		/**
		* Collect data for code coverage analysis
		* @param	array		$backtrace	the debug_backtrace( )
		*/
		protected static function _codeCoverageAnalysis( $backtrace = null)
		{
			$backtrace = ( !$backtrace ) ? 
				debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) : $backtrace;
			$backtrace = array_reverse( $backtrace );
			foreach ( $backtrace as $k => $v )
			{
				if ( @$v[ 'file' ] ==  __FILE__ || 
						@strpos( $v[ 'file' ] ,  'runtime-created function' ) ) { continue; }
				if ( @$v[ 'line' ] && !@array_key_exists( @$v[ 'line' ] , 
							@static::$_coverageData[ $v[ 'file' ] ] ) )
				{
					if ( !@array_key_exists( $v[ 'file' ] , static::$_coverageData ) )
					{
						static::$_coverageData[ $v[ 'file' ] ] = array( );
					}
					static::$_coverageData[ $v[ 'file' ] ][ $v[ 'line' ] ] = 1;
				}
			}
		}
		/**
		* Evaluates the type of variable for output
		* @param 	mixed 		$var	the variable to pass
		* @return	the html output with the variable content
		*/
		protected static function _formatVar($var)
		{	
			if(is_array($var) || is_object($var)){ $html_string=static::_doDump($var); }
			else if(@is_bool($var))
			{ 
				$html_string='<span style="color:#92008d;">'.($var==1 ? 'TRUE' : 'FALSE').'</span>'; 
			}
			else if(@is_null($var)){ $html_strisng='<span style="color:black;">NULL</span>'; }
			else if(@is_float($var)){ $html_string='<span style="color:#10C500;">'.$var.'</span>'; }
			else if(is_int($var)){ $html_string='<span style="color:red;">'.$var.'</span>'; }
			// could be a string
			else{ $html_string='<span>'.static::_cleanBuffer(@print_r($var,true)).'</span>'; }
			return @$html_string;
		}
		/**
		* Retrieves the variable to watch from the "$GLOBALS"
		* @param 	string 	$variableName		the name of the variable to find
		* @return	the watched variable if found, otherwise null
		*/
		protected static function _findWatchVar($variableName)
		{
			$pieces=preg_split('/(->|\[.*?\])/',$variableName,null,PREG_SPLIT_DELIM_CAPTURE);
			$pieces=array_filter($pieces);
			$skip_key=null;
			$watch='$GLOBALS[\''.$pieces[0].'\']';
			foreach($pieces as $k=>$v)
			{
				if($k>0 && $k!=$skip_key && $v)
				{
					if($m=preg_match('/->/',$v) && @$pieces[$k+1])
					{
						$skip_key=($k+1);
						$watch=$watch.'->'.$pieces[$k+1];
					}
					else if($y=preg_match('/\[(.*?)\]/',$v,$match,PREG_OFFSET_CAPTURE))
					{ 
						$watch=$watch.$v;
					}
				}
			}
			return $var=@eval('return @'.$watch.';');
		}
		/**
		* Finds a value in the buffer based on a reference (the "$statement")
		* @param 	string		$reference	the reference to look for
		* @param	numeric	$type		"1" to time execution, "2" to attach data to a message
		* @return	the array if the given reference is found in the buffer
		*/
		protected static function _findReference($reference,$type=1)
		{
			switch($type)
			{
				case 1:$msg='Could not find reference "'.$reference.'" to time execution!';
				break;
				case 2:$msg='Could not find reference "'.$reference.'" to add data to!';
				break;
			}
			for($i=0;$i<@count(static::$_buffer);$i++)
			{
				if($reference==@static::$_buffer[$i]['errmsg'])
				{
					$last['data']=static::$_buffer[$i];
					$last['key']=$i;
				}
			}
			if(!@$last)
			{
				$err=array('errno'=>static::_msgType(E_USER_WARNING),
										'errstr'=>$msg,'errfile'=>'trace');
				static::_buildBuffer('log','{errorHandler}',$err);
				return false; 
			}
			return $last;
		}
		/**
		* Custom dump to properly format a given variable and make it more friendly to read
		* @param 	mixed 		$var			the string to pass
		* @param 	mixed 		$varName		some statement preceding the variable
		* @param 	string 	$indent		uses "|" as indents by default
		* @param 	string 	$reference		a reference to prevent recursion
		* @param 	int 		$depth		maximun depth
		* @return	the html output with the variable
		*/
		protected static function _doDump( &$var , $varName = NULL , $indent = NULL , $reference = NULL , $depth = 0 )
		{
			$span_color='color:grey;';
			$do_dump_indent='<span style="color:white;"> | &nbsp;</span>';
			$reference=$reference.$varName;
			$keyvar='recursion_protection_scheme'; 
			$keyname='referenced_object_name';
			if((is_array($var) && isset($var[$keyvar])) || is_object($var) && property_exists($var,$keyvar))
			{
				if(is_array($var))
				{
					$real_var=&$var[$keyvar];
					$real_name=&$var[$keyname];
					$type=ucfirst(gettype($real_var));
					$result=$indent.$varName.' <span style="'.$span_color.'">'.$type.
						'</span> => **RECURSION** <span style="color:#e87800;">&amp;'.
															$real_name.'</span><br>';
				}
				else // if it's object
				{
					$real_var=&$var;
					$real_name=get_class($var);
					//$real_name=$real_name.'->'.$reference;
					$type=ucfirst(gettype($real_var));
					$varName='<span>'.$varName.'</span>';
					$result=$indent.$varName.' => <span style="'.$span_color.'">'.
								$type.'</span>(<span style="color:#e87800;">&amp;'.
											$real_name.'</span>) **RECURSION** <br>';
				}
				return $result;
			}
			else
			{
				$id="ptc-debug-".rand();
				$var=array($keyvar=>$var,$keyname=>$reference);
				$avar=&$var[$keyvar];
				$type=ucfirst(gettype($avar));
				switch($type)
				{
					case 'String':$type_color='<span style="color:#CF3F33;">';break;
					case 'Integer':$type_color='<span style="color:red;">';break;
					case 'Double':$type_color='<span style="color:#10C500;">'; $type='Float';break;
					case 'Boolean':$type_color='<span style="color:#92008d;">';break;
					case 'NULL':$type_color='<span style="color:black;">';break;
					default:$type_color='<span>';
				}
				if(is_array($avar))
				{
					$count=count($avar);
					@$result.=$indent.($varName ? '<span>' . $varName . '</span> => ' : '</span>');
					if(!empty($avar))
					{
						$depth=($depth+1);
						$result.='<a href="#" onclick="ptc_show_vars(\''.$id.'\',this);return false;"><span>'.
															$type.'('.$count.')&dArr;</span></a>';
						$result.='<div style="display:none;" id="'.$id.'">'.$indent.'<span> (</span><br>';
						$keys=array_keys($avar);
						if ( $depth < static::$_options[ 'max_dump_depth' ] )
						{
							foreach ( $keys as $name )
							{
								if ( $name !== 'GLOBALS' ) // avoid globals for recursion nightmares
								{
									$value = &$avar[ $name ];
									$name=static::_cleanBuffer($name);
									$result.=static::_doDump($value,'<span style="color:#CF7F18;">[\''.
										$name.'\']</span>',$indent.$do_dump_indent,$reference,$depth);
								}
							}
						}
						else
						{
							$result.=$indent.$do_dump_indent.$varName.' <span style="'.
									$span_color.'">'.$type=ucfirst(gettype($var[$keyvar])).
										'</span> => **MAX DEPTH REACHED** <span style="color:#e87800;">'.
																		$var[$keyname].'</span><br>';
						}
						$result.=$indent.'<span> )</span></div><div><!-- --></div>';
					}
					else{ $result.='<span style="'.$span_color.'">'.$type.'('.$count.')</span></br>'; }
				}
				else if ( is_object( $avar ) )
				{
					$rf = @new \ReflectionFunction( $avar );	
					if ( ( @$rf->getName( ) == '{closure}' ) ) // work with lambda functions first
					{
						$result .= $indent . ( $varName ? $varName . ' => ' : '');
						$result .= '<span>**RUNTIME CREATED FUNCTION** ';
						if ( @$rf->getFileName( ) ) { $result .= @$rf->getFileName( ); } 
						if ( @$rf->getStartLine( ) ) { $result .= ':' . @$rf->getStartLine( ); } 
						if ( @$rf->getStartLine( ) ) { $result .= '-' . @$rf->getEndline( ); }
						$result .= '</span><br>'; 
					}
					else
					{
						@$avar->recursion_protection_scheme = "recursion_protection_scheme";
						$depth = ( $depth + 1 );
						@$result .= $indent . ( $varName ? $varName . ' => ' : '');
						$result .= '<a href="#" onclick="ptc_show_vars(\''.$id.'\',this);return false;">';
						$result .= '<span>' . $type . '(' . get_class( $avar ) . ')&dArr;</span></a>'.
							'<div style="display:none;" id="' . $id . '">' . $indent . ' <span> ( </span><br>';
						if ( $depth < static::$_options[ 'max_dump_depth' ] )
						{
							// public properties
							$class_properties = array( );
							foreach ( $avar as $name => $value )
							{					
								$name = static::_cleanBuffer( $name );
								$name=is_object($value) ? '<span>'.$name.'</span>' : $name;
								$result .= static::_doDump( $value , $name ,$indent . 
														$do_dump_indent , $reference,$depth );
								$class_properties[ ] = $name;
							}
							// protected/private properties
							$class = @new \ReflectionClass( $avar );
							$properties = $class->getProperties( );
							foreach ( $properties as $property ) 
							{
								$name = $property->getName( );
								if ( $property->isPrivate( ) ) { $name = $name . ':private'; }
								else if ( $property->isProtected( ) ) { $name = $name.':protected'; }
								if ( $property->isStatic( ) ) { $name = $name . ':static'; }
								$property->setAccessible( true );
								$value = $property->getValue( $avar );
								if(!in_array($name,$class_properties))
								{
									$name=static::_cleanBuffer($name);
									$name=is_object($value) ? '<span>'.$name.'</span>' : $name;
									$result.=static::_doDump($value,$name,$indent.$do_dump_indent,$reference,$depth);
								}
							}
							$methods=$class->getMethods();
							if($methods)
							{
								$class_methods=array();
								$z=0;
								foreach($methods as $method)
								{ 
									$name=$method->getName();
									if($method->isPrivate()){ $name=$name.':private'; }
									else if($method->isProtected()){ $name=$name.':protected'; }
									if($method->isStatic()){ $name=$name.':static'; }
									$class_methods[$z]=$name;
									$z++;
								}
								$result.=static::_doDump($class_methods,
									'<span style="color:#CF7F18;">[**class_methods:'.get_class($avar).
													'**]</span>', $indent.$do_dump_indent,$reference);
							}
						}
						else
						{
								$result.=$indent.$do_dump_indent.$varName.
									' <span style="'.$span_color.'">'.$type=ucfirst(gettype($var[$keyvar])).
									'</span> => **MAX DEPTH REACHED** <span style="color:#e87800;">'.
																		$var[$keyname].'</span><br>';
						}
						$result.=$indent.'<span> ) </span></div><div><!-- --></div>';
						unset($avar->recursion_protection_scheme);
					}
				}
				else
				{
					if($varName=="recursion_protection_scheme"){ return; }
					@$result .= $indent . '<span>' . $varName . '</span> => <span style="'.$span_color.'">';
					if(is_string($avar) && (strlen($avar)>50))
					{ 
						$result.='<a href="#" onclick="ptc_show_string(\''.$id.
									'\',this);return false;" style="font-weight:bold;">'; 
					}
					$result.=$type.'(';
					if ( is_bool( $avar ) )
					{
						$result .= strlen( $avar ) . ')</span> ' . $type_color . ( $avar == 1 ? "TRUE" : "FALSE" ) . 
																					'</span><br>';
					}
					else if ( is_null( $avar ) ) { $result .= strlen( $avar ) . ')</span> ' . $type_color . 'NULL</span><br>'; }
					else if ( is_string( $avar ) )
					{
						$avar = trim( static::_cleanBuffer( $avar ) );
						$string = ( strlen( $avar ) > 50 ) ? substr( $avar , 0 , 47 ) . '...' : $avar;
						$string = '<span id="' . $id . '-span">\'' . $string . '\'</span>';
						$result .= strlen ( $avar ) . ') ';
						$result .= ( strlen( $avar ) > 50 ) ? '&dArr;</span></a>' : '</span>';
						$result .= $type_color . $string . '</span>';
						if ( strlen( $avar ) > 50 )
						{ 
							$result.='<div style="display:none;" id="'.$id.'">'.$type_color.'\''.$avar.'\'</div>'; 
						}
						$result .= '<br>';
					}
					else // could be a float, an integer or undefined
					{										
						//$avar=static::_cleanBuffer($avar);
						$result .= @strlen( $avar ) . ')</span> ' . $type_color . $avar . '</span><br>';
					}
				}
				$var = @$var[ $keyvar ];
			}
			//$var=@$var[$keyvar];			
			return $result;
		}
		/**
		* Sorts the buffer
		* @return	the sorted buffer array
		*/
		protected static function _sortBuffer()
		{
			if(@static::$_buffer)
			{
				foreach(static::$_buffer as $k=>$arr)
				{
					$type=$arr['type'];
					//unset($arr['type']);
					$buffer[$type][]=$arr;
				}
				return @static::$_buffer=$buffer;
			}
		}
		/**
		* Trace php as best as we can
		* @param	int	$depth	the maximum trace depth
		* @return	the trace without the methods in the PtcDebug::$_excludeMethods property
		*/
		protected static function _debugTrace( $depth = NULL )
		{										
			if ( !$depth ) { $depth = static::$_options[ 'trace_depth' ]; }
			if ( version_compare( PHP_VERSION, '5.3.6' ) < 0 ) 
			{
				$raw_trace = debug_backtrace( false );
			}
			else { $raw_trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ); }
			//$this_methods = get_class_methods( get_called_class( ) );
			foreach ( $raw_trace as $k => $arr )
			{
				if((@$arr['class']=='PtcDebug' && (@preg_match("|_|",@$arr['function']) || 
								@in_array(@$arr['function'],static::$_excludeMethods))) || 
							@preg_match("|__|",@$arr['function'])){ unset($raw_trace[$k]); }
			}
			if(!empty($raw_trace))
			{
				$raw_trace=@array_values($raw_trace);
				$raw_trace=@array_reverse($raw_trace);					
				if($depth>count($raw_trace)){ $depth=count($raw_trace); }
				for($i=0;$i<$depth;$i++)
				{ 
					if(@$raw_trace[$i]['line'] && @$raw_trace[$i]['file'])
					{
						$php_trace['file'][]=$raw_trace[$i]['file'].':'.$raw_trace[$i]['line'];
						$php_trace['line'][]=$raw_trace[$i]['line'];
						$php_trace['class'][]=@$raw_trace[$i]['class'];
						//$php_trace['function'][]=$raw_trace[$i]['function'];
						$php_trace['function'][]=(@$raw_trace[$i]['function']) ? 
												$raw_trace[$i]['function'].'()' : null;
					}
				}
				unset( $raw_trace );
			}
			else{ $php_trace=null; }
			return @$php_trace;
		}
		/**
		* Gets the fatal error on shutdown
		*/
		protected static function _lastError()
		{
			if(static::$_options['replace_error_handler'] && $error=error_get_last()) 
			{
				$err_type=static::_msgType( $error['type'] );
				if($err_type=='Php Error')
				{				   
					$err=array('errno'=>$err_type,'errstr'=>$error['message'],
								'errfile'=>$error['file'],'errline'=>$error['line']);		
					static::_buildBuffer('log','{errorHandler}',$err);
				}
			}
		}
		/**
		* Builds the debug interface
		* @return	the html with interface
		*/
		protected static function _buildInterface( )
		{
			static::_sortBuffer( );
			$interface = static::_includeJs( );					// include js
			$interface .= static::_includeCss( );					// include css
			$interface .= '<div id="ptcDebugPanel">';
			$interface .= '<div id="ptcDebugPanelTitle" style="display:none;">&nbsp;</div>';
			$interface .= static::_buildMenu( );					// top menu
			$interface .= static::_buildMsgPanel( 'log' , 'msgPanel' );	// msgs
			$interface .= static::_buildMsgPanel( 'sql' , 'sqlPanel' );		// sql
			$interface .= static::_buildAnalysisPanel( );				// Analysis
			$interface .= static::_buildVarsPanel( );				// vars
			$interface .= static::_buildW3cPanel( );				// w3c
			$interface .= static::_buildTimerPanel( );				// timer
			$interface .= '<div id="ptcDebugStatusBar" style="display:none;">&nbsp;</div>';
			$interface .= '</div>';
			//$interface = static::_compressHtml( $interface );	// make html lighter
			return $interface;
		}
		/**
		* Builds the debug menu
		* @return	the html menu compressed
		*/
		protected static function _buildMenu( )
		{
			$ul = '<ul class="tabs right" id="floatingTab">';
			//$ul .= '<li><a href="#" onClick="minimize();return false;">>></a></li>';
			$num_msg = @count( static::$_buffer[ 'log' ] );
			$ul .= '<li>' . static::_menuLinks( 'msgPanel' , 'log & messages ( ' . $num_msg . ' ) ' , 
												'messages ( ' . $num_msg . ' ) ' ) . '</li>';
			$num_msg = @count( static::$_buffer[ 'sql' ] );
			$ul .= '<li>' . static::_menuLinks( 'sqlPanel' , 'mysql query messages ( ' . $num_msg . ' ) ' , 
														'sql ( ' . $num_msg . ' ) ' ) . '</li>';
			$num_msg = (@count( static::$_finalCoverageData ) + @count( static::$_finalTraceData ) );
			$ul .= '<li>' . static::_menuLinks( 'analysisPanel' , 'analysis ( ' . $num_msg . ' ) ' , 
													'analysis ( ' . $num_msg . ' ) ' ) . '</li>';
			$ul .= '<li>' . static::_menuLinks( 'varsPanel' , 'configuration & environment variables' , 
													'vars & config' ) . '</li>';
			$ul .= '<li>' . static::_menuLinks( 'w3cPanel' , 'W3C validator' , 'w3c' ) . '</a></li>';
			$ul .='<li>' . static::_menuLinks( 'timerPanel' , 'execution time monitor' , 'timer' ) . '</li>';
			$ul .= '<li><a href="#" onClick="hideInterface();return false;">X</a></li>';
			$ul .= '</ul> ';
			return $ul = static::_compressHtml( $ul );
		}
		/**
		* Builds the menu links
		* @param	string		$id		the panel id
		* @param	string		$title		the panel title
		* @param	string		$text		the text for the link
		* @return	the html anchor tag
		*/
		protected static function _menuLinks( $id , $title , $text )
		{
			$title = ucwords( $title );
			$text = strtoupper( $text );
			$return = '<a href="#" onClick="ptc_show_panel(\'' . $id . '\',\'' . 
										$title . '\',this);return false;">';
			return $return .= $text . '</a>';
		}
		/**
		* Checks message types
		* @param	string|numeric	$msg		php standards
		* @return	the message type as a readable string
		*/
		protected static function _msgType( $msg = NULL )
		{
			switch ( $msg )
			{
				case @E_NOTICE:
				case @E_USER_NOTICE:
				case @E_DEPRECATED:
				case @E_USER_DEPRECATED:
				case @E_STRICT:
					return 'Php Notice';
				break;
				case @E_WARNING:
				case @E_USER_WARNING:
				case @E_CORE_WARNING:
				case @E_COMPILE_WARNING:
					return 'Php Warning';
				break;
				case @E_ERROR:
				case @E_PARSE;
				case @E_RECOVERABLE_ERROR:
				case @E_USER_ERROR:
				case @E_CORE_ERROR:
				case @E_COMPILE_ERROR:
					return 'Php Error';
				break;
				case 'exception' : return 'Exception';
				break;
				default: return 'General';
			}
		}
		/**
		* Builds the html log and sql tables
		* @param	string		$type		sql|log
		* @return	the html table data
		*/
		protected static function _buildHtmlTable( $type )
		{
			$div = null;
			if ( @static::$_buffer[ $type ] )
			{
				$categories = array( );
				foreach ( static::$_buffer[ $type ] as $k => $arr )
				{
					if ( @$arr[ 'errno' ] )
					{			
						if ( !array_key_exists( $arr[ 'errno' ] , $categories ) )
						{ 	
							$categories[ $arr[ 'errno' ] ] = 1; 
						}
						else
						{ 
							$categories[ $arr[ 'errno' ] ] = ( $categories[ $arr[ 'errno' ] ] + 1 ); 
						}
					}
				}
				if ( sizeof( $categories ) > 1 )
				{
					ksort( $categories );
					$div .= '<div id="ptcDebugFilterBar"><a href="#" onClick="ptc_filter_categories(\'' . 
									$type . 'Table\',\'showAll\')" class="show-all">Show All</a> | ';
					foreach ( $categories as $k => $v )
					{ 
						$cat_id = str_replace( ' ' , '-' , strtolower( $k ) );
						$div .= '<a href="#" onClick="ptc_filter_categories(\'' . $type . 
							'Table\',\'' . $cat_id . '\')" class="' . $cat_id . '">' . $k . "(" . $v . ")</a> | "; 
					}
					$div = substr( $div , 0 , -3 );
					$div .= '</div>';
				}
				$a = 1;
				$div .= '<table border="1" style="width:100%" class="msgTable" id="' . $type . 'Table"><tr>';
				$div .= '<th>#</th><th>category</th><th>file</th><th>line</th>';
				$div .= '<th>class</th><th>function</th>';
				if ( $type == 'log' ){ $div .= '<th>type</th>'; }
				$div .= '<th>time</th><th>message</th></tr>';
				foreach ( static::$_buffer[ $type ] as $k => $arr )
				{
					$msg_class = @str_replace( ' ' , '-' , $arr[ 'errno' ] );
					$div .= '<tr class="' . strtolower( $msg_class ) . '"><td class="fixed"># ' . $a . '</td>';
					//$div.='<td style="'.static:: _errorMsgStyle($arr['errno']).'">'.$arr['errno'].'</td>';
					$div .= '<td class="fixed"><span style="color:green;">'.@$arr['errno'].'</span></td>';
					$div .= '<td class="fixed">';
					$div .= @static::_buildTraceLink( @$arr[ 'errfile' ][ 0 ] , @$arr[ 'errline' ][ 0 ] );
					$div .= '<span>' . @end( @explode( DIRECTORY_SEPARATOR , $arr[ 'errfile' ][ 0 ] ) ).'</span></a>';
					if ( count( @$arr[ 'errfile' ] ) > 1 )
					{
						$class = 'ptc-debug-class-' . rand( );
						$div .= ' <a href="#" onclick="ptc_show_trace(\'' . $class . 
									'\',this);return false;"><span>' . '&dArr;</span></a>';
					}
					@array_shift( $arr[ 'errfile' ] );
					if ( !empty( $arr[ 'errfile' ] ) )
					{
						$indent = '<span style="color:black;">| &nbsp;</span>';
						foreach ( $arr[ 'errfile' ] as $k => $file )
						{
							$div .= '<div class="' . $class . '" style="display:none;">';
							if ( $file || @$arr[ 'errfile' ][ $k + 1 ] ){ $div .= $indent; }
							$params = @explode( ':' , str_replace( ':\\' , '{win-patch}' , $file ) ); // windows patch;
							@$params[ 0 ] = @str_replace( '{win-patch}' , ':\\' , @$params[ 0 ] );
							$div .= @static::_buildTraceLink( $params[ 0 ] , $params[ 1 ] );
							$div .= @end( @explode( DIRECTORY_SEPARATOR , $file ) ) . '</a></div>';		
							$indent = $indent . '<span style="color:black;">| &nbsp;</span>';							
						}
					}
					$div .= '</td>';
					$div .= '<td class="fixed">' . @static::_buildTraceTree( @$arr[ 'errline' ] , $class , 'black' ) . '</td>';
					$div .= '<td class="fixed">' . @static::_buildTraceTree( @$arr[ 'class' ] , $class , 'purple' ) . '</td>';
					$div.='<td class="fixed">' . @static::_buildTraceTree( @$arr[ 'function' ] , $class , 'darkred' ) . '</td>';
					if ( $type == 'log' )
					{
						$div .= '<td class="fixed">';
						switch ( @$arr[ 'var_type' ] )
						{
							case 'boolean' : $color = 'color:#92008d;'; break;
							case 'NULL' : $color = 'color:black;'; break;
							case 'integer' : $color = 'color:red;';  break;
							case 'double' : $color = 'color:#10C500;'; break;
							case 'array' : $color = 'color:blue'; break;
							case 'object' : $color = 'color:#CF3F33'; break;
							//case 'string': $color='color:#CF3F33'; break;
							default : $color = '';
						}
						$div .= '<span style="' . $color . '">' . @$arr[ 'var_type' ] . '</span>';
						$div .= '</td>';
					}
					$div .= ( @$arr[ 'time' ] ) ? '<td class="fixed"><span style="color:blue;font-weight:normal;"">'.
											$arr[ 'time' ] . '</span></td>' : '<td class="fixed">&nbsp;</td>';	
					$errors = array( 'php-warning' , 'php-notice' , 'php-error' , 'exception' );											
					$err_style = ( !in_array( strtolower( $msg_class ) , $errors ) ) ? 'font-weight:normal;' : 'color:darkred;';			
					$div .= '<td><span style="' . $err_style . '">';
					if ( @$arr[ 'errmsg' ] ){ $div .= @$arr[ 'errmsg' ] . ' '; }
					$div .= static::_formatVar( @$arr[ 'errstr' ] );
					$div .= '</span></td></tr>';
					$a++;
				}
				$div .= "</table><br>";
			}
			else{ $div = '<span class="vars">no messages</span>'; }
			return $div;
		}
		/**
		* Builds the link for the code highlighter popup
		* @param	string		$file		the full path to the file
		* @param	string		$line		the line to be highlighted
		*/
		protected static function _buildTraceLink( $file , $line = null )
		{
			$html = '<a href="#" onclick="';
			if ( session_id( ) !== '' && static::_getSessionVars( 'code_highlighter' ) )
			{
				$js_file = @addslashes( @str_replace( $document_root , '' , $file ) );
				$html .= 'ptc_read_code(\'' . addslashes( $file ) . '\',\'' . $line . '\');return false;" title="' . @$file . '">';
			}
			else
			{ 
				$html .= 'return false;"';
				$html .= ' title="' . @$file . ' '."\n\n".'** USE SESSION_START(), IF YOU WISH';
				$html .= ' TO ACTIVATE THE CODE POPUP HIGHLIGHTER **" style="cursor:text;">'; 
			}	
			return $html;
		}
		/**
		* Builds the tree for the links in the vars & config panel
		* @param	mixed		$var			the variable
		* @param	string		$className		a css class
		* @param	string		$styleColor		the color for 
		*/
		protected static function _buildTraceTree( $var , $className = null , $styleColor = null )
		{
			$indent = '';
			foreach ( $var as $k => $v )
			{
				if ( $k > 0 )
				{ 
					$display = 'display:none;'; 
					$class = ' class="' . $className . '"';
				}
				@$html .= '<div style="font-weight:bold;color:' . $styleColor . ';' . 
												@$display . '"' . @$class . '>';

				if ( $v || @$var[ $k + 1 ] ) { $html .= $indent; }

				if( !$v ) { $v = '&nbsp;'; }
				$html .= $v . '</div>';
				$indent = $indent . '<span style="color:black;">| &nbsp;</span>';
			}
			return $html;
		}
		/**
		* Builds the log/sql panel
		* @param	$type		log or sql
		* @param	$panelId	ome id for the panel
		*/
		protected static function _buildMsgPanel( $type , $panelId )
		{
			$div = '<div id="' . $panelId . '" style="display:none;" class="innerTable">';
			$key = ( $type == 'log' ) ? 'messages' : 'sql';
			if ( !static::$_options[ 'show_' . $key ] )
			{ 
				return $div.='<span class="vars">Panel is Disabled</span></div>'; 
			}
			$div .= ( @static::$_buffer[ $type ] ) ? static::_buildHtmlTable( $type ) : 
								'<span class="vars">no messages</span>';
			return $div .= '</div>';
		}
		/**
		* Builds the timer panel
		*/
		protected static function _buildTimerPanel( )
		{
			$time = ( ( static::$_endTime - static::$_startTime ) - static::$_tickTime );
			$div = '<div id="timerPanel" style="display:none;" class="innerTable">';
			$div .= '<span style="font-weight:bold;">Global Execution Time:</span>';
			$div .= '<br>Seconds: ' . round( $time , 3 ) . '<br>Milliseconds: ' . 
											round( $time * 1000 , 3 );
			$div .= '</div>';
			return   $div = static::_compressHtml( $div );
		}
		/**
		* Builds the Analysis panel for code coverage analysis
		*/
		protected static function _buildAnalysisPanel( )
		{
			$div = '<div id="analysisPanel" style="display:none;" class="innerTable">';
			$div .= '<h2>Code Coverage Analysis</h2>';
			$div .= static::_buildCoverageData( );
			$div .= '<br><h2>Function Calls Trace</h2>';
			$div .= static::_buildTraceData( );
			$div .= '<br><h2>Search Files For String</h2>';
			if ( session_id( ) === '' )
			{
				$div .= '<span class="vars">Use session start to search for a string inside files!</span>';
			}
			else
			{
				$div .= '<form id="searchStringForm" method="get" ';
				$div .= 'onSubmit="ptc_search_string( );return false;">';
				$div .= '<span><b>Path: </b>&nbsp;&nbsp;</span>';
				$div .= '<input name="ptc_search_path" type="text" value="' . 
									$_SERVER[ 'DOCUMENT_ROOT' ] . '" size="120"><br>';
				$div .= '<span><b>String: </b></span>';
				$div .= '<input name="ptc_search_files" type="text" size="120">';
				$div .= '<input name="ptc_submit_search" type="submit" value="Search">';
				$div .= '</form>';
			}
			return $div .= '</div>' ;
		}
		/**
		* Builds the html data for the code coverage analysis
		*/
		protected static function _buildCoverageData( )
		{
			if ( static::$_options[ 'code_coverage' ] )
			{
				if ( !empty( static::$_finalCoverageData ) )
				{
					$i = 1;
					foreach ( static::$_finalCoverageData as $data )
					{
						$div .= '<div style="font-weight:bold;"><span><b>Coverage ' . $i; 
						$div .= ' result:</b></span> &nbsp;&nbsp;' . static::_formatVar( $data ) . '</div>';
						if ( static::$_consoleStarted )
						{
							\PC::debug( $data , static::$_options[ 'default_category' ] .'.coverageResult ' . ( $i ) );
						}
						$i++;
					}
					//static::$_finalCoverageData = array( );
				}
				else{ @$div .= '<span class="vars">no data available</span><br>'; }
			}
			else 
			{ 
				$div .= '<span class="vars">';
				$div .= 'Code coverage is disabled! To use this feature, ';
				$div .= 'set the option [\'code_coverage\'] to \'true\' or \'full\'!';
				$div .= '</span><br>'; 
			}
			return $div;
		}
		/**
		* Builds the html data for the function calls trace
		*/
		protected static function _buildTraceData( )
		{		
			if ( static::$_options[ 'trace_functions' ] )
			{
				if ( !empty( static::$_finalTraceData ) )
				{
					for ( $a = 0; $a < sizeof( static::$_finalTraceData ); $a++ )
					{
						$data = static::$_finalTraceData[ $a ];
						// this is just a patch
						$data = array_unique( $data , SORT_REGULAR );
						//$data = array_map( 'unserialize' , array_unique( array_map( 'serialize' , $data ) ) );
						@$div .= '<span><b>Trace ' . ( $a +1 ) . ' result: ';
						$div .= '<a href="#" onclick="ptc_show_trace_table(\'jsLive-' . $a . '\' , this );return false;">';
						$div .=sizeof( $data ). ' calls &dArr;</a></b></span>';
						$div .= '<table border="1" style="width:100%;display:none" class="msgTable jsLive-' . 
																			$a . '" id="logTable">';
						$div .= '<tbody><tr><th>#</th><th>function</th><th>file</th>';
						$div .= '<th>line</th><!--<th>memory</th>--><th>called by</th><th>in</th></tr>';
						$i = 1;
						foreach ( $data as $k => $v )
						{
							$link = ( @$v[ 'file' ] ) ? static::_buildTraceLink( $v[ 'file' ] , @$v[ 'line' ] ) 
																		. $v[ 'file' ] . '</a>' : '';		
							$args = ( @$v[ 'args' ] ) ? @preg_replace( '/Array/' , '' ,  
													@static::_formatVar( $v[ 'args' ] ) , 1 )  : '( )';
							$called_by_args = ( @$v[ 'called_by_args' ] ) ? @preg_replace( '/Array/' , '' ,  
													@static::_formatVar( $v[ 'called_by_args' ] ) , 1 )  : '';
							if ( !$called_by_args && @$v[ 'called_by' ] ) { @$v[ 'called_by' ] = @$v[ 'called_by' ] . '( )'; }
							$in = ( @$v[ 'in' ] ) ? static::_buildTraceLink( $v[ 'in' ] , @$v[ 'on_line' ] ) . $v[ 'in' ] : '';
							$in .= ( @$v[ 'on_line' ] ) ? ': '. $v[ 'on_line' ] : ''; 
							$in .=( @$v[ 'in' ] ) ? '</a>' : '';
							$div .= '<tr class="general">';
							$div .= '<td class="fixed"><div style="color:black;"># '. $i . '</div></td>';
							$div .= '<td class="fixed"><div style="color:darkred;font-weight:bold;">';
							$div .= $v[ 'function' ] .$args . '</div></td>';
							$div .= '<td class="fixed">'.$link.'</td>';
							$div .= '<td class="fixed"><div style="color:black;font-weight:bold;">';
							$div .= @$v[ 'line' ] .'</div></td>';
							//$div .= '<td class="fixed"><div style="color:green;font-weight:bold;">';
							//$div .= @static::convertMemUsage( $v[ 'memory' ] ) . '</div></td>';
							$div .= '<td class="fixed"><div style="color:purple;font-weight:bold;">';
							$div .= @$v[ 'called_by' ] . $called_by_args . '</div></td>';
							$div .= '<td class="fixed">' . $in . '</td>';
							$div .= '</tr>';
							$i++;
						}
						$div .= '</tbody></table><br>';
						if ( static::$_consoleStarted )
						{
							\PC::debug( $data , static::$_options[ 'default_category' ] .'.traceResult ' . ( $a + 1 ) );
						}
					}
					//static::$_finalTraceData = array( );
				}
				else { $div .= '<span class="vars">no data available</span><br>'; }
			}
			else 
			{ 
				$div .= '<span class="vars">';
				$div .= 'Functions calls tracing is disabled! To use this feature, ';
				$div .= 'set the option [\'trace_functions\'] to \'true\' or \'full\'!';
				$div .= '</span><br>'; 
			}
			return $div;
		}
		/**
		* Builds the vars panel
		*/
		protected static function _buildVarsPanel( )
		{
			$div = '<div id="varsPanel" style="display:none;" class="innerTable">';
			$div .= '<a href="#" onClick="ptc_show_vars(\'files\',this)">Files';
			$included_files = @get_included_files( );
			$div .= '<span class="count_vars">(' . @sizeof( $included_files ) . 
											')</span>&dArr;</a><br>';
			$div .= '<div id="files" class="vars" style="display:none;line-height:20px;">';
			if ( !@empty( $included_files ) )
			{
				$a = 1;
				foreach ( $included_files as $filename )
				{ 
					$div .= $a . ' ' . @static::_buildTraceLink( $filename ) . $filename . '</a>';
					if ( $_SERVER[ 'SCRIPT_FILENAME' ] == $filename )
					{ 
						$div .= ' <span style="font-weight:bold;color:red;">'; 
						$div .= '&laquo; Main File</span>';
					}
					$div .= "<br>\n";
					$a++;
				}
			}
			else { $div .= '<span class="vars">Could not get included files!</span>'; }
			$div .= '</div>'; 
			$div .= static::_buildInnerVars( 'options' , 'Configuration' , static::$_options );
			$constants = @get_defined_constants( true );
			$div .= static::_buildInnerVars( 'constants' , 'Constants' , $constants );
			$functions = @get_defined_functions( );
			$div .= static::_buildInnerVars( 'functionsInternal' , 'Internal Functions' , 
												@$functions[ 'internal' ] );
			$div .= static::_buildInnerVars( 'functionsUser' , 'User Functions' , @$functions[ 'user' ] );
			$div.=static::_buildInnerVars( 'declared_classes' , 'Declared Classes' ,
											$classes = @get_declared_classes( ) );
			$div.=static::_buildInnerVars( 'declared_interfaces' , 'Declared Interfaces' ,
										$interfaces = @get_declared_interfaces( ) );	
			$div .= static::_buildInnerVars( 'phpInfo' , 'Php Config' , $php_info = static::_buildPhpInfo( ) );										
			if ( !static::$_options[ 'show_globals' ] ) 
			{ 
				$div .= '<span class="vars">Global Vars Disabled</span>'; 
			}
			else { $div .= static::_buildInnerVars( 'globals' , 'Globals ' , array_reverse( $GLOBALS ) ); }
			return  $div .= '</div>';
		}
		/**
		* Builds the inner vars div
		* @param	string		$panelId		the id of the panel to show/hide
		* @param	string		$linkTitle		the title of the link
		* @param	string		$array		array of parameters
		*/
		protected static function _buildInnerVars( $panelId , $linkTitle , $array )
		{
			$div = '<div id="' . $panelId . '" class="vars vars-config" ';
			$div .= 'style="line-height:20px;font-size:14px;"><span>' . $linkTitle;
			$div .= '</span> '. @static::_doDump( $array );
			return $div .= '</div>';
		}
		/**
		* Builds the W3C panel
		*/
		protected static  function _buildW3cPanel( )
		{
			$uri = parse_url( $_SERVER[ 'REQUEST_URI' ] );
			if ( @$uri[ 'query' ] )
			{
				$query = '?';
				$parts = explode( '&' , $uri[ 'query' ] );
				foreach ( $parts as $k => $v )
				{
					if ( $v != static::$_options[ 'url_key' ] . '=' . static::$_options[ 'url_pass' ] )
					{
						$query .= ( $k == 0 ) ? $v : '&' . $v;
					}
				}
			}
			$div = '<div id="w3cPanel" style="display:none;" class="innerTable">';
			if ( static::$_options[ 'show_w3c' ] )
			{
				$div .= '<p>Click on the WC3 link to verify the validation or to check errors</p>';
				$div .= '<p><a href="http://validator.w3.org/check?uri=' . $_SERVER[ 'HTTP_HOST' ] .
												$uri[ 'path' ] . @$query . '" target="_blank">';
				$div .= '<img src="http://www.w3.org/Icons/WWW/w3c_home_nb" alt="W3C Validator"></a></p>';
				$div .= '<p>Or copy paste the source here ';
				$div .= '<a href="http://validator.w3.org/#validate_by_input" target="_blank">';
				$div .= 'http://validator.w3.org/#validate_by_input</a></p>';    
			}
			else { $div .= '<span class="vars">Panel is Disabled</span>'; }
			$div .= '</div>';
			return  $div = static::_compressHtml( $div );
		}
		/**
		* Formats phpinfo() function
		*/
		protected static function _buildPhpInfo( )
		{
			$php_array = static::_phpInfoArray( );
			$php_array[ 'version' ] = @phpversion( );
			$php_array[ 'os' ] = @php_uname( );
			$php_array[ 'extensions' ] = @get_loaded_extensions( );
			ksort( $php_array );
			return $php_array;
		}
		/**
		* Includes the css for the interface
		*/
		protected static function _includeCss( )
		{
			return static::_compressHtml( '<style type="text/css">' . str_replace( array( '{PANEL_TOP}' , 
										'{PANEL_RIGHT}' ) , array( static::$_options[ 'panel_top' ] , 
									static::$_options[ 'panel_right' ] ) , static::$_panelCss ) . '</style>' );
		}
		/**
		* Includes the javascript for the interface
		*/
		protected static function _includeJs( )
		{
			return static::_compressHtml(
				'<script>
					var activePanelID=false;
					var panels=new Object;panels.msg="msgPanel";panels.vars="varsPanel";
					panels.sql="sqlPanel";panels.w3c="w3cPanel";panels.timer="timerPanel";
					panels.analysis="analysisPanel";
					function ptc_show_panel(elId,panelTitle,el)
					{
						var floatDivId="ptcDebugPanel";
						var tabs=document.getElementById(\'floatingTab\').getElementsByTagName("a");
						for(var i=0;i<tabs.length;i++){tabs[i].className="";}
						if(document.getElementById(elId).style.display=="none")
						{ 	
							ptc_reset_panels();
							document.getElementById(elId).style.display=\'\'; 
							document.getElementById(\'ptcDebugStatusBar\').style.display=\'\';
							document.getElementById(floatDivId).style.width=\'100%\';
							el.className="active";activePanelID=elId;ptc_set_title(panelTitle);
						}
						else
						{
							document.getElementById(\'ptcDebugPanelTitle\').style.display=\'none\';
							ptc_reset_panels();
							document.getElementById(floatDivId).style.width=\'\';
						}
						return false;
					};
					function ptc_reset_panels()
					{
						document.getElementById(\'ptcDebugStatusBar\').style.display=\'none\'; 
						for(var i in panels){document.getElementById(panels[i]).style.display=\'none\';}
					};
					function ptc_set_title(panelTitle)
					{
						document.getElementById(\'ptcDebugPanelTitle\').style.display=\'\';
						document.getElementById(\'ptcDebugPanelTitle\').innerHTML=\'<h1>\'+panelTitle+\'</h1>\';
					};
					function hideInterface(){document.getElementById(\'ptcDebugPanel\').style.display=\'none\';};
					function ptc_show_vars(elId,link)
					{
						var element=document.getElementById(elId).style;
						if(element.display=="none")
						{
							link.innerHTML=link.innerHTML.replace("\u21d3","\u21d1");
							element.display=\'\'; 
						}
						else
						{
							link.innerHTML=link.innerHTML.replace("\u21d1","\u21d3");
							element.display=\'none\'; 
						}
					};
					function ptc_show_string(elId,link)
					{
						if(document.getElementById(elId).style.display=="none")
						{ 	
							link.innerHTML=link.innerHTML.replace("\u21d3","\u21d1");
							document.getElementById(elId).style.display=\'inline\'; 
							document.getElementById(elId+"-span").style.display=\'none\'; 
						}
						else
						{ 
							link.innerHTML=link.innerHTML.replace("\u21d1","\u21d3");
							document.getElementById(elId+"-span").style.display=\'\'; 
							document.getElementById(elId).style.display=\'none\'; 
						}
					};
					function ptc_show_trace(className,link)
					{
						var elements=document.getElementsByClassName(\'\'+className+\'\');
						for(i in elements)
						{
							if(elements[i].hasOwnProperty(\'style\'))
							{
								if(elements[i].style.display=="none")
								{
									link.innerHTML=link.innerHTML.replace("\u21d3","\u21d1");
									elements[i].style.display=\'\'; 
								}
								else
								{
									link.innerHTML=link.innerHTML.replace("\u21d1","\u21d3");
									elements[i].style.display=\'none\'; 
								}
							}
						}
					};
					function ptc_read_code(filename,line) 
					{
						var query="http://' . addslashes( $_SERVER[ 'HTTP_HOST' ] ) .
							$path = addslashes( str_replace( realpath( $_SERVER[ 'DOCUMENT_ROOT' ] ) ,
							'' , realpath( dirname( __FILE__ ) ) ) ) . '/PtcDebug.php?ptc_read_file="+filename;
						if(line){query+="&ptc_read_line="+line;}
						newwindow=window.open(query,"name","height=350,width=820");
						if(window.focus){newwindow.focus()};
						return false;
					};
					function ptc_search_string( )
					{
						if ( !document.getElementsByName("ptc_search_files")[0].value )
						{
							alert( "Please type a search string!" );
						}
						else if ( !document.getElementsByName("ptc_search_path")[0].value )
						{
							alert( "Please type a search path!" );
						}
						else
						{
							var query="http://' . addslashes( $_SERVER[ 'HTTP_HOST' ] ) .
							$path = addslashes( str_replace( realpath( $_SERVER[ 'DOCUMENT_ROOT' ] ) ,
							'' , realpath( dirname( __FILE__ ) ) ) ) . 
							'/PtcDebug.php?ptc_search_files="+document.getElementsByName("ptc_search_files")[0].value;
							query+="&ptc_search_path="+document.getElementsByName("ptc_search_path")[0].value;
							newwindow=window.open(query,"name","height=350,width=1220");
							if(window.focus){newwindow.focus()};
						}
						return false;
					};
					function ptc_filter_categories( tableId , catId )
					{
						var table=document.getElementById(tableId);
						var trs=table.getElementsByTagName("tr");
						if(catId=="showAll"){for(var i=1; i<trs.length; i++){trs[i].style.display="";}}
						else
						{
							for(var i=1; i<trs.length; i++){trs[i].style.display="none";}
							var cur_cat=document.getElementsByClassName(catId);
							for(var i=0; i<cur_cat.length; ++i){cur_cat[i].style.display="";}
						}
					};
					function ptc_show_trace_table( className , link )
					{
						var panel = document.getElementsByClassName( className );
						if ( panel[ 0 ].style.display == "none" ) 
						{
							link.innerHTML=link.innerHTML.replace( "\u21d3" , "\u21d1" );						
							panel[ 0 ].style.display = ""; 
						}
						else 
						{
							link.innerHTML=link.innerHTML.replace( "\u21d1" , "\u21d3" );
							panel[ 0 ].style.display = "none"; 
						}
					};
					window.onload=function( ) 
					{
						var div=document.getElementById("ptcDebugStatusBar");
						var press=false;
						div.onmousedown=function(){press=true;return false;};
						this.onmouseover=div.style.cursor="s-resize";
						this.onmousemove=function(event)
						{
							event=event ? event : window.event;
							if(press===true && activePanelID)
							{
								document.getElementById(activePanelID).style.height=(event.clientY-49)+"px";
							}
						};
						this.onmouseup=function(){press=false;};
					};
					/*function ptc_minimize( )
					{
						var floatDivId="ptcDebugPanel";resetPanels();
						document.getElementById(floatDivId).style.width=\'300px\';
						return false;
					};*/
				</script>' );
		}
		/**
		* Compresses the html before render
		* @param	string		$html		some html code
		*/
		protected static function _compressHtml( $html )
		{
			if ( static::$_options[ 'minified_html' ] )
			{
				$html = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!' , '' , $html ); // remove comments
				$html = str_replace( array ( "\r\n" , "\r" , "\n" , "\t" , '  ' , '    ' , '    ' ) , '' , $html ); // tabs,newlines,etc.
			} 
			return $html;
		}
		/**
		* Formats phpinfo() into an array
		*/
		protected static function _phpInfoArray( )
		{
			ob_start( );
			@phpinfo( );
			$info_arr = array( );
			$info_lines = explode( "\n" , strip_tags( ob_get_clean( ) , "<tr><td><h2>" ) );
			$cat= 'General';
			$reg_ex = "~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~";
			foreach ( $info_lines as $line )
			{
				preg_match( "~<h2>(.*)</h2>~" , $line , $title) ? $cat = $title[ 1 ] : null;	// new cat?
				if ( preg_match( "~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~" , $line , $val ) )
				{
				    $info_arr[ $cat ][ $val[ 1 ] ] = $val[ 2 ];
				}
				else if ( preg_match( $reg_ex , $line , $val ) )
				{ 
					$info_arr[ $cat ][ $val[1] ] = array( 'local' => $val[ 2 ] , 'master' => $val[ 3 ] ); 
				}
			}
			return $info_arr;
		}
		/**
		* Attempts to disable any detetected opcode caches / optimizers
		*/
		protected static function _disableOpcodeCache( ) 
		{
			if ( extension_loaded( 'xcache' ) ) 
			{
				// will be implemented in 2.0, here for future proofing
				@ini_set( 'xcache.optimizer', false );
				// xcache seems to do some optimizing, anyway..
			}
			else if ( extension_loaded( 'apc' ) )
			{
				@ini_set( 'apc.optimization', 0 ); // removed in apc 3.0.13 (2007-02-24)
				apc_clear_cache();
			} 
			else if ( extension_loaded( 'eaccelerator' ) ) 
			{
				@ini_set( 'eaccelerator.optimizer', 0 );
				if ( function_exists( 'eaccelerator_optimizer' ) ) 
				{
					@eaccelerator_optimizer( false );
				}
				// try setting eaccelerator.optimizer = 0 in a .user.ini or .htaccess file
			} 
			else if (extension_loaded( 'Zend Optimizer+' ) ) 
			{
				@ini_set('zend_optimizerplus.optimization_level', 0);
			}
		}
		/**
		* Function calls trace engine
		* @param	array		$trace	the php debug_backtrace( ) result
		*/
		protected static function _traceFunctionCalls( $trace = null )
		{
			$depth = 10;
			$trace = ( !$trace ) ? debug_backtrace( true ) : $trace;
			$i= 1 ;
			$methods = get_class_methods( get_called_class( ) );
			foreach ( $trace as $k => $v )
			{
				if ( @$v[ 'class' ] == get_called_class( ) || @in_array( $v[ 'function' ] , $methods ) || 
										@in_array( $trace[ $k + 1 ][ 'function' ] , $methods )) 
				{ 
					continue;
				}
				if( $depth === $i ){ break; }
				$new_array = array
				(
					//'ns' 			=> 	$exe_time
					//'memory' 		=> 	memory_get_usage( true ),
					'file' 				=> 	@$v[ 'file' ] ,
					'line'				=>	@$v[ 'line' ] ,
					'args'			=>	@$v[ 'args' ],
					'function' 			=> 	@$v[ 'class' ] . @$v[ 'type' ] . @$v[ 'function' ]  ,
				);
				if ( @$trace[ $k + 1 ][ 'function' ] || @$trace[ $k + 1 ][ 'class' ] )
				{
					$new_array[ 'called_by' ] = @$trace[ $k + 1 ][ 'class' ] . 
								@$trace[ $k + 1 ][ 'type' ] . @$trace[ $k + 1 ][ 'function' ];
		 			$new_array[ 'called_by_args' ] = @$trace[ $k + 1 ][ 'args' ];
					$new_array[ 'in' ] = @$trace[ $k + 1 ][ 'file' ];
					$new_array[ 'on_line' ] = @$trace[ $k + 1 ][ 'line' ];
				}
				@static::$_traceData[ ] = array_filter( $new_array );
				$i++;
			}
			unset( $new_array );
			unset( $trace );
		}
		/**
		* Removes html entities from the buffer
		* @param	string		$var		some string
		*/	
		protected static function _cleanBuffer( $var )
		{ 
			return ( @is_string( $var ) ) ? @htmlentities( $var ) : $var;
		}
		/**
		* Retrieves the session var for the ptcdebug class
		* @param	string		$var		the session var to retrieve
		*/
		protected static function _getSessionVars( $var = null )
		{
			return ( $var ) ? @$_SESSION[ 'ptcdebug' ][ $var ] : @$_SESSION[ 'ptcdebug' ];
		}
		/**
		* Shows the search popup window with the result
		* @param	string		$string	a search string to search for
		* @param	string		$path		a start path where to search for a string recursively
		*/
		public static function showSearchPopup( $string , $path = null )
		{	
			$path = ( $path ) ? $path : dirname( __FILE__ );
			static::$_options[ 'minified_html' ] = false;
			static::$_options[ 'panel_top' ] = '0px';
			static::$_options[ 'panel_right' ] = '0px';
			$result = static::_includeCss( );
			$result .= '<div style="background: #eee;color: #333;height:100%;">';
			$result .= '<table class="msgTable" id="searchString" border="1" style="width:100%;">';
			$result .= '<tbody><tr><th>#</th><th>file</th><th>line</th>';
			$result .= '<th>string</th><th>occurences</th></tr>';
			$result .= PtcDebug::findString( $string , $path );
			$result = str_replace( $path , '' , $result );
			return $result . '</tbody></table></div>';
		}
	}
	/**
	* Calls highlight file method to show source code, session_start() must be active for security reasons
	*/
	if ( @$_GET[ 'ptc_read_file' ] )
	{
		@session_start( );
		if ( !@$_SESSION[ 'ptcdebug' ][ 'code_highlighter' ] ) { exit( ); }
		echo PtcDebug::highlightFile( $_GET[ 'ptc_read_file' ] , @$_GET[ 'ptc_read_line' ] );
		exit( );
	}
	/**
	* Shows a popup with string search results, session_start() must be active for security reasons
	*/
	if ( @$_GET[ 'ptc_search_files' ] )
	{
		@session_start( );
		if ( !@$_SESSION[ 'ptcdebug' ][ 'search_files' ] ) { exit( ); }
		echo PtcDebug::showSearchPopup( $_GET[ 'ptc_search_files' ] , @$_GET[ 'ptc_search_path' ] );
		exit( );
	}