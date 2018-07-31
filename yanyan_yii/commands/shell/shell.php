<?php
//require 'D:\PhpSystem\www\yanyan_local\models\mod_mipush.php';
require 'D:\PhpSystem\www\yanyan_local\models\Easemob.php';
require 'D:\PhpSystem\www\yanyan_local\models\Tool.php';
require 'D:\PhpSystem\www\yanyan_local\models\CmdHelper.php';

$servername = "115.28.153.135";
$username = "root";
$password = "crting2013fairytail";
$dbName = 'yanyan_local';
 
// 创建连接
$conn = new mysqli($servername, $username, $password, $dbName);
$sql = "SELECT * FROM yy_users WHERE status<-1";
$result = $conn->query($sql);
$ids = array();
if ($result->num_rows > 0) {
    // 输出数据
    $i = -1;
    while($row = $result->fetch_assoc()) {
        ++ $i;
        if($row['status'] < -1) { //冻结8小时
            if(strtotime(date('Y-m-d H:i:s')) >= strtotime($row['unfreeze_time'])) {
                $ids[$i]['id'] = $row['id'];
                $ids[$i]['ease_uid'] = $row['ease_uid'];
            }
        }
    }
}
//print_r($ids);die;
if(!empty($ids)) {
    $cmdInfo = ['cmd_type'=>8, 'desc'=>'解除冻结'];
    for($i=0; $i<count($ids); $i++) {
//        print_r($ids[$i]);die;
        $sql = 'update yy_users set status=1,freeze_time="0000-00-00 00:00:00",unfreeze_time="0000-00-00 00:00:00" where id=' . $ids[$i]['id'];
        $conn->query($sql);
        $Easemob = new app\models\Easemob();
        $Easemob->activeUser($ids[$i]['ease_uid']);
        $record = array("user_id"=>$ids[$i]['id'],
                        "is_freeze"=> 0,
                        "add_time"=>time());
        $ext = array_merge($cmdInfo, array("record"=>$record));
        $hxUserName = array( $ids[$i]["ease_uid"] );
        if( !empty( $hxUserName )){
            $CmdHelper = new \app\models\CmdHelper();
            $res = $CmdHelper->sendCmdMessageToUsers($hxUserName, $cmdInfo["desc"], $ext);
            print_r($res);
        }
    }
    
}
$conn->close();
