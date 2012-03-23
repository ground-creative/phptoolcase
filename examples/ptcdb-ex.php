<?
	#### db details needed for the example to work ##########
	$db['host']="localhost";				# mysql host
	$db['user']="mysql user";			# mysql user
	$db['pass']="mysql pass";			# mysql pass
	$db['database']="db name";			# mysql database name
	#########################################

	# initialize the class
	require_once('../PtcDb.php');
	$ptc=new PtcDb();			
	
	# connect to db
	$ptc->dbConnect($db['host'],$db['user'],$db['pass'],$db['database']);
	
	# drop example table
	$ptc->executeSql("DROP TABLE IF EXISTS `test_table`");
	
	# create example table
	$ptc->executeSql("CREATE TABLE `test_table` 
	(
		`id` int NOT NULL AUTO_INCREMENT, 
		PRIMARY KEY(`id`),`field1` varchar(15),`field2` varchar(15)
	)");
	
	
	/* INSERTING ROWS */

	# single row insert
	$ins['field1']="somevalue1";
	$ins['field2']="somevalue2";
	$ptc->insertRow("test_table",$ins);
	# multiple rows insert
	$arr1=array("field1"=>"somevalue3","field2"=>"somevalue5");
	$arr2=array("field1"=>"somevalue4","field2"=>"somevalue6");
	$arr3=array($arr1,$arr2);	# 2 dimensions array
	foreach($arr3 as $ins){ $ptc->insertRow("test_table",$ins); }
	# get last inserted id
	echo "<b>last inserted id is:</b> ".$ptc->lastId()."<br><br>";
	
	
	/* READ MULTIPLE RECORDS FROM ANY GIVEN TABLE */
	
	# all rows
	$data=$ptc->readTable("test_table");							
	print "<b>readTable() result(all records)</b>";
	print "<pre>".print_r($data,true)."</pre>";
	# single value search
	$value="somevalue1";
	$data=$ptc->readTable("test_table","field1:".$value);
	print "<b>readTable() result(single value search)</b>";
	print "<pre>".print_r($data,true)."</pre>";
	# array of values search
	$fields=array("field1"=>"somevalue1","field2"=>"somevalue2");
	$data=$ptc->readTable("test_table",$fields);
	print "<b>readTable() result(array of values)</b>";
	print "<pre>".print_r($data,true)."</pre>";
	# order and limit
	$order="ORDER BY `id` ASC";
	$limit="LIMIT 2";
	$data=$ptc->readTable("test_table","",$order,$limit);							
	print "<b>readTable() result(with order and limit)</b>";
	print "<pre>".print_r($data,true)."</pre>";
	# count rows for last select query
	echo "<b>number of rows (select statement):</b> ".$ptc->countRows()."<br><br>";	
	
	
	/* READ 1 ROW FROM ANY GIVEN TABLE BASED ON FIELD QUERY */
	
	# query based on record id
	$field_id=1;
	$data=$ptc->readRow("test_table",$field_id);	
	print "<b>readRow() result(query based on record id)</b>";
	print "<pre>".print_r($data,true)."</pre>";	
	# single value search
	$value="somevalue1";
	$data=$ptc->readRow("test_table","field1:".$value);
	print "<b>readRow() result(single value search)</b>";
	print "<pre>".print_r($data,true)."</pre>";	
	# array of values
	$fields=array("field1"=>"somevalue1","field2"=>"somevalue2");
	$data=$ptc->readRow("test_table",$fields);
	print "<b>readRow() result(array of values)</b><pre>".print_r($data,true)."</pre>";
	
	
	/* RETRIEVE SINGLE FIELD VALUE FROM 1 RECORD */
	
	# returns 1 value instead of an array like readRow()
	$value="somevalue1";
	$data=$ptc->goFast("test_table","field1",$value,"field2");
	echo "<b>goFast() result:</b> ".$data."<br><br>";
	
	
	/* UPDATING ROWS */

	# single row update
	$up['field1']="somevalue5";
	$up['field2']="somevalue6";
	$record_id=1;	# the record id
	$ptc->updateRow("test_table",$up,$record_id);
	# multiple rows update
	$arr1=array("field1"=>"somevalue7","id"=>1);
	$arr2=array("field1"=>"somevalue8","id"=>2);
	$arr3=array($arr1,$arr2);	# 2 dimensions array
	foreach($arr3 as $up){ $ptc->updateRow("test_table",$up,$up['id']); }
	
	
	/* COMPLEX SELECT */
	
	# just a select statement
	$sql="SELECT  `field1` FROM test_table WHERE `field2` != 'somevalue'";
	$data=$ptc->sqlToArray($sql);	# return a 2 dimensions array
	print "<b>sqlToArray() result(complex select)</b><pre>".print_r($data,true)."</pre>";
	
	
	/* EXECUTE ANY SQL STATEMENT */
	
	# just an update query
	$sql="UPDATE test_table SET `field1` = 'somevalue9' WHERE `field2` = 'somevalue'";
	$ptc->executeSql($sql);
	
	
	/* USING REFERENCES */
	
	# just a select query
	$data=$ptc->readTable("test_table",'','','',"testReference");							
	print "<b>readTable() result adding reference <i>\"testReference\"</i></b>";
	print "<pre>".print_r($data,true)."</pre>";
	# DO MORE QUERIES HERE
	# count rows from a select query with reference
	echo "<b>countRows (select statement) with reference:</b> ";
	echo $ptc->countRows("testReference")."<br>";	
	# show field id of first record with reference
	echo "<b>show field1 of second record with reference(mysql_resultt):</b> ";
	echo mysql_result($ptc->queryResults["testReference"],1,1)."<br>";
	# show number of fields
	$num_fields=mysql_num_fields($ptc->queryResults["testReference"]);
	echo "<b>number of fields in previous query <i>\"testReference\"</i>:</b> ";
	echo  $num_fields."<br>";
	# get fields information
	echo "<b>dump fields information (mysql_fetch_field) <i>\"testReference\"</i>:</b> ";
	for($a=0;$a<$num_fields;$a++)
	{
		echo "<pre>";
		var_dump(mysql_fetch_field($ptc->queryResults["testReference"],$a));
		echo "</pre>";	
	}
	
	
	/* DELETING ROWS */
	
	# single row
	$record_id=1;	# the record id
	$ptc->deleteRow("test_table",$record_id);
	# multiple rows
	$arr3=array("2","3");	# array of id's
	foreach($arr3 as $k=>$v){ $ptc->deleteRow("test_table",$v); }


	# close connection
	$ptc->dbClose();
?>