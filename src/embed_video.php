<?php
/*
$cameras = array();
$cameras[] = 'rtsp://admin:Spanner78@217.34.32.7:6001/mpeg4/ch1/sub/av_stream';
$cameras[] = 'rtsp://admin:Spanner78@217.34.32.7:6001/mpeg4/ch1/main/av_stream';
$cameras[] = 'rtsp://admin:Spanner78@217.34.32.7:6001/h.264/ch1/sub/av_stream';
$cameras[] = 'rtsp://admin:Spanner78@217.34.32.7:6001/h.264/ch1/main/av_stream';

foreach ($cameras AS $v) {
?><OBJECT classid="clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921"
     codebase="http://downloads.videolan.org/pub/videolan/vlc/latest/win32/axvlc.cab"
     width="300" height="200" id="vlc" events="True">
   <param name="Src" value="<?php echo $v; ?>" />
   <param name="ShowDisplay" value="True" />
   <param name="AutoLoop" value="False" />
   <param name="AutoPlay" value="True" />
   <embed id="vlcEmb"  type="application/x-google-vlc-plugin" version="VideoLAN.VLCPlugin.2" autoplay="yes" loop="no" width="300" height="200"
     target="<?php echo $v; ?>" ></embed>
</OBJECT>
<?php
}


foreach ($cameras AS $v) {
?><video src="<?php echo $v; ?>">
	Your browser does not support the VIDEO tag and/or RTSP streams.
</video>

<?php 
}
*/
?>
<img src="http://admin:Spanner89@217.34.32.7:38321/PSIA/streaming/channels/1/picture">