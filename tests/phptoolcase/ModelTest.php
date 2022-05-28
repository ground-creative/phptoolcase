<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;
	use phptoolcase\Db as DB;

	/**
	* @requires extension pdo
	*/
	final class ModelTest extends TestCase
	{
		protected static $_connection = [ ];
		
		public static function setUpBeforeClass( )
		{
			DB::add(
			[
				'host'			=>	$GLOBALS[ 'DB_HOST' ] ,
				'user'			=>	$GLOBALS[ 'DB_USER' ] ,
				'pass'			=>	$GLOBALS[ 'DB_PASSWORD' ] ,
				'db'				=>	$GLOBALS[ 'DB_DBNAME' ] ,
				'query_builder'		=>	true ,	// initialize the query builder
				'pdo_attributes'	=> 			// adding pdo attributes
				[
					\PDO::ATTR_DEFAULT_FETCH_MODE =>	\PDO::FETCH_ASSOC ,
					\PDO::ATTR_ERRMODE			 =>	\PDO::ERRMODE_WARNING
				]
			] );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testGetAllRecords( )
		{
			$data = Test_Table::all( );
			$this->assertTrue( ( sizeof( $data ) > 0 ) );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testFindOneRecordById( )
		{
			$record = Test_Table::find( 1 ); 
			$this->assertNull( $record->stringfield1 );
			$this->assertEmpty( $record->stringfield2 );
			$this->assertEquals( 'some value' , $record->stringfield3 );
			$this->assertTrue( is_numeric( $record->intfield ) );
			$this->assertFalse( ( bool ) $record->boolfield );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testSpecifyTableName( )
		{
			$data = Test_Table_Custom_Name::all( );
			$this->assertTrue( ( sizeof( $data ) > 0 ) );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testUsingTheQueryBuilderDirectly( )
		{
			$data = Test_Table::where( 'id' , '!=' , 200 )->run( );
			$this->assertTrue( ( sizeof( $data ) > 0 ) );
		}
	
		public function testInsertRecord( )
		{
			$row = new Test_Table( );
			$row->stringfield1 = 'model test';
			$row->stringfield2 = 'model test other value';
			$row->save( );
			$data = Test_Table::order( 'id' , 'desc' )->limit( 1 )->run( );
			$this->assertEquals( 'model test' , $data[ 0 ]->stringfield1 );
			$this->assertEquals( 'model test other value' , $data[ 0 ]->stringfield2 );
		}
	
		public function testInsertRecordFromAssociativeArray( )
		{
			$arr = [ 'stringfield1' => 'created from array' , 'stringfield2' => 'created from array again' ];
			$row = Test_Table::create( $arr );
			$row->save( );
			$data = Test_Table::order( 'id' , 'desc' )->limit( 1 )->run( );
			$this->assertEquals( 'created from array' , $data[ 0 ]->stringfield1 );
			$this->assertEquals( 'created from array again' , $data[ 0 ]->stringfield2 );
		}
		/**
		* @depends testInsertRecord
		*/		
		public function testUpdateRecord( )
		{
			$rand = rand( );
			$row = Test_Table::find( 1 ); 
			$row->stringfield1 = 'model updated value ' . $rand;
			$row->stringfield2 = 'model updated value again ' . $rand;
			$row->save( );
			$row = Test_Table::find( 1 ); 
			$this->assertEquals( 'model updated value ' . $rand , $row->stringfield1 );
			$this->assertEquals( 'model updated value again ' . $rand , $row->stringfield2 );
		}
		/**
		* @depends testInsertRecord
		* @depends testUpdateRecord
		*/		
		public function testDeleteRecord( )
		{
			Test_Table::find( 1 )->delete( );
			$row = Test_Table::find( 1 );
			$this->assertNull($row );
		}
		/**
		* @depends testInsertRecord
		*/		
		public function testDeleteRecordWithQueryBuilder( )
		{
			$arr = [ 'stringfield1' => 'delete record with query builder' ];
			$row = Test_Table::create( $arr );
			$row->save( );
			Test_Table::where( 'stringfield1' , '=' , 'delete record with query builder' )->delete( )->run( );
			$data = Test_Table::where( 'stringfield1'  , '=' , 'delete record with query builder' )->run( );
			$this->assertTrue( ( sizeof( $data ) == 0 ) );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/		
		public function testConvertValuesToArray( )
		{
			$row = Test_Table::find( 2 );
			$this->assertTrue( is_array( $row->toArray( ) ) );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/		
		public function testConvertValuesToJson( )
		{
			$row = Test_Table::find( 2 );
			$this->assertNotNull( json_decode( $row->toJson( ) ) );
		}
		/**
		* @Depends QueryBuilderTest::testInsertWithPartialValues
		*/		
		public function testRetrieveSingleValueById( )
		{
			$val = Test_Table::get_stringfield1( 2 );
			$this->assertEquals( 'some string' , $val );
		}
		/**
		* @Depends QueryBuilderTest::testInsertWithPartialValues
		*/		
		public function testRetrieveSingleValueWithColumn( )
		{
			$val = Test_Table::get_stringfield1( 'stringfield1' , 'some string' );
			$this->assertEquals( 'some string' , $val );
		}
		/**
		* @Depends QueryBuilderTest::testInsertWithPartialValues
		*/		
		public function testUpdateSingleValueById( )
		{
			Test_Table::set_stringfield1( 'new single value' , 2 );
			$val = Test_Table::get_stringfield1( 2 );
			$this->assertEquals( 'new single value' , $val );
		}
		/**
		* @depends testUpdateSingleValueById
		*/	
		public function testMapFieldNames( )
		{
			$data = Test_Table::select( 'stringfield1 as str' )
						->where( 'stringfield1' , '=' , 'new single value' )
						->run( );
			$this->assertEquals( 'new single value' , $data[ 0 ]->str );
		}
		
		public function testFindWithEmptyResult( )
		{
			$record = Test_Table::find( 10000 );
			$this->assertNull( $record );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testRemoveValue( )
		{
			$data = Test_Table::all( );
			$data[ 0 ]->remove( 'stringfield1' );
			$this->assertFalse( array_key_exists( 'stringfield1' , $data[ 0 ] ) );
		}
		/**
		* @Depends QueryBuilderTest::testInsert
		*/	
		public function testResetValues( )
		{
			$data = Test_Table::all( );
			$data[ 0 ]->reset( );
			$this->assertCount( 0 , $data[ 0 ]->toArray( ) );
		}
		
		public function testGetColumns( )
		{
			$columns = Test_Table::getColumns( );
			$this->assertTrue( array_key_exists( 'id' , $columns ) );
			$this->assertTrue( array_key_exists( 'stringfield1' , $columns ) );
			$this->assertTrue( array_key_exists( 'stringfield2' , $columns ) );
			$this->assertTrue( array_key_exists( 'stringfield3' , $columns ) );
			$this->assertTrue( array_key_exists( 'intfield' , $columns ) );
			$this->assertTrue( array_key_exists( 'boolfield' , $columns ) );
		}

		public function testGetTableName( )
		{
			$this->assertEquals( 'test_table' , Test_Table::getTable( ) );
		}
	
		public function testGuardColumns( )
		{
			$data = Test_Table_Guard_Columns::all( );
			$this->assertFalse( array_key_exists( 'stringfield1' , $data[ 0 ] ) );
			$this->assertFalse( array_key_exists( 'intfield' , $data[ 0 ] ) );
		}
			
		public function testObserverInsertEvent( )
		{
			Test_Table::observe( '\phptoolcase\TestObserver' );
			$row = new Test_Table( );
			$row->stringfield1 = 'model test';
			$row->stringfield2 = 'model test other value';
			$row->save( );
		}
		/**
		* @depends testObserverInsertEvent
		*/			
		public function testObserverUpdateEvent( )
		{
			/*$row = Test_Table::all( ); 
			$row[ 0 ]->stringfield1 = 'model updated value';
			$row[ 0 ]->stringfield2 = 'model updated value again';
			$row[ 0 ]->save( );*/
		}
		/**
		* @depends testObserverInsertEvent
		*/		
		public function testObserverDeleteEvent( )
		{
			/*$row = new Test_Table( );
			$row->stringfield1 = 'model test';
			$row->stringfield2 = 'model test other value';
			$row->save( );
			$last_id = Test_Table::lastId( );
			Test_Table::find( $last_id )->delete( );*/
		}
	
		public function testAddOptionsOnInitilization( )
		{
			/*$row = new Test_Table_Use_Boot_Method( );
			$row->stringfield1 = 'model test';
			$row->stringfield2 = 'model test other value';
			$row->save( );*/
		}
	
		public function testCustomUniqueKey( )
		{
			$qb = DB::getQB( 'new connection' );
			$qb->run( "DROP TABLE IF EXISTS `test_table_custom_key`" );
			$qb->run( "CREATE TABLE `test_table_custom_key` 
			(
				`sid` int NOT NULL AUTO_INCREMENT, 
				PRIMARY KEY(`sid`),
				`stringfield1` varchar(255),
				`stringfield2` varchar(255),
				`stringfield3` varchar(255),
				`intfield` int(11) ,
				`boolfield` tinyint(1)
			)" );
			$row = new Test_Table_Custom_Unique_key( );
			$row->stringfield1 = 'model test';
			$row->stringfield2 = 'model test other value';
			$row->save( );
			$last_id = Test_Table_Custom_Unique_key::lastId( );
			$record = Test_Table_Custom_Unique_key::find( $last_id );
			$this->assertTrue( array_key_exists( 'sid' , $record->toArray( ) ) );
		}
	
		public function testCustomEventClass( )
		{
			/*Test_Table_Custom_Event_Class::observe( '\phptoolcase\TestObserver' );
			$row = new Test_Table_Custom_Event_Class( );
			$row->stringfield1 = 'model test';
			$row->stringfield2 = 'model test other value';
			$row->save( );*/
		}
		/**
		* @Depends DBTest::testAddConnection
		*/	
		public function testCustomConnectionName( )
		{
			$records = Test_Table_Custom_Connection_Name::order( 'id' , 'desc' )
						->limit( 1 )
						->run( );
			$this->assertTrue( ( sizeof( $records ) == 1 ) );	
		}

		public function testCustomConnectionManagerClass( )
		{
			$records = Test_Table_Custom_Connection_Class::order( 'id' , 'desc' )
						->limit( 1 )
						->run( );
			$this->assertTrue( ( sizeof( $records ) == 1 ) );	
		}
	}