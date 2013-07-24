
$(function(){
	$('select#mainperspective').bind('change',function(){
		
		
		if($(this).val()==1){
			
			window.location ='/residentialsaleperspective/index/';
		}else if($(this).val()==2){
			window.location ='/bussinesssaleperspective/index/';
		}else if($(this).val()==3){
			window.location ='/Supportperspective/index/';
		}else if($(this).val()==4){
			window.location ='/Engineerperspective/index/';
		}else if($(this).val()==5){
			//window.location =
		}else if($(this).val()==6){
			window.location ='/Executiveofficerperspective/index/';
		}else if($(this).val()==7){
			//window.location =
		}else if($(this).val()==8){
			//window.location =
		}else if($(this).val()==9){
			window.location ='/purchasingperspective/index/';
		}else{
		
		}
		
		
	});
	
	$('select#menu').bind('change',function(){
		var value 	  		=$(this).val();
		var temp  	  		= value.split('*');
		var page_id	  		=temp[0];
		var controller		=temp[1];
		var action    		=temp[2];
		
		window.location.href = '/'+controller+'/'+action;
		
	});
	
});
