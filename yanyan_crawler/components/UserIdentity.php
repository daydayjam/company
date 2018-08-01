<?php
/**
 * 控制器总类
 */
namespace app\components;

use Yii;
use app\models\User;
use app\models\Cache;
use app\models\Tool;

class UserIdentity {
    
    private $_username;
    private $_password;
    private $_type;
    
    public $errorMsg = '';
    public $errorCode = 1;
    
    public function __construct($username = '', $password = '', $type = 1) {
        $this->_username = $username;
        $this->_password = $password;
        $this->_type = $type;
    }
    
    public function authenticate() {
        $User = new User();
        if($this->_type == 1) { //常规登陆
            $sql = 'select * from ' . $User->tableName() . ' where tel=:tel or serial_num=:serialNUm';
            $UserRecord = $User->findBySql($sql, [':tel'=>$this->_username, ':serialNUm'=>$this->_username])->one();
        } else if($this->_type == 2) {    //第三方登录
            $UserRecord = $User->findOne([$this->_username=>$this->_password]);
        } else { //注册
            $UserRecord = $User->findOne($this->_username);
        }
        if(!$UserRecord) {
            $this->errorCode = -7;
            $this->errorMsg = '用户不存在';
            return false;
        }
        if($this->_type == 1 && $UserRecord->pwd != $this->_password) {
            $this->errorCode = -5;
            $this->errorMsg = '您的密码有误，请确认';
            return false;
        }
        $ustatus = $UserRecord->ustatus;
        if($ustatus < 1) {
            if($ustatus == -1) {
                $code = -103;
                $msg = '该用户已因涉嫌违规已被停封处理，无法操作';
            } else {
                $hourType = $ustatus == -2 ? 8 : 24;
                $timeDiff = Tool::getTimeDiff($UserRecord->unfreeze_time, date('Y-m-d H:i:s'));
                $hour = floor($timeDiff/3600);
                $second = floor(($timeDiff/3600 - $hour)*60);
                $code = -104;
                $msg = '您因违规被处以冻结'.$hourType.'小时处罚，距离解封还剩'.$hour.'小时'.$second.'分';
            }
            $this->errorCode = $code;
            $this->errorMsg = $msg;
            return false;
        }
        //删除以前的code
        Cache::del($UserRecord->verify_code);
        $id = $UserRecord->id;
        $userInfo = [];
        $userInfo['id'] = $id;
        $userInfo['ease_uid'] = $UserRecord->ease_uid;
        $userInfo['pwd'] = $UserRecord->pwd;
        $userInfo['tel'] = $UserRecord->tel;
        $userInfo['serial_num'] = $UserRecord->serial_num;
        $userInfo['avatar'] =  Tool::connectPath(Yii::$app->params['image_domain'], $UserRecord->avatar);
        $userInfo['nickname'] = $UserRecord->nickname;
        $userInfo['gender'] = $UserRecord->gender;
        $userInfo['birth_date'] = $UserRecord->birth_date;
        $userInfo['emotion'] = $UserRecord->emotion;
        $userInfo['hometown'] = $UserRecord->hometown;
        $userInfo['intrest'] = $UserRecord->intrest;
        $userInfo['signature'] = $UserRecord->signature;
        $userInfo['qq_id'] = $UserRecord->qq_id;
        $userInfo['sina_id'] = $UserRecord->sina_id;
        $userInfo['wechat_id'] = $UserRecord->wechat_id;
        $userInfo['push_code'] = $User->getUserPushAlias($id);
        $userInfo['verify_code'] = $User->getUserVerifyCode($id);
        
        $code = $userInfo['verify_code'];
        Cache::hset('id', $id, $code);
        Cache::hset('ustatus', $UserRecord->ustatus, $code);
        Cache::hset('ease_uid', $UserRecord->ease_uid, $code);
        Cache::hset('nick', $UserRecord->nickname, $code);
        Cache::hset('unfreeze_time', $UserRecord->unfreeze_time, $code);
        Cache::hset('gender', $UserRecord->gender, $code);
        Cache::hset('signature', $UserRecord->signature, $code);
        Cache::hset('avatar', Yii::$app->params['image_domain'] . $UserRecord->avatar, $code);
        return $userInfo;
    }
   
}

