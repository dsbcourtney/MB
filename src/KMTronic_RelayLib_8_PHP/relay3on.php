
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[2] == 0) 
	{   $lib->toggle(3);
		echo 'Relay3 was off and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay3 is on <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

