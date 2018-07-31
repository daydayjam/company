<?php
/**
 * 剧集控制器类
 * @author ztt
 * @date 2018/01-18
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Film;
use app\models\Episode;
use app\models\FollowFilm;

class EpisodeController extends Controller {
    
    public function actionAdd() {
        $filmId = $this->getParam('film_id');
        $epNum = $this->getParam('ep_num');
        $FollowFilm = new FollowFilm();
        $result = $FollowFilm->add($filmId, $epNum);
        if(!$result) {
            $this->showError($FollowFilm);
        }
        $this->showOk($result);
    }
    
    
    
    
}

