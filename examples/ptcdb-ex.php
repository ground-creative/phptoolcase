<?
	/* 
	* EXAMPLE FILE FOR PTCDB DATABASE CONNECTION MANAGER CLASS 
	*/
	
	/*** DB DETAILS NEEDED FOR THE EXAMPLE TO WORK ***/
	$db[ 'user' ] = 'username';				// mysql user
	$db[ 'pass' ] = 'password';				// mysql pass
	$db[ 'database' ] = 'database name';		// mysql database name
	/*************************************************************/
	
	require_once( '../PtcDb.php' ); // including the PtcDb class
	
	
	/* ADDING A CONNECTION, THIS WILL BE THE DEFAULT CONNECTION */
	PtcDb::add( array
	(
		'user'	=>	$db[ 'user' ],
		'pass'	=>	$db[ 'pass' ],
		'db'		=>	$db[ 'database' ],
	) );
	
	
	/* GETTING THE CONNECTION DETAILS */
	$conn_details = PtcDb::getConnection( 'default' ); // gets all connections if name param is not specified
	var_dump( $conn_details );
	
	
	/* GETTING THE PDO OBJECT */
	$pdo_object = PtcDb::getPdo( 'default' );

	
	/* USING PDO TO EXECUTE QUERIES WITH THE DEFAULT CONNECTION */
	$query = PtcDb::prepare( 'SELECT * FROM some table' ); //  same as $pdo_object->prepare( );
	//$query->execute( );
	//var_dump( $query->fetchAll( ) );

	
	/* 
	* THE FOLLOWING LINES REQUIRE THE PTCQUERYBUILDER.PHP FILE
	* COMMENT LINE 44 TO EXECUTE THE REST OF THE CODE
	*/

	die();	

	/* ADDING ANOTHER DATABASE CONNECTION WITH THE QUERY BUILDER CLASS SUPPORT */
	require_once( '../PtcQueryBuilder.php' ); // including the Query Builder class
	PtcDb::add( array
	(
		'user'			=>	$db[ 'user' ],
		'pass'			=>	$db[ 'pass' ],
		'db'				=>	$db[ 'database' ],
		'query_builder'		=>	true,	// initialize the query builder
		'pdo_attributes'	=> 	array	// adding pdo attributes
		( 
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
		)
	) , 'new connection' );
	
	
	/* GETTING THE QUERY BUILDER OBJECT */
	$qb = PtcDb::getQB( 'new connection' );
	
	
	/* RUNNING THE QUERY BUILDER , REFER TO THE QUERY BUILDER EXAMPLE FILES FOR USAGE */
	echo '<b>prepare select statement:</b> ' . $qb->table( 'test_table' )->select( 'some_column as test' )->prepare( );
	
