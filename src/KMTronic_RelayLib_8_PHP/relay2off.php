
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[1] == 1) 
	{   $lib->toggle(2);
		echo 'Relay2 was on and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay2 is off <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

