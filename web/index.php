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
        console.log(e);
    }
    function ping(){
        var jsondata={'type':'pong'};
        websocket.send(jsondata);
        console.log('ping log')
        setTimeout('ping()',10);
    }

    function sendmessage() {
       var txt= document.getElementById('text').value;
       if(txt!=''){
           var jsondata={'type':'sayall','message':txt};
           websocket.send(jsondata);
       }
    }
</script>

<input id="text">

<button onclick="sendmessage()">发送消息</button>
