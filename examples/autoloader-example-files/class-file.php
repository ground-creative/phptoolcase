<?php

	/**
	* AUTOLOADER EXAMPLE CLASS ADDED WITH THE ADDFILES( ) METHOD FOR PTCHM-EX.PHP FILE
	*/
	
	class HmTestClassFile
	{
		public function __construct( )
		{
			$dir = PtcHandyMan::getDirs( 'files' );
			echo 'Class "' . __CLASS__ . '" autoloaded:<br>&nbsp;&nbsp;' . $dir[ __CLASS__ ] . '<br><br>';

		}
	}