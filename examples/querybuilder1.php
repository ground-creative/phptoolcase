<?php

	/* 
	* EXAMPLE 1 FILE FOR PTCQUERYBUILDER CLASS, PREPARING QUERIES FOR LATER USAGE
	* ALL QUERIES THAT ARE PREPARED WITH THE QUERYBUILDER, NEED PLACE HOLDERS
	* TO SEE THE QUERIES EXECUTED REFER TO QUERYBUILDER2.PHP EXAMPLE FILE
	*/
	
	use phptoolcase\QueryBuilder;
	
	require dirname(__FILE__) . '/../vendor/autoload.php';
	
	
	$qb = new QueryBuilder( );	// initializing the class
	
	
	/* SELECT ALL RECORDS */
	$query = $qb->table( 'test_table' )->prepare( );


	/* SELECT WITH COLUMNS SPECIFIED */
	$query_with_columns = $qb->table( 'test_table' )->select( 'some_column as test' )->prepare( );

	
	/* SELECT WITH A WHERE CLAUSE  */
	$query_where = $qb->table( 'test_table' )->where( 'field1' , '=' , ':value' )->prepare( );


	/* SELECT WITH WHERE OR WHERE CLAUSE */
	$query_where1 = $qb->table( 'test_table' )
					   ->where( 'field1' , '=' , ':value1' )
					   ->or_where( 'field2' , '=' , ':value2' ) // adds "OR" to the query
					  //->where( 'field2' , '=' , ':value3' ) // adds "AND" to the query 
					   ->prepare( );

	
	/* SELECT WITH GROUP BY, ORDER AND LIMIT */
	$query_where2 = $qb->table( 'test_table' )
					 //->group( 'field1' ) // group by method
					   ->order( 'field1' , 'desc' )
					   ->limit( ':start' , ':end' ) // place holders are used for limit
					   ->prepare( );

	
	/* INSERT */
	$fields = array( 'field1' => ':value1' , 'field2' => ':value2' ,'field3' => ':value3' ); 
	$query_insert = $qb->table( 'test_table' )->insert( $fields )->prepare( );

	
	/* UPDATE BASED ON ID */
	$fields = array( 'field1' => ':value1' , 'field2' => ':value2' ); 
	$query_update = $qb->table( 'test_table' )->update( $fields , ':id' )->prepare( );


	/* UPDATE BASED ON A WHERE CLAUSE */
	$fields = array( 'field1' => ':value1' , 'field2' => ':value2' );
	$query_update1 = $qb->table( 'test_table' )
					    ->where( 'field2' , '=' , ':value3' )
					    ->update( $fields )->prepare( );


	/* DELETE BASED ON ID */
	$fields = array( 'user_email' => ':value' , 'user_nicename' => ':value1' ); 
	$query_delete = $qb->table( 'test_table' )->delete( ':id' )->prepare( );

	
	/* DELETE BASED ON WHERE CLAUSE */
	$query_delete1 = $qb->table( 'test_table' )
					   ->where( 'field1' , '=' , ':value' )
					   ->delete( )
					   ->prepare( );

	
	/* ADDING A RAW VALUE INSTEAD OF A PLACE HOLDER , RAW( ) CAN BE USED */
	$query_raw = $qb->table( 'test_table' )->where( 'field1' , '!=' , $qb->raw( 'NOW()' ) )->prepare( );


	/* JOINING TABLES , REPLACE "left_" WITH THE TYPE OF JOIN YOUR ARE LOOKING FOR */
	$query_join = $qb->table( 'test_table' )
				    ->left_join( 'test_table1' , 'test_table' . '.id' , '=' ,  'test_table1.id' )
				    ->prepare( );


	/* SELECT WITH WHERE BETWEEN CLAUSE */
	$query_between = $qb->table( 'test_table' )
					     ->where_between( 'field3' , ':value1' , ':value2' )
					   //->where_not_between( 'field3' , ':value1' , ':value2' )
					   //->or_where_between( 'field3' , ':value1' , ':value2' )
					  // ->or_where_not_between( 'field3' , ':value1' , ':value2' )
					     ->prepare( );


	/* SELECT WITH WHERE IN CLAUSE */
	$query_in = $qb->table( 'test_table' )
					   ->where_in( 'field3' , array( ':1' , ':2' ) )
					   //->where_not_in( 'column' , array( ':1' , ':2' ) )
					   //->or_where_in( 'column' , array( ':1' , ':2' ) )
					   //->or_where_not_in( 'column' , array( ':1' , ':2' ) )
					   ->limit( ':limit' )
					   ->prepare( );
	
	
	/* COMPLICATED WHERE CLAUSE, USING CLOSURES */
	$query_where3 = $qb->table( 'test_table' )
		->where( 'field1', '!=', ':value1' )
		->or_where( function( $query ) // will generate " AND ( field3 > 10 OR field3 < 100 )
		{
			$query->where( 'field3', '>', ':value2' )
				   ->where( 'field3' , '<',  ':value3' );
		} )->prepare( );
		

	/* COMPLICATED JOIN CLAUSE, USING CLOSURES */
	$query_join1 = $qb->table( 'test_table' )
				        ->left_join( 'test_table1' , function( $join )
				       {
						$join->on(  'test_table' . '.id' , '=' ,  'test_table1.id' )
							//->or_on(  'test_table' . '.id' , '=' ,  'test_table1.id' )
							->on(  'test_table' . '.id' , '=' ,  'test_table1.id' );
				        })->prepare( );


	if ( !isset( $running ) ) // preventing the example 1 file to print the queries if example 2 file is used
	{ 
		/* PRINTING THE PREPARED STATEMENTS */
		print $query . '<br><br>';
		print $query_with_columns . '<br><br>';
		print $query_where . '<br><br>';
		print $query_where1 . '<br><br>';
		print $query_where2 . '<br><br>';
		print $query_insert . '<br><br>';
		print $query_update . '<br><br>';
		print $query_update1 . '<br><br>';
		print $query_delete . '<br><br>';
		print $query_delete1 . '<br><br>';
		print $query_raw . '<br><br>';
		print $query_join . '<br><br>';
		print $query_between . '<br><br>';
		print $query_in . '<br><br>';
		print $query_where3 . '<br><br>';
		print $query_join1 . '<br><br>';
	}
	
	// REFER TO PTCQUERYBUILDER-EX2.PHP FILE TO SEE THE QUERIES EXECUTED

