<?php

	/**
	* PHP TOOLCASE QUERY BUILDER CLASS
	* PHP version 5.3
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.9.1b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	class PtcQueryBuilder
	{
		/**
		* Adds the pdo instance to the query builder object
		* @param	object	$pdo	the pdo object
		*/
		public function __construct( PDO $pdo = null )
		{
			$this->_randomId = $this->_generateRandomId( );
			if ( $pdo ){ $this->_pdo = $pdo; }
			return $this;
		}
		/**
		* Adds the table to the query
		* @param	array|string	$table	the name of the table
		*/
		public function table( $table ) 
		{ 
			$this->reset( );
			$table = ( is_array( $table ) ) ? $table : array( $table );
			foreach ( $table as $v )
			{
				$this->_table .= $this->addBackTicks( $this->sanitize( $v ) ) . ','; 
			}
			$this->_table = substr( $this->_table , 0 , strlen( $this->_table ) - 1 );
			$this->_currentQueryType = 'select'; // set query type as select by default
			return $this; 
		}
		/**
		* Set the columns to be selected
		* @param  	array|string		$columns		the columns
		*/
		public function select( $columns )
		{ 
			$this->_columns = '';
			$columns = is_array( $columns ) ? $columns : array( $columns );
			foreach ( $columns as $v )
			{
				if ( $val = $this->_checkRawValue( $v ) )
				{ 
					$this->_columns .= ' ' . $val. ','; 
					continue;
				}
				$divider = ( strpos( $v , ' as ' ) ) ? ' as ' : ' AS ';
				$column = explode( $divider , $v );
				if ( preg_match( '/\(([^\)]*)\)/' , $column[ 0 ] , $matches ) )
				{
					$col = str_replace( $matches[ 0 ] , '(' . 
							$this->addBackTicks( $matches[ 1 ] ) . ')' , $column[ 0 ] );
				}
				else{ $col = $this->addBackTicks( $column[ 0 ] ); }
				$col .= ( array_key_exists( 1, $column ) ) ? ' as ' . 
											$this->addBackTicks( $column[ 1 ] ) : ' ';
				$this->_columns .= $this->sanitize( $col ) . ',';
			}
			$this->_columns = substr( $this->_columns , 0 , strlen( $this->_columns ) - 1 ). ' ';
			return $this;
		}
		/**
		* Adds A raw where clause to the query
		* @param	string	$rawClause	the where clause
		*/
		public function rawSelect( $rawClause )
		{
			$this->_where = $this->_where . ' ' . $rawClause;	
			return $this;
		}
		/**
		* Sanitizes unsafe column names
		* @param	string	$value	the value to sanitize
		* @return	returns the value with back slashes added
		*/
		public function sanitize( $value )
		{
			if ( $val = $this->_checkRawValue( $value ) ){ return $value; }
			/*if( $this->_pdo ){ return $this->_pdo->quote( $string ); }*/
			return addslashes( $value ); // should be done better
		}
		/**
		* Adds a raw value to a where clause in the query
		* @param	string	$value	a raw value for the sql query
		*/		
		public function raw( $value ){ return $this->_randomId . 'RAW{' .$value . '}'; }
		/**
		* Adds backticks to columns
		* @param	string	$string	the column or table name
		*/
		public function addBackTicks( $string )
		{
			if ( $val = $this->_checkRawValue( $string ) ){ return $val; }
			$raw = explode( '.' , $string );
			$string = ( $raw[ 0 ] === '*' ) ? $raw[ 0 ] : '`' . $raw[ 0 ] . '`';
			return $string .= ( @$raw[ 1 ] ) ? '.`' . $raw[ 1 ] . '`': ''; 
		}
		/**
		* Creates a join based on the parameters
		* @param	string	$table		the name of the table to join
		* @param	string	$first		the first column
		* @param	string	$operator		the operator to use for the join
		* @param	string	$second		the second column
		* @param	string	$type		the type of join
		*/
		public function join( $table , $first , $operator = null , $second = null , $type = 'inner' )
		{
			if ( !$this->_isTableSet( ) ) { return false; }
			$this->_join .= ' ' . strtoupper( $type ) . ' JOIN ' . $this->addBackTicks( $table );
			$this->_isClosure = true;
			if ( $first instanceof Closure ){ $this->_runClosure( $first  , 'join' ); }
			else{ $this->on( $first, $operator, $second ); }
			return $this;
		}
		/**
		* Joins columns based on values
		*/
		public function on( $column, $operator, $value , $type = 'and' )
		{
			if ( !$this->_checkOperator( $operator ) ) { return; }
			$this->_join .= ( $this->_isClosure ) ? ' ON ' : ' ' . strtoupper( $type ) . ' ';
			$this->_isClosure = false;
			$this->_join .= $this->addBackTicks( $column ) . ' ' . $operator . ' ' . 
											$this->addBackTicks( $value ) . ' ';
			return $this;
		}
		/**
		* Adds where clouses to the query
		* @param	mixed	$column		the column name or a closure function
		* @param	string	$operator		the operator to use for the where clause
		* @param	string	$value		the value to look for in the column
		* @param	string	$type		the type of where clasuse
		*/
		public function where( $column , $operator = null , $value = null , $type = 'and' )
		{
			if ( !$this->_isTableSet( ) ) { return false; }
			if ( $column instanceof Closure )
			{
				$this->_runClosure( $column , $type ); 
				return $this;
			}
			if ( $this->_isClosure )
			{
				$this->_isClosure = false;
				$type = '';
			}
			$this->_buildWhereClause( $type , $column , $operator , $value ); 
			return $this;
		}
		/**
		* returns 1 record from a given table based on the id
		* @param	numeric	$id		the row id
		*/
		public function find( $id )
		{
			if ( !$this->_isTableSet( ) ) { return; }	// check if table property is set
			$this->_where = null;
			$this->_buildWhereClause( 'and', 'id' , '=' , $id ); 
			$query = 'SELECT ' . $this->_columns . ' FROM ' . $this->_table . $this->_where;
			$this->_currentQuery = $query;
			$result = $this->_executeSql( 2 );
			return ( !empty( $result ) ) ?  $result : null;
		}
		/**
		* Runs queries if pdo object is present
		* @param	string		$query		the query to run
		* @param	array		$bind		the values to bind to the query
		* @param	numeric		$type		the query type ( 1,2,3)		
		* @return	returns the query result if select, otherwise the number of affected rows
		*/
		public function run( $query = null , $bind = null , $type = null ) 
		{
			if ( $this->_currentQueryType == 'select' ) // run select query stored in memory
			{
				if ( !$this->_isTableSet( ) ){ return false; }
				$this->_currentQuery = $this->_buildQuery( );
			}
			$this->_bindings = ( $bind )  ? $bind : $this->_bindings;
			$this->_currentQuery = ( $query ) ? $query : $this->_currentQuery;
			if ( !$type )
			{
				$type = 3;
				// check prepared statement that needs a return result
				foreach ( $this->_returnStatements as $statement )
				{
					if ( strpos( trim( strtoupper( $this->_currentQuery ) ) , $statement ) === 0 ) 
					{
						$type = 1;	// set type to 1 to return the result of the query
						break;
					}
				}
			}
			return $this->_executeSql( $type ); // execute the query
		}
		/**
		* Retrieves 1 row from a given table
		* @param	string	$column		column name to return only 1 value as string
		* @return	will return 1 value if $column argument is set, the full row otherwise
		*/
		public function row( $column = null )
		{
			if ( !$this->_isTableSet( ) ) { return false; }
			$this->_columns = ( $column ) ? $this->addBackTicks( $column ) : $this->_columns;
			$this->_currentQueryType = 'select';
			$this->_currentQuery = $this->_buildQuery( );
			$result = $this->_executeSql( 2 );
			if ( empty( $result ) ){ return null; }
			// works only with PDO::FETCH_OBJ and PDO::FETCH_ASSOC
			if ( $column )
			{
				if ( is_array( $result ) && array_key_exists( $column , $result ) )
				{ 
					return $result[ $column ]; 
				}
				else if( is_object( $result ) && property_exists( $result , $column) )
				{
					return $result->$column;
				}
				//trigger_error( 'Could not find column "' . $column . '"!' , E_USER_WARNING );
				return null;
			}
			else{ return $result; }
		}
		/**
		* Builds the query and returns it as string with place holders
		*/
		public function prepare( )
		{
			if ( !$this->_isTableSet( ) ) { return false; }
			$query = ( $this->_currentQuery ) ? $this->_currentQuery : $this->_buildQuery( );
			foreach ( $this->_bindings as $k => $v ) 
			{
				if ( is_string( $v ) ) 
				{
					if ( false === strpos( $v, ':' ) ) { continue; }
					$query = preg_replace( '/\?/' , $v , $query , 1 );
				}
			}
			$this->reset( ); // reset properties
			return $query;
		}
		/**
		* Adds order to the query
		* @param	string		$column		the column names
		* @param	string		$direction	asc or desc
		*/
		public function order( $column , $direction = 'asc' )
		{
			$direction = strtoupper( $direction );
			$this->_orderBy = ( $this->_orderBy ) ? $this->_orderBy . ', ' : ' ORDER BY '; 
			$this->_orderBy .= $this->addBackTicks( $this->sanitize( $column ) ) . ' ' . $direction;
			return $this;
		}
		/**
		* Adds Group By to the query
		* @param	string	$column		the column names
		*/
		public function group( $column )
		{
			$this->_groupBy = ( $this->_groupBy ) ? $this->_groupBy . ', ' : ' GROUP BY ';
			$this->_groupBy .= $this->addBackTicks( $this->sanitize( $column ) );
			return $this;
		}
		/**
		* Adds limit to the query
		* @param	string|int		$start	an integer value or a place holder
		* @param	string|int		$results	an integer value or a place holder
		*/
		public function limit( $start , $results = null )
		{
			$start = is_numeric( $start ) ? ( int ) $start : $start;
			$results = is_numeric( $results ) ? ( int ) $results : $results;
			$this->_bindings[ ] = $start;
			$this->_limit = ' LIMIT ?';
			if ( $results ) 
			{
				$this->_bindings[ ] = $results;
				$this->_limit .= ',?';
			}
			return $this;
		}
		/**
		* Returns number of affected rows from last query
		* @return	resturns the affected rows by the last query
		*/
		public function countRows( )
		{
			if ( !$this->_lastQuery )
			{
				trigger_error( 'No queries Found to countRows!' , E_USER_NOTICE );
				return false;
			}
			return $this->_lastQuery->rowCount( );
		}
		/**
		* Retrieves last inserted id
		* @return	returns the last inserted id
		*/
		public function lastId( ) { return $this->_pdo->lastInsertId( ); }
		/**
		* Inserts a record in a given table
		* @param	array	$array	ann associative array , column => values
		*/
		public function insert( $array )
		{
			if ( !$this->_isTableSet( ) ) { return false; }
			$this->_currentQueryType = 'insert';
			$this->_values = $array;
			$this->_currentQuery = $this->_buildQuery( );
			return $this;
		}
		/**
		* Updates records in a given table based on a where clause
		* @param	array	$array	associative array of values, column => value
		* @param	numeric	$id		a row id
		*/
		public function update( $array , $id = null )
		{
			if ( !$this->_isTableSet( ) ){ return false; }
			if ( !$this->_where && !$id )
			{
				trigger_error( 'No id or where clause was specified for the update!' , 
															E_USER_ERROR );
				return false;
			}
			$this->_currentQueryType = 'update';
			$this->_values = $array;
			if ( $id ) 
			{ 
				$this->_where = null;
				$this->_buildWhereClause( 'and' , 'id' , '=' , $id );
			}
			$this->_currentQuery = $this->_buildQuery( );
			return $this;
		}
		/**
		* Deletes rows from a given table based on a where clause
		* @param	numeric		$id		a row id
		*/
		public function delete( $id = null )
		{
			if ( !$this->_isTableSet( ) ) { return false; }
			if ( !$this->_where && !$id )
			{
				trigger_error( 'No id or where clause was specified for the delete!' , 
														E_USER_ERROR );
				return false;
			}
			$this->_currentQueryType = 'delete';
			if ( $id ) 
			{ 
				$this->_where = null;
				$this->_bindings = null;
				$this->_buildWhereClause( 'and' , 'id' , '=' , $id ); 
			}
			$this->_currentQuery = $this->_buildQuery( );
			return $this;
		}
		/**
		* Resets query parameters
		*/
		public function reset( )
		{
			$this->_table = null;
			$this->_where = null;
			$this->_columns = '*';
			$this->_orderBy = null;
			$this->_groupBy = null;
			$this->_limit = null;
			$this->_query = null;
			$this->_currentQueryType = null;
			$this->_currentQuery = null;
			$this->_bindings = array( );
			$this->_join = null;
		}
		/**
		*
		*/
		public function __call( $method , $args )
		{
			if ( false !== strpos( $method , 'where' ) ) // work with where clause
			{
				$type = str_replace( 'where' , '' , $method );
				if ( false !== strpos( $type , '_between' ) )
				{
					// build between clause
					$type = $this->_addAndOR( $type );
					$this->_buildBetweenClause( $args[ 0 ] , @$args[ 1 ] , @$args[ 2 ] , $type );
				}
				else if ( false !== strpos( $type , '_in' ) )
				{
					// build in clause
					$type = $this->_addAndOR( $type );
					$this->_buildInClause( $args[ 0 ] , $args[ 1 ] , $type );
				}
				else // process or_where( )
				{ 
					$type = str_replace( '_' , ' ' , $type );
					$this->where( $args[ 0 ] , @$args[ 1 ] , @$args[ 2 ] , $type ); 
				} 
				return $this;
			}
			else if ( false !== strpos( $method , '_join' ) ) // work with joins
			{
				$type = str_replace( '_join' , '' , $method );
				$this->join( $args[ 0 ] , @$args[ 1 ] , @$args[ 2 ] , @$args[ 3 ] , $type );
				return $this;
			}
			else if ( false !== strpos( $method , '_on' ) )
			{
				$type = str_replace( '_on' , '' , $method );
				$this->on( $args[ 0 ] , @$args[ 1 ] , @$args[ 2 ] , $type );
				return $this;
			}
			trigger_error( 'Called to undefined method "' . get_called_class( ) . '::' . 
												$method . '( )"!' , E_USER_ERROR );
		}
		/**
		* Limit property for the query
		*/
		protected $_bindLimit = array( );
		/**
		* Columns property for the query
		*/
		protected $_columns = '*';
		/**
		* Table property for the query
		*/
		protected $_table = null;
		/**
		* Where property for the query
		*/
		protected $_where = null;
		/**
		* Order by property for the query
		*/
		protected $_orderBy = null;
		/**
		* Group by property for the query
		*/
		protected $_groupBy = null;
		/**
		* Limit property for the query
		*/
		protected $_limit = null;
		/**
		* Pdo object prperty to run queries
		*/
		protected $_pdo = null;
		/**
		* Prepared query property
		*/
		protected $_query = null;
		/**
		* Last query property
		*/
		protected $_lastQuery = null;
		/**
		* Current query property
		*/
		protected $_currentQuery = null;
		/**
		* Current query type property
		*/
		protected $_currentQueryType = null;
		/**
		* Bind values property
		*/
		protected $_values = null;
		/**
		* Place holders property
		*/
		protected $_bindings = array( );
		/**
		* Operator for where and join clauses property
		*/
		protected $_operators = array( '=' , '<' , '>' , '<=' , '>=' , '<>' , '!=' ,
									'like' , 'not like' , 'between' , 'ilike' );
		/**
		* Queries that need a return result propeerty
		*/
		protected $_returnStatements = array( 'SHOW' , 'SELECT' );
		/**
		* Join Property
		*/
		protected $_join = null;
		/**
		* Random Id property for raw values
		*/
		protected $_randomId = null;
		/**
		* Property that checks if class is running a closure
		*/
		protected $_isClosure = false;
		/**
		* Fecth mode pdo property for current query
		*/
		protected $_fetchMode = array( );
		/**
		* Event class name property
		*/ 
		protected $_eventClass = '\PtcEvent';
		/**
		* Builds the query based on the type
		*/
		protected function _buildQuery( )
		{
			switch( $this->_currentQueryType )
			{
				case 'insert' :
					$this->_bindings = array_values( $this->_values );
					foreach ( $this->_values as $k => $v ) 
					{
						@$fields .= $this->addBackTicks( $k ) . ',';
						@$values .= '?,';
					}
					$fields = substr( $fields , 0 , strlen( $fields ) - 1 );
					$values = substr( $values , 0 , strlen( $values ) - 1 );
					$query = 'INSERT INTO ' . $this->_table . ' (' . $fields . ') VALUES (' . $values . ')';
				break;
				case 'update' :
					$bind = array_values( $this->_values );
					$this->_bindings = array_merge( $bind , $this->_bindings );
					foreach ( $this->_values as $k => $v ) 
					{ 
						@$values .= $this->addBackTicks( $k ) . ' = ?,'; 
					}
					$values = substr( $values , 0 , strlen( $values ) - 1 );
					$query = "UPDATE " . $this->_table . " SET " . $values . $this->_where;
				break;
				case 'delete' : $query = 'DELETE FROM ' . $this->_table . $this->_where;
				break;
				case 'select' :
				default :
					$query = 'SELECT ' . $this->_columns . ' FROM ' . $this->_table . $this->_join . 
								$this->_where . $this->_groupBy . $this->_orderBy . $this->_limit;
			}
			return $query;
		}
		/**
		* Sets fetch mode for the next query
		* @param	constant		$mode	a pdo constant
		* @param	mixed		$class	a class name if needed
		*/
		public function setFetchMode( $mode , $class = null )
		{
			if ( $class && !class_exists( $class ) )
			{
				trigger_error( 'Class ' . $class . ' does not exists!' , E_USER_ERROR );
				return false;
			}
			$this->_fetchMode = ( $class ) ? array( $mode , $class ) : array( $mode );
			
		}
		/**
		* Executes sql queries
		* @param	numeric	$mode	the type of query (1,2,3)
		*/
		protected function _executeSql( $mode = 1 )
		{
			if ( !$this->_checkPdo( ) ) { return false; }
			$query = $this->_currentQuery;
			self::_debug( '' , ' - ' . $query . ' - ' ); // debug
			$this->_query = $this->_pdo->prepare( $query );
			$this->_lastQuery = $this->_query;
			if ( !empty( $this->_bindings ) ) 
			{ 
				foreach ( $this->_bindings as $k => $v ) { $this->_bind( $k , $v ); }
			}	
			$this->_query->execute( );
			if ( !empty( $this->_fetchMode ) )
			{	
				if ( array_key_exists( 1 , $this->_fetchMode ) )
				{
					$this->_query->setFetchMode( $this->_fetchMode[ 0 ] , $this->_fetchMode[ 1 ] );	
				}
				else{ $this->_query->setFetchMode( $this->_fetchMode[ 0 ] ); }
				$this->_fetchMode = array( );
			}
			switch ( $mode )
			{
				case 3 : $result = $this->countRows( );
				break;
				case 2 : $result = $this->_query->fetch( );
				break;
				case 1 :
				default : $result = $this->_query->fetchAll( );
			}
			self::_debugBuffer(  ' - ' . $query . ' - ' ); 	// debug stop timer
			$final_query = $this->_debugQuery( $this->_lastQuery->queryString , $this->_bindings );
			$this->_fireEvent( array( $final_query , $this->_currentQuery , $this->_bindings ) ); // ptc.query event
			$this->reset( );					// reset properties
			// debug attach result
			self::_debugBuffer(  ' - ' . $query . ' - ' , 'attach' , $result , ' - ' . $final_query . ' - ' );
			return $result;
		}
		/**
		* Binds values to the query
		* @param	mixed	$pos		the param position if numeric
		* @param	mixed	$value	the value to bind te place holder to
		* @param	contants	$type	a pdo constant to bind values
		*/
		protected function _bind( $pos , $value , $type = null )
		{
			if ( is_numeric( $pos ) ) { $pos = ( $pos + 1 ); }
			if ( is_null( $type ) ) 
			{
				switch ( $value ) 
				{
					case is_int( $value ): $type = PDO::PARAM_INT;
					break;
					case is_bool( $value ): $type = PDO::PARAM_BOOL;
					break;
					case is_null( $value ): $type = PDO::PARAM_NULL;
					break;
					default: $type = PDO::PARAM_STR;
				}
			}
			$this->_query->bindValue( $pos , $value , $type );
		}
		/**
		* adds "and" or "or" to the query
		* @param	string	$value	the value to check
		*/			
		protected function _addAndOR( $value )
		{
			if ( false !== strpos( $value , 'or_' ) )  
			{
				$value = str_replace( 'or_' , '' , $value );
				$this->_where .= ( !$this->_where ) ? ' WHERE ' : ' OR ';
			}
			else
			{ 
				$value = str_replace( 'and_' , '' , $value );
				$this->_where .= ( !$this->_where ) ? ' WHERE ' : ' AND ';
			}
			return $value;
		}
		/**
		*
		*/
		protected function _buildWhereClause( $type , $column , $operator = null , $value = null )
		{
			if ( !$this->_checkOperator( $operator ) ) { return; }
			$this->_where .= ( $this->_where ) ? ' ' . strtoupper( $type ) . ' ' : ' WHERE ';
			$this->_where .= $this->addBackTicks( $column ) . ' ' . $operator;
			if ( preg_match( '|' . $this->_randomId . 'RAW{(.*?)}|', $value , $matches ) )
			{
				$this->_where .= ' ' . $matches[ 1 ];
				return $this;
			}
			if ( $val = $this->_checkRawValue( $value ) )
			{ 
				$this->_where .= ' ' . $val; 
				return $this;
			}
			$this->_where .= '  ?';
			$this->_bindings[ ] = ( @get_magic_quotes_gpc( ) ) ? @stripslashes( $value ) : $value;
			return $this;
		}
		/**
		*
		*/
		protected function _buildInClause( $column , $array , $type = 'in' )
		{
			$this->_where .= $this->addBackTicks( $column ) . ' ';
			$this->_where .= strtoupper( str_replace( '_' , ' ' , $type ) ) . ' '; 
			$this->_where .= ' (';
			foreach ( $array as $v )
			{
				if ( $val = $this->_checkRawValue( $v ) )
				{
					$this->_where .= ' ' . $val;
					continue;
				}
				$this->_where .= '?,';
				$this->_bindings[ ] = ( @get_magic_quotes_gpc( ) ) ? @stripslashes( $v ) : $v;
				//$this->_where .=  is_numeric( $v ) ? ( int ) $v . ',': $this->sanitize( $v ). ',';
			}
			$this->_where = substr( $this->_where , 0 , strlen( $this->_where ) - 1 );
			$this->_where .= ') ';
			return $this;
		}
		/**
		*
		*/
		protected function _buildBetweenClause( $column , $start , $end , $type = 'between' )
		{
			$needle = ( false !== strpos( $type , '_not' ) ) ? ' NOT BETWEEN ' : ' BETWEEN ';
			$type = str_replace( '_not' , ' ' , $type );
			$type = strtoupper( $type );
			$type = str_replace( '_BETWEEN' , $this->addBackTicks( $column ) . $needle , $type );
			$this->_where .= $type . ' '; 
			if ( $val = $this->_checkRawValue( $start ) ){ $this->_where .= ' ' . $val; }
			else
			{ 
				$this->_where .= ' ? ';
				$this->_bindings[ ] = ( get_magic_quotes_gpc( ) ) ? stripslashes( $start ) : $start;
			}
			$this->_where .= ' AND ';
			if ( $val = $this->_checkRawValue( $end ) ){ $this->_where .= ' ' . $val; }
			else
			{ 
				$this->_where .= ' ? ';
				$this->_bindings[ ] = ( get_magic_quotes_gpc( ) ) ? stripslashes( $end ) : $end;
			}
			return $this;
		}
		/**
		*
		*/
		protected function _checkRawValue( $value )
		{
			if ( preg_match( '|' . $this->_randomId . 'RAW{(.*?)}|', $value , $matches ) )
			{ 
				return $matches[ 1 ]; 
			}
			return false;
		}
		/**
		*
		*/
		protected function _checkOperator( $operator )
		{
			if ( !in_array( strtolower( $operator ) , $this->_operators ) )
			{
				trigger_error( 'Invalid query operator "' . $operator . '"!' , E_USER_ERROR );
				return false;
			}
			return true;
		}
		/**
		*
		*/
		protected function _runClosure( Closure $function , $type )
		{
			$this->_isClosure = true;
			if ( 'join' === $type ){ return call_user_func_array( $function , array( $this ) ); }
			$this->_where .= ' ' . strtoupper( str_replace( '_' , ' ' , strtolower( $type ) ) );
			$this->_where  .= ' ( ';
			call_user_func_array( $function , array( $this ) );
			$this->_where  .= ' ) ';
			$this->_isClosure = false;
		}
		/**
		*
		*/
		protected function _checkPdo( )
		{
			if( !$this->_pdo ) 
			{ 
				trigger_error( 'Pdo was not set to execute queries with query builder!' , 
															E_USER_ERROR );
				return false;
			}
			return true;
		}
		/**
		*
		*/
		protected function _isTableSet( )
		{
			if ( !$this->_table )
			{
				trigger_error( 'No table set for query, use table( )!' , E_USER_ERROR );
				return false;
			}
			return true;
		}
		/**
		* Generates a random numeric string to secure the raw function
		*/
		protected function _generateRandomId( )
		{
			return rand( 1000000 , 9999999 ) . '_';
		}
		/**
		*
		*/
		protected function _fireEvent( $data )
		{
			if ( !class_exists( $event_class = $this->_getEventClass( ) ) ){ return; }
			$listeners = $event_class::getEvents( 'ptc' );
			if ( @$listeners[ 'query' ] ){ $event_class::fire( 'ptc.query' , $data ); }
		}
		/**
		*
		*/		
		protected function _getEventClass( )
		{
			return __NAMESPACE__ . $this->_eventClass; 
		}
		/**
		* Send messsages to the PtcDebug class if present and it\'s namespace
		* @param 	mixed 	$string		the string to pass
		* @param	string	$type		the type of debug  (timer, attach)
		* @param 	mixed 	$statement		some statement if required
		* @param	string	$category		a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = 'QueryBuilder' )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			return @call_user_func_array( array( _PTCDEBUG_NAMESPACE_ , 'bufferSql' ) ,  
										array( $string , $statement , $category )  );
		}
		/**
		*
		*/
		protected function _debugQuery( $string , $data ) 
		{
			$indexed=$data==array_values($data);
			foreach( $data as $k => $v ) 
			{
				$v = ( is_string( $v ) ) ? "'$v'" : $v;
				if ( $indexed ){ $string = preg_replace( '/\?/' , $v , $string , 1 ); }
				else{ $string = preg_replace( '/' . $k . '/' , $v , $string , 1 ); }
			}
			return $string;
		}
		/**
		* Adds execution time and query results to the PtcDebug class
		* @param	string	$reference		a reference to look for ("$statement")
		* @param	string	$type		the type of debug  (timer, attach)
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement		some new statement if required
		*/
		protected static function _debugBuffer( $reference , $type = null , $string = null , $statement = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			if ( $type == 'attach' /*&& $string*/ )
			{
				return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 'addToBuffer' ) ,  
													array( $reference , $string , $statement )  );
			}
			else
			{
				return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 'stopTimer' ) , 
																	array( $reference ) );
			}
		}
	}