<?php

	use phptoolcase\Router;
	
	Router::filter( 'before.filter' , function( $route , $uri , $response = null )
	{
		$GLOBALS[ 'test_before_filter' ] = 'before filter executed';
	} );
	
	Router::filter( 'after.filter', function( $route , $uri , $response = null )
	{
		print "executed after filter";
	} );
	
	Router::filter( 'before.discard_route' , function( $route , $uri , $response = null )
	{
		/* if we want to stop all routes execution we can use the routed( ) method */
		//Router::routed( true ); 
		/* if return is used the current route will be discarded */
		return true; 
	} );	

	
	Router::when( 'some-url/*' , function( )
	{
		print "global pattern executed";
		/* if return is used all routes execution will be discarded */
		// return true; 
	} );