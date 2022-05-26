<?php

	namespace phptoolcase;

	use PHPUnit\Framework\Assert;
	
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
	
	class Test_Table_Guard_Columns extends Model
	{
		protected static $_connectionName ='new connection';
	
		protected static $_table = 'test_table';
		
		protected static $_guard = [ 'stringfield1' , 'intfield' ];
	}
	
	class Test_Table_Custom_Unique_key extends Model
	{
		protected static $_connectionName ='new connection';
	
		protected static $_table = 'test_table_custom_key';
		
		protected static $_uniqueKey ='sid';
	}
	
	class Test_Table_Use_Boot_Method extends Model
	{
		protected static $_connectionName ='new connection';
	
		protected static $_table = 'test_table';
		
		public static function boot( )
		{
			static::observe( '\phptoolcase\TestObserver' );
		}
	}
	
	class Test_Table_Custom_Event_Class extends Model
	{
		protected static $_connectionName ='new connection';
	
		protected static $_table = 'test_table';
		
		protected static $_eventClass ='\phptoolcase\EventClass';
	}
	
	class Test_Table_Custom_Connection_Class extends Model
	{
		protected static $_connectionName ='new connection';
	
		protected static $_table = 'test_table';
		
		protected static $_connectionManager ='\phptoolcase\DBClass';
	}
	
	class TestObserver
	{
		public static function inserting( &$values )
		{
			//var_dump( $values );
			Assert::assertEquals( 'model test' , $values->stringfield1 );
			Assert::assertEquals( 'model test other value' , $values->stringfield2 );
			$values->stringfield1 = 'some new observer value';
			$values->stringfield2 = 'another new observer value';
		}
		
		public static function inserted( $values , $result )
		{
			Assert::assertEquals( 1 , $result );
			Assert::assertEquals( 'some new observer value' , $values->stringfield1 );
			Assert::assertEquals( 'another new observer value' , $values->stringfield2 );
		}
		
		public static function updating( &$values )
		{
			Assert::assertEquals( 'model updated value' , $values->stringfield1 );
			Assert::assertEquals( 'model updated value again' , $values->stringfield2 );
			$values->stringfield1 = 'some new observer updated value';
			$values->stringfield2 = 'another new observer updated value';
		}
		
		public static function updated( $values , $result )
		{
			Assert::assertEquals( 1 , $result );
			Assert::assertEquals( 'some new observer updated value' , $values->stringfield1 );
			Assert::assertEquals( 'another new observer updated value' , $values->stringfield2 );
		}
		
		public static function saving( &$values )
		{
			Assert::assertStringStartsWith( 'model' , $values->stringfield1 );
		}
		
		public static function saved( $values , $result )
		{
			Assert::assertEquals( 1 , $result );
			Assert::assertStringEndsWith( 'value' , $values->stringfield1 );
		}
		
		public static function deleting( &$id )
		{
			Assert::assertTrue( is_numeric( $id ) );
		}
		
		public static function deleted( $id , $values )
		{
			Assert::assertTrue( is_numeric( $id ) );
			Assert::assertEquals( 'some new observer value' , $values->stringfield1 );
			Assert::assertEquals( 'another new observer value' , $values->stringfield2 );
		}
	}
	
	class EventClass extends Event
	{
	
	}
	
	class DBClass extends Db
	{
	
	}