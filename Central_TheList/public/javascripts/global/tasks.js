$(function(){
	
	
	$('#add_task').bind('click',function(){
		
		task_type 		= $(this).attr('task_type');
		task_type_id	= $(this).attr('task_type_id');
		
		windowW = 650;
		windowH = 550;
		x    = (screen.width/2)-(windowW/2);
		y    = (screen.height/2)-(windowH/2);
		
		window.open('/task/addtaskpopup/?task_type='+task_type+'&task_type_id='+task_type_id, 'popup2', 'width='+windowW+', height='+windowH+', menubar=no, scrollbars=yes, toolbar=no, location=no, status=yes, resizable=no, top='+y+', left='+x);

	});
	
	
	
	$('#edit_task').live('click',function(){
		windowW = 950;
		windowH = 550;
		x    = (screen.width/2)-(windowW/2);
		y    = (screen.height/2)-(windowH/2);
		task_id = $(this).attr('task_id');
		window.open('/task/edittaskpopup/?task_id='+task_id, 'popup2', 'width='+windowW+', height='+windowH+', menubar=no, scrollbars=yes, toolbar=no, location=no, status=yes, resizable=no, top='+y+', left='+x);
	});
	
	$('select#task_queue').bind('change',function(){
		var queue_id = $(this).val();
	    
		
		$.ajax({
			url: "/task/getqueuemember/",
			data:"queue_id="+queue_id,
	    	success: function(data) {
		    	$("select#owner").html(data);
		    	
	    	}
		});
		
	});
	
	
});