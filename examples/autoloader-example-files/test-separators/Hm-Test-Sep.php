<?php

	/**
	* AUTOLOADER EXAMPLE CLASS FOR PTCHM-EX.PHP FILE
	*/
	
	class Hm_Test_Sep
	{
		public function __construct( )
		{
			$dir = PtcHandyMan::getDirs( 'directories' );
			echo 'Class "' . __CLASS__ . '" autoloaded from directory by replacing "_" with "-" separator:<br>&nbsp;&nbsp;' . $dir[2] . ' - ' . basename( __FILE__ ) . '<br><br>';
		}
	}