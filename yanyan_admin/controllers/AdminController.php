<?php
/**
 * 首页控制器类
 * @author ztt
 * @date 2018/01/08
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Manager;

class AdminController extends Controller {
    
    /**
     * 渲染首页
     * @return 
     */
    public function actionIndex() {
        $uid = $this->getParam('uid');
        return $this->render('index', $uid);
    }
    
    /**
     * 渲染登录界面
     * @return void
     */
    public function actionLogin() {
        return $this->render('login');
    }
    
    /**
     * 渲染登录界面
     * @return void
     */
    public function actionSignin() {
        $uname = $this->getParam('uname');
        $pwd = $this->getParam('pwd');
        $Manager = new Manager();
        $result = $Manager->signin($uname, $pwd);
        if(!$result) {
            $this->showError($Manager);
        }
        $this->showOk($result);
    }
    
    /**
     * 登出
     * @return void
     */
    public function actionSignout() {
        setcookie('code', '',time()-1, '/');  
        $this->showOk();
    }
}

