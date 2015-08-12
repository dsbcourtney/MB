<?php

$cameras = array();
$cameras[] = 'rtsp://admin:12345@81.136.171.237:8555//Streaming/Channels/1';
$cameras[] = 'rtsp://admin:12345@81.136.171.237:8554//Streaming/Channels/1';
$cameras[] = 'rtsp://admin:12345@81.136.171.237:554/mpeg4/ch1/main/av_stream';

foreach ($cameras AS $v) {
?><OBJECT classid="clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921"
     codebase="http://downloads.videolan.org/pub/videolan/vlc/latest/win32/axvlc.cab"
     width="640" height="480" id="vlc" events="True">
   <param name="Src" value="<?php echo $v; ?>" />
   <param name="ShowDisplay" value="True" />
   <param name="AutoLoop" value="False" />
   <param name="AutoPlay" value="True" />
   <embed id="vlcEmb"  type="application/x-google-vlc-plugin" version="VideoLAN.VLCPlugin.2" autoplay="yes" loop="no" width="640" height="480"
     target="<?php echo $v; ?>" ></embed>
</OBJECT>
<?php } ?>