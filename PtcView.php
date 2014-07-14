<?php

	/**
	* PHPTOOLCASE VIEW CLASS
	* PHP version 5.3
	* @category 	Library
	* @version	0.9.3b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	
	class PtcView
	{
		/**
		*
		*/
		public static function make( $view , $data = null )
		{
			$view = new PtcViewTpl( $view , static::$_base ); // create a new view object
			if ( $data ) // add data to the template
			{ 
				foreach ( $data as $k => $v ){ $view->set( $k , $v ); }
			}
			static::_debug( $view , 'Created a new view template object!' , 'View Config' );
			return $view;
		}
		/**
		*
		*/
		public static function path( $path )
		{
			if ( static::$_base )
			{
				$msg = 'Views path already set, cannot be set ovewritten!';
				trigger_error( $msg , E_USER_ERROR ); 
				return false;
			}
			if ( !$path = @realpath( $path ) )
			{
				trigger_error( 'Views path "' . $path .
					'" does not exists or is not accessible!' , E_USER_ERROR ); 
				return false;
			}
			static::$_base = $path . '/';
			static::_debug( static::$_base , 
				'Configured a base path for the view templates!' , 'View Config' );
			return static::$_base;
		}
		/**
		*
		*/
		protected static $_base = null;
		/**
		* Send messsages to the PtcDebug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement	some statement if required
		* @param		string		$category		a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 
							'bufferLog' ) ,  array( $string , $statement , $category ) );
		}
	}
	
	class PtcViewTpl
	{
		/**
		*
		*/
		public function __construct( $template , $base = null )
		{ 
			$this->_template = $template . '.php'; 
			$this->_base = $base; 
		}
		/**
		*
		*/
		public function set( $var, $val )
		{
			$this->_pageVars[ $var ] = $val;
			return $this;
		}
		/**
		*
		*/
		public function compile( $nested = null )
		{
			if ( !empty( $this->_pageVars ) ){ extract( $this->_pageVars ); }
			ob_start( );
			$view_file = $this->_cleanPath( );
			$this->_debug( $this , 'Compiling ' . $nested . ' view file <b><i>' . 
									$view_file . '</i></b>' , 'View Action' );
			include( $view_file );
			return $this->_compiledHtml = ob_get_clean( );
		}
		/**
		*
		*/
		public function render( )
		{
			if ( !$this->_compiledHtml ){ $this->compile( ); }
			$this->_debug( $this , 'Rendering view file <b><i>' . 
				$this->_cleanPath( ) . '</i></b>' , 'View Action' );
			echo $this->_compiledHtml;
		}
		/**
		*
		*/
		public function nest( $param , $template , $data = null )
		{
			$class = get_called_class( );
			$view = new $class( $template , $this->_base );
			if ( $data ) // add data to the nested view
			{
				foreach ( $data as $k => $v ){ $view->set( $k , $v ); }
			}
			$this->set( $param , $view->compile( 'nested' ) );
			return $this;
		}
		/**
		*
		*/
		public function path( $path )
		{
			if ( !$path = @realpath( $path ) )
			{
				trigger_error( 'Views path "' . $path .
					'" does not exists or is not accessible!' , E_USER_ERROR ); 
				return false;
			}
			$this->_base = $path . '/';
			return $trhis;
		}
		/**
		*
		*/
		protected $_template = null;
		/**
		*
		*/
		protected $_compiledHtml = null;
		/**
		*
		*/
		protected $_base = null;
		/**
		*
		*/
		protected $_pageVars = array( );
		/**
		*
		*/
		protected function _cleanPath( )
		{
			return str_replace( '//' , '/' , $this->_base . $this->_template );
		}
		/**
		* Send messsages to the PtcDebug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement	some statement if required
		* @param		string		$category		a category for the messages panel
		*/
		protected function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 
							'bufferLog' ) ,  array( $string , $statement , $category ) );
		}
	}