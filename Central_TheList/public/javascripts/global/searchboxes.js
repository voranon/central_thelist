$(function(){
	$('form#searchboxform').bind('submit',function(){
		  var pop_up 		= $(this).find("input#searchbox_popup").attr('checked');
		  var searchbox_id	= $(this).find("input#searchbox_id").val();
		  
		  if(pop_up == true){
			  
			 windowW = 650;
			 windowH = 550;
			 x    = (screen.width/2)-(windowW/2);
			 y    = (screen.height/2)-(windowH/2);
				
			 window.open('/search/searchpopup/?searchbox_id='+searchbox_id, 'popup2', 'width='+windowW+', height='+windowH+', menubar=no, scrollbars=yes, toolbar=no, location=no, status=yes, resizable=no, top='+y+', left='+x);
			 		  
			  return false;
		  }else if(pop_up == false ){
			  return true;
		  }
		  
		  
	});
	
});