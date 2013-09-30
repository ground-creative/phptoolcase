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
		'ns\HmTestClassFile'=> PtcHandyMan::getAppPath( 'lib' ) . '/ns-class-file.php' , // adding a namespaced file
	) );
	
	
	/* REGISTER THE AUTOLOADER */
	PtcHandyMan::register( );	// we need to register the autoloader before we start calling classes
	
	
	/* LOAD CLASSES */	
	echo '<b>AUTOLOADING CLASSES ADDED WITH "addFiles( )" METHOD:</b><br><br>';
	$class1 = new HmTestClassFile( );			// loading a preset class file
	$lowercase = new ns\HmTestClassFile( );	// loading a namespaced class


	/* ADDING DIRECTORIES WITH CLASSES TO THE AUTOLOADER */
	PtcHandyMan::addDir( PtcHandyMan::getAppPath( 'lib' ) );	// adding the previously created path
	
	
	/* LOAD CLASSES IN DIRECTORY */
	echo '<br><b>AUTOLOADING CLASSES ADDED WITH "addDirs( )" METHOD:</b><br><br>';
	$class1 = new HmTestClass( );		// loading a class from the directory
	$lowercase = new HmTestClassLs( );	// loading a class with the filename lowercase
	
	
	/* ADDING A NAMESPACED DIRECTORY WITH CLASSES TO THE AUTOLOADER */
	PtcHandyMan::addDir( array
	( 
		'nsTest' => PtcHandyMan::getAppPath( 'lib' ) . '/namespaceTest'    // adding a namespaced directory
	));
	
	
	/* LOAD NAMESPACED CLASSES IN DIRECTORY */
	echo '<br><b>AUTOLOADING NAMESPACED CLASSES ADDED WITH "addDirs( )" METHOD:</b><br><br>';
	$class_ns = new nsTest\HmTestNs( );			// loading a namespaced class
	$class_ns_deep = new nsTest\hmNsDeep\HmTestNsDeep( );	// loading a namespaced class inside a subfolder

	
	/* LOAD CLASSES BASED ON SEPARATORS AND NAMING CONVENTIONS (LOADS SLOWER) */
	echo '<br><b>AUTOLOADING CLASSES BASED ON SEPARATORS AND NAMING CONVENTIONS:</b><br><br>';
	PtcHandyMan::addDir( PtcHandyMan::getAppPath( 'lib' ) . '/test-separators' ); // adding the directory
	
	PtcHandyMan::addSeparator( '-' );			// adding a separator for class names
	PtcHandyMan::addConvention( '{CLASS}' );	// adding a naming convention ( {CLASS} , {SEP} )
	$sep_class=new Hm_Test_Sep( ); // laods by replacing the "_" with "-" added separator in  the class name
	
	PtcHandyMan::addConvention( 'class.{CLASS}' );	// adding another naming convention
	$sep_class1 = new Hm_Test_Sep1( ); // laods by replacing the "_" with "-" added separator in  the class name
		
	
	/* GETTING THE DIRECTORIES OF THE AUTOLOADER */
	$dirs = PtcHandyMan::getDirs( );		// getting all directories ( files , directories , ns )
	
	