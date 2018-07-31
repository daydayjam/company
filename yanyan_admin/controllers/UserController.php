<?php
/**
 * 用户控制器类
 * @author ztt
 * @date 2018/01/09
 */
namespace app\controllers;

use app\components\Controller;
use app\models\User;

class UserController extends Controller {
    
    /**
     * 渲染用户列表
     * @return 
     */
    public function actionList() {
        $params = [];
        $params['status'] = $this->getParam('status');
        $params['uid'] = $this->getParam('uid');
        $params['tel'] = $this->getParam('tel');
        $params['nick'] = $this->getParam('nick');
        $page = $this->getParam('page', 1);
        $record = 20;
        $User = new User();
        $result = $User->getList($params, $page, $record);
        return $this->render('list', $result, $params);
    }
    
    /**
     * 渲染用户详情界面
     * @return 
     */
    public function actionInfo() {
        $uid = $this->getParam('uid');
        $User = new User();
        $result = $User->getInfo($uid);
        return $this->render('info', $result);
    }
    
    /**
     * 渲染用户详情界面
     * @return 
     */
    public function actionAdd() {
        return $this->render('add');
    }
    
    /**
     * 更新用户信息
     * @return void
     */
    public function actionSave() {
        $tel = $this->getParam('tel');
        $pwd = $this->getParam('pwd');
        $nick = $this->getParam('nick');
        $gender = $this->getParam('gender');
        $birth = $this->getParam('birth');
        $signature = $this->getParam('signature');
        $avatar = $this->getParam('avatar');
        $User = new User();
        $result = $User->saveUser($tel, $pwd, $nick, $avatar, $gender, $birth, $signature);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    /**
     * 更新用户信息
     * @return void
     */
    public function actionUpdate() {
        $uid = $this->getParam('uid');
        $status = $this->getParam('status');
        $User = new User();
        $result = $User->updateUser($uid, $status);
        if(!$result) {
            $this->showError($User);
        }
        $this->showOk();
    }
    
    
    
}

