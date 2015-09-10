
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[5] == 1) 
	{   $lib->toggle(6);
		echo 'Relay6 was on and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay6 is off <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

