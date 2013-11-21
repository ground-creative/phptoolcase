<?php

	/**
	* PHP TOOLCASE HTML FORMS GENERATOR/VALIDATOR CLASS 
	* PHP version 5.3
	* @category 	Libraries
	* @package  	PhpToolCase
	* @version	0.9.1b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	
	class PtcForm
	{
		/**
		* Alias of getOptions( )
		* @param	string	$name	the name of the option
		*/
		public function getOption( $name = null ){ return $this->getOptions( $name ); }
		/**
		* Sets form method(POST/GET) and retrieves sent values
		* @param 	array 	$options		see {@link _defaultOptions} for available options
		* @tutorial	PtcForms.cls#class_options
		*/
		public function  __construct( $options = array( ) )
		{	
			$namespace = @strtoupper( @str_replace( '\\' , '_' , __NAMESPACE__ ) ) . '_';
			if ( !defined( '_PTCFORM_' . $namespace ) ) // declare the class namespace
			{
				@define( '_PTCFORM_' . $namespace , get_called_class( ) );
			}
			$this->_options = ( !empty( $options ) ) ? 
							array_merge( $this->_options , $options ) : $this->_options;
			$this->_options = ( !empty( $this->_options ) ) ? 
				array_merge( $this->_defaultOptions , $this->_options ) : $this->_defaultOptions;
			if ( method_exists( get_called_class( ) , 'boot' ) ){ $this->boot( ); }
			if ( $this->_options[ 'keep_values' ] )
			{
				$method = $this->_getFormValues( );
				if ( !empty( $method ) )
				{
					foreach ( @$method  as $k => $v )
					{
						if ( false !== @strpos( $k , '_ptcgen' ) )
						{
							$this->_hiddenValues[ $key = substr( $k , 0 , -12 ) ] = $v;
						}
					}
					$this->_editFormValues( $method );
				}
			}
			if ( method_exists( get_called_class( ) , 'formFields' ) )
			{ 
				$this->formFields( );
				$method = $this->_getFormValues( );
				foreach ( $method as $k => $v )
				{
					if ( in_array( $k , $this->_submit ) )
					{ 
						$this->_fireEvent( 'submit' , array( $k , $this ) ); 
						unset( $this->_submit[ array_search( $k, $this->_submit ) ] );
					}
				}
			}			
			if ( !defined( '_PTC_RANDID_' ) ) 
			{ 
				@$_SESSION[ '_PTC_RANDID_' ] = 0; 
				@define( '_PTC_RANDID_' , 'PhpToolCase Random Id Generator' ); // start counting id's
			}
		}
		/**
		* Adds a field to the form object
		* @param	array		$params		parameters for the field
		* @see		PtcForms::$_fields
		* @tutorial	PtcForms.cls#addField
		*/
		public function addElement( $params )
		{
			if ( !@array_key_exists( 'name' , $params ) ) // check if field name has been set
			{
				trigger_error( __CLASS__ . '::' . __FUNCTION__ . '( ) missing "name" for defined field!' ,
														$this->_options[ 'err_msg_level' ] );
				return;
			}
			if ( !@array_key_exists( 'type' , $params ) ){ $params[ 'type' ] = 'text'; } // set "type" if not present
			$field_name = $params[ 'name' ];
			$field_type = $params[ 'type' ];
			$this->_fields[ $field_name ] = array( 'type' => $params[ 'type' ] );
			unset( $params[ 'name' ] );
			unset( $params[ 'type' ] );
			if ( array_key_exists( 'values' , $params ) ) // move "values" to the top
			{
				$params = array_merge( array( 'values' => $params[ 'values' ] ) , $params );
			}
			if ( array_key_exists( 'value' , $params ) ) // move "value" to the top
			{
				$params = array_merge( array( 'value' => $params[ 'value' ] ) , $params );
			}
			if ( array_key_exists( 'attributes' , $params ) ) // move "attributes" before "validate"
			{
				$params = array_merge( array( 'attributes' => $params[ 'attributes' ] ) , $params );
			}
			$this->_fireEvent( 'adding' , array( $field_name , $field_type , &$params , $this ) );
			foreach( $params as $k => $v )
			{
				$v = ( is_callable( $v ) ) ? call_user_func( $v , $this ) : $v;
				if ( $match = preg_match( '/\[.*?\]/' , $k , $matches ) ) // work with []
				{
					/* brackets[] are supported only by select and radio/checkboxgroup */
					if($field_type!='radiogroup' && $field_type!='checkboxgroup' && $field_type!='select')
					{
						trigger_error(__CLASS__.'::'.__FUNCTION__.'() brackets[] not supported for "'.
												$field_type.'" field',$this->_options['err_msg_level']);
						continue;
					}
					/* check if field has values */
					if ( $err = $this->_checkErrors( $field_name , 2 , __FUNCTION__ . '()' ) ){ break; }
					$type = explode( '[' , trim( $k ) );
					/* check storage keys */
					if ( $err = $this->_checkErrors( $type[ 0 ] , 3 , __FUNCTION__ . '( )' ) ){ continue; }
					/* select only supports attributes[] with brackets[] */
					if ( $field_type == 'select' && $type[ 0 ] != 'attributes' )
					{
						trigger_error( __CLASS__ . '::' . __FUNCTION__ . '( )"' . $type[ 0 ] .
								'[]" parameter is not supported for "select" field!' , 
												$this->_options[ 'err_msg_level' ] );
						continue;
					}
					if ( $type[ 1 ] === ']' )
					{
						foreach ( $this->_fields[ $field_name ][ 'values' ] as $key => $arr )
						{
							$this->_addFieldParams( $field_name . '=>' . $key , $type[ 0 ] , $v ); 
						}
						continue;
					}
					else
					{
						$type[ 1 ] = str_replace( ']' , '' , $type[ 1 ] );
						$this->_addFieldParams( $field_name . '=>' . $type[ 1 ] , $type[ 0 ] , $v ); 
						continue;
					}
				}
				if ( $k === 'values' || $k === 'value' ){ $this->_addFieldValues( $field_name , $v ); continue; }
				if ( $k=== 'events' && ( $field_type === 'radiogroup' || $field_type === 'checkboxgroup'))
				{
					/* check if field has values */
					if ( $err = $this->_checkErrors( $field_name , 2 , __FUNCTION__ . '( )' ) ){ continue; }
					foreach ( $this->_fields[ $field_name ][ 'values' ] as $key => $arr )
					{
						$this->_addFieldParams( $field_name . '=>' . $key , $k , $v ); 
					}
					continue;
				}
				if ( $err = $this->_checkErrors( $k , 3 , __FUNCTION__ . '( )' ) ){ continue; }// check storage keys 
				$this->_addFieldParams( $field_name , $k , $v );
			}
			$this->_fields[ $field_name ] = $this->_addDefaultValues( $this->_fields[ $field_name ] );
			if ( 'submit' === $field_type ){ $this->_submit[ ] = $field_name; } 
			$this->_fireEvent( 'added' , array( $field_name , &$this->_fields[ $field_name ] , $this ) );
		}
		/**
		* Adds a spacer div
		* @param	string	$spacerVal	the height for the spacer in px
		* @tutorial	PtcForms.cls#addField.add_spacer
		* @return	returns the html
		*/
		public function addSpacer( $spacerVal = null )
		{
			$spacer_height = ( $spacerVal ) ? $spacerVal : $this->_options[ 'spacer_height' ];
			$spacer_el = str_replace( '{id}','id="ptc-gen' . $this->_randomId( ) . '"' , $this->_htmlTpls[ 'spacer' ] ) . "\n";			
			return $this->_options[ 'start_tab' ] . $spacer_el = str_replace( '{spacerVal}' , $spacer_height , $spacer_el );
		}
		/**
		* Renders the form
		* @param	array	$attributes	form attributes
		* @param	array	$events		form events
		* @tutorial	PtcForms.cls#render
		* @return	the html will be returned if the option "print_form" is set to false, see {@link _defaultOptions}
		*/
		public function render( $attributes = array( ) , $events = array( ) )
		{
			if ( $err = $this->_checkErrors( '' , 5 , __FUNCTION__ . '( )' ) ){ return; }
			$method = $this->_getFormValues( );
			foreach ( $method as $k => $v )
			{
				if ( in_array( $k , $this->_submit ) ){ $this->_fireEvent( 'submit' , array( $k , $this ) ); }
			}
			$main_container = str_replace( '{form_width}' , $this->_options[ 'form_width' ] ,
						"\n" . $this->_options[ 'start_tab' ] . $this->_htmlTpls[ 'main_container' ] );
			$start_tab = $this->_options[ 'start_tab' ];
			$this->_options[ 'start_tab' ] = $this->_options[ 'start_tab' ] . "\t";
			$container = "\n" . $this->_options[ 'start_tab' ] . $this->_htmlTpls[ 'form' ];
			$container = str_replace( '{action}' , 'action="' . $this->_options[ 'form_action' ] . '"' , $container );
			$form_method = strtolower( $this->_options[ 'form_method' ] );
			$container = str_replace( '{method}' , 'method="' . $form_method . '"' , $container );
			$this->_options[ 'start_tab' ] = $this->_options[ 'start_tab' ] . "\t";
			$container = $this->_buildElAttributes( $container );
			$js = '';
			foreach ( $events as $k => $v ){ $js .= $k . '="' . $v . '" '; }
			$container = str_replace( '{events}' , $js_clean = @substr( $js , 0 , -1 ) , $container );
			if ( !array_key_exists( 'id' , $attributes ) ){ $attributes[ 'id' ] = 'ptc-gen' . $this->_randomId( ); }
			foreach ( $attributes as $k => $v )
			{ 
				$container = str_replace( '{' . $k . '}' , $k . '="' . $v . '"' , $container ); 
			}
			$container = preg_replace( '# {.*?}|{.*?}^fields#i' , '' , $container );
			$container = str_replace( ' >{fields}' , '>{fields}' , $container );
			$fields = '';
			foreach ( $this->_fields as $k => $arrV )
			{
				$this->_fireEvent( 'building' , array( $k , &$this->_fields[ $k ] , $this ) );
				$fields .= $this->_buildField( $k ); 
				$this->_fireEvent( 'built' , array( $k , &$fields  , $this ) );
			}
			$container = str_replace( '{fields}' , "\n" . $fields . $start_tab . "\t" , $container );
			$container = str_replace( '{form}' , $container . "\n" . $start_tab , $main_container );
			/* DEBUGGING */
			self::_debug( $this->_options , $attributes[ 'id' ] .' form options' , $this->_options[ 'debug_category' ] );
			self::_debug( $this->_fields , $attributes[ 'id' ] .' form fields' , $this->_options[ 'debug_category' ] );
			if ( @$this->_validate[ 'fields' ] )
			{
				self::_debug( $this->_validate[ 'fields' ] , $attributes[ 'id' ] . ' validator config' ,
													$this->_options[ 'debug_category' ] );
			}
			self::_debug( $this,$attributes[ 'id' ] . ' form object' , $this->_options[ 'debug_category' ] );
			/* END DEBUGGING */
			$container = ( $this->_errMsg ) ? $this->_errMsg . $container : $container;
			$this->_fireEvent( 'rendering' , array( &$container , $this ) );
			if ( $this->_options[ 'print_form' ] ){ print $container . "\n"; }
			else{ return $container . "\n"; }
			$this->_fireEvent( 'rendered' , array( &$container , $this ) );
		}
		/**
		* Validate form fields defined with the "validate" parameter
		* @tutorial	PtcForms.cls#addFieldValidator.validate_form
		* @return	returns the validator fields, isValid(bool) and errors(array) as array keys
		*/
		public function validate( )
		{
			$signature = __CLASS__ . '::' . __FUNCTION__;
			if ( is_array( $this->_validate ) )
			{
				$method = $this->_getFormValues( );
				$errs = array( );
				
				$validate = array( 'errors' => false , 'isValid' => true , 'fields' => $this->_validate );
				foreach ( $validate[ 'fields' ] as $k => $arr )
				{
					foreach ( $arr as $key => $val )
					{
						$this->_fireEvent( 'validating' , array( $key , &$k , &$method ) );
						switch ( $key )
						{
							case 'required' :
								if ( !$is_valid = $this->validateRequired( $k , $method ) )
								{ 
									@$errs[ $key ][ $k ] = 1; 
								}
							break;
							case 'email' :
								if ( !$is_valid = $this->validateEmail( $k , $method ) )
								{ 
									@$errs[ $key ][ $k ] = 1; 
								}
								$this->_fireEvent( 'validated' , array( $k , $method , &$errs ) );
							break;
							case 'number':
								if ( !$is_valid = $this->validateNumber( $k , $method ) )
								{ 
									@$errs[ $key ][ $k ] = 1; 
								}
							break;
							case 'equalTo':
								if ( !$is_valid = $this->validateEqualTo( $k , $val , $method ) )
								{ 
									@$errs[ $key ][ $k ] = 1; 
								}
							break;
							case 'pattern':
								if ( !$is_valid = $this->validatePattern( $k , $val , $method ) )
								{ 
									@$errs[ $key ][ $k ] = 1; 
								}
							break;
							//case "custom":
							default:
								if ( method_exists( $this , $val ) )
								{
									if ( !$is_valid = @call_user_func( array( $this , $val ) ,  $k ) )
									{ 
										@$errs[ $val ][ $k ] = 1; 
									}
								}
								else if ( is_callable( $val ) )
								{
									if ( !$is_valid = @call_user_func( $val , $k ) )
									{ 
										@$errs[ $val ][ $k ] = 1; 
									}
								}
								else
								{ 
									trigger_error( $signature . ' could not run validator "'.
												$val . '"!' , $this->_options[ 'err_msg_level' ] ); 
								}
						}
						$this->_fireEvent( 'validated' , array( $key , $k , &$method , &$errs ) );
					}
				}
				$this->_validate = $validate;
				if ( !empty( $errs ) )
				{
					$this->_validate[ 'errors' ] = $errs; 
					$this->_validate[ 'isValid' ] = false; 
					$this->_fireEvent( 'error' , array( &$this->_validate , &$this->_errMsg , $this ) );
				}
				else{ $this->_fireEvent( 'valid' , array( &$this->_validate , &$this->_errMsg , $this ) ); }
				self::_debug( $this->_validate , 'validator result' , $this->_options[ 'debug_category' ] );
				return $this->_validate;
			}
			trigger_error( $signature . ' no fields to validate found, quitting now!' , $this->_options[ 'err_msg_level' ] );
			return false;
		}
		
		protected $_errMsg = null;
		/**
		* Check if value is empty
		* @param	string	$fieldName	the name of the input field
		* @param	array	$array		array of values to check
		* @return	returns true if value is not empty, otherwise false
		*/
		public function validateRequired( $fieldName , $array )
		{ 
			return ( @strlen( $array[ $fieldName ] ) > 0 ) ? true : false; 
		}
		/**
		* Check if value is valid email
		* @param	string	$fieldName	the name of the input field
		* @param	array	$array		array of values to check
		* @return	returns true if value is a correct email, otherwise false
		*/
		public function validateEmail( $fieldName , $array )
		{
			if ( $this->validateRequired( $fieldName , $array ) )
			{
				// invalid email regex
				$pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";
				return @eregi( $pattern , @$array[ $fieldName ] ) ? true : false; 
			}
			else{ return true; }
		}
		/**
		* Check if value is numeric
		* @param	string	$fieldName	the name of the input field
		* @param	array	$array		array of values to check
		* @return	returns true if value is numeric, otherwise false
		*/
		public function validateNumber( $fieldName , $array )
		{ 
			if ( $this->validateRequired( $fieldName , $array ) )
			{
				return ( @is_numeric( @$array[ $fieldName ] ) ) ? true : false; 
			}
			else{ return true; }
		}
		/**
		* Check if value matches other field value
		* @param	string	$fieldName	the name of the input field
		* @param	string	$matchField	the name of the input field to match
		* @param	array	$array		array of values to check
		* @return	returns true if value is equal to other given value, otherwise false
		*/
		public function validateEqualTo( $fieldName , $matchField , $array )
		{ 
			if ( $this->validateRequired( $fieldName , $array ) )
			{
				return ( @$array[ $fieldName ] == @$array[ $matchField ] ) ? true : false; 
			}
			else{ return true; }
		}
		/**
		* Check if given regex pattern is matched
		* @param	string	$fieldName	the name of the input field
		* @param	string	$pattern		the pattern to match(regex)
		* @param	array	$array		array of values to check
		* @return	returns true if value matches the given pattern, otherwise false
		*/
		public function validatePattern( $fieldName , $pattern , $array )
		{ 
			if ( $this->validateRequired( $fieldName , $array ) )
			{
				return @eregi( $pattern , @$array[ $fieldName ] ) ? true : false; 
			}
			else{ return true; }
		}
		/**
		* Alias of {@link customTpls()}
		*/
		public function customTpl($templates){ $this->customTpls($templates); }
		/**
		* Manipulates html templates for all elements
		* @param	array	$templates	array of html templates
		* @see		PtcForms::$_htmlTpls
		* @tutorial	PtcForms.cls#customTpls
		*/
		public function customTpls( $templates )
		{
			$this->_htmlTpls=array_merge( $this->_htmlTpls , $templates );
		}
		/**
		* Alias of {@link addElAttributes()}
		*/
		public function addElAttribute( $attributes ) { $this->addElAttributes( $attributes ); }
		/**
		* Adds attributes to array of attributes for html elements 
		* @param	array|string	$attributes	array or string to add as attribute/s
		* @see		$_elAttributes
		* @tutorial	PtcForms.cls#addFieldAttributes.add_el_attributes
		*/
		public function addElAttributes( $attributes )
		{
			$attributes = ( @is_array( $attributes ) ) ? $attributes : array( $attributes );
			$this->_elAttributes = array_merge( $this->_elAttributes , $attributes );
		}
		/**
		* Changes label containers default styles
		* @param	array	$labelStyle	ex: "array('float'=>'left','margin'=>'2px 3px 0 0');"
		* @param	int		$num		options(1,2,3)
		* @param	string	$type		"left","right","top"
		* @see		$_labelStyles
		* @tutorial	PtcForms.cls#changeDefaultStyles.setLabelStyle		
		*/
		public function setLabelStyle( $labelStyle , $num , $type = null )
		{ 
			if ( !$type ) { $type = $this->_options[ 'labels_align' ]; }
			$this->_labelStyles[ $num ][ $type ] = array_merge( 
						$this->_labelStyles[ $num ][ $type ] , $labelStyle );
			foreach ( $this->_labelStyles[ $num ][ $type ] as $k => $v )
			{
				if ( false === $v ){ unset($this->_labelStyles[ $num ][ $type ][ $k ]); }
			}
		}
		/**
		* Changes default input fields style
		* @param	array	$style		ex: "array('border'=>'2px inset','padding'=>'5px');"
		* @param	string	$type		"input", "radio" or "button"
		* @see		$_inputStyles	
		* @tutorial	PtcForms.cls#changeDefaultStyles.setInputStyle
		*/
		public function setInputStyle( $style , $type )
		{ 
			$this->_inputStyles[ $type ] = array_merge( $this->_inputStyles[ $type ] , $style );
			foreach ( $this->_inputStyles[ $type ] as $k => $v )
			{
				if ( false === $v ){ unset($this->_inputStyles[ $type ][ $k ]); }
			}
		}
		/**
		* Adds observers to manage form events with the PtcEvent component
		* @param	string	$class	the name of the observer class
		*/
		public function observe( $class = null )
		{
			if ( !class_exists( $event_class = $this->_getEventClass( ) ) )
			{
				trigger_error( $event_class . ' NOT FOUND!' , E_USER_ERROR );
				return false;
			}
			$class = ( $class ) ? $class : get_called_class( );
			$methods = get_class_methods( $class );
			foreach ( $this->_events as $event )
			{
				if ( in_array( $event , $methods ) )
				{
					$cls = strtolower( $class );
					$event_class::listen( $cls . '.' . $event , $class . '::' . $event );
					$this->_observers[ $cls . '.' . $event ] = $event;
				}
			}
		}
		/**
		* Returns options defined for the form
		* @param	string	$name	the option name
		*/
		public function getOptions( $name = null )
		{
			return ( $name ) ? $this->_options[ $name ] : $this->_options;
		}
		/**
		* Adds html before the form container, doesn't have to be an error msg
		* @param	string	$msg	some text
		*/
		public function setErrorMsg( $msg ){ return $this->_errMsg = $msg; }
		/**
		* Class options property, to be merged with {@link $_defaultOptions} property
		* @var	array
		* @see		$_defaultOptions
		*/
		protected $_options = array( );
		/**
		* Default options for the class
		* @var	array
		* @tutorial	PtcForms.cls#class_options	
		*/
		protected $_defaultOptions = array
		(
			'form_method'			=>	'post' ,	// the form method to use
			'form_action'			=>	'#' ,		// the form action url
			'form_width'			=>	'500px' ,	// the width for the main container
			'add_class_validator'	=>	false ,	// add validator classes to fields for use with jquery
			'labels_align'			=>	'left' ,	// align labels globally(left,top,right,none)
			'labels_width'			=>	'40%' ,	// the width for labels as a percentage
			'style_elements'		=>	true ,	// add default style to input elements to align properly
			'style_labels'			=>	true ,	// add default style to label elements to align properly
			'style_tables'			=>	true ,	// add default style to table elements to align properly
			'spacer_height'		=>	'3px' ,	// height for the spacer between fields
			'keep_values'			=>	true ,	// repopulate filled fields on form submission
			'print_form'			=>	true ,	// print form to screen or return html only
			'start_tab'			=>	"\t" ,		// format html code with tabs
			'err_msg_level'			=>	E_USER_WARNING ,	// error messages level
			'debug_category'		=>	'PtcForm' ,	// default category for the PtcDebug class
			'event_class'			=>	'\PtcEvent' 	// event class parameter
		);
		/**
		* Html templates property for all elements
		* @var	array
		* @tutorial	PtcForms.cls#customTpls
		*/
		protected $_htmlTpls = array
		(
			'select_option'		=>	'<option {attributes}>{label}</option>',
			'select'			=>	'<select name="{name}" {attributes}>{options}</select>',
			'textarea'			=>	'<textarea name="{name}" {attributes}>{value}</textarea>',
			'input'			=>	'<input type="{type}" name="{name}" {attributes}>',
			'fieldset'			=>	'<fieldset {attributes}>{label}{data} {start_tab}</fieldset>',
			'form'			=>	'<form {method} {action} {attributes}>{fields}</form>',
			'spacer'			=>	'<div style="clear:both;height:{spacerVal}" {id}><!-- --></div>',
			'label'			=>	'<label {for} {attributes}>{label}</label>',
			'legend'			=>	'<legend {attributes}>{label}</legend>',
			'span'			=>	'<span {attributes}>{label}</span>',
			'td'				=>	'<td align="left" valign="top">',
			'table'			=>	'<table {attributes}>',
			'field_container'	=>	'<div {attributes}>{label}{field} {start_tab}</div>',
			'main_container'	=>	'<div style="width:{form_width}">{form}</div>'
		);
		/**
		*  Default label styles property
		*  @var	array
		* @tutorial	PtcForms.cls#changeDefaultStyles.setLabelStyle
		*/
		protected $_labelStyles = array
		(
			/* Styles for input, select and textarea */
			1	=>	array( 'left' => array( 'float' => 'left' , 'margin' => '2px 0 0 0' , 'width' => '{label_width}%' ) ,
						'right' => array( 'float' => 'left' , 'margin' => '1px 3px 0 0' , //margin:0 3px 0 0;
												'text-align' => 'right' , 'width' => '{label_width}%' ) ,
						'top' => array( ) ) ,
			/* Styles for  checkbox and radio buttons */
			2	=>	array( 'left' => array( 'vertical-align' => 'middle' , 'border' => 'none' ) ,
						'right' => array( 'vertical-align' => 'middle' , 'border' => 'none' ) ,
						'top' => array( ) ) ,
			/* Styles for radio/checkbox group and composite fields */
			3	=>	array( 'left' => array( 'float' => 'left' , 'margin' => '3px 0 0 0' , 'width' => '{label_width}%' ) ,
						'right' => array( 'float' => 'left' , 'margin' => '1px 3px 0 0' ,
												'text-align' => 'right' , 'width' => '{label_width}%' ) ,
						'top' => array( ) )
		);
		/**
		*  Default input styles options property
		*  @var	array
		* @tutorial	PtcForms.cls#changeDefaultStyles.setInputStyle
		*/
		protected $_inputStyles=array
		(
			'radio'	=>	array( 'padding' => '0px' , 'margin' => '0px' , 'vertical-align' => 'middle' , 'width' => '14px' ) ,
			'input'	=>	array( 'margin' => '0px' , 'padding' => '3px' , 'border' => '1px inset' ) , //padding:2px;
			'button'	=>	array( 'margin' => '0px' )
		);
		/**
		* Html attributes for all elements
		* @var	array
		* @tutorial	PtcForms.cls#addFieldAttributes
		*/
		protected $_elAttributes = array
		( 
			'class' , 'id' , 'style' , 'value' , 'maxlength' , 'minlength' ,'cellpadding' , 
			'cellspacing' , 'size' , 'disabled' , 'checked' , 'target' , 'align' , 'events' , 
			'title' , 'selected' , 'cols' , 'rows' , 'equalTo' , 'border' , 'pattern' 
		);
		/**
		* Possible options in fields storage
		* @var	array
		*/
		protected $_storageKeys = array( 'events' , 'attributes' , 'validate' , 'label' , 'labelOptions' , 'parentEl' );
		/**
		* Fields storage property
		* @var	array
		*/
		protected $_fields = array( );
		/**
		* Auto generated hidden fields storage
		* @var	array
		*/
		protected $_hiddenValues = array( );
		/**
		* Build hidden values property
		* @var	bool
		*/
		protected $_buildHidden = true;
		/**
		* Array of fields to validate with the validator engine
		* @var	array
		* @tutorial	PtcForms.cls#addFieldValidator
		*/
		protected $_validate = array( );
		/**
		* Observers property
		*/
		protected $_observers = array( );
		/**
		* Class events property
		*/
		protected $_events = array
		(	
			'rendering' , 'rendered' , 'validating' , 'validated' , 
			'building' , 'built' , 'error' , 'valid' , 'submit' , 'adding' , 'added'
		);
		/**
		* Property that holds the submit buttons names
		*/
		protected $_submit = array( );
		/**
		* Adds values to fields
		* @param	string		$fieldName	the name of the field
		* @param	array|string	$options		value/s to add
		*/
		protected function _addFieldValues( $fieldName , $options )
		{
			if ( $err = $this->_checkErrors( $fieldName , 1 , __FUNCTION__ . "( )" ) ){ return; }
			if ( !is_array( $options ) )
			{
				$options = array( 'value' => $options );
				$this->_addFieldParams( $fieldName , 'attributes' , $options ); 
				return;
			}
			switch( $this->_fields[ $fieldName ][ 'type' ] )
			{
				case 'select' :
				case 'radiogroup' :
				case 'checkboxgroup' :
					foreach ( $options as $k => $v )
					{
						if ( !is_array( $v ) )
						{
							$field_type = str_replace( 'group' , '' , $this->_fields[ $fieldName ][ 'type' ] );
							$v = array( 'type' => $field_type , 'label' => array( $v ) , 
												'attributes' => array( 'value' => $k ) ); 
						}
						$this->_fields[ $fieldName ][ 'values' ][ $k ] = $this->_addDefaultValues( $v );
					}
				break;
				case 'composite' :
				case 'fieldset' :
					$this->_fields[ $fieldName ][ 'values' ] = $options;
					$this->_addCompositeField( $fieldName , $options );
				break;
				default:$this->_addFieldParams( $fieldName , 'values' , $options ); 
				break;
			}
		}
		/**
		* Removes a field from the object
		* @param	string	$fieldName	the name of the field to be removed
		*/
		protected function _removeField( $fieldName ){ unset( $this->_fields[ $fieldName ] ); }
		/**
		* Builds the container for the field
		* @param	string	$fieldName	the name of the field
		* @param	string	$fieldHtml	the html field element
		* @param	string	$labelHtml	the html label element
		* @param	bool		$switch		reverse html label position(for radio/checkbox)
		*/
		protected function _buildContainer( $fieldName , $fieldHtml , $labelHtml = '' , $switch = false )
		{
			$main_container = '';
			/* build container <div> attributes */
			$main_container .= $this->_options[ 'start_tab' ] . $this->_htmlTpls[ 'field_container' ] . "\n";
			$main_container = $this->_buildAttributes( $fieldName , $main_container , 'parentEl' );
			/* build container <div> events */
			//$mainContainer=$this->_buildAttributes($fieldName,$mainContainer,"events");
			$main_container = str_replace( ' {start_tab}' , $this->_options[ 'start_tab' ] , $main_container );
			/* for checkbox or radio only switch field with label */
			if ( $switch ){ $container = str_replace( '{label}{field}' , $fieldHtml . $labelHtml , $main_container ); }
			else{ $container = str_replace( '{label}{field}' , $labelHtml . $fieldHtml , $main_container ); }
			return $container;
		}
		/**
		* Add composite for multiple layouts with html table
		* @param	string	$fieldName	the name of the input field
		* @param	array	$values		array of fields
		*/
		protected function _addCompositeField( $fieldName , $values )
		{
			foreach ( $this->_fields[ $fieldName ][ 'values' ] as $k => $v )
			{
				if ( $err = $this->_checkErrors( $v , 4 , __FUNCTION__ . '( )' ) )
				{
					unset( $this->_fields[ $fieldName ][ 'values' ][ $k ] );
					continue;
				}
				$this->_fields[ $fieldName ][ 'values' ][ $v ] = $this->_fields[ $v ];
				$this->_removeField( $v );
				unset( $this->_fields[ $fieldName ][ 'values' ][ $k ] );
			}
		}
		/**
		* Adds empty default values when addElement() is called
		* @param	string	$array
		* @see	PtcForms::$_storageKeys
		*/
		protected function _addDefaultValues( $array )
		{
			foreach ( $this->_storageKeys as $k => $v )
			{
				if ( !@array_key_exists( $v , $array ) ){ $array[ $v ] = 0; }
			}
			return $array;
		}
		/**
		* Switches between span and label elements according to field type
		* @param	string	$fieldName	the name of the input field
		* @param	string	$labelText	the text for the label
		*/
		protected function _switchLabelEl( $fieldName , $labelText )
		{
			switch ( $this->_fields[ $fieldName ][ 'type' ] )
			{
				case 'radiogroup' :
				case 'checkboxgroup' :
				case 'composite' :
					$label_container = $this->_htmlTpls[ 'span' ];
				break;
				case 'fieldset' :
					$label_container = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
													$this->_htmlTpls[ 'legend' ];
				break;
				default:$label_container = $this->_htmlTpls[ 'label' ];
				break;
			}
			$label_html = str_replace( '{label}' , $labelText , $label_container );
			return $label_html = $this->_buildAttributes( $fieldName , $label_html , 'labelOptions' );
		}
		/**
		* Builds the fields
		* @param	string	$fieldName	the name of the field
		*/
		protected function _buildField($fieldName)
		{
			if($err=$this->_checkErrors($fieldName,1,__FUNCTION__."()")){ return; }
			$label_html='';
			$align_label='none';
			$label_width='';
			$dyn_style='';
			if(@$this->_fields[$fieldName]['label'][0])
			{ 
				$align_label=@array_key_exists('align',$this->_fields[$fieldName]['labelOptions']) ?
							$this->_fields[$fieldName]['labelOptions']['align'] : $this->_options['labels_align'];
				$label_width=@array_key_exists('width',$this->_fields[$fieldName]['labelOptions']) ?
							$this->_fields[$fieldName]['labelOptions']['width'] : $this->_options['labels_width'];
				$label_html=$this->_switchLabelEl($fieldName,$this->_fields[$fieldName]['label'][0]);
			}				
			$spacer_height=@array_key_exists('spacer_height',$this->_fields[$fieldName]['attributes']) ? 
					$this->_fields[$fieldName]['attributes']['spacer_height'] : $this->_options['spacer_height'];
			switch($field_type=$this->_fields[$fieldName]['type'])
			{
				case 'checkbox':
				case 'radio':
					/* add default style to inputs if not set */
					foreach( $this->_inputStyles[ 'radio' ] as $k => $v ){ $dyn_style .= $k . ':' . $v . ';'; }
					$this->_addInputStyle( $fieldName , $dyn_style );
					$label=$this->_buildLabel(2,$align_label,$label_width,$label_html);
					/* add default style to label containers */
					$label_container=$this->_addLabelStyle($fieldName,$label['container'],$label['style']);
					$field=str_replace('{inputField}',$this->_buildHtml($fieldName),$label['input_container']);
					$container=$this->_buildContainer($fieldName,$field,$label_container,$label['switch']);
				break;
				case 'custom':
					$container=$this->_options['start_tab'].$this->_fields[$fieldName]['attributes']['value']."\n";
				break;
				case 'submit':
					$label_style='';
					$input_container="\n".$this->_options['start_tab']."\t".'{inputField}'."\n";
					/* add default style to inputs if not set */
					foreach($this->_inputStyles['button'] as $k=>$v){ $dyn_style.=$k.":".$v.";"; }
					$this->_addInputStyle($fieldName,$dyn_style);
					$label_container='';
					$field=str_replace('{inputField}',$this->_buildHtml($fieldName),$input_container);
					$container=$this->_buildContainer($fieldName,$field,$label_container);	
				break;
				case 'fieldset':
					$data="\n";
					$ori_tab=$this->_options['start_tab'];
					$this->_options['start_tab']=$this->_options['start_tab']."\t";
					foreach($this->_fields[$fieldName]['values'] as $k=>$arr)
					{
						$this->_fields[$k]=$arr;
						$data.=$this->_buildField($k);
						unset($this->_fields[$k]);
					}
					$this->_options['start_tab']=$ori_tab;
					$container=str_replace('{data}',$data,$this->_options['start_tab'].$this->_htmlTpls['fieldset']);
					$container=$this->_buildAttributes($fieldName,$container,'attributes');
					$container=str_replace('{label}',$label_html,$container);
					$container=str_replace(' {start_tab}',$this->_options['start_tab'],$container."\n");
				break;
				case 'checkboxgroup':
				case 'radiogroup':
					$cols=!@array_key_exists('cols',$this->_fields[$fieldName]['attributes']) ? 1 : 
													$this->_fields[$fieldName]['attributes']['cols'];
					/* force </tr> in any case */
					$cols=$cols>sizeof(@$this->_fields[$fieldName]['values']) ? 
							sizeof(@$this->_fields[$fieldName]['values']) : $cols;
					$this->_fields['group_now']=$this->_fields[$fieldName];
					unset($this->_fields[$fieldName]);
					$label=$this->_buildLabel(3,$align_label,$label_width,$label_html);
					$this->_addTableStyle('group_now',$label['table_style']);
					$table_container=$this->_buildAttributes('group_now',$this->_htmlTpls['table'],"attributes");
					$table=$this->_buildTableData($cols,1,$fieldName,
												@$this->_fields['group_now']['values'],$table_container);
					$this->_fields[$fieldName]=$this->_fields['group_now'];
					unset($this->_fields['group_now']);
					$field="\n".$this->_options['start_tab']."\t<div>{table}\t".$this->_options['start_tab']."</div>\n";
					$field=str_replace('{table}',$table,$field);
					/* add default style to label containers */
					$label_container=$this->_addLabelStyle($fieldName,$label['container'],$label['style']);
					$container=$this->_buildContainer($fieldName,$field,$label_container);
				break;
				case 'composite':
					$field_attributes=$this->_fields[$fieldName]['attributes'];
					$cols=!@array_key_exists('cols',$field_attributes) ? 
							sizeof($this->_fields[$fieldName]['values']) : $field_attributes['cols'];
					/* force </tr> in any case */
					$cols=$cols>sizeof($this->_fields[$fieldName]['values']) ? 
											sizeof($this->_fields[$fieldName]['values']) : $cols;
					$label=$this->_buildLabel(3,$align_label,$label_width,$label_html);
					$this->_addTableStyle($fieldName,$label['table_style']);
					$table_container=$this->_buildAttributes($fieldName,$this->_htmlTpls['table'],'attributes');
					$table=$this->_buildTableData($cols,2,$fieldName,
												$this->_fields[$fieldName]['values'],$table_container);
					$field="\n".$this->_options['start_tab']."\t<div>{table}\t".
														$this->_options['start_tab']."</div>\n";
					$field=str_replace('{table}',$table,$field);
					/* add default style to label containers */
					$label_container=$this->_addLabelStyle($fieldName,$label['container'],$label['style']);
					$container=$this->_buildContainer($fieldName,$field,$label_container);
					//$spacer_height="0px";
				break;
				default:
					/* add default style to inputs if not set */
					foreach($this->_inputStyles['input'] as $k=>$v){ $dyn_style.=$k.":".$v.";"; }
					$this->_addInputStyle($fieldName,$dyn_style);//padding:2px;
					$label=$this->_buildLabel(1,$align_label,$label_width,$label_html);
					/* add default style to label containers */
					$label_container=$this->_addLabelStyle($fieldName,$label['container'],$label['style']);
					$field=str_replace('{inputField}',$this->_buildHtml($fieldName),$label['input_container']);
					/* fix \t in the </div> of select field */
					if($this->_fields[$fieldName]['type']=='select')
					{
						$field=str_replace('</div>',$this->_options['start_tab']."\t</div>",$field); 
					}	
					$container=$this->_buildContainer($fieldName,$field,$label_container);
				break;
			}
			$container.=str_replace('{spacerVal}',$spacer_height,$this->addSpacer()); 
			$container=preg_replace('# {.*?}|{.*?}#i','',$container); // clean up
			return $container;
		}
		/**
		* Builds a dynamic table for multiple layouts
		* @param	string	$cols			number of columns
		* @param	string	$type			the table type(1,2)
		* @param	string	$fieldName		the name of the field
		* @param	array	$data			the values for the table
		* @param	string	$container		the html table template
		*/
		protected function _buildTableData($cols,$type,$fieldName,$data,$container)
		{
			if(!$data){ return; }	// if no values are set for the field
			$table="\n".$this->_options['start_tab']."\t\t".$container."\n";
			$table.=$this->_options['start_tab']."\t\t\t".'<tr>';
			$a=1;
			$b=1;
			$ori_build_hidden=$this->_buildHidden;
			foreach($data as $k=>$arr)
			{
				$opts_spacer_height=$this->_options['spacer_height'];
				$key=($type==1) ? $fieldName : $k; 
				$this->_fields[$key]=$arr;
				$opts_start_tab=$this->_options['start_tab'];
				$this->_options['start_tab']=$this->_options['start_tab']."\t\t\t\t\t";
				$this->_options['spacer_height']='0px';
				if($b>1 && $type==1){ $this->_buildHidden=false; }
				$method=$this->_getFormValues();
				if($this->_options['keep_values'] && !empty($this->_hiddenValues) && !empty($method))
				{
					if(isset($method[str_replace('[]','',$fieldName)]))
					{
						$keep_vals=$method[str_replace('[]','',$fieldName)];
						if(@is_array($keep_vals))
						{
							foreach($keep_vals as $k=>$v)
							{
								if(@$arr['attributes']['value']==$v)
								{ 
									$this->_addFieldParams($key,'attributes',array('checked'=>1));
									$new_method_arr=$method;
									$new_method_arr[str_replace('[]','',$fieldName)]=$v;
									$this->_editFormValues($new_method_arr);
								}
							}
						}
						$keep_vals='';
					}
				}
				$td_content=$this->_buildField($key); 
				$this->_editFormValues($method);
				$this->_options['start_tab']=$opts_start_tab;
				$this->_options['spacer_height']=$opts_spacer_height;
				$table.="\n".$this->_options['start_tab']."\t\t\t\t".$this->_htmlTpls['td'];
				$table.="\n".$td_content.$this->_options['start_tab']."\t\t\t\t".'</td>';
				if($a==$cols)
				{
					$table.="\n".$this->_options['start_tab']."\t\t\t".'</tr>';
					$a=1;
					if($b<sizeof($data)){ $table.="\n".$this->_options['start_tab']."\t\t\t".'<tr>'; }
				}
				else{ $a++; }
				$b++;
				unset($this->_fields[$key]);
			}
			$final=($cols*ceil(sizeof($data)/$cols));    
			if(sizeof($data)<$final)
			{
				for($z=sizeof($data);$z<$final;$z++)
				{ 
					$table.="\n".$this->_options['start_tab']."\t\t\t\t".$this->_htmlTpls['td']."&nbsp;</td>"; 
				}
				$table.="\n".$this->_options['start_tab']."\t\t\t".'</tr>';
			}
			$this->_buildHidden=$ori_build_hidden;
			return $table.="\n".$this->_options['start_tab']."\t\t".'</table>'."\n";
		}
		/**
		* Adds parameters to the fields
		* @param	string		$fieldName	the name of the field
		* @param	array		$type		('events','attributes','validate','label','labelOptions','parentEl','value/s')
		* @param	array|string	$options		the options to pass
		*/
		protected function _addFieldParams($fieldName,$type,$options)
		{
			$options=is_array($options) ? $options : array($options);
			$name=explode('=>',$fieldName);
			$a=sizeof($name);
			$exclude_types=array('composite','fieldset'); // exclude from validate
			switch($a)
			{
				case 1:
					if(!@$this->_fields[$fieldName])
					{ 
						trigger_error(__CLASS__.'::'.__FUNCTION__.' could not add '.$type.
										' for field '.$fieldName,E_USER_WARNING); return;
					}
					if($type=='validate')
					{ 
						if(in_array(@$this->_fields[$name[0]]['type'],$exclude_types))
						{
							trigger_error(__CLASS__.'::'.__FUNCTION__.' could not add validator to field '.$fieldName.
									', '.@$this->_fields[$name[0]]['type'].' type not supported!',E_USER_WARNING); 
							return;
						}
						$this->_addValidator($name[0],$options);
						$this->_addClassValidator($name[0],$options,@$this->_fields[$name[0]]['type']);
					}
					if ( @array_key_exists( 'class' , $this->_fields[ $name[ 0 ] ][ 'attributes' ] ) && 
							$type == 'attributes' && @array_key_exists( 'class' , $options ) )
					{ 
						$class=$this->_fields[$name[0]]['attributes']['class'].' '.$options['class'];
						$this->_fields[$name[0]]['attributes']['class']=trim($class);
						return;
					}
					if(!@is_array($this->_fields[$fieldName][$type])){ $this->_fields[$fieldName][$type]=$options; }
					else{ $this->_fields[$fieldName][$type]=array_merge($this->_fields[$fieldName][$type],$options); }
				break;
				case 2:
					if ( !@array_key_exists( $name[ 1 ] , $this->_fields[ $name[ 0 ] ][ 'values' ] ) )
					{ 
						trigger_error(__CLASS__.'::'.__FUNCTION__.' could not find fieldname '.$fieldName,
																				E_USER_WARNING); 
						return;
					}
					if ( $type == 'validate' )
					{ 
						if ( in_array( @$this->_fields[ $name[ 0 ] ][ 'values' ][ $name[ 1 ] ][ 'type' ] , $exclude_types ) )
						{
							trigger_error(__CLASS__.'::'.__FUNCTION__.' could not add validator to field '.$fieldName.
								', field type '.@$this->_fields[$name[0]]['values'][$name[1]]['type'].' not supported!',
																					E_USER_WARNING);
							return;
						}
						
						
						if ( $this->_fields[$name[0]][ 'type' ] == 'checkboxgroup' || 
								$this->_fields[$name[0]][ 'type' ] == 'radiogroup' /*&& 
										!array_key_exists( $name[ 0 ] , $this->_validate*/ )
						{
							$this->_addValidator( $name[ 0 ] , $options );
						}
						else{ $this->_addValidator( $name[ 1 ] , $options ); }
						
						
						if ( $this->_fields[$name[0]]['values'][$name[1]]['type']=='radiogroup' || 
							$this->_fields[$name[0]]['values'][$name[1]]['type']=='checkboxgroup')
						{
							foreach($this->_fields[$name[0]]['values'][$name[1]]['values'] as $k=> $v)
							{
								$this->_fields[$k.'_temp']=$v;
								$this->_addClassValidator($k.'_temp',$options);
								$this->_fields[$name[0]]['values'][$name[1]]['values'][$k]=$this->_fields[$k.'_temp'];
								unset($this->_fields[$k.'_temp']);
							}
						}
						else{ $this->_addClassValidator($fieldName,$options); }
					} 
					if(@array_key_exists('class',$this->_fields[$name[0]]['values'][$name[1]]['attributes']) && 
											$type=='attributes' && @array_key_exists('class',$options))
					{ 
						$class=$this->_fields[$name[0]]['values'][$name[1]]['attributes']['class'].' '.$options['class'];
						$this->_fields[$name[0]]['values'][$name[1]]['attributes']['class']=trim($class);
						return;
					}
					if(!@is_array($this->_fields[$name[0]]['values'][$name[1]][$type]))
					{ 
						$this->_fields[$name[0]]['values'][$name[1]][$type]=$options; 
					}
					else
					{ 
						$options=array_merge($this->_fields[$name[0]]['values'][$name[1]][$type],$options);
						$this->_fields[$name[0]]['values'][$name[1]][$type]=$options; 
					}
				break;
			}
			return $this->_fields[$name[0]];
		}
		/**
		* Adds validation to the input field
		* @param	string		$fieldName	the name of the field
		* @param	array|string	$options		the options to pass
		*/
		protected function _addValidator( $fieldName , $options )
		{
			$options = is_array( $options ) ? $options : array( $options );
			foreach ( $options as $k => $v )
			{
				if ( is_numeric( $k ) )
				{
					$options[ $v ] = $v;
					unset( $options[ $k ] );
				}
			}
			if ( !@array_key_exists( $fieldName , $this->_validate ) )
			{
				$this->_validate[ str_replace( '[]' , '' , $fieldName ) ] = $options;
			}
			else{ array_merge( $this->_validate[ $fieldName ] , $options ); }
		}
		/**
		* Adds validator classes to the fields for js validation
		* @param	string		$fieldName	the name of the field
		* @param	array|string	$options		the options to pass
		* @param	string		$fieldType	used by checkbox and radio groups only
		*/
		protected function _addClassValidator( $fieldName , $options , $fieldType = 'default' )
		{
			if($this->_options['add_class_validator'])
			{
				foreach($options as $k=>$v)
				{ 
					/* equalTo[matchFieldName] */
					if($k==='equalTo')
					{ 
						$this->_addFieldParams($fieldName,'attributes',array($k=>$v));
						$v=$k.'['.$v.']';
					}
					if($fieldType=='radiogroup' || $fieldType=='checkboxgroup')
					{
						if(!@$this->_fields[$fieldName]['values']){ break; }
						foreach($this->_fields[$fieldName]['values'] as $key=>$arr)
						{
							$this->_addFieldParams($fieldName.'=>'.$key,'attributes',array('class'=>$v));
						}
					}
					else{ $this->_addFieldParams( $fieldName , 'attributes' , array( 'class' => $v ) ); }
				}
			}
		}
		/**
		* Adds default styles to fields to align properly
		* @param	string	$fieldName	the name of the field
		* @param	string	$fieldStyle	the style property
		*/
		protected function _addInputStyle( $fieldName , $fieldStyle )
		{
			if ( $this->_options[ 'style_elements' ] )
			{
				if ( @array_key_exists( 'style' , $this->_fields[ $fieldName ][ 'attributes' ] ) ){ return; }
				if ( !is_array( $this->_fields[ $fieldName ][ 'attributes' ] ) )
				{ 
					$this->_fields[ $fieldName ][ 'attributes' ] = array( ); 
				}
				$this->_fields[ $fieldName ][ 'attributes' ][ 'style' ] = $fieldStyle; 
			}
		}
		/**
		* Adds default style to the label container to align properly
		* @param	string	$fieldName		the name of the field
		* @param	string	$labelContainer	the html template for label element
		* @param	string	$style			the style property
		*/
		protected function _addLabelStyle( $fieldName , $labelContainer , $style )
		{
			$label_style = '';
			if ( !@array_key_exists( 'style' , $this->_fields[ $fieldName ][ 'labelOptions' ] ) && 
													$this->_options[ 'style_labels' ] )
			{ 
				$label_style = $style; 
			}					
			$label_html = str_replace( ' {label_style}' , $label_style .
								' id="ptc-gen' . $this->_randomId( ) .'"' , $labelContainer );
			$this->_addElementId( $fieldName , 'attributes' );
			return $label_html = str_replace( '{for}' , 'for="' . 
							$this->_fields[ $fieldName ][ 'attributes' ][ 'id' ] . '"' , $label_html );
		}
		/**
		* Adds default style to the table to align properly
		* @param	string	$fieldName	the name of the field
		* @param	string	$tableStyle	the style property
		*/
		protected function _addTableStyle( $fieldName , $tableStyle )
		{
			if ( $this->_options[ 'style_tables' ] )
			{ 
				if ( !@is_array( $this->_fields[ $fieldName ][ 'attributes' ] ) )
				{ 
					$this->_fields[ $fieldName ][ 'attributes' ] = array( ); 
				}
				if ( !@array_key_exists( 'style' , $this->_fields[ $fieldName ][ 'attributes' ] ) )
				{ 
					$this->_fields[ $fieldName ][ 'attributes' ][ 'style' ] = $tableStyle; 
				}
				if ( !@array_key_exists( 'cellpadding' , $this->_fields[ $fieldName ][ 'attributes' ] ) )
				{
					$this->_fields[ $fieldName ][ 'attributes' ][ 'cellpadding' ] = 0; 
				}
				if ( !@array_key_exists( 'cellspacing' , $this->_fields[ $fieldName ][ 'attributes' ] ) )
				{
					$this->_fields[ $fieldName ][ 'attributes' ][ 'cellspacing' ] = 0; 
				}
			}
		}
		/**
		* Compiles {@link _elAttributes} with the template {attributes}
		* @param	string	the html element
		*/
		protected function _buildElAttributes( $container )
		{
			$chain_attributes = '';
			foreach ( $this->_elAttributes as $k => $v ){ $chain_attributes .= '{' . $v . '} '; }
			return $container = str_replace( '{attributes}' , 
										substr( $chain_attributes , 0 , -1 ) , $container );
		}
		/**
		* Builds attributes for html elements
		* @param	string	$fieldName	the name of the field
		* @param	string	$container	the html template for container
		* @param	string	$arrKey		(events,attributes,validate,label,labelOptions,parentEl)	
		*/
		protected function _buildAttributes( $fieldName , $container , $arrKey )
		{
			$container = $this->_buildElAttributes( $container );
			if ( $arrKey == 'parentEl' || $arrKey == 'attributes')
			{ 
				$this->_addElementId( $fieldName , $arrKey ); 
			}
			if ( @is_array( $this->_fields[ $fieldName ][ $arrKey ] ) )
			{
				if ( $arrKey == 'events' )
				{
					$events = '';
					foreach ( $this->_fields[ $fieldName ][ $arrKey ] as $k => $v )
					{
						$events .= $k . '="' . $v . '" '; 
					}
					$events = substr( $events , 0 , -1 ); // remove last space from events chain
					return $container = str_replace( '{events}' , $events , $container );
				}
				$this->_fields[ $fieldName ][ $arrKey ] = 
									array_filter( $this->_fields[ $fieldName ][ $arrKey ] , 'strlen' );
				foreach ( $this->_fields[ $fieldName ][ $arrKey ] as $k => $v )
				{
					$container = str_replace( '{' . $k . '}' , $k . '="' . $v . '"' , $container ); 
				}
			}
			return $container;
		}
		/**
		* Adds an id to all html elements
		* @param	string	$fieldName	the name of element
		* @param	string	$arrKey		the key inside the {@link _fields} array
		*/
		protected function _addElementId($fieldName,$arrKey)
		{
			if(!@array_key_exists('id',$this->_fields[$fieldName][$arrKey]))
			{
				if(!@is_array($this->_fields[$fieldName][$arrKey]))
				{ 
					$this->_fields[$fieldName][$arrKey]=array(); 
				}
				$this->_fields[$fieldName][$arrKey]['id']='ptc-gen'.$this->_randomId();
			}
		}
		/**
		* Builds html select options
		* @param	string	$fieldName	the name of the select field
		*/
		protected function _buildList($fieldName)
		{

			if(@$this->_fields[$fieldName]['values'])
			{
				$options='';
				foreach(@$this->_fields[$fieldName]['values'] as $k => $arrV)
				{
					$option=$this->_buildElAttributes($this->_htmlTpls['select_option']);
					if(@$arrV['label'][0]){ $option=str_replace('{label}',$arrV['label'][0],$option); }
					foreach(@$arrV['attributes'] as $k=>$v){ $option=str_replace('{'.$k.'}',$k.'="'.$v.'"',$option); }
					$options.=preg_replace('# {.*?}|{.*?}#i','',"\n".$this->_options['start_tab']."\t\t\t".$option);// clean up
				}
				$select_field=str_replace('{options}',$options."\n",$this->_htmlTpls['select']);
				$select_field="\n".$this->_options['start_tab']."\t\t".$select_field."\n";
				return $select_field=str_replace('</select>',$this->_options['start_tab']."\t\t</select>",$select_field);
			}
		}
		/**
		* Builds container for the field
		* @param	string	$fieldName	the name of the field
		*/
		protected function _buildHtml( $fieldName )
		{
			$this->_rebuildValues( $fieldName ); // rebuild field values if form has been sent already
			switch( $this->_fields[ $fieldName ][ 'type' ] )
			{
				case 'textarea':
					$html_field = $this->_htmlTpls[ 'textarea' ];
					if ( isset( $this->_fields[ $fieldName ][ 'attributes' ][ 'value' ] ) )
					{
						$html_field = str_replace( '{value}' , 
									$this->_fields[ $fieldName ][ 'attributes' ][ 'value' ] , $html_field ); 
					}
				break;
				case 'select':$html_field = $this->_buildList( $fieldName );
				break;
				case 'checkbox':
				case 'radio':
					if ( $this->_options[ 'keep_values' ] && $this->_buildHidden )
					{
						$hidden_field = str_replace( '{type}' , 'hidden' , $this->_htmlTpls[ 'input' ] ) . "\n" .
																$this->_options[ 'start_tab' ] . "\t";
						$html_field = str_replace( '{name}' , str_replace( '[]' , '' , $fieldName ) . '_' .
													mt_rand( 1000 , 9999 ) . '_ptcgen' , $hidden_field );
						$html_field = preg_replace( '# {.*?}|{.*?}#i' , '' , $html_field ); // clean up
					}
					@$html_field .= str_replace( '{type}' , 
								$this->_fields[ $fieldName ][ 'type' ] , $this->_htmlTpls[ 'input' ] );
				break;
				default:$html_field = str_replace( '{type}' , 
								$this->_fields[ $fieldName ][ 'type' ] , $this->_htmlTpls[ 'input' ] );
				break;
			}
			$html_field = str_replace( '{name}' , $fieldName , $html_field );
			$html_field = $this->_buildAttributes( $fieldName , $html_field , 'attributes' );
			return $html_field = $this->_buildAttributes( $fieldName , $html_field , 'events' );
		}
		/**
		* Rebuilds values for the fields if form has been sent
		* @param	string	$fieldName	the name of the field
		*/
		protected function _rebuildValues( $fieldName )
		{
			if ( !@array_key_exists( 'noAutoValue' , @$this->_fields[ $fieldName ][ 'attributes' ] ) )
			{
				$method = $this->_getFormValues( );
				if ( $this->_options[ 'keep_values' ]  && !empty( $method ) )
				{
					switch ( $this->_fields[ $fieldName ][ 'type' ] )
					{
						case 'checkbox' :
						case 'radio' :
							if ( !@$this->_fields[ $fieldName ][ 'attributes' ][ 'value' ] )
							{
								$this->_fields[ $fieldName ][ 'attributes' ][ 'value' ] = 'on';
							}
							unset( $this->_fields[ $fieldName ][ 'attributes' ][ 'checked' ] );
							foreach ( $this->_hiddenValues  as $k => $v )
							{
								if ( @array_key_exists( $k , $method ) && 
									$k == str_replace( '[]' , '' , $fieldName ) &&  
									@$method[ $k ] == $this->_fields[ $fieldName ][ 'attributes' ][ 'value' ] )
								{
									$this->_addFieldParams( $fieldName , 'attributes' , array( 'checked' => 1 ) );
								}
							}
						break;
						case 'textarea' :
							unset( $this->_fields[ $fieldName ][ 'attributes' ][ 'value' ] );
							if ( @strlen( $method[ $fieldName ] ) > 0 )
							{
								$this->_addFieldValues( $fieldName , @$method[ $fieldName ] ); 
							}
						break;
						case 'select' :
							foreach ( @$this->_fields[ $fieldName ][ 'values' ] as $k => $arrV )
							{
								unset( $this->_fields[ $fieldName ][ 'values' ][ $k ][ 'attributes' ][ 'selected' ] );
								if ( @$method[ $fieldName ] == $k )
								{ 
									$this->_addFieldParams( $fieldName . '=>' . $k , 
																'attributes' , array( 'selected' => 1 ) );
								}
							}
						break;
						default :
							unset( $this->_fields[ $fieldName ][ 'attributes' ][ 'value' ] );
							if ( @strlen( $method[ $fieldName ] ) > 0 )
							{ 
								$this->_addFieldValues( $fieldName , @$method[ $fieldName ] ); 
							}
					}
				}
			}
		}
		/**
		* Builds the label for field
		* @param	string	$case		(1,2,3)
		* @param	string	$alignLabel	("left","top","right","none")
		* @param	string	$labelWidth	the width of the label as a percentage
		* @param	string	$labelHtml	the html template
		*/
		protected function _buildLabel( $case , $alignLabel , $labelWidth , $labelHtml )
		{
			$label_width = str_replace( '%' , '' , $labelWidth );
			$table_width = ( 99 - $label_width );
			$label = array( );
			if ( $case == 1 ) // build label input,select and textarea fields
			{
				switch ( $alignLabel )
				{
					case 'left' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
											'<div {label_style}>' . $labelHtml . '</div>';
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
													'<div {id}>{inputField}</div>' . "\n";
					break;
					case 'top'  :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
												'<div {label_style}>' . $labelHtml . '</div>';
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
														'<div {id}>{inputField}</div>'."\n";
					break;
					case 'right' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
												'<div {label_style}>' . $labelHtml . '</div>';
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
														'<div {id}>{inputField}</div>'."\n";
					break;
					case 'none' :
						$label[ 'container' ] = ''; 
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
														'<div {id}>{inputField}</div>' . "\n";	
					break;
				}
				$label[ 'input_container' ] = str_replace( "{id}" , 'id="ptc-gen' . 
											$this->_randomId( ) . '"' , $label[ 'input_container' ] );
			}
			else if ( $case == 2 ) // build label for checkbox and radio buttons
			{
				switch ( $alignLabel )
				{
					case 'left' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
											'<span {label_style}>' . $labelHtml . '</span>';
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . 
																"\t" . '{inputField}' . "\n";
						$label[ 'switch' ] = false;
					break;
					case 'top' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
												'<div {label_style}>' . $labelHtml . '</div>';
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
														'<div {id}>{inputField}</div>' . "\n";
						$label[ 'switch' ] = false;
					break;
					case 'right' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
											'<span {label_style}>' . $labelHtml . '</span>' . "\n";
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . '{inputField}';
						$label[ 'switch' ] = true;
					break;
					case 'none' :
						$label[ 'container' ] = ''; 
						$label[ 'input_container' ] = "\n" . $this->_options[ 'start_tab' ] . 
															"\t" . '{inputField}' . "\n";
						$label[ 'switch' ] = false;
					break;
				}
				$label[ 'input_container' ] = str_replace( "{id}" , 'id="ptc-gen' . 
									$this->_randomId( ) . '"' , $label[ 'input_container' ] );
			}
			else if ( $case == 3 ) // build label for radio/checkbox group and composite fields
			{
				switch ( $alignLabel )
				{
					case 'left' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
												'<div {label_style}>' . $labelHtml . '</div>';
						$label[ 'table_style' ] = "width:" . $table_width . "%;";
					break;
					case 'top' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
												'<div {label_style}>' . $labelHtml . '</div>';
						$label[ 'table_style' ] = "width:100%;";
					break;
					case 'right' :
						$label[ 'container' ] = "\n" . $this->_options[ 'start_tab' ] . "\t" . 
												'<div {label_style}>' . $labelHtml . '</div>';
						$label[ 'table_style' ] = "width:" . ( $table_width - 1 ) . "%;";
					break;
					case 'none' :
						$label[ 'container' ] = '';
						$label[ 'table_style' ] = "width:100%;";						
					break;
				}
			}
			if ( !@array_key_exists( 'style' , $label ) && @$this->_labelStyles[ $case ][ $alignLabel ] )
			{
				$label[ 'style' ] = ' style="';
				foreach ( $this->_labelStyles[ $case ][ $alignLabel ] as $k => $v )
				{ 
					$label[ 'style' ] .= $k . ':' . $v . ';'; 
				}
				$label[ 'style' ] = str_replace( '{label_width}' , $label_width , $label[ 'style' ] ) . '"';
			}
			else{ $label[ 'style' ] = ''; }
			return $label;
		}
		/**
		* Checks for errors while building and rendering the form
		* @param	string	$fieldName	the name of the field
		* @param	string	$type		the check type (1,2,3,4,5)
		* @param	string	$function		which function called this process
		* @param	string	$errType		the error type		
		*/
		protected function _checkErrors( $fieldName , $type , $function = null , $errType = null )
		{
			$errType = ( !$errType ) ? $this->_options[ 'err_msg_level' ] : $errType;
			$signature = ( !$function ) ? __CLASS__ . ' ' : __CLASS__ . '::' . $function . ' ';
			$debug_msg = '';
			switch( $type )
			{
				case 1 :	// test fieldname
					if ( !@$this->_fields[ $fieldName ] )
					{ 
						$debug_msg = 'could not find field ' . $fieldName;
						trigger_error( $signature . $debug_msg , $errType ); 
						return true;
					}
				break;
				case 2 :	// test field values
					if ( !@$this->_fields[ $fieldName ][ 'values' ] )
					{
						$debug_msg = 'could not find values for ' . $fieldName;
						trigger_error( $signature . $debug_msg , $errType ); 
						return true;
					}
				break;
				case 3 :	// test storage keys
					if ( !in_array( $fieldName , $this->_storageKeys ) )
					{
						$debug_msg = '"' . $fieldName . '"  is not a valid type';
						trigger_error( $signature . $debug_msg , $errType ); 
						return true;
					}
				break;
				case 4 :	// test composite value
					if ( !@$this->_fields[ $fieldName ] )
					{
						$debug_msg = 'could not find field ' . $fieldName . ' to add to composite';
						trigger_error( $signature . $debug_msg , $errType ); 
						return true;
					}
				break;
				case 5 :	// no fields in $_fields parameter
					if ( empty( $this->_fields ) )
					{
						$debug_msg = 'no fields defined, quitting now!';
						trigger_error( $signature . $debug_msg , $errType ); 
						return true;
					}
				break;
			}
			return false;
		}
		/**
		* Retrieves form values from POST or GET
		*/
		protected function _getFormValues( )
		{
			switch ( strtolower( $this->_options[ 'form_method' ] ) )
			{
				case 'get': return $_GET; break;
				default: return $_POST; break;
			}
		}
		/**
		* Manipulates form values from POST or GET
		* @param	array	$array	array of values(POST or GET)
		*/
		protected function _editFormValues( $array )
		{
			switch ( strtolower( $this->_options[ 'form_method' ] ) )
			{
				case 'get' : $_GET = $array; break;
				default: $_POST = $array; break;
			}
		}
		/**
		* Increases number of random generated id for elements
		*/
		protected function _randomId( ) 
		{ 
			return @$_SESSION[ '_PTC_RANDID_' ] = ( @$_SESSION[ '_PTC_RANDID_' ] + 1 ); 
		}
		/**
		* Fires events with the event class
		* @param	string	$event	the name of the event
		* @param	mixed	$data	the data to push
		*/
		protected function _fireEvent( $event , $data )
		{
			$event = ( is_array( $event ) ) ? $event : array( $event );
			$event_class = $this->_getEventClass( );
			if ( !empty( $this->_observers ) )
			{
				foreach ( $this->_observers as $k => $v )
				{
					foreach ( $event as $ev )
					{
						if ( $v === $ev ){ $event_class::fire( $k , $data ); }
					}
				}
			}
		}	
		/**
		* Returns the event component name
		*/		
		protected function _getEventClass( )
		{
			return __NAMESPACE__ . $this->_options[ 'event_class' ]; 
		}		
		/**
		* Send messsages to the PtcDebug class if present and it\'s namespace
		* @param 	mixed 	$string		the string to pass
		* @param 	mixed 	$statement	some statement if required
		* @param	string	$category	a category for the messages panel
		*/
		protected static function _debug( $string , $statement = null , $category = null )
		{
			if ( !defined( '_PTCDEBUG_NAMESPACE_' ) ) { return false; }
			return @call_user_func_array( array( '\\' . _PTCDEBUG_NAMESPACE_ , 'bufferLog' ) ,  
											array( $string , $statement , $category ) );
		}
	}