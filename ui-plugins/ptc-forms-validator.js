/* 
* JQUERY VALIDATOR DEFAULT OPTIONS FOR PTCFORMS.PHP CLASS
* http://bassistance.de/jquery-plugins/jquery-plugin-validation/
*/

var $=jQuery;

$.validator.setDefaults
 ({
	//debug: true,
	errorClass:"ui", /* replace namespace for custom classes -state-error and -state-error-text */
	errorPlacement:function(error,element) 
	{
		var elem=$(element);
		if(!error.is(':empty')) 
		{
			elem.filter(':not(.valid)').qtip
			({
				content:error,show:{event:'mouseover'},hide:{event:'mouseout'},
				position:{my:'top center',at:'bottom center',viewport:$(window)},         
				style:{classes:'ui-tooltip-rounded ui-tooltip-shadow',widget:true}
			}).qtip('option','content.text',error);
		}
		else{elem.qtip('destroy');}
	},
	success:$.noop,
	highlight:function(element,errorClass,validClass)
	{
		if(element.type=='radio' || element.type=='checkbox')      // if it's a radio or checkbox, highlight every option
		{
			$.each($("input[name='"+element.name+"']:not(:disabled)"),function(i,j) 
			{
				field=$(j.form);
				field.find("label[for="+j.id+"]").addClass(errorClass+'-state-error-text').removeClass(validClass).bind
				({
					hover:function(event){$("input[name='"+element.name+"']").trigger('mouseenter');},
					mouseout:function(event){$("input[name='"+element.name+"']").trigger('mouseleave');}
				});
				field.find("label[for="+j.id+"]").parent().addClass(errorClass+'-state-error').removeClass(validClass);
				field.find("input[name='"+element.name+"']").addClass(errorClass+'-state-error').removeClass(validClass);
				field.find("input[name='"+element.name+"']").parent().next("span").addClass(errorClass+'-state-error').removeClass(validClass);
			});
		} 
		else{$(element).addClass(errorClass+'-state-error').removeClass(validClass);}
	},
	unhighlight:function(element,errorClass,validClass) 
	{
		if(element.type=='radio' || element.type=='checkbox')      // if it's a radio or checkbox, unhighlight every option
		{
			$.each($("input[name='"+element.name+"']"),function(i,j)
			{
				field=$(j.form);
				field.find("label[for="+j.id+"]").removeClass(errorClass+'-state-error-text').addClass(validClass);
				field.find("label[for="+j.id+"]").parent().removeClass(errorClass+'-state-error').addClass(validClass);
				field.find("input[name='"+element.name+"']").removeClass(errorClass+'-state-error').addClass(validClass);
				field.find("input[name='"+element.name+"']").parent().next("span").removeClass(errorClass+'-state-error').addClass(validClass);
			});
		} 
		else{$(element).removeClass(errorClass+'-state-error').addClass(validClass);} 
	},
	messages:
	{
		required:"This field is required.",
		remote:"Please fix this field.",
		email:"Please enter a valid email address.",
		url:"Please enter a valid URL.",
		date:"Please enter a valid date.",
		dateISO:"Please enter a valid date (ISO).",
		number:"Please enter a valid number.",
		digits:"Please enter only digits.",
		creditcard:"Please enter a valid credit card number.",
		equalTo:"Please enter the same value again.",
		accept:"Please enter a value with a valid extension.",
		maxlength:jQuery.validator.format("Please enter no more than {0} characters."),
		minlength:jQuery.validator.format("Please enter at least {0} characters."),
		rangelength:jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
		range:jQuery.validator.format("Please enter a value between {0} and {1}."),
		max:jQuery.validator.format("Please enter a value less than or equal to {0}."),
		min:jQuery.validator.format("Please enter a value greater than or equal to {0}.")
	}
});
$(document).ready(function()
{
	/* Replaced equalTo method to look for a field name instead of an id to work with the PtcForms equalTo method */
	$.validator.addMethod("equalTo",function(value,element,param)
	{
		return (value==$("input[name="+param+"]").val()) ? true : false;
	});
	/* Field default value validator, add "dafault-value='val'" to the input field as an attribute */
	$.validator.addMethod("defaultValue",function(value,element) 
	{
		return (value==$(element).attr("default-value")) ?  false : true;
        },$.validator.messages.required);
});