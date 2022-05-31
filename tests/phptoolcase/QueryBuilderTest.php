<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use PHPUnit\Framework\Assert;

	/**
	* @requires extension pdo
	*/
	final class QueryBuilderTest extends TestCase
	{
		protected static $_connection = [ ];
		
		protected static $_qb = '';
	
		public static function setUpBeforeClass( ) : void
		{
			static::$_connection =
			[
				'host' => $GLOBALS[ 'DB_HOST' ] ,			// mysql host
				'user' =>  $GLOBALS[ 'DB_USER' ] ,		// mysql user
				'pass' => $GLOBALS[ 'DB_PASSWORD' ] ,	// mysql pass
				'database' => $GLOBALS[ 'DB_DBNAME' ]	// mysql database name
			];
			$pdo = new \PDO( 'mysql:host=' . static::$_connection[ 'host' ] . ';dbname=' . 
							static::$_connection[ 'database' ] . ';charset:uft8;' , 
								static::$_connection[ 'user' ] , static::$_connection[ 'pass' ] );
			$pdo->setAttribute( \PDO::ATTR_DEFAULT_FETCH_MODE , \PDO::FETCH_OBJ );
			$pdo->setAttribute( \PDO::ATTR_ERRMODE , \PDO::ERRMODE_WARNING );
			static::$_qb = new QueryBuilder( $pdo );
			static::$_qb->run( "DROP TABLE IF EXISTS `test_table`" );
			static::$_qb->run( "CREATE TABLE `test_table` 
			(
				`id` int NOT NULL AUTO_INCREMENT, 
				PRIMARY KEY(`id`),
				`stringfield1` varchar(255),
				`stringfield2` varchar(255),
				`stringfield3` varchar(255),
				`intfield` int(11) ,
				`boolfield` tinyint(1)
			)" );
		}
	
		public function testPrepare( )
		{
			$statement = static::$_qb->table( 'test_table' )->prepare( );
			$this->assertTrue( is_string( $statement ) );
			$this->assertEquals( 'SELECT * FROM `test_table`' , $statement );
		}
		
		public function testInsert( )
		{
			$rand = rand( );
			$values = 
			[ 
				'stringfield1' 	=> null , 				// test null value
				'stringfield2' 	=> '' , 				// test empty value
				'stringfield3' 	=> 'some value' , 		// test string value
				'intfield' 		=> $rand , 			// test integer value
				'boolfield' 	=> false				// test bool value
			];
			$query = static::$_qb->table( 'test_table' )->insert( $values )->run( );
			$this->assertEquals( 1 , $query );
			$record = static::$_qb->table( 'test_table' )
							->where( 'intfield' , '=' , $rand )
							->row( );				
			$this->assertNull( $record->stringfield1 );
			$this->assertTrue( is_string( $record->stringfield2 ) );
			$this->assertEquals( 'some value' , $record->stringfield3 );
			$this->assertTrue( is_numeric( $record->intfield ) );
			$this->assertEquals( $rand , $record->intfield );
			$this->assertEquals( '0' , $record->boolfield );
		}
		
		public function testInsertWithPartialValues( )
		{
			$values = [ 'stringfield1' => 'some string' , 'boolfield' => true ];
			$query = static::$_qb->table( 'test_table' )->insert( $values )->run( );
			$this->assertEquals( 1 , $query );
		}
		/**
		* @depends testInsert
		*/
		public function testGetAllRecords( )
		{
			$query = static::$_qb->table( 'test_table' )->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectQuery( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'stringfield3' , '=' , 'some value' )
							->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectRowWithWhereClause( )
		{
			$record = static::$_qb->table( 'test_table' )
							->where( 'stringfield3' , '=' , 'some value' )
							->row( );				
			$this->assertEquals( 'some value' , $record->stringfield3 );			
		}
		/**
		* @depends testInsert
		*/
		public function testSelectRowWithOneColumnOnly( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'stringfield3' , '=' , 'some value' )
							->row( 'boolfield' );
			$this->assertFalse( ( bool ) $query );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithSpecificColumns( )
		{
			$query = static::$_qb->table( 'test_table' )
							->select( [ 'stringfield1 as i' , 'stringfield3' ] )
							->run( );
			$records = ( array ) $query[ 0 ];
			$this->assertTrue( ( sizeof( $records ) == 2 ) );
			$this->assertArrayHasKey( 'i' , $records );
		}
		/**
		* @depends testInsert
		*/
		public function testFindRecordbyId( )
		{
			$query = static::$_qb->table( 'test_table' )->find( 1 );
			$this->assertFalse( is_null( $query ) );
		}
		/**
		* @depends testInsert
		*/
		public function testCannotFindRecordbyId( )
		{
			$query = static::$_qb->table( 'test_table' )->find( 999 );
			$this->assertTrue( is_null( $query ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithEmptyResult( )
		{
			$query = static::$_qb->table( 'test_table' )
				->where( 'stringfield1' , '=' , 'nothing' )
				->run( );
			$this->assertTrue( ( sizeof( $query ) == 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectRowWithEmptyResult( )
		{
			$query = static::$_qb->table( 'test_table' )
				->where( 'stringfield1' , '=' , 'nothing' )
				->row( );
			$this->assertTrue( is_null( $query ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithEmptyQueryValue( )
		{
			$query = static::$_qb->table( 'test_table' )
				->where( 'stringfield2' , '=' , '' )
				->row( );
			$this->assertFalse( is_null( $query ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithIsNullOperator( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'stringfield1' , 'is' , NULL )
							->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
			$this->assertNull( $query[ 0 ]->stringfield1 );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectRowWithIsNullOperator( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'stringfield1' , 'is' , NULL )
							->row( );
			$this->assertFalse( is_null( $query ) );
			$this->assertNull( $query->stringfield1 );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithNumericQueryValue( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'boolfield' , '=' , 0 )
							->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
			$this->assertFalse( ( bool ) $query[ 0 ]->boolfield );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectRowWithNumericQueryValue( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'boolfield' , '=' , 0 )
							->row( );
			$this->assertFalse( is_null( $query ) );
			$this->assertFalse( ( bool ) $query->boolfield );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectMultipleTables( )
		{
			static::$_qb->run( "DROP TABLE IF EXISTS `test_table1`" );
			static::$_qb->run( "CREATE TABLE test_table1 AS SELECT * FROM test_table;" );
			$query = static::$_qb->table( [ 'test_table' , 'test_table1 as i' ] )->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithMultipleWhereOperators( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'boolfield' , '=' , 0 )
							->where( 'stringfield3' , '=' , 'some value' )
							->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectRowWithMultipleWhereOperators( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'boolfield' , '=' , 0 )
							->where( 'stringfield3' , '=' , 'some value' )
							->row( );
			$this->assertFalse( is_null( $query ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithWhereOrWhereOperators( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'boolfield' , '=' , 0 )
							->or_where( 'stringfield3' , '=' , 'some value' )
							->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectRowWithWhereOrWhereOperators( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'boolfield' , '=' , 0 )
							->or_where( 'stringfield3' , '=' , 'some value' )
							->row( );
			$this->assertFalse( is_null( $query ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithWhereInOperators( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where_in( 'boolfield' , [ 0 , 2 ] )
							->or_where_in( 'boolfield' , [ 1 , 6 ] )
							->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithWhereBetweenOperators( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where_between( 'boolfield' , 0 , 0 )
							->or_where_between( 'boolfield' , 2 , 5 )
							->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithClosure( )
		{
			$query = static::$_qb->table( 'test_table' )
						->where( 'stringfield3' , '=' , 'some value' )
						->where( function( $query )
						{
							$query->where( 'stringfield1', 'is' , null )
								->or_where( 'intfield' , '<' , 9999999999999 );
						} )->run( );	
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithGroupBy( )
		{
			$query = static::$_qb->table( 'test_table' )
						->select( 'stringfield1' )
						->group( 'stringfield1' )
						->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		* @depends testInsertWithPartialValues
		*/
		public function testSelectWithOrder( )
		{
			$query = static::$_qb->table( 'test_table' )->order( 'id' , 'asc' )->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
			$this->assertEquals( 1 , $query[ 0 ]->id );
			$this->assertEquals( 2 , $query[ 1 ]->id );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithLimit( )
		{
			$query = static::$_qb->table( 'test_table' )->limit( 1 , 100 )->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}

		public function testGetLastInsertId( )
		{
			$current_id =  static::$_qb->table( 'test_table' )
							->order( 'id' ,'desc' )
							->limit( 1 )
							->row( 'id' );
			$values = [ 'stringfield1' => 'something' , 'stringfield2' => 'some other value' ];
			$query = static::$_qb->table( 'test_table' )
						->insert( $values ) 
						->run( );
			$last_insert_id = static::$_qb->lastId( );
			$this->assertEquals( ( $current_id + 1 ) , $last_insert_id  );
			return $last_insert_id;
		}	
		/**
		* @depends testGetLastInsertId
		*/
		public function testUpdateRecordBasedOnId( $recordID )
		{
			$rand = rand( );
			$values = 
			[ 
				'stringfield1' 	=> null , 					// test null value
				'stringfield2' 	=> '' , 					// test empty value
				'stringfield3' 	=> 'some updated value' , 	// test string value
				'intfield' 		=> $rand , 				// test integer value
				'boolfield' 	=> true					// test bool value 
			];
			$query = static::$_qb->table( 'test_table' )
						->update( $values , $recordID ) 
						->run( );
			$this->assertEquals( 1 , $query );
			$record = static::$_qb->table( 'test_table' )->find( $recordID );
			$this->assertNull( $record->stringfield1 );
			$this->assertEmpty( $record->stringfield2 );
			$this->assertEquals( 'some updated value' , $record->stringfield3 );
			$this->assertTrue( is_numeric( $record->intfield ) );
			$this->assertEquals( $rand , $record->intfield );
			$this->assertTrue( ( bool ) $record->boolfield );
		}	
		/**
		* @depends testGetLastInsertId
		* @depends testUpdateRecordBasedOnId
		*/		
		public function testUpdateRecords( )
		{
			$rand = rand( );
			$values = 
			[ 
				'stringfield1' 	=> null , 						// test null value
				'stringfield2' 	=> '' , 						// test empty value
				'stringfield3' 	=> 'some other updated value' , 	// test string value
				'intfield' 		=> $rand , 					// test integer value
				'boolfield' 	=> true						// test bool value 
			];
			$query = static::$_qb->table( 'test_table' )
						 ->where( 'stringfield3' , '=' , 'some updated value' )
						->update( $values ) 
						->run( );
			$this->assertEquals( 1 , $query );
			$record = static::$_qb->table( 'test_table' )
						->where( 'intfield' , '=' , $rand )
						->row( );
			$this->assertNull( $record->stringfield1 );
			$this->assertEmpty( $record->stringfield2 );
			$this->assertEquals( 'some other updated value' , $record->stringfield3 );
			$this->assertTrue( is_numeric( $record->intfield ) );
			$this->assertEquals( $rand , $record->intfield );
			$this->assertTrue( ( bool ) $record->boolfield );
		}	
		/**
		* @depends testGetLastInsertId
		* @depends testUpdateRecordBasedOnId
		* @depends testUpdateRecords
		*/		
		public function testDeleteRecordBasedOnId( $recordID )
		{
			$query = static::$_qb->table( 'test_table' )->delete( $recordID )->run( );
			$this->assertEquals( 1 , $query );
		}			
	
		public function testDeleteRecords( )
		{
			$rand = rand( );
			$values = [ 'intfield' => $rand ];
			$query = static::$_qb->table( 'test_table' )->insert( $values )->run( );
			$this->assertEquals( 1 , $query );
			$query = static::$_qb->table( 'test_table' ) 
						->where( 'intfield' , '=' , $rand )
						->delete( )
						->run( );
			$this->assertEquals( 1 , $query );
		}
		/**
		* @depends testDeleteRecords
		*/	
		public function testAffectedRows( )
		{
			$rows = static::$_qb->countRows( );	
			$this->assertEquals( 1 , $rows );
		}
		/**
		* @depends testInsert
		*/
		public function testRawStatement( )
		{
			$query = static::$_qb->table( 'test_table' )
						->where( 'stringfield3' , '=' , 'some value' )
						->or_where( 'stringfield3' , 'is' , null )
						->rawSelect( 'ORDER BY id ASC' )
						->run( );
			$this->assertEquals( 1 , $query[ 0 ]->id );
			$this->assertEquals( 2 , $query[ 1 ]->id );			
		}
		/**
		* @depends testInsert
		*/
		public function testSelectWithRawValue( )
		{
			$query = static::$_qb->table( 'test_table' )
						->where( 'stringfield1' , '!=' , static::$_qb->raw( 'NOW()' ) )
						->run( );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );	
		}
		
		public function testPrepareQuery( )
		{
			$fields = [ 'stringfield1' => ':value1' , 'intfield' => ':value2' ]; 
			$query = static::$_qb->table( 'test_table' )->insert( $fields )->prepare( );
			for ( $i = 0; $i < 2; $i++ )
			{
				$fields = [ ':value1' => 'some value ' . $i , ':value2' => rand( ) ];
				$result = static::$_qb->run( $query , $fields );
				$this->assertEquals( 1 , $result );
			}			
		}
		
		public function testSpecifyReturnType( )
		{
			$query = static::$_qb->run( 'SHOW COLUMNS FROM `test_table`' , '' ,  1 );
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		* @depends testSelectMultipleTables
		*/
		public function testSimpleJoin( )
		{
			$query = static::$_qb->table( 'test_table' )
						->join( 'test_table1' , 'test_table.id' , '=' ,  'test_table1.id' )
						->run( );			
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		/**
		* @depends testInsert
		* @depends testSelectMultipleTables
		*/
		public function testJoinWithClosure( )
		{
			$query = static::$_qb->table( 'test_table' )
						->left_join( 'test_table1' , function( $join )
						{
							$join->on(  'test_table.id' , '=' ,  'test_table1.id' )
								->or_on(  'test_table.id' , '=' ,  'test_table1.id' );
						} )->run( );			
			$this->assertTrue( ( sizeof( $query ) > 0 ) );
		}
		
		public function testQueryEvent( )
		{
			Event::listen( 'ptc.query' , function( $data )
			{
				Assert::assertStringStartsWith( 'INSERT INTO' , $data );
				Event::remove( 'ptc.query' , 0 );
			} );
			$this->testInsert( );
		}
	}