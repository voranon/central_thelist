<?php
interface thelist_commander_pattern_interface_ideviceconfiguration
{
	
	//this is the reasoning for the dual approch (object -> array -> config file), i wrote it down because i keep forgetting
	
	//because we need the abillity to compare a config from a running service in the
	//field on a device to the config we have in the database, we have to format the
	//config as an array first. All our objects are deeply rooted in the database
	//and we therefore cannot create an object of the device service config.
	
	//there are many of the commander classes that format the device config then
	//pulls the config from the database and compares
	
	//there are also many commander classes that receive orders to remove a specific
	//item by another class, the requesting class will send the request and the commander class
	//generates the service config as array, finds the item to remove, pushes the array back to
	//this class and gets a corectly formatted config file back, sans the item to be removed.
	//then returns ok to the requesting class that can now remove the item from the database.
	//doing removals on device config any otherway was very complicated.
	
	public function generate_config_array();
	public function generate_config_device_syntax($config_array);

}
?>