<?
	//session_start();				# start session for persistent debugging

	require_once('../PtcDebug.php');	# include the PtcDebug class
	
	$options=array
	(
		'url_key'		=>	'debug',
		'url_pass'		=>	'true',
		'die_on_error'	=>	false,
	);
	
	PtcDebug::setErrorHandler($options['die_on_error']);# set error handler to be the debug class
	
	PtcDebug::debugLoader($options);

	PtcDebug::bufferLog('debug self initialized ','this is the result msg');

	trigger_error("the rorrss",E_USER_NOTICE);
	
	trigger_error("the rorrss",E_USER_WARNING);
	
	trigger_error("the rorrss333333333333333",E_USER_ERROR);
	
	PtcDebug::bufferLog(32,'some statement');	
	PtcDebug::stopTimer();
	
	PtcDebug::bufferSql("select from where something",'some other statement');
	PtcDebug::stopTimer();
	
	PtcDebug::bufferLog(array("dasd"=>"dasdsa","daasd"=>"23333333"),'some other statement');
	
	test();
	
	function ddas()
	{
		fopen();
	}
	
	echo ddas();

	//session_destroy();
?>