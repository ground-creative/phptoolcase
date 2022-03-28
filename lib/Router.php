<?php

	namespace phptoolcase;

	/**
	* PHPTOOLCASE ROUTER CLASS
	* PHP version 5.4+
	* @category 	Library
	* @version	v1.1.0-stable
	* @author   	Carlo Pietrobattista <carlo@ground-creative.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	class Router
	{
		/**
		* Retrievesthe executed route for a request
		*/
		public static function currentRoute( ){ return static::$_currentRoute; }
		/**
		* Retrieves the executed controller  for a request
		*/
		public static function currentController( ){ return static::$_currentController; }
		/**
		* Retrieves route values if any
		* @param	string	$name		the name of the value
		* @return	the value by it's name, or all values if the $name argument is null
		*/
		public static function getValue( $name = null ){ return static::getValues( $name ); }
		/**
		* Adds or removes the trailing slash from requests
		* @param	bool		$value	true to add the traling slash, false to remove
		*/
		public static function trailingSlash( $value = null )
		{ 
			return static::$_trailingSlash = ( $value ) ? $value : static::$_trailingSlash;
		}
		/**
		* Retrieves the requested uri
		* @param	string	$part	the "path" or the "query"
		* @return	the uri part if $part argument is set, an array otherwise
		*/
		public static function getUri( $part = false )
		{
			if ( $part )
			{
				$uri = parse_url( $_SERVER[ 'REQUEST_URI' ] );
				return ( isset( $uri[ $part ] ) ) ? $uri[ $part ] : null;
			}
			return parse_url( $_SERVER[ 'REQUEST_URI' ] ); 
		}
		/**
		* Retrieves the request protocol
		*/
		public static function getProtocol( )
		{ 
			return ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] ) ? 'https' : 'http';
		}
		/**
		* Alias of Router::getControllers( )
		*/
		public static function getController( $name = null )
		{ 
			return static::getControllers( $name ); 
		}
		/**
		* Checks if the request is an ajax request
		* @return	true if the request is ajax, false otherwise
		*/
		public static function isAjax( )
		{ 
			if ( !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && 
				( strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' || 
							strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'ajax' ) ) 
			{
				return true;
			}
			return false;
		}
		/**
		*  Retrieves added controllers
		* @param	string	$name		the name of the controller
		* @rerturn	the controller if the $name argument is set , or all set controllers
		*/
		public static function getControllers( $name = null )
		{ 
			return ( $name ) ? static::$_controllers[ $name ] : static::$_controllers;
		}
		/**
		* Changes the main group name for the routes
		* @param	string	$name	some new name for the main group
		*/
		public static function mainGroupName( $name = null )
		{
			return static::$_mainGroupName = ( $name ) ? $name : static::$_mainGroupName;
		}
		/**
		* Adds a route for a get request
		* @param	string	$route		some route
		* @param	mixed	$callback		some callback to execute
		*/
		public static function get( $route , $callback )
		{
			return static::_addRoute( $route , $callback , 'get' );
		}
		/**
		* Adds a route for a post request
		* @param	string	$route		some route
		* @param	mixed	$callback		some callback to execute
		*/
		public static function post( $route , $callback )
		{
			return static::_addRoute( $route , $callback , 'post' );
		}
		/**
		* Adds a route for a put request
		* @param	string	$route		some route
		* @param	mixed	$callback		some callback to execute
		*/
		public static function put( $route , $callback )
		{
			return static::_addRoute( $route , $callback , 'put' );
		}
		/**
		* Adds a route for a post request
		* @param	string	$route		some route
		* @param	mixed	$callback		some callback to execute
		*/
		public static function delete( $route , $callback )
		{
			return static::_addRoute( $route , $callback , 'delete' );
		}
		/**
		* Adds a route for any request
		* @param	string	$route		some route
		* @param	mixed	$callback		some callback to execute
		*/
		public static function any( $route , $callback )
		{
			return static::_addRoute( $route , $callback );
		}
		/**
		*
		*/
		public static function notFound( $code = 404 , $callback = null )
		{
			$callback = static::_checkCallback( $callback );
			return static::$_notFound = array( 'code' => $code , 'callback' => $callback );
		}
		/**
		*
		*/
		public static function redirect( $location , $statusCode = 301 )
		{
			static::_setRedirectCookie( $statusCode );
			$location = ( 0 !== strpos( $location , 'Location: ' ) ) ? 
								'Location: ' . $location : $location; 
			header( $location , true , $statusCode );
			exit( );
		}
		/**
		*
		*/
		public static function controller( $route , $controller )
		{
			/*if ( array_key_exists( $route , static::$_controllers ) )
			{
				trigger_error( 'A controller is already set for route ' . 
									$route . '!' , E_USER_ERROR );
				return false;
			}*/
			if ( !class_exists( $controller ) )
			{
				trigger_error( 'Controller class ' . $controller . 
							' could not be found!' , E_USER_ERROR );
				return false;
			}
			return static::_addControllerMethods( $controller , $route );
		}
		/**
		*
		*/
		public static function segment( $segment , $uri = null )
		{
			$uri = ( $uri ) ? $uri : static::url( );
			$pieces = explode( '/' , $uri );
			return ( isset( $pieces[ $segment ] ) ) ? $pieces[ $segment ] : false;
		}
		/**
		*
		*/
		public static function url( $uri = null , $full = true , $segment = false )
		{
			$uri = ( !$uri ) ? $_SERVER[ 'REQUEST_URI' ] : $uri;
			$return = ( $full ) ? $_SERVER[ 'HTTP_HOST' ] . 
				static::_cleanRoute( $uri ) : static::_cleanRoute( $uri );
			return ( $segment ) ? static::segment( $segment , $return ) : $return;
		}
		/**
		*
		*/
		public static function getValues( $name = null , $clean = true )
		{
			$array = ( $clean ) ? static::$_cleanParamValues : static::$_paramValues;
			if ( !$name ) { return $array; } // return all values
			if ( !array_key_exists( $name , $array ) ){ return null; }
			return $array[ $name ]; 
		}
		/**
		*
		*/
		public static function getRoute( $name , $convert = true )
		{
			if ( !array_key_exists( $name , static::$_map ) )
			{
				trigger_error( 'Route alias ' . $name . ' does not exist!' , E_USER_ERROR );
				return false;
			}
			if ( $convert ) // try to replace route values in url
			{
				if ( !empty( static::$_paramValues ) )
				{
					$route = static::$_map[ $name ]->route;
					foreach ( static::$_paramValues as $k => $v )
					{
						$route = str_replace( $k , $v , $route );
					}
					return $route;
				}
			}
			return static::$_map[ $name ]->route; 
		}
		/**
		*
		*/
		public static function routed( $routed = null )
		{ 
			if ( is_bool( $routed ) )
			{ 
				if ( !$routed && static::$_routed )
				{
					static::_debug( '' , 'Current route <b><i>' . static::$_currentRoute . 
								'</i></b> has been aborted manually!' , 'Router Action' );
					static::$_currentRoute = null;
				}
				static::$_routed = $routed;
			}
			return static::$_routed; 
		}
		/**
		*
		*/
		public static function filter( $name , $callback )
		{
			if ( array_key_exists( $name , static::$_filters ) )
			{
				trigger_error( 'Filter ' . $name . 
							' already exists, use some other name!' , E_USER_ERROR );
				return false;
			}
			// check if we have a valid callback
			if ( !$callback = static::_checkCallback( $callback ) ){ return false; }
			static::$_filters[ $name ] = $callback;
			$msg = 'Added new filter with name <b><i>' . $name . '</I></b>';
			static::_debug( static::$_filters[ $name ] , $msg , 'Router Config' );
			return static::$_filters[ $name ];
		}
		/**
		*
		*/
		public static function header( $statusCode , $replace = true ) 
		{
			$string = ( array_key_exists( $statusCode , static::$_statusCodes ) ) ? 
							$_SERVER[ 'SERVER_PROTOCOL' ] . ' ' . $statusCode . ' ' . 
								static::$_statusCodes[ $statusCode ] : $statusCode;
			if ( is_numeric( $statusCode ) ){ header( $string , $replace , $statusCode ); }
			else{ header( $string , $replace ); }
			return true;
		}
		/**
		*
		*/	
		public static function group( $name , $callback )
		{
			if ( array_key_exists( $name , static::$_groups ) ) // check if group name exists
			{
				trigger_error( 'Group name ' . $name . ' already exists!' , E_USER_ERROR );
				return false;
			}
			if ( !$callback = static::_checkCallback( $callback ) ){ return false; }
			static::$_currentGroups[ ] = $name;
			static::$_groups[ $name ] = new GroupRoutes( static::$_currentGroups );
			call_user_func( $callback ); // execute the callback
			array_pop( static::$_currentGroups ); // remove last added group
			return static::$_groups[ $name ];
		}
		/**
		*
		*/
		public static function when( $route , $callback , $options = array( ) )
		{
			$route = static::_cleanRoute( $route );
			if ( array_key_exists( $route , static::$_globalFilters ) ) // check if routes exists
			{
				trigger_error( 'Route ' . $route . 
					' already exists as a possible global filter!' , E_USER_ERROR );
				return false;
			}
			$options = static::_addWhenOptions( $options ); // add default options
			if ( !$callback = static::_checkCallback( $callback ) ){ return false; }
			$options[ 'callback' ] = $callback;
			static::_debug( $options , 'Added new global pattern <b><i>' . 
								$route . '</i></b>' , 'Router Config' );
			return static::$_globalFilters[ $route ] = $options;
		}
		/**
		*
		*/
		public static function run( $checkErrors = true , $uri = null )
		{
			$request = strtolower( $_SERVER[ 'REQUEST_METHOD' ] );
			$uri = ( $uri ) ? $uri : static::getUri( );
			$protocol = static::getProtocol( );		
			$debug = array
			( 
				'request' 		=> $request , 
				'protocol'		=> $protocol , 
				'uri' 			=> $uri ,
				'filters'		=> static::$_filters ,
				'groups'		=> static::$_groups ,
				'global_filters'	=> static::$_globalFilters ,
				'redirects'	=> null
			);
			if ( isset( $_COOKIE[ 'PtcRouter_redirects' ] ) && 
					null !== $_COOKIE[ 'PtcRouter_redirects' ] )
			{
				$debug[ 'redirects' ] = json_decode( $_COOKIE[ 'PtcRouter_redirects' ] );
			}
			if ( !empty( static::$_globalFilters ) ) // check global filters patterns first
			{
				static::_debug( $debug , 
							'Starting to check global patterns filters!' , 'Router Config' );
				if ( static::_runGlobalPatterns( $uri , $request , $protocol ) ){ return; }
			}
			if ( static::_redirectTrailingStash( static::$_trailingSlash ) ){ return; }
			if ( !$routes = static::_buildGroupRoutes( $checkErrors ) ){ return false; }
			if ( !empty( $routes ) ) // start processing routes if any
			{
				$debug[ 'routes' ] = $routes;
				static::_debug( $debug , 
							'Starting to check available routes!' , 'Router Config' );
				return static::_processRoutes( $routes , $uri , $request , $protocol );
			}
			static::_setRedirectCookie( 'remove' );
			return false;
		}
		/**
		*
		*/
		protected static $_mainGroupName = 'mainGroupRoutes'; 
		/**
		*
		*/
		protected static $_currentRoute = null;
		/**
		*
		*/
		protected static $_currentController = null;
		/**
		*
		*/
		protected static $_subdomain = null;
		/**
		*
		*/
		protected static $_notFound = null;
		/**
		*
		*/
		protected static $_routed = false;		
		/**
		*
		*/
		protected static $_trailingSlash = true;
		/**
		*
		*/
		protected static $_map = array( );
		/**
		*
		*/
		protected static $_controllers = array( );
		/**
		*
		*/
		protected static $_globalFilters = array( );
		/**
		*
		*/
		protected static $_groupFilters = array( );
		/**
		*
		*/
		protected static $_filters = array( );
		/**
		*
		*/
		protected static $_values = array( );
		/**
		*
		*/
		protected static $_paramValues = array( );
		/**
		*
		*/
		protected static $_cleanParamValues = array( );		
		/**
		*
		*/
		protected static $_groups = array( );
		/**
		*
		*/
		protected static $_currentGroups = array( );
		/**
		*
		*/
		protected static $_fileExtensions = array
		(
			'.php' , '.html' , '.xml', '.gif' , '.jpeg' , '.png' , '.js' , 
			'.swf' , '.pdf' , '.ppd' , '.atom' , '.avi' , '.torrent' , 
			'.rep' , '.bz' , '.bz2' , '.css' , '.tpl' , '.jpg' , '.jpgv' ,
			'.exe' , '.mpeg' , '.mp4' , '.mp3' , '.xhtml' , '.htm', '.txt'
		);
		/**
		*
		*/
		protected static $_statusCodes = array 
		(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended'
		);
		/**
		*
		*/						
		protected static function _processRoutes( $routes , $uri , $request , $protocol )
		{
			$response = null;
			$pieces = array_values( array_filter( explode( '/' , $uri[ 'path' ] ) ) );
			foreach ( $routes as $route )
			{
				if ( 'any' != $route->request && $route->request != $request ){ continue; }
				if ( 'any' != $route->protocol && $route->protocol != $protocol ){ continue; }
				static::$_values = array( ); // set empty values array
				if ( !static::_checkDomain( $route ) ){ continue; } // check if domain matches
				$response = static::_buildParams( $route , $pieces );
				if ( false === $response ){ return false; } // filter stopped route execution
				if ( 'continue' === $response ){ continue; } // continue if params check failed
				if ( static::$_routed ){ return $response; } // return if routed with parameters
				if ( $uri[ 'path' ] === $route->route )  // execute uri without parameters if matches
				{
					if ( !$response = static::_execute( $route->route , $route ) ){ return false; }
					if ( static::$_routed ) // return if routed
					{
						static::_setRedirectCookie( 'remove' );
						return $response; 
					} 
				}
			}
			if ( !static::$_routed )
			{
				static::_setRedirectCookie( 'remove' );
				$debug_mgs = 'No available routes found matching the current request!';
				static::_debug( $debug_mgs , '' , 'Router Config' );
				if ( static::$_notFound ){ return static::_processErrorPage( ); } // the error page
				return false;
			}
		}
		/**
		*
		*/
		protected static function _processErrorPage( )
		{
			$debug = '<b><i>Router::notFound( )</i></b> was called with status code';
			static::_debug( static::$_notFound[ 'code' ] , $debug , 'Router Action' );
			if ( static::$_notFound[ 'code' ] ){ static::header( static::$_notFound[ 'code' ] ); }
			if ( static::$_notFound[ 'callback' ] )
			{
				return call_user_func( static::$_notFound[ 'callback' ] ); 
			}
		}
		/**
		*
		*/
		protected static function _execute( $route , $obj )
		{
			if ( $obj->controller ){ static::$_currentController = $obj->controller; }
			$response = null;
			if ( static::_runFilters( $obj , 'before' ) ){ return false; }
			static::_debug( $obj , 'Executing callback for route <b><i>' . 
									$route . '</i></b>' , 'Router Action' );
			static::$_currentRoute = $route;
			$obj->set( 'executed' , true );
			static::$_routed = true;
			if ( static::$_values ) // run callback with params 
			{
				$response = call_user_func_array( $obj->callback , static::$_values );
			}
			else{ $response = call_user_func( $obj->callback ); } // run without params
			if ( static::_runFilters( $obj, 'after' , $response ) ){ return false; }
			return $response;
		}
		/**
		*
		*/
		protected static function _redirectTrailingStash( $trailingSlash = null )
		{
			$uri = static::getUri( );
			if ( 'get' !== strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) || 
					static::_checkFileExtension( $uri[ 'path' ] ) ){ return false; }
			if ( true === $trailingSlash && '/' !== substr( $uri[ 'path' ] , -1 ) )
			{
				$route = ( isset( $uri[ 'query' ] ) ) ? 
						$uri[ 'path' ] . '/?' . $uri[ 'query' ] : $uri[ 'path' ] . '/';
				static::redirect( static::getProtocol( ) . '://' . 
					$_SERVER[ 'HTTP_HOST' ] . $route , 301 );
				exit( );
				return true;
			}
			else if ( false === $trailingSlash && '/' === substr( $uri[ 'path' ] , -1 ) )
			{
				$route = ( isset( $uri[ 'query' ] ) ) ? 
						substr( $uri[ 'path' ] , 0 , -1 ) . '?' . 
						$uri[ 'query' ] : substr( $uri[ 'path' ] , 0 , -1 );
				static::redirect( static::getProtocol( ) . '://' . 
					$_SERVER[ 'HTTP_HOST' ] . substr( $uri[ 'path' ] , 0 , -1 ) , 301 );
				exit( );
				return true;
			}
			return false;
		}
		/**
		*
		*/
		protected static function _addWhenOptions( $options )
		{
			$default_options = array( 'request' => 'any' , 
				'protocol' => 'any' , 'domain' => $_SERVER[ 'HTTP_HOST' ] );
			$options = ( empty( $options ) ) ? $default_options : $options;
			if ( !array_key_exists( 'request' , $options ) ){ $options[ 'request' ] = 'any'; }
			if ( !array_key_exists( 'protocol' , $options ) ){ $options[ 'protocol' ] = 'any'; }
			if ( !array_key_exists( 'domain' , $options ) )
			{ 
				$options[ 'domain' ] = $_SERVER[ 'HTTP_HOST' ]; 
			}
			return $options;
		}
		/**
		*
		*/
		protected static function _runGlobalPatterns( $uri , $request , $protocol = 'any' )
		{
			if ( !empty( static::$_globalFilters ) )
			{
				foreach ( static::$_globalFilters as $k => $v )
				{
					$check_request = explode( '|' , $v[ 'request' ] );
					if ( false === in_array( 'any' , $check_request ) && 
						false === in_array( $request , $check_request ) ){ continue; }
					if ( 'any' != $v[ 'protocol' ] && $protocol != $v[ 'protocol' ] ){ continue; }
					if ( $_SERVER[ 'HTTP_HOST' ] != $v[ 'domain' ] ){ continue; }
					if ( 0 === strpos( $uri[ 'path' ] , str_replace( '*' , '' , $k ) ) )
					{
						$debug_msg = 'Executing global pattern filter <b><i>' . $k . 
												'</i></b> on current request!';
						$debug_arr = array( 'request' => $request , 'uri' => $uri );
						$debug_arr[ 'callback' ] = $v[ 'callback' ];
						static::_debug( $debug_arr , $debug_msg , 'Router Action' );
						$params = array( $uri , $request , $protocol );
						$filtered = call_user_func_array( $v[ 'callback' ] , $params );
					}
					if ( $filtered ) // stop any route execution if filter returns anything
					{ 
						$debug_msg = '<b>Global pattern filter <i>' . $k . 
										'</i> has stopped routes execution!</b>';
						static::_debug( $debug_arr , $debug_msg . '!' , 'Router Action' );
						return true;
					}
				}
			}
			return false;
		}
		/**
		*
		*/
		protected static function _setRedirectCookie( $statusCode )
		{
			if ( 'remove' === $statusCode )
			{
				setcookie( 'PtcRouter_redirects' , null , time( ) + 600 , '/' );
				return;
			}
			if ( isset( $_COOKIE[ 'PtcRouter_redirects' ] ) && 
					null !== $_COOKIE[ 'PtcRouter_redirects' ] )
			{
				$data = json_decode( $_COOKIE[ 'PtcRouter_redirects' ] );
			}
			$current = static::getUri( );
			$build = static::getProtocol( ) . '://' . $_SERVER[ 'HTTP_HOST' ] . $current[ 'path' ];
			if ( isset( $current[ 'query' ] ) ){ $build .= '?' . $current[ 'query' ]; }
			$build .= ' ' . $statusCode;
			@$data[ ] = static::getProtocol( ) . '://' . $_SERVER[ 'HTTP_HOST' ] . 
										$current[ 'path' ] . ' ' . $statusCode;
			setcookie( 'PtcRouter_redirects' , json_encode( $data ) , time( ) + 600 , '/' );
		}
		/**
		* Stores group filters to pass to the routes
		*/
		protected static function _buildGroupFilters( $groupName , $type )
		{
			if ( @array_key_exists( $type , static::$_groups[ $groupName ]->filters ) )
			{
				if ( !array_key_exists( $type , static::$_groupFilters ) )
				{
					static::$_groupFilters[ $type ] = array( );
				}
				foreach ( static::$_groups[ $groupName ]->filters[ $type ] as $filter )
				{
					static::$_groupFilters[ $type ][ ] = $filter; 
				}
			}
		}
		/**
		* Add filters from group to routes
		*/
		protected static function _addRouteFilters( $route , $type )
		{
			if ( array_key_exists( $type , static::$_groupFilters ) )
			{
				if ( isset ( $route->filters[ $type ] ) )
				{
					$filters = $route->filters;
					$filters[ $type ] = array_merge( static::$_groupFilters[ $type ] , $filters[ $type ] );
					$route->set( 'filters' , $filters );
				}
				else{ $route->set( 'filters' , array( $type => static::$_groupFilters[ $type ] ) ); }
			}
		}
		/*
		*
		*/
		protected static function _addGroupPatterns( $group , $name )
		{
			if ( $patterns = static::$_groups[ $name ]->patterns ) 
			{
				foreach ( $patterns as $k => $v ){ $group->where( $k , $v ); }
			}
		}
		/**
		*
		*/
		protected static function _checkNestedGroups( $nested , $name , $prefix = null )
		{
			if ( $nested->groups > 1 ) // work with prefix and filters from nested groups
			{
				foreach ( $nested->groups as $k => $v )
				{ 
					if ( $name !== $v ) // add params only if not in the current group
					{
						$prefix = static::_addGroupPrefix( $v , $prefix );
						static::_buildGroupFilters( $v , 'before' );
						static::_buildGroupFilters( $v , 'after' );
						static::_addGroupPatterns( $nested , $v );
					}
				}
			}
			return $prefix;
		}
		/**
		*
		*/
		protected static function _addGroupPrefix( $groupName , $prefix )
		{
			$prefix = ( static::$_groups[ $groupName ]->prefix ) ?  $prefix . 
						static::_cleanRoute( static::$_groups[ $groupName ]->prefix ) : $prefix;
			return $prefix;
		}
		/**
		*
		*/
		protected static function _buildRoutes( $group , $prefix , $routes = array( ) , $checkErrors = false )
		{
			foreach ( $group->routes as $key => $val )
			{
				if ( $val->controller )
				{
					preg_match_all( '|/{.*?}|' , $prefix , $pr_matches ); // prefix placeholders
					if ( !empty( $pr_matches[ 0 ] ) )
					{
						foreach ( $pr_matches[ 0 ] as $match )
						{
							$val->set( 'route' , str_replace( $match  , '' , $val->route ) );
						}
					}
				}
				$val->set( 'route' , $prefix . $val->route ); // set the uri with the group prefix
				preg_match_all( '|/{.*?}|' , $val->route , $matches ); // placeholders
				if ( !empty( $matches[ 0 ] ) )
				{
					$parameters = array( );
					foreach ( $matches[ 0 ] as $match )
					{
						$match_raw = preg_replace( '#/{|}|\?#' , '' , $match );
						$val->parameters( $match_raw , $match );
					}
				}
				if ( $group->patterns )  // set patterns from group if any
				{
					foreach ( $group->patterns as $prm => $regex ){ $val->where( $prm , $regex ); }
				}
				if ( !empty( static::$_groupFilters ) ) // add filters from current group
				{
					static::_addRouteFilters( $val , 'before' );
					static::_addRouteFilters( $val , 'after' );
				}
				if ( $checkErrors && !static::_checkErrors( $val ) ){ return false; }
				foreach ( $group->groups as $parent ) // add parent groups domain and protocol
				{
					if ( static::$_groups[ $parent ]->domain )
					{
						$val->domain( static::$_groups[ $parent ]->domain );
					}
					if ( static::$_groups[ $parent ]->protocol )
					{
						$val->protocol( static::$_groups[ $parent ]->protocol );
					}
				}
				if ( $checkErrors ) // check for duplicate routes
				{
					foreach ( $routes as $route )
					{
						if ( $val->route === $route->route && 
							$val->protocol === $route->protocol && 
								$val->domain === $route->domain && 
									$val->request === $route->request )
						{
							trigger_error( 'Duplicate route found, ' . $val->route . 
							' cannot continue execution!' , E_USER_ERROR );
							return false;
						}
					}
				}
				if ( !static::_buildRouteAlias( $val ) ){ return false; } // map the route to a name
				$routes[ ] = $val;
			}
			return $routes;
		}
		/**
		*
		*/
		protected static function _buildRouteAlias( $route )
		{
			if ( $route->map ) // check if a route alias is set
			{ 
				if ( array_key_exists( $route->map , static::$_map ) )
				{
					trigger_error( 'Route alias ' . $route->map . 
						' already defined, use some other name!' , E_USER_ERROR );
					return false;
				}
				static::$_map[ $route->map ] = $route; 
			}
			return true;
		}
		/**
		*
		*/
		protected static function _checkErrors( $route )
		{	
			if ( !static::_checkPatterns( $route ) ){ return false; }
			if ( !static::_checkFilters( $route , 'before' ) ){ return false; }
			if ( !static::_checkFilters( $route , 'after' ) ){ return false; }
			return true;
		}
		/**
		*
		*/
		protected static function _checkDomain( $route )
		{
			if ( $_SERVER[ 'HTTP_HOST' ] != $route->domain ) // work with domain name
			{
				$host = explode( '.' , $_SERVER[ 'HTTP_HOST' ] );
				$domain = explode( '.' , $route->domain );
				if ( count( $domain ) !== count( $host ) ) // check if parameter is optional
				{
					if ( false !== strpos( $domain[ 0 ] , '?}' ) ){ unset( $domain[ 0 ] ); }
					else{ return false; } // abort if param is not optional
				}
				if ( false !== strpos( $domain[ 0 ] , '}' ) )
				{
					$raw = preg_replace( '#{|}|\?#' , '' , $domain[ 0 ] );
					if ( $route->patterns && array_key_exists( $raw , $route->patterns ) )
					{
						preg_match( '~' . $route->patterns[ $raw ] . '~' , $host[ 0 ] , $matches );
						if ( empty( $matches ) ){ return false; } // abort if no matches
					}
					static::$_subdomain = $host[ 0 ];
					static::$_paramValues[ $domain[ 0 ] ] = $host[ 0 ];
					static::$_cleanParamValues[ $raw ]	= $host[ 0 ];	
					unset( $domain[ 0 ] );
					unset( $host[ 0 ] );
					$host = array_values( $host );
				}
				$domain = array_values( $domain );
				foreach ( $domain as $pos => $part )
				{
					if ( $host[ $pos ] !== $part  && '*' !== $part ){ return false; } 
				}
			}
			return true;
		}
		/**
		*
		*/
		protected static function _runRoute( $params , $route , $pieces )
		{
			if ( !static::_checkParams( $params , $route , $pieces ) ){ return 'continue'; }
			if ( !$response = static::_execute( $route->route , $route ) ){ return false; }
			return $response;
		}
		/**
		*
		*/
		protected static function _buildParams( $route , $pieces )
		{				
			if ( $route->parameters ) // work with parameters if present
			{
				$params = array_values( array_filter( explode( '/' , $route->route ) ) );
				if ( count( $pieces ) === count( $params ) ) // uri matches route
				{							
					return static::_runRoute( $params , $route , $pieces );
				}
				else // try removing optional parameters
				{
					foreach ( $params as $k => $v )
					{
						if ( false !== strpos( $v , '?}' ) && 
							!array_key_exists( $k , $pieces ) ){ unset( $params[ $k ] ); }
					}
					if ( count( $pieces ) === count( $params ) ) // uri matches route
					{							
						return static::_runRoute( $params , $route , $pieces );
					}
				}
			}
			return true;
		}
		/**
		*
		*/
		protected static function _buildGroupRoutes( $checkErrors = false )
		{
			if ( empty( static::$_groups ) )	// if no routes defined, exit
			{
				trigger_error( 'No routes were defined, quitting now!' , E_USER_NOTICE );
				return false;
			}
			$routes = array( );
			foreach ( static::$_groups as $name => $group )
			{
				static::$_groupFilters = array( ); // reset group filters
				if ( $group->routes ) // check if we have any routes defined for this group
				{ 
					$prefix = static::_checkNestedGroups( $group , $name );
					if ( $group->filters ) // check if current group has any filters
					{
						static::_buildGroupFilters( $name , 'before' );
						static::_buildGroupFilters( $name , 'after' );					
					}
					$prefix = static::_addGroupPrefix( $name , $prefix );
					$routes = static::_buildRoutes( $group , $prefix , $routes , $checkErrors );
					if ( !$routes ){ return false; } // return if error occured building routes
				}
			}
			return $routes;
		}
		/**
		*
		*/
		protected static function _addControllerProperties( $controller , $method , $route , $params )
		{
			$properties = static::_getControllerProperties( $controller );
			$route->set( 'controller' , $controller );
			$route->map( strtolower( $controller . '.' . $method ) );
			If ( array_key_exists( '_domain' , $properties ) )
			{
				$route->domain( $properties[ '_domain' ] );
			}
			If ( array_key_exists( '_protocol' , $properties ) && 
				array_key_exists( $method , $properties[ '_protocol' ] ) )
			{
				$route->protocol( $properties[ '_protocol' ][ $method ] );
			}
			If ( array_key_exists( '_before' , $properties ) && 
				array_key_exists( $method , $properties[ '_before' ] ) )
			{
				$route->before( $properties[ '_before' ][ $method ] );
			}
			If ( array_key_exists( '_after' , $properties ) && 
				array_key_exists( $method , $properties[ '_after' ] ) )
			{
				$route->after( $properties[ '_after' ][ $method ] );
			}			
			if ( array_key_exists( '_where' , $properties ) )
			{
				foreach ( $properties[ '_where' ] as $k => $v )
				{
					if ( false !== strpos( $params , '{' . $k . '}' ) || 
						false !== strpos( $params , '{' . $k . '?}' ) )
					{ 
						$route->where( $k , $v );
					}
				}
			}
			return $route;
		}
		/**
		*
		*/
		protected static function _getControllerProperty( $controller , $property )
		{
			$reflection = new \ReflectionClass( $controller );
			if ( $reflection->hasProperty( $property ) )
			{
				$property = $reflection->getProperty( $property );
				$property->setAccessible( true );
				return $property->getValue( new $controller( ) );
			}
			return null;
		}
		/**
		*
		*/
		protected static function _getControllerProperties( $controller )
		{
			$properties = array( 'protocol' , 'domain' , 'before' , 'after' , 'where' );
			$params = array( );
			foreach ( $properties as $property )
			{
				if ( $param = static::_getControllerProperty( $controller , '_' . $property ) )
				{
					$params[ '_' . $property ] = $param; 
				}
			}
			return $params;
		}
		/**
		*
		*/
		protected static function _addControllerMethods( $controller , $route )
		{
			if ( $methods = get_class_methods( $controller ) )
			{
				$class = get_called_class( );
				$route = static::_cleanRoute( $route , false );
				foreach ( $methods as $method )
				{	
					$call = null;
					$prefixes  = array( 'get_' , 'post_' , 'any_' , 'put_' , 'delete_' );
					$prefix = substr( $method , 0 , 4 );
					$prefix1 = substr( $method , 0 , 5 );
					$prefix2 = substr( $method , 0 , 7 );
					if ( in_array( $prefix , $prefixes ) ){ $call = $prefix; }
					else if ( in_array( $prefix1 , $prefixes ) ){ $call = $prefix1; }
					else if ( in_array( $prefix2 , $prefixes ) ){ $call = $prefix2; }
					if ( $call )	
					{
						$base = strtolower( str_replace( '_' , '-' , str_replace( $call , '' , $method ) ) ); 
						$base = ( 'index' === $base ) ? '' : '/' . $base;
						$params = static::_addControllerParams( $controller , $method , $base );
						$params = static::_cleanRoute( $params , static::$_trailingSlash ); 
						$obj = call_user_func_array( array( $class , substr( $call , 0 , -1 ) ) , 
									array( $route . $params , $controller . '@' . $method ) );
						static::_addControllerProperties( $controller, $method , $obj , $params );
					}
				}
				static::$_controllers[ $route ]  = $controller;
				return true;
			}
			return false;
		}
		/**
		*
		*/
		protected static function _addControllerParams( $controller , $method , $route )
		{
			$reflection = new \ReflectionMethod( $controller , $method );
			$params = $reflection->getParameters( );
			if ( !empty( $params ) )
			{
				$route = static::_cleanRoute( $route , true );
				foreach ( $params as $param ) 
				{
					$var = '{' . $param->getName( ); 
					if ( $param->isOptional( ) ){ $var .= '?'; }
					$var .= '}/';
					$route .= $var;
				}
			}
			return $route;
		}
		/**
		*
		*/
		protected static function _checkFilters( $route , $type )
		{
			if ( $route->filters && isset( $route->filters[ $type ] ) )
			{
				foreach ( $route->filters[ $type ] as $filter )
				{
					if ( !array_key_exists( $filter , static::$_filters ) )
					{
						trigger_error( 'Filter name ' . $filter . ' does not exist!' , E_USER_ERROR );
						return false;
					}
				}							
			}
			return true;
		}
		/**
		*
		*/
		protected static function _runFilters( $route , $type , $response = null )
		{
			if ( isset( $route->filters[ $type ] ) )
			{
				foreach ( $route->filters[ $type ] as $filter )
				{
					static::_debug( '' , 'Executing ' . $type .
									' filter <b><i>' . $filter . 
									'</i></b> for route <b></i>' . 
									$route->route .'</i></b>' , 'Router Action' );
					$params = array( $route , static::getUri( ) );
					if ( $response ){ $params[ ] = $response; } 
					$result = call_user_func_array( static::$_filters[ $filter ] , $params );
					if ( $result )
					{  
						static::$_currentRoute = null;
						static::$_routed = false;
						$msg = '<b>Stopped routes execution requested by filter</b>';
						static::_debug( $filter , $msg , 'Router Action' );
						return true;
					}
				}
			}
			return false;
		}
		/**
		*
		*/
		protected static function _checkParams( $params , $route , $uri )
		{
			foreach ( $params as $k => $v )
			{
				if ( preg_match( '|{.*?}|' , $v ) ) // check patterns on parameters
				{
					$raw = preg_replace( '#{|}|\?#' , '' , $v );
					if ( $extension = static::_checkFileExtension( $raw ) )
					{
						$raw = str_replace( $extension , '' , $raw );
						$real_uri = $uri[ $k ];
						$uri[ $k ] = str_replace( $extension , '' , $uri[ $k ] );
						if ( $uri[ $k ] . $extension !== $real_uri ){ return false; }
					}
					if ( $route->patterns && array_key_exists( $raw , $route->patterns ) )
					{
						if ( $route->patterns[ $raw ] instanceof \Closure || 
									is_callable( $route->patterns[ $raw ] ) )
						{	 
							$result = call_user_func_array( 
								$route->patterns[ $raw ] , array( $uri[ $k ] , $route->route ) );
							if ( !$result ){ return false; }
							static::$_values[ ] = $result;
							continue;
						}
						preg_match( '~' . $route->patterns[ $raw ] . '~' , $uri[ $k ] , $matches );
						if ( empty( $matches ) ){ return false; } // abort if no matches
					}
					static::$_paramValues[ $v ] = $uri[ $k ];
					static::$_cleanParamValues[ $raw ]	= $uri[ $k ];			
					static::$_values[ ] = $uri[ $k ];
					continue;
				}
				if ( $v !== $uri[ $k ] ){ return false; } // uri does not match
			}
			if ( static::$_subdomain ){ static::$_values[ ] = static::$_subdomain; }
			if ( !empty( static::$_cleanParamValues ) ) // set values for the route object
			{ 
				$route->set( 'values' , static::$_cleanParamValues ); 
			}
			return true;
		}
		/**
		*
		*/
		protected static function _checkPatterns( $route )
		{
			if ( $route->patterns ) // check if parameter name is correct for regex
			{
				foreach ( $route->patterns as $prm => $regex )
				{
					if ( !@array_key_exists( $prm , $route->parameters ) )
					{
						trigger_error( 'No parameter defined named ' . $prm . 
									' for regular expression!' , E_USER_ERROR );
						return false;
					}
				}
			}
			return true;
		}
		/**
		*
		*/
		protected static function _addRoute( $route , $callback , $request = 'any' )
		{
			if ( !array_key_exists( static::$_mainGroupName , static::$_groups ) )
			{ 
				$main_group = new GroupRoutes( array( static::$_mainGroupName ) );
				static::$_groups[ static::$_mainGroupName ] = $main_group;
			}
			$current_group = ( !empty( static::$_currentGroups ) ) ? 
				static::$_currentGroups : array( static::$_mainGroupName );
			$options = array // add route startup options
			( 
				'route'	=> static::_cleanRoute( $route , static::$_trailingSlash ), 
				'callback'	=> static::_checkCallback( $callback ) , 
				'request'	=> $request ,
				'protocol'	=> 'any' ,
				'domain'	=> $_SERVER[ 'HTTP_HOST' ]
			);	
			$obj = new Route( $options );
			static::$_groups[ end( $current_group ) ]->add( $obj );
			$msg = 'Added new route <b><i>' . $obj->route . '</i></b>';
			static::_debug( $obj , $msg , 'Router Config' );
			return $obj;
		}
		/**
		*
		*/
		protected static function _checkFileExtension( $uri )
		{
			foreach ( static::$_fileExtensions as $extension )
			{
				$length = strlen( $extension );
				if ( strlen( $uri ) < $length ){ continue; }
				if ( 0 === substr_compare( $uri , $extension , - $length , $length ) )
				{ 
					return $extension; 
				}
			}
			return false;
		}
		/**
		*
		*/
		protected static function _cleanRoute( $route , $trailingSlash = null )
		{
			$route = str_replace( '//' , '/' , $route ); // patch
			if ( '*' === substr( $route , 0 , 1 ) ){ return $route; }
			$route = ( '/' !== substr( $route , 0 , 1 ) ) ? '/' . $route : $route;
			if ( static::_checkFileExtension( $route ) ){ return $route; } 
			if ( is_bool( $trailingSlash ) ) // work with trailing slash if required
			{
				if ( $trailingSlash ) // add trailing slash if not present
				{
					$route = ( '/' !== substr( $route , -1 ) && 
						'*' !== substr( $route ,  -1 ) ) ? $route . '/' : $route;
				}
				else // remove trailing slash if present
				{
					$route = ( '/' === substr( $route , -1 ) ) ? substr( $route , 1 ) : $route;	
				}
			}
			return $route;
		}
		/**
		*
		*/
		protected static function _checkCallback( $callback )
		{
			$call = null;
			if ( $callback instanceof \Closure || is_callable( $callback ) ){ return $callback; }
			else
			{
				$try = explode( '@' , $callback );
				$clean_name = explode( '::' , $try[ 0 ] );
				if ( @class_exists( $clean_name[ 0 ] ) )
				{
					$method = ( sizeof( $try ) > 1 ) ? $try[ 1 ] : 'handle';
					$call = ( false !== strpos( $try[ 0 ] , '::' ) ) ? $try[ 0 ] : array( new $try[ 0 ] , $method );
				}
				else	// no valid callback found
				{
					trigger_error( 'Route ' . $callback . ' is not a valid callback!' , E_USER_ERROR );
					return false;
				}
			}
			return $call;
		}
		/**
		* Send messsages to the PtcDebug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement	some statement if required
		* @param		string		$category		a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 
							'bufferLog' ) ,  array( $string , $statement , $category ) );
		}
	}

	/**
	| ----------------------------------------------------------------------------
	| Group Routes Interface
	| ----------------------------------------------------------------------------
	*/
	
	class GroupRoutes
	{
		/**
		*
		*/
		public function __construct( $groups )
		{
			foreach ( $this->_defaultProperties as $k => $v )
			{ 
				$this->_properties[ $v ] = null; 
			}
			return $this->_properties[ 'groups' ] = $groups;
		}
		/**
		*
		*/
		public function __get( $key )
		{
			if ( !array_key_exists( $key , $this->_properties ) ) // check if property exists
			{
				trigger_error( 'Property ' .$key . 'does not exists!' , E_USER_ERROR );
				return false;
			}
			return $this->_properties[ $key ];
		}
		/**
		*
		*/
		public function prefix( $prefix )
		{
			$this->_properties[ 'prefix' ] = $prefix;
			return $this;
		}
		/**
		*
		*/
		public function domain( $domain )
		{
			$this->_properties[ 'domain' ] = $domain;
			return $this;
		}
		/**
		*
		*/
		public function before( $filters )
		{
			$filters = explode( '|' , $filters ); 
			foreach ( $filters as $filter )
			{ 	
				@$this->_properties[ 'filters' ][ 'before' ][ ] = $filter; 
			}
			return $this;
		}
		/**
		*
		*/
		public function after( $filters )
		{
			$filters = explode( '|' , $filters ); 
			foreach ( $filters as $filter )
			{ 
				@$this->_properties[ 'filters' ][ 'after' ][ ] = $filter; 
			}
			return $this;
		}
		/**
		*
		*/
		public function where( $param , $pattern )
		{
			if ( !$this->_properties[ 'patterns' ] )
			{ 
				$this->_properties[ 'patterns' ] = array( ); 
			}
			$params = ( is_array( $param ) ) ? $param : array( $param => $pattern );
			foreach ( $params as $k => $v )
			{ 
				$this->_properties[ 'patterns' ][ $k ] = $v;	
			}
			return $this;
		}
		/**
		*
		*/
		public function protocol( $protocol )
		{
			$protocols = array( 'any' ,'https' , 'http' );
			if ( !in_array( $protocol , $protocols ) )
			{
				trigger_error( 'Protocol ' . $protocol . ' is invalid!' , E_USER_ERROR );
				return false;
			}
			$this->_properties[ 'protocol' ] = $protocol;
			return $this;
		}
		/**
		*
		*/
		public function add( $route )
		{
			if ( !$this->_properties[ 'routes' ] )
			{ 
				$this->_properties[ 'routes' ] = array( ); 
			}
			//$this->_properties[ 'routes' ][ $route->route ] = $route;
			$this->_properties[ 'routes' ][ ] = $route;
			return $this;
		}
		/**
		*
		*/
		protected $_properties = array( );
		/**
		*
		*/
		protected $_defaultProperties = array
		( 
			'groups' , 'routes' , 'prefix' , 'domain' , 'filters' , 'patterns' , 'protocol'
		);
	}
	
	/**
	| ----------------------------------------------------------------------------
	| Routes Interfacce
	| ----------------------------------------------------------------------------
	*/
	
	class Route
	{
		/**
		*
		*/
		public function __construct( $route )
		{
			foreach ( $route as $k => $v ){ $this->_properties[ $k ] = $v; }
			foreach ( $this->_defaultProperties as $k => $v )
			{
				if ( !@array_key_exists( $v , $this->_properties ) )
				{ 
					$this->_properties[ $v ] = null; 
				}
			}
		}
		/**
		*
		*/
		public function __get( $key )
		{
			if ( !array_key_exists( $key , $this->_properties ) ) // check if property exists
			{
				trigger_error( 'Property ' .$key . 'does not exists!' , E_USER_ERROR );
				return false;
			}
			return $this->_properties[ $key ];
		}
		/**
		*
		*/
		public function set( $property , $value )
		{
			if ( !in_array( $property , $this->_defaultProperties ) ) // check if property exists
			{
				trigger_error( 'Property ' . $property . ' does not exists!' , E_USER_ERROR );
				return $this;
			}
			$this->_properties[ $property ] = $value;
			return $this;
		}
		/**
		*
		*/
		public function where( $param , $pattern = null )
		{
			if ( !is_array( $this->_properties[ 'patterns' ] ) )
			{
				$this->_properties[ 'patterns' ] = array( ); 
			}
			$params = ( is_array( $param ) ) ? $param : array( $param => $pattern );
			foreach ( $params as $k => $v )
			{ 
				$this->_properties[ 'patterns' ][ $k ] = $v; 
			}
			return $this;
		}
		/**
		*
		*/
		public function before( $filters )
		{
			$filters = explode( '|' , $filters ); 
			foreach ( $filters as $filter )
			{ 
				@$this->_properties[ 'filters' ][ 'before' ][ ] = $filter; 
			}
			return $this;
		}
		/**
		*
		*/
		public function after( $filters )
		{
			$filters = explode( '|' , $filters ); 
			foreach ( $filters as $filter )
			{ 
				@$this->_properties[ 'filters' ][ 'after' ][ ] = $filter; 
			}
			return $this;
		}
		/**
		*
		*/
		public function protocol( $protocol )
		{
			$protocols = array( 'any' ,'https' , 'http' );
			if ( !in_array( $protocol , $protocols ) )
			{
				trigger_error( 'Protocol ' . $protocol . ' is invalid!' , E_USER_ERROR );
				return false;
			}
			$this->_properties[ 'protocol' ] = $protocol;
			return $this;
		}
		/**
		*
		*/
		public function parameters( $key , $value )
		{
			if ( !is_array( $this->_properties[ 'parameters' ] ) )
			{ 
				$this->_properties[ 'parameters' ] = array( );
			}
			$this->_properties[ 'parameters' ][  $key ] =  $value;
			return $this;
		}
		/**
		*
		*/
		public function domain( $domain )
		{
			$param = explode( '.' , $domain );
			preg_match( '|{.*?}|' , $param[ 0 ] , $matches ); 
			if ( !empty( $matches ) )
			{
				$match_raw = preg_replace( '#{|}|\?#' , '' , $matches[ 0 ] );
				$this->parameters( $match_raw , $matches[ 0 ] );
			}
			$this->_properties[ 'domain' ] = $domain;
			return $this;
		}
		/**
		*
		*/
		public function map( $name )
		{
			$this->_properties[ 'map' ] = $name;
			return $this;
		}
		/**
		*
		*/
		protected $_properties = array( );
		/**
		*
		*/
		protected $_defaultProperties = array
		( 
			'request' , 'protocol' ,  'domain' , 'route' , 'callback' , 'filters' , 
			'parameters' , 'patterns' , 'executed' , 'map' , 'values' , 'controller'
		);
	}
