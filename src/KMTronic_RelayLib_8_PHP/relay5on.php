
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[4] == 0) 
	{   $lib->toggle(5);
		echo 'Relay5 was off and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay5 is on <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

