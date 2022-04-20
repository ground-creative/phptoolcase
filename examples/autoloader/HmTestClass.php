<?php

	/**
	* AUTOLOADER EXAMPLE CLASS FOR PTCHM-EX.PHP FILE
	*/
	
	use phptoolcase\HandyMan;
	
	class HmTestClass
	{
		public function __construct( )
		{
			$dir = HandyMan::getDirs( 'directories' );
			echo 'Class "' . __CLASS__ . '" autoloaded from directory:<br>&nbsp;&nbsp;' . $dir[1] . ' - ' . basename( __FILE__ ) . '<br><br>';
		}
	}