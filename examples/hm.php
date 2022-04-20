<?php

	/* 
	* EXAMPLE FILE FOR THE HANDYMAN CLASS
	*/
	
	use phptoolcase\HandyMan;
	
	require dirname(__FILE__) . '/../vendor/autoload.php';

	
	/* ADDING APPLICATION PATHS FOR LATER USAGE */
	HandyMan::addAppPath( array
	( 
		'lib' => dirname( __FILE__ ) . '/autoloader' 	// creating an entry in the application paths array
	) );


	/* ADDING CLASS FILES */
	HandyMan::addFiles( array
	(
		'HmTestClassFile' => HandyMan::getAppPath( 'lib' ) . '/class-file.php' , // adding a file to the autoloader
		'ns\HmTestClassFile' => HandyMan::getAppPath( 'lib' ) . '/ns-class-file.php' , // adding a namespaced file
	) );
	
	
	/* REGISTER THE AUTOLOADER */
	HandyMan::register( );	// we need to register the autoloader before we start calling classes
	
	
	/* LOAD CLASSES */	
	print '<b>AUTOLOADING CLASSES ADDED WITH "addFiles( )" METHOD:</b><br><br>';
	$class1 = new HmTestClassFile( );			// loading a preset class file
	$lowercase = new ns\HmTestClassFile( );	// loading a namespaced class


	/* ADDING DIRECTORIES WITH CLASSES TO THE AUTOLOADER */
	HandyMan::addDir( HandyMan::getAppPath( 'lib' ) );	// adding the previously created path
	
	
	/* LOAD CLASSES IN DIRECTORY */
	print '<br><b>AUTOLOADING CLASSES ADDED WITH "addDirs( )" METHOD:</b><br><br>';
	$class1 = new HmTestClass( );		// loading a class from the directory
	$lowercase = new HmTestClassLs( );	// loading a class with the filename lowercase
	
	
	/* ADDING A NAMESPACE DIRECTORY WITH CLASSES TO THE AUTOLOADER */
	HandyMan::addDir( array
	( 
		'nsTest' => HandyMan::getAppPath( 'lib' ) . '/namespaceTest'    // adding a namespaced directory
	));
	
	
	/* LOAD NAMESPACED CLASSES IN DIRECTORY */
	print '<br><b>AUTOLOADING NAMESPACED CLASSES ADDED WITH "addDirs( )" METHOD:</b><br><br>';
	$class_ns = new nsTest\HmTestNs( );			// loading a namespaced class
	$class_ns_deep = new nsTest\hmNsDeep\HmTestNsDeep( );	// loading a namespaced class inside a subfolder

	
	/* LOAD CLASSES BASED ON SEPARATORS AND NAMING CONVENTIONS (LOADS SLOWER) */
	print '<br><b>AUTOLOADING CLASSES BASED ON SEPARATORS AND NAMING CONVENTIONS:</b><br><br>';
	HandyMan::addDir( HandyMan::getAppPath( 'lib' ) . '/test-separators' ); // adding the directory
	
	HandyMan::addSeparator( '-' );			// adding a separator for class names
	HandyMan::addConvention( '{CLASS}' );	// adding a naming convention ( {CLASS} , {SEP} )
	$sep_class=new Hm_Test_Sep( ); // laods by replacing the "_" with "-" added separator in  the class name
	
	HandyMan::addConvention( 'class.{CLASS}' );	// adding another naming convention
	$sep_class1 = new Hm_Test_Sep1( ); // laods by replacing the "_" with "-" added separator in  the class name

	
	/* CREATE AN ALIAS FOR A CLASS TO BE AUTOLOADED */
	print '<br><b>AUTOLOADING CLASS WITH AN ALIAS NAME:</b><br><br>';
	HandyMan::addAlias( array( 'aliasTest' => 'HmTestClass' ) );
	$alias = new aliasTest( );		// loading a class as alias
	
	
	/* RETRIEVE ALL ADDED ALIAS NAMES */
	print '<br><b>RETRIEVE ALL ADDED ALIAS NAMES:</b><br><br>';
	print '<pre>' . print_r( HandyMan::getAlias( ) , true ). '</pre>';

	
	/* GETTING THE DIRECTORIES OF THE AUTOLOADER */
	$dirs = HandyMan::getDirs( );		// getting all directories ( files , directories , ns )
	
	
	/* WORKING WITH ARRAYS */
	print '<br><br><b>WORKING WITH MULTIDIMENSIONAL ARRAYS:</b><br><br>';
	
	
	/* RETRIEVE VALUES FROM A MULTIDIMENSIONAL ARRAY */
	$array = array
	( 
		'depth1'	=>	array( 'first value' , 'second value' , 'third value' )
	);
	print 'Getting a value inside a multidimensional array: ';
	print HandyMan::arrayGet( $array , 'depth1.0' );
	
	
	/* SETTING VALUES IN A MULTIDIMENSIONAL ARRAY */
	print '<br><br>Setting a value inside a multidimensional array: ';
	HandyMan::arraySet( $array , 'depth1.3' , 'some new value' );
	HandyMan::arraySet( $array , 'depth1.4' , array( 'some new value' , 'some other value' ) ); // setting an array as value
	print HandyMan::arrayGet( $array , 'depth1.4.0' );
	HandyMan::arraySet( $array , 'depth1.3' , 'forced new value' , true ); // force to change a value that is already set	
	

	/* COUNT VALUES OF ELEMENT INSIDE MULTIDIMENSIONAL ARRAY */
	print '<br><br>Counting values of an element inside a multidimensional array: ';
	print HandyMan::arrayCount( $array , 'depth1.4' );
	
	
	/* REMOVE ELEMENTS FROM MULTIDIMENSIONAL ARRAY */
	HandyMan::arrayDel( $array , 'depth1.2' );	// returns true if successful
	
	
	/* WORKING WITH SESSIONS */
	print '<br><br><br><b>WORKING WITH SESSION VARIABLES:</b><br><br>';
	
	
	/* STARTING A SESSION WITH THE SESSION MANAGER */
	HandyMan::session( 'start' );
	
	
	/* SET AND RETRIEVE SESSION VALUES */
	HandyMan::sessionSet( 'val' , 'some value' );
	HandyMan::sessionSet( 'key' , array( 'some stuff' ) );
	HandyMan::sessionSet( 'key.1' , 'some other value' );
	print 'retrieve session values: ';
	print HandyMan::sessionGet( 'key.1' );
	HandyMan::sessionSet( 'key.1' , 'some new value' , true ); // force to change a value that is already set	
	
	
	/* DELETING SESSION VALUES */
	HandyMan::sessionDel( 'key.0' ); // returns true if successful
	
	
	/* DESTROYING AND  CLOSING A SESSION WITH THE SESSION MANAGER */
	HandyMan::session( 'destroy' );
	HandyMan::session( 'close' );
	
	
	/* CONVERT  ARRAY TO JSON AND SEND HEADER RESPONSE */
	print '<br><br><br><b>CONVERTING ARRAYS TO JSON:</b><br><br>';
	print HandyMan::json( $array , null , false ); // do not send json response header otherwise this example page will not work
	//print '<br><br><br><b>CONVERTING ARRAYS TO JSONP:</b><br><br>';
	//print HandyMan::json( $array , 'jsonp_function' , false ); // do not send json response header otherwise this example page will not work
	
	
	
	print '<br><br><br><br>';