<?php
/**
 * 好友模型类
 * @author ztt
 * @date 2017/10/31
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\User;
use app\models\Black;
use app\models\Cmd;

class Friend extends ActiveRecord {
    
    private $_Transaction = null;  //事务处理对象
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%friend}}';
    }
    
    
    /**
     * 获取某用户的关注/粉丝列表
     * @param int $uid 用户ID
     * @param int $type 关注列表还是好友列表,true=关注列表，false=粉丝列表
     * @return array
     */
    public function getList($uid, $type = false) {
        $sql = 'select u.id,u.ease_uid,concat("'.Yii::$app->params['image_domain'].'",u.avatar) as avatar,u.nickname,u.signature,u.gender '
                . 'from '.$this->tableName().' f ';
        if($type) {
            $sql .= 'inner join '.User::tableName().' u on f.fuid=u.id where f.uid=:uid';
        }else {
            $sql .= 'inner join '.User::tableName().' u on f.uid=u.id where f.fuid=:uid';
        }
        $sql .= ' order by f.create_time asc';
        $params = [':uid' => $uid];
        $records = $this->findBySql($sql, $params)->asArray()->all();
        return $records;
    }
    
    /**
     * 关注用户
     * @param int $uid 关注人用户ID
     * @param int $fuid 被关注人用户ID
     * @return mixed false=关注失败，array=关注成功
     */
    public function follow($fuid) {
        $uid = Cache::hget('id');
        if(!is_numeric($fuid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $User = new User();
        $FUserRecord = $User->findOne($fuid);
        if(!$FUserRecord) {
            $this->addError('', '-7:要关注的用户不存在');
            return false;
        }
        $Record = $this->findByCondition(['uid'=>$uid, 'fuid'=>$fuid])->one();
        if($Record) {
            $this->addError('', '-9:您已关注过该用户，请勿重复关注');
            return false;
        }
        $Black = new Black();
        $BlackRecord = $Black->findByCondition(['uid'=>$uid, 'to_uid'=>$fuid])->one();
        if($BlackRecord) {
            $this->addError('', '-300:您要关注的用户在黑名单中，无法再次关注');
            return false;
        }
        $this->beginTransaction();
        $this->uid = $uid;
        $this->fuid = $fuid;
        if(!$this->save()) {
            $this->rollback();
            $this->addError('', '0:关注失败');
            return false;
        }
        //组织数据
        $cmdInfo = ['cmd_type'=>6, 'desc'=>'有人关注了您'];
        $record = ['from_uid'=>$uid,
                    'from_ease_uid'=>Cache::hget('ease_uid'),
                    'from_name'=>Cache::hget('nick'),
                    'from_avatar'=>Cache::hget('avatar'),
                    'from_gender'=>Cache::hget('gender'),
                    'from_signature'=>Cache::hget('signature'),
                    'to_uid'=>$fuid,
                    'to_name'=>$FUserRecord->nickname,
                    'to_gender'=>$FUserRecord->gender,
                    'to_signature'=>$FUserRecord->signature,
                    'add_time'=>time()
                ];
        $ext = array_merge($cmdInfo, ['record'=>$record]);
        $Cmd = new Cmd();
        $cmdId = $Cmd->add($ext);
        if(!$cmdId) {
            $this->rollback();
            $this->addError('', '0:关注失败');
            return false;
        }
        $ext['cmd_id'] = $cmdId;
        $CmdHelper = new CmdHelper();
        $CmdHelper->sendCmdMessageToUsers([$FUserRecord->ease_uid], $cmdInfo['desc'], $ext);
        $this->commit();
        $result = ['to_nickname'=>$FUserRecord->nickname];
        return $result; 
    }
    
    /**
     * 取消关注用户
     * @param int $uid 关注人用户ID
     * @param int $fuid 被关注人用户ID
     * @return mixed false=取消关注失败，array=取消关注成功
     */
    public function unfollow($fuid) {
        $uid = Cache::hget('id');
        if(!is_numeric($fuid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $User = new User();
        $FUserRecord = $User->findOne($fuid);
        if(!$FUserRecord) {
            $this->addError('', '-7:要取消关注的用户不存在');
            return false;
        }
        $Record = $this->findByCondition(['uid'=>$uid, 'fuid'=>$fuid])->one();
        if(!$Record) {
            $this->addError('', '-7:您还未关注过该用户，无法操作');
            return false;
        }
        $this->beginTransaction();
        if(!$Record->delete()) {
            $this->rollback();
            $this->addError('', '0:取消关注失败');
            return false;
        }
        //组织数据
        $cmdInfo = ['cmd_type'=>7, 'desc'=>'有人取消了对您的关注'];
        $record = ['from_uid'=>$uid,
                    'from_name'=>Cache::hget('nick'),
                    'from_avatar'=>Cache::hget('avatar'),
                    'from_gender'=>Cache::hget('gender'),
                    'from_signature'=>Cache::hget('signature'),
                    'to_uid'=>$fuid,
                    'to_name'=>$FUserRecord->nickname,
                    'to_gender'=>$FUserRecord->gender,
                    'add_time'=>time()
                ];
        $ext = array_merge($cmdInfo, ['record'=>$record]);
        $Cmd = new Cmd();
        $cmdId = $Cmd->add($ext);
        if(!$cmdId) {
            $this->rollback();
            $this->addError('', '0:关注失败');
            return false;
        }
        $ext['cmd_id'] = $cmdId;
        $CmdHelper = new CmdHelper();
        $CmdHelper->sendCmdMessageToUsers([$FUserRecord->ease_uid], $cmdInfo['desc'], $ext);
        $this->commit();
        $result = ['to_nickname'=>$FUserRecord->nickname];
        return $result; 
    }
    
    /**
     * 启动事务
     * @return void 无返回值
     */
    public function beginTransaction() {
        if($this->_Transaction != null) {
            $this->_Transaction = Yii::$app->db->beginTransaction();
        }
    }
    
    /**
     * 回滚事务
     * @return void 无返回值
     */
    public function rollback() {
        if($this->_Transaction != null) {
            $this->_Transaction->rollBack();
        }
    }
    
    /**
     * 提交事务
     * @return void 无返回值
     */
    public function commit() {
        if($this->_Transaction != null) {
            $this->_Transaction->commit();
        }
    }
    
    
}

