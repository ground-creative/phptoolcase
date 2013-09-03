<? 
	//session_start();				// start session for persistent debugging and code highlighter popup

	$_GET['debug']=true;       		// turn on the debug

	//$_GET['debug_off']=true;    		// turn off debug

	require_once('../PtcDebug.php');	// include the PtcDebug class

	$options=array				// add some options before class initialization
	(
		'url_key'		=>	'debug',
		'url_pass'		=>	'true',
		'die_on_error'	=>	false,
	);

	PtcDebug::load($options);		// initialize the class

	/* LOGGING A MESSAGE */
	log_msg('just a message');
	
	/* LOGGING A VARIABLE WITH A STATEMENT */
	$var='something';
	log_msg($var,'testing a variable');
	
	/* LOGGING AN ARRAY TO THE MESSAGE PANEL WITH A DIFFERENT CATEGORY */
	$array=array('key'=>'value','key1'=>'value1');
	log_msg($array,'testing an array','new category');

	/* THROWING A NOTICE */
	trigger_error('some notice',E_USER_NOTICE);

	/* THROWING A WARNING */
	trigger_error('some warning',E_USER_WARNING);

	/* THROWING AN ERROR */
	trigger_error('some error',E_USER_ERROR);	// continue execution with the options "die_on_error" set to false

	/* TESTING AN ERROR WITHIN A FUNCTION */
	function some_func(){ fopen(); }
	echo some_func();						// will throw an error
	
	/* LOGGING SQL QUERIES AND TIMING EXECUTION */
	$sql='select from where something';	// some sql query, will be used as reference
	log_sql('',$sql);			// leaving the first parameter empty, can be added later with the query result
	$sql_result=array('key'=>'value','key1'=>'value1');	// this should be the sql result of the sql query
	stop_timer($sql);					// time execution, the query is used as reference
	add_to_log($sql,$sql_result);			// attaching the result to the message based on the reference

	/* WATCHING A VARIABLE */
	declare(ticks=1)
	{
		$var='some test';
		watch_var('var');					// passing the variable without the "$" symbol
		$var='some new value';				// the variable changed
	}
	
	/* TIMING A LOOP */
	log_msg('','timing a loop');	// leaving the first parameter empty
	for($i=0;$i<100;$i++){ @$a[]=$i; }
	stop_timer('timing a loop');	// using the reference to attach the execution time to the buffer
	
	/* CATCHING AN EXCEPTION */
	throw new Exception('Uncaught Exception');
	
	//session_destroy();
?>