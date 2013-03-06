<?
	/*
	* REGISTRATION FORM EXAMPLE FOR PTCFORMS.PHP CLASS
	* REMOVE COMMENT FROM LINE 20 FOR UI-PLUGINS
	*/
	
	/* INITIALIZE THE CLASS WITH SOME OPTIONS */	
	$options=array
	(
		"add_class_validator"	=>	true,
		"form_width"			=>	"500px",
		"spacer_height"		=>	"10px;",
	);
	require_once('../PtcForms.php');
	$ptc=new PtcForms($options);
	
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
	$ptc->addField("custom","spacer1");
	$ptc->addFieldValue("spacer1",$ptc->addSpacer("3px"));

	/* ADDING A TEXT FIELD */
	$ptc->addField("text","reg_name");
	$ptc->addFieldLabel("reg_name","Username:*");
	$ptc->addFieldValidator("reg_name","required");
	
	/* ADDING A PASSWORD FIELD */
	$ptc->addField("password","reg_password");
	$ptc->addFieldLabel("reg_password","Password:*");
	$ptc->addFieldValidator("reg_password","required");
	
	/* ADDING A PASSWORD FIELD */
	$ptc->addField("password","reg_password1");
	$ptc->addFieldLabel("reg_password1","Confirm Password:*");
	$ptc->addFieldValidator("reg_password1",array("required","equalTo"=>"reg_password"));
	
	/* ADDING A TEXT FIELD */
	$ptc->addField("text","reg_email");
	$ptc->addFieldLabel("reg_email","Email Address:*");
	$ptc->addFieldValidator("reg_email",array("required","email"));
	
	/* ADDING A FIELDSET AS CONTAINER FOR THE PREVIOUS FIELDS */
	$ptc->addField("fieldset","reg_fieldset");
	$ptc->addFieldLabel("reg_fieldset","User Registration Form");
	$ptc->addFieldValues("reg_fieldset",array("spacer1","reg_name","reg_password","reg_password1","reg_email"));
	$ptc->addFieldAttributes("reg_fieldset",array("style"=>"padding:10px;"));
	
	/* ADDING A RADIOGROUP */
	$ptc->addField("radiogroup","reg_newsletter");
	$ptc->addFieldValues("reg_newsletter",array("yes"=>"Yes !!! (please)","no"=>"No (thank you)"));
	$ptc->addValuesParams("reg_newsletter","labelOptions",array("align"=>"right"));
	$ptc->addFieldValidator("reg_newsletter","required");
	$ptc->addFieldAttributes("reg_newsletter=>yes",array("checked"=>true));
	
	/* ADDING A SPACER */
	$ptc->addField("custom","spacer2");
	$ptc->addFieldValue("spacer2",$ptc->addSpacer("1px"));
	
	/* ADDING A FIELDSET AS CONTAINER FOR THE PREVIOUS FIELDS */
	$ptc->addField("fieldset","reg_fieldset1");
	$ptc->addFieldLabel("reg_fieldset1","Signup for our newsletter");
	$ptc->addFieldValues("reg_fieldset1",array("spacer2","reg_newsletter"));
	$ptc->addFieldAttributes("reg_fieldset1",array("style"=>"padding:10px;"));
	
	/* ADDING A SUBMIT BUTTON */
	$ptc->addField("submit","reg_send");
	$ptc->addFieldValue("reg_send","Register");
	$ptc->fieldParentEl("reg_send",array("style"=>"text-align:right"));
	
	echo'</head><body>
	<div id="switcher"></div>';
	$ok=false;
        $err_msg="";
	if(isset($_POST['reg_send']))
	{
		$validate=$ptc->validate();	# validate the form with php
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
		$ptc->render();
	}
	echo'</body></html>';
?>