<?php

	/* 
	* RESTFUL CONTROLLER EXAMPLE FILE FOR PTCROUTER CLASS 
	*/

	class UserController
	{
		/* THE INDEX PAGE */
		public function get_index( )
		{
			print "excuting restful controller index page ";
		}
		
		/* THE USER PAGE WITH AND ID /user/{id} */
		public function get_user( $id )
		{
			print "excuting restful controller user page with id as param " . $id;
		}
		
		/* POST SOME DATA TO UPDATE AN ACCOUNT /user/{id} */
		public function post_user( $id )
		{
			print "excuting restful controller user post data to update account with id as param " . $id;
		}
		
		/* ADDING A PATTERN TO THE $id ARGUMENT
		//protected static $_where = array( 'id' => '[0-9]' );
		
		/* ADDING A SPECIFIC PROTOCOL TO THE ROUTES
		//protected static $_protocol = array( 'post_user' => 'https' );
		
		/* ADDING FILTERS TO THE ROUTES */
		//protected static $_before = array( 'get_user' => 'some.filter' );
		//protected static $_after = array( 'get_user' => 'some.filter' , 'post_user' => 'some.filter' );
	}