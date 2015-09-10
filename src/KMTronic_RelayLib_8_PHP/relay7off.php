
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[6] == 1) 
	{   $lib->toggle(7);
		echo 'Relay7 was on and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay7 is off <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

