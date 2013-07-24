$(function(){
	
	$('img[class=info]').bind('click', function(){
		var ipaddress	=	$(this).siblings('span').text();
		
		var query_string	=	"host=" + ipaddress;
		var windowSize		=	"width=600,height=300";	
		window.open("/Device/ping?" + query_string,"popUP", windowSize);

			
	})
	

})

