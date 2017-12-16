
function SSO(test) {
	var logout_url = ""
	if (eDemoSSO_ada_logout_url != "undefined" ) logout_url='https://' + eDemoSSO_ada_logout_url
	var self = this
	test=test || { debug: false }
	this.debug=test.debug
	win = test.win || window;
	this.target_url=""
	
	SSO.prototype.ajaxBase = function(callback) {
		var xmlhttp;
		if (win.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp = new win.XMLHttpRequest();
//		  xmlhttp.oName="XMLHttpRequest"; // for testing
		  }
		else
		  {// code for IE6, IE5
		  xmlhttp = new win.ActiveXObject("Microsoft.XMLHTTP");
//		  xmlhttp.oName="ActiveXObject";   // for testing
		  }
		xmlhttp.callback=callback // for testing
		xmlhttp.onreadystatechange=function()
		  {
		  if (xmlhttp.readyState==4)
		    {
		    	callback(xmlhttp.status,xmlhttp.responseText,xmlhttp.responseXML);
		    }
		  }
		return xmlhttp;
	}

	SSO.prototype.ajaxpost = function( uri, data, callback ) {
		xmlhttp = this.ajaxBase( callback );
		xmlhttp.open( "POST", uribase + uri, true );
		xmlhttp.setRequestHeader( "Content-type","application/x-www-form-urlencoded" );
		l = []
		for (key in data) l.push( key + "=" + encodeURIComponent( data[key] ) ); 
		var dataString = l.join("&")
		xmlhttp.send( dataString );
	}

	SSO.prototype.ajaxget = function( uri, callback ) {
		xmlhttp = this.ajaxBase( callback )
		xmlhttp.open( "GET", uribase + uri, true);
		xmlhttp.send();
	}
	
	SSO.prototype.showMessageBox = function() {
		var messageDiv=document.createElement('div')
		messageDiv.id='SSO-message-container'
		messageDiv.setAttribute("class", "SSO-message-container")
		document.body.append(messageDiv)
	}

	SSO.prototype.removeMessageBox = function() {
		document.body.removeChild(document.getElementById('SSO-message-container'))
	}

	SSO.prototype.callForMessage = function(wpNonce, container) {
		this.container=container || 'modalwindow';
		this.ajaxget('/wp-admin/admin-ajax.php?_wpnonce='+wpNonce+'&action=eDemoSSO_get_message', this.messageCallback)
	}
	
	SSO.prototype.messageCallback = function(status, text, xml) {
		console.log(self.container)
		console.log(status)
		console.log(text)
		console.log(xml)
		var message=JSON.parse(text)
		if (message.text!="") {
			var container=document.getElementById('eDemoSSO-message-container')
			container.innerHTML='<p class="notice">'+message.text+'</p>'
		}
	}
	
	SSO.prototype.button_click = function(target_url){
		var item
		self.target_url=target_url
		if ( item = document.getElementById("login-iframe") ) item.parentNode.removeChild(item)
		var my_iframe=document.createElement("iframe")
		my_iframe.id="login-iframe"
		var container=jQuery("#eDemoSSO_message_frame")
		container.append(my_iframe)
		my_iframe.onload= function(){
			if (document.getElementById("login-iframe").src==target_url) jQuery(container).show()
		}
		if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
			container.css("overflow","auto");
			container.css("-webkit-overflow-scrolling","touch");
		}
		my_iframe.src=target_url; 
	}
	
	SSO.prototype.xOnClick = function() {
		jQuery("#eDemoSSO_message_frame").hide('slow')
		var item
		if ( item = document.getElementById("login-iframe") ) item.parentNode.removeChild(item)
	}

	SSO.prototype.adalogout = function() {
//		document.getElementById("logout_url_container").click()
		window.location.href=logout_url+"?next="+encodeURIComponent(document.getElementById("logout_url_container").href)
/*		var logout_iframe=document.createElement("iframe")
		logout_iframe.id="logout_iframe"
		logout_iframe.onload=function(){
//			window.location.href=document.getElementById("logout_url_container").href
		}
		document.getElementById("eDemoSSO_message_frame").appendChild(logout_iframe)
		logout_iframe.src=logout_url */
	}
	
	SSO.prototype.get_the_target_url = function(){
		return self.target_url
	}
	
	var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
	var eventer = window[eventMethod];
	var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
	eventer( messageEvent, function(e) {
		var key = e.message ? "message" : "data";
		switch (e[key]) {
			case "reload":
				if ( -1 == window.location.href.indexOf("wp-login.php") ) window.location.reload();
				else window.location.href=window.location.origin;
				break;
			case "hide":
				jQuery("#eDemoSSO_message_frame").hide('slow');
				var item
				if ( item = document.getElementById("login-iframe") ) item.parentNode.removeChild(item)
				console.log("hide")
				break;
			case "blank":
				console.log("load loginform in _blank_")
				window.location.href=self.get_the_target_url();
				break;
			case "done":
				alert ("done")
		}
	},false);

}




