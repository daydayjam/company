<?php
/**
 * 角色模型类
 * @author ztt
 * @date 2017/12/13
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use yii\db\Query;
use app\models\Film;
use app\models\Chatroom;

class Role extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%roles}}';
    }
    
    /**
    * 获取角色列表
    * @param array $params
    * @param int $page
    * @param int $record
    * @return array
    */
    public function getList($fid, $page = 1, $record = 20) {
        $andParams = ['tv_id'=>$fid];
        $select = 'id,role_name,concat("'.Yii::$app->params['image_domain'].'",`avatar`) as avatar,rdesc';
        $result = $this->getListData($select, $page, $record, $andParams);
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
    /**
     * 获取聊天室信息详情
     * @param int $id 影视剧ID
     * @return array
     */
    public function getInfo($id) {
        $Query = new Query();
        $result = $Query->select('id,role_name,concat("'.Yii::$app->params['image_domain'].'",`avatar`) as avatar,rdesc')
                        ->from($this->tableName())
                        ->where(['id'=>$id])
                        ->one();
        return $result;
    }
    
    /**
     * 添加角色信息
     * @param int $fid
     * @param string $roleName
     * @param string $rdesc
     * @param string $avatar
     * @return boolean
     */
    public function saveRole($fid, $roleName, $rdesc, $avatar) {
        if(!is_numeric($fid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(empty($roleName) || empty($rdesc) || empty($rdesc)) {
            $this->addError('', '-3:角色名称、描述、头像不允许为空');
            return false;
        }
        $Film = new Film();
        $Record = $Film->findOne($fid);
        if(!$Record) {
            $this->addError('', '-7:相应聊天室不存在');
            return false;
        }
        $this->beginTransaction();
        $Attachment = new Attachment();
        $avatar = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $avatar);
        $result = $Attachment->uploadBase64Img($avatar, 'chatroom');
        if(!$result) {
            $this->rollback();
            $error = $Attachment->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        $avatarUrl = $result['path'];
        $this->role_name = $roleName;
        $this->tv_id = $fid;
        $this->rdesc = $rdesc;
        $this->avatar = $avatarUrl;
        if(!$this->save()) {
            $this->addError('', '0:角色创建失败2');
            return false;
        }
        $this->commit();
        return true;
    }
    
    /**
     * 修改角色信息
     * @param int $id
     * @param string $roleName
     * @param string $rdesc
     * @param string $avatar
     * @return boolean
     */
    public function updateRole($id, $roleName, $rdesc, $avatar) {
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(empty($roleName) || empty($rdesc)) {
            $this->addError('', '-3:角色名称和描述不允许为空');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:相应角色不存在');
            return false;
        }
        $this->beginTransaction();
        $avatarUrl = '';
        if($avatar) {
            $Attachment = new Attachment();
            $avatar = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $avatar);
            $result = $Attachment->uploadBase64Img($avatar, 'chatroom');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $avatarUrl = $result['path'];
        }
        $Record->role_name = $roleName;
        $Record->rdesc = $rdesc;
        $Record->avatar = $avatarUrl ? $avatarUrl : $Record->avatar;
        if(!$Record->save()) {
            $this->addError('', '0:聊天室修改失败2');
            return false;
        }
        $this->commit();
        return true;
    }
    
    /**
     * 删除角色
     * @param string $ids  角色ID字符串，格式1,2,3,4
     * @return boolean
     */
    public function del($ids) {
        $idArr = explode(',', $ids);
        $this->beginTransaction();
        foreach($idArr as $item) {
            if(!$item) {
                continue;
            }
            $Record = $this->findOne($item);
            if(!$Record) {
                $this->rollback();
                $this->addError('', '-7:角色不存在');
                return false;
            }
            if(!$Record->delete()) {
                $this->rollback();
                $this->addError('', '0:删除失败');
                return false;
            }
        }
        $this->commit();
        return true;
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

