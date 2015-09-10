
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[0] == 1) 
	{   $lib->toggle(1);
		echo 'Relay1 was on and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay1 is off <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

