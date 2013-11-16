<?php

        /* 
        * LOGIN-FORM EXAMPLE FOR PTCFORMS.PHP CLASS
        * REMOVE COMMENT FROM LINE 43 FOR UI-PLUGINS
        */

        ### PARAMATERS FOR THE EXAMPLE ###############
        $redirect_page="http://www.google.com/";         // the landing page if login was successfull
        $user="test";                                                        // login username
        $pass=md5("test1");                                        // login password
        #########################################
        
        @session_start();
        
        /*  CHECK IF COOKIE IS SET FOR THE REMEMBER ME OPTION */
        if(@$_COOKIE['cookname'] && @$_COOKIE['cookpass'])
        {
                $_POST['lg_username']=$_COOKIE['cookname'];
                $_POST['lg_password']=$_COOKIE['cookpass'];
                if($valid_login=check_login()){ $_SESSION['loggedIn']=true; }
        }
        if(@$_SESSION['loggedIn']){ header('Location: '.$redirect_page); exit(); }

        $_SESSION['loggedIn']=false;
                
        /* INITIALIZE THE CLASS WITH SOME OPTIONS */
        $options=array
        (
                'add_class_validator'        =>        true,        // adding js validation
                'keep_values'                        =>        false,        // remove posted values from the inputs
                'labels_align'                        =>        'right',        // align labels right
                'form_width'                        =>        '415px',        // form width in pixels
                'spacer_height'                =>        '10px;'        // spacer height between fields
        );
        
        require_once('../PtcForm.php');
        $form=new PtcForm($options);
        
        echo'<!DOCTYPE html><html><head>';
        
        /* (OPTIONAL) INCLUDE JS FOR JQUERY VALIDATOR AND STYLES */
        //require_once("ptcforms-ui-plugins.php");
        
        /* MINIMAL CSS FOR THE EXAMPLE */
        echo'<style>
                        body{font:normal .85em "trebuchet ms",arial,sans-serif;color:#555;}
                        #loginForm input[type=text], input[type=password]{width:237px;}
                        .errMsg{color:red;}
                </style>';
        
        /* ADDING A TEXT FIELD */
        $form->addElement(array
        (
                'name'        =>        'lg_username',
                'label'        =>        'Username:',
                'validate'        =>        'required'
        ));
        
        /* ADDING A PASSWORD FIELD */
        $form->addElement(array
        (
                'type'        =>        'password',
                'name'        =>        'lg_password',
                'label'        =>        'Password:',
                'validate'        =>        array('required','check_login')
        ));
        
        /* ADDING A CHECKBOX */
        $checkbox=array
        (
                'type'        =>        'checkbox',
                'name'        =>        'lg_keep_login',
                'label'        =>        'Remember me',
                'parentEl'        =>        array('style'=>'text-align:right;')
        );
        /* ADDING CHECKED AS ATTRIBUTE IF FORM WAS SENT */
        if(isset($_POST['lg_keep_login'])){ $checkbox['attributes']=array('checked'=>true); }
        $form->addElement($checkbox);

        /* ADDING A SUBMIT BUTTON */
        $form->addElement(array
        (
                'type'                =>        'submit',
                'name'                =>        'lg_login',
                'value'                =>        'Login',
                'parentEl'                =>        array('style'=>'text-align:right;margin-right:5px;'),
                'attributes'        =>        array('style'=>false) // remove default style for this field only
        ));
        
        /* COMPOSITE FOR CUSTOM LAYOUTS */
        $form->addElement(array
        (
                'type'                =>        'composite',
                'name'                =>        'lg_login_box',
                'values'                =>        array('lg_keep_login','lg_login'),                 // add previously created fields
                'attributes'        =>        array('style'=>'width:265px;float:right;')         // add some style attributes
        ));
                
        $err_msg='';
        if(@$_POST['lg_login'])
        {
                /* VALIDATE THE FORM SERVER SIDE */
                $validate=$form->validate();        
                if(!$validate['isValid'])                        // returns a bool(true/false)
                {
                        $err_msg='<div class="errMsg" style="text-align:center;width:'.$options['form_width'].'">
                                                                                                                Wrong user ID or password!</div><br>';
                }
                else
                {
                        $_SESSION['loggedIn']=true;
                        if(@$_POST['lg_keep_login'])        // working with the remember me option
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
        $form->render(array('id'=>'loginForm'));        
        
        echo'</body></html>';
        
        /* CHECK IF USER CAN LOGIN, A CUSTOM FUNCTION ADDED TO THE SERVER SIDE VALIDATOR */
        function check_login()
        {
                ### THE FOLLOWING LINES COULD BE REPLACED WITH A SQL QUERY ############
                global $user,$pass;                
                return ($_POST['lg_username']!=$user || @md5($_POST['lg_password'])!=$pass) ? false : true;
                ###########################################################
        }
        
        /* USE THIS FUNCTION IN THE HEADER OF THE PAGES IN THE PROTECTED AREA 
        *  TO CHECK IF USER HAS ACCESS
        */
        /*function check_user()
        {
                if(!@$_SESSION['loggedIn'])
                { 
                        header('Location: login.php');                 // replace login.php with the full path to the login page
                        exit(); 
                }
        }*/