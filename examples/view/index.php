<?php

	/* 
	* EXAMPLE FILE FOR VIEW CLASS 
	*/
	
	use phptoolcase\View;
	
	require dirname(__FILE__) . '/../../vendor/autoload.php';
	
	
	/* ADDING A BASE PATH FOR ALL VIEWS */
	View::path( dirname( __FILE__ ) );
	
	
	/* RENDERING A VIEW WITH DATA */
	View::make( 'test.view' , array( 'test' => 'some data' , 'child' => 'some more data' ) )
		->render( );
	
	
	/* NESTING VIEWS INSIDE EACH OTHER */
	/*View::make( 'test.view' , array( 'test' => 'some data' ) )
		->nest( 'child' , 'nested.view' , array( 'some' => 'nested view data' ) )
		->render( );*/

	
	/* COMPILE VIEW ONLY, DON'T RENDER */	
	$compiled = View::make( 'test.view' , array( 'test' => 'some data' , 'child' => 'some more data' ) )
				->compile( );
	//print $compiled; // DO SOMETHING WITH THE HTML STRING BEFORE RENDERING
	
	
	/* ADD PARAMETERS SEPARATELY */
	$view = View::make( 'test.view' )
			->set( 'test' , 'some data' )
			->set( 'child' , 'some more data');
	//$view->render( ); // RENDER THE VIEW