<?php

require_once __DIR__ . '/Autoloader.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

$tcp_worker = new Worker("Websocket://0.0.0.0:2347");

// 启动4个进程对外提供服务
$tcp_worker->count =1;

$globalConnects=[];
$globalUserGroup=[];
// 当客户端发来数据时
$tcp_worker->onMessage = function($connection, $data)  use($tcp_worker)
{
    $message=json_decode($data,true);


    if(empty($data)||!is_array($message)){
        return false;
    }
    global  $globalConnects;
    global  $globalUserGroup;

    switch ($message['type']){
        case 'pong':
             $connection->lastsendtime=time();
               //$connection->send(json_encode(['type'=>'sysmessage','text'=>$connection->lastsendtime]));
        break;
        case 'close':

            foreach ($globalConnects as $connectionrow){
				
                $connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connection->username.'已经离开'],JSON_UNESCAPED_UNICODE));
            }
            $connection->close();
            break;
        case 'login':
			if(empty($message['uid'])){
				$connection->send(json_encode(['type'=>'errmsg','text'=>'登录失败']));
				$connection->close();
			}
            global $userlimit;

			 $connection->uid=$message['uid'];
			 $connection->islogin=1;


				if(empty($_SESSION[$message['uid']])){
					$userlimit+=1;
					$_SESSION[$message['uid']]='用户'.$userlimit;
				}
				$connection->username=$_SESSION[$message['uid']];	
					
				
				
				$connection->lastsendtime=time();

				foreach ($globalConnects as $connectionrow){
					$connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connection->username.'加入会话'],JSON_UNESCAPED_UNICODE));
				}
            $globalConnects[$connection->id]=$connection;
            $globalUserGroup[$connection->uid][$connection->id]=1;
            $connection->send(json_encode(['type'=>'sysmessage','text'=>'登陆成功'],JSON_UNESCAPED_UNICODE));
         break;
        case 'sayall':
            $messagetext=$message['message'];
			if(empty($connection->uid)){
				$connection->send(json_encode(['type'=>'errmsg','text'=>'登录失败无法发送信息！！']));
				$connection->close();
				return false;
			}
            foreach ($globalConnects as $connectionrow){
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

	$message=['type'=>'connect','status'=>1];

	$connection->send(json_encode($message));
    
};

$tcp_worker->onClose = function($connection) use($tcp_worker)
{

    global  $globalConnects;

    foreach ($globalConnects as $connectionrow){
        $connectionrow->send(json_encode(['type'=>'sysmessage','text'=>$connection->username.'退出会话'],JSON_UNESCAPED_UNICODE));
    }
};

$tcp_worker->onWorkerStart = function($worker) use ($tcp_worker)
{

    global $userlimit;
    global  $globalConnects;

    $userlimit=0;
    Timer::add(3,function() {
    $connections=$globalConnects;
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
