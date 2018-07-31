<?php
/**
 * 文章操作控制器
 * @author ztt
 * @date 2018/03/09
 */

namespace app\controllers;

use app\components\Controller;
use app\models\PoetrySpider;
use app\models\RhesisSpider;

class SpiderController extends Controller {
    
    /**
     * 添加诗文信息
     * @return void
     */
    public function actionAdd() {
        $data = $_POST['data'];
        $PoetrySpider = new PoetrySpider();
        $result = $PoetrySpider->add($data);
        if(!$result) {
            $this->showError($PoetrySpider);
        }
        $this->showOk();
    }
    
    /**
     * 添加名句信息
     * @return void
     */
    public function actionAddrhesis() {
        $data = $_POST['data'];
        $RhesisSpider = new RhesisSpider();
        $result = $RhesisSpider->add($data);
        if(!$result) {
            $this->showError($RhesisSpider);
        }
        $this->showOk();
    }
}
