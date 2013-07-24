$(function(){
	
	
	$('input#add_contact').bind('click',function(){
		
		var contact_type 		= $(this).attr('contact_type');
		var contact_type_id		= $(this).attr('contact_type_id'); 
	
		windowW = 760;
		windowH = 520;
		x    = (screen.width/2)-(windowW/2);
		y    = (screen.height/2)-(windowH/2);
		window.open('/contact/addcontactpopup/?contact_type='+contact_type+'&contact_type_id='+contact_type_id, 'popup2', 'width='+windowW+', height='+windowH+', menubar=no, scrollbars=yes, toolbar=no, location=no, status=yes, resizable=no, top='+y+', left='+x);
	});
	

	
		
	$('select#contact').bind('change',function(){
			if($(this).val()==0){

					$('input#firstname').val('');
			    	$('input#firstname').removeAttr('readonly');
			    	$('input#lastname').val('');
			    	$('input#lastname').removeAttr('readonly');
			    	$('input#streetnumber').val('');
			    	$('input#streetnumber').removeAttr('readonly');
			    	$('input#streetname').val('');
			    	$('input#streetname').removeAttr('readonly');
			    	$('input#streettype').val('');
			    	$('input#streettype').removeAttr('readonly');
			    	$('input#city').val('');
			    	$('input#city').removeAttr('readonly');
			    	$('select#state').val('CA');
			    	$('select#state').removeAttr('readonly');
			    	$('input#zip').val('');
			    	$('input#zip').removeAttr('readonly');
			    	$('input#cellphone').val('');
			    	$('input#cellphone').removeAttr('readonly');
			    	$('input#homephone').val('');
			    	$('input#homephone').removeAttr('readonly');
			    	$('input#officephone').val('');
			    	$('input#officephone').removeAttr('readonly');
			    	$('input#fax').val('');
			    	$('input#fax').removeAttr('readonly');
			    	$('input#email').val('');
			    	$('input#email').removeAttr('readonly');
				
			}else{// contact is selected
				
			$.ajax({
					url: "/contact/getcontactajax/",
					data:"contactid="+$(this).val(),
				    success: function(data) {
		  		    $temp=data.split('!');
		  		  		
					
				    	$('input#firstname').val($temp[0]);
				    	$('input#firstname').attr('readonly','true');
				    	
				    	$('input#lastname').val($temp[1]);
				    	$('input#lastname').attr('readonly','true');
				    	
				    	$('input#streetnumber').val($temp[2]);
				    	$('input#streetnumber').attr('readonly','true');
				    	
				    	$('input#streetname').val($temp[3]);
				    	$('input#streetname').attr('readonly','true');

				    	$('input#streettype').val($temp[4]);
				    	$('input#streettype').attr('readonly','true');
				    	
				    	$('input#city').val($temp[5]);
				    	$('input#city').attr('readonly','true');
				    
				    	$('select#state').val($temp[6]);
				    	$('select#state').attr('readonly','true');
				    	
				    	$('input#zip').val($temp[7]);
				    	$('input#zip').attr('readonly','true');
				    	
				    	$('input#cellphone').val($temp[8]);
				    	$('input#cellphone').attr('readonly','true');
				    	
				    	$('input#homephone').val($temp[9]);
				    	$('input#homephone').attr('readonly','true');

				    	$('input#officephone').val($temp[10]);
				    	$('input#officephone').attr('readonly','true');
				    	
				    	$('input#fax').val($temp[11]);
				    	$('input#fax').attr('readonly','true');
				    	
				    	$('input#email').val($temp[12]);
				    	$('input#email').attr('readonly','true');
				    	  		    	
				    }
			});
			}
	});

	
	$('input#edit_contact').live('click',function(){
		
		var contact_type 	= $(this).attr('contact_type');
		var contact_type_id	= $(this).attr('contact_type_id');
		
		//var project_id = $(this).attr('project_id');
		
		windowW = 760;
		windowH = 520;
		x    = (screen.width/2)-(windowW/2);
		y    = (screen.height/2)-(windowH/2);
		contact_id = $(this).attr('contact_id');
		
		window.open('/contact/editcontactpopup/?contact_type='+contact_type+'&contact_type_id='+contact_type_id+'&contact_id='+contact_id, 'popup2', 'width='+windowW+', height='+windowH+', menubar=no, scrollbars=yes, toolbar=no, location=no, status=yes, resizable=no, top='+y+', left='+x);
	});
		
	
});