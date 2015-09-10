
<?php

include 'RelayLib.php';
try 
{
	$status = $lib->status();

	if ($status[5] == 0) 
	{   $lib->toggle(6);
		echo 'Relay6 was off and will be toggled <br/>';
		
	} 
	else 
	{
		echo 'Relay6 is on <br/>';
	}
	
} 
catch (Exception $exc) 
{
	
	echo $exc->getMessage().'<br/>';
	echo $exc->getTraceAsString();
	die();
}

