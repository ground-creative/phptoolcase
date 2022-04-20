<?php

	/**
	* AUTOLOADER EXAMPLE CLASS FOR PTCHM-EX.PHP FILE
	*/
	
	use phptoolcase\HandyMan;
	
	class Hm_Test_Sep1
	{
		public function __construct( )
		{
			$dir = HandyMan::getDirs( 'directories' );
			echo 'Class "' . __CLASS__ . '" autoloaded lowercase file name, from directory by replacing "_" with "-" separator, using naming convention "class.{CLASS}":<br>&nbsp;&nbsp;' . 
																												$dir[2] . ' - ' . basename( __FILE__ ) . '<br><br>';
		}
	}