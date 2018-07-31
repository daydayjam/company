<?php
/**
 * 标签控制器
 * @date 2018/06/06
 * @author ztt
 */

namespace app\controllers;

use app\components\Controller;
use app\models\Tag;

class TagController extends Controller {
    
    /**
     * 获取标签列表
     * @return void
     */
    public function actionList() {
        $type = $this->getParam('type', 1);
        $Tag = new Tag();
        $result = $Tag->getList($type);
        if($result === false) {
            $this->showError($Tag);
        }
        $this->showOk($result);
        
    }
}


