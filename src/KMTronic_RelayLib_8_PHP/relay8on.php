
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[7] == 0) 
	{   $lib->toggle(8);
		echo 'Relay8 was off and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay8 is on <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

