<?php

//exception codes 21200-21299

class thelist_routeros_command_setinterfacestandardnames implements Thelist_Commander_pattern_interface_idevicecommand 
{
	private $_device;
	private $_arp_table=null;
	
	public function __construct($device)
	{
		$this->_device = $device;
	
	}
	
	public function execute()
	{
		$get_all_interfaces		= new Thelist_Routeros_command_getinterfaces($this->_device);
		$interfaces				= $get_all_interfaces->get_configured_interfaces();
		
		$random_number = rand(1, 100000);
		
		$i=0;
		foreach ($interfaces as $interface) {
			
			if (isset($interface['name_components']['vlan_id'])) {
				$new_interface_name = $interface['name_components']['name'] . "" . $interface['name_components']['index'] . "." . $interface['name_components']['vlan_id'];
			} else {
				$new_interface_name = $interface['name_components']['name'] . "" . $interface['name_components']['index'];
			}
			
			if ($interface['interface_name'] != $new_interface_name) {
				
				$changes[$i]['old_name'] 	= $interface['interface_name'];
				$changes[$i]['new_name'] 	= $new_interface_name;
				$changes[$i]['temp_name'] 	= "temp" . ($random_number + $i);
					
				$i++;
			}
		}
		
		//if there are any changes
		if (isset($changes)) {
			
			foreach ($changes as $change) {
				
				//we are going to set the description as the old interface name
				//but if there if there is already a description then we are going to append it
				$get_description		= new Thelist_Routeros_command_getinterfacedescription($this->_device, $change['old_name']);
				$current_description	= $get_description->get_configured_description();
					
				if ($current_description == null) {
					$new_description = "Interface name corrected by system, old: '".$change['old_name']."'.";
				} else {
					$new_description = $current_description . "App. Interface name corrected by system, old: '".$change['old_name']."'.";
				}
				
				$set_description		= new Thelist_Routeros_command_setinterfacedescription($this->_device, $change['old_name'], $new_description);
				$set_description->execute();
					
				//set all temp names first
				//we need to set temp names before setting the final names, because we cannot set i.e. ether4 when ether4 already exists
				$set_temp_name		= new Thelist_Routeros_command_setinterfacename($this->_device, $change['old_name'], $change['temp_name']);
				$set_temp_name->execute();
			}
			
			//now that all have been renamed to temp names we do the correct names
			foreach ($changes as $change) {
				
				//set all permanent names 
				$set_new_name		= new Thelist_Routeros_command_setinterfacename($this->_device, $change['temp_name'], $change['new_name']);
				$set_new_name->execute();
				
			}
		}
	}
}
?>