<?php

$xml_request = new DOMDocument();
	
$root = $xml_request->appendChild(
$xml_request->createElement("Providing_Data"));
	
$location = $root->appendChild(
$xml_request->createElement("location"));

$location->appendChild(
$xml_request->createElement("provided_data", 'user_login_screen'));
	
$location->appendChild(
$xml_request->createElement("latitude", $_POST['latitude']));

$location->appendChild(
$xml_request->createElement("longitude", $_POST['longitude']));

$location->appendChild(
$xml_request->createElement("accuracy", $_POST['accuracy']));

$location->appendChild(
$xml_request->createElement("altitude", $_POST['altitude']));

$location->appendChild(
$xml_request->createElement("altitudeaccuracy", $_POST['altitudeaccuracy']));

$location->appendChild(
$xml_request->createElement("heading", $_POST['heading']));

$location->appendChild(
$xml_request->createElement("speed", $_POST['speed']));

$location->appendChild(
$xml_request->createElement("ip_address", getenv("REMOTE_ADDR")));
	
$xml_request->formatOutput = true;

$client = new SoapClient("http://martin-zend-dev.belairinternet.com/wsdl/fieldinstaller.wsdl");
$client->postInformation($xml_request->saveXML());

?>