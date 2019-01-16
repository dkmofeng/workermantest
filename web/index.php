<script>
     websocket=new WebSocket("ws://129.204.72.68:2347");
    websocket.onopen=function(e){
        console.log('open success ');
        ping();
    }
    websocket.onmessage=function(e){
        console.log(e);
    }
    websocket.onclose=function(e){
        console.log('已关闭连接！！')
    }
    function ping(){
        var jsondata='{"type":"pong"}';
        send(jsondata)
        console.log('ping log')
        setTimeout('ping()',10000);
    }

    function sendmessage() {
       var txt= document.getElementById('text').value;
       if(txt!=''){
           var jsondata='{"type":"sayall","message":"'+txt+'"}';
           send(jsondata)
       }
    }
    function  send(data){
        console.log("websocket握手成功，发送登录数据:"+data);
        websocket.send(data);
    }
</script>

<input id="text">

<button onclick="sendmessage()">发送消息</button>
