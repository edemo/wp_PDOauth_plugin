var uribase=""
function SSO(test) {
	var self = this
	test=test || { debug: false }
	this.debug=test.debug
	win = test.win || window;
	
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
		var my_iframe=document.createElement("iframe")
		var container=jQuery("#eDemoSSO_message_frame")
		jQuery(container).append(my_iframe)
		my_iframe.onload= function(){
			try {
				this.contentWindow.document
			}
			catch(err) {
				jQuery(container).show()
				this.height="500px";
			}
		}
		my_iframe.width="370px";
		my_iframe.style.border="none";
		my_iframe.src=target_url; 
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
		}
	},false);

}

eDemo_SSO = new SSO();

