<?php

	/* 
	* EXAMPLE FILE FOR PTCVIEW CLASS 
	*/
	
	require_once( '../../PtcView.php' );
	
	
	/* ADDING A BASE PATH FOR ALL VIEWS */
	PtcView::path( dirname( __FILE__ ) );
	
	
	/* RENDERING A VIEW WITH DATA */
	PtcView::make( 'test.view' , array( 'test' => 'some data' , 'child' => 'some more data' ) )
		->render( );
	
	
	/* NESTING VIEWS INSIDE EACH OTHER */
	/*PtcView::make( 'test.view' , array( 'test' => 'some data' ) )
		->nest( 'child' , 'nested.view' , array( 'some' => 'nested view data' ) )
		->render( );*/

	
	/* COMPILE VIEW ONLY, DON'T RENDER */	
	$compiled = PtcView::make( 'test.view' , array( 'test' => 'some data' , 'child' => 'some more data' ) )
				->compile( );
	//print $compiled; // DO SOMETHING WITH THE HTML STRING BEFORE RENDERING
	
	
	/* ADD PARAMETERS SEPARATELY */
	$view = PtcView::make( 'test.view' )
			->set( 'test' , 'some data' )
			->set( 'child' , 'some more data');
	//$view->render( ); // RENDER THE VIEW