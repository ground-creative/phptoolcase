<?	
	/* 
	* LOGIN-FORM EXAMPLE FOR PTCFORMS.PHP CLASS
	* REMOVE COMMENT FROM LINE 41 FOR UI-PLUGINS
	*/

	### PARAMATERS FOR THE EXAMPLE ###############
	$redirect_page="http://www.google.com/"; 	# the landing page if login was successfull
	$user="test";							# login username
	$pass=md5("test1");					# login password
	#########################################
	
	@session_start();
	
	/*  CHECK IF COOKIE IS SET FOR THE REMEMBER ME OPTION */
	if(@$_COOKIE['cookname'] && @$_COOKIE['cookpass'])
	{
		$_POST['lg_username']=$_COOKIE['cookname'];
		$_POST['lg_password']=$_COOKIE['cookpass'];
		if($valid_login=chek_login()){ $_SESSION['loggedIn']=true; }
	}
	if(@$_SESSION['loggedIn']){ header('Location: '.$redirect_page); exit(); }

	$_SESSION['loggedIn']=false;
		
	/* INITIALIZE THE CLASS WITH SOME OPTIONS */
	$options=array
	(
		"add_class_validator"	=>	true,	# adding js validation
		"keep_values"			=>	false,	# remove posted values from the inputs
		"labels_align"			=>	"right",	# align labels right
		"form_width"			=>	"410px",	# form width in pixels
		"spacer_height"		=>	"10px;"	# spacer height between fields
	);
	require_once('../PtcForms.php');
	$ptc=new PtcForms($options);
	
	echo'<html><head>';
	
	/* (OPTIONAL) INCLUDE JS FOR JQUERY VALIDATOR AND STYLES */
	//require_once("ptcforms-ui-plugins.php");
	
	/* MINIMAL CSS FOR THE EXAMPLE */
	echo'<style>
			body{font:normal .85em "trebuchet ms",arial,sans-serif;color:#555;}
			#loginForm input[type=text], input[type=password]{width:237px;}
			.errMsg{color:red;}
		</style>';
	
	/* ADDING A TEXT FIELD */
	$ptc->addField('text','lg_username');	
	$ptc->addFieldLabel('lg_username','Username:');
	$ptc->addFieldValidator('lg_username','required');
	
	/* ADDING A PASSWORD FIELD */
	$ptc->addField('password','lg_password');	
	$ptc->addFieldLabel('lg_password','Password:');
	$ptc->addFieldValidator('lg_password',array('required',"custom"=>"check_login"));
	
	/* ADDING A CHECKBOX */
	$ptc->addField("checkbox","lg_keep_login");
	$ptc->addFieldLabel("lg_keep_login","Remember me");
	$ptc->fieldParentEl('lg_keep_login',array("style"=>"text-align:right;"));
	if(isset($_POST['lg_keep_login'])){ $ptc->addFieldAttributes("lg_keep_login",array("checked"=>true)); }

	/* ADDING A SUBMIT BUTTON */
	$ptc->addField("submit","lg_login");	
	$ptc->addFieldValue('lg_login','Login');
	$ptc->fieldParentEl('lg_login',array("style"=>"text-align:right;margin-right:5px;"));
	$ptc->addFieldAttributes('lg_login',array("style"=>false));	# remove default style for this field only
	
	/* COMPOSITE FOR CUSTOM LAYOUTS */
	$ptc->addField('composite','lg_login_box');
	$ptc->addFieldValues('lg_login_box',array("lg_keep_login",'lg_login'));	# add previously created fields
	$fld_style=array("style"=>"width:265px;float:right;");
	$ptc->addFieldAttributes('lg_login_box',$fld_style);	# add some style attributes
		
	$err_msg="";
	if(@$_POST['lg_login'])
	{
		/* VALIDATE THE FORM SERVER SIDE */
		$validate=$ptc->validate();	
		if(!$validate['isValid'])			# returns a bool(true/false)
		{
			$err_msg='<div class="errMsg" style="text-align:center;width:'.$options['form_width'].'">
														Wrong user ID or password!</div><br>';
		}
		else
		{
		        $_SESSION['loggedIn']=true;
			if(@$_POST['lg_keep_login'])	# working with the remember me option
			{
				setcookie("cookname",$user,time()+60*60*24*100, "/");
				setcookie("cookpass",$pass,time()+60*60*24*100, "/");
			}
			header('Location: '.$redirect_page); exit();
		}
	}
	echo '</head><body>';
	echo '<div style="text-align:center;width:'.$options['form_width'].'"><h1>Login Form</h1></div>';
	echo $err_msg;
	
	/* FINALLY RENDER THE FORM */
	$ptc->render(array("id"=>"loginForm"));	
	
	echo'</body></html>';
	
	/* CHECK IF USER CAN LOGIN, A CUSTOM FUNCTION ADDED TO THE SERVER SIDE VALIDATOR */
	function check_login()
	{
		### THE FOLLOWING LINES COULD BE REPLACED WITH A SQL QUERY ############
		global $user,$pass;		
		return ($_POST['lg_username']!=$user || @md5($_POST['lg_password'])!=$pass) ? false : true;
		###########################################################
	}
	
	# USE THIS FUNCTION IN THE HEADER OF THE PAGES IN THE PROTECTED AREA
	# TO CHECK IF USER HAS ACCESS
	/*function check_user()
	{
		if(!@$_SESSION['loggedIn'])
		{ 
			header('Location: login.php'); 		# replace login.php with the full path to the login page
			exit(); 
		}
	}*/
?>