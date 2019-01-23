<?php
session_start();
$_SESSION['UID']=session_id();
$ran=date('i');
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="css/chat.css?c=<?php echo $ran; ?>">


<div id="messagebox">

</div>
<div>
    <textarea id="text"></textarea>
</div>

<button class="sendbutton" onclick="sendmessage()">发送消息</button>
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/websocket.js?c=<?php echo $ran; ?>"></script>
