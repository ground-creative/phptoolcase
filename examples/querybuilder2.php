<?php

	/* 
	* EXAMPLE 2 FILE FOR PTCQUERYBUILDER CLASS
	* PREPARED QUERIES FROM THE QUERYBUILDER-EX1.PHP FILE WILL BE EXECUTED
	* RUN() , ROW() AND FIND() WIL BE USED TO EXECUTE  QUERIES INSTEAD OF PREPARE()
	* WHEN USING THE ABOVE METHODS, PLACE HOLDERS ARE NOT NEEDED
	*/
		
	use phptoolcase\QueryBuilder;
	
	/*** DB DETAILS NEEDED TO EXECUTE QUERIES ***/
	$db[ 'host' ] = 'localhost';				// mysql host
	$db[ 'user' ] = 'root';					// mysql user
	$db[ 'pass' ] = '';						// mysql pass
	$db[ 'database' ] = 'testtoolcase';			// mysql database name
	/*************************************************************/
	
	require dirname(__FILE__) . '/../vendor/autoload.php';

	$running = true;	// preventing the example1 file to print the queries
	
	require_once( 'querybuilder1.php' ); 	// require the example 1 file with the prepared queries
	
	/* INITIALIZING A PDO OBJECT TO RUN QUERIES WITH THE QUERYBUILDER */
	$pdo = new PDO( 'mysql:host=' . $db[ 'host' ] . ';dbname=' . $db[ 'database' ] . 
									';charset:uft8;' , $db[ 'user' ] , $db[ 'pass' ] );
	$pdo->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_OBJ ); // setting pdo default fetch mode


	/* INITIALIZING THE QUERY BUILDER WITH PDO SUPPORT */
	$qb = new QueryBuilder( $pdo );
	
	
	/* CREATE THE EXAMPLE TABLE */
	$qb->run( "DROP TABLE IF EXISTS `test_table`" );
	$qb->run( "CREATE TABLE `test_table` 
	(
		`id` int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(`id`),
		`field1` varchar(255),
		`field2` varchar(255),
		`field3` varchar(255)
	)" );


	/* INSERTING DATA WITH PREVIOUSLY PREPARED STATEMENT */
	$qb->run( $query_insert , array( ':value1' => 'somevalue' , ':value2' => 'somevalue12' , ':value3' => 180 ) ); 
	$qb->run( $query_insert , array( ':value1' => 'somevalue' , ':value2' => 'somevalue1' , ':value3' => 20 ) );
	$qb->run( $query_insert , array( ':value1' => 'somevalue' , ':value2' => 'somevalue12' , ':value3' => 200 ) );
	/* GET LAST INSERTED ID */
	$last_id = $qb->lastId( );
	print '<b>last inserted Id:</b> '. $last_id . '<br><br>';


	/* UPDATING DATA WITH PREVIOUSLY PREPARED STATEMENT */
	$qb->run( $query_update , array( ':value1' => 'somevalue' , 
					':value2' => 'insert id' , ':id' => $last_id ) ); // using last inserted id here
	$qb->run( $query_update1 , array( ':value1' => 'som32' , 
						':value2' => 'so 43' , ':value3' => 'somevalue12' ) );
	/* GET NUMBER OF AFFECTED ROWS BY LAST QUERY */
	print '<b>Number of affected rows by update query:</b> ' . $qb->countRows( ) . '<br><br>';


	/* SELECTING DATA WITH PREVIOUSLY PREPARED STATEMENTS */
	$fields = array( ':value1' => 'somevalue' , ':value2' => 'insert id' );
	print "<b>prepared select query result:</b> <pre>";
	print print_r( $qb->run( $query_where1 , $fields ) , true ) . "</pre><br>";
	/* LIMITING RESULTS */
	$fields = array( ':start' => 1 , ':end' => 10 );
	print "<b>prepared select query result with limit:</b> <pre>";
	print print_r( $qb->run( $query_where2 , $fields ) , true ) . "</pre><br>";
	/* USING WHERE BETWEEN */
	$fields = array( ':value1' => 170 , ':value2' => 300 );
	print "<b>prepared select between query result:</b> <pre>";
	print print_r( $qb->run( $query_between , $fields ) , true ) . "</pre><br>";
	/* USING WHERE IN */
	$fields = array( ':1' => 20 , ':2' => 180 , ':limit' => 10 );
	print "<b>prepared select where in query result:</b> <pre>";
	print print_r( $qb->run( $query_in , $fields ) , true ) . "</pre><br>";


	/* DELETING DATA WITH PREVIOUSLY  PREPARED STATEMENTS */
	$qb->run( $query_delete , array( ':id' => $last_id ) );
	print '<b>Number of affected rows by delete based on id query:</b> ';
	print $qb->countRows( ) . '<br><br>';
	$qb->run( $query_delete1 , array( ':value' => 'somevalue' ) );
	print '<b>Number of affected rows by delete based on where clause query:</b> ';
	print $qb->countRows( ) . '<br><br>';
	
	
	/* CREATE ONE MORE EXAMPLE TABLE FOR THE JOIN QUERY */
	$qb->run( "CREATE TABLE `test_table1` 
	(
		`id` int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(`id`),
		`field4` varchar(255)
	)" );
	
	
	/* RUNNING QUERIES WITH RUN() INSTEAD OF PREPARE(), NO PLACE HOLDERS NEEDED! */
	$qb->table( 'test_table1' )->insert( array( 'field4' => 'somevalue' ) )->run( );
	
	
	/* GET LAST INSERTED ID */
	$last_id = $qb->lastId( );
	print '<b>last inserted Id:</b> '. $last_id . '<br><br>';
	
	
	/* JOINING TABLES WITH PREVIOUSLY PREPARED QUERY, 
	REPLACE "left_" WITH THE TYPE OF JOIN YOUR ARE LOOKING FOR */
	$qb->run( $query_join );
	
	
	/* RETRIEVEING ONLY ONE ROW */
	print "<b>return only 1 row query result:</b> <pre>";
	print print_r( $qb->table( 'test_table1' )
				  ->where( 'field4' , '=' , 'somevalue' )
				  ->row( ) , true ) . '</pre><br><br>';
	
	
	/* RETRIEVEING ONLY ONE COLUMN VALUE */
	print "<b>return only column value:</b> ";
	print print_r( $qb->table( 'test_table1' )
				  ->where( 'field4' , '=' , 'somevalue' )
				  ->row( 'field4' ) , true ) . '<br><br>';
	
	
	/* SELECTING A ROW BASED ON ID */
	print "<b>return record with ->find(yourID) , shortcut for where('id' , '=' , yourID ):</b> <pre>";
	print print_r( $qb->table( 'test_table1' )->find( $last_id ) , true ) . '</pre><br><br>';
	
