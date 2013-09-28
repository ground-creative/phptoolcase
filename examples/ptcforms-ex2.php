<?php

	/* 
	* CONTACT FORM EXAMPLE FOR PTCFORMS.PHP CLASS
	* REMOVE COMMENT FROM LINE 28 FOR UI-PLUGINS
	*/

	### PARAMATERS FOR THE EXAMPLE ################
	$email_address='me@example.com'; 
	$mail_subject='TEST CONTACT FORM';
	#########################################

	/* INITIALIZE THE CLASS WITH SOME OPTIONS */
	$options=array
	(
		'add_class_validator'	=>	true,
		'form_width'			=>	'400px',
		'labels_align'			=>	'right',
		'spacer_height'		=>	'10px',
	);
	
	require_once('../PtcForms.php');
	$form=new PtcForms($options);
	
	echo '<!DOCTYPE html><html><head>';
	
	/* (OPTIONAL) INCLUDE JS FOR JQUERY VALIDATOR AND UI STYLES  PLUGINS */
	//require_once("ptcforms-ui-plugins.php");
	
	/* MINIMAL CSS FOR THE EXAMPLE */
	echo'<style>
		body{font:normal .85em "trebuchet ms",arial,sans-serif;color:#555;}
		input[type=text], select, textarea{width:220px;}
		.errMsg{color:red;}
	</style>';
	
	/* ADDING A TEXT FIELD */
	$form->addElement(array
	(
		'name'	=>		'ct_firstname',
		'label'	=>		'Firstname:*',
		'validate'	=>		'required'
	));

	/* ADDING A TEXT FIELD */
	$form->addElement(array
	(
		'name'	=>		'ct_lastname',
		'label'	=>		'Lastname:*',
		'validate'	=>		'required'
	));
	
	/* ADDING A TEXT FIELD */
	$form->addElement(array
	(
		'name'	=>		'ct_email',
		'label'	=>		'Your email:*',
		'validate'	=>		array('required','email')
	));
	
	/* ADDING A TEXT FIELD */
	$form->addElement(array
	(
		'name'	=>		'ct_phone',
		'label'	=>		'Your phone:*',
		'validate'	=>		'required'
	));
	
	/* ADDING A SELECT FIELD */
	$values=array(''=>'Choose','enquiry'=>'Enquiry','information'=>'Information',
										'billing'=>'Billing','other'=>'Other');
	$form->addElement(array
	(
		'type'	=>	'select',
		'name'	=>	'ct_reason',
		'label'	=>	'Contact reason:*',
		'values'	=>	$values,
		'validate'	=>	'required'
	));
	
	/* ADDING A TEXTAREA FIELD */
	$form->addElement(array
	(
		'type'		=>		'textarea',
		'name'		=>		'ct_message',
		'label'		=>		'Write message:*',
		'attributes'	=>		array('rows'=>7),
		'validate'		=>		'required'
	));
	
	/* ADDING A SUBMIT BUTTON */
	$form->addElement(array
	(
		'type'		=>	'submit',
		'name'		=>	'ct_contact_me',
		'value'		=>	'Submit',
		'parentEl'		=>	array('style'=>'text-align:right;')
	));

	$err_msg='';
	$sent=false;
	if(isset($_POST['ct_contact_me']))
	{
		$validate=$form->validate();	// validate the form
		if(!$validate['isValid'])
		{
			$err_msg='<div class="errMsg" style="text-align:center;width:'.$options['form_width'].'">
									Something went wrong. Please review the form!</div><br>';
		}
		else	/* form is valid, let's build the email and send it */
		{	
			$mail_body="Firstname: ".$_POST['ct_firstname']."\n";
			$mail_body.="Lastname: ".$_POST['ct_lastname']."\n";
			$mail_body.="Email: ".$_POST['ct_email']."\n";
			$mail_body.="Phone: ".$_POST['ct_phone']."\n";
			$mail_body.="Reason: ".$_POST['ct_reason']."\n";
			$mail_body.="Message: ".$_POST['ct_message']."\n";
			mail($email_address,$mail_subject,$mail_body);
			echo "Form has been sent...";
			$sent=true;
		}
	}
	
	echo'</head><body>';	

	if(!$sent)
	{
		echo '<div style="text-align:center;width:'.$options['form_width'].'"><h1>Contact Form</h1></div>';
		echo $err_msg;
		
		/* FINALLY RENDER THE FORM */
		$test=$form->render();
	}
	echo'</body></html>';
