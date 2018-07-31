<?php
/**
 * 测试控制器类
 * @author ztt
 * @date 2017/10/30
 */
namespace app\controllers;

use app\components\Controller;

class TestController extends Controller {
    
    public function actionD() {
        return $this->render('test.tpl');
    }
    public function actionA() {
        $a = [1];
        echo implode(' ', $a);
    }
    
    
    
}

