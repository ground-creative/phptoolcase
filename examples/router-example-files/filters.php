<?php

	/* 
	* EXAMPLE FILE FOR PTCROUTER CLASS WITH GLOBAL PATTERNS AND ROUTE FILTERS
	*/
	
	
	/* ADDING A FILTER THAT CAN BE USED WITH ROUTES */
	PtcRouter::filter( 'some.filter', function( $route , $uri , $response = null )
	{
		print "executing filter";
		/* if we want to stop all routes execution we can use the routed( ) method */
		//PtcRouter::routed( true ); 
		/* if return is used the current route will be discarded */
		// return true; 
	} );
	
	/* ADDING A GLOBAL PATTERN */
	PtcRouter::when( 'some-url/*' , function( )
	{
		print "global pattern executed";
		/* if return is used all routes execution will be discarded */
		// return true; 
	} );