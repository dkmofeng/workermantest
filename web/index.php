<style>
    .sendbutton{width:90%;margin:10px 5%;background-color:red;color:#fff;}
    #text{width:100%;height:80px;border:1px solid #ccc;}
    #messagebox{width:100%;width:100%;padding:10px;margin-bottom:20px;}
    
	
	#messagebox div{height:40px;line-height:40px;font-size:13px;}

</style>
<script>
     websocket=new WebSocket("ws://129.204.72.68:2347");
    websocket.onopen=function(e){
        console.log('open success ');
        ping();
    }
    websocket.onmessage=function(e){
        console.log(e);
        data=JSON.parse(e.data);
        if(data.type=='usermessage'){
            var box=document.getElementById('messagebox');
			var messagerow = document.createElement("div");
			messagerow.class='messagetext';
			messagerow.innerHTML=data.text;
            box.appendChild(messagerow);

        }
    }
    websocket.onclose=function(e){
        console.log('已关闭连接！！')
    }
    function ping(){
        var jsondata='{"type":"pong"}';
        send(jsondata)
        console.log('ping log')
        setTimeout('ping()',2000);
    }

    function sendmessage() {
       var txt= document.getElementById('text').value;
       if(txt!=''){
		   document.getElementById('text').value='';
           var jsondata='{"type":"sayall","message":"'+txt+'"}';
           send(jsondata)
       }
    }
    function  send(data){
       // console.log("websocket握手成功，发送登录数据:"+data);
        websocket.send(data);
    }
</script>

<div id="messagebox">

</div>
<div>
    <textarea id="text"></textarea>
</div>

<button class="sendbutton" onclick="sendmessage()">发送消息</button>
