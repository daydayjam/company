<?php
/**
 * 用户模型类
 * @author ztt
 * @date 2017/10/26
 */
namespace app\models;

use Yii;
use app\models\UserHelper;
use yii\db\Query;
use app\models\Tool;
use app\models\Setting;
use app\models\Easemob;
use app\models\CmdHelper;

class User extends UserHelper {
    
    public static $gender = [1,2,0];
    public static $systemAvatar = [1,2,3,4,5,6,7,8];
    public static $status = [1,-1,-2,-3];
    public static $thirdPlat = ['qq','sina','wechat'];
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%user}}';
    }
    
    /**
     * 
     * @param type $status
     * @param type $uid
     * @param type $mobile
     * @param type $nick
     * @param type $page
     * @return type
     */
    public function getList($params, $page = 1, $record = 20) {
        $andParams = [];
        if($params['status']) {
            $andParams['status'] = $params['status'];
        }
        if($params['uid']) {
            $andParams['id'] = $params['uid'];
        }
        if($params['tel']) {
            $andParams['tel'] = [
                'val'=>'%' . $params['tel'] . '%',
                'op' =>'like'
            ];
        }
        if($params['nick']) {
            $andParams['nickname'] = [
                'val'=>'%' . $params['nick'] . '%',
                'op' =>'like'
            ];
        }
        $select = 'id,serial_num,tel,nickname,avatar,gender,emotion,status,create_time';
        $result = $this->getListData($select, $page, $record, $andParams);
        foreach($result['rows'] as $key=>$user) {
            $result['rows'][$key]['avatar'] = Yii::$app->params['image_domain'] . $user['avatar'];
        }
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
    /**
     * 获取用户信息详情
     * @param int $uid 用户ID
     * @return array
     */
    public function getInfo($uid) {
        $Query = new Query();
        $result = $Query->select('id,serial_num,nickname,concat("'.Yii::$app->params['image_domain'].'",`avatar`) as avatar,tel,status,birth_date,gender,emotion,hometown,signature')
                        ->from($this->tableName())
                        ->where(['id'=>$uid])
                        ->one();
        return $result;
    }
    
    public function saveUser($tel, $pwd, $nick, $avatar, $gender, $birth, $signature) {
        if(!Tool::isMobile($tel)) {
            $this->addError('', '-4:手机号格式有误');
            return false;
        }
        if(!Tool::isPwd($pwd)) {
            $this->addError('', '-4:密码格式有误');
            return false;
        }
        if(!in_array($gender, self::$gender)) {
            $this->addError('', '-4:性别格式有误');
            return false;
        }
        if(!Tool::isDate($birth)) {
            $this->addError('', '-4:日期格式有误');
            return false;
        }
        $Record = $this->findByCondition(['tel'=>$tel])->one();
        if($Record) {
            $this->addError('', '-101:该手机号已存在');
            return false;
        }
        $pwd = Tool::md5Double($pwd);
        //启动事务
        $this->beginTransaction();
        $this->tel = $tel;
        $this->pwd = $pwd;
        $this->nickname = $nick;
        $this->gender = $gender;
        $this->signature = $signature ? $signature : '神秘的小言';
        $this->birth_date = $birth;
        if(!$this->save()) {
            $this->rollback();//回滚事务
            $this->addError('', '0:注册失败1');
            return false;
        }
        //修改serial_num字段,言言ID
        $serialNum = $this->getSerialNum($this->id);
        $this->serial_num = $serialNum;
        if(!$this->save(false)) {
            $this->rollback();
            $this->addError('', '0:注册失败2');
            return false;
        }
        //上传用户头像
        $avatarUrl = '/system/avatar_default_'.$avatar.'.png';
        if(strlen($avatar) > 1) {
            $Attachment = new Attachment();
            $avatar = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $avatar);
            $result = $Attachment->uploadBase64Img($avatar, 'avatar');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $avatarUrl = $result['path'];
        }
        $this->avatar = $avatarUrl;
        if(!$this->save()) {
            $this->rollback();
            $this->addError('', '0:注册失败3');
            return false;
        }
        //添加用户设置
        $Setting = new Setting();
        if(!$Setting->add($this->id)) {
            $this->rollback();
            $this->addError('', '0:注册失败4');
            return false;
        }
        //注册环信用户
        $easeUname = $this->getEaseUname($this->id);
        $response = Easemob::getInstance()->createUser($easeUname, $this->pwd);
        if($response['code'] != '200') {
            $this->rollback();
            $this->addError('', '0:注册失败5');
            return false;
        }
        $this->ease_uid = $easeUname;
        if(!$this->save()) {
            $this->rollback();
            $this->addError('', '0:注册失败6');
            return false;
        }
        $this->commit();
        return true;
    }
    
    /**
     * 更新用户状态或职业兴趣
     * @param int $uid 用户ID
     * @param int $status  用户状态
     * @return boolean
     */
    public function updateUser($uid, $status) {
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!in_array($status, self::$status)) {
            $this->addError('', '-4:用户状态格式有误');
            return false;
        }
        $Record = $this->findOne($uid);
        if(!$Record) {
            $this->addError('', '-7:该用户不存在');
            return false;
        }
        if($Record->status == $status) {
            return true;
        }
        if($Record->status != $status) {
            if($status == -1) {
                $freezeTime = date('Y-m-d H:i:s');
                $unfreezeTime = '0000-00-00 00:00:00';
                $cmdInfo = ['cmd_type'=>8, 'desc'=>'解除冻结'];
                $record = [
                    'user_id'  =>$uid,
                    'is_freeze'=>1,
                    'add_time' =>time()
                ];
                $ext = array_merge($cmdInfo, ['record'=>$record]);
                Easemob::getInstance()->deactiveUser($Record->ease_uid);
            }else if($status == 1) {
                $freezeTime = '0000-00-00 00:00:00';
                $unfreezeTime = '0000-00-00 00:00:00';
                if($Record->status != -1) {    //解除冻结
                    $cmdInfo = ['cmd_type'=>8, 'desc'=>'解除冻结'];
                    $record = [
                        'user_id'  =>$uid,
                        'is_freeze'=>1,
                        'add_time' =>time()
                    ];
                    $ext = array_merge($cmdInfo, ['record'=>$record]);
                }else {
                    Easemob::getInstance()->activeUser($Record->ease_uid);
                }
            } else {
                $record = [
                        'user_id'  =>$uid,
                        'is_freeze'=>1
                    ];
                if($status == -2) {    //冻结8小时
                    $freezeTime = date('Y-m-d H:i:s');
                    $unfreezeTime = date('Y-m-d H:i:s', strtotime(' + 8 hour'));
                    $cmdInfo = ['cmd_type'=>8, 'desc'=>'该账户因违规被处以冻结8小时处罚'];
                    $record['add_time'] = 8;
                }else if($status == -3) {      //冻结24小时
                    $freezeTime = date('Y-m-d H:i:s');
                    $unfreezeTime = date('Y-m-d H:i:s', strtotime(' + 24 hour'));
                    $cmdInfo = ['cmd_type'=>24, 'desc'=>'该账户因违规被处以冻结24小时处罚'];
                    $record['add_time'] = 24;
                }
                $ext = array_merge($cmdInfo, ['record'=>$record]);
            }
            $Record->status = $status;
            $Record->freeze_time = $freezeTime;
            $Record->unfreeze_time = $unfreezeTime;
            if(!$Record->save()) {
                $this->addError('', '0:更新失败');
                return false;
            }
        }
        
        if(!empty($ext)){//给用户发送通知
            $hxUserName = [$Record->ease_uid];
            if( !empty($hxUserName)){
                $CmdHelper = new CmdHelper();
                $res = $CmdHelper->sendCmdMessageToUsers($hxUserName, $cmdInfo['desc'], $ext);
            }
        }
        return true;
    }
    
    /**
     * 获取系统用户列表
     * @return array
     */
    public function getSysUsers() {
        $sql = 'select id,nickname from ' . $this->tableName() . ' where tel like "926%"';
        return $this->findBySql($sql)->asArray()->all();
    }
}

