<?php

	/**
	* AUTOLOADER EXAMPLE NAMESPACED CLASS ADDED WITH THE ADDFILES( ) METHOD FOR PTCHM-EX.PHP FILE
	*/
	namespace ns;
	
	use phptoolcase\HandyMan;
	
	class HmTestClassFile
	{
		public function __construct( )
		{
			$dir = HandyMan::getDirs( 'files' );
			echo 'Class "' . __CLASS__ . '" autoloaded, namespace "' . __NAMESPACE__ . '":<br>&nbsp;&nbsp;' . $dir[ __CLASS__ ]. '<br><br>';

		}
	}