<?php
/**
 * 屏蔽模型类
 * @author ztt
 * @date 2017/11/27
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\User;
use app\models\Black;
use app\models\Cmd;

class Shield extends ActiveRecord {
    
    private $_Transaction = null;  //事务处理对象
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%shields}}';
    }
    
    
    /**
     * 屏蔽用户
     * @param int $uid 用户ID
     * @param int $suid 被屏蔽的用户ID
     * @return boolen true=屏蔽成功
     */
    public function add($suid) {
        $uid = Cache::hget('id');
        if(!is_numeric($suid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $User = new User();
        $SUserRecord = $User->findOne($suid);
        if(!$SUserRecord) {
            $this->addError('', '-7:要屏蔽的用户不存在');
            return false;
        }
        $Record = $this->findByCondition(['uid'=>$uid, 'to_uid'=>$suid])->one();
        if($Record) {
            $this->addError('', '-9:您已屏蔽该用户，请勿重复操作');
            return false;
        }
        $this->uid = $uid;
        $this->to_uid = $suid;
        if(!$this->save()) {
            $this->addError('', '0:屏蔽失败');
            return false;
        }
        return true;
    }
    
    /**
     * 取消屏蔽用户
     * @param int $uid 用户ID
     * @param int $suid 被屏蔽的用户ID
     * @return boolen true=取消屏蔽成功
     */
    public function del($suid) {
        $uid = Cache::hget('id');
        if(!is_numeric($suid)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $User = new User();
        $SUserRecord = $User->findOne($suid);
        if(!$SUserRecord) {
            $this->addError('', '-7:要取消屏蔽的用户不存在');
            return false;
        }
        $Record = $this->findByCondition(['uid'=>$uid, 'to_uid'=>$suid])->one();
        if(!$Record) {
            $this->addError('', '-7:您还未屏蔽该用户');
            return false;
        }
        if(!$Record->delete()) {
            $this->addError('', '0:取消屏蔽失败');
            return false;
        }
        return true;
    }
    
    public function getlist() {
        
    }
    
    
    
    /**
     * 启动事务
     * @return void 无返回值
     */
    private function beginTransaction() {
        $this->_Transaction = Yii::$app->db->beginTransaction();
    }
    
    /**
     * 回滚事务
     * @return void 无返回值
     */
    private function rollback() {
        if($this->_Transaction != null) {
            $this->_Transaction->rollBack();
        }
    }
    
    /**
     * 提交事务
     * @return void 无返回值
     */
    private function commit() {
        if($this->_Transaction != null) {
            $this->_Transaction->commit();
        }
    }
    
    
}

