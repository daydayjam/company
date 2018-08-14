<?php
/**
 * 黑名单制器类
 * @author ztt
 * @date 2017/12/18
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Black;

class BlackController extends Controller {
    
    /**
     * 将用户添加到黑名单
     * @return void
     */
    public function actionAdd() {
        $uid = $this->getParam('uid');
        $Black = new Black();
        $result = $Black->add($uid);
        if(!$result) {
            $this->showError($Black);
        }
        $this->showOk();
    }
    
    /**
     * 移除黑名单
     * @return void
     */
    public function actionDel() {
        $uid = $this->getParam('uid');
        $Black = new Black();
        $result = $Black->remove($uid);
        if(!$result) {
            $this->showError($Black);
        }
        $this->showOk();
    }
    
    /**
     * 获取当前登录用户黑名单列表
     * @return void
     */
    public function actionList() {
        $Black = new Black();
        $result = $Black->getList();
        if($result === false) {
            $this->showError($Black);
        }
        $this->showOk($result);
    }
}

