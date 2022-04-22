<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

	final class QueryBuilderTest extends TestCase
	{
		protected static $_connection = [ ];
		
		protected static $_qb = '';
	
		public static function setUpBeforeClass( )
		{
			static::$_connection =
			[
				'host' => 'localhost' ,			// mysql host
				'user' => 'root' ,				// mysql user
				'pass' => '' ,					// mysql pass
				'database' => 'testtoolcase'		// mysql database name
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
				`field1` varchar(255),
				`field2` varchar(255),
				`field3` varchar(255)
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
			$values = [ 'field1' => 'somevalue' , 'field2' => 'somevalue12' , 'field3' => 180 ];
			$query = static::$_qb->table( 'test_table' )->insert( $values )->run( );
			$this->assertEquals( 1 , $query );
		}
		
		public function testInsertWithNullValue( )
		{
			$values = [ 'field1' => 'somevalue' , 'field2' => 'somevalue12' ];
			$query = static::$_qb->table( 'test_table' )->insert( $values )->run( );
			$this->assertEquals( 1 , $query );
		}
		/**
		* @depends testInsert
		*/
		public function testGetAllRecords( )
		{
			$query = static::$_qb->table( 'test_table' )->run( );
			$this->assertTrue( is_array( $query ) );
		}
		/**
		* @depends testInsert
		*/
		public function testSelectQuery( )
		{
			$query = static::$_qb->table( 'test_table' )
							->where( 'field1' , '=' , 'somevalue' )
							->run( );
			$this->assertTrue( is_array( $query ) );
		}
	}