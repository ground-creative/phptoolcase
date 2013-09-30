function g(e)
{
	return(e=RegExp(";\\s*"+e+"=(.*?);","g").exec(";"+document.cookie+";"))?e[1]:null
}
var k=g("php-console-server");
k||(k=g("phpcsls"));
k&&new function()
{
	var e="",h="",l=0;
	window.addEventListener("error",function(a,d,b)
	{
		a.filename&&(d=a.filename);
		"undefined"!=typeof a.lineno&&(b=a.lineno?a.lineno:1);
		if(!a.target.chrome||d)
		{
			a.message?a=a.message:a.data?a=a.data:a.target&&a.target.src&&(d=window.location.href,a="File not found: "+a.target.src);
			if("string"!=typeof a||"Script error."==a)a=null;
			var c=a+d+b;c!=h&&10>l&&(h=c,l++,chrome.extension.sendMessage({_handleJavascriptError:!0,text:a,url:d,line:b}))
		}
	},!1);
	chrome.extension.onMessage.addListener(function(a)
	{
		//if(a._id==e||a._url==window.location.href)
		if(a._handleConsolePacks)for(var d in a.packs)
		{
			
			var b=a.packs[d];
			if(b.redirectUrl)
			{
				var c=b.url+" --\x3e "+b.redirectUrl;b.errorInPack?console.group(c):console.groupCollapsed(c)
			}
			else console.group(b.url);
			c=void 0;
			for(c in b.messages)
			{
				var f=b.messages[c];
				if("eval_result"==f.type)for(var h in f.args)console.log.apply(console,f.args[h]);
				else"error"==f.type?console.error.apply(console,f.args):console.log.apply(console,f.args)
			}
			console.groupEnd()
		}
		else a._clearConsole&&console.clear()
	});
	chrome.runtime.sendMessage({_registerTab:!0,url:window.location.href,protocol:k},function(a){a.url&&(window.location.href=a.url);e=a.id})
};