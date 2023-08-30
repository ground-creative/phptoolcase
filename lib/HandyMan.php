<?php

	namespace phptoolcase;

	/**
	* PHP TOOLCASE HANDYMAN CLASS
	* @category 	Library
	* @version	v1.1.8
	* @author   	Carlo Pietrobattista <carlo@ground-creative.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	class HandyMan			
	{
		/**
		* Alias of @ref addDirs()
		*/
		public static function addDir( $path ) { static::addDirs( $path ); }
		/**
		* Alias of @ref addFiles()
		*/
		public static function addFile( $file ){ static::addFiles( $file ); }
		/**
		* Alias of @ref addSeparator()
		*/
		public static function addSeparator( $sep ) { static::addSeparators( $sep ); }
		/**
		* Alias of @ref addConventions()
		*/
		public static function addConvention( $conventions ) 
		{ 
			static::addConventions( $conventions ); 
		}
		/**
		* Alias of @ref addAppPaths()
		*/
		public static function addAppPath( $path ) { static::addAppPaths( $path ); }
		/**
		* Alias of @ref getAppPaths()
		*/
		public static function getAppPath( $type = null ) { return static::getAppPaths( $type ); }
		/**
		* Retrieves an element of the given array using dot-notation
		* @param	array	$array			the array where to look in
		* @param	string	$path			the search path
		* @param	string	$defaultValue		default value returned if key is not found
		* @param	string	$delimiter		the delimiter to use
		* @return	the value if the array key is found, the defaultValue otherwise
		*/
		public static function arrayGet( array &$array , $path , $defaultValue = null , $delimiter = '.' )
		{
			$keys = explode( $delimiter , $path );
			foreach ( $keys as $k )
			{
				if ( isset( $array[ $k ] ) ){ $array = &$array[ $k ]; }
				else{ return $defaultValue; }
			}
			return $array;
		}
		/**
		* Sets an element of the given array using dot-notation
		* @param	array	$array		the array where to look in
		* @param	string	$path		the search path
		* @param	string	$value		the value to set
		* @param	string	$force		overwrite value if set already
		* @param	string	$delimiter	the delimiter to use
		* @return	true if value has been set, false if already exists
		*/
		public static function arraySet( array &$array , $path , $value , $force = false , $delimiter = '.' ) 
		{
			$keys = explode( $delimiter , $path );
			$last = array_pop( $keys );
			foreach ( $keys as $k )
			{
				if ( isset( $array[ $k ] ) && is_array( $array[ $k ] ) ){ $array = &$array[ $k ]; }
				else
				{
					$array[ $k ] = array( );
					$array = &$array[ $k ];
				}
			}
			if ( isset( $array[ $last ] ) && !$force )
			{
				trigger_error( 'Array key is already set, ' . 
					'use $force argument to overwrite!' , E_USER_WARNING );
				return false;
			}
			$array[ $last ] = $value;
			return true;
		}
		/**
		* Counts elements of the given array using dot-notation
		* @param	array	$array		the array where to look in
		* @param	string	$path		the search path
		* @param	string	$delimiter	the delimiter to use
		* @return	the number of values inside the array element
		*/
		public static function arrayCount( array &$array , $path , $delimiter = '.' )
		{
			$keys = explode( $delimiter , $path );
			$last = array_pop( $keys );
			foreach ( $keys as $k )
			{
				if ( isset( $array[ $k ] ) && is_array( $array[ $k ] ) ){ $array = &$array[ $k ]; }
				else{ return null; }
			}
			return isset( $array[ $last ] ) && is_array( $array[ $last ] ) ? count( $array[ $last ] ) : null;
		}
		/**
		* Removes an the element of the given array using dot-notation.
		* @param	array	$array		the array where to look in
		* @param	string	$path		the search path
		* @param	string	$delimiter	the delimiter to use
		* @return	true if element has been unset, false otherwise
		*/
		public static function arrayDel( array &$array , $path , $delimiter = '.' )
		{
			$keys = explode( '.' , $path );
			$last = array_pop( $keys );
			foreach ( $keys as $k )
			{
				if ( isset( $array[ $k ] ) && is_array( $array[ $k ] ) ){ $array = &$array[$k]; }
				else{ return false; }
			}
			unset( $array[ $last ] );
			return true;
		}
		/**
		* Creates an alias of a class
		* @param	array	$aliases	an array with aliases and class names
		*/
		public static function addAlias( array $aliases , $load = false )
		{
			foreach ( $aliases as $k => $v )
			{
				if ( array_key_exists( $k , static::$_aliases ) )
				{
					trigger_error( 'Alias name ' . $k . 
						' already exists!' , E_USER_WARNING );
					continue;
				}
				if ( class_exists( $k ) )
				{
					trigger_error( 'Cannot use ' . $k . 
						' as alias, a class with this name' . ' already exists!' , E_USER_WARNING );
					continue;
				}
				if ( $load ){ class_alias( $v , $k , true ); }
				static::$_aliases[ $k ] = $v;
				static::_debug( $v , 'Added new alias ' . $k . ' for class', 'Autoloader Config' );
			}
			return true;
		}
		/**
		* Retrieves assigned class aliases
		* @param	string	$alias	some previously configured class alias
		*/
		public static function getAlias( $alias = null )
		{
			return ( $alias ) ? static::$_aliases[ $alias ] : static::$_aliases; 
		}
		/**
		* Retrieves a session variable
		* @param	string	$path			the session key
		* @param	mixed	$defaultValue		default value to return if key is not found
		* @return	the session key if found, the default value otherwise
		*/
		public static function sessionGet( $path = null , $defaultValue = null ) 
		{
			return ( $path ) ? static::arrayGet( $_SESSION , $path , $defaultValue ) : $_SESSION;
		}
		/**
		* Sets a session variable
		* @param	string	$path	the session key
		* @param	mixed	$value	the value to set
		* @param	mixed	$force	overwrites previous value if set
		*/
		public static function sessionSet( $path , $value , $force = false )
		{
			static::session( 'start' ); // start session if not started
			if ( static::arrayGet( $_SESSION , $path ) && !$force )
			{
				trigger_error( 'Session key ' . $path . 
					' already set, use $force argument maybe?' , E_USER_ERROR );
				return false;
			}
			return static::arraySet( $_SESSION , $path , $value , $force );
		}
		/**
		* Removes a session key
		* @param	string	$path	the session key to remove
		*/
		public static function sessionDel( $path ){ return static::arrayDel( $_SESSION , $path ); }
		/**
		* Intercats with the session functions
		* @param	string	$type	the name of the function
		*/
		public static function session( $type )
		{
			switch ( $type )
			{
				case 'start' : 
					if ( session_id( ) === '' )
					{ 
						$debug_config = null;
						static::_debug( session_id( ) , 'Initializing session!' , 'Session Manager' );
						if ( isset( $_SESSION[ 'ptcdebug' ] ) ){ $debug_config = $_SESSION[ 'ptcdebug' ]; }
						session_start( ); 
						if ( $debug_config ){ $_SESSION[ 'ptcdebug' ] = $debug_config; }
					} 
				break;
				case 'destroy' : 
					if ( session_id( ) !== '' )
					{
						static::_debug( session_id( ) , 'Destroying session!' , 'Session Manager' );
						session_destroy( ); 
					} 
				break;
				case 'close' : 
					if ( session_id( ) !== '' )
					{
						static::_debug( session_id( ) , 'Closing session write!' , 'Session Manager' );
						session_write_close( ); 
					} 
				break;
				default :
					static::_debug( '' , 'Session option "' . $type . '" is not supported!' , 'Session Manager' );
			}
		}
		/**
		* Creates a json or a jsonp response with the passed data
		* @param	array	$data		the data to convert to a json
		* @param	mixed	$callback		the name of the callback parameter
		* @param	mixed	$sendHeader	send the response header for the json
		* @return	the jsonp if $callback parameter is set, the json otherwise
		*/
		public static function json( array $data , $callback = null , $sendHeader = true )
		{
			if ( $sendHeader )
			{ 
				if ( is_string( $sendHeader ) ){ header( 'Content-Type: ' .$sendHeader ); }
				else if ( $callback ){ header( 'Content-Type: application/javascript' ); }
				else{ header( 'Content-Type: application/json' ); }
			}
			return ( $callback ) ? $_GET[ $callback ] . '(' . json_encode( $data ) . ')' : json_encode( $data );
		}
		/**
		* Adds path(s) to the HandyMan::$_dirs property to load classes when needed. 
		* See @ref add_dirs and @ref namespace_dirs
		* @param	string|array		$paths	full path(s) to directories with classes
		*/
		public static function addDirs( $paths )
		{
			if ( !is_array( $paths ) ) { $paths = array( $paths ); }
			foreach ( $paths as $k => $v )
			{
				//$path = realpath( $v );		// get real os path
				if ( $path = static::_realpath( $v ) )	// if path exists
				{	
					$result = ( !is_int( $k ) ) ? static::_addNamespaceDirectory( 
						str_replace( '\\' , '' , $k ) , $path ) : static::_addDirectory( $path );
					if ( !$result ) { unset( $paths[ $k ] ); continue; }
				}
				else						// need to trigger error if path is not found
				{
					trigger_error( 'The path "' . $v . 
						'" does not exists or is not accessible!' , E_USER_ERROR );
				}
			}
			if ( $paths ) // debug
			{ 
				static::_debug( $paths , 'Added path(s) to autoloader' , 'Autoloader Config' ); 
			} 
		}
		/**
		* Adds class file(s) to the autoloader. See @ref add_files
		* @param	string		$files		full path of the class file		
		*/
		public static function addFiles( $files )
		{
			if ( !is_array( $files ) ) { $files = array( $files ); }
			foreach ( $files as $k => $v )
			{
				//$file = realpath( $v );		// get real OS path
				if( $file = static::_realpath( $v ) )		// path exists
				{	
					$key = ( substr( $k , 0 , 1 ) == '\\' ) ? substr( $k , 1 ) : $k; // remove first '\'  if present
					if ( @array_key_exists( $key , @static::$_dirs[ 'files' ] ) )  // check if name exists already
					{
						trigger_error( 'Cannot redeclare "' . $k . '" as class name!' , E_USER_ERROR );
						unset( $files[ $k ] );
						continue;
					}
					@static::$_dirs[ 'files' ][ $key ] = $file;
				}
				else { trigger_error( 'the file "' . $v . '" cannot be found!' , E_USER_ERROR ); } // file not found
			}
			if ( $files ){ static::_debug( $files , 'Added file(s) to autoloader' , 'Autoloader Config' ); } // debug
		}
		/** 
		* Registers the autoloader to load classes when needed. See @ref hm_getting_started
		* @param	bool		$addThisPath			adds the path where the class resides as a directory
		* @param	bool		$useHelpers			loads the shortcuts.php file if found
		* @param	bool		$registerAutoLoader	registers the load method with the spl utilities and the event class if present
		*/
		public static function register( $addThisPath = true , $useHelpers = true , $registerAutoLoader = true )
		{
			$this_class = get_called_class( );
			if ( $addThisPath )	// add this path
			{
				$this_dir = ( is_string( $addThisPath ) ) ? 
							[ $addThisPath => dirname( __FILE__ ) ] : dirname( __FILE__ );
				static::addDir( $this_dir ); 
			}	
			if ( $registerAutoLoader ) { spl_autoload_register( [ $this_class , 'load' ] ); }
			if ( $useHelpers && file_exists( dirname( __FILE__ ) . '/shortcuts.php' ) ) // add helpers if found
			{ 
				require_once( dirname( __FILE__ ) . '/shortcuts.php' ); 
			}
			if ( $registerAutoLoader && file_exists( dirname( __FILE__ ) . '/Event.php' ) ){ Event::register( ); }
			//$namespace = @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
			if ( !defined( '_PTCHANDYMAN_' ) ){ @define( '_PTCHANDYMAN_' , $this_class ); }
			$debug = [ $addThisPath , $useHelpers , $registerAutoLoader , static::getDirs( ) ];
			static::_debug( $debug , '<b>Autoloader registerd!<b>' , 'Autoloader Registered' );
		}
		/**
		* Retrieves the separators used by the autoloader
		*/
		public static function getSeparators( ){ return static::$_separators; }
		/**
		* Retrieves the naming conventions used by the autoloader
		*/		
		public static function getConventions( ){ return static::$_namingConventions; }
		/**
		* Adds a separator to the  HandyMan::$_separators property for the autoloader. 
		* See @ref using_separators
		* @param	array|string		$sep		the separator(s) to be added
		*/
		public static function addSeparators( $sep )
		{
			$seps = ( is_array( $sep ) ) ? $sep : array( $sep );
			foreach ( $seps as $k => $v )
			{
				if ( in_array( $v , static::$_separators ) )
				{
					static::_debug( 'Separator "' . $v . ' " already present!' , '' , 'Autoloader Warning' );	
					continue;
				}
				static::$_separators[ ] = $v; 
			}
		}
		/**
		* Adds naming convention(s) to the HandyMan::$_namingConventions property for the autoloader.
		* See @ref using_separators
		* @param	array|string		$conventions		the naming convention(s) to be added
		*/
		public static function addConventions( $conventions )
		{
			$conventions = ( is_array( $conventions ) ) ? $conventions : array( $conventions );
			foreach ( $conventions as $k => $v)
			{
				if ( in_array( $v, static::$_namingConventions ) )
				{
					static::_debug( 'Naming convention"' . $v . 
						' " already present!' , '' , 'Autoloader Warning' );	
					continue;
				}
				static::$_namingConventions[ ] = $v; 
			}
		}
		/**
		* Returns the current included paths for  the autoloader. See @ref getDirs
		* @param	string		$type		directories , ns , files
		* @return	the HandyMan::$_dirs property based on the $type argument
		*/
		public static function getDirs( $type = null )
		{ 
			if ( $type && !in_array( $type , [ 'directories' , 'ns' , 'files' ] ) )
			{ 
				trigger_error( 'No directories are present with the "' . 
								$type . '" parameter!' , E_USER_WARNING );
				return;
			}
			else if ( $type && !@static::$_dirs[ $type ] ) { return null; } // no values in array
			if ( @static::$_dirs[ $type ] ) {  return static::$_dirs[ $type ]; } // return value
			return @static::$_dirs;								// return all values
		}
		/**
		* Helper method to retrieve paths for the application. See @ref usingAddedPath
		* @param	string		$name		the path to return stored in the HandyMan::$_appPaths property
		* @return	the HandyMan::$_appPaths property based on the $name argument
		*/
		public static function getAppPaths( $name = null )
		{
			if ( empty( static::$_appPaths ) ) { static::_buildAppPaths( ); } // build paths once
			if ( !$name ) { return static::$_appPaths; } // return all application paths by default
			if ( !@array_key_exists( $name , static::$_appPaths ) )
			{
				trigger_error( 'No paths found with the option "' . $name . '"!' , E_USER_WARNING );
				return;
			}
			return static::$_appPaths[ $name ];
		}
		/**
		* Adds paths to the HandyMan::$_appPaths property for later usage. See @ref addingAppPath 
		* @param	array 		$paths		array of paths to add
		*/
		public static function addAppPaths( $paths )
		{
			if ( empty( static::$_appPaths ) ) { static::_buildAppPaths( ); } // build paths once
			foreach ( $paths as $k => $v )
			{
				if ( !$path = @static::_realpath( $v ) )
				{
					trigger_error( 'The file or path "' . $v .
						'" does not exists or is not accessible!' , E_USER_ERROR ); 
					return;
				}
				@$add_paths[ $k ] = $path; 
			}
			static::$_appPaths = array_merge( static::$_appPaths , $add_paths );
			static::_debug( $add_paths , 'Added path(s) to autoloader' , 'Paths Manager' );
		}
		/**
		* Retrieves inaccessible properties from a class or object. See @ref read_properties
		* @param	mixed		$object		the name of the class or the initialized object
		* @param	string		$propertyName	the name of the property
		* @return	the value of the property if found.
		*/
		public static function getProperty( $object , $propertyName )
		{
			if ( !$object ){ return null; }
			if ( is_string( $object ) )	// static property
			{
				if ( !class_exists( $object ) ){ return null; }
				$reflection = new \ReflectionProperty( $object , $propertyName );
				if ( !$reflection ){ return null; }
				$reflection->setAccessible( true );
				return $reflection->getValue( );
			}
			$class = new \ReflectionClass( $object );
			if ( !$class ){ return null; }
			if( !$class->hasProperty( $propertyName ) ) // check if property exists
			{
				trigger_error( 'Property "' . 
					$propertyName . '" not found in class "' . 
					get_class( $object ) . '"!' , E_USER_WARNING );
				return null;
			}
			$property = $class->getProperty( $propertyName );
			$property->setAccessible( true );
			return $property->getValue( $object );
		}		
		/**
		* Load classes automatically with namespaces support based on folder structure. 
		* See @ref adding_dirs
		* @param	string 	$class		the name of the class to autoload
		* @return	true if a file has been loaded, false otherwise.
		*/
		public static function load( $class )
		{
			/* check if wa have an alias with this class name */
			if ( isset( static::$_aliases[ $class ] ) )
			{ 
				class_alias( static::$_aliases[ $class ] , $class , true );
				return;
			}
			/* check files array first  if class matches any name */
			if ( @array_key_exists( $class , @static::$_dirs[ 'files' ] ) )
			{
				$msg = array( 'file' => static::$_dirs[ 'files' ][ $class ] , 'class' => $class );
				static::_debug( $msg , 'Included class file' , 'Autoloader Action' );
				require_once( static::$_dirs[ 'files' ][ $class ] );
				return true;
			}
			else if ( strpos( $class , '\\' ) )	// try namespace first
			{
				$folders = explode( '\\' , $class );
				if ( @static::$_dirs[ 'ns' ][ $folders[ 0 ] ] )
				{
					$path = static::$_dirs[ 'ns' ][ $folders[ 0 ] ];
					unset( $folders[ 0 ] );
					$class_name = end( $folders );
					if ( sizeof( $folders ) > 1 )
					{
						array_pop( $folders );
						foreach ( $folders as $k => $v ) { $path .= DIRECTORY_SEPARATOR . $v; }
					}
					if ( !@static::_realpath( $path ) )
					{
						trigger_error( 'The path "' . $path . 
							'" does not exists or is not accessible!' , E_USER_ERROR ); 
						return false;
					}
					if ( $found = static::_loadClass( $class_name , $path , $class ) ) { return true; }
				}
			}
			else if ( @static::$_dirs[ 'directories' ] )	// try all paths in directories array
			{
				foreach ( static::$_dirs[ 'directories' ] as $k => $v ) 
				{ 
					if ( $found = static::_loadClass( $class , $v ) ) { return true; } 
				}
			}
			return false;
		}
		/**
		* Paths for directories to autoload classes
		*/
		protected static $_dirs = array();
		/**
		* Separators for naming conventions
		*/
		protected static $_separators = array( );
		/**
		* Naming conventions to attempt to load with the autoloader
		*/		
		protected static $_namingConventions = array( );
		/**
		* Application paths property
		*/
		protected static $_appPaths = array( );
		/**
		* Aliases for class names property
		*/
		protected static $_aliases = array( );
		/**
		* Custom realpath( ) you can add an event listener here
		* @param	string	$path	the full path to a directory
		*/
		protected static function _realpath( $path )
		{
			if ( $result = realpath( $path ) ){ return $result; } 
			if ( class_exists( __NAMESPACE__ . '\Event' ) )
			{
				$listener = Event::get( 'autoloader' );
				if ( isset( $listener[ 'realpath' ] ) )
				{
					$p_path = $path;
					Event::fire( 'autoloader.realpath' , array( &$path ) );
					return ( $path !== $p_path ) ? $path : $result;
				}
			}
			return false;
		}
		/**
		* Classes autoloader engine, tries various possibilities for file names
		* @param	string		$class		the class name without namespaces
		* @param	string		$path		the full path to the file
		* @param	string		$namespace	the class name with namespace if present
		* @return	true if a file is found, false otherwise
		*/
		protected static function _loadClass( $class , $path , $namespace = null )
		{
			$class_name = ( $namespace ) ? $namespace : $class;
			$path = $path . DIRECTORY_SEPARATOR;
			if ( file_exists( $path . $class . '.php' ) )	// try the file
			{
				$dbg = array( 'file' => $path . $class . '.php' , 'class' => $class_name );
				static::_debug( $dbg , 'Included class file' , 'Autoloader Action' ); // debug
				require_once( $path . $class . '.php');
				return true;
			}
			else if ( file_exists( $path . $new_file = strtolower( $class ) . '.php' ) )	// try the file lowercase
			{
				$dbg = array( 'file' => $path . $new_file , 'class' => $class_name );
				static::_debug( $dbg , 'Included class file' , 'Autoloader Action' ); // debug
				require_once( $path . $new_file );
				return true;
			}
			else{ return static::_guessFileName( $class , $path , $namespace ); } // use an engine to guess the file
			return false;
		}
		/**
		* Tries to guess the filename to load based on the naming conventions and the separator properties
		* @param	string		$class		the class name without namespaces
		* @param	string		$path		the full path to the file
		* @param	string		$namespace	the class name with namespace if present
		* @return	true if a file is found, false otherwise
		*/
		protected static function _guessFileName( $class , $path , $namespace = null )
		{
			$class_name = ( $namespace ) ? $namespace : $class;
			foreach ( static::$_separators as $sep )	// try separators and naming conventions combinations
			{
				foreach ( static::$_namingConventions as $convention )
				{
					$filename = str_replace( array( '{SEP}' , '{CLASS}' ) , 
										array( $sep , $class ) , $convention );
					if ( file_exists( $path . $filename  . '.php') ) // try new filename
					{
						static::_debug( array( 'file' => $path . $filename . '.php' , 
							'class' => $class_name ) , 'Included class file' , 'Autoloader Action' ); // debug
						require_once(  $path . $filename .'.php');
						return true;
					}
					else if ( file_exists( $path .  $new_file = strtolower( $filename ) . '.php') ) // try lower case
					{
						static::_debug( 
							array( 'file' => $path . $new_file  , 'class' => $class_name ) , 
								'Included class file' , 'Autoloader Action' ); // debug
						require_once(  $path . $new_file );
						return true;
					}
					else if ( $sep != '_')	// try replacing "_" with other separators in class name with lowercase
					{
						$replaced = str_replace( '_' , $sep , $filename ); // try new file name
						if ( file_exists( $path . $replaced . '.php' ) )
						{
							static::_debug( array( 'file' =>$path . $replaced . '.php' , 
									'class' => $class_name ) , 'Included class file','Autoloader Action' );	// debug
							require_once( $path . $replaced . '.php'); 
							return true; 
						}
						else if ( file_exists( $file = $path . strtolower( $replaced ) . '.php' ) )	// lowercase
						{
							static::_debug( array( 'file' => $file , 
								'class' => $class_name ) , 'Included class file' , 'Autoloader Action' ); // debug
							require_once( $file ); 
							return true; 
						}
					}
				}
			}
			return false;
		}
		/**
		* Builds application paths for the first time when HandyMan::getAppPath( ) is called.
		*/
		protected static function _buildAppPaths( )
		{
			static::$_appPaths = array
			(
				'docRoot' 	=> 	@$_SERVER[ 'DOCUMENT_ROOT' ] , // the document root folder
				'handyMan'	=>	dirname( __FILE__ ) // the directory where this file resides
			);
		}
		/**
		* Adds a directory to the HandyMan::$_dirs array to autoload classes
		* @param	string		$directory		the full path to the directory
		* @return	false if directory is already present, true otherwise
		*/
		protected static function _addDirectory( $directory )
		{
			if ( @in_array( $directory , @static::$_dirs[ 'directories' ] ) ) // check if dir is already present
			{
					static::_debug( $directory , 'Path already exists!' , 'Autoloader Warning' );	// debug
					return false;
			}
			@static::$_dirs[ 'directories' ][ ] = $directory;
			return true;
		}
		/**
		* Adds a directory to the HandyMan::$_dirs array to load classes that use namespaces
		* @param	string		$namespace		the namespace for the path toa utoload classes
		* @param	string		$directory		the full path to the directory
		* @return	true if directory is not present, triggers an error otherwise 
		*/		
		protected static function _addNamespaceDirectory( $namespace , $directory )
		{
			if ( @array_key_exists( $namespace , @static::$_dirs[ 'ns' ] ) ) // check if dir is already present
			{
				trigger_error( 'Cannot redeclare namespace "' . $namespace . '"!' , E_USER_ERROR );
				return false;
			}
			@static::$_dirs[ 'ns' ][ $namespace ] = $directory;
			return true;
		}
		/**
		* Send messsages to the Debug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement		some statement if required
		* @param	string		$category		a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ) { return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 'bufferLog' ) ,  
											array( $string , $statement , $category )  );
		}
	}