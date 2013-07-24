$(function(){
	
	if(navigator.geolocation)
	{
	  navigator.geolocation.getCurrentPosition(function(position)
	    {
	      var lat = position.coords.latitude;
	      var lng = position.coords.longitude;
	      $.ajax({
	    	  type: 'POST',
	    	  url: 'martin-zend-dev.belairinternet.com/public/receivelocation.php',
	    	  data: lat + lng,
	    	  success: success,
	    	  dataType: text
	    	});

	    });
	}
});