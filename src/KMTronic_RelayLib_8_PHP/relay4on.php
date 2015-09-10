
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[3] == 0) 
	{   $lib->toggle(4);
		echo 'Relay4 was off and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay4 is on <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

