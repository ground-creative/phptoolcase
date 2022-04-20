<?php
	
	/* 
	* EXAMPLE FILE FOR PTCROUTER CLASS 
	*/
	
	use phptoolcase\Router;
	
	require dirname(__FILE__) . '/../../vendor/autoload.php';
	
	require_once( 'filters.php' ); // require a file with routes filters for the example
	
	
	/* Guess the base path to make the example work */
	$base_path = dirname( $_SERVER[ 'SCRIPT_NAME' ] );
	
	
	/* ADDING THE MAIN PAGE ROUTE */
	Router::get( $base_path , function( )
	{
		print 'called the main page';
	} )->map( 'index' ); // map route to be retrieved later
	
	
	/* RETRIEVE THE MAPPED ROUTE */
	//echo Router::getRoute( 'index' );
	
	
	/* ADDING A ROUTE FOR A POST REQUEST */
	Router::post( $base_path , function( )
	{
		print 'called the main page by post request';
	} );
	
		
	/* ADDING A ROUTE FOR ANY REQUEST */
	Router::any( $base_path . '/any-request' , function( )
	{
		print 'called any request uri';
	} );
	
	
	/* ADDING PARAMETERS TO ROUTES */
	Router::get( $base_path . '/user/{id}' , function( $id )
	{
		print "testing a parameter " . $id;
	} );
	
	
	/* ADDING PARAMETERS THAT MATCH CERTAIN PATTERNS */
	Router::get( $base_path . '/param-test/{lang}' , function( $lang )
	{
		print "testing a parameter against a pattern " . $lang;
	} )->where( 'lang' , 'es|en' );
	
	
	/* ADDING OPTIONAL PARAMETERS */
	Router::get( $base_path . '/post/{id?}' , function( $id = null )
	{
		print "testing optional parameter " . @$id;
	} );
	
		
	/* ADDING BEFORE AND AFTER FILTERS TO ROUTES */
	Router::get( $base_path . '/filter-test' , function( )
	{
		print "excuting route after filter ";
	} )->before( 'some.filter' )
	->after( 'some.filter' );
	
	
	/* GROUPING ROUTES WITH A PREFIX */
	Router::group( 'someName' , function( )
	{
		Router::get( '/' , function( )
		{
			print "testing main admin section";
		} );
		
		Router::get( 'somepage' , function( )
		{
			print "testing some admin section";
		} );
		
		/* NESTED GROUP, ALL ROUTES INSIDE ONLY WORK IF PROTOCOL IS HTTPS */
		Router::group( 'someOtherName' , function( )
		{
			Router::get( 'secure' , function( )
			{
				
			} );
		} )
		//->before( 'some.filter' ) // adding filters to all routes inside group
		->protocol( 'https' );
		
	} )
	//->domain( '{lang}.somedomain.com' ) // add a subdomain prefix for all routes of this group with pattern
	//->where( 'lang' , 'es|en' )
	->prefix( $base_path . '/admin' );
	
	
	/* ADDING A RESTFUL CONTROLLER TO HANDLE ROUTES */
	require_once( 'UserController.php' ); // just a test controller
	Router::controller( $base_path . '/controller' ,  'UserController' );
	
	
	/* 404 NOT FOUND PAGE */
	Router::notFound( 404 , function( ) // not found urls
	{
		print "the 404 callback was executed";
	} );
	
	
	/* EXECUTING THE ROUTES */
	Router::run( true );  // argument $checkErrors should be set to false in production