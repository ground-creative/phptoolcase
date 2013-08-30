<?php
	/**
	* DEBUGGER & LOGGER CLASS
	* <br>All class properties and methods are static because it's required 
	* to let them work on script shutdown when FATAL error occurs.
	* PHP version 5
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.8.3
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	class PtcDebug
	{
		/**
		* Checks if  the debug "url_key" and "url_pass" are set on the referer url
		* @return	returns true if "url_key" and "url_pass" are in the referer url, otherwise false
		*/
		public static function checkReferer()
		{
			if(@array_key_exists('HTTP_REFERER',@$_SERVER))
			{ 
				$query=parse_url($_SERVER['HTTP_REFERER'],PHP_URL_QUERY);
				$params=array();
				parse_str($query,$params);
				if(@$params[self::$_options['url_key']]==self::$_options['url_pass'])
				{
					$_GET[self::$_options['url_key']]=$params[self::$_options['url_key']];
					return true;
				}
			}
			return false;
		}													
		/**
		* Sets the error handler to be the debug class. good for production with "$dieOnFatal" set to false
		* @param	string	$dieOnFatal		die if fatal error occurs
		* @tutorial	PtcDebug.cls#setErrorHandler
		*/
		public static function setErrorHandler($dieOnFatal=true)
		{
			ini_set('display_errors',false);
			ini_set('html_errors', false);	
			if(!@self::$_options['error_reporting'])
			{
				@self::$_options['error_reporting']=self::$_defaultOptions['error_reporting'];
			}			
			ini_set('error_reporting',self::$_options['error_reporting']);
			@self::$_options['die_on_error']=$dieOnFatal;
			set_error_handler("PtcDebug::errorHandler"); 
		}
		/**
		* Loads the debug interface and/or the console class if requested
		* @param 	array 	$options		array of options, see {@link _defaultOptions}
		* @tutorial	PtcDebug.cls#debugLoader
		*/
		public static function load($options=null)
		{
			$now=microtime(true);
			if(self::$_isLoaded)	// check if the debug class is already loaded
			{
				$err=array('errno'=>self::_msgType(E_USER_NOTICE),
							'errstr'=>'Debug already loaded!','errfile'=>'trace');
				self::_buildBuffer('log','{errorHandler}',$err);
				return; 
			}
			if(@isset(self::$_options['die_on_error']))
			{ 
				self::$_defaultOptions['die_on_error']=self::$_options['die_on_error']; 
			}
			if(@isset(self::$_options['error_reporting']))
			{ 
				self::$_defaultOptions['error_reporting']=self::$_options['error_reporting']; 
			}
			self::$_options=(is_array($options)) ? 
				array_merge(self::$_defaultOptions,$options) : self::$_defaultOptions;
			if(!$has_access=self::_checkAccess()){ return; }// check access with ips
			$buffer='Debug Info:';
			if(self::$_options['check_referer']){ self::checkReferer(); }// check if referer has debug vars
			if(self::$_options['session_start'])// start session on request
			{
				if(session_id()==="")// check if session is already active
				{ 
					session_start(); 
					$buffer.='<br>Initialized browser session with session_start()';
				}
				else{ $buffer.='<br>Session id is '.session_id(); }
			}
			if(!@$_SESSION){ $_SESSION=array(); }
			if(@$_GET[self::$_options['url_key']]==self::$_options['url_pass'])
			{
				$_SESSION[self::$_options['url_key']]=true;
				$_SESSION['code_highlighter']=true;
				//$buffer.='<br>PtcDebug turned on!';
			}
			else if(@$_GET[self::$_options['url_key'].'_off']==self::$_options['url_pass'])
			{
				$_SESSION[self::$_options['url_key']]=false; 
				$_SESSION['code_highlighter']=false;
			}
			if(@$_SESSION[self::$_options['url_key']])
			{ 
				self::$_startTime=microtime(true);
				$console_debug['errors']=false;
				$console_debug['exceptions']=false;
				if(self::$_options['set_time_limit']){ set_time_limit(self::$_options['set_time_limit']); }
				if(self::$_options['memory_limit']){ ini_set('memory_limit',self::$_options['memory_limit']); }
				if(self::$_options['show_interface'] || self::$_options['debug_console'])
				{
					register_shutdown_function('PtcDebug::processBuffer'); 
				}
				if(self::$_options['replace_error_handler'])	// replace error handler
				{
					$console_debug['errors']=true;
					$console_debug['exceptions']=true;
					self::setErrorHandler(self::$_options['die_on_error']);
					$buffer.='<br>Error handler has been overridden!';
				}
				if(self::$_options['catch_exceptions'])	// set exception handler
				{
					set_exception_handler('PtcDebug::exceptionHandler');
					$buffer.="<br>Exception Handler turned on!";
				}
				if(self::$_options['debug_console'])	// try to laod the console class
				{
					$buffer.='<br>Console debug turned on';
					if(file_exists(dirname(__FILE__)."/PhpConsole/PhpConsole.php"))
					{
						require_once(dirname(__FILE__)."/PhpConsole/PhpConsole.php");
						self::$_consoleStarted=true;
					}
					if(self::$_consoleStarted || class_exists('PhpConsole',true))
					{ 
						PhpConsole::start($console_debug['errors'],$console_debug['exceptions'],
																		dirname(__FILE__));
						$buffer.=", phpConsole class started!";
						self::$_consoleStarted=true;
					}
					else
					{ 
						self::$_consoleStarted=false;
						$buffer.=', but could not find phpConsole class!';
					}
				}
				if(self::$_options['enable_inspector'])
				{ 
					register_tick_function('PtcDebug::watchCallback'); 
					if(self::$_options['declare_ticks']){ declare(ticks=1); }
					$buffer.="<br>Variables inspector enabled!";
				}
				if(!isset($_SESSION['debug_show_messages'])){ self::_setSessionVars(); }
				if(@$_GET['hidepanels']){ self::_disablePanels(); }
				else
				{
					self::$_options['show_messages']=$_SESSION['debug_show_messages']; 
					self::$_options['show_globals']=$_SESSION['debug_show_globals']; 
					self::$_options['show_sql']=$_SESSION['debug_show_sql']; 
				}
				log_msg('','<span>'.$buffer.'<span>');
			}	
			self::$_isLoaded=true;
			self::$_tickTime=((microtime(true)-$now)+self::$_tickTime);
		}
		/**
		* Watches a variable that is in a declare(ticks=n); code block, for changes 
		* @param 	string 	$variableName		the name of the variable to watch
		* @see	watch_var()
		* @tutorial	PtcDebug.cls#watchVar
		*/
		public static function watch($variableName)
		{
			if(self::$_options['enable_inspector'])
			{
				$var=self::_findWatchVar($variableName);
				self::$_watchedVars[$variableName]=$var;
				$value=self::$_watchedVars[$variableName];		
				log_msg($value,'Watching variable <span style="font-weight:bold;">$'.
											$variableName.'</span> = ','Inspector');
			}
			else
			{ 
				$err=array('errno'=>self::_msgType(E_USER_NOTICE),'errfile'=>'trace',
					'errstr'=>'Please set to true [\'enable_inspector\'] option to be able to watch a variable');
				self::_buildBuffer('log','{errorHandler}',$err);
			}
		}
		/**
		* Callback function that checks if a given variable has changed
		*/
		public static function watchCallback()
		{
			//$now=microtime(true);
			if(count(self::$_watchedVars)) 
			{
				foreach(self::$_watchedVars as $variableName=>$variableValue) 
				{
					$var=self::_findWatchVar($variableName);
					if(@$var!==@$variableValue) 
					{
						$info=array
						(
							'variable'			=>	'$'.$variableName,
							'previous_value'	=>	self::$_watchedVars[$variableName],
							'new_value'		=>	$var
						);			
						self::$_watchedVars[$variableName]=$var;
						log_msg($info,'Watched variable changed  <span style="font-weight:bold;">$'.
														$variableName.'</span> = ','Inspector');
					}
				}	
			}
			//self::$_tickTime=((microtime(true)-$now)+self::$_tickTime); // FIXME: the timer goes to minus
		}
		/**
		* Writes data to the messages panel
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement if required
		* @param	string	$category	a category for the messages panel
		* @see	log_msg()
		* @tutorial	PtcDebug.cls#logging.log_msg
		*/
		public static function bufferLog($string,$statement=null,$category=null)
		{ 
			self::_buildBuffer('log',$string,$statement,$category);
		}
		/**
		* Writes data to the sql panel
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement if required
		* @param	string	$category	a category for the sql panel
		* @see	log_sql()
		* @tutorial	PtcDebug.cls#logging.log_sql
		*/
		public static function bufferSql($string,$statement=null,$category=null)
		{ 
			self::_buildBuffer('sql',$string,$statement,$category); 
		}
		/**
		* Monitors the execution of php code, or sql queries based on a reference 
		* @param	string			$reference	a reference to look for ("$statement")
		* @param 	string|numeric 	$precision	sec/ms
		* @see	stop_timer()
		* @tutorial	PtcDebug.cls#stopTimer
		* @return	resturns true if a given reference is found, otherwise false
		*/
		public static function stopTimer($reference=null,$precision=1)
		{
			$now=microtime(true);
			$last=self::_findReference($reference,1);
			if(!$last){ return false; }
			$time=($now-@$last['data']['start_time']);
			switch($precision)
			{
				case 0:		// seconds
				case 'sec':	// seconds
					self::$_buffer[$last['key']]['time']=round($time,3).' sec';
				break;
				case 1:		// millisecons
				case 'ms':		// millisecons
				default:
					self::$_buffer[$last['key']]['time']=round($time*1000,3).' ms';
				break;
			}
			return true;
		}
		/**
		* Handles php errors
		* @param 	string 	$errno	error number (php standards)
		* @param 	string 	$errstr	error string
		* @param 	string 	$errfile	error file
		* @param 	string 	$errline	error line
		* @see		setErrorHandler()
		@return	returns true to prevent php default error handler to fire
		*/
		public static function errorHandler($errno,$errstr,$errfile,$errline) 
		{
			if(error_reporting()==0){ return; }	// if error has been supressed with an @
			$err=array('errno'=>self::_msgType($errno),'errstr'=>$errstr,
									'errfile'=>$errfile,'errline'=>$errline);
			self::_buildBuffer('log','{errorHandler}',$err);
			// stop if fatal error occurs
			if(self::$_options['die_on_error'] && self::_msgType($errno)=="Php Error"){ die(); }
			return true;	// don't execute php error handler
		}
		/**
		* Exception handler, catches exceptions that are not in a try/catch block
		* @param 	object 	$exception	the exception object
		*/
		public static function exceptionHandler($exception)
		{
			$err=array('errno'=>self::_msgType('exception'),'errstr'=>$exception->getMessage(),
								'errfile'=>$exception->getFile(),'errline'=>$exception->getLine());
			self::_buildBuffer('log','{errorHandler}',$err);
		}
		/**
		* Attaches a message to the end of the buffer array to add data based on a reference 
		* @param	string	$reference	a reference to look for ("$statement")
		* @param	mixed	$string		the message to show
		* @param	string	$statement	a new statement if required
		* @return	returns true if the given reference is found, false otherwise
		* @see	add_to_log()
		* @tutorial	PtcDebug.cls#addToLog
		*/
		public static function addToBuffer($reference,$string,$statement=null)
		{
			$raw_buffer=self::_findReference($reference,2);
			if(!$raw_buffer){ return false; }
			$last=$raw_buffer['data'];
			if(@$string)
			{	
				$last['var_type']=gettype($string);
				$last['errstr']=$string; 
			}
			if($statement){ $last['errmsg']=$statement; }
			if(self::$_options['debug_console'])
			{
				$last['console_string']=(!@$string) ? $last['errstr'] : $string;
				$last['console_statement']=(!@$statement) ? $last['errmsg'] : $statement;
			}
			@self::$_buffer[$raw_buffer['key']]=$last;
			return true;
		}
		/**
		* Processes the buffer to show the interface and/or the console messages
		* @see		load()
		*/
		public static function processBuffer()
		{
			self::$_countTime=false;
			self::$_endTime=microtime(true);
			if(self::$_consoleStarted){ self::_debugConsole(); }
			if(self::$_options['show_interface'])
			{
				self::_lastError();				// get last php fatal error
				$interface=self::_buildInterface();	// build the interface
				print $interface;
			}
		}
		/**
		* File highlighter that opens a popup window inspect source code
		* @param 	string 	$file		the full path for the file
		* @param 	string 	$line	the line to be highlighted
		* @tutorial	PtcDebug.cls#fileInspector
		* @return	returns the html output of highlight_file()
		*/
		public static function highlightFile($file,$line=null)
		{
			$lines=implode(range(1,count(file($file))),'<br />'); 
			$content=highlight_file($file,true); 
			if($line)
			{
				$line=$line-1;
				$l=explode('<br />',$content);
				$l[$line]='<div id="line" style="display:inline;background-color:yellow;">'.
															$l[$line].'</div>';
				$content=implode('<br />',$l);
			}
			$html=' 
			    <style type="text/css"> 
				.num{float:left;color:gray;font-size:13px;
				font-family:monospace;text-align:right;margin-right:6pt;
				padding-right:6pt;border-right:1px solid gray;} 
				body{margin:0px;margin-left:5px;}
				td{vertical-align:top;}code{white-space:nowrap;} 
			    </style>
			    <script>
				window.onload=function() 
				{
					var SupportDiv = document.getElementById("line");
					window.scroll(0,findPos(SupportDiv));
					return false;
				};	
				function findPos(obj)
				{
					var curtop=0;
					if(obj.offsetParent)
					{
						do{
							curtop+=(obj.offsetTop-40);
						} while (obj = obj.offsetParent);
						return [curtop];
					}
				};
			</script>';
			$html.="<table><tr><td class=\"num\">\n$lines\n</td>
									<td>\n$content\n</td></tr></table>"; 
			return $html;
		}
		/**
		* Default options for the debug class
		* @var	array
		* @tutorial	PtcDebug.cls#_defaultOptions
		*/
		protected static $_defaultOptions=array
		(
			'url_key'				=>	'debug',// the key to pass to the url to turn on debug
			'url_pass'				=>	'true',// the pass to turn on debug
			'replace_error_handler'	=>	true,// replace default php error handler
			'error_reporting'		=>   E_ALL,// error reporting flag
			'catch_exceptions'		=>	true,// sets exception handler to be this class method
			'check_referer'			=>   false,// check referer for key and pass(good for ajax debugging)
			'die_on_error'			=>	true,// die if fatal error occurs(with this class error handler)
			'debug_console'		=>	false,// only for Chrome,show messages in console(phpConsole needed)
			'allowed_ips'			=>	null,// restrict access with ip's
			'session_start'			=>	false,// start session for persistent debugging
			'show_interface'		=>	true,// show the interface(false to debug in console only)
			'enable_inspector'		=>	true,// enable variables inspector, use declare(ticks=n); in code block
			'declare_ticks'			=>   false,// declare ticks glabally with value of 1
			'set_time_limit'			=>	null,// set php execution time limit
			'memory_limit'			=>	null,// set php memory size	
			'show_messages'		=>	true,// show messages panel
			'show_globals'			=>	true,// show global variables in vars panel
			'show_sql'			=>	true,// show sql panel
			'trace_depth'			=>	10,// maximum depth for the backtrace
			'max_dump_depth'		=>	6,// maximum depth for the dump function		
			'default_category'		=>	'General'
		);
		/**
		* Array of methods excluded from the backtrace
		* @var	array
		*/
		protected static $_excludeMethods=array('bufferLog','bufferSql');
		/**
		* Sends the buffer to the PhpConsole class
		*/
		protected static function _debugConsole()
		{
			if(function_exists('debug'))
			{
				foreach(self::$_buffer as $k=>$arr)
				{
					if(@$arr['console_string']!='{errorHandler}' && 
						(@$arr['console_string'] || @$arr['console_statement']))
					{
						$console_string=@$arr['console_string'];
						if(!@$arr)
						{
							$php_trace=self::_debugTrace(1);
							$arr=array('errline'=>$php_trace['line'],'errfile'=>$php_trace['file']); 
						}
						$console_string=(@is_array($console_string) || @is_object($console_string)) ? 
													@print_r($console_string,true) : $console_string;
						$statement=(@$arr['console_statement']) ? 
									preg_replace("=<br */?>=i", "\n",@$arr['console_statement']) : null;
						$debug_console=($statement) ? @strip_tags($statement)." ".$console_string : $console_string;
						$console_type=$arr['type'].'['.@end(@explode('/',$arr['errfile'][0])).':';
						$console_type.=$arr['errline'][0].']';
						$key=(@$arr['type']=='log') ? 'messages' : 'sql';
						if(self::$_options['show_'.$key]){ @debug($debug_console,$console_type); }
					}
				}
				$time=((self::$_endTime-self::$_startTime)-self::$_tickTime);
				$console_final='Seconds: '.round($time,3).' | Milliseconds: '.round($time*1000,3);
				@debug($console_final,'Global Execution Time');
			}
		}
		/**
		* Checks if a given ip has access
		* @param 	string|array	$allowedIps	the ip's that are allowed
		*/
		protected static function _checkAccess($allowedIps=null)
		{
			self::$_options['allowed_ips']=(!$allowedIps) ? self::$_options['allowed_ips'] : $allowedIps;
			if(self::$_options['allowed_ips'])
			{
				self::$_options['allowed_ips']=(is_array(self::$_options['allowed_ips'])) ? 
									self::$_options['allowed_ips'] : array(self::$_options['allowed_ips']);
				if(@in_array(@$_SERVER['REMOTE_ADDR'],self::$_options['allowed_ips'])){ return true; }
				return false;
			}
			return true;
		}
		/**
		* Sets session vars to control which panels will be shown
		*/
		protected static function _setSessionVars()
		{
			$_SESSION['debug_show_messages']=self::$_options['show_messages'];
			$_SESSION['debug_show_globals']=self::$_options['show_globals'];
			$_SESSION['debug_show_sql']=self::$_options['show_sql'];
		}
		/**
		* Controls which panels will be shown with $_GET variable "hidepanels"
		*/
		protected static function _disablePanels()
		{
			$hide=@explode(',',$_GET['hidepanels']);
			if(!@empty($hide))
			{
				$_SESSION['debug_show_messages']=true;
				$_SESSION['debug_show_globals']=true;
				$_SESSION['debug_show_sql']=true;
				foreach($hide as $k=>$v)
				{
					if($v=='msg' || $v=='all'){ $_SESSION['debug_show_messages']=false; }
					if($v=='globals' || $v=='all'){ $_SESSION['debug_show_globals']=false; }
					if($v=='sql' || $v=='all'){ $_SESSION['debug_show_sql']=false; }
				}
			}			
			self::$_options['show_messages']=$_SESSION['debug_show_messages']; 
			self::$_options['show_globals']=$_SESSION['debug_show_globals']; 
			self::$_options['show_sql']=$_SESSION['debug_show_sql']; 
		}
		/**
		* Builds the buffer
		* @param 	string	$type		log/sql
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement preceding the string
		* @param	string	$category	a category for the message
		*/
		protected static function _buildBuffer($type,$string,$statement=null,$category=null)
		{
			if(@$_SESSION[self::$_options['url_key']])	// if debug is on
			{
				if(self::$_options['show_interface'] || self::$_options['debug_console'])
				{
					$buffer=array('start_time'=>microtime(true),'type'=>$type);
					$php_trace=self::_debugTrace(self::$_options['trace_depth']);
					$buffer['errline']=@$php_trace['line'];
					$buffer['errfile']=@$php_trace['file'];
					$buffer['function']=@$php_trace['function'];
					$buffer['class']=@$php_trace['class'];
					if($string==='{errorHandler}')
					{
						$buffer['errno']=$statement['errno'];
						$buffer['errstr']=$statement['errstr'];
						if($statement['errfile']=='trace')
						{
							$params=@explode(':',@$buffer['errfile'][0]);
							@$buffer['errfile'][0]=@$params[0];
						}
						else // if self::errorHandler() called the function
						{
							if(!@is_array($buffer['errline'])){ $buffer['errline']=array(); }
							if(!@is_array($buffer['errfile'])){ $buffer['errfile']=array(); }
							if(!@is_array($buffer['function'])){ $buffer['function']=array(); }
							if(!@is_array($buffer['class'])){ $buffer['class']=array(); }
							@array_unshift($buffer['errline'],$statement['errline']);
							@array_unshift($buffer['errfile'],$statement['errfile']);
							@array_unshift($buffer['function'],'');
							@array_unshift($buffer['class'],'');
						}
					}
					else
					{
						$params=@explode(':',@$buffer['errfile'][0]);
						@$buffer['errfile'][0]=@$params[0];
						$buffer['var_type']=gettype($string);
						if(!$category){ $category=self::$_defaultOptions['default_category']; } 
						$buffer['errno']=$category;
						$buffer['errstr']=$string;
						$buffer['errmsg']=$statement;						
						if(self::$_options['debug_console'])
						{
							$buffer['console_string']=$string;
							$buffer['console_statement']=$statement;
						}
					}
					@self::$_buffer[]=$buffer;
					if(self::$_countTime)
					{ 					
						self::$_tickTime=((microtime(true)-$buffer['start_time'])+self::$_tickTime);
					}
				}
			}	
		}
		/**
		* Evaluates the type of variable for output
		* @param 	mixed 	$string	the variable to pass
		* @return	returns the html output with the variable content
		*/
		protected static function _formatVar($var)
		{	
			if(is_array($var) || is_object($var)){ $html_string=self::_doDump($var); }
			else if(@is_bool($var))
			{ 
				$html_string='<span style="color:#92008d;">'.($var==1 ? 'TRUE' : 'FALSE').'</span>'; 
			}
			else if(@is_null($var)){ $html_strisng='<span style="color:black;">NULL</span>'; }
			else if(@is_float($var)){ $html_string='<span style="color:#10C500;">'.$var.'</span>'; }
			else if(is_int($var)){ $html_string='<span style="color:red;">'.$var.'</span>'; }
			// could be a string
			else{ $html_string='<span>'.self::_cleanBuffer(@print_r($var,true)).'</span>'; }
			return $html_string;
		}
		/**
		* Retrieves the variable to watch from the "$GLOBALS"
		* @param 	string 	$variableName		the name of the variable to find
		* @return	returns the watched variable if found, otherwise null
		*/
		protected static function _findWatchVar($variableName)
		{
			$pieces=preg_split('/(->|\[.*?\])/',$variableName,null,PREG_SPLIT_DELIM_CAPTURE);
			$pieces=array_filter($pieces);
			$skip_key=null;
			$watch='$GLOBALS[\''.$pieces[0].'\']';
			foreach($pieces as $k=>$v)
			{
				if($k>0 && $k!=$skip_key && $v)
				{
					if($m=preg_match('/->/',$v) && @$pieces[$k+1])
					{
						$skip_key=($k+1);
						$watch=$watch.'->'.$pieces[$k+1];
					}
					else if($y=preg_match('/\[(.*?)\]/',$v,$match,PREG_OFFSET_CAPTURE))
					{ 
						$watch=$watch.$v;
					}
				}
			}
			return $var=@eval('return @'.$watch.';');
		}
		/**
		* Finds a value in the buffer based on a reference (the "$statement")
		* @param 	string	$reference	the reference to look for
		* @param	numeric	$type		"1" to time execution, "2" to attach data to a message
		* @return	returns the array if the given reference is found in the buffer
		*/
		protected static function _findReference($reference,$type=1)
		{
			switch($type)
			{
				case 1:$msg='Could not find reference "'.$reference.'" to time execution!';
				break;
				case 2:$msg='Could not find reference "'.$reference.'" to add data to!';
				break;
			}
			for($i=0;$i<@count(self::$_buffer);$i++)
			{
				if($reference==@self::$_buffer[$i]['errmsg'])
				{
					$last['data']=self::$_buffer[$i];
					$last['key']=$i;
				}
			}
			if(!@$last)
			{
				$err=array('errno'=>self::_msgType(E_USER_WARNING),'errstr'=>$msg,'errfile'=>'trace');
				self::_buildBuffer('log','{errorHandler}',$err);
				return false; 
			}
			return $last;
		}
		/**
		* Custom dump to properly format a given variable and make it more friendly to read
		* @param 	mixed 	$var			the string to pass
		* @param 	mixed 	$varName	some statement preceding the variable
		* @param 	string 	$indent		uses "|" as indents by default
		* @param 	string 	$reference	a reference to prevent recursion
		* @return	returns the html output with the variable
		*/
		protected static function _doDump(&$var,$varName=NULL,$indent=NULL,$reference=NULL,$depth=0)
		{
			$span_color='color:grey;';
			$do_dump_indent='<span style="color:white;"> | &nbsp;</span>';
			$reference=$reference.$varName;
			$keyvar='recursion_protection_scheme'; 
			$keyname='referenced_object_name';
			if((is_array($var) && isset($var[$keyvar])) || is_object($var) && property_exists($var,$keyvar))
			{
				if(is_array($var))
				{
					$real_var=&$var[$keyvar];
					$real_name=&$var[$keyname];
					$type=ucfirst(gettype($real_var));
					$result=$indent.$varName.' <span style="'.$span_color.'">'.$type.
						'</span> => **RECURSION** <span style="color:#e87800;">&amp;'.
															$real_name.'</span><br>';
				}
				else // if it's object
				{
					$real_var=&$var;
					$real_name=get_class($var);
					//$real_name=$real_name.'->'.$reference;
					$type=ucfirst(gettype($real_var));
					$varName='<span>'.$varName.'</span>';
					$result=$indent.$varName.' => <span style="'.$span_color.'">'.
								$type.'</span>(<span style="color:#e87800;">&amp;'.
											$real_name.'</span>) **RECURSION** <br>';
				}
				return $result;
			}
			else
			{
				$id="ptc-debug-".rand();
				$var=array($keyvar=>$var,$keyname=>$reference);
				$avar=&$var[$keyvar];
				$type=ucfirst(gettype($avar));
				switch($type)
				{
					case 'String':$type_color='<span style="color:#CF3F33;">';break;
					case 'Integer':$type_color='<span style="color:red;">';break;
					case 'Double':$type_color='<span style="color:#10C500;">'; $type='Float';break;
					case 'Boolean':$type_color='<span style="color:#92008d;">';break;
					case 'NULL':$type_color='<span style="color:black;">';break;
					default:$type_color='<span>';
				}
				if(is_array($avar))
				{
					$count=count($avar);
					@$result.=$indent.'<span>'.($varName ? $varName.'</span> => ' : '</span>');
					if(!empty($avar))
					{
						$depth=($depth+1);
						$result.='<a href="#" onclick="showVars(\''.$id.'\',this);return false;"><span>'.
															$type.'('.$count.')&dArr;</span></a>';
						$result.='<div style="display:none;" id="'.$id.'">'.$indent.'<span> (</span><br>';
						$keys=array_keys($avar);
						if($depth<self::$_options['max_dump_depth'])
						{
							foreach($keys as $name)
							{
								if($name!=="GLOBALS") // avoid globals for recursion nightmares
								{
									$value=&$avar[$name];
									$name=self::_cleanBuffer($name);
									$result.=self::_doDump($value,'<span style="color:#CF7F18;">[\''.
										$name.'\']</span>',$indent.$do_dump_indent,$reference,$depth);
								}
							}
						}
						else
						{
							$result.=$indent.$do_dump_indent.$varName.' <span style="'.
									$span_color.'">'.$type=ucfirst(gettype($var[$keyvar])).
										'</span> => **MAX DEPTH REACHED** <span style="color:#e87800;">'.
																		$var[$keyname].'</span><br>';
						}
						$result.=$indent.'<span> )</span></div><div><!-- --></div>';
					}
					else{ $result.='<span style="'.$span_color.'">'.$type.'('.$count.')</span></br>'; }
				}
				else if(is_object($avar))
				{
					@$avar->recursion_protection_scheme="recursion_protection_scheme";
					$depth=($depth+1);
					@$result.=$indent.($varName ? $varName.' => ' : '');
					$result.='<a href="#" onclick="showVars(\''.$id.'\',this);return false;">';
					$result.='<span>'.$type.'('.get_class($avar).')&dArr;</span></a>'.
						'<div style="display:none;" id="'.$id.'">'.$indent.' <span> ( </span><br>';
					if($depth<self::$_options['max_dump_depth'])
					{
						// public properties
						$class_properties=array();
						foreach($avar as $name=>$value)
						{					
							$name=self::_cleanBuffer($name);
							$result.=self::_doDump($value,$name,$indent.$do_dump_indent,$reference,$depth);
							$class_properties[]=$name;
						}
						// protected/private properties
						$class=new ReflectionClass($avar);
						$properties=$class->getProperties();
						foreach($properties as $property) 
						{
							$name=$property->getName();
							if($property->isPrivate()){ $name=$name.':private'; }
							else if($property->isProtected()){ $name=$name.':protected'; }
							if($property->isStatic()){ $name=$name.':static'; }
							$property->setAccessible(true);
							$value=$property->getValue($avar);
							if(!in_array($name,$class_properties))
							{
								$name=self::_cleanBuffer($name);
								$result.=self::_doDump($value,$name,$indent.$do_dump_indent,$reference,$depth);
							}
						}
						$methods=$class->getMethods();
						if($methods)
						{
							$class_methods=array();
							$z=0;
							foreach($methods as $method)
							{ 
								$name=$method->getName();
								if($method->isPrivate()){ $name=$name.':private'; }
								else if($method->isProtected()){ $name=$name.':protected'; }
								if($method->isStatic()){ $name=$name.':static'; }
								$class_methods[$z]=$name;
								$z++;
							}
							$result.=self::_doDump($class_methods,
								'<span style="color:#CF7F18;">[**class_methods:'.get_class($avar).
												'**]</span>', $indent.$do_dump_indent,$reference);
						}
					}
					else
					{
							$result.=$indent.$do_dump_indent.$varName.
								' <span style="'.$span_color.'">'.$type=ucfirst(gettype($var[$keyvar])).
								'</span> => **MAX DEPTH REACHED** <span style="color:#e87800;">'.
																	$var[$keyname].'</span><br>';
					}
					$result.=$indent.'<span> ) </span></div><div><!-- --></div>';
					unset($avar->recursion_protection_scheme);
				}
				else
				{
					if($varName=="recursion_protection_scheme"){ return; }
					@$result.=$indent.'<span>'.$varName.'</span> => <span style="'.$span_color.'">';
					if(is_string($avar) && (strlen($avar)>50))
					{ 
						$result.='<a href="#" onclick="showString(\''.$id.
									'\',this);return false;" style="font-weight:bold;">'; 
					}
					$result.=$type.'(';
					if(is_bool($avar))
					{
						$result.=strlen($avar).')</span> '.$type_color.($avar==1 ? "TRUE" : "FALSE").'</span><br>';
					}
					else if(is_null($avar)){ $result.=strlen($avar).')</span> '.$type_color.'NULL</span><br>'; }
					else if(is_string($avar))
					{
						$avar=trim(self::_cleanBuffer($avar));
						$string=(strlen($avar)>50) ? substr($avar,0,47).'...' : $avar;
						$string='<span id="'.$id.'-span">\''.$string.'\'</span>';
						$result.=strlen($avar).') ';
						$result.=(strlen($avar)>50) ? '&dArr;</span></a>' : '</span>';
						$result.=$type_color.$string.'</span>';
						if(strlen($avar)>50)
						{ 
							$result.='<div style="display:none;" id="'.$id.'">'.$type_color.'\''.$avar.'\'</div>'; 
						}
						$result.='<br>';
					}
					else // could be a float, an integer or undefined
					{										
						//$avar=self::_cleanBuffer($avar);
						$result.=@strlen($avar).')</span> '.$type_color.$avar.'</span><br>';
					}
				}
				$var=@$var[$keyvar];
			}
			//$var=@$var[$keyvar];			
			return $result;
		}
		/**
		* Sorts the buffer
		* @return	returns the sorted buffer array
		*/
		protected static function _sortBuffer()
		{
			if(@self::$_buffer)
			{
				foreach(self::$_buffer as $k=>$arr)
				{
					$type=$arr['type'];
					//unset($arr['type']);
					$buffer[$type][]=$arr;
				}
				return @self::$_buffer=$buffer;
			}
		}
		/**
		* Trace php as best as we can
		* @return	returns the trace without the methods in the {@link _excludeMethods} property
		*/
		protected static function _debugTrace($depth=NULL)
		{										
			if(!$depth){ $depth=self::$_options['trace_depth']; }
			$raw_trace=debug_backtrace();
			$this_methods=get_class_methods(__CLASS__);
			foreach($raw_trace as $k=>$arr)
			{
				if((@$arr['class']=='PtcDebug' && (@preg_match("|_|",@$arr['function']) || 
								@in_array(@$arr['function'],self::$_excludeMethods))) || 
								@preg_match("|__|",@$arr['function'])){ unset($raw_trace[$k]); }
			}
			if(!empty($raw_trace))
			{
				$raw_trace=@array_values($raw_trace);
				$raw_trace=@array_reverse($raw_trace);					
				if($depth>count($raw_trace)){ $depth=count($raw_trace); }
				for($i=0;$i<$depth;$i++)
				{ 
					if(@$raw_trace[$i]['line'] && @$raw_trace[$i]['file'])
					{
						$php_trace['file'][]=$raw_trace[$i]['file'].':'.$raw_trace[$i]['line'];
						$php_trace['line'][]=$raw_trace[$i]['line'];
						$php_trace['class'][]=@$raw_trace[$i]['class'];
						//$php_trace['function'][]=$raw_trace[$i]['function'];
						$php_trace['function'][]=(@$raw_trace[$i]['function']) ? 
												$raw_trace[$i]['function'].'()' : null;
					}
				}
			}
			else{ $php_trace=null; }
			return @$php_trace;
		}
		/**
		* Gets the fatal error on shutdown
		*/
		protected static function _lastError()
		{
			if($error=error_get_last()) 
			{
				$err_type=self::_msgType($error['type']);
				if($err_type=='Php Error')
				{				   
					$err=array('errno'=>$err_type,'errstr'=>$error['message'],
								'errfile'=>$error['file'],'errline'=>$error['line']);
					self::_buildBuffer('log','{errorHandler}',$err);
				}
			}
		}
		/**
		* Builds the debug interface
		* @return	returns the html with interface
		*/
		protected static function _buildInterface()
		{
			self::_sortBuffer();
			$interface=self::_includeJs();						// include js
			$interface.=self::_includeCss();					// include css
			$interface.='<div id="topRight">';
			$interface.='<div id="panelTitle" style="display:none;">&nbsp;</div>';
			$interface.=self::_buildMenu();					// top menu
			$interface.=self::_buildMsgPanel('log','msgPanel');		// msgs
			$interface.=self::_buildVarsPanel();					// vars
			$interface.=self::_buildMsgPanel('sql','sqlPanel');		// sql
			$interface.=self::_buildW3cPanel();					// w3c
			$interface.=self::_buildTimerPanel();				// timer
			$interface.='<div id="statusBar" style="display:none;">&nbsp;</div>';
			$interface.='</div>';
			//$interface=self::_compressHtml($interface);	// make html lighter
			return $interface;
		}
		/**
		* Builds the debug menu
		* @return	returns the html menu compressed
		*/
		protected static function _buildMenu()
		{
			$ul='<ul class="tabs right" id="floatingTab">';
			//$ul.='<li><a href="#" onClick="minimize();return false;">>></a></li>';
			$ul.='<li>'.self::_menuLinks('msgPanel','log & messages','messages').'</li>';
			$ul.='<li>'.self::_menuLinks('varsPanel','configuration & variables','vars & config').'</li>';
			$ul.='<li>'.self::_menuLinks('sqlPanel','mysql query messages','sql').'</li>';
			$ul.='<li>'.self::_menuLinks('w3cPanel','W3C validator','w3c').'</a></li>';
			$ul.='<li>'.self::_menuLinks('timerPanel','execution time monitor','timer').'</li>';
			$ul.='<li><a href="#" onClick="hideInterface();return false;">X</a></li>';
			$ul.='</ul> ';
			return $ul=self::_compressHtml($ul);
		}
		/**
		* Builds the menu links
		* @param	string	$Id		the panel id
		* @param	string	$title	the panel title
		* @param	string	$text	the text for the link
		* @return	returns the html anchor tag
		*/
		protected static function _menuLinks($Id,$title,$text)
		{
			$title=ucwords($title);
			$text=strtoupper($text);
			$return='<a href="#" onClick="showPanel(\''.$Id.'\',\''.$title.'\',this);return false;">';
			return $return.=$text.'</a>';
		}
		/**
		* Checks message types
		* @param	string|numeric		php standards
		* @return	returns the message type as a readable string
		*/
		protected static function _msgType($msg=NULL)
		{
			switch($msg)
			{
				case @E_NOTICE:
				case @E_USER_NOTICE:
				case @E_DEPRECATED:
				case @E_USER_DEPRECATED:
				case @E_STRICT:
					return "Php Notice";
				break;
				case @E_WARNING:
				case @E_USER_WARNING:
				case @E_CORE_WARNING:
				case @E_COMPILE_WARNING:
					return "Php Warning";
				break;
				case @E_ERROR:
				case @E_RECOVERABLE_ERROR:
				case @E_USER_ERROR:
				case @E_CORE_ERROR:
				case @E_COMPILE_ERROR:
					return 'Php Error';
				break;
				case 'exception': return 'Exception';
				break;
				default:return 'General';break;
			}
		}
		/**
		* Builds the html log and sql tables
		* @param	string	$type	sql|log
		* @return	returns the html table data
		*/
		protected static function _buildHtmlTable($type)
		{
			$div=null;
			if(@self::$_buffer[$type])
			{
				$categories=array();
				foreach(self::$_buffer[$type] as $k=>$arr)
				{
					if(@$arr['errno'])
					{			
						if(!array_key_exists($arr['errno'],$categories)){ $categories[$arr['errno']]=1; }
						else{ $categories[$arr['errno']]=($categories[$arr['errno']]+1); }
					}
				}
				if(sizeof($categories)>1)
				{
					ksort($categories);
					$div.='<div id="filterBar"><a href="#" onClick="filter_categories(\''.$type.
										'Table\',\'showAll\')" class="show-all">Show All</a> | ';
					foreach($categories as $k=>$v)
					{ 
						$catId=str_replace(" ","-",strtolower($k));
						$div.='<a href="#" onClick="filter_categories(\''.$type.
									'Table\',\''.$catId.'\')" class="'.$catId.'">'.$k."(".$v.")</a> | "; 
					}
					$div=substr($div,0,-3);
					$div.='</div>';
				}
				$a=1;
				$div.='<table border="1" style="width:100%" class="msgTable" id="'.$type.'Table"><tr>';
				$div.='<th>#</th><th>category</th><th>file</th><th>line</th>
												<th>class</th><th>function</th>';
				if($type=="log"){ $div.='<th>type</th>'; }
				$div.='<th>time</th><th>message</th></tr>';
				foreach(self::$_buffer[$type] as $k=>$arr)
				{
					$msg_class=@str_replace(' ','-',$arr['errno']);
					$div.='<tr class="'.strtolower($msg_class).'"><td class="fixed"># '.$a.'</td>';
					//$div.='<td style="'.self:: _errorMsgStyle($arr['errno']).'">'.$arr['errno'].'</td>';
					$div.='<td class="fixed"><span style="color:green;">'.@$arr['errno'].'</span></td>';
					$div.='<td class="fixed">';
					$div.=@self::_buildTraceLink(@$arr['errfile'][0],@$arr['errline'][0]);
					$div.='<span>'.@end(@explode('/',$arr['errfile'][0])).'</span></a>';
					if(count(@$arr['errfile'])>1)
					{
						$class='ptc-debug-class-'.rand();
						$div.=' <a href="#" onclick="showTrace(\''.$class.'\',this);return false;"><span>'.
																		'&dArr;</span></a>';
					}
					@array_shift($arr['errfile']);
					if(!empty($arr['errfile']))
					{
						$indent='<span style="color:black;">| &nbsp;</span>';
						foreach($arr['errfile'] as $k=>$file)
						{
							$div.='<div class="'.$class.'" style="display:none;">';
							
							if($file || @$arr['errfile'][$k+1]){ $div.=$indent; }
							
							$params=@explode(':',$file);
							$div.=@self::_buildTraceLink($params[0],$params[1]);
							$div.=@end(@explode('/',$file)).'</a></div>';		
							$indent=$indent.'<span style="color:black;">| &nbsp;</span>';							
						}
					}
					$div.='</td>';
					$div.='<td class="fixed">'.@self::_buildTraceTree(@$arr['errline'],$class,'black').'</td>';
					$div.='<td class="fixed">'.@self::_buildTraceTree(@$arr['class'],$class,'purple').'</td>';
					$div.='<td class="fixed">'.@self::_buildTraceTree(@$arr['function'],$class,'darkred').'</td>';
					if($type=="log")
					{
						$div.='<td class="fixed">';
						switch(@$arr['var_type'])
						{
							case 'boolean': $color='color:#92008d;'; break;
							case 'NULL': $color='color:black;'; break;
							case 'integer': $color='color:red;';  break;
							case 'double': $color='color:#10C500;'; break;
							case 'array': $color='color:blue'; break;
							case 'object': $color='color:#CF3F33'; break;
							//case 'string': $color='color:#CF3F33'; break;
							default: $color='';
						}
						$div.='<span style="'.$color.'">'.@$arr['var_type'].'</span>';
						$div.='</td>';
					}
					$div.=(@$arr['time']) ? '<td class="fixed"><span style="color:blue;font-weight:normal;"">'.
											$arr['time'].'</span></td>' : '<td class="fixed">&nbsp;</td>';	
					$errors=array('php-warning','php-notice','php-error','exception');											
					$err_style=(!in_array(strtolower($msg_class),$errors)) ? 'font-weight:normal;' : 'color:darkred;';			
					$div.='<td><span style="'.$err_style.'">';
					if(@$arr['errmsg']){ $div.=@$arr['errmsg'].' '; }
					$div.=self::_formatVar(@$arr['errstr']);
					$div.='</span></td></tr>';
					$a++;
				}
				$div.="</table><br>";
			}
			else{ $div='<span class="vars">no messages</span>'; }
			return $div;
		}
		/**
		* Builds the link for the code highlighter popup
		* @param	string	$file		the full path to the file
		* @param	string	$line	the line to be highlighted
		*/
		protected static function _buildTraceLink($file,$line=null)
		{
			$html='<a href="#" onclick="';
			if(session_id()!=='' && @$_SESSION['code_highlighter'])
			{
				$html.='read_code(\''.$file.'\',\''.$line.'\');return false;" title="'.@$file.'">';
			}
			else
			{ 
				$html.='return false;"';
				$html.=' title="'.@$file.' '."\n\n".'** USE SESSION_START(), IF YOU WISH';
				$html.=' TO ACTIVATE THE CODE POPUP HIGHLIGHTER **" style="cursor:text;">'; 
			}	
			return $html;
		}
		/**
		* Builds the tree for the links in the vars & config panel
		* @param	mixed	$var			the variable
		* @param	string	$className	a css class
		* @param	string	$styleColor	the color for 
		*/
		protected static function _buildTraceTree($var,$className=null,$styleColor=null)
		{
			$indent='';
			foreach($var as $k=>$v)
			{
				if($k>0)
				{ 
					$display='display:none;'; 
					$class=' class="'.$className.'"';
				}
				@$html.='<div style="font-weight:bold;color:'.$styleColor.';'.@$display.'"'.@$class.'>';
				
				if($v || @$var[$k+1]){ $html.=$indent; }
				
				if(!$v){ $v='&nbsp;'; }
				$html.=$v.'</div>';
				$indent=$indent.'<span style="color:black;">| &nbsp;</span>';
			}
			return $html;
		}
		/**
		* Builds the log/sql panel
		* @param	$type		log or sql
		* @param	$panelId		some id for the panel
		*/
		protected static function _buildMsgPanel($type,$panelId)
		{
			$div='<div id="'.$panelId.'" style="display:none;" class="innerTable">';
			$key=($type=='log') ? 'messages' : 'sql';
			if(!self::$_options['show_'.$key]){ return $div.='<span class="vars">Panel is Disabled</span></div>'; }
			$div.=(@self::$_buffer[$type]) ? self::_buildHtmlTable($type) : '<span class="vars">no messages</span>';
			return $div.='</div>';
		}
		/**
		* Builds the timer panel
		*/
		protected static function _buildTimerPanel()
		{
			$time=((self::$_endTime-self::$_startTime)-self::$_tickTime);
			$div='<div id="timerPanel" style="display:none;" class="innerTable">';
			$div.='<span style="font-weight:bold;">Global Execution Time:</span>';
			$div.='<br>Seconds: '.round($time,3).'<br>Milliseconds: '.round($time*1000,3);
			$div.='</div>';
			return   $div=self::_compressHtml($div);
		}
		/**
		* Builds the vars panel
		*/
		protected static function _buildVarsPanel()
		{
			$div='<div id="varsPanel" style="display:none;" class="innerTable">';
			$div.='<a href="#" onClick="showVars(\'files\',this)">Files';
			$included_files=@get_included_files();
			$div.='<span class="count_vars">('.@sizeof($included_files).')</span>&dArr;</a><br>';
			$div.='<div id="files" class="vars" style="display:none;line-height:20px;">';
			if(!@empty($included_files))
			{
				foreach($included_files as $filename)
				{ 
					$div.=@self::_buildTraceLink($filename).$filename.'</a>';
					if($_SERVER['SCRIPT_FILENAME']==$filename)
					{ 
						$div.=' <span style="font-weight:bold;color:red;">&laquo; Main File</span>'; 
					}
					$div.="<br>\n"; 
				}
			}
			else{ $div.='<span class="vars">Could not get include files!</span>'; }
			$div.='</div>'; 
			$div.=self::_buildInnerVars('options','Configuration',self::$_options);
			$constants=@get_defined_constants(true);
			$div.=self::_buildInnerVars('constants','Constants',$constants);
			$functions=@get_defined_functions();
			$div.=self::_buildInnerVars('functionsInternal','Internal Functions',@$functions['internal']);
			$div.=self::_buildInnerVars('functionsUser','User Functions',@$functions['user']);
			$div.=self::_buildInnerVars('phpInfo','Php',$php_info=self::_buildPhpInfo());
			$div.=self::_buildInnerVars('declared_classes','Declared Classes',
											$classes=@get_declared_classes());
			$div.=self::_buildInnerVars('declared_interfaces','Declared Interfaces',
										$interfaces=@get_declared_interfaces());
			if(!self::$_options['show_globals']){ $div.='<span class="vars">Global Vars Disabled</span>'; }
			else{ $div.=self::_buildInnerVars('globals','Globals',array_reverse($GLOBALS)); }
			return  $div.='</div>';
		}
		/**
		* Builds the inner vars div
		* @param	string	$panelId		the id of the panel to show/hide
		* @param	string	$linkTitle		the title of the link
		* @param	string	$array		array of parameters
		*/
		protected static function _buildInnerVars($panelId,$linkTitle,$array)
		{
			$div='<div id="'.$panelId.'" class="vars vars-config" ';
			$div.='style="line-height:20px;font-size:14px;">'.$linkTitle;
			$div.=@self::_doDump($array);
			return $div.='</div>';
		}
		/**
		* Builds the W3C panel
		*/
		protected static  function _buildW3cPanel()
		{
			$uri=parse_url($_SERVER['REQUEST_URI']);
			if(@$uri['query'])
			{
				$query='?';
				$parts=explode('&',$uri['query']);
				foreach($parts as $k=>$v)
				{
					if($v!=self::$_options['url_key'].'='.self::$_options['url_pass'])
					{
						$query.=($k==0) ? $v : '&'.$v;
					}
				}
			}
			$div='<div id="w3cPanel" style="display:none;" class="innerTable">';
			$div.='<p>Click on the WC3 link to verify the validation or to check errors</p>';
			$div.='<p><a href="http://validator.w3.org/check?uri='.$_SERVER['HTTP_HOST'].
												$uri['path'].@$query.'" target="_blank">';
			$div.='<img src="http://www.w3.org/Icons/WWW/w3c_home_nb" alt="W3C Validator"></a></p>';
			$div.='<p>Or copy paste the source here ';
			$div.='<a href="http://validator.w3.org/#validate_by_input" target="_blank">';
			$div.='http://validator.w3.org/#validate_by_input</a></p>';    
			$div.='</div>';
			return  $div=self::_compressHtml($div);
		}
		/**
		* Formats phpinfo() function
		*/
		protected static function _buildPhpInfo()
		{
			
			$php_array=self::_phpInfoArray();
			$php_array['version']=@phpversion();
			$php_array['os']=@php_uname();
			$php_array['extensions']=@get_loaded_extensions();
			ksort($php_array);
			return $php_array;
		}
		/**
		* Includes the css for the interface
		*/
		protected static function _includeCss()
		{
			return self::_compressHtml(
				'<style type="text/css">
					#topRight{font-family:Arial,sant-serif;position:fixed;top:0px;right:3px;
					background:#eee;color:#333;z-index:10000;}ul.tabs li{background-color:#ddd;
					border-color:#999;margin:0 -3px -1px 0;padding:3px 6px;border-width:1px;
					list-style:none;display:inline-block;border-style:solid;}
					ul.tabs li.active{background-color:#fff;border-bottom-color:transparent;
					text-decoration:}ul.tabs li:hover{background-color:#eee;}
					ul.tabs li.active:hover{background-color:#fff;}
					ul.tabs.merge-up{margin-top:-24px;}
					ul.tabs.right{padding:0 0 0 0;text-align:right;}
					ul.tabs{border-bottom-color:#999;border-bottom-width:1px;font-size:14px;
					list-style:none;margin:0;padding:0;z-index:100000;position:relative;
					background-color:#EEE}ul.tabs a{color:purple;font-size:10px;
					text-decoration:none;}.tabs a:hover{color:red;}
					ul.tabs a.active{color:black;background-color:yellow;}
					.msgTable{padding:0;margin:0;border:1px solid #999;font-family:Arial;
					font-size:11px;text-align:left;}.msgTable th{margin:0;border:0;
					padding:3px 5px;vertical-align:top;background-color:#999;color:#EEE;
					white-space:nowrap;}.msgTable td{margin:0;border:0;
					padding:3px 3px 3px 3px;vertical-align:top;}
					.msgTable tr td{background-color:#ddd;color:#333}
					.msgTable tr.php-notice td{background-color:lightblue;}
					.msgTable tr.exception td{background-color:greenyellow;}
					.msgTable tr.php-warning td{background-color:yellow;}
					.msgTable tr.php-error td{background-color:orange;}
					.msgTable tr.inspector td{background-color:lightgreen;}
					.innerTable a.php-notice{color:lightblue;}
					.innerTable a.exception{color:greenyellow;}
					.innerTable a.php-warning{color:yellow;}
					.innerTable a.php-error{color:orange;}.innerTable a.inspector{color:lightgreen;}
					.innerTable a.general{color:darkgrey;}.innerTable a.show-all{color:red;}
					#filterBar{background-color:black;margin-bottom:8px;padding:4px;font-size:13px;}
					.innerTable{z-index:10000;position:relative;background:#eee;
					height:300px;padding:30px 10px 0 10px;overflow:auto;clear:both;}
					.innerTable a{color:dodgerBlue;font-size:bold;text-decoration:none}
					.innerTable p{font-size:12px;color:#333;text-align:left;line-height:12px;}
					.innerPanel h1{font-size:16px;font-weight:bold;margin-bottom:20px;
					padding:0;border:0px;background-color:#EEE;}
					#panelTitle{height:25px;float:left;z-index:1000000;position:relative;}
					#panelTitle h1{font-size:16px;font-weight:bold;margin-bottom:20px;
					margin-left:10px;padding:0 0 0 0;border:0px;background-color:#EEE;
					color:#669;margin-top:5px;;height:20px;}
					.vars-config, .vars-config span{font-weight:bold;}
					.msgTable pre span, .vars-config span{padding:2px;}
					.msgTable pre, span, .vars{font-size:11px;line-height:15px;
					font-family:"andale mono","monotype.com","courier new",courier,monospace;}
					.msgTable pre,.msgTable span{font-weight:bold;}
					#varsPanel a{text-decoration:none;font-size:14px;font-weight:bold;color:#669;
					line-height:25px;}.count_vars{font-size:11px;color:purple;padding:0;margin:0;}
					.fixed{width:1%;white-space:nowrap;}.fixed1{width:5%;white-space:nowrap;}
					#statusBar{height:2px;background-color:#999;}
				</style>');
		}
		/**
		* Includes the javascript for the interface
		*/
		protected static function _includeJs()
		{
			return self::_compressHtml(
				'<script>
					var activePanelID=false;
					var panels=new Object;panels.msg="msgPanel";panels.vars="varsPanel";
					panels.sql="sqlPanel";panels.w3c="w3cPanel";panels.timer="timerPanel";
					function showPanel(elId,panelTitle,el)
					{
						var floatDivId="topRight";
						var tabs=document.getElementById(\'floatingTab\').getElementsByTagName("a");
						for(var i=0;i<tabs.length;i++){tabs[i].className="";}
						if(document.getElementById(elId).style.display=="none")
						{ 	
							resetPanels();
							document.getElementById(elId).style.display=\'\'; 
							document.getElementById(\'statusBar\').style.display=\'\';
							document.getElementById(floatDivId).style.width=\'100%\';
							el.className="active";activePanelID=elId;setTitle(panelTitle);
						}
						else
						{
							document.getElementById(\'panelTitle\').style.display=\'none\';
							resetPanels();
							document.getElementById(floatDivId).style.width=\'\';
						}
						return false;
					};
					function resetPanels()
					{
						document.getElementById(\'statusBar\').style.display=\'none\'; 
						for(var i in panels){document.getElementById(panels[i]).style.display=\'none\';}
					};
					function setTitle(panelTitle)
					{
						document.getElementById(\'panelTitle\').style.display=\'\';
						document.getElementById(\'panelTitle\').innerHTML=\'<h1>\'+panelTitle+\'</h1>\';
					};
					function hideInterface(){document.getElementById(\'topRight\').style.display=\'none\';};
					function showVars(elId,link)
					{
						var element=document.getElementById(elId).style;
						if(element.display=="none")
						{
							link.innerHTML=link.innerHTML.replace("\u21d3","\u21d1");
							element.display=\'\'; 
						}
						else
						{
							link.innerHTML=link.innerHTML.replace("\u21d1","\u21d3");
							element.display=\'none\'; 
						}
					};
					function showString(elId,link)
					{
						if(document.getElementById(elId).style.display=="none")
						{ 	
							link.innerHTML=link.innerHTML.replace("\u21d3","\u21d1");
							document.getElementById(elId).style.display=\'inline\'; 
							document.getElementById(elId+"-span").style.display=\'none\'; 
						}
						else
						{ 
							link.innerHTML=link.innerHTML.replace("\u21d1","\u21d3");
							document.getElementById(elId+"-span").style.display=\'\'; 
							document.getElementById(elId).style.display=\'none\'; 
						}
					};
					function showTrace(className,link)
					{
						var elements=document.getElementsByClassName(\'\'+className+\'\');
						for(i in elements)
						{
							if(elements[i].hasOwnProperty(\'style\'))
							{
								if(elements[i].style.display=="none")
								{
									link.innerHTML=link.innerHTML.replace("\u21d3","\u21d1");
									elements[i].style.display=\'\'; 
								}
								else
								{
									link.innerHTML=link.innerHTML.replace("\u21d1","\u21d3");
									elements[i].style.display=\'none\'; 
								}
							}
						}
					};
					function read_code(filename,line) 
					{
						var query="http://'.$_SERVER['HTTP_HOST'].
							$path=str_replace($_SERVER['DOCUMENT_ROOT'],'',
							realpath(dirname(__FILE__))).'/PtcDebug.php?file="+filename;
						if(line){query+="&line="+line;}
						newwindow=window.open(query,"name","height=350,width=820");
						if(window.focus){newwindow.focus()};
						return false;
					};
					function filter_categories(tableId,catId)
					{
						var table=document.getElementById(tableId);
						var trs=table.getElementsByTagName("tr");
						if(catId=="showAll"){for(var i=1; i<trs.length; i++){trs[i].style.display="";}}
						else
						{
							for(var i=1; i<trs.length; i++){trs[i].style.display="none";}
							var cur_cat=document.getElementsByClassName(catId);
							for(var i=0; i<cur_cat.length; ++i){cur_cat[i].style.display="";}
						}
					};
					window.onload=function() 
					{
						var div=document.getElementById("statusBar");
						var press=false;
						div.onmousedown=function(){press=true;return false;};
						this.onmouseover=div.style.cursor="s-resize";
						this.onmousemove=function(event)
						{
							event=event ? event : window.event;
							if(press===true && activePanelID)
							{
								document.getElementById(activePanelID).style.height=(event.clientY-49)+"px";
							}
						};
						this.onmouseup=function(){press=false;};
					};
					/*function minimize()
					{
						var floatDivId="topRight";resetPanels();
						document.getElementById(floatDivId).style.width=\'300px\';
						return false;
					};*/
				</script>');
		}
		/**
		* Compresses the html before render
		* @var	string	$html	some html code
		*/
		protected static function _compressHtml($html)
		{
			$html=preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$html);// remove comments
			$html=str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),'',$html);// tabs,newlines,etc.
			return $html;
		}
		/**
		* Formats phpinfo() into an array
		*/
		protected static function _phpInfoArray()
		{
			ob_start();
			@phpinfo();
			$info_arr=array();
			$info_lines=explode("\n",strip_tags(ob_get_clean(),"<tr><td><h2>"));
			$cat="General";
			$reg_ex="~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~";
			foreach($info_lines as $line)
			{
				preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat=$title[1] : null;	// new cat?
				if(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~",$line,$val))
				{
				    $info_arr[$cat][$val[1]]=$val[2];
				}
				else if(preg_match($reg_ex,$line,$val))
				{ 
					$info_arr[$cat][$val[1]]=array("local"=>$val[2],"master"=>$val[3]); 
				}
			}
			return $info_arr;
		}
		/**
		* Array with all options
		* @var	array
		*/
		private static $_options=array();
		/**
		* The debug buffer
		* @var	array
		*/	
		private static $_buffer=array();
		/**
		* Application start time
		* @var	time
		* @see stopTimer()
		*/
		private static $_startTime=null;		
		/**
		* Application end time
		* @var	time
		* @see stopTimer()
		*/
		private static $_endTime=null;
		/**
		* Decides if we should send the buffer to the PhpConsole class
		* @var	bool
		*/
		private static $_consoleStarted=false;
		/**
		* Array of watched variables declared
		* @var	array
		*/
		private static $_watchedVars=array();
		/**
		* Tick execution time property
		* @var	array
		* @see watch_var()
		*/
		private static $_tickTime=0;
		/**
		* Checks if the {@link PtcDebug::load()} method has been called already 
		* @var	bool
		*/		
		private static $_isLoaded=false;
		/**
		* Exclude {@link $_buildBuffer} from execution timing property
		* @var	bool
		*/	
		private static $_countTime=true;
		/**
		* Removes html entities from the buffer
		* @param	string	$var		some string
		*/	
		private static function _cleanBuffer($var)
		{ 
			return (@is_string($var)) ? @htmlentities($var) : $var;
		}
	}
	/**
	* Writes data to the messages panel
	* @param 	mixed 	$string		the string to pass
	* @param 	mixed 	$statement	some statement if required
	* @param	string	$category	a category for the messages panel
	* @see PtcDebug::bufferLog()
	* @tutorial	PtcDebug.cls#logging.log_msg
	*/
	function log_msg($string,$statement=null,$category=null)
	{
		PtcDebug::bufferLog($string,$statement,$category);	
	}
	/**
	* Writes data to the sql panel
	* @param 	mixed 	$string		the string to pass
	* @param 	mixed 	$statement	some statement if required
	* @param	string	$category	a category for the sql panel
	* @see PtcDebug::bufferSql()
	* @tutorial	PtcDebug.cls#logging.log_sql
	*/
	function log_sql($string,$statement=null,$category=null)
	{
		PtcDebug::bufferSql($string,$statement,$category);	
	}
	/**
	* Monitors the execution of php code, or sql queries based on a reference 
	* @param	string			$reference	a reference to look for ("$statement")
	* @param 	string|numeric 	$precision	sec/ms
	* @see PtcDebug::stopTimer()
	* @tutorial	PtcDebug.cls#stopTimer
	*/
	function stop_timer($reference=null,$precision=1){ PtcDebug::stopTimer($reference,$precision); }
	/**
	* Attaches data to the buffer array based on a reference 
	* @param	string	$reference	a reference to look for ("$statement")
	* @param	mixed	$string		the message to show
	* @param	string	$statement	a new statement if required
	* @see PtcDebug::addToBuffer()
	* @tutorial	PtcDebug.cls#addToLog
	*/
	function add_to_log($reference,$string,$statement=null)
	{
		PtcDebug::addToBuffer($reference,$string,$statement);
	}
	/**
	* Watches a variable that is in a declare(ticks=n){ code block }, for changes 
	* @param 	string 	$variableName		the name of the variable to watch
	* @see PtcDebug::watch()
	* @tutorial	PtcDebug.cls#watchVar
	*/
	function watch_var($variableName){ PtcDebug::watch($variableName); }
	/**
	* Calls highlight file method to show source code, session_start() must be active for security reasons
	*/
	if(@$_GET['file'])
	{
		@session_start();
		if(!@$_SESSION['code_highlighter']){ exit(); }
		echo PtcDebug::highlightFile($_GET['file'],@$_GET['line']);
		exit();
	}
?>