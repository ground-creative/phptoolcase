<?php

	namespace phptoolcase;

	/**
	* PHPTOOLCASE VIEW CLASS
	* PHP version 5.6+
	* @category 	Library
	* @version	v1.1.1-stable
	* @author   	Carlo Pietrobattista <carlo@ground-creative.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/

	class View
	{
		/**
		* Compiles a view
		* @param	string	$view	the name of the view file
		* @param	array	$data	data to pass to the view
		*/
		public static function make( $view , $data = null )
		{
			if ( !static::$_base )
			{
				$msg = 'Please configure a base path for the view templates!';
				trigger_error( $msg , E_USER_ERROR ); 
				return false;
			}
			$view = new ViewTpl( $view , static::$_base ); // create a new view object
			if ( $data ) // add data to the template
			{ 
				foreach ( $data as $k => $v ){ $view->set( $k , $v ); }
			}
			static::_debug( $view , 'Created a new view template object!' , 'View Config' );
			return $view;
		}
		/**
		* Adds a base path that will be used when loading views
		*/
		public static function path( $path )
		{
			if ( static::$_base )
			{
				$msg = 'Views path already set, and cannot be ovewritten!';
				trigger_error( $msg , E_USER_ERROR ); 
				return false;
			}
			$err_path = $path;
			if ( !$path = @realpath( $path ) )
			{
				trigger_error( 'Views path "' . $err_path .
					'" does not exists or is not accessible!' , E_USER_ERROR ); 
				return false;
			}
			static::$_base = $path . '/';
			static::_debug( static::$_base , 
				'Configured a base path for the view templates!' , 'View Config' );
			return static::$_base;
		}
		/**
		* Base path for views property
		*/
		protected static $_base = null;
		/**
		* Sends messsages to the PtcDebug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement	some statement if required
		* @param	string		$category	a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			return @call_user_func_array( [ '\\' . _PTCDEBUG_NAMESPACE_ , 'bufferLog' ] ,  [ $string , $statement , $category ] );
		}
	}
	
	/**
	| ----------------------------------------------------------------------------
	| Html View Compiler Interface
	| ----------------------------------------------------------------------------
	*/
	
	class ViewTpl
	{
		/**
		* Adds the html template and the base path
		* @param	string	$template	the html view file name without .php
		* @param	string	$base		a base path where the file resides
		*/
		public function __construct( $template , $base = null )
		{ 
			$this->_template = $template . '.php'; 
			$this->_base = $base; 
		}
		/**
		* Sets a value for the view
		* @param	string	$var		the name of the variable
		* @param	mixed	$val		the value for the variable
		*/
		public function set( $var, $val )
		{
			$this->_pageVars[ $var ] = $val;
			return $this;
		}
		/**
		* Retrieves page variables for the view
		* @param	string	$var		used to secify a variable by name to return
		*/
		public function get( $var = null )
		{
			return ( $var ) ? $this->_pageVars[ $var] : $this->_pageVars;
		}
		/**
		* @deprecated, use @ref get( )
		*/
		public function getPageVars( $var = null )
		{
			return ( $var ) ? $this->_pageVars[ $var] : $this->_pageVars;
		}
		/**
		* Compiles the html view with the variables
		* @param	string	$nested	used by the debugger to alert if the view is nested
		* @return	the compiled html view
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
		* Compiles and / or renders the view
		*/
		public function render( )
		{
			if ( !$this->_compiledHtml ){ $this->compile( ); }
			$this->_debug( $this , 'Rendering view file <b><i>' . 
				$this->_cleanPath( ) . '</i></b>' , 'View Action' );
			echo $this->_compiledHtml;
		}
		/**
		* Adds a nested view to the main view
		* @param	string	$param		the param to compile with the nested view
		* @param	string	$templete	the nested view template
		* @param	array	$data		data to add to the nested view
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
		* Adds a base path for the view file
		* @param	string	$path	folder path where the view resied
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
		* Property that holds the html for the view
		*/
		protected $_template = null;
		/**
		* Property that holds the compiled html
		*/
		protected $_compiledHtml = null;
		/**
		* Base folder path for the views
		*/
		protected $_base = null;
		/**
		* Property that holds data to pass to the view
		*/
		protected $_pageVars = array( );
		/**
		* Cleans the base paths for the view files
		*/
		protected function _cleanPath( )
		{
			return str_replace( '//' , '/' , $this->_base . $this->_template );
		}
		/**
		* Sends messsages to the Debug class if present
		* @param 	mixed 		$string		the string to pass
		* @param 	mixed 		$statement	some statement if required
		* @param	string		$category	a category for the messages panel
		*/
		protected function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ){ return false; }
			return @call_user_func_array( [ '\\' . _PTCDEBUG_NAMESPACE_ , 'bufferLog' ] ,  [ $string , $statement , $category ] );
		}
	}