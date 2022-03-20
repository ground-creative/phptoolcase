<?php

	/**
	* PHPTOOLCASE AUTHENTICATION CLASS
	* PHP version 5.4+
	* @category 	Library
	* @version	1.1.7
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	class PtcAuth 
	{
		/**
		*
		*/
		public static function token( $string = null )
		{
			$string = ( $string ) ? $string : 
				$_SERVER[ 'HTTP_USER_AGENT' ] . static::random( );
			return static::_hashPassword( $string );
		}
		/**
		*
		*/
		public static function once( $userID , $expires = false )
		{
			$columns = static::$_tableColumns;
			if ( '*' === $userID )
			{
				$rand = mt_rand( 10000000 , 99999999 );
				return static::_processLogin( $rand , 'none' , $expires );
			}
			if ( !$user = static::user( $userID ) ){ return false; }
			return static::_processLogin( $user->{ $columns[ 'unique_key' ] } , 'none' , $expires );
		}
		/**
		*
		*/
		public static function password( $param , $password )
		{
			$result = true;
			$set = null;
			static::_fireEvent( 'updating' , array( 'setPassword' , &$param ) );
			static::$_guard = false;
			if ( !$user = static::user( $param ) ){ $result = false; }
			if ( $result )
			{
				$password = static::_hashPassword( 
					$user->{ static::$_tableColumns[ 'salt' ] } . $password );
				$data = static::_track( 
					array( static::$_tableColumns[ 'password' ] => $password ) );
				$set = static::_connection( static::$_options[ 'users_table' ] )
					->update( $data , $user->{ static::$_tableColumns[ 'unique_key' ] } )
					->run( );
			}
			static::_fireEvent( 'updated' , array( 'setPassword' , $param , &$result ) );
			return $result;
		}
		/**
		*
		*/
		public static function match( $param , $password )
		{
			static::$_guard = false;
			if ( !$user = static::user( $param ) ){ return false; }
			$password = static::_hashPassword( 
				$user->{ static::$_tableColumns[ 'salt' ] } . $password );
			if ( $password === 
				$user->{ static::$_tableColumns[ 'password' ] } ){ return true; } // match
			return false;
		}
		/**
		*
		*/
		public static function setActive( $param , $code = 1 )
		{
			static::_fireEvent( 'updating' , array( 'setActive' , &$param , &$code ) );
			$data = array( static::$_tableColumns[ 'active' ] => $code );
			$result = static::_connection( static::$_options[ 'users_table' ] )
					->where( static::_checkColumn( $param ) , '=' , $param )
					->update( static::_track( $data ) )
					->run( );
			static::_fireEvent( 'updated' , array( 'setActive' , $param , $code , &$result ) );
			return $result;
		}
		/**
		*
		*/
		public static function setVerified( $param , $code = 1 )
		{
			static::_fireEvent( 'updating' , array( 'setVerified' , &$param , &$code ) );
			$data = array( static::$_tableColumns[ 'verified' ] => $code );
			$result = static::_connection( static::$_options[ 'users_table' ] )
					->where( static::_checkColumn( $param ) , '=' , $param )
					->update( static::_track( $data ) )
					->run( );
			static::_fireEvent( 'updated' , array( 'setActive' , $param , $code , &$result ) );
			return $result;
		}		
		/**
		*
		*/
		public static function isActive( $param = null )
		{
			$user_id = ( $param ) ? $param : @$_SESSION[ 'user_id' ];
			if ( !$user_id ){ return false; }
			return ( boolean ) static::_connection( static::$_options[ 'users_table' ] )
					->where( static::_checkColumn( $user_id ) , '=' , $user_id )
					->row( static::$_tableColumns[ 'active' ] );
		}
		/**
		*
		*/
		public static function isVerified( $userID = null )
		{
			$user_id = ( $userID ) ? $userID : @$_SESSION[ 'user_id' ];
			if ( !$user_id ){ return false; }
			return ( boolean ) static::_connection( static::$_options[ 'users_table' ] )
					->where( static::_checkColumn( $user_id ) , '=' , $user_id )
					->row( static::$_tableColumns[ 'verified' ] );
		}
		/**
		* // 4 already verified , 3 some error occured , 2 code not found in db, 1 user verified
		*/
		public static function verify( $code , $newCode = false )
		{
			$result = 3;
			static::_fireEvent( 'verifying' , array( &$code ) );
			static::_connection( )->setFetchMode( \PDO::FETCH_OBJ );
			$user = static::_connection( static::$_options[ 'users_table' ])
					->where( static::$_tableColumns[ 'verification' ] , '=' , $code )
					->row( );
			if ( !$user ){ $result = 2; } // code not found
			if ( 3 === $result )
			{
				if ( true === ( boolean ) $user->{static::$_tableColumns[ 'verified' ]} ){ $result = 4; }
				else if ( static::setVerified( $user->id ) )
				{
					if ( $newCode )
					{
						$data = array( static::$_tableColumns[ 'verification' ] => static::random( ) );
						static::_connection( static::$_options[ 'users_table' ] )
							->update( static::_track( $data ) , $user->id )
							->run( );
					}
					$result = 1; // user has been verfied
				}
			}
			static::_fireEvent( 'verified' , array( $code , &$result , static::_guard( $user ) ) );
			return $result;
		}
		/**
		*
		*/
		public static function user( $value = null )
		{
			$connection = ( static::$_options[ 'model' ] ) ? static::$_options[ 'model' ] : 
								static::_connection( static::$_options[ 'users_table' ] );
			static::_connection( )->setFetchMode( \PDO::FETCH_OBJ );
			if ( is_array( $value ) )
			{
				foreach( $value as $k => $v )
				{
					$obj = call_user_func_array( 
						array( $connection , 'where' ) , array( $k , '=' , $v ) );
				}
				return static::_guard( $obj->row( ) ); 
			}
			$value = ( $value ) ? $value : @$_SESSION[ 'user_id' ];
			if ( !$value ){ return null; } // no user id set
			$columns = static::$_tableColumns;
			$column = ( is_numeric( $value ) ) ? $columns[ 'unique_key' ] : $columns[ 'username' ];
			$obj = call_user_func_array( 
				array( $connection , 'where' ) , array( $column , '=' , $value ) );	
			return static::_guard( $obj->row( ) );
		}
		/**
		*
		*/
		public static function setCookie( $name , $value , $expire = null , $path = null, $domain = null, $secure = null, $httpOnly = null ) 
		{
			$hash = ( function_exists( 'mcrypt_create_iv' ) ) ? 
				mcrypt_create_iv( 2 , MCRYPT_DEV_URANDOM ) : static::random( 2 );
			$value = ( null === $value ) ? $value : static::_hashCookie( $value , $hash );
			return setcookie( $name , $value , 
				static::makeTime( $expire ) , $path , $domain , $secure , $httpOnly );
		}
		/**
		*
		*/
		public static function getCookie( $name , $decrypt = true ) 
		{	
			if ( !isset( $_COOKIE[ $name ] ) ){ return null; } // does not exist
			if ( true === $decrypt ) 
			{
				if ( substr( $_COOKIE[ $name ] , 0 , 3 ) !==  
					static::$_options[ 'cookie_prefix' ] ){ return - 1; } // modified
				$data = pack( "H*" , substr( $_COOKIE[ $name ] , 3 ) ); // Unpack hex
				$value = substr( $data , 32 , - 2 ); // get value
				$rand = substr( $data , - 2 , 2 ); // get random prefix
				if ( static::_hashCookie( $value , $rand ) !== $_COOKIE[ $name ] ){ return - 1; }
				return $value;
			}
			return $_COOKIE[ $name ];
		}
		/**
		*
		*/
		public static function configure( $options = array( ) , $columns = array( ) )
		{
			if ( static::$_configured )
			{
				trigger_error( 'Application has been configured ' . 
					'to run already, cannot run configure( ) after component ' . 
						' has been called or already configured!' , E_USER_ERROR );
				return false;
			}
			foreach ( $options as $k => $v )
			{
				if ( !array_key_exists( $k , static::$_options ) )
				{
					trigger_error( 'Authenticantion configuration error, option ' .
									$k . ' is not recognized!' , E_USER_ERROR );
					return false;
				}
			}
			if ( isset( $options[ 'remember_options' ] ) )
			{
				$options[ 'remember_options' ] = ( is_array( $options[ 'remember_options' ] ) ) ? 
					$options[ 'remember_options' ] : array( 'param' => $options[ 'remember_options' ] ); 
				$options[ 'remember_options' ] = array_merge( static::$_options[ 'remember_options' ] , 
																$options[ 'remember_options' ] );
			}
			static::$_options = array_merge( static::$_options , $options );
			static::$_options[ 'connection_manager' ] = 
				static::_namespace( static::$_options[ 'connection_manager' ] ,'PtcDb' );
			static::$_tableColumns = ( empty( $columns ) ) ? static::$_tableColumns : $columns;
			if ( !static::_checkConfig( static::$_tableColumns ) ){ return false; }
			static::_debug( array( static::$_options , static::$_tableColumns ) , 
							'User options set for authentication component!', 
										static::$_options[ 'debug_category' ] );
			return static::$_configured = true;
		}		
		/**
		*
		*/
		public static function setUp( )
		{
			if ( !static::_isConfigured( ) ){ return false; };
			$qb = static::_connection( );
			if ( static::_checkSetup( static::$_options[ 'login_table' ] ) )
			{
				$qb->run( ' CREATE TABLE `' . static::$_options[ 'login_table' ] . '` (
						`id` int(11) NOT NULL auto_increment,
						`user_id` int(11) NOT NULL, 
						`session_id` char(32) binary NOT NULL, 
						`token` char(128) NOT NULL,
						`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
						`updated` TIMESTAMP NULL ,
						`expires` TIMESTAMP NULL ,
						`expired` tinyint(1) NOT NULL DEFAULT 0,
						PRIMARY KEY (`id`) )' );
			}
			if ( static::_checkSetup( static::$_options[ 'users_table' ] ) )
			{ 
				$qb->run( static::_createUsersTable( ) );
			}
			//return true;
		}
		/**
		*
		*/			
		public static function random( $length = 50 , $characters = null )
		{
			$characters = ( $characters ) ? $characters :
							'0123456789abcdefghijklmnopqrstuvwxyz';
			$string = '';
			for ( $p = 0; $p < $length; $p++ ) 
			{
				$string .= $characters[ mt_rand( 0 , ( strlen( $characters ) -1 ) ) ];
			}
			return $string;
		}
		/**
		*
		*/
		public static function makeTime( $value = '7' )
		{
			switch ( $value )
			{
				case is_int( $value ) : return $value; // do nothing
				case 'forever' : 
					return ( time( ) + ( 10 * 365 * 24 * 60 * 60 ) ); // 10 years
				case false !== stripos( $value , 'y' ) : 
					return ( time( ) + 60 * 60 * 365 * 24 * intval( $value ) ); // *years
				case false !== stripos( $value , 'h' ) : 
					return ( time( ) + 60 * 60 * intval( $value ) ); // *hours
				case false !== stripos( $value , 'm' ) : 
					return ( time( ) + 60 * intval( $value ) ); // *minutes
				case false !== stripos( $value , 's' ) : 
					return ( time( ) + 1 * intval( $value ) ); // *seconds
				default: return ( time( ) + 60 * 60 * 24 * intval( $value ) ); // days
			}
		}
		/**
		*
		*/
		public function isAdmin( $param = null )
		{		
			if ( !static::_isConfigured( ) ){ return false; }
			$user_id = ( $param ) ? $param : @$_SESSION[ 'user_id' ];
			if ( !$user_id ){ return false; } // no user id set
			$is_admin = static::_connection( static::$_options[ 'users_table' ] )
						->where( static::_checkColumn( $user_id ) , '=' , $user_id )
						->row( static::$_tableColumns[ 'admin' ] );
			if ( true === ( boolean ) $is_admin ){ return true; }
			return false;
		}
		/**
		*
		*/
		public static function dencrypt( $string ) 
		{
			// to do in the future
		}
		/**
		*
		*/
		public static function encode( $string ) 
		{
			// to do in the future
		}
		/**
		*
		*/
		public static function decode( $string ) 
		{
			// to do in the future
		}
		/**
		* UNTESTED
		*/
		public static function encrypt( $length = 50 ) 
		{
			switch ( true ) 
			{
				case function_exists( 'mcrypt_create_iv' ) :
					$r = mcrypt_create_iv( $length , MCRYPT_DEV_URANDOM );
				break;
				case function_exists( 'openssl_random_pseudo_bytes' ) :
					$r = openssl_random_pseudo_bytes( $length );
				break;
				case is_readable( '/dev/urandom' ) :
					$r = file_get_contents( '/dev/urandom' , false , null , 0 , $length );
				break;
				default :
					$i = 0;
					$r = "";
					while ( $i ++ < $length ){ $r .= chr( mt_rand( 0 , 255 ) ); }
				break;
			}
			return substr( bin2hex( $r ) , 0 , $length );
		}
		/**
		* 0 some error occured , 2 email already exists , 1 created
		*/
		public static function create( $username , $password , $extraData = null , $isAdmin = 0 )
		{			
			if ( !static::_isConfigured( ) ){ return false; }
			$code = 0;
			$data = null;
			static::_fireEvent( 'creating' , array( &$username , &$extraData , &$isAdmin ) );
			$columns = static::$_tableColumns;
			$connection = static::_connection( );
			$connection->table( static::$_options[ 'users_table' ] )
				->where( $columns[ 'username' ] , '=' , $username )
				->run( );
			if ( $connection->CountRows( ) > 0 ){ $code = 2; } // email already exists
			if ( 0 === $code )
			{
				$user_salt = static::random( ); // generate users salt
				$password = $user_salt . $password; // salt and Hash the password
				$password = static::_hashPassword( $password );
				$data = array
				(
					$columns[ 'salt' ] 		=> $user_salt ,
					$columns[ 'password' ] 	=> $password ,
					$columns[ 'username' ] 	=> $username
				);
				if ( static::$_options[ 'verify' ] ) // create verification code
				{
					$data[ $columns[ 'verified' ] ] = 0;
					$data[ $columns[ 'verification' ] ] = static::random( ); 
				}
				if ( static::$_options[ 'check_active' ] ){ $data[ $columns[ 'active' ] ] = 0; }
				if ( static::$_options[ 'check_admin' ] ){ $data[ $columns[ 'admin' ] ] = $isAdmin; }
				if ( $extraData ) // add extra data if any to the insert
				{
					$invalid_keys = array( $columns[ 'password' ] , 
						$columns[ 'username' ] , $columns[ 'unique_key' ] , $columns[ 'salt' ] );
					foreach ( $extraData as $k => $v )
					{
						if ( in_array( $k , $invalid_keys ) ){ continue; }
						$data[ $k ] = $v; 
					}
				}
				$insert = $connection->table( static::$_options[ 'users_table' ] )
							->insert( $data )
							->run( );
				$code = ( $insert ) ? 1 : 0;
			}	
			static::_fireEvent( 'created' , array( $username , $code , $data ) );
			return $code;
		}
		/**
		*
		*/		
		public static function sendMail( $param , $emailTpl = array( ) , $extraData = array( ) )
		{
			if ( !static::_isConfigured( ) ){ return false; }
			if ( !$user = static::user( $param ) ){ return false; }
			$columns = static::$_tableColumns;
			$header = ( array_key_exists( 'header' , $emailTpl ) ) ? 
				$emailTpl[ 'header' ] : static::$_defaultEmailTpl[ 'header' ];
			$subject = ( array_key_exists( 'header' , $emailTpl ) ) ? 
				$emailTpl[ 'subject' ] : static::$_defaultEmailTpl[ 'subject' ];
			$message = ( array_key_exists( 'header' , $emailTpl ) ) ? 
				$emailTpl[ 'message' ] : static::$_defaultEmailTpl[ 'message' ];
			foreach ( $extraData as $k => $v )
			{
				if ( !$v ){ continue; }
				$message = str_replace( '{' . $k . '}' , $v , $message ); 
			}
			foreach ( $columns as $k => $v )
			{
				if ( !$v ){ continue; }
				$header = str_replace( '{' . $k . '}' , @$user->{ $v } , $header );
				$subject = str_replace( '{' . $k . '}' , @$user->{ $v } , $subject );
				$message = str_replace( '{' . $k . '}' , @$user->{ $v } , $message ); 
			}
			mail( $user->{ $columns[ 'email' ] } , $subject , $message , $header );
			return true;
		}
		/**
		*
		*/
		public static function logout( $destroySession = false )
		{
			if ( !static::_isConfigured( ) ){ return false; }
			$user_id = ( isset( $_SESSION[ 'user_id' ] ) ) ? $_SESSION[ 'user_id' ] : null;
			static::_fireEvent( 'before_logout' , array( $user_id , &$destroySession ) );
			if ( isset( $_SESSION[ 'user_id' ] ) )
			{ 
				$options = static::$_options;
				static::_connection( static::$_options[ 'login_table' ] )
					->where( 'user_id' , '=' ,  $_SESSION[ 'user_id' ] )
					->delete( )
					->run( );
				/*$qb = static::_connection( static::$_options[ 'login_table' ] );
				$qb->where( 'user_id' , '=' , $_SESSION[ 'user_id' ] ) // delete previous sessions
				->where( function( $query )
				{
					$query->where( 'expires' , '<' , $query->raw( 'NOW()' ) )
						->rawSelect( ' OR `expires` IS NULL' );
				} )->delete( )->run( );*/
				if ( @$options[ 'remember_options' ][ 'param' ] )
				{
					$update = array( static::$_tableColumns[ 'remember' ] => '' );
					static::_connection( $options[ 'users_table' ] )
						->update( static::_track( $update ) , $_SESSION[ 'user_id' ] )
						->run( );
					if ( static::getCookie( $options[ 'remember_options' ][ 'param' ] ) )
					{
						static::setCookie( $options[ 'param' ] , '' , strtotime( '-1 day' ) , $options[ 'path' ] , 
									$options[ 'domain' ] , $options[ 'secure' ] , $options[ 'http_only' ] );
					}
				}
			}
			unset( $_SESSION[ 'token' ] );
			unset( $_SESSION[ 'user_id' ] );
			if ( $destroySession ){ session_destroy( ); }
			static::_fireEvent( 'after_logout' , array( $user_id , $destroySession ) );
			return true;
		}
		/**
		* 0 some error occured , 4 no match , 3 not verified , 2 not active ,  1 logged in
		*/
		public static function login( $username , $password )
		{
			if ( !static::_isConfigured( ) ){ return false; }
			$code = 0;
			static::_fireEvent( 'before_login' , array( $username ) );
			$columns = static::$_tableColumns;
			static::$_guard = false;
			if ( !$user = static::user( $username ) ){ $code = 4; } // mo match
			else if ( static::$_options[ 'verify' ] && 
				true !== ( boolean ) $user->{ $columns[ 'verified' ] } ){ $code = 3; } // user is not verified
			else if ( static::$_options[ 'check_active' ] && 
				true !== ( boolean ) $user->{ $columns[ 'active' ] } ){ $code = 2; } // user is not active
			else
			{
				$password = static::_hashPassword( $user->{ $columns[ 'salt' ] } . $password );
				if ( $password !== $user->{ $columns[ 'password' ] } ){ $code = 4; } // mo match
			}
			if ( 0 === $code ){ $code = static::_processLogin( $user->{ $columns[ 'unique_key' ] } ); }
			static::_fireEvent( 'after_login' , array( $username , $code , static::_guard( $user ) ) );
			return $code;
		}
		/**
		*
		*/
		public static function setExpired( $record = null )
		{
			if ( !static::_isConfigured( ) ){ return false; }
			$qb = static::_connection( static::$_options[ 'login_table' ] ); 
			if ( $record )
			{
				$result = $qb->where( 'id' , '=' , $record )
						->update( array( 'expired' => 1) )
						->run( );
				return $result;
			}
			$qb->where( 'expires' , '<' , $qb->raw( 'NOW()' ) )
				->update( array( 'expired' => 1) )
				->run( );
		}
		/**
		*
		*/
		public static function check( )
		{
			if ( !static::_isConfigured( ) ){ return false; }
			static::setExpired( );
			if ( @static::$_options[ 'remember_options' ][ 'param' ] && 
									!isset( $_SESSION[ 'user_id' ] ) )
			{
				if ( $user = static::_checkCookie( ) )
				{ 
					return ( static::_processLogin( 
						$user->{ static::$_tableColumns[ 'unique_key' ] } 
											, 'update' ) ) ? true : false; 
				}
			}
			if ( !isset( $_SESSION[ 'user_id' ] ) ){ return false; }
			static::_connection( )->setFetchMode( \PDO::FETCH_OBJ );			
			$login_data = static::_connection( static::$_options[ 'login_table' ] )
						->where( 'user_id' , '=' , $_SESSION[ 'user_id' ] )
						->where( 'expired' , '!=' , 1 )
						->row( );
			if ( !$login_data ){ return false; }
			if ( session_id( ) === $login_data->session_id && 
					$_SESSION[ 'token' ] === $login_data->token ) 
			{			
				static::_refresh( $login_data->id );
				return true;
			}
			return false;
		}
		/**
		* Adds observers to the class to use event listeners with the queries. See @ref using_observers
		* @param	string		$class		the name of the class that will be used as observer
		*/
		public static function observe( $class = null )
		{
			if ( !class_exists( $events_class = 
				static::_namespace( static::$_options[ 'event_class' ] , 'PtcEvent' ) ) )
			{
				trigger_error( $events_class . ' not found, cannot use observer!' , E_USER_ERROR );
				return false;
			}
			$class = ( $class ) ? $class : get_called_class( );
			$methods = get_class_methods( $class );
			foreach ( static::$_events as $event )
			{
				if ( in_array( $event , $methods ) )
				{
					$cls = strtolower( $class );
					$events_class::listen( $cls . '.' . $event , $class . '::' . $event );
					static::$_observers[ get_called_class( ) ][ $cls . '.' . $event ] = $event;
				}
			}
		}
		/**
		*
		*/
		protected static $_options = array
		( 
			'app_key'				=>	'private_app_key' ,
			'connection_name'		=>	'default' ,
			'connection_manager'	=>	'PtcDb' ,
			'model'				=>	null ,
			'event_class'			=>	'PtcEvent' ,
			'users_table'			=>	'users' ,
			'login_table'			=>	'logged_in_users' ,
			'verify'				=>	false ,
			'check_active'			=>	false ,
			'check_admin'			=>	false ,
			'use_dates'			=>	false ,
			'cookie_prefix'			=>	'$x$' ,
			'keep_sessions'		=>	30 , // in days
			'debug_category'		=>	'Authentication' , 
			'remember_options'		=>	array
			(
				'param' 			=>	null ,
				'type' 			=>	'request' ,
				'expires' 			=>	'30' ,
				'path'			=>	'/' ,
				'domain'			=>	null ,
				'secure'			=>	null ,
				'http_only'			=>	null
			) ,
			'guard'				=>	array
			(
				'salt' , 'password' , 'created' , 'last_login' , 'remember'
			) 
		);		
		/**
		*
		*/
		protected static $_tableColumns = array
		(
			'salt'			=> 'user_salt' , // required ***
			'username'	=> 'username' , // required ***
			'password'	=> 'password' , // required ***
			'unique_key'	=> 'id' , // required ***
			'firstname'	=> 'firstname' , // optional
			'lastname'	=> 'lastname' , // optional
			'email'		=> 'email' , // optional
			'group'		=> 'group' , // optional
			'active'		=> 'is_active' , // optional controlled
			'admin'		=> 'is_admin' , // optional controlled 
			'verified'		=> 'is_verified' , // optional controlled
			'verification'	=> 'verification_code' , // optional controlled
			'remember'	=> 'remember_me' , // optional controlled
			'created'		=> 'created' , // optional controlled
			'updated'		=> 'updated' , // optional controlled
			'last_login'	=> 'last_login' , // optional controlled
		);
		/**
		*
		*/
		protected static $_defaultEmailTpl = array
		(
			'header'		=> 'Your verification code' ,
			'subject'		=> 'Sent by your website' ,
			'message'	=> 'Your verification code is {verification}' ,
		);
		/**
		* Possible observer events array. See @ref using_observers
		*/
		protected static $_events = array
		(	
			'creating' , 'created' , 'updating' , 'updated' , 'before_login' , 
			'after_login' , 'before_logout' , 'after_logout' ,'verifying' , 'verified'
		);
		/**
		* Property that holds the observer classes
		*/
		protected static $_observers = array( );
		/**
		*
		*/
		protected static $_guard = true;
		/**
		*
		*/
		protected static $_configured = false;
		/**
		*
		*/
		protected static function _checkSetup( $table )
		{
			$qb = static::_connection( );
			$qb->run( 'SHOW TABLES LIKE ?' , array( $table ) );
			if ( $qb->countRows( ) )
			{ 
				static::_debug( 'Table ' . $table . 
						' already exists, either remove setUp( ) or drop ' . 
						' the table manually!' , '' , 'Authentication Setup' );
				return false;
			}
			return true;
		}
		/**
		* Fires events if methods are present in observers classes. See @ref using_observers
		* @param	string		$event	the event name stored in the $_observers property
		* @param	array		$data	in array with the data to pass to the listeners
		*/
		protected static function _fireEvent( $event , $data )
		{
			$event = ( is_array( $event ) ) ? $event : array( $event );
			$event_class = static::_namespace( static::$_options[ 'event_class' ] , 'PtcEvent' );
			if ( array_key_exists( $class = get_called_class( ) , static::$_observers ) )
			{
				foreach ( static::$_observers[ $class ] as $k => $v )
				{
					foreach ( $event as $ev )
					{
						if ( $v === $ev ){ $event_class::fire( $k , $data ); }
					}
				}
			}
		}
		/**
		*
		*/
		protected static function _guard( $user )
		{
			if ( static::$_guard && static::$_options[ 'guard' ] && is_object( $user ) )
			{
				foreach ( static::$_options[ 'guard' ] as $column )
				{
					$col = static::$_tableColumns[ $column ];
					if ( static::$_options[ 'model' ] && 
						method_exists( $user , 'remove' ) )
					{ 
						$user->remove( $col ); 
					}
					else{ unset( $user->{ $col } ); }
				}
			}
			static::$_guard = true;
			return $user;
		}
		/**
		*
		*/
		protected static function _checkCookie( )
		{		
			$data = static::getCookie( static::$_options[ 'remember_options' ][ 'param' ] );
			if ( !$data ){ return false; }
			$cookie = json_decode( $data );
			if ( !@$cookie[ 0 ] || !$cookie[ 1 ]  || !$cookie[ 2 ] ){ return false; }
			static::_connection( )->setFetchMode( \PDO::FETCH_OBJ );			
			$user = static::_connection( static::$_options[ 'users_table' ] ) 
					->where( static::$_tableColumns[ 'unique_key' ] , '=' , $cookie[ 0 ] )
					->row( );
			if ( !@$user->{ static::$_tableColumns[ 'remember' ] } ){ return false; }
				$check = static::_hashPassword( $cookie[ 0 ] . 
					$user->{ static::$_tableColumns[ 'remember' ] } );
				if ( $check !== $cookie[ 1 ] ){ return false; }
			return $user;
		}
		/**
		*
		*/
		protected static function _checkColumn( $value )
		{
			$column = ( is_numeric( $value ) ) ? 
				static::$_tableColumns[ 'unique_key' ] : static::$_tableColumns[ 'email' ];
			return $column;
		}
		/**
		*
		*/
		protected static function _track( $data )
		{
			if ( static::$_options[ 'use_dates' ] )
			{
				$data[ static::$_tableColumns[ 'updated' ] ] =  date( 'Y-m-d H:i:s' );
			}
			return $data;
		}
		/**
		*
		*/
		protected static function _refresh( $recordID )
		{
			session_regenerate_id( ); // regenerate id
			$token = static::token( );
			$_SESSION[ 'token' ] = $token; // store in session
			$data = array( 'session_id' => session_id( ) , 
				'token' => $token , 'updated' => date( 'Y-m-d H:i:s' ) );
			static::_connection( static::$_options[ 'login_table' ] )
				->update( $data , $recordID )
				->run( );
			return true;
		}
		/**
		*
		*/
		protected static function _hashPassword( $data )
		{
			return hash_hmac( 'sha512' , $data , static::$_options[ 'app_key' ] );
		}
		/**
		*
		*/
		protected static function _hashCookie( $value , $suffix ) 
		{
			$hash = bin2hex( hash_hmac( 'sha256' , $value . $suffix , 
					static::$_options[ 'app_key' ] , true ) . $value . $suffix );
			return static::$_options[ 'cookie_prefix' ] . $hash;
		}
		
		/**
		*
		*/
		protected static function _isConfigured( )
		{
			if ( !static::$_configured )
			{
				trigger_error( 'Authentication cannot run, please use PtcAuth::configure( ) ' .
									' and make sure all options and required tables are ' .
									' set with PtcAuth::setUp( ) or manually!' , E_USER_ERROR );
			}	
			return static::$_configured; 
		}
		/**
		*
		*/
		protected static function _checkConfig( $columns )
		{
			$check_columns = array( 'password' , 'email' , 'unique_key' , 'salt' );
			foreach ( $check_columns as $required )
			{
				if ( !array_key_exists( $required , $columns ) )
				{
					trigger_error( 'Missing required column ' . $required . 
						', authentication class cannot work properly!' , E_USER_ERROR );
					return false;
				}
			}
			if ( static::$_options[ 'guard' ] )
			{
				foreach ( static::$_options[ 'guard' ] as $guard )
				{
					if ( !array_key_exists( $guard , static::$_tableColumns ) )
					{
						trigger_error( 'Guard column ' . $guard .
							' does not exist, cannot continue!' , E_USER_ERROR );
						return false;
					}
				}
			}
			if ( static::$_options[ 'verify' ] && 
				( !@$columns[ 'verified' ] || !@$columns[ 'verification' ] ) )
			{
					trigger_error( 'Cannot use verify option , columns verified ' . 
						' and verification must be set in options!' , E_USER_ERROR );
				return false;
			}
			if ( static::$_options[ 'check_active' ] && !@$columns[ 'active' ] )
			{
				trigger_error( 'Cannot use check_active option , ' . 
					'column must be set in options!' , E_USER_ERROR );
				return false;
			}
			if ( static::$_options[ 'check_admin' ] && !@$columns[ 'admin' ] )
			{
				trigger_error( 'Cannot use admin option , ' . 
					'column admin must be set in options!' , E_USER_ERROR );
				return false;
			}
			if ( static::$_options[ 'use_dates' ] )
			{
				if ( !@$columns[ 'created' ] || 
					!@$columns[ 'updated' ] || !@$columns[ 'last_login' ] )
				{
					
					trigger_error( 'Cannot track user activity , please make sure ' . 
						' that all columns for use_dates are available' , E_USER_ERROR );
					return false;
				}
			}
			if ( @static::$_options[ 'remember_options' ][ 'param' ] && !@$columns[ 'remember' ] )
			{
				trigger_error( 'Column for remember_options is not set!' , E_USER_ERROR );
				return false;
			}
			return  true;
		}
		/**
		*
		*/
		protected static function _createUsersTable( )
		{
			$query = ' CREATE TABLE `' . static::$_options[ 'users_table' ] . '` ( ';
			$columns = static::$_tableColumns;
			$query .= '`' . $columns[ 'unique_key' ] . '` int(11) NOT NULL auto_increment,';
			$query .= '`' . $columns[ 'username' ] . '` varchar(255) NOT NULL,';
			if ( @$columns[ 'firstname' ] )
			{
				$query .= '`' . $columns[ 'firstname' ] . '` varchar(255) NULL,';
			}
			if ( @$columns[ 'lastname' ] )
			{
				$query .= '`' . $columns[ 'lastname' ] . '` varchar(255) NULL,';
			}
			if ( @$columns[ 'email' ] )
			{
				$query .= '`' . $columns[ 'email' ] . '` varchar(255) NULL,';
			}
			if ( @$columns[ 'group' ] )
			{
				$query .= '`' . $columns[ 'group' ] . '` varchar(255) NULL,';
			}
			$query .=  '`' . $columns[ 'password' ] . '` varchar(128) NOT NULL,';
			$query .= '`' . $columns[ 'salt' ] . '` varchar(50) NOT NULL,';
			if ( static::$_options[ 'verify' ] )
			{
				$query .= '`' . $columns[ 'verification' ] . '` varchar(65) NOT NULL,';
				$query .= '`' . $columns[ 'verified' ] . '` tinyint(1) NOT NULL,';
			}
			if ( @static::$_options[ 'remember_options' ][ 'param' ] )
			{
				$query .= '`' . $columns[ 'remember' ] . '` char(128) NULL,';
			}
			if ( static::$_options[ 'check_active' ] )
			{
				$query .= '`' . $columns[ 'active' ] . '` tinyint(1) NOT NULL,';
			}
			if ( static::$_options[ 'check_admin' ] )
			{
				$query .= '`' . $columns[ 'admin' ] . '` tinyint(1) NOT NULL,';
			}
			if ( static::$_options[ 'use_dates' ] )
			{
				$query .= '`' . $columns[ 'created' ] . '` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,';
				$query .= '`' . $columns[ 'updated' ] . '` TIMESTAMP,';
				$query .= '`' . $columns[ 'last_login' ] . '` TIMESTAMP,';
			}
			$query .= 'UNIQUE (`' . $columns[ 'email' ] . '`),';
			$query .= 'PRIMARY KEY (`' . $columns[ 'unique_key' ] . '`)';
			return $query .= ')';
		}
		/**
		*
		*/
		protected static function _cleanUp( )
		{
			$frequency = static::$_options[ 'keep_sessions' ];
			$qb = static::_connection( static::$_options[ 'login_table' ] );
			$qb->where( 'created' , '<' , 
				$qb->raw( 'TIMESTAMPADD(DAY,-' . intval( $frequency ) . ',NOW())' ) )
				->where( function( $query )
				{
					$query->where( 'expires' , '<' , $query->raw( 'NOW()' ) )
						->rawSelect( ' OR `expires` IS NULL' );
				} )->delete( )->run( );
		}
		/**
		*
		*/
		protected static function _processLogin( $userID , $setCookie = 'new' , $expires = false )
		{
			static::_cleanUp( ); // clean up old logins from table
			$data = null;
			$columns = static::$_tableColumns;
			$token = static::token( );
			$qb = static::_connection( static::$_options[ 'login_table' ] ); 
			$qb->where( 'user_id' , '=' , $userID ) // delete previous sessions
				->where( function( $query )
				{
					$query->where( 'expires' , '<' , $query->raw( 'NOW()' ) )
						->rawSelect( ' OR `expires` IS NULL' );
				} )->delete( )->run( );
			if ( session_id( ) === '' ){ session_start( ); } // setup sessions vars
			$data = array( 'user_id' => $userID , 'session_id' => session_id( ) , 'token' => $token );
			if ( $expires ){ $data[ 'expires' ] = date( 'Y-m-d H:i:s' , static::makeTime( $expires ) ); }
			$insert = static::_connection( static::$_options[ 'login_table' ] )->insert( $data )->run( );
			$code = ( $insert ) ? 1 : 0; // 0 if some error occured inserting a new row
			if ( $code === 1 )
			{
				$_SESSION[ 'token' ] = $token;
				$_SESSION[ 'user_id' ] = $userID;
				if ( static::$_options[ 'use_dates' ] ) // add last login
				{
					$updated = array( $columns[ 'last_login' ] => date( 'Y-m-d H:i:s' ) );
					static::_connection( static::$_options[ 'users_table' ] )
						->update( static::_track( $updated ) , $userID )
						->run( );
				}
				if ( @static::$_options[ 'remember_options' ][ 'param' ] )	 // add remember me cookie
				{
					$token = static::token( );
					static::_addCookie( $userID , $token , $setCookie );
				}			
			}
			return $code;
		}
		/**
		*
		*/
		protected static function _addCookie( $userID , $token , $setCookie = 'new' )
		{					
			$options = static::$_options[ 'remember_options' ];
			if ( 'new' === $setCookie )
			{
				$cookie = false;
				$type = explode( '|' , $options[ 'type' ] );
				foreach ( $type as $check )
				{
					$var = strtoupper( '$_' . $check );
					if ( eval( 'return @' . $var . '[' . $options[ 'param' ] . '];' ) )
					{
						$cookie = true;
						break;
					}
				}
				if ( $cookie ){ static::_createCookie( $userID , $token ); }
			}
			else if ( 'update' === $setCookie )
			{
				$cookie = static::getCookie( $options[ 'param' ] );
				$cookie_data = json_decode( $cookie );
				static::_createCookie( $userID , $token , intval( $cookie_data[ 2 ] ) );
			}
		}
		/**
		*
		*/		
		protected static function _createCookie( $userID , $token , $expires = null )
		{
			static::_fireEvent( 'updating' , array( 'autoLogin' , &$userID , &$token , &$expires ) );
			$options = static::$_options[ 'remember_options' ];
			$signature = static::_hashPassword( $userID . $token );
			$expires = ( $expires ) ? $expires : $options[ 'expires' ];
			$expires = static::makeTime( $expires );
			$data = json_encode( array( $userID , $signature , $expires ) );
			static::setCookie( $options[ 'param' ] , $data , $expires , $options[ 'path' ] , 
					$options[ 'domain' ] , $options[ 'secure' ] , $options[ 'http_only' ] );
			$update = array( static::$_tableColumns[ 'remember' ] => $token );
			$result = static::_connection( static::$_options[ 'users_table' ] )
					->update( static::_track( $update ) , $userID )
					->run( );
			static::_fireEvent( 'updated' , array( 'autoLogin' , $userID , $result , $update ) );
		}
		/**
		*
		*/
		protected static function _connection( $table = null )
		{ 
			$class = static::_namespace( static::$_options[ 'connection_manager' ] ,'PtcDb' );
			$arg = static::$_options[ 'connection_name' ];
			$connection = call_user_func_array( array( $class , 'getQB' ) , array( $arg ) );
			return ( $table ) ? $connection->table( $table ) : $connection;
		}
		/**
		* Adds namespace to the library components
		*/	
		protected static function _namespace( $className , $string = 'PtcDb' )
		{
			return ( $string === $className ) ? 
				__NAMESPACE__ . '\\' . $className : $className;
		}
		/**
		* Send messsages to the PtcDebug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement	some statement if required
		* @param	string		$category	a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 
							'bufferLog' ) ,  array( $string , $statement , $category ) );
		}
	}
