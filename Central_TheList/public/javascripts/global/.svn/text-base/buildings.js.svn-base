$(function(){
	
	$('input#add_building').bind('click',function(){
		var project_id = $(this).attr('project_id');
		windowW = 850;
		windowH = 250;
		x    = (screen.width/2)-(windowW/2);
		y    = (screen.height/2)-(windowH/2);
		window.open('/building/create?project_id='+project_id, 'popup2','width='+windowW+', height='+windowH+', menubar=no, scrollbars=yes, toolbar=no, location=no, status=yes, resizable=no, top='+y+', left='+x);
	});
	
	$('input#edit_building').bind('click',function(){
		building_id = $(this).attr('building_id');
		project_id  = $(this).attr('project_id');
		
		windowW = 850;
		windowH = 250;
		x    = (screen.width/2)-(windowW/2);
		y    = (screen.height/2)-(windowH/2);
		window.open('/building/editbuildingpopup?project_id='+project_id+'&building_id='+building_id, 'popup2','width='+windowW+', height='+windowH+', menubar=no, scrollbars=yes, toolbar=no, location=no, status=yes, resizable=no, top='+y+', left='+x);
	});
	
});