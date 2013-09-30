<?php

	/**
	* AUTOLOADER EXAMPLE NAMESPACED CLASS ADDED WITH THE ADDFILES( ) METHOD FOR PTCHM-EX.PHP FILE
	*/
	namespace ns;
	
	class HmTestClassFile
	{
		public function __construct( )
		{
			$dir = \PtcHandyMan::getDirs( 'files' );
			echo 'Class "' . __CLASS__ . '" autoloaded, namespace "' . __NAMESPACE__ . '":<br>&nbsp;&nbsp;' . $dir[ __CLASS__ ]. '<br><br>';

		}
	}