/*
This is to validate adding forms

*/

//lksjlkfdjs
$(function(){
	$("#submit").click(function(){
		var interface_name = $("#interfacename").val();
		$val_exists = true;
		$("input[type=hidden]").each(function(){
			
			if ($(this).val() == interface_name){
				$val_exists = false;
				alert(interface_name + " already exists");
				//alert(interface_name + " exist already");
				
			}
		})
		return $val_exists;
	})
	/*

	*/
});