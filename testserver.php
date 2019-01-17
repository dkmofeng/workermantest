<?php

require_once __DIR__ . '/Autoloader.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

$tcp_worker = new Worker("Websocket://0.0.0.0:2347");

// 启动4个进程对外提供服务
$tcp_worker->count =2;

// 当客户端发来数据时
$tcp_worker->onMessage = function($connection, $data)  use($tcp_worker)
{
    $message=json_decode($data,true);



    if(empty($data)||!is_array($message)){
        return false;
    }

    switch ($message['type']){
        case 'pong':
             $connection->lastsendtime=time();
               //$connection->send(json_encode(['type'=>'sysmessage','text'=>$connection->lastsendtime]));
        break;
        case 'close':

            foreach ($tcp_worker->connections as $connectionrow){
                $connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connection->username.'已经离开'],JSON_UNESCAPED_UNICODE));
            }
            $connection->close();
            break;
        case 'login':
            $username=$message['username'];
            $connection->username=$username;
            $connection->send(json_encode(['type'=>'sysmessage','text'=>'登陆成功','id'=>$connection->id],JSON_UNESCAPED_UNICODE));
         break;
        case 'sayall':
            $messagetext=$message['message'];

            foreach ($tcp_worker->connections as $connectionrow){
                $connectionrow->send(json_encode(['type'=>'usermessage','text'=>$connection->username.'说:'.$messagetext],JSON_UNESCAPED_UNICODE));
            }
         break;
        default:
            $connection->send(json_encode(['type'=>'sysmessage','text'=>'无效数据！','id'=>$connection->id]));

         break;
    }
    return false;
};

// 当客户端发来数据时
$tcp_worker->onConnect = function($connection) use($tcp_worker)
{
    global $userlimit;
	if(!empty($_SESSION['autocreateid'])){
		$userlimit+=1;
		$_SESSION['autocreateid']=$userlimit;
	}
    
    $connection->lastsendtime=time();
    $connection->username='游客'.$_SESSION['autocreateid'];
	
	$message=['type'=>'connect','status'=>1];
	$connection->send(json_encode($message));
    foreach ($tcp_worker->connections as $connectionrow){
        $connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connection->username.'加入会话'],JSON_UNESCAPED_UNICODE));
    }
};

$tcp_worker->onClose = function($connection) use($tcp_worker)
{


    foreach ($tcp_worker->connections as $connectionrow){
        $connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connection->username.'退出会话'],JSON_UNESCAPED_UNICODE));
    }
};

$tcp_worker->onWorkerStart = function($worker) use ($tcp_worker)
{

    global $userlimit;
    $userlimit=0;
    Timer::add(3,function() {
    $connections=$worker->connections;
    if(!empty($connections)){
        foreach($connections as $connection){
           // $message=['type'=>'ping'];
            //$connection->send(json_encode($message));

            if(empty($connection->lastsendtime)){
                $connection->lastsendtime=time();
                continue;
            }
            if(time()-$connection->lastsendtime>9){

                $connection->close();
            }
        }
    }
});
};

// 运行worker
Worker::runAll();


?>
