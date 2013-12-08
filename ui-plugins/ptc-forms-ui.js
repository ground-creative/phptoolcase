/*!
* UI-FORM PLUGIN TO CREATE JQUERY-UI FORM FIELDS 
* Version: 0.1
* Jquery Version: 1.8
* Copyright (c) 2013 Irony
* Licensed under the MIT license.
* http://phptoolcase.com/
*
* usage: $("form").form(); // see options paramater for options
* for a select with scroll, add a height to the ul tag like this: #parent-element-id ul{height:200px;}
*
* Todo: 
*     	- add fields array to be able to control field options individually
*	- add fields that should be excluded
*
*	- selector:
*		- fix position var between arrows and a-z-1-9 keys
*		- fix overflow height by getting individual li height
*
*/
(function($) 
{
	$.widget("ui.form",
	{
		options:
		{
			roundCorners:true						// Adds round corners to the fields
			,widget:true							// Adds widget style controls to the fields
			,fontWeight:"normal"					// Sets fonts weight for the input fields, set to false to ignore this option
			,textPadding:"3px"						// Padding for text inside input fields, set to false to ignore this option
			,borders:"1px ridge"					// Adds borders to all input elements, set to false to ignore this option
			,margin:"0px"							// Margins for text fields, set to false to ignore this option
			,boxSizing:true							// Real box sizes standard for html doctype declaration
			,selectIcon:"ui-icon ui-icon-triangle-1-s"		// The icon image for the selector
			//,exclude:[]							// Fields to be excluded (by Id)
			//,fields:[]								// Control the previous options by fieldName
		},
		_create:function()
		{
			var browserVars=this._browserConfig();
			if(!browserVars){return;}
			var	object=this,
				options=object.options,
				form=object.element,
				inputs=form.find("input,select,textarea");
			if(options.widget)
			{
				form.addClass("ui-widget");
				form.find("fieldset").addClass("ui-widget ui-widget-content");
				form.find("fieldset").find("table").addClass("ui-widget-content").css({"background":"transparent","border":"none"});
				form.find("legend").addClass("ui-widget-header").css({"width":"98%","padding":"5px 0 5px 5px"});
			}
			if(options.roundCorners)
			{
				form.find("fieldset").addClass("ui-corner-all");
				form.find("legend").addClass("ui-corner-all");
			}
			$.each(inputs,function()
			{
				var input=$(this);
				if(options.fontWeight && !input.is(":reset, :submit")){input.css("font-weight",options.fontWeight);}
				if(options.textPadding && !input.is(":reset, :submit")){input.css("padding",options.textPadding);}
				if(options.margin){input.css("margin",options.margin);}
				if(options.boxSizing){input.css(browserVars['border-box'],"border-box");}
				/* Add an id to the input if not present */
				if(!input.attr('id')){input.attr('id','gen-'+Math.floor(Math.random()*99999));}
				if(options.widget)
				{
					input.addClass('ui-state-default');
					$("label[for="+input.attr("id")+"]").addClass("ui-helper-reset");
				}
				if(options.roundCorners){input.addClass('ui-corner-all');}
				//input.wrap("<label />");
				if(input.is(":reset, :submit")){input.wrap("<label />");object._buttons(this);}
				else if(input.is(":checkbox")){input.wrap("<label />");object._checkboxes(this);}
				else if(input.is(":radio")){input.wrap("<label />");object._radio(this);}
				else if(input.is("select")){input.wrap("<div />");object._selector(this);}
				else if(input.is("input[type='text']")||input.is("textarea")||
					input.is("input[type='password']")){input.wrap("<div />");object._textelements(this);}
				//if(input.hasClass("date")){input.wrap("<div />");input.datepicker();}
			});
			$(".hover").hover(function(){$(this).addClass("ui-state-hover");},function(){$(this).removeClass("ui-state-hover");});
			$(".hoverUl").hover(function(){$(this).addClass("ui-state-hover").css("margin","-1px");},
				function(){$(this).removeClass("ui-state-hover").css("margin","0px");});
			$(".hoverRadio").hover(
				function(){$(this).addClass("ui-state-hover").removeClass("ui-state-active");},
				function()
				{
					ra=$(this);
					if(ra.parent().find("input:radio").attr("checked")){ra.addClass("ui-state-active");}
					ra.removeClass("ui-state-hover");
				});
			$(".hoverCheck").hover(
				function(){$(this).addClass("ui-state-hover").removeClass("ui-state-active");},
				function()
				{
					ck=$(this);
					if(ck.parent().find("input:checkbox").attr("checked")){ck.addClass("ui-state-active");}
					ck.removeClass("ui-state-hover");
				});
		},
		_browserConfig:function()
		{
			var browser=$.browser
			var options=[];
			options['browser']=browser;
			if(browser.chrome)
			{
				options['border-box']="-webkit-box-sizing";
			}
			else if(browser.msi)
			{
				if(parseInt($.browser.version,10) == 6,7){return;} // no support for IE7, there are problems with ghe selector layout
				options['border-box']="-ms-box-sizing";
			}
			else if(browser.mozilla)
			{
				options['border-box']="-moz-box-sizing";
			}
			else
			{
				options['border-box']="box-sizing";
			}
			return this.browserVars=options;
		},
		_textelements:function(element)
		{
			if(this.options.widget)
			{
				var mainEl=$(element);
				mainEl.addClass('hover');
				if(this.options.borders){mainEl.css("border",this.options.borders);}
				mainEl.bind({focusin:function(){$(this).toggleClass('ui-state-focus');},
							focusout:function(){$(this).toggleClass('ui-state-focus');}});
			}
		},
		_buttons:function(element)
		{
			var	object=this,
				mainEl=$(element);
			if(mainEl.is(":submit"))
			{
				//mainEl.bind("click",function(event){event.preventDefault();});
				if(object.options.widget){mainEl.button().addClass('ui-priority-primary hover');}
			}
			else if(mainEl.is(":reset"))
			{
				if(object.options.widget)
				{
					mainEl.button().bind('mousedown mouseup',function()
					{
						mainEl.toggleClass('ui-state-active');
					}).addClass('ui-priority-primary hover');
				}
			}
		},
		_checkboxes:function(element)
		{
			var	object=this,
				mainEl=$(element);
			if(object.options.widget)
			{
				mainEl.parent("label").after("<span />");
				var parent=mainEl.parent("label").next();
				mainEl.addClass("ui-helper-hidden-accessible");
				parent.css({width:16,height:16,display:"block"});
				parent.wrap("<span class='ui-state-default ui-corner-all' style='display:inline-block;width:16px;height:16px;margin-right:5px;vertical-align:middle;'/>");
				parent.parent().addClass('hoverCheck');
				if(object.options.borders){parent.parent().css("border",object.options.borders);}
				parent.parent("span").click(function(event)
				{
					$(this).toggleClass("ui-state-active");
					parent.toggleClass("ui-icon ui-icon-check");
					mainEl.click();
					if($().validate){mainEl.valid();}
				}).bind
				({
					hover:function(){$("input[name*='"+mainEl.attr('name')+"']").trigger('mouseenter');},
					mouseout:function(){$("input[name*='"+mainEl.attr('name')+"']").trigger('mouseleave');}
				});
				if(mainEl.attr('checked'))
				{
					parent.parent("span").toggleClass("ui-state-active");
					parent.toggleClass("ui-icon ui-icon-check");
				};
				$("label[for="+mainEl.attr("id")+"]").bind
				({
					click:function(event)
					{
						parent.parent("span").click();
						if(mainEl.attr("checked"))
						{
							mainEl.attr("checked",true);
							parent.parent("span").addClass("ui-state-active").removeClass("ui-state-hover");
						}
						else
						{
							mainEl.attr("checked",false);
							parent.parent("span").addClass("ui-state-hover").removeClass("ui-state-active");
						}
						//mainEl.click();
						if($().validate){mainEl.valid();}
						event.preventDefault();
					},
					hover:function()
					{
						$("input[name*='"+mainEl.attr('name')+"']").trigger('mouseenter');
						parent.parent("span").addClass("ui-state-hover").removeClass("ui-state-active");
					},
					mouseout:function()
					{
						$("input[name*='"+mainEl.attr('name')+"']").trigger('mouseleave');
						if(mainEl.attr("checked")){parent.parent("span").addClass("ui-state-active");}
						parent.parent("span").removeClass("ui-state-hover");
					}
				});
			}
			mainEl.focus(function()
			{
				parent.parent("span").addClass("ui-state-hover").removeClass("ui-state-active");
			}).blur(function()
			{
				if(mainEl.attr("checked")){parent.parent("span").addClass("ui-state-active");}
				parent.parent("span").removeClass("ui-state-hover");
			}).keydown(function(event) 
			{
				if(event.keyCode=="32")
				{
					parent.toggleClass("ui-icon ui-icon-check");
					if(!mainEl.attr("checked")){parent.parent("span").addClass("ui-state-active");}
					else{parent.parent("span").removeClass("ui-state-active");}
				}
			});
		},
		_radio:function(element)
		{
			var	object=this,
				mainEl=$(element);
			if(object.options.widget)
			{
				mainEl.parent("label").after("<span />");
				var parent=$(element).parent("label").next();
				mainEl.addClass("ui-helper-hidden-accessible");
				parent.addClass("ui-icon ui-icon-radio-off");
				parent.wrap("<span class='ui-state-default ui-corner-all ui-form-"+mainEl.attr("name")+
						"' style='display:inline-block;width:16px;height:16px;margin-right:5px;vertical-align:middle;'/>");
				parent.parent().addClass('hoverRadio');
				if(object.options.borders){parent.parent().css("border",object.options.borders);}
				parent.parent("span").click(function(event)
				{
					$(".ui-form-"+mainEl.attr("name")).removeClass("ui-state-active");
					$(this).addClass("ui-state-active");
					parent.removeClass("ui-icon-radio-off").addClass("ui-icon-radio-on");
					mainEl.click();
					if($().validate){mainEl.valid();}
				}).bind
				({
					hover:function(){$("input[name*='"+mainEl.attr('name')+"']").trigger('mouseenter');},
					mouseout:function(){$("input[name*='"+mainEl.attr('name')+"']").trigger('mouseleave');}
				});
				if(mainEl.attr('checked'))
				{
					$(".ui-form-"+mainEl.attr("name")).removeClass("ui-state-active");
					parent.parent("span").addClass("ui-state-active");
					parent.removeClass("ui-icon-radio-off").addClass("ui-icon-radio-on");
				}
				$('input[name='+mainEl.attr("name")+']').change(function()
				{
					$(".ui-form-"+mainEl.attr("name")).removeClass("ui-state-active ui-state-hover");
					$.each($("input[name='"+mainEl.attr("name")+"']"),function(i,j) 
					{
						el=$(j);
						if(!el.attr("checked")){el.parent().next("span").children("span").
							removeClass("ui-icon-radio-on ui-state-active").addClass("ui-icon-radio-off");}
						else{el.parent().next("span").addClass("ui-state-active").children("span").
										removeClass("ui-icon-radio-off").addClass("ui-icon-radio-on");} 
					});
					
				});
				$("label[for="+mainEl.attr("id")+"]").bind
				({
					click:function(event)
					{
						parent.parent("span").click();
						mainEl.click();
						if($().validate){mainEl.valid();}
						event.preventDefault();
					},
					hover:function(){parent.parent("span").addClass("ui-state-hover");},
					mouseout:function(){parent.parent("span").removeClass("ui-state-hover");}
				});
				mainEl.focus(function()
				{
					parent.parent("span").addClass("ui-state-hover").removeClass("ui-state-active");
					
				}).blur(function()
				{
					parent.parent("span").removeClass("ui-state-hover");
				});
			}
		},
		_selector:function(element)
		{
			var object=this;
			if(object.options.roundCorners){$(element).addClass('ui-corner-all');}
			if(object.options.widget)
			{
				var	mainEl=$(element),
					parent=mainEl.parent(),
					container=$(parent),
					elId=mainEl.attr('id'),
					tpl=$("#"+elId),
					wt=tpl.outerWidth(true),
					elName=mainEl.attr('name'),
					elClass=mainEl.attr('class');
				mainEl.addClass('ui-helper-hidden');
				object.inputTextTpl="<input type='text' class='"+elClass+" labeltext hover' readOnly='true' name='uiForm-"+elId+"' id='ui-form-"+elId+"'>";
				object.iconTpl="<span class='"+object.options.selectIcon+"'></span>";
				parent.append(object.inputTextTpl+object.iconTpl);
				container.wrap("<div style='position:relative;display:inline-block;' />");
				var	inputText=$("#ui-form-"+elId),
					ht=(tpl.outerHeight(true)+(parseInt(mainEl.css("margin-top")))),
					elHeight=0;
				inputText.css(
				{
					"cursor":"pointer"
					,"float":"left"
					,"padding":parseInt(mainEl.css("padding-top"))+"px "+parseInt(mainEl.css("padding-right"))+"px "+
									parseInt(mainEl.css("padding-bottom"))+"px "+parseInt(mainEl.css("padding-left"))+"px"
					,"margin":parseInt(mainEl.css("margin-top"))+"px "+parseInt(mainEl.css("margin-right"))+"px "+
									parseInt(mainEl.css("margin-bottom"))+"px "+parseInt(mainEl.css("margin-left"))+"px"
					,"width":(wt-(parseInt(mainEl.css("margin-left"))+parseInt(mainEl.css("margin-right"))))+"px"
				});
				if(object.options.borders){inputText.css("border",object.options.borders);}
				if(ht>15 && (ht-16)>1){elHeight=Math.floor((ht-16)/2);}
				if(object.options.fontWeight){inputText.css("font-weight",object.options.fontWeight);}
				if(object.options.boxSizing){inputText.css(object.browserVars["border-box"],"border-box");}
				container.find("span").attr('style','float:right;top:'+elHeight+'px;right:8px;position:absolute;cursor:pointer;');
				if($.browser.msie){parent.css({"display":"block",width:wt});}
				else{parent.css({"display":"inline-block",width:wt});}
				parent.css({"background":"transparent","border":"none"});
				parent.addClass("ui-state-default");
				parent.after("<ul class='ui-helper-reset ui-widget ui-widget-content ui-helper-hidden ui-corner-bottom ui-form-ul'></ul>");
				container.next("ul").css(
				{
					"overflow":"auto"
					,"overflow-x":"hidden"
					,"overflow-y":"auto"
					,"position":"absolute"
					,"top":tpl.innerHeight()+"px"
					,"z-index":"3000"
					,"width":((wt-2)-(parseInt(mainEl.css("margin-left"))+parseInt(mainEl.css("margin-right"))))+"px"
					,"margin-left":mainEl.css("margin-left")+"px"
					,"margin-right":mainEl.css("margin-right")+"px"
				});
				$.each(mainEl.find("option"),function(k,v)
				{
					var el=$(this);
					if(el.attr('selected'))
					{
						inputText.val(el.text());
						mainEl.val(el.attr('value'));
						if(!el.attr('value')){inputText.addClass("defaultValue").attr("default-value",el.text());}
					}
					if(el.attr('value') && el.text())
					{
						container.next("ul").append("<li class='hoverUl ui-corner-all' style='margin:0px;padding:2px;list-style-type:none;'>"+
							"<a href='javascript:void(0)' style='text-decoration:none;display:block;padding:2px .4em;line-height:1.5;zoom:1;font-weight:normal;'>"+el.text()+"</a></li>");
					}
				});
				container.next("ul").append("<div style='clear:both;height:1px'><!-- --></div>");
				container.next("ul").find("li").click(function(event)
				{
					var el=$(this);
					inputText.val(el.text());
					$.each(tpl.find("option"),function(k,v){$(v).removeAttr("selected");});
					tpl.find("option:contains('"+el.text()+"')").attr("selected","selected");
					if($().validate)
					{
						if(inputText.val()!=inputText.attr("default-value"))
						{
							mainEl.valid(); 
							inputText.valid();
						}
					}
					if(object.options.roundCorners){inputText.switchClass('ui-corner-top','ui-corner-all',10);}
					parent.next("ul").toggle(10);
					event.preventDefault();
				});
				container.find("span").click(function(event)
				{
					//inputText.focus();		// does does not seem to fire the blur() but the next line does
					document.getElementById("ui-form-"+elId).focus();
					event.preventDefault();
				});
				position=0;
				container.click(function(event)
				{
					var el=$(this);
					if(object.options.roundCorners)
					{
						if(el.next("ul").is(':visible')){inputText.switchClass('ui-corner-top','ui-corner-all',10);}
						else
						{
							inputText.switchClass('ui-corner-all','ui-corner-top',10);
							container.next("ul").find("li.ui-state-hover").removeClass("ui-state-hover");
						}
					}
					el.next("ul").toggle(10);
					event.preventDefault();
				}).keydown(function(event) 
				{
					var 	k=event.which || event.keyCode,
						charStr=String.fromCharCode(k);
						el=$(this);
					if(k=="32" || k=="13")
					{
						useElement=container.next("ul").find("li.ui-state-hover");
						inputText.val($(useElement).text());
						$.each(tpl.find("option"),function(k,v){$(v).removeAttr("selected");});
						tpl.find("option:contains('"+$(useElement).text()+"')").attr("selected", "selected");
						if(object.options.roundCorners)
						{
							if(el.next("ul").is(':visible')){inputText.switchClass('ui-corner-top','ui-corner-all',10);}
							else{inputText.switchClass('ui-corner-all','ui-corner-top',10);}
						}
						el.next("ul").toggle(10);
						event.preventDefault();
					}
					else if(k=="39" || k=="40")
					{
						if(!el.next("ul").is(':visible'))
						{
							if(object.options.roundCorners){inputText.switchClass('ui-corner-all','ui-corner-top',10);}
							el.next("ul").toggle(10);
						}
						if(!container.next("ul").find("li").hasClass("ui-state-hover"))
						{
							container.next("ul").children("li").first().addClass("ui-state-hover").css("margin","-1px");
						}
						else
						{
							rMe=container.next("ul").find("li.ui-state-hover");
							if(!$(rMe).is(':last-child') && $(rMe).next().is("li")) 
							{
								ulPosition=(el.next("ul").offset().top+el.next("ul").height());
								if(ulPosition<(rMe.offset().top+40))
								{
									position=(position+el.next("ul").height());
									el.next("ul").scrollTop(position);
								}
								$(rMe).next().addClass("ui-state-hover").css("margin","-1px");
								$(rMe).removeClass("ui-state-hover").css("margin","0px");
							}
						}
						event.preventDefault();
					}
					else if(k=='38' || k=='37') 
					{
						if(!el.next("ul").is(':visible'))
						{
							if(object.options.roundCorners){inputText.switchClass('ui-corner-all','ui-corner-top',10);}
							el.next("ul").toggle(10);
						}
						if(!container.next("ul").find("li").hasClass("ui-state-hover")){container.next("ul").children("li").first().addClass("ui-state-hover");}
						else
						{
							rMe=container.next("ul").find("li.ui-state-hover");
							if(!$(rMe).is(':first-child')) 
							{
								ulHeight=el.next("ul").height();
								ulPosition=(el.next("ul").offset().top+ulHeight);
								if(ulPosition>(rMe.offset().top+ulHeight)-20)
								{
									position=(position-ulHeight);
									el.next("ul").scrollTop(position);	
								}
								container.next("ul").find("li.ui-state-hover").prev().addClass("ui-state-hover").css("margin","-1px");
								$(rMe).removeClass("ui-state-hover").css("margin","0px");
							}
						}
						event.preventDefault();
					}
					else if(/[a-z0-9]/i.test(charStr))
					{
						container.next("ul").find("li").each(function(k,v)
						{
							opLi=$(v),
							ul=container.next("ul");
							if(opLi.find("a").text().substr(0,1)==charStr && !opLi.hasClass("ui-state-hover"))
							{
								ul.find("li.ui-state-hover").removeClass("ui-state-hover").css("margin","0px");
								opLi.addClass("ui-state-hover").css("margin","-1px");
								if(!ul.is(':visible'))
								{
									ul.toggle(10,function(){if(this.clientHeight<ul[0].scrollHeight)
									{
										v.scrollIntoView(true);
									}
									});
									if(object.options.roundCorners){inputText.switchClass('ui-corner-all','ui-corner-top',10);}
								}
								else if(ul[0].clientHeight<ul[0].scrollHeight){v.scrollIntoView(true);}
								return false;
							}
						});
					}
				});
				inputText.focus(function(event)
				{
					this.select();
					$(this).addClass('ui-state-focus');
					event.preventDefault();
				});
				mouseover=false;
				parent.next("ul").mouseenter(function(){mouseover=true;}).mouseleave(function(){mouseover=false;});
				inputText.blur(function(event)
				{
					if(mouseover){this.focus(); return; }
					var ul=parent.next("ul");
					$(this).removeClass('ui-state-focus ui-state-hover');
					if(!mouseover && ul.is(':visible')) 
					{
						if(object.options.roundCorners){inputText.switchClass('ui-corner-top','ui-corner-all',10);}
						ul.toggle(10);
					}
					event.preventDefault();
				});
			}
		}
	});
})(jQuery); 