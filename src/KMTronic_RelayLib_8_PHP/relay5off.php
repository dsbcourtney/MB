
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[4] == 1) 
	{   $lib->toggle(5);
		echo 'Relay5 was on and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay5 is off <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

