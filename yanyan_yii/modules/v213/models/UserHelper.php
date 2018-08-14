<?php
/**
 * 用户z模型类
 * @author ztt
 * @date 2017/11/14
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\Tool;
use app\models\Setting;
use app\models\FilmFollow;
use app\models\Friend;

class UserHelper extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
    
    /**
     * 生成环信用户名
     * @param int $uid 用户ID
     * @return string 环信用户名
     */
    public function getEaseUname($uid) {
        return 'yanyan_pre_chat_' . $uid;
    }
    
   /**
    * 生成言言ID
    * @param type $uid
    * @return type
    */
   public function getSerialNum($uid){
       $begin = 220010;
       $end = rand( 11,99 );
       return $begin.$uid.$end;
   }
    
    /**
     * 判断手机号/第三方账号是否使用
     * @param string $filed 字段名
     * @param string $value 值
     * @return boolen true=已使用
     */
    public function isUsed($filed, $value) {
        $Record = $this->findOne([$filed => $value]);
        if($Record) {
            return true;
        }
        return false;
    }
    
    /**
     * 获取用户账号状态
     * @param int $uid 用户ID
     * @return int 账号状态
     */
    public function getstatus($uid) {
        $Record = $this->findOne();
    }
    
    /**
     * 获取登录返回数据
     * @param int $uid 用户ID
     * @return array
     */
    public function getLoginData($uid) {
        $Setting = new Setting();
        $setting = $Setting->getList($uid);
        $Friend = new Friend();
        $fansList = $Friend->getList($uid);
        $concernList = $Friend->getList($uid, true);
        $FilmFollow = new FilmFollow();
        $followfilmList = $FilmFollow->getList(0, 10);
        return ['concern_list'=>$concernList,'fans_list'=>$fansList,'setting'=>$setting, 'followfilm_list'=>$followfilmList];
    }
    

    
    /**
     * 获取用户登录码
     * @param int $userId 用户ID
     * @return string 用户登录码
     */
    public function getUserVerifyCode($userId) {
        $code = Tool::md5Double('user_cheHui&^%$#_'.$userId.'_login_'.time());
        if($code){
            $UserRecord = $this->findOne($userId);
            $UserRecord->verify_code = $code;
            if(!$UserRecord->save()) {
                return false;
            }
        }
        return $code;
    }
    
    /**
     * 获取用户冻结状态错误信息
     * @param type $status 用户冻结状态 -1=停封；-2=冻结8小时；-3=冻结24小时
     * @param type $type 业务类型 register,login
     * @return boolean false
     */
    public function getstatusMsg($status, $unfreezeTime, $type = 'register') {
        if($status < 1) {
            if($status == -1) {
                 $msg = $type == 'register' ? '-103:该账号已被停封，无法再次注册' : '-103:该用户已因涉嫌违规已被停封处理，无法操作';
            } else {
                $hourType = $status == -2 ? 8 : 24;
                $timeDiff = Tool::getTimeDiff($unfreezeTime, date('Y-m-d H:i:s'));
                $hour = floor($timeDiff/3600);
                $second = floor(($timeDiff/3600 - $hour)*60);
                $msg = '-104:您因违规被处以冻结'.$hourType.'小时处罚，距离解封还剩'.$hour.'小时'.$second.'分';
            }
            $this->addError('', $msg);
            return false;
        }
        return true;
    }

    //获取用户小米推送的别名
    public function getUserPushAlias($userId) {
        return Tool::md5Double('user_cheHui@$%&*8639_'.$userId);
    }
    
    /**
     * 启动事务
     * @return void 无返回值
     */
    protected function beginTransaction() {
        $this->_Transaction = Yii::$app->db->beginTransaction();
    }
    
    /**
     * 回滚事务
     * @return void 无返回值
     */
    protected function rollback() {
        if($this->_Transaction != null) {
            $this->_Transaction->rollBack();
        }
    }
    
    /**
     * 提交事务
     * @return void 无返回值
     */
    protected function commit() {
        if($this->_Transaction != null) {
            $this->_Transaction->commit();
        }
    }
    
}

