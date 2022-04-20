<?php

	/**
	* PTCMAPPER OBJECT RELATIONAL MAPPING CLASS EXAMPLE FILE
	* PTCMAPPER DEPENDS ON PTCDB AND PTCQUERYBUILDER COMPONENTS
	* THEREFORE, IT IS NOT A STAND-ALONE CLASS
	*
	* OBSERVERS CAN BE ADDED TO THE CLASS WITH THE PTCEVENT COMPONENT
	*
	*/
	
	use phptoolcase\Db as DB;
	use phptoolcase\Model;

	/*** DB DETAILS NEEDED FOR THE EXAMPLE TO WORK ***/
	$db[ 'host' ] = 'localhost';				// mysql host
	$db[ 'user' ] = 'root';					// mysql user
	$db[ 'pass' ] = '';						// mysql pass
	$db[ 'database' ] = 'testtoolcase';			// mysql database name
	/*************************************************************/
	
	require dirname(__FILE__) . '/../vendor/autoload.php';


	DB::add( array
	(
		'host'			=>	$db[ 'host' ] ,
		'user'			=>	$db[ 'user' ] ,
		'pass'			=>	$db[ 'pass' ] ,
		'db'				=>	$db[ 'database' ] ,
		'query_builder'		=>	true ,	// initialize the query builder
	) );
	
	/* CREATE THE EXAMPLE TABLE */
	DB::run( "DROP TABLE IF EXISTS `test_table`" );
	DB::run( "CREATE TABLE `test_table` 
	(
		`id` int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(`id`),
		`field1` varchar(255),
		`field2` varchar(255)
	)" );
	
	
	/* EXTENDING THE CLASS WITH THE TABLE NAME */
	class Test_Table extends Model
	{
		/* USING THE CLASS NAME AS TABLE */
		//protected static $_table = ''; 
		
		/* MAP FIELD NAMES IF "AS" IS USED IN A SELECT QUERY */
		//protected static $_map = array( 'field1' => 'test' ); 
	
		/* USE THIS PROPERTY IF THE TABLE USES ANOTHER COLUMN NAME FOR THE PRIMARY KEY */
		//protected static $_uniqueKey = 'id';
		
		/* OBSERVER EXAMPLE, WORKS WITH PTCEVENT COMPONENT */
		public static function saved( $data , $result )
		{
			// do some stuff here after save( ) is called
			//var_dump( $result );
			//var_dump( $data );
		}
	}
	
	
	/* USING OBSERVERS WITH PTCEVENT COMPONENT */
	//Test_Table::observe( ); // observe events
	//Test_Table::observe( 'some_class' ); // use other class as observer
	
	
	/* ADDING NEW RECORDS */
	$data = new Test_Table( );
	$data->field1 = 'some value';
	$data->field2 = 'some other value';
	$data->save( );
	

	/* CREATING NEW RECORD FROM ASSOCIATIVE ARRAY */
	$arr = array( 'field1' => 'created from array' , 'field2' => 'created from array' );
	$created = Test_Table::create( $arr );
	$created->save( );
	//Test_Table::create( $arr )->save( ); // in 1 line

	
	/* LAST INSERTED ID */
	print "<br><br><b>Last inserted id:</b> "; 
	print $last_id = Test_Table::lastId( );
	print "<br><br>";
	
	
	/* RETRIEVING 1 ROW BASED ON ID */
	$data = Test_Table::find( 1 );
	print "<b>Getting 1 row:</b><br>";
	print $data->field1 . "<br>";
	print $data->field2 . "<br><br>";
	
	
	/* RETRIEVING ALL RECORDS FROM TABLE */
	$data = Test_Table::all( );
	print "<b>Looping through all records:</b><br>";
	foreach( $data as $v )
	{
		print $v->field1 . "<br>";
		print $v->field2 . "<br>";
	}
	print "<br>";
	
	
	/* UPDATING RECORDS */
	$data = Test_Table::find( $last_id );
	$data->field1 = 'updated value';
	$data->field2 = 'updated value';
	$data->save( );
	
	
	/* DELETING RECORDS */
	$data = Test_Table::find( $last_id );
	$data->delete( ); // delete retrieved record
	//Test_Table::find( $last_id )->delete( ); // same as above but in 1 line
	//Test_Table::where('field1', '!=', 'some value' )->delete()->run( ); // using the query builder directly
	
	
	/* RETRIEVING ONLY 1 COLUMN VALUE */
	print '<b>Retrieve 1 column value based on id:</b> ';
	print Test_Table::get_field1( 1 ); // retrieving by id
	print "<br><br><b>Retrieving 1 column value based on query:</b> ";
	print Test_Table::get_field2( 'field1' , 'some value' ); // select field2 where field1 = 'created from array'
	print "<br><br>";
	
	
	/* UPDATING ONLY 1 COLUMN VALUE BASED ON ID */
	Test_Table::set_field1( 'new value saved' , 1 ); // set field1 = 'new value saved' where id = 1
	

	/* USING THE QUERY BUILDER METHODS DIRECTLY */
	$data = Test_Table::where( 'id' , '!=' , 2 )->run( );
	print "<b>Using the query builder directly:</b><br>";
	foreach( $data as $v )
	{
		print $v->field1 . "<br>";
		print $v->field2 . "<br>";
		/* CONVERT TO ARRAY */
		$to_arr = $v->toArray( );
		/* CONVERT TO JSON */
		$to_json = $v->toJson( );
	}
	print "<br>";
	
	
	/* GET THE COLUMN NAMES */
	print "<b>Getting table column names:</b> <pre>";
	print print_r( Test_Table::getColumns( ) , true ) . "</pre><br>";

