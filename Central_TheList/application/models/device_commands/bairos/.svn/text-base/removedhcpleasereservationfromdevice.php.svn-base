<?php

//exception codes 6000-6099

class thelist_bairos_command_removedhcpleasereservationfromdevice implements Thelist_Commander_pattern_interface_idevicecommand
{

private $_device;
	private $_ip_address_id;
	
	public function __construct($device, $ip_address_id)
	{
		$this->_device 			= $device;
		$this->_ip_address_id 	= $ip_address_id;
	
	}
	
	public function execute()
	{
		//the ip must have a map lease entry.
		$sql = 	"SELECT i.if_mac_address, ipa.ip_address FROM ip_address_mapping ipam
				INNER JOIN interfaces i ON i.if_id=ipam.if_id
				INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
				WHERE ipam.ip_address_id='".$this->_ip_address_id."'
				AND ipam.ip_address_map_type='91'
				";
		
		$reservation_detail = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
		
		if (isset($reservation_detail['if_mac_address'])) {
			
			//lets find out if its already running on this device
			//get current config
			$device_current_dhcp_config			= new Thelist_Bairos_command_getdhcpconfigurationfromdevice($this->_device);
			$is_active							= $device_current_dhcp_config->host_reservation_active($reservation_detail['if_mac_address']);

		if ($is_active == false) {
			
			//lease is already removed, do nothing
			return;
		}

			//if the reservation does exist
			//locate the equipment app map id
			$sql2 = 	"SELECT eam.equipment_application_map_id FROM ip_address_mapping ipam
						INNER JOIN interfaces i ON i.if_id=ipam.if_id
						INNER JOIN ip_addresses ipa ON ipa.ip_address_id=ipam.ip_address_id
						INNER JOIN equipment_application_mapping eam ON eam.eq_id=i.eq_id
						WHERE ipam.ip_address_id='".$this->_ip_address_id."'
						AND ipam.ip_address_map_type='89'
						AND eam.equipment_application_id='1'
						";
			
			$equipment_application_map_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql2);
				
			if (isset($equipment_application_map_id['equipment_application_map_id'])) {
				
				//get the current config from the device
				$config_arrays			= $device_current_dhcp_config->get_dhcp_configuration();

				//remove the ip address from the config before submitting it to construction of config build
				foreach($config_arrays as $dhcp_server_id => $config_array) {
					
					foreach($config_array['hosts'] as $host_index => $host) {
						
						if ($reservation_detail['if_mac_address'] == $host['mac_address']) {
							
							unset($config_arrays[$dhcp_server_id]['hosts'][$host_index]);
							
							//track that the remove was done
							$host_removed = 1;
						}
					}
				}
				
				if (!isset($host_removed)) {
					
					throw new exception('6005 we are asked to remove a DHCP lease rom a BAI router, but it dident exist', 6005);
					
				}

				//the data is ready to be turned into config syntax
				$eq_app_conf			= new Thelist_Model_equipmentapplicationconfiguration();
				$config_file_content	= $eq_app_conf->create_configuration($equipment_application_map_id, $config_arrays, 'string');

				//now execute on the device
				try {
					
					//push the newconfig to the device
					$this->_device->execute_command("echo '".$config_file_content."' > /etc/dhcpd.conf");

					//restart the service
					$server_service		= new Thelist_Bairos_command_setapplicationondevice($this->_device);
					$server_service->set_dhcp_server_status('restart');

				} catch (Exception $e) {
	
					switch($e->getCode()){
	
						case 6003;
						//6003,bai dhcp service was stopped and action restart did not start the service
						//push the old config to the device
						$this->_device->execute_command("echo '".$eq_app_conf->create_configuration($equipment_application_map_id, $device_current_dhcp_config->get_dhcp_configuration(), 'string')."' > /etc/dhcpd.conf");
						$server_service->set_dhcp_server_status('start');
						throw new exception('6002 BAIROS DHCP config fails to load corectly, old configuration was pushed back into production. system running without new reservations', 6002);
						break;
						case 6004;
						//6004,bai dhcp service was running but after restart the service failed
						//push the old config to the device
						$this->_device->execute_command("echo '".$eq_app_conf->create_configuration($equipment_application_map_id, $device_current_dhcp_config->get_dhcp_configuration(), 'string')."' > /etc/dhcpd.conf");
						$server_service->set_dhcp_server_status('start');
						throw new exception('6003 BAIROS DHCP config fails to load corectly, old configuration was pushed back into production. system running without new reservations', 6003);
						break;
						default;
						throw $e;
	
					}
				}

			} else {
				throw new exception('the equipment does not have a dhcp server config in the database', 6001);
			}
			


		} else {

			throw new exception('the provided ip address id does not have a lease reservation mapped', 6000);
			
		}
	}

}