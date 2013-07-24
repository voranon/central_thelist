
$(function(){

	
	$('#jquery_contextmenu').hide();
		
	jQuery(document).ajaxStart(function(){

		$('body').css('background-color', '#B8B8B8');
		})

	jQuery(document).ajaxStop(function(){
		
		$('body').css('background-color', 'transparent');
		
		})
	//returns: true or false
	function cisco3524(xml){
		
		var equipment_div	= create_new_equipment(xml);
		var serial_number	= $(xml).find('eq_serial_number').text();
		equipment_div.attr('style', 'width: 900px; height: 100px; border: 1px solid black; border-radius: 0px');
		var equipment_inner	=				
			'<div style="width: 75px; height: 100px; border: 1px dashed red; float: left; background-color: #339999; border-radius: 0px">\
				<span>Cisco Systems</span>\
			</div>\
			<div style="width: 825px; height: 100px; border: 1px dotted green; margin-left: 75px; background-color: #339999">\
				<div style="float: left; width: 220px; height: 80px; border: 2px solid yellow; background-color: #C8C8C8; margin-top: 10px"></div>\
				<div style="float: left; width: 220px; height: 80px; border: 2px solid yellow; margin-left: 70px; background-color: #C8C8C8; margin-top: 10px"></div>\
				<div style="float: left; width: 100px; height: 40px; border: 2px solid yellow; margin-left: 40px; margin-top: 50px; background-color: #C8C8C8"></div>\
				<div style="float: left; width: 100px; height: 40px; border: 2px solid yellow; margin-left: 40px; margin-top: 50px; background-color: #C8C8C8"></div>\
			</div></div>';
		equipment_div.append(equipment_inner);
		equipment_div.appendTo('body');
		var ethernet_port			= create_endpoint("switchport");
		var endpoints = [];
		var i = 0;
		var x_coordinate_init		= .108;
		var y_coordinate_top_row	= .39;
		var y_coordinate_bottom_row = .69;
		var x_coordinate_offset		= .040;
		
		for (i = 0; i < 6; i++){
			//add endpoint on top row & bottom row
			var x_coordinate					= x_coordinate_init + (i * x_coordinate_offset);
			var endpoint_top					= jsPlumb.addEndpoint(equipment_div, { anchor:[x_coordinate, y_coordinate_top_row,0,1] }, ethernet_port);
			endpoint_top.if_id					= $(xml).find('if_index').filter(function(){return $(this).text() == i*2}).siblings('if_id').text();
			endpoint_top.connects_to_if_id		= $(xml).find('if_index').filter(function(){return $(this).text() == i*2}).siblings('connection').find('if_connects_to').text();
			var endpoint_bottom					= jsPlumb.addEndpoint(equipment_div, { anchor:[x_coordinate, y_coordinate_bottom_row,0,1] }, ethernet_port);
			endpoint_bottom	.if_id				= $(xml).find('if_index').filter(function(){return $(this).text() == (i*2) + 1}).siblings('if_id').text();
			endpoint_bottom.connects_to_if_id	= $(xml).find('if_index').filter(function(){return $(this).text() == (i*2) + 1}).siblings('connection').find('if_connects_to').text();			
			endpoints.push(endpoint_top,endpoint_top);
			equipment_div.data(endpoint_top.if_id, endpoint_top);
			equipment_div.data(endpoint_bottom.if_id, endpoint_bottom);
			
		}
		//redefine initial x coordinate & bottom row
		x_coordinate_init			= .435;
		for (i = 0; i < 6; i++){
			//add endpoint on top row
			var x_coordinate					= x_coordinate_init + (i * x_coordinate_offset);
			var endpoint_top					= jsPlumb.addEndpoint(equipment_div, { anchor:[x_coordinate, y_coordinate_top_row,0,1] }, ethernet_port);
			endpoint_top.if_id					= $(xml).find('if_index').filter(function(){return $(this).text() == (i * 2) + 12}).siblings('if_id').text();
			endpoint_top.connects_to_if_id		= $(xml).find('if_index').filter(function(){return $(this).text() == (i * 2) + 12}).siblings('connection').find('if_connects_to').text();
			var endpoint_bottom					= jsPlumb.addEndpoint(equipment_div, { anchor:[x_coordinate, y_coordinate_bottom_row,0,1] }, ethernet_port);
			endpoint_bottom	.if_id				= $(xml).find('if_index').filter(function(){return $(this).text() == (i * 2) + 13}).siblings('if_id').text();
			endpoint_bottom.connects_to_if_id	= $(xml).find('if_index').filter(function(){return $(this).text() == (i * 2) + 13}).siblings('connection').find('if_connects_to').text();			
			endpoints.push(endpoint_top,endpoint_top);
			equipment_div.data(endpoint_top.if_id, endpoint_top);
			equipment_div.data(endpoint_bottom.if_id, endpoint_bottom);
			
			
		}
		equipment_div.data('endpoints', endpoints);
		//connectOnPageEquipment(xml, equipment_div);		
		equipment_div.data('endpoints', endpoints);
		connectOnPageEquipment(xml, equipment_div);		
		
	}
	
	function get_element(id){
		var css_selector = "#" + id;
		return $(css_selector);
	}
	
	function is_element_on_page(id){
		//css selector
		var css_selector = "#" + id;
		if ($(css_selector).length > 0){
			return $(css_selector);
		}
		else{
			return false;
		}
		
	}


	
	function connectEndpoints(e1,e2, color){
		jsPlumb.connect({source:e1, target:e2});
		setConnectedEndpointsColor(e1,e2,color);
	}
	function setEndpointColor(endpoint,color){
		var paintColor;
		if (color == null){
			paintColor = "#FF0000";
		}else{
			paintColor = color;
		}		
		endpoint.setStyle({fillStyle:paintColor});
	}
	function setConnectedEndpointsColor(e1, e2, color){
		var paintColor;
		if (color == null){
			paintColor = "#FF0000";
		}
		else{
			paintColor = color;
		}
		e1.setStyle({fillStyle:paintColor});
		e2.setStyle({fillStyle:paintColor});
	}
	

	//When element is clicked, display generic equipment information
	$("div.equipment").live("dblclick", function(){
		
		var serial_number			= ($(this).data('serial_number'));
		var equipment_model_name	= ($(this).data('equipment_model_name'));
		var equipment_data			= "Serial Number: " + serial_number + "\n" + "Model Name: " + equipment_model_name + "\n";
		alert(equipment_data);
	});
	
	
	
	function is_baidiplexer_on_page(xml){
		var bai_diplexer_id			= $(xml).find('eq_type_id:contains("22")').siblings('interface').find('if_type_id:contains("32")').siblings('if_id').text();
		var bai_diplexer_selector	= "#" + bai_diplexer_id;
		
		//if element exist; 
		if($(bai_diplexer_selector).length){
			return bai_diplexer_id;
		}else{
			return false;
		}
		
		
	}
	
	
	function get_baidiplexer_id(xml){
		var bai_diplexer_id			= $(xml).find('eq_type_id:contains("22")').siblings('interface').find('if_type_id:contains("32")').siblings('if_id').text();
		return bai_diplexer_id;
	}
	
	jsPlumb.bind("dblclick", function(connection){
		var remove_connection		= confirm("Are you sure you want to disconnect these endpoints?");
		if (remove_connection){
			jsPlumb.detach(connection, {fireEvent: true});
		}
	});
	
	
	$('#jquery_contextmenu').bind('click', function(){
		var endpoint	= $('body').data('clicked_endpoint');
		var xml_equipment			= get_equipment('if_id', endpoint.if_id);
		var is_connected_to;
		//is_connected_to				= $(xml_equipment).find('if_id:contains("' + endpoint.if_id + '")').siblings('connection').find('if_connects_to').text();
		
		is_connected_to				= $(xml_equipment).find('if_id').filter(function(){return $(this).text() == endpoint.if_id}).siblings('connection').find('if_connects_to').text();
		if(is_connected_to){
			
			var xml_target_equipment	= get_equipment('if_id',is_connected_to);
			var target_eq_type_id		= parseInt(get_eq_type_id(xml_target_equipment));
			if(target_eq_type_id == 22 || target_eq_type_id == 23){
				xml_target_equipment	= get_baidiplexer_http(is_connected_to)
			}
			var dom_target_equipment	= addEquipment(xml_target_equipment);
			//connectEndpoints(dom_target_equipment.data(is_connected_to), endpoint, null);
		}
		
		
		//alert("Interface ID: " + endpoint.if_id + "\nInterface Type:" + endpoint.if_type_name);
	
	
	
	});
	
	
	$(':not(#jquery_contextmenu').bind('click', function(e){
		$('#jquery_contextmenu').hide();
	});
	
	$('#jquery_contextmenu').hover(
			
			//current_color	= $(this).css('background-color');
			
			function(){
				$(this).css('background-color', '#FF9933')
			},
			function(){
				$(this).css('background-color', '#F0F0F0');				
			}
			
		);
	
	jsPlumb.bind("contextmenu", function(component, e){

		var pageX		= e.pageX;
		var pageY		= e.pageY - 125;
		//alert(component.constructor);
		$('#jquery_contextmenu').css({ left: pageX, top: pageY, zIndex: 101}).show();
		$('body').data('clicked_endpoint', component);
		

	});
	
	$('*').bind('contextmenu', function(e){
		return false;
	});
	

	
	function LA145(xml){
		var sonora_div		= create_new_equipment(xml);
		var serial_number	= $(xml).find('eq_serial_number').text();
		sonora_div.attr('style', "width: 300px; height: 90px; border: 1px dashed red; padding: 10px; border-radius: 0; background-color: #000000; ");
		
		var sonora_inner	=				
				'<div style="width: 100%; height: 25px; border: 1px solid black; background-color: orange; text-align: center; font-size: x-small; border-radius: 0 ">\
					<span style="margin-right: 23px">INPUT 1</span><span style="margin-right: 23px">INPUT 2</span><span style="margin-right: 23px; ">INPUT 3</span><span style="margin-right: 23px">INPUT 4</span><span style="margin-right: 23px">INPUT 5</span>\
				</div>\
				<div style="width: 100%; height: 40px; border: 1px solid black; background-color: #D0D0D0; border-radius: 0">\
					<div style="width: 100%; height: 20px; border: 1px solid black; background-color: #000000; margin-top: 3px; border-radius: 0; text-align: left">\
						<span style="color: orange; ">•</span><span style="color: #FFFFFF; font-size: x-small">SONORA [serial-number: ' + serial_number + ']</span>\
					</div>\
				</div>\
				<div style="width: 100%; height: 20px; border: 1px solid black; background-color: orange; text-align: center; font-size: x-small; border-radius: 0">\
					<span style="margin-right: 12px">OUTPUT 1</span><span style="margin-right: 12px">OUTPUT 2</span><span style="margin-right: 12px">OUTPUT 3</span><span style="margin-right: 12px">OUTPUT 4</span><span style="margin-right: 12px">OUTPUT 5</span>\
				</div>\
			</div>';
		sonora_div.append(sonora_inner);	
		sonora_div.appendTo('body');
		var swmendpoint			= create_endpoint("swm_coaxial");

		
		var e0_top					= jsPlumb.addEndpoint(sonora_div, { anchor:[.15,0,0,1] }, swmendpoint);
		e0_top.if_id				= $(xml).find('if_index:contains("0")').siblings('if_id').text();
		e0_top.connects_to_if_id	= $(xml).find('if_index:contains("0")').siblings('connection').find('if_connects_to').text();
		e0_top.defaultColor			= swmendpoint.defaultColor;		
		var e1_top					= jsPlumb.addEndpoint(sonora_div, { anchor:[.32,0,0,1] }, swmendpoint);
		e1_top.if_id				= $(xml).find('if_index:contains("1")').siblings('if_id').text();
		e1_top.defaultColor			= swmendpoint.defaultColor;
		e1_top.connects_to_if_id	= $(xml).find('if_index:contains("1")').siblings('connection').find('if_connects_to').text();
		var e2_top					= jsPlumb.addEndpoint(sonora_div, { anchor:[.49,0,0,1] }, swmendpoint);
		e2_top.if_id				= $(xml).find('if_index:contains("2")').siblings('if_id').text();
		e2_top.defaultColor			= swmendpoint.defaultColor;
		e2_top.connects_to_if_id	= $(xml).find('if_index:contains("2")').siblings('connection').find('if_connects_to').text();		
		var e3_top					= jsPlumb.addEndpoint(sonora_div, { anchor:[.66,0,0,1] }, swmendpoint);
		e3_top.if_id				= $(xml).find('if_index:contains("3")').siblings('if_id').text();
		e3_top.defaultColor			= swmendpoint.defaultColor;
		e3_top.connects_to_if_id	= $(xml).find('if_index:contains("3")').siblings('connection').find('if_connects_to').text();		
		var e4_top					= jsPlumb.addEndpoint(sonora_div, { anchor:[.83,0,0,1] }, swmendpoint);
		e4_top.if_id				= $(xml).find('if_index:contains("4")').siblings('if_id').text();
		e4_top.defaultColor			= swmendpoint.defaultColor;
		e4_top.connects_to_if_id	= $(xml).find('if_index:contains("4")').siblings('connection').find('if_connects_to').text();

		
		var e0_bottom				= jsPlumb.addEndpoint(sonora_div, { anchor:[.15,1,0,1] }, swmendpoint);
		e0_bottom.if_id				= $(xml).find('if_index:contains("5")').siblings('if_id').text();
		e0_bottom.defaultColor		= swmendpoint.defaultColor;
		e0_bottom.connects_to_if_id	= $(xml).find('if_index:contains("5")').siblings('connection').find('if_connects_to').text();		
		var e1_bottom				= jsPlumb.addEndpoint(sonora_div, { anchor:[.32,1,0,1] }, swmendpoint);
		e1_bottom.if_id				= $(xml).find('if_index:contains("6")').siblings('if_id').text();
		e1_bottom.defaultColor		= swmendpoint.defaultColor;
		e1_bottom.connects_to_if_id	= $(xml).find('if_index:contains("6")').siblings('connection').find('if_connects_to').text();		
		var e2_bottom				= jsPlumb.addEndpoint(sonora_div, { anchor:[.49,1,0,1] }, swmendpoint);
		e2_bottom.if_id				= $(xml).find('if_index:contains("7")').siblings('if_id').text();
		e2_bottom.defaultColor		= swmendpoint.defaultColor;
		e2_bottom.connects_to_if_id	= $(xml).find('if_index:contains("7")').siblings('connection').find('if_connects_to').text();		
		
		var e3_bottom				= jsPlumb.addEndpoint(sonora_div, { anchor:[.66,1,0,1] }, swmendpoint);
		e3_bottom.if_id				= $(xml).find('if_index:contains("8")').siblings('if_id').text();
		e3_bottom.defaultColor		= swmendpoint.defaultColor;
		e3_bottom.connects_to_if_id	= $(xml).find('if_index:contains("8")').siblings('connection').find('if_connects_to').text();		
		var e4_bottom				= jsPlumb.addEndpoint(sonora_div, { anchor:[.83,1,0,1] }, swmendpoint);
		e4_bottom.if_id				= $(xml).find('if_index:contains("9")').siblings('if_id').text();
		e4_bottom.defaultColor		= swmendpoint.defaultColor;
		e4_bottom.connects_to_if_id	= $(xml).find('if_index:contains("9")').siblings('connection').find('if_connects_to').text();		
		
		
		sonora_div.data(e0_top.if_id, e0_top);
		sonora_div.data(e1_top.if_id, e1_top);
		sonora_div.data(e2_top.if_id, e2_top);
		sonora_div.data(e3_top.if_id, e3_top);
		sonora_div.data(e4_top.if_id, e4_top);
		sonora_div.data(e0_bottom.if_id, e0_bottom);
		sonora_div.data(e1_bottom.if_id, e1_bottom);
		sonora_div.data(e2_bottom.if_id, e2_bottom);
		sonora_div.data(e3_bottom.if_id, e3_bottom);
		sonora_div.data(e4_bottom.if_id, e4_bottom);	

		var endpoints				= [e0_top,e1_top,e2_top,e3_top,e4_top,e0_bottom,e1_bottom,e2_bottom,e3_bottom,e4_bottom];
		sonora_div.data('endpoints',endpoints);
		connectOnPageEquipment(xml, sonora_div);

		
	}
	//determine if XML is a diplexer
	function isBaiDiplexer(xml){
		var equipment_count			= $(xml).find('equipment').length;
		var is_bai_diplexer			= (equipment_count == 3)? true: false;
		return is_bai_diplexer;
		
	}
	function addEquipment(xml){


		var eq_type_id					= (isBaiDiplexer(xml) == true)? 23: parseInt($(xml).find('eq_type_id').text());
		var eq_id						= get_eq_id(xml);
		var equipment;
		if(!is_element_on_page(eq_id)){
			switch (eq_type_id){
			case 13:
				create_cisco_switch_3548(xml);
				break;
			case 60:
				LA145(xml);
				break;
			case 21:
				create_swm_16(xml);
				break;
			case 23:
				create_new_baidiplexer(xml);
				break;
			case 19:
				create_deca_adapter(xml);
				break;
			case 28:
				create_patch_panel(xml);
				break;
			case 30:
				create_patch_panel_48_port_ethernet(xml);
				break;
			case 61:
				create_cisco_switch_3548(xml);
				break;
			case 62:
				cisco3524(xml);
				break;				
			default:
					alert("Unable to determine case ");
					
			}	
			
				
		}

		equipment						= $('#' + eq_id);
		return equipment;
		
	}


	//bind to element onclick for the class "close"
	$(".close").live("click", function(){
		
		var confirmation = confirm("Are you sure you want to close this equipment");
		if(confirmation == true)
			{
				//remove parent - equipment DIV
			
				var parent_div	= $(this).parent();
				parent_div_id	= parent_div.attr('id');
				var endpoints	= parent_div.data('endpoints');
				var endpoint;
				var i=0;
				for (i=0; i<endpoints.length; i++){
					
						endpoint = endpoints[i];
						if (endpoint.isFull()){
							
							//change the color to red
							//grab the ther endpoint it is connected to
							var connection			= endpoint.connections[0];
							var connected_endpoints	= connection.endpoints;
							var v=0;
								
							
								for (v=0;v<2;v++){
									connected_endpoints[v].setStyle({fillStyle:"#FF0000"});
								}
							
							
							
							
							}
				}
				

				//var connections = jsPlumb.getConnections({target: parent_div_id});
		
				//function(params)
				jsPlumb.detachAllConnections(parent_div,{fireEvent: false});
				jsPlumb.hide(parent_div,true);
				parent_div.remove();
				jsPlumb.repaintEverything();
				
				
				
			}
	});
	
	//This function will take an equipment object and set the color endpoints to connected interfaces
	//as well as establish its connections to equipment that is already on the page
	
	function connectOnPageEquipmentHelper(connection,xml, dom_obj){

		var if_id					= $(connection).siblings('if_id').text();
		var if_id_connected_to		= $(connection).find('if_connects_to').text();
		var xml_target_eq_id		= $(connection).find('eq_id');
		var endpoint				= dom_obj.data(if_id);
		
		setEndpointColor(endpoint, null);
		var target_equipment_xml	= get_equipment('if_id', if_id_connected_to);
		var target_equip_type_id	= get_eq_type_id(target_equipment_xml);
		var target_equipment_eq_id;
		

		if(target_equip_type_id == 23 || target_equip_type_id == 22){
			target_equipment_xml	= get_baidiplexer_http(if_id_connected_to);
			target_equipment_eq_id	= get_baidiplexer_id(target_equipment_xml);
		}else{
			target_equipment_eq_id	= get_eq_id(target_equipment_xml);
			//target_equipment_eq_id	= xml_target_eq_id;
		}

		
		
		

		var target_equip_dom		= is_element_on_page(target_equipment_eq_id);
		
		if(target_equip_dom){
			var endpoint_target		= target_equip_dom.data(if_id_connected_to);
			connectEndpoints(endpoint_target, endpoint, null);
		}		
		
	}
	
	function connectOnPageEquipment(xml, dom_obj){
		
		
		//reason for having separate logic for bai diplexer is because there are internal connections
		//between the diplexer and splitters that we do not want to establish connections for
		if (isBaiDiplexer(xml)){
			

			$(xml).find('if_type_id:contains("31")').siblings('connection').each(function(){
				

				connectOnPageEquipmentHelper(this, xml, dom_obj);

				
				
			});				
			
			$(xml).find('if_type_id:contains("33")').siblings('connection').each(function(){

				connectOnPageEquipmentHelper(this, xml, dom_obj);

				
				
			});
			
			
			$(xml).find('if_type_id:contains("32")').siblings('connection').each(function(){

				connectOnPageEquipmentHelper(this, xml, dom_obj);

				
				
			});		
			

			
			

		}else{
				
			$(xml).find('connection').each(function(){
				
				connectOnPageEquipmentHelper(this, xml, dom_obj);
				
				
			})
		};
			
	}
	
	function create_endpoint(endpoint_type){
		var endpoint;
		switch(endpoint_type){
		case "switchport":
			var defaultColor = "#000000";
			var exampleColor = "#00f";
			endpoint = {
				isSource		:	true,
				isTarget		:	true,
				scope			:	"rj45",
				endpoint		:	["Rectangle", {cssClass:"switchport"}],
				connector		:	["Bezier", { curviness:63 } ],
				paintStyle		:	{ width:25, height:21, fillStyle:"#000000" },
				connectorStyle 	:	{
					gradient		:	{stops:[[0, exampleColor], [0.5, "#09098e"], [1, exampleColor]]},
					lineWidth		:	5,
					strokeStyle		:	exampleColor,
					dashstyle		:	"2 2"
				},
				
		};
			endpoint.defaultColor = defaultColor;
			break;
		case "decacoaxial":
			var exampleColor = "#FFCC00";
			var defaultColor = "FFCC00";
			endpoint = {
				isSource			: true,
				isTarget			: true,
				scope				: "decacoaxial",
				endpoint			: ["Dot"],
				paintStyle			: { fillStyle:"#FFCC00", radius:12},
				connectorStyle		: {
					gradient			:{stops:[[0, exampleColor], [0.5, "#FFCC00"], [1, exampleColor]]},
					lineWidth			:5,
					strokeStyle			:exampleColor,
					dashstyle			:"2 2"
										},
				beforeDetach		:function(conn){
				return confirm("Detach connection?");
			}
				
			};
			endpoint.defaultColor = defaultColor;
			break;
		case "patch_panel_port_ethernet":
			var exampleColor = "#00f";
			var defaultColor = "#456";
			endpoint = {
				isSource: true,
				isTarget: true,
				scope: "rj45",
				endpoint: ["Rectangle"],
				paintStyle: { width:25, height:21, fillStyle:"#456"},
				connectorStyle : {
					gradient:{stops:[[0, exampleColor], [0.5, "#09098e"], [1, exampleColor]]},
					lineWidth:5,
					strokeStyle:exampleColor,
					dashstyle:"2 2"
				},
				beforeDetach:function(conn){
				return confirm("Detach connection?");
			}
				
			};
			endpoint.defaultColor = defaultColor;
			break;			
		case "swm_coaxial":
			var exampleColor = "#990099";
			var defaultColor = "#990099";
			endpoint = {
				isSource: true,
				isTarget: true,
				scope: "swmcoaxial",
				endpoint: ["Dot"],
				paintStyle:{ fillStyle:"#990099", radius:12},
				connectorStyle : {
					gradient:{stops:[[0, exampleColor], [0.5, "#990099"], [1, exampleColor]]},
					lineWidth:5,
					strokeStyle:exampleColor,
					dashstyle:"2 2"
				},
				beforeDetach:function(conn){
				return confirm("Detach connection?");
			}
				
			};		
			endpoint.defaultColor = defaultColor;
			break;
		case "patch_panel_port":
			var exampleColor = "#00FF00";	
			var defaultColor = "#456";
			endpoint = {
				isSource: true,
				isTarget: true,
				endpoint: ["Dot"],
				connectorStyle : {
					gradient:{stops:[[0, exampleColor], [0.5, exampleColor], [1, exampleColor]]},
					lineWidth:5,
					strokeStyle:exampleColor,
					dashstyle:"2 2"
				},				
		}
			endpoint.defaultColor = defaultColor;
			break;
		}
		return endpoint;
	}

	function create_swm_16(xml){

		var swm_16			= create_new_equipment(xml);
		var eq_id			= get_eq_id(xml);
		var eq_type_id		= get_eq_type_id(xml);
		swm_16.addClass('swm16 draggable');
		swm_16.appendTo('body');
		
		var swm_label		= $('<div>');
		swm_label.addClass('swm16_label');
		var serial_number	=	$(xml).find('eq_serial_number').text();										//Define serial number
		swm_label.text('DirectTV 16 Channel ' + serial_number);
		swm_16.append(swm_label);
		
		
		var endpoints	= [];
		var swm_coaxial_endpoint = create_endpoint("swm_coaxial");
		
		
		//create endpoints
		var e0 				= jsPlumb.addEndpoint(swm_16, { anchor:"TopRight"}, swm_coaxial_endpoint);
		e0.if_id			= $(xml).find('if_index:contains("0")').siblings('if_id').text();
		e0.if_type_name		= $(xml).find('if_index:contains("0")').siblings('if_type_name').text();
		e0.defaultColor		= swm_coaxial_endpoint.defaultColor;
		var e1				= jsPlumb.addEndpoint(swm_16, { anchor:"RightMiddle"}, swm_coaxial_endpoint);
		e1.if_id			= $(xml).find('if_index:contains("1")').siblings('if_id').text();
		e1.if_type_name		= $(xml).find('if_index:contains("1")').siblings('if_type_name').text();
		e1.defaultColor		= swm_coaxial_endpoint.defaultColor;
		
		
		endpoints.push(e0, e1);
		swm_16.data(e0.if_id, e0);
		swm_16.data(e1.if_id, e1);
		swm_16.data('endpoints', endpoints);
		connectOnPageEquipment(xml, swm_16);

	}
	function get_eq_type_id(xml){
		return $(xml).find('eq_type_id').text();
	}
	
	
	//function to get bai diplexer based off any IF_ID of the components it consists of

	function get_eq_id(xml){
		return ($(xml).find('eq_id').text());
	}
	function create_patch_panel_48_port_ethernet(xml){
		var endpoints				= [];
		var patch_panel				= create_new_equipment(xml);
		var eq_id					= get_eq_id(xml);
		
		
		patch_panel.addClass('patch_panel_48 draggable');		
		var patch_panel_label_rows	= [];
		var i;
		
		//create patch panel label rows
		for (i=1; i<=3;i++){
				var className				= "patch_panel_48_row_" + i;
				var patch_panel_label_row	= $('<div>');
				patch_panel_label_row.css("padding-left", "2px");
				patch_panel_label_row.addClass(className);
				patch_panel_label_rows[i]	= patch_panel_label_row;
				patch_panel_label_row.appendTo(patch_panel);
		}
		
		patch_panel.appendTo('body');
		
		
		//for loop 48
		var switchport_endpoint = create_endpoint("patch_panel_port_ethernet");
		for (i=0; i<48; i++){
			var x_offset		= .06175;
			var x_anchor_init	= .035;
			var y_anchor_top	= .250;
			var y_anchor_middle = .550;
			var y_anchor_bottom = .850;
			
			var x_anchor;			
			var y_anchor;
			//First row
			if (i>=0 && i<=15){
				x_anchor =+ (x_anchor_init) + (i * x_offset);
				y_anchor			= y_anchor_top;
				row_to_add_label_to = patch_panel_label_rows[1];

			}
			
			if (i>=16 && i<=31){
				row_to_add_label_to = patch_panel_label_rows[2];
				if (i==16){
					//re_initialize x_anchor
					x_anchor = 0;
				}
				
				x_anchor =+ (x_anchor_init) + ((i-16) * x_offset);
				y_anchor = y_anchor_middle;
				
			}
			if (i>=32 && i<=47){
				row_to_add_label_to = patch_panel_label_rows[3];
				if (i==32){
					//re_initialize x_anchor
					x_anchor = 0;
				}
				x_anchor =+ (x_anchor_init) + ((i-32) * x_offset);
				y_anchor = y_anchor_bottom;
								
			}	
			var endpoint			= jsPlumb.addEndpoint($("#" + eq_id), { anchor:[ x_anchor, y_anchor, 0, -.25]}, switchport_endpoint);
			
			//create a label for this endpoint
			var span_label = $('<div>');
			span_label.css("border", "1px solid white");
			span_label.css("float", "left");
			span_label.css("color", "#FFFFFF");
			span_label.css("margin-right", "6.25px");
			span_label.css("width", "40px");
			span_label.css("text-align", "center");
			span_label.text(i + 1);
			span_label.appendTo(row_to_add_label_to);
			
			endpoint.defaultColor	= switchport_endpoint.defaultColor;
			//if_id
			var if_type_name;
			var if_id = $(xml).find('if_index').filter(function(){
				return $(this).text() == i;
				
			}).siblings('if_id').text();
			endpoint.if_id = if_id;
			endpoints.push(endpoint);
			patch_panel.data(endpoint.if_id, endpoint);
			
			
			
		}
		

		patch_panel.data('endpoints', endpoints);
		connectOnPageEquipment(xml, patch_panel);
	}
	function create_patch_panel(xml){
		//alert("creating patch panel port");
		var endpoints						= [];
		var patch_panel						= create_new_equipment(xml);
		var eq_id							= get_eq_id(xml);
		
		patch_panel.addClass('patch_panel_32 draggable');
		patch_panel.appendTo('body');

		var pp_endpoint						= create_endpoint("patch_panel_port");
		var patch_panel_label_top_row		= $('<div>');
		patch_panel_label_top_row.addClass('patch_panel_label_row_top');
		var patch_panel_label_bottom_row 	= $('<div>');
		
		
		patch_panel_label_bottom_row.addClass('patch_panel_label_row_bottom');
		patch_panel_label_top_row.appendTo(patch_panel);
		patch_panel_label_bottom_row.appendTo(patch_panel);
		
		var i=1;		
		for (i=1; i<=32; i++){
			//odd-top row
			var patch_panel_label_group = $('<div>');
			patch_panel_label_group.addClass('pp_label_group');
			var patch_panel_label 		= $('<span>');
			
			
			patch_panel_label.text(i);
			patch_panel_label.addClass('pp_label');
			patch_panel_label.appendTo(patch_panel_label_group);
			
			if (i%2 == 0){
				
				patch_panel_label_group.appendTo(patch_panel_label_bottom_row);
			}
			//even bottom row
			if (i%2 == 1){
				
				patch_panel_label_group.appendTo(patch_panel_label_top_row);
			}


			

		}
		
		//for each inteface, add an endpoint
		var v 				= 0;
		var x_anchor		= .035;
		var y_anchor_top	= .370;
		var y_anchor_bottom = .825;
		var x_offset		= .06175;
		
		var y_anchor;		
		for (v=0; v<32; v++){
			//if odd - top row
			//if even - bottom row

			//if odd
			if (v%2 == 0){
				y_anchor		= y_anchor_top;
				//IF not first element, there is no offset
				if (v!=0){
					x_anchor	+= x_offset;

				}

			}
			else{
				y_anchor = y_anchor_bottom;
			}
			//
			var endpoint			= jsPlumb.addEndpoint($("#" + eq_id), { anchor:[ x_anchor, y_anchor, 0, -.25]}, pp_endpoint);
			endpoint.if_id			= ($(xml).find('interface').eq(v).find('if_id').text());
			endpoint.if_type_name	= ($(xml).find('interface').eq(v).find('if_type_name').text());
			endpoint.defaultColor	= pp_endpoint.defaultColor;
			
			
			patch_panel.data(endpoint.if_id, endpoint);
			endpoints.push(endpoint);
			
			
			//for each patch panel port, we need to programatically create the connections

			
			
		}
		patch_panel.data('endpoints', endpoints);
		connectOnPageEquipment(xml, patch_panel);

		

	}
	function create_new_equipment(xml){

		var eq_id				= ($(xml).find('eq_id').text());
		var eq_serial_number	= ($(xml).find('eq_serial_number').text());
		var eq_model_name		= ($(xml).find('eq_model_name').text());
		var new_div				= $('<div>');		
		
				new_div.data('serial_number', eq_serial_number);
				new_div.data('equipment_model_name', eq_model_name);
				new_div.attr('id', eq_id);
				new_div.addClass('equipment draggable');
		
    	var close_div_elem		= $('<div>');
    	
		    	close_div_elem.text('X');
		    	close_div_elem.addClass('close');
		    	close_div_elem.appendTo(new_div);
    	
    	jsPlumb.draggable(new_div, {containment: 'document'});
		return new_div;
	}

	function create_cisco_switch_3548(xml)
	{
		
		var new_div				= create_new_equipment(xml);
		new_div.addClass('switch equipment draggable');
		var eq_id				= ($(xml).find('eq_id').text());
		new_div.appendTo('body');
		
		var endpoint_collection = [];
		var switchportEndpoint	= create_endpoint("switchport");

		/*
		 * For each interface, grab the interface from the XML of that index
		 */
		
		var x_anchor_block_1 	= .035;
		var x_anchor_block_2 	= .357;
		var x_anchor_block_3 	= .683;
		var y_anchor_top 		= .50;
		var y_anchor_bottom		= .78;
		var x_offset			= .037;
		var i=0;		
		for (i=0;i<48;i++){

			//block 1 
			if (i==0){
				
					var switchport_block	= $('<div>');
					switchport_block.addClass('portblock');
					switchport_block.appendTo(new_div);

				}
			if (i == 15){
				var switchport_block		= $('<div>');
				switchport_block.addClass('portblock');
				switchport_block.appendTo(new_div);

			}
			if (i == 31){
				var switchport_block		= $('<div>');
				switchport_block.addClass('portblock');
				switchport_block.appendTo(new_div);
			}
			
			//add a switchportlabel instance if the number is odd
			if(i%2 == 0){
				$div_spl = $('<div>');
				$div_spl.addClass('switchportlabel');
				$div_pl_1 = $('<div>');
				$div_pl_1.addClass('portlabel');
				$div_pl_1.text(i+1);
				$div_pl_1.appendTo($div_spl);
				$div_pl_2 = $('<div>');
				$div_pl_2.addClass('portlabel');
				$div_pl_1.text(i+2);
				$div_pl_2.appendTo($div_spl);
				
				

			}
			
			if (i>=0 && i<=15){
				
				var anchor_x;
				var anchor_y;
				//if odd top row
				if (i == 0){
					anchor_x = x_anchor_block_1;
					anchor_y = y_anchor_top;
				}
				if (i !== 0){
					
					
					//bottom
					if (i%2 == 0){
						anchor_y = y_anchor_top;
						x_anchor_block_1 += x_offset;
					}
					
					if (i%2 == 1){
						
						anchor_y = y_anchor_bottom;
					}
					anchor_x = x_anchor_block_1;
				}

				
			}

			if (i>=16 && i<=31){
				
				var anchor_x;
				var anchor_y;
				//if odd top row
				if (i == 16){
					anchor_x = x_anchor_block_2;
					anchor_y = y_anchor_top;
				}
				if (i !== 16){
					
					
					//bottom
					if (i%2 == 0){
						anchor_y = y_anchor_top;
						x_anchor_block_2 += x_offset;
					}
					
					if (i%2 == 1){
						
						anchor_y = y_anchor_bottom;
					}
					anchor_x = x_anchor_block_2;
				}

				
			}			
			
			
			
			if (i>=32 && i<=47){
				
				var anchor_x;
				var anchor_y;
				//if odd top row
				if (i == 32){
					anchor_x = x_anchor_block_3;
					anchor_y = y_anchor_top;
				}
				if (i !== 32){
					
					
					//bottom
					if (i%2 == 0){
						anchor_y = y_anchor_top;
						x_anchor_block_3 += x_offset;
					}
					
					if (i%2 == 1){
						
						anchor_y = y_anchor_bottom;
					}
					anchor_x = x_anchor_block_3;
				}

				
			}			
			
			var endpoint			= jsPlumb.addEndpoint($("#" + eq_id), { anchor:[ anchor_x, anchor_y, 0, -.25]}, switchportEndpoint);
			//endpoint.if_id			= ($(xml).find('interface').eq(i).find('if_id').text());
			
			endpoint.if_id			= ($(xml).find('interface').find('if_index').filter(function(){return $(this).text() == i}).parent().find('if_id').text());
			endpoint.if_type_name	= ($(xml).find('interface').find('if_index').filter(function(){return $(this).text() == i}).parent().find('if_type_name').text());
			
			//endpoint.if_type_name	= ($(xml).find('interface').eq(i).find('if_type_name').text());
			endpoint.defaultColor	= switchportEndpoint.defaultColor;
			new_div.data(endpoint.if_id, endpoint);
			
			endpoint_collection.push(endpoint);			
			
			
			
				
			}
		
		/*
		 * This section needs to be cleaned up.  Adding gi0/1 & gi0/2 manually.
		 */

		//endpoint.if_id			= ($(xml).find('interface').eq(i).find('if_id').text());
		var endpoint			= jsPlumb.addEndpoint($("#" + eq_id), { anchor:[ .98, .50, 0, -.25]}, switchportEndpoint);
		endpoint.if_id			= ($(xml).find('interface').find('if_index').filter(function(){return $(this).text() == 48}).parent().find('if_id').text());
		endpoint.if_type_name	= ($(xml).find('interface').find('if_index').filter(function(){return $(this).text() == 48}).parent().find('if_type_name').text());
		
		//endpoint.if_type_name	= ($(xml).find('interface').eq(i).find('if_type_name').text());
		endpoint.defaultColor	= switchportEndpoint.defaultColor;
		new_div.data(endpoint.if_id, endpoint);
		
		endpoint_collection.push(endpoint);	
		
		new_div.data('endpoints', endpoint_collection);
		var endpoint			= jsPlumb.addEndpoint($("#" + eq_id), { anchor:[ .98, .765, 0, -.25]}, switchportEndpoint);
		endpoint.if_id			= ($(xml).find('interface').find('if_index').filter(function(){return $(this).text() == 49}).parent().find('if_id').text());
		endpoint.if_type_name	= ($(xml).find('interface').find('if_index').filter(function(){return $(this).text() == 49}).parent().find('if_type_name').text());
		
		//endpoint.if_type_name	= ($(xml).find('interface').eq(i).find('if_type_name').text());
		endpoint.defaultColor	= switchportEndpoint.defaultColor;
		new_div.data(endpoint.if_id, endpoint);
		
		endpoint_collection.push(endpoint);	
		
		new_div.data('endpoints', endpoint_collection);
		
		connectOnPageEquipment(xml, new_div);		


		


		
	}
	

	function create_deca_adapter(xml){
		var e0; //define coaxial endpoint
		var e1; //define ethernet endpoint
		var endpoint_collection = [];
		
		var sn							= $(xml).find('eq_serial_number').text();										//Define serial number
		var eq_id						= $(xml).find('eq_id').text();
		var eth_if_id					= $(xml).find('if_type_id:contains("22")').siblings('if_id');
		var eth_if_type_name			= $(xml).find('if_type_id:contains("22")').siblings('if_type_name').text();
		var coax_if_id					= $(xml).find('if_type_id:contains("23")').siblings('if_id');
		var coax_if_type_name			= $(xml).find('if_type_id:contains("23")').siblings('if_type_name').text();
		var coax_if_connects_to_id		= coax_if_id.parent().find('if_connects_to').text();
		var ethernet_if_connects_to_id	= eth_if_id.parent().find('if_connects_to').text();
		
		

		//get the interface at the coax_connects to
		var eth_connection				= eth_if_id.siblings('connection').find('if_connects_to');
		var coax_connection				= coax_if_id.siblings('connection').find('if_connects_to');

    	
    	var new_div						= create_new_equipment(xml);
		var nested_div					= $('<div>');
		
		nested_div.addClass('decalabel label');
		nested_div.text(sn);
		nested_div.appendTo(new_div);
		new_div.addClass('deca draggable').appendTo('body');
		
		var selector					= "#"+eq_id;
		
		var decaCoaxialEndpoint			= create_endpoint("decacoaxial");
		var switchportEndpoint			= create_endpoint("switchport");

		e0 								= jsPlumb.addEndpoint($(selector), { anchor:[0,1,0,1] }, decaCoaxialEndpoint);
		e0.defaultColor					= decaCoaxialEndpoint.defaultColor;
		//assign interface id to e0
		e0.if_id						= coax_if_id.text();
		e0.if_type_name					= coax_if_type_name;
		e1 = jsPlumb.addEndpoint($(selector), { anchor:[1,1,0,1] }, switchportEndpoint);
		e1.defaultColor					= switchportEndpoint.defaultColor;
		e1.if_id						= eth_if_id.text();
		e1.if_type_name					= eth_if_type_name;
		
		new_div.data(coax_if_id.text(), e0);
		new_div.data(eth_if_id.text(), e1);
		endpoint_collection.push(e0);
		endpoint_collection.push(e1);
		
		new_div.data('endpoints', endpoint_collection);
		connectOnPageEquipment(xml, new_div);
		
		
	}


	jsPlumb.bind("jsPlumbConnection", function(info){
		

		$.ajax({
			url		: "/inventory/addconnection",
			type	: "POST",
			data	: "src_if_id="+info.targetEndpoint.if_id+"&dst_if_id="+info.sourceEndpoint.if_id,
			dataType: 'text',
		    success	: function(data) {
		    	
					    	setConnectedEndpointsColor(info.targetEndpoint, info.sourceEndpoint, null);

		    },
			error	: function(t,error,ts){
							alert("error status:" + t.status + " text: " + t.statusText);
			jsPlumb.detach(info.connection);
							alert("detach successful");
			}
		});
		
	});
	
	jsPlumb.bind("jsPlumbConnectionDetached", function(info){
		//alert(info.targetEndpoint.if_id);
		//alert(info.sourceEndpoint.if_id);
		//alert("detaching connection");
		$.ajax({
			url		: "/inventory/removeconnection",
			type	: "POST",
			data	:"src_if_id="+info.targetEndpoint.if_id+"&dst_if_id="+info.sourceEndpoint.if_id,
			dataType: 'text',
			error	: function(t,error,ts){
							alert("Unable to complete ajax call");
							return 0;
			},
			success	: function(data){
				/*
				 * Need to perform error checking
				 */

				info.sourceEndpoint.setStyle({fillStyle:info.sourceEndpoint.defaultColor});
				info.targetEndpoint.setStyle({fillStyle:info.targetEndpoint.defaultColor});
				var error	= $(data).find('error').text();
				
				if (error 	!= "" && error != null)
					{
						alert("There was an error disconnecting the connection: "  + error);
					}
				
			}
		});

		
	});
	$("#add_equipment").bind("click", function(){
		
		
		
		var serial_number	= prompt("Please provide serial-number");
		var equipment_xml	= get_equipment("serial_number",serial_number);
		var equipment_id	= get_eq_id(equipment_xml);
		
		
		if (equipment_id != null && equipment_id !=""){
			if ($("#"+equipment_id).length > 0 )
			{
				alert(serial_number + " is already on page");
				//$("#"+serial_number).show();
				//jsPlumb.show($("#"+serial_number));
				
				
				return;
			}
		}
		//1. check if equipment already exist on page, it so - alert user and exit
		

		
		//2. make sure that the serial number is not NULL or EMPTY
		//		If not, make an HTTP request to retrieve based of that serial number
		
		if (serial_number !=null && serial_number!="")
			{
				//alert(serial_number);
			$.ajax({
				url		: "/inventory/geteqifxml",
				type	: "GET",
				data	:"eq_serial_number="+serial_number,
				error	: function(t, error,ts){
								alert("Error adding " + serial_number);
				},
				dataType: 'xml',
				success	: function(xml){
								
								var equip = $(xml).find('equipment');
								if(!equip.length > 0){
									alert("The serial number " + serial_number + " does not exist in the database");
									return;
								}
								
								$equip_type_id = parseInt(equip.find('eq_type_id').text());
					
					//depending on equipment type
								switch($equip_type_id)
								{
								case 60:
									LA145(xml);
									break;
								case 12:
									cisco3524(xml);
									break;
								case 13:
									
									create_cisco_switch_3548(xml);
									break;
								case 19:
									
									create_deca_adapter(xml);
									break;
								case 28:
									
									create_patch_panel(xml);
									break;
								case 21:
									create_swm_16(xml);
									break;
								case 30:
									create_patch_panel_48_port_ethernet(xml);						
									break;
								case 61:
									create_cisco_switch_3548(xml);
									break;
								case 62:
									cisco3524(xml);
									break;
								}

						
					/*
					 * Once the equipment has been created, we must programatically create the connections
					 * to the other pieces of equipment that are on the page.
					 * 
					 * Considerations: if there is already a connection on the endpoint, DO NOT create it
					 * For the coax interface, check if there is a connection for it
					 * Then, make an AJAX call to determine the equipment type of the IF_ID that it connects to
					 * If that equipment_type_name is GENERIC SPLITTER, make a final call to bai diplexer and return XML
					 */
					
				}
			});
			
			}
		
	});
	
	function create_new_baidiplexer(xml){
		var generic_splitter		= $(xml).find('equipment').find("eq_model_name:contains('Generic_Splitter')").parent();
    	var diplexer_1				= $(xml).find('equipment').find("eq_model_name:contains('Generic_Diplexer')").slice(0,1).parent();
    	var diplexer_2				= $(xml).find('equipment').find("eq_model_name:contains('Generic_Diplexer')").slice(1,2).parent();

    	//var l_band_in = generic_splitter.find('interface').find("if_type_name:contains('L_band_IN')").parent().find('if_id').text();
    	
    	//L_BAND_IN
    	generic_splitter.l_band_in = generic_splitter.find('interface').find("if_type_name:contains('L_band_IN')").siblings('if_id');
    	
    	//L_BAND_UHF OUT for diplexer 1
    	diplexer_1.l_band_uhf		= diplexer_1.find('interface').find("if_type_name:contains('L_band_UHF')").siblings('if_id');
    	
    	diplexer_1.uhf_in			= diplexer_1.find('interface').find("if_type_name:contains('UHF_IN')").siblings('if_id');
    	//L_BAND_UHF OUT for diplexer 2
    	diplexer_2.l_band_uhf 		= diplexer_2.find('interface').find("if_type_name:contains('L_band_UHF')").siblings('if_id');
    	diplexer_2.uhf_in			= diplexer_2.find('interface').find("if_type_name:contains('UHF_IN')").siblings('if_id');
    	

    	/*
    	 * This section creates the BAI Diplexer and the appropriate endpoints
    	 */
    	//The ID of the BAI Diplexer will be the IF_ID of the generic_splitter for L_BAND_IN
    	
    	
    	var new_div			= $('<div>');
    	//assign data values to the DIV
    	new_div.data()
    	new_div.attr('id',generic_splitter.l_band_in.text());	//set bai diplexer unique ID
    	new_div.data('serial_number', "NONE");
    	var close_div_elem	= $('<div>');
    	
    	close_div_elem.text('X');
    	close_div_elem.addClass('close');
    	close_div_elem.appendTo(new_div);
    	
		var nested_div 		= $('<div>');
		
		nested_div.addClass('baidiplexer_label label');
		nested_div.text('BAI Diplexer');
		nested_div.appendTo(new_div);
		new_div.addClass('baidiplexer equipment').appendTo('body');
		
		coaxialEndpoint				= create_endpoint("patch_panel_port");
		var decaCoaxialEndpoint 	= create_endpoint("decacoaxial");
		var patch_panel_endpoint	= create_endpoint("patch_panel_port");

		
		var e0 						= jsPlumb.addEndpoint(new_div, { anchor:"TopLeft" }, patch_panel_endpoint);
		e0.if_id					= diplexer_1.l_band_uhf.text();
		e0.defaultColor 			= patch_panel_endpoint.defaultColor;
		var e1 						= jsPlumb.addEndpoint(new_div, { anchor:"TopRight" }, patch_panel_endpoint);
		e1.if_id 					= diplexer_2.l_band_uhf.text();
		e1.defaultColor 			= patch_panel_endpoint.defaultColor;
		var e2 						= jsPlumb.addEndpoint(new_div, { anchor:"BottomLeft" }, decaCoaxialEndpoint);
		e2.if_id 					= diplexer_1.uhf_in.text();
		e2.defaultColor 			= decaCoaxialEndpoint.defaultColor;
		var swm_coaxial_endpoint	= create_endpoint("swm_coaxial");
		var e3 						= jsPlumb.addEndpoint(new_div, { anchor:"BottomCenter" }, swm_coaxial_endpoint);
		e3.defaultColor 			= swm_coaxial_endpoint.defaultColor;
		e3.if_id 					= generic_splitter.l_band_in.text();
		var e4 						= jsPlumb.addEndpoint(new_div, { anchor:"BottomRight" }, decaCoaxialEndpoint);
		e4.if_id 					= diplexer_2.uhf_in.text();
		e4.defaultColor 			= decaCoaxialEndpoint.defaultColor;
		
		new_div.data(diplexer_1.l_band_uhf.text(), e0);
		new_div.data(diplexer_2.l_band_uhf.text(), e1);
		new_div.data(diplexer_1.uhf_in.text(), e2);
		new_div.data(generic_splitter.l_band_in.text(),e3);
		new_div.data(diplexer_2.uhf_in.text(), e4);
		
    	var endpoints				= [];		
		endpoints.push(e0,e1,e2,e3,e4);
		new_div.data('endpoints', endpoints);
		jsPlumb.draggable(new_div);
		connectOnPageEquipment(xml, new_div);

	};
	
	$("#add_baidiplexer").click(function(){
		var bai_diplexer_xml = Baidiplexer();
		create_new_baidiplexer(bai_diplexer_xml);

	});

});