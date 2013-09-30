<?php

	/**
	* AUTOLOADER LOWERCASE FILE EXAMPLE CLASS FOR PTCHM-EX.PHP FILE
	*/
	
	class HmTestClassLs
	{
		public function __construct( )
		{
			$dir = \PtcHandyMan::getDirs( 'directories' );
			echo 'Class "' . __CLASS__ . '" autoloaded, example with file name lowercase which is always the second guess for the autoloader: <br>&nbsp;&nbsp;' . 
																							$dir[ 1 ] . ' - ' . basename( __FILE__ ) . '<br><br>';
		}
	}