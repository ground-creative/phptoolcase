<?php

	/* 
	* EXAMPLE FILE FOR PTCDB DATABASE CONNECTION MANAGER CLASS 
	*/
	
	use phptoolcase\Db as DB;
	
	/*** DB DETAILS NEEDED FOR THE EXAMPLE TO WORK ***/
	$db[ 'user' ] = 'root';					// mysql user
	$db[ 'pass' ] = '';						// mysql pass
	$db[ 'database' ] = 'database name';		// mysql database name
	/*************************************************************/

	require dirname(__FILE__) . '/../vendor/autoload.php';
	
	DB::add( array
	(
		'user'			=>	$db[ 'user' ],
		'pass'			=>	$db[ 'pass' ],
		'db'				=>	$db[ 'database' ],
		'query_builder'		=>	true,	// initialize the query builder
		'pdo_attributes'	=> 		// adding pdo attributes
		[
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC 
		]
	) , 'new connection' );
	
	/* GETTING THE QUERY BUILDER OBJECT */
	$qb = DB::getQB( 'new connection' );
	
	/* RUNNING THE QUERY BUILDER , REFER TO THE QUERY BUILDER EXAMPLE FILES FOR USAGE */
	echo '<b>prepare select statement:</b> ' . $qb->table( 'test_table' )->select( 'some_column as test' )->prepare( );
	
	