<?php
/**
 * 聊天室控制器类
 * @author ztt
 * @date 2017/12/30
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Cache;
use app\models\Chatroom;

class ChatroomController extends Controller {
    
    /**
     * 获取聊天室详情
     * @return void
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $Chatroom = new Chatroom();
        $result = $Chatroom->getInfo($id);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取聊天室细则
     * @return void
     */
    public function actionDetail() {
        $id = $this->getParam('id');
        $Chatroom = new Chatroom();
        $result = $Chatroom->getDetail($id);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取聊天室成员信息
     * @return void
     */
    public function actionUserinfo() {
        $easeUid = $this->getParam('euid');
	$easeRoomId = $this->getParam('erid');
        $Chatroom = new Chatroom();
        $result = $Chatroom->getUserinfo($easeUid, $easeRoomId);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取聊天室角色列表
     * @return void
     */
    public function actionRoles() {
        $id = $this->getParam('id');
        $Chatroom = new Chatroom();
        $result = $Chatroom->getRoles($id);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk($result);
    }
    
    /**
     * 选择角色
     * @return void
     */
    public function actionSetrole() {
        $roomId = $this->getParam('room_id');
        $roleId = $this->getParam('role_id');
        $Chatroom = new Chatroom();
        $result = $Chatroom->setRole($roomId, $roleId);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk($result);
    }
    
    /**
     * 聊天室举报用户
     * @return void
     */
    public function actionTipoff() {
        $roomId = $this->getParam('room_id');
        $uid = $this->getParam('uid');
        $reason = $this->getParam('reason', 0);
        $needNum = $this->getParam('need_num', 3);
        $Chatroom = new Chatroom();
        $result = $Chatroom->tipoff($roomId, $uid, $reason, $needNum);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk($result);
    }
    
    /**
     * 聊天室同意举报用户
     * @return void
     */
    public function actionAgreetipoff() {
        $tipoffId = $this->getParam('tid');
        $Chatroom = new Chatroom();
        $result = $Chatroom->agreeTipoff($tipoffId);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk($result);
    }
    
    
    
    
    
}

