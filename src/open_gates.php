<!DOCTYPE html>
<html>
<head>
	<title>Gates Controller</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script>

/*
* jQuery Web Sockets Plugin v0.0.1
* http://code.google.com/p/jquery-websocket/
*
* This document is licensed as free software under the terms of the
* MIT License: http://www.opensource.org/licenses/mit-license.php
*
* Copyright (c) 2010 by shootaroo (Shotaro Tsubouchi).
*/
(function ($) {
    $.extend({
        websocketSettings: {
            open: function () { },
            close: function () { },
            message: function () { },
            options: {},
            events: {}
        },
        websocket: function (url, protocol, s) {
            var ws = WebSocket ? new WebSocket(url, protocol) : {
                send: function (m) { return false },
                close: function () { }
            };
            ws._settings = $.extend($.websocketSettings, s);
            $(ws)
            .bind('open', $.websocketSettings.open)
            .bind('close', $.websocketSettings.close)
            .bind('message', $.websocketSettings.message)
            .bind('message', function (e) {
                var m = $.evalJSON(e.originalEvent.data);
                var h = $.websocketSettings.events[m.command];
                if (h) h.call(this, m);
            });
            ws._send = ws.send;
            ws.send = function (type, data) {
                var m = { command: type };
                m = $.extend(true, m, $.extend(true, {}, $.websocketSettings.options, m));
                if (data) m['data'] = data;
                try {
                    this._send(JSON.stringify(m));
                }
                catch (ex) {
                    alert(ex);
                    return false;
                }
                return true;
            }
            $(window).unload(function () { ws.close(); ws = null });
            return ws;
        }
    });
})(jQuery);
 
        // sends a message to the websocket server
        function sendToServer() {
 
            ws.send('krakenmsgA', '{ messageTextA: ' + $('#echoText').val() + ' }');
            ws.send('krakenmsgB', '{ messageTextB: ' + $('#echoText').val() + ' }');
        }

        //var ws = $.websocket("ws://192.168.0.11:17494");
        //ws.opopen = function(){
        	//console.log("Open");
        //}
        //ws.onclose = function(){
        	//console.log("Close");
        //}
        //ws.send('32 03 00');
 				//$.get('http://192.168.0.11:17494/io.cgi?DOA2=50');
 /*
        // set-up web socket
        var ws = $.websocket("ws://192.168.0.11:17494/", "tcp", {
            open: function () { },
            close: function () { alert('websocket has been closed'); },
            events: {
                krakenmsgA: function (e) { $('#returnText').append(e.data + "<br/>"); },
                krakenmsgB: function (e) { $('#returnText').append(e.data + "<br/>"); }
            }
        });
 */
 			if ("WebSocket" in window) {
 				console.log("Web sockets available in this browser!");
 				var socket = new WebSocket('ws://192.168.0.11:17494');
 				socket.send("0x10");
 				socket.onopen = function(){
 					console.log("DATA");
 				};

    	}
    </script>
    <div>
        <div style="float: left; clear: left; padding-top: 2px;">
            Your text:
        </div>
        <div style="float: left; padding-left: 20px;">
            <input type="text" id="echoText" style="width: 150px;" required />
        </div>
        <div style="clear: left;">
            <input type="button" onclick="javascript:sendToServer();" value="Send" />
        </div>
        <div id="returnText" style="clear: left; height: 200px; padding-top: 30px;">
        </div>
    </div>
  </body>
</html>