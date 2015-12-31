
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

}

eDemo_SSO = new SSO();