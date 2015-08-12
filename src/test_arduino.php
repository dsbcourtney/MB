<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

//session_start();
//ob_start();

$server_ip = "192.168.0.11";
$service_port = 17494;


$hex_turnon = 32; // Decimal of 32
$hex_turnoff = 0x21;
$hex_relay = 5; // Decimal of 1
$hex_pulse = 50; // Decimal of 50




$fp = fsockopen($server_ip,$service_port, $errno, $errstr, 30);
if (!$fp) {
    	echo "$errstr ($errno)<br />\n";
} 
else {
    	echo "connected\n";
  $msg = pack("CCC",$hex_turnon,$hex_relay,$hex_pulse);
	//$msg = pack("CCC",$output_active,$output_number,$output_pulsetime);    
	fwrite($fp,$msg);
	echo "string sent\n";
	$returned_data = fread($fp,1);
	if($returned_data == 0) echo "success\n";
	else echo "failed\n";
	fclose($fp);
}