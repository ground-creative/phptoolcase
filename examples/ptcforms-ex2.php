<?
	/* 
	* CONTACT FORM EXAMPLE FOR PTCFORMS.PHP CLASS
	* REMOVE COMMENT FROM LINE 27 FOR UI-PLUGINS
	*/

	### PARAMATERS FOR THE EXAMPLE ################
	$email_address="me@example.com"; 
	$mail_subject="TEST CONTACT FORM";
	#########################################

	/* INITIALIZE THE CLASS WITH SOME OPTIONS */
	$options=array
	(
		"add_class_validator"	=>	true,
		"form_width"			=>	"400px",
		"labels_align"			=>	"right",
		"spacer_height"		=>	"10px",
	);
	
	require_once('../PtcForms.php');
	$ptc=new PtcForms($options);
	
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
	$ptc->addField('text','ct_firstname');
	$ptc->addFieldLabel('ct_firstname','Firstname:*');
	$ptc->addFieldValidator('ct_firstname','required');

	/* ADDING A TEXT FIELD */
	$ptc->addField('text','ct_lastname');
	$ptc->addFieldLabel('ct_lastname','Lastname:*');
	$ptc->addFieldValidator('ct_lastname','required');
	
	/* ADDING A TEXT FIELD */
	$ptc->addField('text','ct_email');
	$ptc->addFieldLabel('ct_email','Your email:*');
	$ptc->addFieldValidator('ct_email',array('required','email'));
	
	/* ADDING A TEXT FIELD */
	$ptc->addField('text','ct_phone');
	$ptc->addFieldLabel('ct_phone','Your phone:*');
	$ptc->addFieldValidator('ct_phone','required');
	
	/* ADDING A SELECT FIELD */
	$ptc->addField('select','ct_reason');
	$ptc->addFieldLabel('ct_reason','Contact reason:*');
	$ptc->addFieldValues('ct_reason',array(""=>"Choose","enquiry"=>"Enquiry",
				"information"=>"Information","billing"=>"Billing","other"=>"Other"));
	$ptc->addFieldValidator('ct_reason','required');
	
	/* ADDING A TEXTAREA FIELD */
	$ptc->addField('textarea','ct_message');
	$ptc->addFieldLabel('ct_message','Write a message:*');
	$ptc->addFieldAttributes('ct_message',array("rows"=>"7"));
	$ptc->addFieldValidator('ct_message','required');
	
	/* ADDING A SUBMIT BUTTON */
	$ptc->addField('submit','ct_contact_me');
	$ptc->addFieldValue('ct_contact_me','Submit');
	$ptc->fieldParentEl('ct_contact_me',array("style"=>"text-align:right;"));

	$err_msg="";
	$sent=false;
	if(isset($_POST['ct_contact_me']))
	{
		$validate=$ptc->validate();	# validate the form
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
		$test=$ptc->render();
	}
	echo'</body></html>';
?>