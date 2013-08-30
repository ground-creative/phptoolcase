<?
	/*
	* REGISTRATION FORM EXAMPLE FOR PTCFORMS.PHP CLASS
	* REMOVE COMMENT FROM LINE 20 FOR UI-PLUGINS
	*/
	
	/* INITIALIZE THE CLASS WITH SOME OPTIONS */	
	$options=array
	(
		'add_class_validator'	=>	true,
		'form_width'			=>	'500px',
		'spacer_height'		=>	'10px;',
	);
	require_once('../PtcForms.php');
	$form=new PtcForms($options);
	
	echo'<!DOCTYPE html><html><head>';
		
	/* (OPTIONAL) INCLUDE JS FOR JQUERY VALIDATOR AND STYLES */
	//require_once("ptcforms-ui-plugins.php");
	
	/* MINIMAL CSS FOR THE EXAMPLE */
	echo'<style>
			body{font:normal .85em "trebuchet ms",arial,sans-serif;color:#555;}
			input[type=text], input[type=password]{width:220px;}
			.errMsg{color:red;}
		</style>';

	/* ADDING A SPACER */
	$form->addElement(array
	(
		'type'	=>		'custom',
		'name'	=>		'spacer1',
		'value'	=>		$form->addSpacer('3px')
	));

	/* ADDING A TEXT FIELD */
	$form->addElement(array
	(
		'name'	=>		'reg_name',
		'label'	=>		'Username:*',
		'validate'	=>		'required'
	));
	
	/* ADDING A PASSWORD FIELD */
	$form->addElement(array
	(
		'type'	=>		'password',
		'name'	=>		'reg_password',
		'label'	=>		'Password:*',
		'validate'	=>		'required'
	));
	
	/* ADDING A PASSWORD FIELD */
	$form->addElement(array
	(
		'type'	=>		'password',
		'name'	=>		'reg_password1',
		'label'	=>		'Confirm Password:*',
		'validate'	=>		array('required','equalTo'=>'reg_password')
	));
	
	/* ADDING A TEXT FIELD */
	$form->addElement(array
	(
		'type'	=>		'text',
		'name'	=>		'reg_email',
		'label'	=>		'Email Address:*',
		'validate'	=>		array("required","email")
	));
	
	/* ADDING A FIELDSET AS CONTAINER FOR THE PREVIOUS FIELDS */
	$form->addElement(array
	(
		'type'		=>		'fieldset',
		'name'		=>		'reg_fieldset',
		'label'		=>		'User Registration Form',
		'values'		=>		array('spacer1','reg_name','reg_password','reg_password1','reg_email'),
		'attributes'	=>		array('style'=>'padding:10px;')
	));
	
	/* ADDING A RADIOGROUP */
	$form->addElement(array
	(
		'type'			=>		'radiogroup',
		'name'			=>		'reg_newsletter',
		'values'			=>		array('yes'=>'Yes !!! (please)','no'=>'No (thank you)'),
		'labelOptions[]'	=>		array('align'=>'right'),	// align labels right
		'attributes[yes]'	=>		array('checked'=>true),	// set 1 value checked
		'validate'			=>		'required'
	));
	
	/* ADDING A SPACER */
	$form->addElement(array
	(
		'type'	=>		'custom',
		'name'	=>		'spacer2',
		'value'	=>		$form->addSpacer('1px')
	));
	
	/* ADDING A FIELDSET AS CONTAINER FOR THE PREVIOUS FIELDS */
	$form->addElement(array
	(
		'type'		=>		'fieldset',
		'name'		=>		'reg_fieldset1',
		'label'		=>		'Signup for our newsletter',
		'values'		=>		array('spacer2','reg_newsletter'),
		'attributes'	=>		array('style'=>'padding:10px;')
	));
	
	/* ADDING A SUBMIT BUTTON */
	$form->addElement(array
	(
		'type'		=>	'submit',
		'name'		=>	'reg_send',
		'value'		=>	'Register',
		'parentEl'		=>	array('style'=>'text-align:right;')
	));
	
	echo'</head><body>
	<div id="switcher"></div>';
	$ok=false;
        $err_msg='';
	if(isset($_POST['reg_send']))
	{
		$validate=$form->validate();	// validate the form with php
		if(!$validate['isValid'])
		{
			$err_msg='<div class="errMsg" style="text-align:center;width:'.$options['form_width'].'">
									Something went wrong. Please review the form!</div><br>';
		}
		else	/* register new user we could use a sql query here */
		{
		        $ok=true;
			echo '<span>New account created!</span>';
		}
	}
        if(!$ok)
	{ 
		echo '<div><h1>New User Registration</h1></div>';
		echo $err_msg;
		$form->render();
	}
	echo'</body></html>';
?>