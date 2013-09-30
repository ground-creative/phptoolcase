<?php

	/**
	* AUTOLOADER NAMESPACED FILE EXAMPLE CLASS FOR PTCHM-EX.PHP FILE
	*/
	
	namespace nsTest;
	
	class HmTestNs		
	{
		public function __construct( )
		{
			$dir = \PtcHandyMan::getDirs( 'ns' );
			echo 'Class "' . __CLASS__ . '" autoloaded, namespace "' . __NAMESPACE__ . '" example: <br>&nbsp;&nbsp;' . 
															$dir[ __NAMESPACE__ ] . ' - ' . basename( __FILE__ ) . '<br><br>';
		}
	}