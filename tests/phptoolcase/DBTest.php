<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use phptoolcase\Db as DB;

	/**
	* @requires extension pdo
	*/
	final class DBTest extends TestCase
	{
		protected static $_connection = [ ];
	
		public static function setUpBeforeClass( ) : void
		{
			static::$_connection =
			[
				'host' => $GLOBALS[ 'DB_HOST' ] ,			// mysql host
				'user' =>  $GLOBALS[ 'DB_USER' ] ,		// mysql user
				'pass' => $GLOBALS[ 'DB_PASSWORD' ] ,	// mysql pass
				'database' => $GLOBALS[ 'DB_DBNAME' ]	// mysql database name
			];
		}
	
		public function testAddConnection( )
		{
			$con = DB::add(
			[
				'host'			=>	static::$_connection[ 'host' ] ,
				'user'			=>	static::$_connection[ 'user' ] ,
				'pass'			=>	static::$_connection[ 'pass' ] ,
				'db'				=>	static::$_connection[ 'database' ] ,
				'query_builder'		=>	true ,	// initialize the query builder
				'pdo_attributes'	=> 			// adding pdo attributes
				[
					\PDO::ATTR_DEFAULT_FETCH_MODE =>	\PDO::FETCH_ASSOC ,
					\PDO::ATTR_ERRMODE			 =>	\PDO::ERRMODE_WARNING
				]
			] , 'new connection' );
			$this->assertTrue( is_array( $con ) );
			return $con;
		}
		/**
		* @depends testAddConnection
		*/
		public function testGetConnection( )
		{
			$con = DB::getConnection( 'new connection' );
			$this->assertTrue( is_array( $con ) );
			return $con;
		}
		/**
		* @depends testAddConnection
		*/		
		public function testAllConnections( )
		{
			$all_con = DB::getConnections( );
			$this->assertTrue( is_array( $all_con ) );
			return $all_con;
		}
		/**
		* @depends testAddConnection
		*/
		public function testGetQueryBuilder( )
		{
			$qb = DB::getQB( 'new connection' );
			$this->assertInstanceOf( QueryBuilder::class , $qb );
			return $qb;
		}
		/**
		* @depends testAddConnection
		*/
		public function testGetPDO( )
		{
			$pdo = DB::getPDO( 'new connection' );
			$this->assertInstanceOf( \PDO::class , $pdo );
			return $pdo;
		}
		/**
		* @depends testGetQueryBuilder
		*/
		public function testCallQueryBuilderDirectly( $qb )
		{
			$statement = $qb->table( 'test_table' )
							->select( 'some_column as test' )
							->prepare( );
			$this->assertTrue( is_string( $statement ) );
		}
	}