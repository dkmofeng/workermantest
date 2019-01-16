<?php
use Workerman\Worker;
use Workerman\Lib\Timer;
require_once __DIR__ . '/Autoloader.php';

$tcp_worker = new Worker("ws://0.0.0.0:2347");

// 启动4个进程对外提供服务
$tcp_worker->count =2;

// 当客户端发来数据时
$tcp_worker->onMessage = function($connection, $data)
{
    if(empty($data)||!is_array($data)){
        return false;
    }
   $message=json_decode($data,true);
    switch ($message['type']){
        case 'pong':
             $connection->lastsendtime=time();
        break;
        case 'close':
            $connection->close();
            foreach ($tcp_worker->connections as $connectionrow){
                $connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connectionrow->username.'已经离开']));
            }
            break;
        case 'login':
            $username=$message['username'];
            $connection->username=$username;
            $connection->send(json_encode(['type'=>'sysmessage','text'=>'登陆成功','id'=>$connection->id]));
         break;
        case 'sayall':
            $messagetext=$message['message'];

            foreach ($tcp_worker->connections as $connectionrow){
                $connectionrow->send(json_encode(['type'=>'usermessage','text'=>$connection->username.'说'.$messagetext]));
            }
         break;
        default:
            $connection->send(json_encode(['type'=>'sysmessage','text'=>'无效数据！','id'=>$connection->id]));

         break;
    }
    return false;
};

// 当客户端发来数据时
$tcp_worker->onConnect = function($connection)
{
    global $userlimit;
    $userlimit+=1;
    $message=['type'=>'connect','status'=>1];
    $connection->send(json_encode($message));
    $connection->lastsendtime=time();
    $connection->username='游客'.$userlimit;
    foreach ($tcp_worker->connections as $connectionrow){
        $connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connection->username.'加入会话']));
    }
};

$tcp_worker->onWorkerStart = function($worker)
{

    global $userlimit;
    $userlimit=0;
    Timer:add(50,function() use($worker){
    $connections=$worker->connections;
    if(!empty($connections)){
        foreach($connections as $connection){
           // $message=['type'=>'ping'];
            //$connection->send(json_encode($message));

            if(empty($connection->lastsendtime)){
                $connection->lastsendtime=time();
                continue;
            }
            if(time()-$connection->lastsendtime>60){
                $connection->close();
            }
        }
    }
});
};

// 运行worker
Worker::runAll();


?>
