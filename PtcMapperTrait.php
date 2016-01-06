<?php

	/**
	* PHP TOOLCASE OBJECT RELATIONAL MAPPING CLASS
	* PHP version 5.4
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.9.1b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	trait PtcMapperTrait
	{
		/**
		* Retrive the query builder from the connection manager and table column names 
		*/
		public function __construct( ){ static::_initialize( ); }
		/**
		* Resets array of values
		*/
		public function reset( ) { $this->_fields = array( ); }
		/**
		* Remove value from fields property
		* @param	string		$key		the table column name
		*/
		public function remove( $key ) { unset( $this->_fields[ $key ] ); }
		/**
		*
		*/
		public function toArray( ){ return $this->_fields; }
		/**
		*
		*/
		public function toJson( ){ return json_encode( $this->_fields ); }
		/**
		* Sets values based on associative array
		* @param	array		$array	associative array with values
		*/
		public static function create( $array ) 
		{ 
			$class = get_called_class( );
			$record = new $class( );
			foreach ( $array as $k => $v ){ $record->$k = $v; }
			return $record;
		}
		/**
		*
		*/
		public static function lastId( )
		{ 
			static::_initialize( );
			return static::$_db->lastId( ); 
		}
		/**
		* Deletes record in table based on id
		* @param
		* @param
		*/
		public function delete( )
		{
			static::$_uniqueKey = static::_getProperty( 'uniqueKey' );
			static::_fireEvent( 'deleting' , 
					array( &$this->_fields[ static::$_uniqueKey ] , &$this->_fields ) );
			$result = static::$_db->table( static::$_table )
							 ->delete( $this->_fields[ static::$_uniqueKey ] )
							 ->run( );	 
			static::_fireEvent( 'deleted' , 
				array( &$this->_fields[ static::$_uniqueKey ] , &$this->_fields , &$result ) );
			//$this->reset( );	// reset fields
			return $result;
		}
		/**
		* Inserts a new record in table
		*/
		public function save( )
		{
			if ( empty( $this->_fields ) )
			{
				trigger_error( 'Nothing to save in table' . static::$_table . '!' , E_USER_WARNING );
				return false;
			}
			static::_mapFields( );
			$values = $this->_fields;
			static::_fireEvent( 'saving' , array( &$values ) );
			static::$_uniqueKey = static::_getProperty( 'uniqueKey' );
			if ( array_key_exists( static::$_uniqueKey , $this->_fields ) ) // update record
			{
				static::_fireEvent( 'updating' , array( &$values ) );
				unset( $values[ static::$_uniqueKey ] );
				$result = static::$_db->table( static::$_table )
								->update( $values , $this->_fields[ static::$_uniqueKey ] )
								->run( );
				static::_fireEvent( 'updated' , array( &$values , &$result ) );
			}
			else // insert new row
			{
				static::_fireEvent( 'inserting' , array( &$values ) );
				$result = static::$_db->table( static::$_table )->insert( $this->_fields )->run( ); 
				static::_fireEvent( 'inserted' , array( &$values , &$result ) );
			}
			static::_fireEvent( 'saved' , array( &$values , &$result ) );
			//$this->reset( );	// reset fields
			return $result;
		}
		/**
		* Retrieves a record from the table
		* @param
		*/
		public static function find( $id )
		{
			$class = static::_initialize( );
			static::$_db->setFetchMode( PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE , $class );
			return $result = static::$_db->table( static::$_table )
								    ->where( 'id' , '=' , $id )
								    ->row( );
		}
		/**
		*
		*/
		public static function all( )
		{
			$class = static::_initialize( );
			static::$_db->setFetchMode( PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE , $class );
			return $result = static::$_db->table( static::$_table )->run( );
		}
		/**
		* Retrieve column names for table
		* @param	string		$table		the table name
		*/
		public static function getColumns( )
		{
			if ( !static::$_db ) { static::_initialize( ); }
			if ( static::$_columns ){ return static::$_columns; }
			$qb = static::$_db;
			$fields = array( );
			$columns = $qb->run( 'SHOW COLUMNS FROM ' . $qb->sanitize( static::$_table ) );
			$result_type = 'object'; // start with object as default result type
			if ( @is_array( $columns[ 0 ] ) ){ $result_type = 'array'; }
			switch( $result_type )
			{
				case 'array' :
					foreach ( $columns as  $name )
					{
						static::$_columns[ $name[ 'Field' ] ] = $name[ 'Field' ]; 
					}
				break;
				case 'object' :
				default :
					foreach ( $columns as  $name )
					{
						static::$_columns[ $name->Field ] = $name->Field; 
					}
			}
			return static::$_columns;
		}
		/**
		*
		*/
		public static function observe( $class = null )
		{
			if ( !class_exists( $events_class = static::_getProperty( 'eventClass' ) ) )
			{
				trigger_error( $events_class . ' NOT FOUND!' , E_USER_ERROR );
				return false;
			}
			$class = ( $class ) ? $class : get_called_class( );
			$methods = get_class_methods( $class );
			foreach ( static::$_events as $event )
			{
				if ( in_array( $event , $methods ) )
				{
					$cls = strtolower( $class );
					$events_class::listen( $cls . '.' . $event , $class . '::' . $event );
					static::$_observers[ $cls . '.' . $event ] = $event;
				}
			}
		}
		/**
		*
		*/
		public function __set( $key , $value )
		{
			if ( !static::_checkColumn( $key ) ){ return false; }
			return $this->_fields[ $key ] = $value;
		}
		/**
		*
		*/
		public function __get( $key )
		{
			if ( !static::_checkColumn( $key ) ){ return false; }
			return $this->_fields[ $key ];
		}
		/**
		*
		*/
		public static function __callStatic( $method , $args )
		{
			$class = static::_initialize( );
			if ( strpos( $method , 'get_' ) === 0 )
			{
				$meth = explode( 'get_' , $method );
				if ( !static::_checkColumn( $meth[ 1 ] ) ){ return false; }
				$column = ( !array_key_exists( 1 , $args ) ) ? static::$_uniqueKey : $args[ 0 ];
				$value = ( !array_key_exists( 1 , $args ) ) ? $args[ 0 ] : $args[ 1 ];
				return static::$_db->table( static::$_table )
							     ->where( $column , '=' , $value )
							     ->row( $meth[ 1 ] );
			}
			else if ( strpos( $method , 'set_' ) === 0 )
			{
				$meth = explode( 'set_' , $method );
				if ( !static::_checkColumn( $meth[ 1 ] ) ){ return false; }
				static::_fireEvent( array( 'saving' , 'updating' ) , array( &$meth , &$args ) );			     
				$result = static::$_db->table( static::$_table )
							     ->where( static::$_uniqueKey , '=' , $args[ 1 ] )
							     ->update( array( $meth[ 1 ] => $args[ 0 ] ) )
							     ->run( );
				static::_fireEvent( array( 'updated' , 'saved' ) , array( &$meth , &$args , &$result ) );
				return $result;
			}
			$qb = static::$_db->table( static::$_table );
			$qb->setFetchMode( PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE , $class );
			return call_user_func_array( array( $qb , $method ), $args );
		}
		/**
		*
		*/
		protected static $_eventClass = null;
		/**
		*
		*/
		protected static $_table = null;
		/**
		*
		*/
		protected static $_connectionManager = null;
		/**
		*
		*/
		protected static $_connectionName = null;
		/**
		*
		*/
		protected static $_uniqueKey = null;		
		/**
		*
		*/
		protected static $_map = null;
		/**
		*
		*/
		protected static $_columns = array( );
		/**
		*
		*/
		protected static $_db = null;
		/**
		*
		*/
		protected static $_events = array
		(	
			'inserting' , 'inserted' , 'updating' , 'updated' , 
			'deleting' , 'deleted' , 'saving' , 'saved'
		);
		/**
		*
		*/
		protected static $_observers = array( );
		/**
		*
		*/
		protected $_fields = array( );
		/**
		* Checks if column name exists in table
		* @param	string	$column		the value to check
		*/
		protected static function _checkColumn( $column )
		{
			if ( !array_key_exists( $column , static::$_columns ) && 
								!in_array( $column , static::$_map ) )
			{
				trigger_error( 'Column ' . $column . ' does not exists in table  ' . 
									static::$_table . '!' , E_USER_ERROR );
				return false;
			}
			return true;
		}
		/**
		*
		*/
		protected static function _fireEvent( $event , $data )
		{
			$event = ( is_array( $event ) ) ? $event : array( $event );
			$events_class = static::_getProperty( 'eventClass' );
			if ( !empty( static::$_observers ) )
			{
				foreach ( static::$_observers as $k => $v )
				{
					foreach ( $event as $ev )
					{
						if ( $v === $ev ){ $events_class::fire( $k , $data ); }
					}
				}
			}
		}
		/**
		*
		*/		
		protected static function _getQB( )
		{
			if ( !static::$_db )
			{
				static::$_connectionManager = static::_getProperty( 'connectionManager' );
				static::$_connectionName = static::_getProperty( 'connectionName' );
				static::$_db = call_user_func( static::$_connectionManager . 
									'::getQB' , static::$_connectionName );	
			}
			return static::$_db;
		}
		/**
		*
		*/		
		protected static function _initialize( )
		{
			if ( !static::$_db ){ static::_getQB( ); }
			if ( !static::$_table ) 
			{
				static::$_table = static::_getProperty( 'table' );
				static::$_db->run( 'SHOW TABLES LIKE ?' , array( static::$_table ) );
				if ( !static::$_db->countRows( ) )
				{ 
					trigger_error( static::$_table . 
							'does not exists, quitting now!' , E_USER_ERROR );
					return false;
				}
			}
			if ( empty( static::$_columns ) ){ static::getColumns( ); }
			static::$_uniqueKey = static::_getProperty( 'uniqueKey' ); 
			static::$_map = static::_getproperty( 'map' );
			return get_called_class( );
		}
		/**
		*
		*/
		protected function _mapFields( )
		{
			static::$_map = static::_getproperty( 'map' );
			if ( !empty( static::$_map ) )
			{
				foreach ( static::$_map as $k => $v )
				{
					if ( array_key_exists( $v , $this->_fields ) )
					{
						$this->_fields[ $k ] =  $this->_fields[ $v ];
						unset( $this->_fields[ $v ] );
					}
				}
			}
		}
		/**
		*
		*/
		public static function _getProperty( $property , $found = false )
		{
			if ( property_exists( $class = get_called_class( ) , $property ) )
			{
				$found = true;
			}
			switch( $property )
			{
				case 'table' :
					if ( $found ){ return static::$table; }
					if ( strpos( $class , '\\' ) )
					{ 
						$class = end( explode( '\\' . $class ) ); 
					}
					return strtolower( $class );
				break;
				case 'map' : 
					return ( $found ) ? static::$map : array( );
				break;
				case 'uniqueKey' : return ( $found ) ? static::$uniqueKey : 'id';
				break;
				case 'connectionManager' : return ( $found ) ? 
								static::$connectionManager : 'PtcDb';
				break;
				case 'connectionName' : 
					if ( $found ){ return static::$connectionName; }
					return 'default';
				break;
				case 'eventClass' : return ( $found ) ? static::$eventClass : 
										__NAMESPACE__ . '\PtcEvent';
				default : return null;
			}
		}
	}