<?php
/**
 * 播放源控制器
 * @author ztt
 * @date 2017/10/30
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\AuthCode;
use app\models\FilmSource;

class FilmsourceController extends Controller {
    
    /**
     * 片源报错反馈
     * @return void
     */
    public function actionFeedback() {
        $filmId = $this->getParam('film_id');
        $epNum = $this->getParam('ep_num');
        $routeId = $this->getParam('route_id');
        $FilmSource = new FilmSource();
        $result = $FilmSource->feedback($filmId, $epNum, $routeId);
        if(!$result) {
            $this->showError($FilmSource);
        }
        $this->showOk();
    }
    
    
}

