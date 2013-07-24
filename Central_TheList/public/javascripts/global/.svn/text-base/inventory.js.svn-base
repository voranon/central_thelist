	function get_equipment(key,value){

		
		var data_to_return;
		var query_string;
		switch(key){
		
		case "serial_number":
			query_string = "eq_serial_number=";
			break;
		case "if_id":
			query_string = "if_id=";
			break;			
		};


		$.ajax({
			url: "/inventory/geteqifxml",
			type: "GET",
			async: false,
			data:query_string+value,
			error: function(t, error,ts){
				alert("Error adding " + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				data_to_return = xml;
				
			}})
			
		return data_to_return;
		
	};
	
	function is_connected_to(if_id, eq_type_id){
		var is_connected_to;
		$.ajax({
			url: "/inventory/geteqifxml",
			data: "if_id="+if_id,
			dataType: "xml",
			async: false,
			success:function(xml){
				var equip_type_id = parseInt($(xml).find('eq_type_id').text());
				if (equip_type_id == eq_type_id){
					is_connected_to = xml;
					
				}
				else{
					is_connected_to = false;

				}
						
			}})

			return is_connected_to;
	};
	
	function get_baidiplexer_http(if_id){
		var bai_diplexer_xml;
		$.ajax({
			url: "/inventory/geteqifxml",
			data: "if_id="+if_id+"&composite_equipment=baidiplexer",
			async: false,
			dataType: "xml",
			success: function(xml){
				bai_diplexer_xml = xml;
				
			}})
			return bai_diplexer_xml;
	};
	function Baidiplexer(){
		var bai_diplexer;
		
		$.ajax({
			url		: "/inventory/newbaidiplexer",
			type	: "GET",
			dataType: 'xml',
			async	: false,
		    success	: function(xml){
		    				bai_diplexer	= xml;
		    			},
			error	: function(t,error,ts){
							alert("error status:" + t.status + " text: " + t.statusText);
						}
		});
		
		return bai_diplexer;
	}
	
	