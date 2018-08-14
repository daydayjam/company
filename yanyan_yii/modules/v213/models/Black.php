<?php
/**
 * 黑名单模型类
 * @author ztt
 * @date 2017/11/10
 */
namespace app\models;

use Yii;
use yii\db\Query;
use app\components\ActiveRecord;
use yii\db\Transaction;

class Black extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%black}}';
    }
    
    /**
     * 判断用户是否在黑名单中
     * @param int $fuid 需要查找的用户ID
     * @param int $uid 
     * @return int 1=在黑名单中
     */
    public function isInBlack($fuid, $uid) {
        $Record = $this->findByCondition(['uid'=>$uid, 'to_uid'=>$fuid])->asArray()->one();
        if(!$Record) {
            return 0;
        }
        return 1;
    }
    
    /**
     * 获取黑名单用户ID集合
     * @param int $uid 用户ID
     * @return array
     */
    public function getBlacks($uid) {
        $Black = new Black();
        $sql = 'select to_uid from ' . $Black->tableName() . ' where uid=:uid';
        try {
            $blackRecords = $Black->findBySql($sql, [':uid'=>$uid])->asArray()->all();
        }catch(Exception $e) {
            $this->addError('', '-11:' . $e->getMessage());
            return false;
        }
        $blacks = [];
        foreach($blackRecords as $record) {
            $blacks[] = $record['to_uid'];
        }
        return $blacks;
    }
    
    /**
     * 添加黑名单
     * @param int $blackUid 要加入黑名单的用户ID
     * @return mixed array=添加黑名单成功
     */
    public function add($blackUid) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($blackUid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $User = new User();
        $BlackUserRecord = $User->findOne($blackUid);
        if(!$BlackUserRecord) {
            $this->addError('', '-7:要加入黑名单的用户不存在');
            return false;
        }
        if($this->isInBlack($blackUid, $loginUid)) {
            $this->addError('', '-300:该用户已在您的黑名单中');
            return false;
        }
        //关联环信
        $Friend = new Friend();
        $Friend->beginTransaction();
        $usernames = array('usernames'=>array($BlackUserRecord->ease_uid));
        $response = Easemob::getInstance()->addUserForBlacklist(Cache::hget('ease_uid'), $usernames);
	if($response['code'] != 200) {
            $Friend->rollback();
            $this->addError('', '0:加入黑名单失败');
            return false;
	}
        $this->uid = $loginUid;
        $this->to_uid = $blackUid;
        if(!$this->save()) {
            $Friend->rollback();
            $this->addError('', '0:加入黑名单失败');
            return false;
        }
        //要是当前用户关注了该黑名单用户，则取消关注
        $FriendRecord = $Friend->findByCondition(['uid'=>$loginUid, 'fuid'=>$blackUid])->one();
        if($FriendRecord) {
            $result = $Friend->unfollow($blackUid);
            if(!$result) {
                $Friend->rollback();
                $error = $Friend->getCodeError();
                $this->addError('', $error['code'].':'.$error['msg']);
                return false;
            }
        }
        $Friend->commit();
        return true;
    }
    
    /**
     * 移除黑名单用户
     * @param int $uid 黑名单用户ID
     * @return boolean true=移除成功
     */
    public function remove($uid) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $User = new User();
        $UserRecord = $User->findOne($uid);
        if(!$UserRecord) {
            $this->addError('', '-7:该用户不存在');
            return false;
        }
        $Record = $this->findByCondition(['uid'=>$loginUid, 'to_uid'=>$uid])->one();
        if(!$Record) {
            $this->addError('', '-301:该用户不在您的黑名单中');
            return false;
        }
        //关联环信
        $this->beginTransaction();
        $response = Easemob::getInstance()->deleteUserFromBlacklist(Cache::hget('ease_uid'), $UserRecord->ease_uid);
	if($response['code'] != 200) {
            $this->rollback();
            $this->addError('', '0:黑名单用户移除失败1');
            return false;
	}
        if(!$Record->delete()) {
            $this->rollback();
            $this->addError('', '0:黑名单用户移除失败2');
            return false;
        }
        $this->commit();
        return true;
    }
    
    /**
     * 获取当前登录用户的黑名单列表
     * @return array 黑名单列表
     */
    public function getList() {
        $loginUid = Cache::hget('id');
        $Query = new Query();
        $result = $Query->select(['u.id','concat("'.Yii::$app->params['image_domain'].'",u.avatar) as avatar','u.nickname','u.ease_uid'])
                        ->from($this->tableName() . ' as b')
                        ->leftJoin(User::tableName() . ' as u', 'u.id=b.to_uid')
                        ->where(['b.uid'=>$loginUid])
                        ->orderBy(['b.create_time'=>SORT_DESC])
                        ->all();
        return $result;
    }
    
    /**
     * 启动事务
     * @return void 无返回值
     */
    protected function beginTransaction($isolationLevel = null) {
        $this->_Transaction = Yii::$app->db->beginTransaction($isolationLevel);
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

