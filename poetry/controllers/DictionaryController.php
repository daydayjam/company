<?php
/**
 * 标签控制器
 * @date 2018/06/06
 * @author ztt
 */

namespace app\controllers;

use app\components\Controller;
use app\models\Dictionary;

class DictionaryController extends Controller {
    
    /**
     * 获取汉字详解
     * @return void
     */
    public function actionInfo() {
        $word = $this->getParam('word');
        $Dictionary = new Dictionary();
        $result = $Dictionary->getInfo($word);
        if(!$result) {
            $this->showError($Dictionary);
        }
        $this->showOkN($result);
    }
}


