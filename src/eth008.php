<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

$ip_address = "192.168.0.11";
$port = 17494;

$output_active = 32;
$output_inactive = 33;
$output_number = 2;
$output_pulsetime = 0;

$input = 16;

$curl = curl_init($ip_address);

//curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($curl, CURLOPT_PORT, 17494);
//curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//url_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);

print_r($response);
/*

$fp = fsockopen($ip_address,$port, $errno, $errstr, 100);
if (!$fp) {
    	echo "$errstr ($errno)<br />\n";
} 
else {
    echo "connected<br>";
	$msg = pack("CCC",$output_active,$output_number,$output_pulsetime); 

	//$msg = pack("C",$input);   
	fwrite($fp,$msg);
	$info = stream_get_meta_data($fp);
	//while (!feof($fp)) {
		echo fgets($fp, 1);
	//}
	//stream_set_timeout($fp,2000);
	echo "string sent<br>";
	//stream_set_blocking($fp,0);
	$returned_data = fread($fp,1);
	$info = stream_get_meta_data($fp);
	echo "RETURNED DATA:".$returned_data."<br>";
	if($returned_data == 0) {
		echo "success<br>";
	}
	else echo "failed<br>";
	fclose($fp);
	print_r($info);
}

*/

?>
