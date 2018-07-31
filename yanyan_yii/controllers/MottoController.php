<?php
/**
 * 寄语制器类
 * @author ztt
 * @date 2017/12/27
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Motto;

class MottoController extends Controller {
    
    /**
     * 获取影视剧寄语
     * @return void
     */
    public function actionRandom() {
        $filmId = $this->getParam('film_id', 0);
        $Motto = new Motto();
        $result = $Motto->getRandom($filmId);
        if(!$result) {
            $this->showError($Motto);
        }
        $this->showOk($result);
    }
}

