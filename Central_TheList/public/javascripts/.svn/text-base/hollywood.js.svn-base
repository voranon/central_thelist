$(function(){
	var selected_switch;
	var switchport;
	var directtv_required;
	var speed_configuration;
	var dhcp_configuration;
	var directtv_switchport_status;
	function initialize_data(){

		
		$(".readonly").attr("disabled", true);
		//$('#unit_number').attr("disabled", true);	
		$('input[id=ipaddress]').attr("disabled", true);
//		$("select.properties option[id=1823g]").data('router', 'bairos-1.1823g.4th.belairinternet.com');
//		$("select.properties option[id=1820w]").data('router', 'bairos.1820w.4th.belairinternet.com');
//		$("select.properties option[id=1837w]").data('router', 'bairos.1837w.4th.belairinternet.com');
//		$("select.properties option[id=6600y]").data('router', 'bairos-6600y.rt.belairinternet.com');
//		$("select.properties option[id=130s]").data('router', 'bairos-1.130s.mpoe.belairinternet.com');
		
		$('body').data('router_username', 'root');
		$('body').data('router_password', 'Pump4k11ne&');
		$('body').data('password', 'WiGwOoU');
		$('body').data('enable_password', 'nitram');
		$('img[id=loading]').hide();
		//default the speeds for hollywood to 10x5
		$('#downloadspeed').val('10000');
		$('#uploadspeed').val('5000');
		
		//Hide More Options
		$('#more_options').hide();
		//$('#more_options #content').hide();	
		directtv_required = $('#directtv-label').parent().parent();
		directtv_required.hide();
		$('input[type=text][name=unit_number]').focus();
		
		
	}
	$('input[type=text], select').bind('focus', function(){
		$('input[type=text], select').each(function(){
			//alert($(this).css('border'));
			$(this).css('border', '1px solid #ccc');
		});
		
		$(this).css('border', '1px solid #FF0000');
	});
	
	$('#more_options #header').bind('click', function(){
		$('#more_options #content').toggle();
	});
	
	jQuery(document).ajaxStart(function(){
		$('img[id=loading]').show();
		})

	jQuery(document).ajaxStop(function(){
		$('img[id=loading]').hide();
		})
	//when the selection of the switch changes
	
		
		function isPortInUse(){
		
		var hostname 				= $('#switches option:selected').text();
		var switchport_interface	= $('#switchport option:selected').text();		
		var query_string 			= 'switch=' + hostname + "&switchport=" + switchport_interface;
		var in_use					= true;
		$.ajax({
			url: "/unit/isportconfigured",
			type: "GET",
			async: false,
			data:query_string,
			error: function(t, error,ts){
				alert("Errow validating switchport / switch LINE 52 " + value);
			},
			dataType: 'text',
			success: function(text){
				if(text == 0){
					in_use			= false;
				}

				
			}})
			return in_use;
	}
	function isDirectTvInstall(){
		var directtv_checkbox	= $('input[name=directtv][type=checkbox]');
		return directtv_checkbox.attr('checked');
	}	
	$("#ConfigureUnit").bind("click", function(){
		

		if(isDirectTvInstall()){
			var interface_status	= directtv_switchport_status.val();
			if(interface_status == 'down'){
				alert("Direct TV Connection interface is down. Please check cable or select correct switch port");
				return false;
			}
		}

		if(isPortInUse() == true){
			alert("Port is already configured for another unit. Please verify correct switch and port.");
			return false;
		}

		var patch_panel	=	$('#patch_panel').val();
		if (patch_panel == null || patch_panel == "" || isNaN(patch_panel)){
			alert("Patch panel is not a valid number");
			$('#patch_panel-label').css('color', '#FF0000');
			return false;
		}
		
		var unit_number = $('#unit_number').val();
		if(unit_number == "" || unit_number == null){
			alert("Please input a valid Unit Number");
			return false;
		}
		var mac_address	=	$('#macaddress').val();
		if (mac_address == "" || mac_address == null){
			alert("Please select a valid mac address. If this is value is emtpy, confirm the correct port is selected.  Contact networkengineering@belairinternet.com for additional support");
			$('#macaddress-label').css('color', '#FF0000');
			return false;
		}
		
		$('img[id=loading]').show();
		$(this).attr("disabled", true);
		return true;

		
	})
	
	
	
	function create_select_option(text, value){
		var option = "<option value='" + value + "'>" + text + "</option>";
		return option;
	}
	initialize_data();
	//when a different switch is selected, bind to that event
	$('#switches').change(function(){
		var password 				= $('body').data('password');
		var enable_password	 		= $('body').data('enable_password');
		var hostname 				= $('#switches option:selected').text();
		var switchport_interface 	= $('#switchport option:selected').text();
		var interfaces 				= get_device_interfaces(hostname,password,enable_password);
		//alert(device.find('eq_id').text());
		$('#switchport').empty();
		$(interfaces).find('interface').each(function(){
			var if_name = $(this).find('name').text();
			var option = create_select_option(if_name,if_name);
			$('#switchport').append(option);
		})
		
		$('#switchport').trigger('change');
	});
	
	
	$('#directtv_switch').bind('change', function(){

		var password				= $('body').data('password');
		var enable_password			= $('body').data('enable_password');
		var hostname				= $('#directtv_switch option:selected').text();
		var switchport_interface 	= $('#directtv_switchport').text();
		var interfaces 				= get_device_interfaces(hostname,password,enable_password);
		//alert(device.find('eq_id').text());
		

		$('#directtv_switchport').empty();
		
		$(interfaces).find('interface').each(function(){
			var if_name = $(this).find('name').text();
			var option = create_select_option(if_name,if_name);
			$('#directtv_switchport').append(option);
		})
		
		
		$('#directtv_switchport').trigger('change');	
		
		
	});	
	$('#directtv_switchport').bind('change', function(){

		var selected_directtv_switchport	= $(this).find('option:selected').val();
		var password 						= $('body').data('password');
		var enable_password 				= $('body').data('enable_password');
		var hostname						= $('#directtv_switch option:selected').text();
		var username						= '';
		var switchport_status;
		switchport_status					= get_interface_status(hostname, username, password, enable_password, selected_directtv_switchport);
		directtv_switchport_status		= $('#directtv_switchport_status');
		directtv_switchport_status.val(switchport_status.status);
		var directtv_interface_label	= directtv_switchport_status.parent().siblings().find('.interface_status');
		if(switchport_status.status == 'up'){
			directtv_interface_label.css('color', '#336600');
			directtv_interface_label.text('Interface Status √');
		}else if(switchport_status.status == 'down'){
			directtv_interface_label.css('color', '#FF0000');
			directtv_interface_label.text('Interface Status X');
		}

	});
	
	//Set elements to read only
	

	$("select.properties").change(function(){
		//set the text label to normal
		var selectedIndex = $(this)[0].selectedIndex;
		var select_switches = $('#switches');
		$("select.properties option:selected").each(function(){
			if(!selectedIndex == 0){
				$('#unit_number').attr("disabled", false);
				var selected_property = get_selected_property();
				get_connected_switch(selected_property);
				
			
			}
			else{
				$('#unit_number').val("");
				$('#unit_number').attr("disabled", true);	
				select_switches.empty().end().append('<option></option>');
			}
			
			//get_next_available_ipaddress(get_selected_property());
			
		});			
		

		
	
	});

	$('#switchport').bind('change', function(){
		$(this).parent().parent().find('label').css('color', '#000000')
		var select_mac_address = $('#macaddress');
		select_mac_address.parent().parent().find('label').css('color', '#000000')
		select_mac_address.empty().end();
		var password = $('body').data('password');
		var enable_password = $('body').data('enable_password');
		var hostname = $('#switches option:selected').text();
		var switchport_interface = $('#switchport option:selected').text();
		var xml_cam_table = get_cam_table(hostname,password,enable_password,switchport_interface);
		
		$(xml_cam_table).find('macaddress').each(function(){
			var formatted_mac_address = format_macaddress($(this).text(),':');
			var option = create_select_option(formatted_mac_address, formatted_mac_address);
			select_mac_address.append(option);
		})

	})
	function get_selected_property(){
		return $("select.properties option:selected").val();
	}
	function get_unit_number(){
		
		var unit_number = $('#unit_number').val();
		return unit_number;
	}
	function is_valid_unit_number(unit_number){
		if(unit_number == null || unit_number == ""){
			return false;
		}
		return (!isNaN(unit_number));
	}
	
	function get_connected_switch(selected_property){
		
		directtv_required.hide();
		var property 				= selected_property;
		//var property_switches;
		var select_switches 		= $('#switches');
		var more_options_subform	= $('#more_options');
		more_options_subform.hide();
		/*
		 * Currently there is a bug with xml2jso;
		 * If there is only one element in the object model, it will not return an array
		 */
		var property_switches		= get_edge_switches(property);
		var property_switches		= property_switches.edge_switch;
		
		switch(property){
		
		case "130s":

			directtv_required.show();
			
		}

		select_switches.empty().end().append('<option></option>');
		
		var direct_tv_switch	= more_options_subform.find('#directtv_switch');
		direct_tv_switch.empty().end().append('<option></option>');


		for (i=0;i<property_switches.length;i++){
			var option 				= "<option value='" + property_switches[i].serial_number + "'>" + property_switches[i].hostname + "</option>";

			select_switches.append(option);
			direct_tv_switch.append(option);
			
		}

		$('#switches').trigger('change');
		if (property == '130s'){
			$('#directtv_switch').trigger('change');
			$('input[name=directtv]').trigger('change');
			
		}


		
			
		

		
	};
	
	

	

	function get_selected_router(){

		return $('select.properties option:selected').data('router');
	}
	
	$('input[name=directtv]').bind('change', function(){
		var more_options	= $('#more_options');
		if ($(this).attr('checked')){
			more_options.show();	
		}else{
			more_options.hide();
		}
		
	});
	
	function get_edge_switches(property){
		var query_string = "property=" + property;
		var switches;
		$.ajax({
			url: "/unit/getswitches",
			type: "GET",
			async: false,
			data:query_string,
			error: function(t, error,ts){
				alert("Unable to get edge switches for the property: " + property + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				switches =  $.xml2json(xml);
				
			}})


			return switches;	
		
	};
	
	
})