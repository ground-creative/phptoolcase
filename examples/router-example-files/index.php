<?php
	
	/* 
	* EXAMPLE FILE FOR PTCROUTER CLASS 
	*/
	
	require_once( '../../PtcRouter.php' ); // including the PtcRouter class
	require_once( 'filters.php' ); // require a file with routes filters for the example
	
	
	/* Guess the base path to make the example work */
	$base_path = dirname( $_SERVER[ 'SCRIPT_NAME' ] );
	
	
	/* ADDING THE MAIN PAGE ROUTE */
	PtcRouter::get( $base_path , function( )
	{
		print 'called the main page';
	} )->map( 'index' ); // map route to be retrieved later
	
	/* RETRIEVE THE MAPPED ROUTE */
	//echo PtcRouter::getRoute( 'index' );
	
	
	/* ADDING A ROUTE FOR A POST REQUEST */
	PtcRouter::post( $base_path , function( )
	{
		print 'called the main page by post request';
	} );
	
		
	/* ADDING A ROUTE FOR ANY REQUEST */
	PtcRouter::any( $base_path . '/any-request' , function( )
	{
		print 'called the main page';
	} );
	
	
	/* ADDING PARAMETERS TO ROUTES */
	PtcRouter::get( $base_path . '/user/{id}' , function( $id )
	{
		print "testing a parameter " . $id;
	} );
	
	
	/* ADDING PARAMETERS THAT MATCH CERTAIN PATTERNS */
	PtcRouter::get( $base_path . '/param-test/{lang}' , function( $lang )
	{
		print "testing a parameter against a pattern " . $lang;
	} )->where( 'lang' , 'es|en' );
	
	
	/* ADDING OPTIONAL PARAMETERS */
	PtcRouter::get( $base_path . '/post/{id?}' , function( $id = null )
	{
		print "testing optional parameter " . @$id;
	} );
	
		
	/* ADDING BEFORE AND AFTER FILTERS TO ROUTES */
	PtcRouter::get( $base_path . '/filter-test' , function( )
	{
		print "excuting route after filter ";
	} )->before( 'some.filter' )
	->after( 'some.filter' );
	
	
	/* GROUPING ROUTES WITH A PREFIX */
	PtcRouter::group( 'someName' , function( )
	{
		PtcRouter::get( '/' , function( )
		{
			print "testing main admin section";
		} );
		
		PtcRouter::get( 'somepage' , function( )
		{
			print "testing some admin section";
		} );
		
		/* NESTED GROUP, ALL ROUTES INSIDE ONLY WORK IF PROTOCOL IS HTTPS */
		PtcRouter::group( 'someOtherName' , function( )
		{
			PtcRouter::get( 'secure' , function( )
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
	PtcRouter::controller( $base_path . '/controller' ,  'UserController' );
	
	
	/* 404 NOT FOUND PAGE */
	PtcRouter::notFound( 404 , function( ) // not found urls
	{
		print "the 404 callback was executed";
	} );
	
	
	/* EXECUTING THE ROUTES */
	PtcRouter::run( true );  // argument $checkErrors should be set to false in production
	
