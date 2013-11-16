<?
	/**
	* PHP TOOLCASE DATABASE CONNECTION MANAGER CLASS
	* PHP version 5.3
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.9.1b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	
	class PtcDb
	{
		/** 
		* Adds a connection to the connection details array
		* @param	array	$options		the details of the connection
		* @param	string	$name		the name of the connection
		*/
		public static function add( $options , $name = 'default' )
		{
			if( array_key_exists( $name , static::$_connections ) )
			{
				if( array_key_exists( $name , static::$_connections ) )
				{
					trigger_error( 'Connection name "' . $name . 
						'" already exists, use some other name!' , E_USER_ERROR );
					return false;
				}
			}
			foreach( $options as $k => $v )
			{
				if( !array_key_exists( $k , static::$_connectionOptions ) )
				{
					trigger_error( 'Unknown option "' . $k . '" passed as argument to PtcDb!',
																E_USER_WARNING );
				}
			}
			$options['name' ] = $name;
			if( array_key_exists( 'pdo_attributes' , $options ) )
			{
				$options['pdo_attributes' ] = $options['pdo_attributes' ] + 
										static::$_connectionOptions[ 'pdo_attributes' ];
			}
			$options = array_merge( static::$_connectionOptions , $options );
			static::$_connectionsDetails[ $name ] = $options;
			static::_debug( static::$_connectionsDetails[ $name ] , 
					'added connection <b>"' . $name . '"</b>' , 'Connection Manager' );
			return static::$_connectionsDetails[ $name ] = $options;
		}
		/**
		* Retrieves connection details previously configured
		* @param	string	$name	the name of the connection to retrieve
		* @return 	returns the connection if $name is set, otherwise all connections
		*/
		public static function getConnection( $name  = null )
		{
			if( !$name ){ return static::$_connectionsDetails; } // return all connections
			if( !static::_checkConnectionName( $name ) ){ return false; }
			return static::$_connectionsDetails[ $name ];
		}
		/**
		* Retrieves the Pdo object
		* @param	string 	$name 	the name of the connection
		*/
		public static function getPdo( $name )
		{
			if ( !static::_checkConnectionName( $name ) ){ return false; }
			static::_initializeConnection( $name );
			return static::$_connections[ $name ][ 'pdo_object' ];
		}
		/**
		* Retreive the query builder object if present
		* @param	string	$name	the name of the connection
		*/
		public static function getQB( $name )
		{
			if( !static::_checkConnectionName( $name ) ){ return false; }
			static::_initializeConnection( $name );
			if( !static::$_connectionsDetails[ $name ][ 'query_builder' ] )
			{
				trigger_error( 'QueryBuilder was not set for connection "' . $name . 
														'"!', E_USER_ERROR );
				return false;
			}
			return $connection = static::$_connections[ $name ][ 'query_builder' ];
			//return function ( ) use ( $connection ){ return $connection }; // lambda time!
		
		}
		/**
		* Calls methods from the default connection
		* @param	string	$method		the name of the method to call
		* @param	array	$args		arguments for the method
		*/
		public static function __callStatic( $method , $args = null )
		{
			$name = 'default'; // use the default connection
			if ( !static::_initializeConnection( $name ) ){ return false; }
			if ( $qb =@static::$_connections[ $name ][ 'query_builder' ] ) // call query builder
			{
				if( in_array( $method , get_class_methods( $qb ) ) )
				{
					return call_user_func_array( array( $qb , $method ) , $args );
				}
			}
			else // call the pdo object methods
			{
				$pdo =static::$_connections[ $name ][ 'pdo_object' ];
				return call_user_func_array( array(  $pdo , $method ) , $args );
			}
			trigger_error( 'Call to undefined method "' .$method . '"!' , E_USER_ERROR );
			return false;
		}
		/** 
		* Connections options property
		*/
		protected static $_connectionOptions = array
		(
			'name'				=>	'default' ,
			'driver'    				=> 	'mysql' ,
			'user'				=>	'root' ,
			'pass'				=>	'' ,
			'host'				=>	'localhost' ,
			'db'					=>	'database' ,
			'charset'   			=> 	'utf8' ,
			'query_builder'			=>	false ,
			'query_builder_class'	=>	'PtcQueryBuilder' ,
			//'collation' 			=> 	'utf8_unicode_ci' ,
			//'prefix'    				=> 	'' , 
			'pdo_attributes'		=>	array
			( 
				PDO::ATTR_ERRMODE 			=> 	PDO::ERRMODE_WARNING ,
				PDO::ATTR_DEFAULT_FETCH_MODE 	=> 	PDO::FETCH_OBJ
			)
		);
		/**
		* Pdo and query builder objects property
		*/
		protected static $_connections = array( );
		/**
		* Connection details property
		*/
		protected static $_connectionsDetails = array( );
		/**
		* Initializes the pdo and query builder obejcts
		* @param	string	$name	the name of the connection
		*/
		protected static function _initializeConnection( $name )
		{
			if ( !array_key_exists( $name , static::$_connections ) )
			{
				$options = static::$_connectionsDetails[ $name ];
				static::$_connections[ $name ][ 'pdo_object' ] = new \PDO( 
							static::_pdoDriver( $options[ 'driver' ] , $options[ 'host' ] ) . 
								';dbname=' . $options[ 'db' ] . ';charset:' . $options[ 'charset' ] .';' , 
														$options[ 'user' ] , $options [ 'pass' ] );
				if ( !static::$_connections[ $name ][ 'pdo_object' ]){ return false; } // pdo failed
				foreach ( $options[ 'pdo_attributes' ] as $k => $v )
				{
					static::$_connections[ $name ][ 'pdo_object' ]->setAttribute( $k , $v );
				}
				if ( $options[ 'query_builder' ] )
				{
					$qb =false;
					if ( class_exists( $options[ 'query_builder_class' ] ) || 
					file_exists( dirname(__FILE__) . $options[ 'query_builder_class' ] . '.php' ) ){ $qb = true; }
					if ( !$qb )
					{
						trigger_error( 'Class "' . $options[ 'query_builder_class' ] . 
								'" not found , please include the class file manually!' , E_USER_ERROR );
						return false;
					}
					static::$_connections[ $name ][ 'query_builder' ] = 
						new $options[ 'query_builder_class' ]( static::$_connections[ $name ][ 'pdo_object' ] );
				}
				static::_debug( array( 'details' => static::$_connectionsDetails[ $name ] , 
						'connection' => static::$_connections[ $name ] ) , 'connection <b>"' . 
											$name . '"</b> initialized' , 'Connection Manager' );
			}
			return true;
		}
		/**
		* Checks if a given connection exists
		* @param	string	$name	the name of the connection to check
		*/
		protected static function _checkConnectionName( $name )
		{
			if ( !array_key_exists( $name , static::$_connectionsDetails ) )
			{
				trigger_error( 'Could not find connection details with name "' . $name . '"!' , 
																E_USER_ERROR );
				return false;
			}
			return true;
		}	
		/**
		* Builds the pdo driver
		* @param	string	$driver	the driver type
		* @param	string	$host	the database server host
		*/
		protected static function _pdoDriver( $driver , $host )
		{
			switch( $driver )
			{
				case 'mysql' :
				default : return 'mysql:host=' . $host;
			}
		}
		/**
		* Send messsages to the PtcDebug class if present and it\'s namespace
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement if required
		* @param	string	$category	a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ) { return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 'bufferSql' ) ,  
											array( $string , $statement , $category )  );
		}
	}