function bailoc()
{
	if(navigator.geolocation)
	{
	  navigator.geolocation.getCurrentPosition(function(position)
	    {
	      var lat = position.coords.latitude;
	      var lng = position.coords.longitude;
	      var acu = position.coords.accuracy;
	      var alt = position.coords.altitude;
	      var altacu = position.coords.altitudeAccuracy;
	      var head = position.coords.heading;
	      var spd = position.coords.speed;
	      
	  		document.getElementById("latitude").value=lat;
	  		document.getElementById("longitude").value=lng;
	  		document.getElementById("accuracy").value=acu;
	  		document.getElementById("altitude").value=alt;
	  		document.getElementById("altitudeaccuracy").value=altacu;
	  		document.getElementById("heading").value=head;
	  		document.getElementById("speed").value=spd;
	    
			$.ajax({
				url		: "receivelocation.php",
				type	: "POST",
				data	: "latitude="+lat+"&longitude="+lng+"&accuracy="+acu+"&altitude="+alt+"&altitudeaccuracy="+altacu+"&heading="+head+"&speed="+spd,
				dataType: 'text',
			    success	: function(data) {

			    
			    },
				error	: function(error){

					
				}
			});
	      

	    });
	}
};