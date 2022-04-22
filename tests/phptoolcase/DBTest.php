<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use phptoolcase\Db as DB;

	final class DBTest extends TestCase
	{
		public function setup( )
		{
			$this->connection =
			[
				'host' => 'localhost' ,			// mysql host
				'user' => 'root' ,				// mysql user
				'pass' => '' ,					// mysql pass
				'database' => 'testtoolcase'		// mysql database name
			];
		}
	
		public function testAddConnection( )
		{
			$con = DB::add( array
			(
				'host'			=>	$this->connection[ 'host' ] ,
				'user'			=>	$this->connection[ 'user' ] ,
				'pass'			=>	$this->connection[ 'pass' ] ,
				'db'				=>	$this->connection[ 'database' ] ,
				'query_builder'		=>	true ,	// initialize the query builder
				'pdo_attributes'	=> 			// adding pdo attributes
				[
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC 
				]
			) , 'new connection' );
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