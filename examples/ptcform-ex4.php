<?php

	/* 
	* CONTACT FORM EXAMPLE FOR PTCFORMS.PHP CLASS WITH EVENT HANDLERS
	* THIS EXAMPLES REQUIRES THE PTCEVENT COMPONENT
	* REMOVE COMMENT FROM LINE 144 FOR UI-PLUGINS
	*/

	### PARAMETERS FOR THE EXAMPLE ##################
	$email_address = 'me@example.com'; 
	$mail_subject = 'TEST CONTACT FORM';
	#########################################
	
	require_once( '../PtcForm.php' );
	require_once( '../PtcEvent.php' );

	/* EXTENDING THE CLASS TO HANDLE EVENTS */
	class contactForm extends PtcForm
	{
		/* OVERRIDING DEFAULT OPTIONS PROPERTY */
		protected $_options = array
		(
			'add_class_validator'	=>	true ,
			'form_width'		=>	'400px' ,
			'labels_align'		=>	'right' ,
			'spacer_height'		=>	'10px'
		);

		/* USING THE BOOT METHOD TO ADD EVENT LISTENERS TO THE CLASS */
		public function boot( )
		{
			$this->observe( );  // we need to initialize the event listners
		}
		
		/* METHOD THAT WILL BUILD THE FIELDS , WILL BE CALLED BY CONSTRUCTOR */
		public function formFields( )
		{					
			/* ADDING A TEXT FIELD */
			$this->addElement( array
			(
				'name'	=>		'ct_firstname',
				'label'		=>		'Firstname:*',
				'validate'	=>		'required'
			) );

			/* ADDING A TEXT FIELD */
			$this->addElement( array
			(
				'name'	=>		'ct_lastname',
				'label'		=>		'Lastname:*',
				'validate'	=>		'required'
			) );
			
			/* ADDING A TEXT FIELD */
			$this->addElement( array
			(
				'name'	=>		'ct_email',
				'label'		=>		'Your email:*',
				'validate'	=>		array( 'required' , 'email' )
			) );
			
			/* ADDING A TEXT FIELD */
			$this->addElement( array
			(
				'name'	=>		'ct_phone',
				'label'		=>		'Your phone:*',
				'validate'	=>		'required'
			) );
			
			/* ADDING A SELECT FIELD */
			$this->addElement( array
			(
				'type'		=>	'select' ,
				'name'	=>	'ct_reason' ,
				'label'		=>	'Contact reason:*' ,
				'values'	=>	array
							( 
								'' 			=> 'Choose' , 
								'enquiry' 		=> 'Enquiry' , 
								'information' 	=> 'Information' , 
								'billing' 		=> 'Billing' , 
								'other' 		=> 'Other' 
							) ,
				'validate'	=>	'required'
			) );
			
			/* ADDING A TEXTAREA FIELD */
			$this->addElement( array
			(
				'type'		=>		'textarea' ,
				'name'		=>		'ct_message' ,
				'label'		=>		'Write message:*' ,
				'attributes'	=>		array( 'rows' => 7 ) ,
				'validate'		=>		'required'
			) );
			
			/* ADDING A SUBMIT BUTTON */
			$this->addElement( array
			(
				'type'		=>	'submit' ,
				'name'		=>	'ct_contact_me' ,
				'value'		=>	'Submit' ,
				'parentEl'		=>	array( 'style' => 'text-align:right;' )
			) );
		}
		
		/* OBSERVER EVENTS, STATIC METHODS ARE USED, COULD BE IN A SEPARATE CLASS FILE */
		public static function submit( $fieldName , $obj ) // form submit event, run validator here
		{ 
			$obj->validate( ); 
		} 
		public static function error( $result , $errMsg , $obj ) // form is not valid, add an error msg
		{
			$errMsg = '<div class="errMsg" style="text-align:center;width:' . $obj->getOption( 'form_width' ) . 
										'">Something went wrong. Please review the form!</div><br>';
		}
		public static function valid( $result , $msg , $obj ) // form is valid, let's redirect the user to the login area
		{
			global $email_address , $mail_subject;
			$mail_body = "Firstname: " . $_POST[ 'ct_firstname' ] . "\n";
			$mail_body .= "Lastname: " . $_POST[ 'ct_lastname' ] . "\n";
			$mail_body .= "Email: " . $_POST[ 'ct_email' ] . "\n";
			$mail_body .= "Phone: " . $_POST[ 'ct_phone' ] . "\n";
			$mail_body .= "Reason: " . $_POST[ 'ct_reason' ] . "\n";
			$mail_body .= "Message: " . $_POST[ 'ct_message' ] . "\n";
			mail( $email_address , $mail_subject , $mail_body );
			$msg = '<div class="errMsg" style="text-align:center;width:' . $obj->getOption( 'form_width' ) . 
															'">Form has been sent!</div><br>';
		}
		public static function rendering( $container , $obj ) // before render event
		{
			// do something before the html is printed
			$title = '<div style="text-align:center;width:' . $obj->getOption( 'form_width' ) . 
												'"><h1>Contact Form</h1></div>';
			$container = $title . $container;
		}
	}
	
	$form = new ContactForm( );
	
	echo '<!DOCTYPE html><html><head>';
	
	/* (OPTIONAL) INCLUDE JS FOR JQUERY VALIDATOR AND UI STYLES  PLUGINS */
	//require_once("ptcforms-ui-plugins.php");
	
	/* MINIMAL CSS FOR THE EXAMPLE */
	echo '<style>
		body{font:normal .85em "trebuchet ms",arial,sans-serif;color:#555;}
		input[type=text], select, textarea{width:220px;}
		.errMsg{color:red;}
	</style>';
	echo'</head><body>';	

	/* FINALLY RENDER THE FORM */
	$form->render( );
		
	echo '</body></html>';
