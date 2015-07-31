<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

session_start();

if (isset($_POST['btn1status'])) { // Button 1 pushed
	$btn1status = $_POST['btn1status'];
	$_SESSION['btn1status'] = $_POST['btn1status'];
	$btn2status = $_SESSION['btn2status'];
} elseif (isset($_POST['btn2status'])) { // Button 2 pushed
	$btn2status = $_POST['btn2status'];
	$_SESSION['btn2status'] = $_POST['btn2status'];
	$btn1status = $_SESSION['btn1status'];
} else { // First load of page
	$_SESSION['btn1status'] = 0;
	$_SESSION['btn2status'] = 0;
}

$server_ip   = '192.168.0.21';
if (isset($_POST['btn1status'])) {
	$server_port = 8877;
	$message = $btn1status;
} else {
	$server_port = 8878;	
	$message = $btn2status;
}

//print "<p>Sending ".(($btn1status==0)?'On':'Off')." heartbeat to IP $server_ip, port $server_port";
if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
    socket_sendto($socket, $message, strlen($message), 0, $server_ip, $server_port);
    //print " at " . date('Y-m-d H:i:s') . "</p>";
    //sleep($beat_period);
} else {
  print("can't create socket\n");
}

?>
<form action="" method="post">
    <input type="hidden" name="btn1status" value="<?php echo ($btn1status==0)?1:0; ?>">
    <button type="submit">Turn Switch <?php echo ($btn1status==0)?'On':'Off'; ?></button>
</form>
<form action="" method="post">
    <input type="hidden" name="btn2status" value="<?php echo ($btn2status==0)?1:0; ?>">
    <button type="submit">Turn Switch <?php echo ($btn2status==0)?'On':'Off'; ?></button>
</form>