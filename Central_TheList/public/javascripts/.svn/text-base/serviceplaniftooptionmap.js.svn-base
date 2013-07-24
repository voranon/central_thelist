$(function(){
	
	$('#service_plan_eq_type_map_id').bind('change',function(){
		$('#service_plan_eq_type_map_id option:selected').each(function(){
			
			//eq option currently selected
			var option_id				=	$(this).val();
			var static_int_xml			=	get_static_interfaces(option_id);
			var static_if_type			=	$('#static_if_type_id');
			static_if_type.empty().end();
			
			//static_if_type.append("<option value=''>-----Select One----</option>"); //not needed since menu is mandetory
			
			$(static_int_xml).find('static_if_type_id').each(function(){

				var static_if_type_id	=	$(this).text();
				var if_type_name		= 	$(this).siblings('if_type_name').text();
				var option				=	"<option value='" + static_if_type_id + "'>" + if_type_name + "</option>";
				static_if_type.append(option);
				
				
			})
			
		})
	})
	
	function get_static_interfaces(if_id)
	{
		var static_interfaces;
		var query_string		=	"service_plan_eq_type_map_id=" + if_id;
		$.ajax({
			url: "/sales/sitetm",
			type: "GET",
			async: false,
			data:query_string,
			error: function(t, error,ts){
				alert("Unable to GET static interfaces type");
			},
			dataType: 'xml',
			success: function(xml){
				static_interfaces	=	xml;
				
			}})
			
			return static_interfaces;
	}

})