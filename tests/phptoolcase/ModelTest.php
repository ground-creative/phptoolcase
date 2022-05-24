<?php

	namespace phptoolcase;

	use PHPUnit\Framework\TestCase;

	/**
	* @requires extension pdo
	*/
	final class ModelTest extends TestCase
	{
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testGetAllRecords( )
		{
			$data = Test_Table::all( );
			$this->assertTrue( ( sizeof( $data ) > 0 ) );
		}
		/**
		* @Depends DBTest::testAddConnection
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
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testSpecifyTableName( )
		{
			$data = Test_Table_Custom_Name::all( );
			$this->assertTrue( ( sizeof( $data ) > 0 ) );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testUsingTheQueryBuilderDirectly( )
		{
			$data = Test_Table::where( 'id' , '!=' , 200 )->run( );
			$this->assertTrue( ( sizeof( $data ) > 0 ) );
		}
		/**
		* @Depends DBTest::testAddConnection
		*/		
		public function testAddRecord( )
		{
			$row = new Test_Table( );
			$row->stringfield1 = 'model test';
			$row->stringfield2 = 'model test other value';
			$row->save( );
			$data = Test_Table::order( 'id' , 'desc' )->limit( 1 )->run( );
			$this->assertEquals( 'model test' , $data[ 0 ]->stringfield1 );
			$this->assertEquals( 'model test other value' , $data[ 0 ]->stringfield2 );
		}
		/**
		* @Depends DBTest::testAddConnection
		*/		
		public function testAddRecordFromAssociativeArray( )
		{
			$arr = [ 'stringfield1' => 'created from array' , 'stringfield2' => 'created from array again' ];
			$row = Test_Table::create( $arr );
			$row->save( );
			$data = Test_Table::order( 'id' , 'desc' )->limit( 1 )->run( );
			$this->assertEquals( 'created from array' , $data[ 0 ]->stringfield1 );
			$this->assertEquals( 'created from array again' , $data[ 0 ]->stringfield2 );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @depends testAddRecord
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
		* @Depends DBTest::testAddConnection
		* @depends testAddRecord
		* @depends testUpdateRecord
		*/		
		public function testDeleteRecord( )
		{
			Test_Table::find( 1 )->delete( );
			$row = Test_Table::find( 1 );
			$this->assertNull($row );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @depends testAddRecord
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
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsert
		*/		
		public function testConvertValuesToArray( )
		{
			$row = Test_Table::find( 2 );
			$this->assertTrue( is_array( $row->toArray( ) ) );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsert
		*/		
		public function testConvertValuesToJson( )
		{
			$row = Test_Table::find( 2 );
			$this->assertNotNull( json_decode( $row->toJson( ) ) );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsertWithPartialValues
		*/		
		public function testRetrieveSingleValueById( )
		{
			$val = Test_Table::get_stringfield1( 2 );
			$this->assertEquals( 'some string' , $val );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsertWithPartialValues
		*/		
		public function testRetrieveSingleValueWithColumn( )
		{
			$val = Test_Table::get_stringfield1( 'stringfield1' , 'some string' );
			$this->assertEquals( 'some string' , $val );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsertWithPartialValues
		*/		
		public function testUpdateSingleValueById( )
		{
			Test_Table::set_stringfield1( 'new single value' , 2 );
			$val = Test_Table::get_stringfield1( 2 );
			$this->assertEquals( 'new single value' , $val );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @depends testUpdateSingleValueById
		*/	
		public function testMapFieldNames( )
		{
			$data = Test_Table::select( 'stringfield1 as str' )
						->where( 'stringfield1' , '=' , 'new single value' )
						->run( );
			$this->assertEquals( 'new single value' , $data[ 0 ]->str );
		}
		/**
		* @Depends DBTest::testAddConnection
		*/			
		public function testFindWithEmptyResult( )
		{
			$record = Test_Table::find( 10000 );
			$this->assertNull( $record );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsert
		*/
		public function testRemoveValue( )
		{
			$data = Test_Table::all( );
			$data[ 0 ]->remove( 'stringfield1' );
			$this->assertFalse( array_key_exists( 'stringfield1' , $data[ 0 ] ) );
		}
		/**
		* @Depends DBTest::testAddConnection
		* @Depends QueryBuilderTest::testInsert
		*/	
		public function testResetValues( )
		{
			$data = Test_Table::all( );
			$data[ 0 ]->reset( );
			$this->assertCount( 0 , $data[ 0 ]->toArray( ) );
		}
		/**
		* @Depends DBTest::testAddConnection
		*/			
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
		/**
		* @Depends DBTest::testAddConnection
		*/			
		public function testGetTableName( )
		{
			$this->assertEquals( 'test_table' , Test_Table::getTable( ) );
		}
		/**
		* @Depends DBTest::testAddConnection
		*/			
		public function testGuardColumns( )
		{
		
		}
	}
	
	class Test_Table extends Model
	{
		protected static $_connectionName ='new connection';
		
		protected static $_map = [ 'stringfield1' => 'str' ]; 
	}
	
	class Test_Table_Custom_Name extends Model
	{
		protected static $_connectionName ='new connection';
	
		protected static $_table = 'test_table';
	}