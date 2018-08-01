<?php
//凌晨4点清楚聊天室成员
require 'D:\web\yanyan\app\model\mod_mipush.php';
require 'D:\PhpSystem\www\yanyan_local\models\Easemob.php';
require 'D:\PhpSystem\www\yanyan_local\models\Tool.php';
require 'D:\PhpSystem\www\yanyan_local\models\CmdHelper.php';


$servername = "115.28.153.135";
$username = "root";
$password = "crting2013fairytail";
$dbName = 'yanyan_local';
 
// 创建连接
$conn = new mysqli($servername, $username, $password, $dbName);
$sql = 'select ease_id,name from yy_chatroom where unix_timestamp(end_time)>unix_timestamp(now()) and film_id=3';
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    // 输出数据
    while($room = $result->fetch_assoc()) {
        $roomId = $room['ease_id'];
        $roomName = $room['name'];
        //获取每个聊天室详情，根据聊天室详情获取该聊天室成员
        $members = array();
        $Easemob = new app\models\Easemob();
        $response = $Easemob->getChatRoomDetail($roomId);
        if($response['code'] != 200) {
            sleep(1);
            $response = Easemob::getInstance()->getChatRoomDetail($roomId);
            if($response['code'] == 200) {
                $members = $response['result']['data'][0]['affiliations'];
            }
        }
        $members = $response['result']['data'][0]['affiliations'];
        if($members) {
            $targets = array();
            foreach($members as $item) {
                if(isset($item['member']) && !in_array($item['member'], $targets)) {
                    $targets[] = $item['member'];
                }
            }
            if($targets) {
                $memberStr = implode(',', $targets);
                $res = $Easemob->deleteChatRoomMembers($roomId, $memberStr);
                if($res['code'] != 200) {
                    Easemob::getInstance()->deleteChatRoomMembers($roomId, $memberStr);
                }
                //给所有人发送被踢出的消息
                $CmdHelper = new \app\models\CmdHelper();
                $cmdInfo = ['cmd_type'=>10, 'desc'=>'您已经离开'.$roomName.'聊天室'];
                $record = array(
                    'ease_id'=>$roomId,
                    'room_name'=>$roomName,
                    'add_time'=>time()
                );
                $ext = array_merge($cmdInfo, array('record'=>$record));
                $res = $CmdHelper->sendCmdMessageToUsers($targets, $cmdInfo['desc'], $ext);
                print_r($res);
            }
        }
    }
}else {
	return;
}

  