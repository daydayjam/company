<?php
/**
 * 用户控制器类
 * @author ztt
 * @date 2018/01/09
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Chatroom;

class ChatroomController extends Controller {
    
    /**
     * 渲染聊天室列表
     * @return 
     */
    public function actionList() {
        $params = [];
        $params['fid'] = $this->getParam('fid');
        $params['is_open'] = $this->getParam('is_open');
        $params['name'] = $this->getParam('name');
        $page = $this->getParam('page', 1);
        $record = 20;
        $Chatroom = new Chatroom();
        $result = $Chatroom->getList($params, $page, $record);
        return $this->render('list', $result, $params);
    }
    
    /**
     * 渲染聊天室详情界面
     * @return 
     */
    public function actionInfo() {
        $fid = $this->getParam('fid');
        $Chatroom = new Chatroom();
        $result = $Chatroom->getInfo($fid);
        return $this->render('info', $result);
    }
    
    /**
     * 渲染聊天室创建界面
     * @return 
     */
    public function actionAdd() {
        $params = [];
        $params['fid'] = $this->getParam('fid');
        $params['name'] = $this->getParam('name');
        return $this->render('add', $params);
    }
    
    /**
     * 保存信息
     * @return void
     */
    public function actionSave() {
        $fid = $this->getParam('fid');
        $st = $this->getParam('st');
        $et = $this->getParam('et');
        $question = $this->getParam('question');
        $ansRight = $this->getParam('ans_right', 1);
        $imgTop = $this->getParam('img_top');
        $imgQue = $this->getParam('img_que');
        $ansList = $this->getParam('ans_list');
        $Chatroom = new Chatroom();
        $result = $Chatroom->saveRoom($fid, $st, $et, $question, $ansList, $ansRight, $imgTop, $imgQue);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk();
    }
    
    /**
     * 更新聊天室信息
     * @return void
     */
    public function actionUpdate() {
        $fid = $this->getParam('fid');
        $st = $this->getParam('st');
        $et = $this->getParam('et');
        $question = $this->getParam('question');
        $ansRight = $this->getParam('ans_right', 1);
        $imgTop = $this->getParam('img_top');
        $imgQue = $this->getParam('img_que');
        $ansList = $this->getParam('ans_list');
        $Chatroom = new Chatroom();
        $result = $Chatroom->updateRoom($fid, $st, $et, $question, $ansList, $ansRight, $imgTop, $imgQue);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk();
    }
    
    /**
     * 更新开关信息
     * @return void
     */
    public function actionSwitch() {
        $fid = $this->getParam('fid');
        $isOpen = $this->getParam('is_open');
        $Chatroom = new Chatroom();
        $result = $Chatroom->changeSwitch($fid, $isOpen);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk();
    }
    
    /**
     * 删除聊天室
     * @return void
     */
    public function actionDel() {
        $fid = $this->getParam('fid');
        $Chatroom = new Chatroom();
        $result = $Chatroom->del($fid);
        if(!$result) {
            $this->showError($Chatroom);
        }
        $this->showOk();
    }
    
    
}

