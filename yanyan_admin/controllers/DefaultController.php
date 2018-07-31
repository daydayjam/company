<?php
/**
 * 首页控制器类
 * @author ztt
 * @date 2018/01/08
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Action;
use app\models\Manager;

class DefaultController extends Controller {
    
    /**
     * 渲染首页
     * @return 
     */
    public function actionTop() {
        $uid = $this->getParam('uid');
        $Manager = new Manager();
        $result = $Manager->getInfo($uid);
        return $this->render('top', $result);
    }
    
    /**
     * 渲染首页
     * @return 
     */
    public function actionNav() {
        return $this->render('nav');
    }
    
    /**
     * 渲染首页
     * @return 
     */
    public function actionMenu() {
        $Action = new Action();
        $menus = $Action->getMenu();
        return $this->render('menu', $menus);
    }
    
    
    
    
    
}

