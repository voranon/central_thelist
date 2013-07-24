function baibrowser()
{
	if(navigator.geolocation)
	{
	  navigator.geolocation.getCurrentPosition(function(position)
	    {
	      var browsercode = navigator.appCodeName;
	      var browsername = navigator.appName;
	      var browserversion = navigator.appVersion;
	      var cookiesenabled = navigator.cookieEnabled;
	      var platform = navigator.platform;
	      var useragentheader = navigator.userAgent;

	      
	  		document.getElementById("browsercode").value=browsercode;
	  		document.getElementById("browsername").value=browsername;
	  		document.getElementById("browserversion").value=browserversion;
	  		document.getElementById("cookiesenabled").value=cookiesenabled;
	  		document.getElementById("platform").value=platform;
	  		document.getElementById("useragentheader").value=useragentheader;

	    });
	}
};