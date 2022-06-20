<?php

	class UserController
	{
		public function get_index( )
		{
			print "executing restful controller index page";
		}

		public function get_user( $id )
		{
			print "executing restful controller get request with param " . $id;
		}
		
		public function post_user( $id )
		{
			print "executing restful controller post request with param " . $id;
		}
		
		public function delete_user( $id )
		{
			print "executing restful controller delete request with param " . $id;
		}
		
		public function put_user( $id )
		{
			print "executing restful controller put request with param " . $id;
		}
		
		public function get_optional_param( $id = null )
		{
			print "executing restful controller get request with optonal param " . @$id;
		}
		
		/* ADDING A PATTERN TO THE $id ARGUMENT */
		protected static $_where = array( 'id' => '[0-9]' );
		
		/* ADDING A SPECIFIC PROTOCOL TO THE ROUTES */
		//protected static $_protocol = array( 'post_user' => 'https' );
		
		/* ADDING FILTERS TO THE ROUTES */
		//protected static $_before = array( 'get_user' => 'some.filter' );
		//protected static $_after = array( 'get_user' => 'some.filter' , 'post_user' => 'some.filter' );
	}