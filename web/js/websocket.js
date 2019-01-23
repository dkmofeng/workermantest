websocket=new WebSocket("ws://129.204.72.68:2347");
websocket.onopen=function(e){
    console.log('open success ');
    var jsondata='{"type":"login","uid":"<?php echo $_SESSION['UID']; ?>"}';
    send(jsondata)
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
    }else{
        console.log(data.text)
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
    tobottom()
}
function tobottom(){
    $('#messagebox').scrollHeight;
    $('#messagebox').scrollTop=$('#messagebox').scrollHeight;
}