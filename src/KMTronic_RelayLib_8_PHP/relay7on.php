
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[6] == 0) 
	{   $lib->toggle(7);
		echo 'Relay7 was off and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay7 is on <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

