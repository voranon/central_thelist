<?php
interface thelist_commander_pattern_interface_idevicecommand{
	
	//all commands must have dual purpose.
	//i.e. if the command is getting the interface configuration from a cisco switch and it requires 
	//only the interface name, then the constructor should require $device and $interface, but $interface
	//in this case could be both an object or a string with the interface name
	
	//if the object model requires a single attribute i.e. just the interface object, but the non database related function
	//requires more than one, then the constructor should require $device and $options, in the object case $options is an
	//interface object and in the string case it is an array of strings.
	
	//we MUST decouple the commander classes from the database. they have MUCH higher value in the future if they are not relying
	//on our data structure. A massive effort has been made so the device class is 100% decoupled from the database.
	//there are functions that rely on the database, but they in no way limit its use. they are primarily for validation
	
	
	
	public function execute();

}
?>