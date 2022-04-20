<?php

	/**
	* AUTOLOADER NAMESPACED INSIDE SUB FOLDER FILE EXAMPLE CLASS FOR PTCHM-EX.PHP FILE
	*/
	
	namespace nsTest\hmNsDeep;
	
	use phptoolcase\HandyMan;
	
	class HmTestNsDeep		
	{
		public function __construct( )
		{
			$dir = HandyMan::getDirs( 'ns' );
			echo 'Class "' . __CLASS__ . '" autoloaded, namespace "' . __NAMESPACE__. 
					'" inside subfolder example: <br>&nbsp;&nbsp;' . $dir[ 'nsTest' ] . '/hmNsDeep - ' . basename( __FILE__ ) . '<br><br>';
		}
	}