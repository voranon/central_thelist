
	function get_cam_table(hostname,password,enable_password,if_name){

		var query_string = "hostname="+hostname+"&password="+password+"&enable_password="+enable_password;
		
		if (if_name != null && if_name != ""){
			query_string += "&interface=" + if_name;
		}

		var xml_cam_table;
		$.ajax({
			url: "/device/getcamtable",
			type: "GET",
			async: false,
			data:query_string,
			error: function(t, error,ts){
				alert("Errow getting cam table " + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				xml_cam_table = xml;
				
			}})


			return xml_cam_table;
	
	

	
	
	}
	
	function get_device_interfaces(hostname,password,enable_password){
		var query_string = "hostname="+hostname+"&password="+password+"&enable_password="+enable_password;
		var interfaces_xml;
		$.ajax({
			url: "/device/getinterfaces",
			type: "GET",
			async: false,
			data:query_string,
			error: function(t, error,ts){
				alert("Unable to get interfaces " + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				interfaces_xml = xml;
				
			}})


			return interfaces_xml;		
	}
	
	function format_macaddress(macaddress, format){
		var query_string = "macaddress="+macaddress+"&format="+format;
		var formatted_mac;
		$.ajax({
			url: "/device/formatmacaddress",
			type: "GET",
			async: false,
			data: query_string,
			error: function(t, error,ts){
				alert("Unable to get interfaces " + value);
			},
			dataType: 'text',
			success: function(text){
				
				formatted_mac = text;
				
			}})


			return formatted_mac;		
	}	
	//http://matthew-zend-dev.belairinternet.com/device/getdhcpaddress/hostname/bairos-1.1823g.4th.belairinternet.com/password/Pump4k11ne&/username/root
	function get_dhcp_fixed_addresses(hname, uname, pword){
		var hostname = hname;
		var username = uname;
		var password = pword.replace(/\&/, '%26');
		var query_string = "hostname=" + hostname + "&username="+username+"&password="+password;
		var fixed_addresses;
		$.ajax({
			url: "/device/getdhcpaddress",
			type: "GET",
			async: false,
			data: query_string,
			error: function(t, error,ts){
				alert("Unable to get interfaces " + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				fixed_addresses = xml;
				
			}})
			
			return fixed_addresses;

		
	}
	function configure_interface(query_string){
		$.ajax({
			url: "/device/configureinterface",
			type: "GET",
			async: false,
			data: query_string,
			error: function(t, error,ts){
				alert("Unable to configure switch " + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				
				
			}})
	}
	function get_interface_status(hostname, username, password, enable_password, inter){

		var query_string = 'hostname=' + hostname +"&username=" + username + "&password=" + password + "&enable_password=" + enable_password + "&interface=" + inter;
		var interfacestatus;
		$.ajax({
			url: "/device/getinterfacestatus",
			type: "GET",
			async: false,
			data: query_string,
			error: function(t, error,ts){
				alert("Unable to post execute commands " + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				interfacestatus = $.xml2json(xml);
				
			}})		
			return interfacestatus;;		
		
	};
	//commands must already be encoded
	function bairouterexecutecmds(hostname,username,password,commands){
		var query_string = 'hostname=' + hostname +"&username=" + username + "&password=" + password + "&" + commands;
		var commands_result;
		$.ajax({
			url: "/device/bairouterexecutecmds",
			type: "POST",
			async: false,
			data: query_string,
			error: function(t, error,ts){
				alert("Unable to post execute commands " + value);
			},
			dataType: 'xml',
			success: function(xml){
				
				commands_result = xml;
				
			}})		
			return commands_result;
	}
