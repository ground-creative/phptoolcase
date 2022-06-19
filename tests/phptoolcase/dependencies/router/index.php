<?php	

	use phptoolcase\Router;

	require dirname(__FILE__) . '/../../../../vendor/autoload.php';

	$base_path = dirname( $_SERVER[ 'SCRIPT_NAME' ] );

	Router::get( $base_path , function( )
	{
		print 'called the main page by get request';
	} )->map( 'index' ); // map route to be retrieved later
	
	Router::post( $base_path , function( )
	{
		print 'called the main page by post request';
	} );
	
	Router::put( $base_path , function( )
	{
		print 'called the main page by put request';
	} );
	
	Router::delete( $base_path , function( )
	{
		print 'called the main page by delete request';
	} );
	
	Router::any( $base_path . '/any-request/' , function( )
	{
		print 'called any request uri';
	} );

	Router::get( $base_path . '/user/test-param/{id}/' , function( $id )
	{
		print "testing a parameter " . $id;
	} );

	Router::get( $base_path . '/user/test-param/{name}/{id}/' , function( $name , $id )
	{
		print "testing a parameter " . $name . "-" . $id;
	} );	
	
	Router::get( $base_path . '/user/test-param/{name}/{id}/{date}/' , function( $name , $id , $date )
	{
		print "testing a parameter " . $name . "-" . $id . "/" . $date;
	} );

	Router::get( $base_path . '/user/test-param/{name}/{id}/{date}/{time}/' , function( $name , $id , $date , $time )
	{
		print "testing a parameter " . $name . "-" . $id . "/" . $date . "/" . $time;
	} );	
	
	Router::get( $base_path . '/param-test/{lang}/' , function( $lang )
	{
		print "testing a parameter against a pattern " . $lang;
	} )->where( 'lang' , 'es|en' );	

	Router::get( $base_path . '/param-test/{sid}/{lang}/' , function( $sid , $lang )
	{
		print "testing a parameter against a pattern " . $sid . "-" . $lang;
	} )->where( 'sid' , '\d+' )
		->where( 'lang' , 'es|en' );	
	
	Router::get( $base_path . '/lang-test/{lang?}/' , function( $lang )
	{
		print "testing a parameter against a pattern " . $lang;
	} )->where( 'lang' , 'es|en' );
	
	
	
	
	Router::get( $base_path . '/user/area/{user_name}/{id?}/' , function( $userName , $id = null )
	{
		print "testing optional parameter " . $userName . "-" . @$id;
	} );
	
	Router::get( $base_path . '/user/private/{user_name?}/{id?}' , function( $userName = null , $id = null )
	{
		print "testing optional parameter " . @$userName . "-" . @$id;
	} );
	
	Router::get( $base_path . '/user/account/{folder}/{user_name?}/{id?}/' , function( $folder , $userName = null , $id = null )
	{
		print "testing optional parameter " . $folder . "-" . @$userName . "-" . @$id;
	} );
	
	Router::get( $base_path . '/user/member/{folder?}/{user_name?}/{id?}/' , function( $folder = null , $userName = null , $id = null )
	{
		print "testing optional parameter " . @$folder . "-" . @$userName . "-" . @$id;
	} );
	
	Router::get( $base_path . '/user/{id?}/' , function( $id = null )
	{
		print "testing optional parameter " . @$id;
	} );	

	Router::notFound( 404 , function( ) // not found urls
	{
		print "the 404 callback was executed";
	} );

	/* EXECUTING THE ROUTES */
	Router::run( true );  // argument $checkErrors should be set to false in production