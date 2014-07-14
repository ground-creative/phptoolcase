<?php

	/* 
	* EXAMPLE FILE FOR THE PTCHANDYMAN CLASS
	*/
	
	require_once( '../PtcHm.php' );					// require the component

	
	/* ADDING APPLICATION PATHS FOR LATER USAGE */
	PtcHandyMan::addAppPath( array
	( 
		'lib' => dirname( __FILE__ ) . '/autoloader-example-files' 	// creating an entry in the application paths array
	) );


	/* ADDING CLASS FILES */
	PtcHandyMan::addFiles( array
	(
		'HmTestClassFile' => PtcHandyMan::getAppPath( 'lib' ) . '/class-file.php' , // adding a file to the autoloader
		'ns\HmTestClassFile' => PtcHandyMan::getAppPath( 'lib' ) . '/ns-class-file.php' , // adding a namespaced file
	) );
	
	
	/* REGISTER THE AUTOLOADER */
	PtcHandyMan::register( );	// we need to register the autoloader before we start calling classes
	
	
	/* LOAD CLASSES */	
	print '<b>AUTOLOADING CLASSES ADDED WITH "addFiles( )" METHOD:</b><br><br>';
	$class1 = new HmTestClassFile( );			// loading a preset class file
	$lowercase = new ns\HmTestClassFile( );	// loading a namespaced class


	/* ADDING DIRECTORIES WITH CLASSES TO THE AUTOLOADER */
	PtcHandyMan::addDir( PtcHandyMan::getAppPath( 'lib' ) );	// adding the previously created path
	
	
	/* LOAD CLASSES IN DIRECTORY */
	print '<br><b>AUTOLOADING CLASSES ADDED WITH "addDirs( )" METHOD:</b><br><br>';
	$class1 = new HmTestClass( );		// loading a class from the directory
	$lowercase = new HmTestClassLs( );	// loading a class with the filename lowercase
	
	
	/* ADDING A NAMESPACE DIRECTORY WITH CLASSES TO THE AUTOLOADER */
	PtcHandyMan::addDir( array
	( 
		'nsTest' => PtcHandyMan::getAppPath( 'lib' ) . '/namespaceTest'    // adding a namespaced directory
	));
	
	
	/* LOAD NAMESPACED CLASSES IN DIRECTORY */
	print '<br><b>AUTOLOADING NAMESPACED CLASSES ADDED WITH "addDirs( )" METHOD:</b><br><br>';
	$class_ns = new nsTest\HmTestNs( );			// loading a namespaced class
	$class_ns_deep = new nsTest\hmNsDeep\HmTestNsDeep( );	// loading a namespaced class inside a subfolder

	
	/* LOAD CLASSES BASED ON SEPARATORS AND NAMING CONVENTIONS (LOADS SLOWER) */
	print '<br><b>AUTOLOADING CLASSES BASED ON SEPARATORS AND NAMING CONVENTIONS:</b><br><br>';
	PtcHandyMan::addDir( PtcHandyMan::getAppPath( 'lib' ) . '/test-separators' ); // adding the directory
	
	PtcHandyMan::addSeparator( '-' );			// adding a separator for class names
	PtcHandyMan::addConvention( '{CLASS}' );	// adding a naming convention ( {CLASS} , {SEP} )
	$sep_class=new Hm_Test_Sep( ); // laods by replacing the "_" with "-" added separator in  the class name
	
	PtcHandyMan::addConvention( 'class.{CLASS}' );	// adding another naming convention
	$sep_class1 = new Hm_Test_Sep1( ); // laods by replacing the "_" with "-" added separator in  the class name

	
	/* CREATE AN ALIAS FOR A CLASS TO BE AUTOLOADED */
	print '<br><b>AUTOLOADING CLASS WITH AN ALIAS NAME:</b><br><br>';
	PtcHandyMan::addAlias( array( 'aliasTest' => 'HmTestClass' ) );
	$alias = new aliasTest( );		// loading a class as alias
	
	
	/* RETRIEVE ALL ADDED ALIAS NAMES */
	print '<br><b>RETRIEVE ALL ADDED ALIAS NAMES:</b><br><br>';
	print '<pre>' . print_r( PtcHandyMan::getAlias( ) , true ). '</pre>';

	
	/* GETTING THE DIRECTORIES OF THE AUTOLOADER */
	$dirs = PtcHandyMan::getDirs( );		// getting all directories ( files , directories , ns )
	
	
	/* WORKING WITH ARRAYS */
	print '<br><br><b>WORKING WITH MULTIDIMENSIONAL ARRAYS:</b><br><br>';
	
	
	/* RETRIEVE VALUES FROM A MULTIDIMENSIONAL ARRAY */
	$array = array
	( 
		'depth1'	=>	array( 'first value' , 'second value' , 'third value' )
	);
	print 'Getting a value inside a multidimensional array: ';
	print PtcHandyMan::arrayGet( $array , 'depth1.0' );
	
	
	/* SETTING VALUES IN A MULTIDIMENSIONAL ARRAY */
	print '<br><br>Setting a value inside a multidimensional array: ';
	PtcHandyMan::arraySet( $array , 'depth1.3' , 'some new value' );
	PtcHandyMan::arraySet( $array , 'depth1.4' , array( 'some new value' , 'some other value' ) ); // setting an array as value
	print PtcHandyMan::arrayGet( $array , 'depth1.4.0' );
	PtcHandyMan::arraySet( $array , 'depth1.3' , 'forced new value' , true ); // force to change a value that is already set	
	

	/* COUNT VALUES OF ELEMENT INSIDE MULTIDIMENSIONAL ARRAY */
	print '<br><br>Counting values of an element inside a multidimensional array: ';
	print PtcHandyMan::arrayCount( $array , 'depth1.4' );
	
	
	/* REMOVE ELEMENTS FROM MULTIDIMENSIONAL ARRAY */
	PtcHandyMan::arrayDel( $array , 'depth1.2' );	// returns true if successful
	
	
	/* WORKING WITH SESSIONS */
	print '<br><br><br><b>WORKING WITH SESSION VARIABLES:</b><br><br>';
	
	
	/* STARTING A SESSION WITH THE SESSION MANAGER */
	PtcHandyMan::session( 'start' );
	
	
	/* SET AND RETRIEVE SESSION VALUES */
	PtcHandyMan::sessionSet( 'val' , 'some value' );
	PtcHandyMan::sessionSet( 'key' , array( 'some stuff' ) );
	PtcHandyMan::sessionSet( 'key.1' , 'some other value' );
	print 'retrieve session values: ';
	print PtcHandyMan::sessionGet( 'key.1' );
	PtcHandyMan::sessionSet( 'key.1' , 'some new value' , true ); // force to change a value that is already set	
	
	
	/* DELETING SESSION VALUES */
	PtcHandyMan::sessionDel( 'key.0' ); // returns true if successful
	
	
	/* DESTROYING AND  CLOSING A SESSION WITH THE SESSION MANAGER */
	PtcHandyMan::session( 'destroy' );
	PtcHandyMan::session( 'close' );
	
	
	/* CONVERT  ARRAY TO JSON AND SEND HEADER RESPONSE */
	print '<br><br><br><b>CONVERTING ARRAYS TO JSON:</b><br><br>';
	print PtcHandyMan::json( $array , null , false ); // do not send json response header otherwise this example page will not work
	print '<br><br><br><b>CONVERTING ARRAYS TO JSONP:</b><br><br>';
	print PtcHandyMan::json( $array , 'jsonp_function' , false ); // do not send json response header otherwise this example page will not work
	
	
	
	print '<br><br><br><br>';
	
	