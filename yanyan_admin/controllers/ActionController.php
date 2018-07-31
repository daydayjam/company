<?php
/**
 * 菜单控制器类
 * @author ztt
 * @date 2018/01/09
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Action;

class ActionController extends Controller {
    
    /**
     * 渲染首页
     * @return 
     */
    public function actionList() {
        $Action = new Action();
        $menus = $Action->getMenu();
        $this->showOk($menus);
    }
    
    
    
}

