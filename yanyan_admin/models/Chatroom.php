<?php
/**
 * 聊天室类
 * @author ztt
 * @date 2017/12/13
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\Role;
use app\models\Film;
use app\models\UserRole;
use app\models\Tipoff;


class Chatroom extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
    public static $ansRight = [1,2,3,4];
    public static $reason = [0,1,2,3,4,5];    //举报原因，0=其他；1=暴力色情；2=人身攻击；3=广告骚扰；4=谣言及虚假信息；5=政治敏感，默认为0
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%chatroom}}';
    }
    
    /**
     * 创建聊天室
     * @param int $fid
     * @param datetime $st
     * @param datetime $et
     * @param string $question
     * @param string $ansList
     * @param int $ansRight
     * @param string $imgTop base64
     * @param string $imgQue base64
     * @return boolean
     */
    public function saveRoom($fid, $st, $et, $question, $ansList, $ansRight, $imgTop, $imgQue) {
        if(!is_numeric($fid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!Tool::isDate($st, 'Y-m-d H:i:s') || !Tool::isDate($et, 'Y-m-d H:i:s')) {
            $this->addError('', '-4:开始结束时间格式有误');
            return false;
        }
        if(strtotime($st) >= strtotime($et)) {
            $this->addError('', '-4:结束结束时间不应小于开始时间');
            return false;
        }
        if(!in_array($ansRight, self::$ansRight)) {
            $this->addError('', '-4:选项格式不正确');
            return false;
        }
        $Record = $this->findOne($fid);
        if($Record) {
            $this->addError('', '-203:聊天室已存在');
            return false;
        }
        $Film = new Film();
        $FilmRecord = $Film->findOne($fid);
        if(!$FilmRecord) {
            $this->addError('', '-7:关联影视剧不存在，无法创建聊天室');
            return false;
        }
        $options = [];
        $options['name'] = $FilmRecord->name;
        $options['description'] = $FilmRecord->name;
        $options['maxusers'] = 100;
//        $options['owner'] = "yanyan_chat_owner";    //正式
        $options['owner'] = "dev_yanyan_chat_owner";    //测试
//        $options['owner'] = "local_yanyan_chat_owner";    //本地
        
        $this->beginTransaction();
//        $reaseId = '123456';
        $response = Easemob::getInstance()->createChatRoom($options);
//        print_r($response);die;
        if($response['code'] != 200) {
            sleep(1);
            $response = Easemob::getInstance()->createChatRoom($options);
            if($response['code'] == 200){
                $reaseId = $response['result']['data']['id'];//返回的聊天室的id
            } else {
                $this->rollback();
                $this->addError('', '0:聊天室创建失败1');
                return false;
            }
        } else {
            $reaseId = $response['result']['data']['id'];//返回的聊天室的id
        }
        
        //图像处理
        $imgTopUrl = '';
        if($imgTop) {
            $Attachment = new Attachment();
            $imgTop = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $imgTop);
            $result = $Attachment->uploadBase64Img($imgTop, 'chatroom');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $imgTopUrl = $result['path'];
        }
        $imgQueUrl = '';
        if($imgQue) {
            $Attachment = new Attachment();
            $imgQue = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $imgQue);
            $result = $Attachment->uploadBase64Img($imgQue, 'chatroom');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $imgQueUrl = $result['path'];
        }
        
        $this->film_id = $fid;
        $this->name = $FilmRecord->name;
        $this->ease_id = $reaseId;
        $this->start_time = $st;
        $this->end_time = $et;
        $this->question = $question;
        $this->img_top = $imgTopUrl;
        $this->img_question = $imgQueUrl;
        $this->answers = stripcslashes($ansList);
        $this->ans_right = $ansRight;
        if(!$this->save()) {
            $this->rollback();
            $this->addError('', '0:聊天室创建失败2');
            return false;
        }
        $FilmRecord->ease_id = $reaseId;
        if(!$FilmRecord->save()) {
            $this->rollback();
            $this->addError('', '0:聊天室创建失败3');
            return false;
        }
        $this->commit();
        return true;
    }
    
    /**
     * 更新聊天室
     * @param int $fid
     * @param datetime $st
     * @param datetime $et
     * @param string $question
     * @param string $ansList
     * @param int $ansRight
     * @param string $imgTop base64
     * @param string $imgQue base64
     * @return boolean
     */
    public function updateRoom($fid, $st, $et, $question, $ansList, $ansRight, $imgTop, $imgQue) {
        if(!is_numeric($fid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!Tool::isDate($st, 'Y-m-d H:i:s') || !Tool::isDate($et, 'Y-m-d H:i:s')) {
            $this->addError('', '-4:开始结束时间格式有误');
            return false;
        }
        if(strtotime($st) >= strtotime($et)) {
            $this->addError('', '-4:结束结束时间不应小于开始时间');
            return false;
        }
        if(!in_array($ansRight, self::$ansRight)) {
            $this->addError('', '-4:选项格式不正确');
            return false;
        }
        $Record = $this->findOne($fid);
        if(!$Record) {
            $this->addError('', '-7:聊天室不存在');
            return false;
        }
        
        $this->beginTransaction();
        //图像处理
        $imgTopUrl = '';
        if($imgTop) {
            $Attachment = new Attachment();
            $imgTop = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $imgTop);
            $result = $Attachment->uploadBase64Img($imgTop, 'chatroom');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $imgTopUrl = $result['path'];
        }
        $imgQueUrl = '';
        if($imgQue) {
            $Attachment = new Attachment();
            $imgQue = preg_replace('/^(data:\s*image\/(\w+);base64,)/', '', $imgQue);
            $result = $Attachment->uploadBase64Img($imgQue, 'chatroom');
            if(!$result) {
                $this->rollback();
                $error = $Attachment->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $imgQueUrl = $result['path'];
        }
        $Record->start_time = $st;
        $Record->end_time = $et;
        $Record->question = $question;
        $Record->img_top = $imgTopUrl ? $imgTopUrl : $Record->img_top;
        $Record->img_question = $imgQueUrl ? $imgQueUrl : $Record->img_question;
        $Record->answers = stripcslashes($ansList);
        $Record->ans_right = $ansRight;
        if(!$Record->save()) {
            $this->rollback();
            $this->addError('', '0:聊天室修改失败2');
            return false;
        }
        $this->commit();
        return true;
    }
    
   /**
    * 获取聊天室列表
    * @param array $params
    * @param int $page
    * @param int $record
    * @return array
    */
    public function getList($params, $page = 1, $record = 20) {
        $andParams = [];
        if($params['fid']) {
            $andParams['film_id'] = $params['fid'];
        }
        if($params['is_open']!=='') {
            $andParams['is_open'] = $params['is_open'];
        }
        if($params['name']) {
            $andParams['name'] = [
                'val'=>'%' . $params['name'] . '%',
                'op' =>'like'
            ];
        }
        $select = 'film_id as id,name,is_open,end_time';
        $result = $this->getListData($select, $page, $record, $andParams);
        foreach($result['rows'] as $key=>$room) {
            $result['rows'][$key]['is_over_time'] = strtotime($room['end_time'])<time() ? 1 : 0;
        }
        $result['page'] = $page;
        $result['record'] = $record;
        return $result;
    }
    
    /**
     * 获取聊天室信息详情
     * @param int $fid 影视剧ID
     * @return array
     */
    public function getInfo($fid) {
        $Query = new Query();
        $result = $Query->select('film_id as id,name,start_time,end_time,concat("'.Yii::$app->params['image_domain'].'",`img_top`) as img_top,concat("'.Yii::$app->params['image_domain'].'",`img_question`) as img_que,question,answers,ans_right,is_open')
                        ->from($this->tableName())
                        ->where(['film_id'=>$fid])
                        ->one();
        $result['answers'] = json_decode($result['answers'], true);
        return $result;
    }
    
    /**
     * 更新聊天室开关信息
     * @param int $fid
     * @param int $isOpen
     * @return boolean
     */
    public function changeSwitch($fid, $isOpen) {
        if(!is_numeric($fid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!in_array($isOpen, [0,1])) {
            $this->addError('', '-4:开关参数格式有误');
            return false;
        }
        $Record = $this->findOne($fid);
        if(!$Record) {
            $this->addError('', '-7:聊天室不存在');
            return false;
        }
        if($Record->is_open == $isOpen) {
            return true;
        }
        $Record->is_open = $isOpen;
        if(!$Record->save()) {
            $this->addError('', '0:开关信息更新失败');
            return false;
        }
        return true;
    }
    
    /**
     * 删除聊天室
     * @param int $fid
     * @return boolean
     */
    public function del($fid) {
        if(!is_numeric($fid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($fid);
        if(!$Record) {
            $this->addError('', '-7:聊天室不存在');
            return false;
        }
        $this->beginTransaction();
//        $result = Easemob::getInstance()->deleteChatRoom($Record->ease_id);
//        if($result['code'] != 200) {
//            $this->rollback();
//            $this->addError('', '0:删除失败');
//            return false;
//        }
        if(!$Record->delete()) {
            $this->rollback();
            $this->addError('', '0:删除失败1');
            return false;
        }
        $Film = new Film();
        $FilmRecord = $Film->findOne($fid);
        $FilmRecord->ease_id = '';
        if(!$FilmRecord->save()) {
            $this->rollback();
            $this->addError('', '0:删除失败2');
            return false;
        }
        $Role = new Role();
        $RoleRecords = $Role->findByCondition(['tv_id'=>$fid])->one();
        if($RoleRecords && !$Role->deleteAll(['tv_id'=>$fid])) {
            $this->rollback();
            $this->addError('', '0:删除失败2');
            return false;
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

