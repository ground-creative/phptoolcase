<?php
	/**
	* DEBUGGER & LOGGER CLASS
	* <br>All class properties and methods are static because it's required 
	* to let them work on script shutdown when FATAL error occurs.
	* PHP version 5
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.8.1
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	class PtcDebug
	{
		/**
		* Alias of {@link PtcDebug::bufferLog()}
		*/
		public static function log($string,$statement=null,$function=null,$class=null)
		{ 
			self::_buildBuffer("log",$string,$statement,$function,$class);
		}
		/**
		* Alias of {@link PtcDebug::bufferSql()}
		*/
		public static function logSql($string,$statement=null,$function=null,$class=null)
		{ 
			self::_buildBuffer("sql",$string,$statement,$function,$class); 
		}
		/**
		* Check if debug parameters are set on referer url
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
				}
			}
		}													
		/**
		* Set the error handler to be the debug class. good for production if param is set to false
		* @param	string	$dieIfFatalError		die if fatal error occurs
		* @tutorial	PtcDebug.cls#setErrorHandler
		*/
		public static function setErrorHandler($dieIfFatalError=true)
		{
			ini_set('display_errors',false);
			ini_set('html_errors', false);				
			ini_set('error_reporting',E_ALL);
			self::$_options['die_on_error']=$dieIfFatalError;
			set_error_handler("PtcDebug::errorHandler"); 
		}
		/**
		* Load the debug interface if requested
		* @param 	array 	$options	array of options {@link _defaultOptions}
		* @tutorial	PtcDebug.cls#debugLoader
		*/
		public static function debugLoader($options=null)
		{
			self::$_options=(is_array($options)) ? 
				array_merge(self::$_defaultOptions,$options) : self::$_defaultOptions;
			if(!$has_access=self::_checkAccess()){ return; }	# check access with ips
			$buffer="Debug Info:";
			if(self::$_options['check_referer']){ self::checkReferer(); }# check if referer has debugging vars
			if(self::$_options['session_start'])# start session on request
			{
				if(session_id()==="")# check if session is already active
				{ 
					session_start(); 
					$buffer.="<br>Initialized browser session with session_start()";
				}
				else{ $buffer.="<br>Session id is ".session_id(); }
			}
			if(!@$_SESSION){ $_SESSION=array(); }
			if(@$_GET[self::$_options['url_key']]==self::$_options['url_pass'])
			{
				$_SESSION[self::$_options['url_key']]=true;
				$buffer.="<br>Html debug turned on!";
			}
			else if(@$_GET[self::$_options['url_key']."Off"]==self::$_options['url_pass'])
			{
				$_SESSION[self::$_options['url_key']]=false; 
			}
			if(@$_SESSION[self::$_options['url_key']])
			{ 
				$console_debug['errors']=false;
				$console_debug['exceptions']=false;
				$buffer_options=array();
				if(self::$_options['show_interface']){ register_shutdown_function('PtcDebug::showInterface'); }
				if(self::$_options['replace_error_handler'])	# replace error handler
				{
					$console_debug['errors']=true;
					$console_debug['exceptions']=true;
					self::setErrorHandler(self::$_options['die_on_error']);
					$buffer.="<br>Php debug turned on,error handler has been overridden!";
				}
				if(self::$_options['debug_console'])	# try to laod the console class
				{
					$buffer.="<br>Console debug turned on";
					if(file_exists(dirname(__FILE__)."/PhpConsole/PhpConsole.php"))
					{
						require_once(dirname(__FILE__)."/PhpConsole/"."PhpConsole.php");
						PhpConsole::start($console_debug['errors'],$console_debug['exceptions'],
																		dirname(__FILE__));
						$buffer.=", phpConsole class started!";
					}
					else if(class_exists('PhpConsole',true))
					{ 
						PhpConsole::start($console_debug['errors'],$console_debug['exceptions'],
																		dirname(__FILE__));
						$buffer.=", phpConsole class started!";
					}
					else{ $buffer.=", but could not find phpConsole class!"; }
				}
				self::$_startTime=microtime(true);
				self::bufferLog('',$buffer,__FUNCTION__,__CLASS__);
			}
		}
		/**
		* Write messsage to log panel
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement if required
		* @param 	string 	$function		the name of the function
		* @param 	string 	$class		the name of the class
		* @tutorial	PtcDebug.cls#bufferLog
		*/
		public static function bufferLog($string,$statement=null,$function=null,$class=null)
		{ 
			self::_buildBuffer("log",$string,$statement,$function,$class);
		}
		/**
		* Write message to sql panel
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement if required
		* @param 	string 	$function		the name of the function
		* @param 	string 	$class		the name of the class
		* @tutorial	PtcDebug.cls#bufferSql
		*/
		public static function bufferSql($string,$statement=null,$function=null,$class=null)
		{ 
			self::_buildBuffer("sql",$string,$statement,$function,$class); 
		}
		/**
		* Monitor the execution of something
		* @param 	string|numeric 	$precision	sec/ms
		* @tutorial	PtcDebug.cls#stopTimer
		*/
		public static function stopTimer($precision=1)
		{
			$now=microtime(true);
			$last=end(self::$_buffer);
			$time=($now-@$last['start_time']);
			switch($precision)
			{
				case 0:		#seconds
				case "sec":	#seconds
					self::$_buffer[$key=key(self::$_buffer)]['time']=round($time,3)." sec";
				break;
				case 1:		#millisecons
				case "ms":	#millisecons
				default:
					self::$_buffer[$key=key(self::$_buffer)]['time']=round($time*1000,3)." ms";
				break;
			}
		}
		/**
		* Handle php errors
		* @param 	string 	$errno	error number (php standards)
		* @param 	string 	$errstr	error string
		* @param 	string 	$errfile	error file
		* @param 	string 	$errline	error line
		* @see		setErrorHandler()
		*/
		public static function errorHandler($errno,$errstr,$errfile,$errline) 
		{
			if(error_reporting()==0){ return; }	# if error has been supressed with an @
			$err=array("errno"=>self::_msgType($errno),"errstr"=>$errstr,
									"errfile"=>$errfile,"errline"=>$errline);
			self::_buildBuffer("log","{errorHandler}",$err);
			# stop if fatal error occurs
			if(self::$_options['die_on_error'] && self::_msgType($errno)=="Php Error"){ die(); }
			return true;	# don't execute php error handler
		}
		/**
		* Attach message to the end of buffer array
		* @param	string	$string	the message to show
		* @param	string	$type	the array key in the buffer
		*/
		public static function addToBuffer($string,$statement=null,$type="errstr")
		{
			$now=microtime(true);
			$last=end(self::$_buffer);
			//$last[$type]=$string;
			
			if(is_array($string) || is_object($string))
			{ 
				$html_string=@print_r($string,true);
				$html_string=self::_cleanBuffer($html_string);
				$id="ptc-debug-".rand();
				$html_string='<a href="#" onclick="showArray(\''.$id.'\',this);return false;">
				Show Data</a><pre style="display:none" id="'.$id.'">'.$html_string.'</pre>';
			}
			else
			{ 
				$string=@print_r($string,true); 
				$html_string=htmlentities($html_string);
			}
			
			$last[$type]=($statement) ? $statement." ".$html_string : $html_string;
			
			self::$_buffer[$key=key(self::$_buffer)]=$last;
		}
		/**
		* Show the debug interface
		* @see		debugLoader()
		*/
		public static function showInterface()
		{
			self::$_endTime=microtime(true);
			self::_lastError();				# get last php fatal error
			$interface=self::_buildInterface();	# build the interface
			print $interface;
		}
		/**
		* default options for the debug class
		* @var	array
		* @tutorial	PtcDebug.cls#_defaultOptions
		*/
		protected static $_defaultOptions=array
		(
			'url_key'				=>	'debug',# the key to pass  to turn on debug
			'url_pass'				=>	'true',# the pass to turn on debug
			'replace_error_handler'	=>	true,# replace default php error handler
			'check_referer'			=>   false,# check referer for key and pass(good for ajax debugging)
			'die_on_error'			=>	true,# die if fatal error occurs(with class error handler)
			'debug_console'		=>	false,# only for Chrome,show messages in console(phpConsole needed)
			'allowed_ips'			=>	null,# restrict access with ip's
			'session_start'			=>	false,# start session for persistent debugging
			'show_interface'		=>	true	# show the interface(false to debug in console only)
		);
		/**
		* Check if ip has access
		* @param 	string|array	$allowedIps	the ip's that are allowed
		*/
		protected static function _checkAccess($allowedIps=null)
		{
			self::$_options['allowed_ips']=(!$allowedIps) ? self::$_options['allowed_ips'] : $allowedIps;
			if(self::$_options['allowed_ips'])
			{
				self::$_options['allowed_ips']=(is_array(self::$_options['allowed_ips'])) ? 
									self::$_options['allowed_ips'] : array(self::$_options['allowed_ips']);
				if(@in_array(@$_SERVER['REMOTE_ADDR'],self::$_options['allowed_ips'])){return true; }
				return false;
			}
			return true;
		}
		/**
		* Build the buffer
		* @param 	string	$type		log/sql
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement preceding the string
		* @param 	string 	$function		the name of the function
		* @param 	string 	$class		the name of the class
		*/
		protected static function _buildBuffer($type,$string,$statement=null,$function=null,$class=null)
		{
			if(@$_SESSION[self::$_options['url_key']])	# if debug is on
			{
				if(self::$_options['show_interface'])
				{
					$html_string=$string;
					$buffer=array("start_time"=>microtime(true),"type"=>$type);
					if($html_string=="{errorHandler}")	# reserve vars for the errorHandler
					{
						$buffer['errno']=$statement['errno'];
						$buffer['errstr']=$statement['errstr'];
						$buffer['errline']=$statement['errline'];
						$buffer['errfile']=$statement['errfile'];
					}
					else
					{
						$buffer['var_type']=gettype($html_string);
						$php_trace=self::_debugTrace();
						if(is_array($string) || is_object($html_string))
						{ 
							$html_string=@print_r($html_string,true);
							$html_string=self::_cleanBuffer($html_string);
							$id="ptc-debug-".rand();
							$html_string='<a href="#" onclick="showArray(\''.$id.'\',this);return false;">
							Show Data</a><pre style="display:none" id="'.$id.'">'.$html_string.'</pre>';
						}
						else
						{ 
							$string=@print_r($html_string,true); 
							$html_string=htmlentities($html_string);
						}
						$buffer['errno']="Message";
						$buffer['errstr']=($statement) ? $statement." ".$html_string : $html_string;
						$buffer['errline']=$php_trace['line'];
						$buffer['errfile']=$php_trace['file'];
						$buffer['function']=(!$function) ? @$php_trace['function'] : $function;
						$buffer['class']=(!$class) ? @$php_trace['class'] : $class;
					}
					@self::$_buffer[]=$buffer;
				}
				if(self::$_options['debug_console'] && $string!="{errorHandler}")
				{
					if(function_exists('debug'))
					{
						$console_string=$string;
						if(!@$buffer)
						{
							$php_trace=self::_debugTrace();
							$buffer=array("errline"=>$php_trace['line'],"errfile"=>$php_trace['file']); 
						}
						$console_string=(is_array($console_string) || is_object($console_string)) ? 
						@print_r($console_string,true) : $console_string;
						$statement=($statement) ? preg_replace("=<br */?>=i", "\n",$statement) : null;
						$debug_console=($statement) ? $statement." ".$console_string : $console_string;
						$console_type=$type."[".@end(@explode("/",$buffer['errfile'])).":";
						$console_type.=$buffer['errline']."]";
						debug($debug_console,$console_type);
					} 
				}
			}	
		}
		/**
		* Sort the buffer
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
		*/
		protected static function _debugTrace()
		{										
			$raw_trace=debug_backtrace();
			$this_methods=get_class_methods(__CLASS__);
			unset($this_methods[$key=array_search('debugLoader',$this_methods)]);
			unset($this_methods[$key=array_search('bufferSql',$this_methods)]);
			unset($this_methods[$key=array_search('bufferLog',$this_methods)]);
			$classes=array();
			foreach($raw_trace as $k=>$arrV)
			{	
				$classes[]=@$arrV['class'];
				if(@in_array(@$arrV['function'],self::$_excludeTrace)/*exclude some functions*/ || 
					@in_array(@$arrV['function'],$this_methods)/*exclude this class methods*/ || 
						 @preg_match("|__|",@$arrV['function'])/*exclude all magic methods*/)
				{
					unset($raw_trace[$k]); 
				}
			}
			$raw_trace=@array_values($raw_trace);
			$php_trace=@end($raw_trace);
			$classes=@array_filter($classes);
			$classes=@array_unique($classes);
			if(@$classes)	# try to find which class called the method
			{
				foreach($classes as $class_name)
				{
					if(@in_array(@$php_trace['function'],$class_methods=@get_class_methods($class_name)))
					{
						$php_trace['class']=$class_name;
					}
				}
			}		
			if(@$php_trace['class']=="PtcDebug" &&  @$php_trace['function']!="debugLoader")
			{
				$php_trace['class']=null;
				$php_trace['function']=null;
			}
			return $php_trace;
		}
		/**
		* Get fatal error on shutdown
		*/
		protected static function _lastError()
		{
			if($error=error_get_last()) 
			{
				$err_type=self::_msgType($error['type']);
				if($err_type=="Php Error")
				{				   
					$err=array("errno"=>$err_type,"errstr"=>$error['message'],
							"errfile"=>$error['file'],"errline"=>$error['line']);
					self::_buildBuffer("log","{errorHandler}",$err);
				}
			}
		}
		/**
		* Build the debug interface
		*/
		protected static function _buildInterface()
		{
			self::_sortBuffer();
			$interface=self::_includeJs();			# include js
			$interface.=self::_includeCss();		# include css
			$interface.='<div id="topRight">';
			$interface.='<div id="panelTitle" style="display:none;">&nbsp;</div>';
			$interface.=self::_buildMenu();		# top menu
			$interface.=self::_buildMsgPanel();		# msg
			$interface.=self::_buildVarsPanel();		# vars
			$interface.=self::_buildQueriesPanel();	# queries 
			$interface.=self::_buildW3cPanel();		# w3c
			$interface.=self::_buildTimerPanel();	# timer
			$interface.='<div id="statusBar" style="display:none;">&nbsp;</div>';
			$interface.='</div>';
			//$interface=self::_compressHtml($interface);	# make html lighter
			return $interface;
		}
		/**
		* Build the debug menu
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
		* Build the debug menu links
		* @param	string	$Id		the panel id
		* @param	string	$title	the panel title
		* @param	string	$text	the text for the link
		*/
		protected static function _menuLinks($Id,$title,$text)
		{
			$title=ucwords($title);
			$text=strtoupper($text);
			$return='<a href="#" onClick="showPanel(\''.$Id.'\',\''.$title.'\',this);return false;">';
			return $return.=$text.'</a>';
		}
		/**
		* Check message types
		* @param	string|numeric		php standards
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
					return "Php Error";
				break;
				default:return "Message";break;
			}
		}

		/**
		* Build the html log and sql tables
		* @param	string	$type	sql|log
		*/
		protected static function _buildHtmlTable($type)
		{
			$div=null;
			if(@self::$_buffer[$type])
			{
				$a=1;
				$div='<table border="1" style="width:100%" id="msgTable"><tr>';
				$div.='<th>#</th><th>category</th><th>file</th><th>line</th>
												<th>class</th><th>function</th>';
				if($type=="log"){ $div.='<th>type</th>'; }
				$div.='<th>time</th><th>message</th></tr>';
				foreach(self::$_buffer[$type] as $k=>$arr)
				{
					if(@$arr['function']){ $arr['function']=$arr['function']."()"; }
					$div.='<tr class="'.strtolower(@$arr['errno']).'"><td class="fixed"># '.$a.'</td>';
					//$div.='<td style="'.self:: _errorMsgStyle($arr['errno']).'">'.$arr['errno'].'</td>';
					$div.='<td class="fixed" style="color:green;">'.@$arr['errno'].'</td>';
					$div.='<td class="fixed"><a href="'.$arr['errfile'].'" onclick="return false;">
					'.@end(@explode("/",$arr['errfile'])).'</a></td>';
					$div.='<td class="fixed">'.@$arr['errline'].'</td>';
					$div.='<td class="fixed" style="color:purple;">'.@$arr['class'].'</td>';
					$div.='<td class="fixed">'.@$arr['function'].'</td>';
					if($type=="log"){ $div.='<td class="fixed">'.@$arr['var_type'].'</td>'; }
					$div.=(@$arr['time']) ? '<td class="fixed">'.$arr['time'].'</td>' : 
												'<td class="fixed">&nbsp;</td>';
					$div.="<td>".@$arr['errstr']."</td></tr>";
					$a++;
				}
				$div.="</table><br>";
			}
			else{ $div='<span class="vars">no messages</span>'; }
			return $div;
		}
		/**
		* Build the log panel
		*/
		protected static function _buildMsgPanel()
		{
			
			$div='<div id="msgPanel" style="display:none;" class="innerTable">';
			$div.=(self::$_buffer) ? self::_buildHtmlTable("log") : '<span class="vars">no messages</span>';
			return $div.='</div>';
		}
		/**
		* Build the sql panel
		*/		
		protected static function _buildQueriesPanel()
		{
			$div='<div id="sqlPanel" style="display:none;" class="innerTable">';
			$div.=(self::$_buffer) ? self::_buildHtmlTable("sql") : '<span class="vars">no messages</span>';
			return $div.='</div>';
		}
		/**
		* Build the timer panel
		*/
		protected static function _buildTimerPanel()
		{
			$time=(self::$_endTime-self::$_startTime);
			$div='<div id="timerPanel" style="display:none;" class="innerTable">';
			$div.='Global Execution Seconds: '.round($time,3)." Milliseconds: ".round($time*1000,3);    
			$div.='</div>';
			return   $div=self::_compressHtml($div);
		}
		/**
		* Build the vars panel
		*/
		protected static function _buildVarsPanel()
		{
			$div='<div id="varsPanel" style="display:none;" class="innerTable">';
			foreach(self::$_options as $k=>$v)	# format options for to be read in debug msg panel
			{
				if(is_bool($v)){ $v=($v) ? "true": "false"; } 	# turn bools into strings
				if(is_null($v)){ $v="null"; }				# turn nulls into strings
				$buffer_options[$k]=(is_array($v)) ? "array(".implode(",",$v).")" : $v;
			}
			$div.=self::_buildInnerVars('options','Configuration',$buffer_options);
			$div.='<a href="#" onClick="showVars(\'files\',this)">Files';
			$included_files=@get_included_files();
			$div.='<span class="count_vars">('.@sizeof($included_files).')</span>&dArr;</a><br>';
			$div.='<div id="files" class="vars" style="display:none;line-height:25px;">';
			if(!@empty($included_files))
			{
				foreach($included_files as $filename)
				{ 
					$div.=$filename;
					if($_SERVER['SCRIPT_FILENAME']==$filename){ $div.=" <b>&laquo; Main File</b>"; }
					$div.="<br>\n"; 
				}
			}
			else{ $div.='<span class="vars">no vars</span>'; }
			$div.='</div>'; 
			$div.=self::_buildInnerVars('constants','Constants',$constants=@get_defined_constants(true));
			$global_vars=$GLOBALS;
			unset($global_vars['GLOBALS']);
			ksort($global_vars);
			$div.=self::_buildInnerVars('globals','Globals',$global_vars);
			$php_info=self::_buildPhpInfo();
			$div.=self::_buildInnerVars('phpInfo','Php',$php_info);
			$div.='</div>';
			return  $div;
		}
		/**
		* Build the inner vars div
		* @param	string	$panelId		the id of the panel to show/hide
		* @param	string	$linkTitle		the title orf the link
		* @param	string	$array		array of parameters
		*/
		protected static function _buildInnerVars($panelId,$linkTitle,$array)
		{
			$div='<a href="#" onClick="showVars(\''.$panelId.'\',this)">'.$linkTitle.'';
			$div.='<span class="count_vars">('.@sizeof($array).')</span>&dArr;</a><br>';
			$div.='<div id="'.$panelId.'" class="vars" style="display:none;line-height:25px;">';
			$div.="<pre>".self::_cleanBuffer($final=@print_r($array,true))."</pre>";
			$div.='</div>';
			return $div;
		}
		/**
		* Build the W3C panel
		*/
		protected static  function _buildW3cPanel()
		{
			$div='<div id="w3cPanel" style="display:none;" class="innerTable">';
			//$div.='<p>Click on the WC3 link to verify the validation or to check the errors</p>';
			//$div.='<p><a href="http://validator.w3.org/check?uri=referer" target="_blank">';
			//$div.='<img src="http://www.w3.org/Icons/WWW/w3c_home_nb" alt="W3C Validator"></a></p>';
			$div.='<p>Copy paste the source here ';
			$div.='<a href="http://validator.w3.org/#validate_by_input" target="_blank">';
			$div.='http://validator.w3.org/#validate_by_input</a></p>';    
			$div.='</div>';
			return  $div=self::_compressHtml($div);
		}
		/**
		* Format phpinfo() function
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
		* Include the css for the debug interface
		*/
		protected static function _includeCss()
		{
			return self::_compressHtml(
				'<style type="text/css">
					#topRight{font-family:Arial,sant-serif;position:fixed;top:0px;right:3px;
					background:#eee;color:#333;}ul.tabs li{background-color:#ddd;
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
					#msgTable{padding:0;margin:0;border:1px solid #999;font-family:Arial;
					font-size:11px;text-align:left;}#msgTable th{margin:0;border:0;
					padding:3px 5px;vertical-align:top;background-color:#999;color:#EEE;
					white-space:nowrap;}#msgTable td{margin:0;border:0;
					padding:3px 3px 3px 3px;vertical-align:top;}
					#msgTable tr.notice td{background-color:#DDD;color:#333;}
					#msgTable tr.message td{background-color:#DDD;color:#333;}
					#msgTable tr.warning td{background-color:yellow;color:#333;}
					#msgTable tr.error td{background-color:orange;color:#333;}
					.innerTable{z-index:10000;position:relative;background:#eee;
					height:300px;padding:30px 10px 0 10px;overflow:auto;clear:both;}
					.innerTable a{color:dodgerBlue;font-size:bold;text-decoration:none}
					.innerTable p{font-size:12px;color:#333;text-align:left;line-height:12px;}
					.innerPanel h1{font-size:16px;font-weight:bold;margin-bottom:20px;
					padding:0;border:0px;background-color:#EEE;}
					#panelTitle{height:20px;float:left;z-index:1000000;position:relative;}
					#panelTitle h1{font-size:16px;font-weight:bold;margin-bottom:20px;
					margin-left:10px;padding:0 0 10px 0;border:0px;background-color:#EEE;
					color:#669;margin-top:5px;;height:20px;}code,pre,.vars{font-family:"andale mono",
					"monotype.com","courier new",courier,monospace;font-size:11px;line-height:12px;}
					#varsPanel a{text-decoration:none;font-size:14px;font-weight:bold;color:#669;
					line-height:25px;}.count_vars{font-size:11px;color:purple;padding:0;margin:0;}
					.fixed{width:1%;white-space:nowrap;}.fixed1{width:5%;white-space:nowrap;}
					#statusBar{height:2px;background-color:#999;}
				</style>');
		}
		/**
		* Include the javascript for the debug interface
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
					function showArray(elId,linkEl)
					{
						if(document.getElementById(elId).style.display=="none")
						{ 	
							linkEl.innerHTML="Hide Data";
							document.getElementById(elId).style.display=\'\'; 
						}
						else
						{ 
							linkEl.innerHTML="Show Data";
							document.getElementById(elId).style.display=\'none\'; 
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
					}*/
				</script>');
		}
		/**
		* Compress the html before render
		* @var	string	$html	some html code
		*/
		protected static function _compressHtml($html)
		{
			$html=preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',$html);# remove comments
			$html=str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),'',$html);# tabs,newlines,etc.
			return $html;
		}
		/**
		* Format phpinfo() into an array
		*/
		protected static function _phpInfoArray()
		{
			ob_start();
			@phpinfo();
			$info_arr=array();
			$info_lines=explode("\n",strip_tags(ob_get_clean(),"<tr><td><h2>"));
			$cat="General";
			$regEx="~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~";
			foreach($info_lines as $line)
			{
				preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat=$title[1] : null;	# new cat?
				if(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~",$line,$val))
				{
				    $info_arr[$cat][$val[1]]=$val[2];
				}
				else if(preg_match($regEx,$line,$val))
				{ 
					$info_arr[$cat][$val[1]]=array("local"=>$val[2],"master"=>$val[3]); 
				}
			}
			return $info_arr;
		}
		/**
		* array with all options
		* @var	array
		*/
		private static $_options=array();
		/**
		* the debug buffer
		* @var	array
		*/	
		private static $_buffer=array();
		/**
		* application start time
		* @var	time
		* @see stopTimer()
		*/
		private static $_startTime=null;		
		/**
		* application end time
		* @var	time
		* @see stopTimer()
		*/
		private static $_endTime=null;
		/**
		* Exclude functions in this array from the backtrace
		* @var	array
		*/
		private static $_excludeTrace=array("require","require_once","include");
		/**
		* Remove html entities from the buffer
		* @param	string	$var		some string
		*/		
		private static function _cleanBuffer($var){ return $var=htmlentities($var); }
	}
?>