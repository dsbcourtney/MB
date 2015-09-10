
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[0] == 0) 
	{   $lib->toggle(1);
		echo 'Relay1 was off and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay1 is on <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

