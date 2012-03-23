<?php
	/**
	* MYSQL QUERY HELPER CLASS 
	* PHP version 5
	* @category 	Framework
	* @package  	PhpToolCase
	* @version	0.7
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	class PtcDb
	{
		/**
		* Mysql mode(MYSQL_BOTH,MYSQL_ASSOC,MYSQL_NUM)
		* @var 	string
		* @tutorial	PtcDb.cls#mySqlFlag
		*/
		public $mySqlFlag=MYSQL_ASSOC;
		/**
		* Store query results in array
		* @var 	array
		* @tutorial	PtcDb.cls#references
		*/
		public $queryResults=array();
		/**
		* Call class methods without initializing the class
		*/
		public static function selfInstance()	
		{
			$class=__CLASS__;
			return new $class();
		}
		/**
		* Connect to database
		* @param	string	$dbHost		address for mysql server
		* @param	string	$dbUser		user for mysql server
		* @param	string	$dbPass		password for mysql server
		* @param	string	$dbName		database name
		* @param	string	$dbCharset	charset to use
		* @tutorial	PtcDb.cls#getting_started.dbConnect
		*/
		public function dbConnect($dbHost,$dbUser,$dbPass,$dbName=NULL,$dbCharset=NULL)
		{
			if(class_exists('PtcDebug',true))	# debug
			{ 
				PtcDebug::bufferSql($dbUser."@".$dbHost."[".$dbName."]",
								"connecting to ",__FUNCTION__,__CLASS__);
			}
			$this->dbLink=@mysql_connect($dbHost,$dbUser,$dbPass) or 
				trigger_error('Reference '.$ref.' Mysql Error: '.mysql_error(),E_USER_ERROR);
			if(class_exists('PtcDebug',true)){ PtcDebug::stopTimer(); }
			if($dbName)
			{ 
				@mysql_select_db($dbName) or 
					trigger_error('Reference '.$ref.' Mysql Error: '.mysql_error(),E_USER_ERROR);
			}
			if($dbCharset)
			{ 
				@mysql_query("SET NAMES '".$dbCharset."'",$this->dbLink) or 
					trigger_error('Reference '.$ref.' Mysql Error: '.mysql_error(),E_USER_ERROR);
			}
		}
		/**
		* Execute any select statement
		* @param	string	$sql	sql query to be returned
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @tutorial	PtcDb.cls#complex_queries.sqlToArray		
		* @return	returns a 2 dimensions array with values or null if query is empty
		*/
		public function sqlToArray($sql,$ref=0)
		{
			if(class_exists('PtcDebug',true))	# debug
			{ 
				$php_trace=$this->_tracePhp();
				PtcDebug::bufferSql('ref '.$ref.' '.$sql,'',@$php_trace['function'],__CLASS__); 
			}
			$this->queryResults[$ref]=@mysql_query($sql) or 
				trigger_error('Reference '.$ref.' Mysql Error: '.mysql_error(),E_USER_ERROR);
			if(class_exists('PtcDebug',true)){ PtcDebug::stopTimer(); }# debug
			while($row=@mysql_fetch_array($this->queryResults[$ref],$this->mySqlFlag)){ $result[]=$row; } 
			return (@$result) ? $result : null;
		}
		/**
		* Execute sql statement and return result
		* @param	string	$sql	sql query to be returned
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @tutorial	PtcDb.cls#complex_queries.executeSql
		* @return	returns the sql reference.
		*/
		public function executeSql($sql,$ref=0)
		{
			if(class_exists('PtcDebug',true))	# debug
			{ 
				$php_trace=$this->_tracePhp();
				PtcDebug::bufferSql('ref '.$ref.' '.$sql,'',@$php_trace['function'],__CLASS__); 
			}
			$this->queryResults[$ref]=@mysql_query($sql) or
				trigger_error('Reference '.$ref.' Mysql Error: '.mysql_error(),E_USER_ERROR);
			if(class_exists('PtcDebug',true)){ PtcDebug::stopTimer(); }# debug
			return  $this->queryResults[$ref];
		}
		/**
		* Read 1 row from given table
		* @param	string		$table	mysql table
		* @param	array|string	$fields	query fields
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @tutorial	PtcDb.cls#select_data.readRow
		* @return	returns an array  with values or null if query is empty.
		*/
		public function readRow($table,$fields,$ref=0)
		{
			$sql="SELECT * FROM ".$table.$this->_queryFields($fields);
			$result=$this->sqlToArray($sql,$ref);
			return is_array($result) ?  $result[0] : $result;
		}
		/**
		* Read records from given table
		* @param	string		$table	mysql table
		* @param	array|string	$fields	query fields
		* @param	string		$order 	order records
		* @param	string		$limit	limit number of records
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @tutorial	PtcDb.cls#select_data.readTable
		* @return	returns a multidimensional array or null if query is empty
		*/
		public function readTable($table,$fields=null,$order=null,$limit=null,$ref=0)
		{	
			$sql="SELECT * FROM ".$table.$this->_queryFields($fields)." ".trim($order)." ".trim($limit);
			return $this->sqlToArray($sql,$ref);
		}
		/**
		* Insert record from given table
		* @param	string	$table	mysql table
		* @param	array	$array	query fields
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @tutorial	PtcDb.cls#manipulating data.insertRow
		* @return	returns the sql reference.
		*/
		public function insertRow($table,$array,$ref=0)
		{
			$fields="";
			$values="";
			foreach($array as $k => $v) 
			{
				$fields.='`'.$k.'`,';
				$values.="'".$this->_cleanQuery($v)."',";
			}
			$fields=substr($fields,0,strlen($fields)-1);
			$values=substr($values,0,strlen($values)-1);
			$sql="INSERT INTO ".$table." (".$fields.") VALUES (".$values.")";
			return $this->executeSql($sql,$ref);
		}
		/**
		* Update 1 record in given table
		* @param	string	$table		mysql table
		* @param	array	$array		array of values to update
		* @param	int		$recordId		the record id to be updated
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @tutorial	PtcDb.cls#manipulating data.updateRow
		* @return	returns the sql reference.
		*/
		public function updateRow($table,$array,$recordId,$ref=0)
		{
			$values="";
			foreach($array as $k => $v) { $values.="`".$k."` = '".$this->_cleanQuery($v)."',"; }
			$values=substr($values,0,strlen($values)-1);
			$sql="UPDATE ".$table." SET ".$values." WHERE `id` = ".$recordId;
			return $this->executeSql($sql,$ref);
		}
		/**
		* Get last inerted id
		* @tutorial	PtcDb.cls#manipulating data.lastId
		* @return	returns last inserted id
		*/
		public function lastId()
		{ 
			$last_id=@mysql_insert_id() or trigger_error('Mysql Error: '.mysql_error(),E_USER_ERROR); 
			if(class_exists('PtcDebug',true))	# debug
			{
				PtcDebug::bufferSql($last_id,"sql last inserted id ",__FUNCTION__,__CLASS__); 
			}
			return $last_id; 
		} 
		/**
		* Delete row from given table
		* @param	string	$table		mysql table
		* @param	int		$recordId		the record id to be deleted
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @return	returns the sql reference.
		* @tutorial	PtcDb.cls#manipulating data.deleteRow
		*/
		public function deleteRow($table,$recordId,$ref=0)
		{	
			$sql="DELETE FROM ".$table." WHERE `id` = '".$recordId."'";
			return $this->executeSql($sql,$ref);
		}
		/**
		* Retrive value from given table based on 1 field
		* @param	string		$table		mysql table
		* @param	string		$key		table field name
		* @param	string		$value		the value to look for
		* @param	string		$return		the field to return
		* @tutorial	PtcDb.cls#select_data.goFast
		* @return	returns the value for the specified field,or null if query was empty
		*/
		public function goFast($table,$key,$value,$return="id")
		{
			$field[$key]=$value;
			$result=$this->readRow($table,$field);
			return is_array($result) ? @$result[$return] : null;
		}
		/**
		* Count rows of select query(based on reference)
		* @param	string	$ref	gives a reference to the resource. See {@tutorial PtcDb.cls#references}
		* @tutorial	PtcDb.cls#select_data.countRows
		* @return	returns number rows from select statement
		**/
		public function countRows($ref=0)
		{
			if(class_exists('PtcDebug',true))	# debug
			{ 
				PtcDebug::bufferSql('','',__FUNCTION__,__CLASS__);
			}
			$result=mysql_num_rows($this->queryResults[$ref]); 
			if(class_exists('PtcDebug',true))	# debug
			{ 
				PtcDebug::stopTimer(); 
				PtcDebug::addToBuffer('ref '.$ref.' number of rows: '.$result);	# attach result to buffer
			}
			return $result;
		}
		/**
		* Close link to DB
		* @param	string	$dbLink	link resource
		* @tutorial	PtcDb.cls#getting_started.dbClose
		**/
		public function dbClose($dbLink=null)
		{
			if(!$dbLink){ $dbLink=$this->dbLink; }
			$sql_ref=@mysql_query("SELECT DATABASE()",$dbLink) or 
				trigger_error('Reference '.$ref.' Mysql Error: '.mysql_error(),E_USER_ERROR);
			$connection=@mysql_result($sql_ref,0) or
				trigger_error('Reference '.$ref.' Mysql Error: '.mysql_error(),E_USER_ERROR);
			if(class_exists('PtcDebug',true))	# debug
			{ 
				PtcDebug::bufferSql($connection,"closing connection to ",__FUNCTION__,__CLASS__);
			}
			@mysql_close($dbLink) or trigger_error('Mysql Error: '.mysql_error(),E_USER_ERROR);
			if(class_exists('PtcDebug',true)){ PtcDebug::stopTimer(); }# debug
		}
		/**
		* Protect against sql injection
		* @param	string	$string	clean values before sql query(prevent sql injection)
		*/
		protected function _cleanQuery($string)
		{
			if(get_magic_quotes_gpc()){ $string=stripslashes($string); }# prevent duplicate backslashes
			if(phpversion() >='4.3.0'){ $string=mysql_real_escape_string($string); }
			else{ $string=mysql_escape_string($string); }
			return $string;
		}
		/**
		* Fields in the select statement WHERE clause
		* @param	array|string	$fields	 query fields
		*/
		protected function _queryFields($fields)
		{
			$i=1;
			$query_fields=null;
			if(is_array($fields))
			{
				foreach($fields as $k => $v) 
				{
					if($i<sizeof($fields)){ $query_fields.="`".$k."` = '".$this->_cleanQuery($v)."' AND "; }
					else{ $query_fields.="`".$k."` = '".$this->_cleanQuery($v)."'"; }
					$i++;
				}
			}
			else if($fields)
			{ 
				if(preg_match("/:/",$fields))
				{ 
					$fields=explode(":",$fields); 
					$query_fields="`".$fields[0]."` = '".$this->_cleanQuery($fields[1])."'";
				}
				else{ $query_fields="`id` = '".$this->_cleanQuery($fields)."'"; }
			}
			return ($query_fields) ? " WHERE ".$query_fields :  "";
		}
		/*
		* Trace php for debugging
		*/
		protected function _tracePhp()
		{
			$raw_trace=@debug_backtrace();
			//PtcDebug::bufferLog($raw_trace);
			return $php_trace=@end($raw_trace);
		}
 	}
?>