<?php
/**
 * 爬虫制器类
 * @author ztt
 * @date 2017/12/22
 */
namespace app\controllers;

use app\components\Controller;
use app\models\FilmSpider;

class SpiderController extends Controller {
    
    /**
     * 影视剧入库
     * @return void
     */
    public function actionSavefilm() {
        $data = $_POST['data'];
        $FilmSpider = new FilmSpider();
        $result = $FilmSpider->add($data);
        if(!$result) {
            $this->showError($FilmSpider);
        }
        $this->showOk();
    }
    
    
}

