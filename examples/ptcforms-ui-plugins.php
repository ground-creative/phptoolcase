<?
	/* THIS FILE IS NEED FOR THE JAVASCRIPT TO WORK CORRECTLY
	*  FOR VALIDATOR THE FILE ptc-forms-validator.js IS REQUIRED
	*  FOR UI STYLES THE FILE ptc-forms-ui.js IS REQUIRED
	*  THE REST IS ALL CDN BASED
	*/

	echo '
		<!-- JQUERY-UI CSS //-->
		<link type="text/css" href="http://code.jquery.com/ui/1.8.23/themes/start/jquery-ui.css" rel="stylesheet" />
		<!-- Jquery CORE //-->
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script> 
		<!-- JQUERY-UI CORE //-->
		<script type="text/javascript" src="http://code.jquery.com/ui/1.8.23/jquery-ui.min.js"></script>
		<!-- VALIDATOR AND TOOLTIP PLUGIN FOR CLIENT SIDE VALIDATION //-->
		<link type="text/css" href="http://cdnjs.cloudflare.com/ajax/libs/qtip2/2.0.0/jquery.qtip.min.css" rel="stylesheet" />
		<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/qtip2/2.0.0/jquery.qtip.min.js"></script>
		<script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.min.js"></script>
		<script type="text/javascript" src="../ui-plugins/ptc-forms-validator.js"></script>
		<!-- PLUGIN TO STYLE FORM FIELDS AUTOMATICALLY WITH JQUERY-UI //-->
		<script src="../ui-plugins/ptc-forms-ui.js" type="text/javascript"></script>
	';
	echo '<script>
			$(document).ready(function()
			{

				$("form").form();	// add ui styles
				$("form").validate();	// validate client side
				if($(".errMsg").text())	// add ui to error msgs
				{
					$(".errMsg").html(\'<span class="ui-state-error-text">\'+$(".errMsg").text()+\'</span>\').
																addClass("ui-state-error ui-corner-all");
				}
			});
		</script>';
?>