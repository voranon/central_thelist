$(function(){
	$("th").click(function(){
		var currentTH = $(this);
		
		/*
		*/
		//alert(currentTH);
		//alert(currentTH.attr("class"));
		
		//get index of the TH element that is clicked
		//store this in $index
		
		var th_index = currentTH.parent().children().index(currentTH);
		
		//var th_index = ($("table th").index(currentTH));


		
		var first_row = $(this).parent().parent();										//get the first row of the table; this contains the TH elements
		var data_rows = $(this).parent().parent().children().slice(1);					//get all the data rows in the table that contain the TD elements

		data_rows.sort(sortTable).appendTo(first_row);									//sort all the rows and then append to the first row
		
		
		function sortTable(a,b){  
			
			var a_cell_ref = a.cells[th_index];
			var b_cell_ref = b.cells[th_index];
			
			
			//get the class name of the TD element to determine how to sort
			switch(a_cell_ref.className){
				case "date":
					var date_a = new Date(a_cell_ref.innerHTML);
					var date_b = new Date(b_cell_ref.innerHTML);			
					return date_a > date_b ? 1: -1;
					break;
					
					
					
				case "string":
					if (a_cell_ref.innerHTML.toLowerCase() < b_cell_ref.innerHTML.toLowerCase()){
						return -1;
					}
					if (a_cell_ref.innerHTML.toLowerCase() > b_cell_ref.innerHTML.toLowerCase()){
						return 1;
					}			
					return 0;
					
					break;
					
					
				case "ipaddress":
					
					var array_a_cell_ip = convertToIP(a_cell_ref.innerHTML);
					var array_b_cell_ip = convertToIP(b_cell_ref.innerHTML);
					
					
					var i=0;
					for(i=0;i < 4; i++){
						var int_a_octet = Number(array_a_cell_ip[i]);
						var int_b_octet = Number(array_b_cell_ip[i]);
						if (int_a_octet < int_b_octet){
							return -1;
						}
						if (int_a_octet > int_b_octet){
							return 1;
						}
					}
					break;
					
				case "macaddress":
					var array_a_cell_ip = convertToMac(a_cell_ref.innerHTML);
					var array_b_cell_ip = convertToMac(b_cell_ref.innerHTML);
					
					for(i=0;i < 6; i++){
						var int_a_octet = parseInt("0x" + array_a_cell_ip[i],16);
						var int_b_octet = parseInt("0x" + array_b_cell_ip[i], 16);
						if (int_a_octet < int_b_octet){
							return -1;
						}
						if (int_a_octet > int_b_octet){
							return 1;
						}
					}
					break;
			}
		};

		function convertToIP(str_ip){
			
			var array_ip = str_ip.split(".");
			return array_ip;
		}
		
		function convertToMac(str_mac){
			if(str_mac =="incomplete"){
				
				return ["0x00","0x00","0x00","0x00","0x00","0x00"]
				
			}
			
			var array_mac = str_mac.split(":");
			return array_mac;
		}



		});
});