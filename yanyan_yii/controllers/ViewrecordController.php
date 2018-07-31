<?php
/**
 * 观影历史控制器类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Film;
use app\models\FilmSource;
use app\models\FilmFollow;
use app\models\ViewRecord;

class ViewrecordController extends Controller {
    
    /**
     * 获取观影历史
     * @return void
     */
    public function actionList() {
        $userId = $this->getParam('user_id', 0);
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 50);
        $ViewRecord = new ViewRecord();
        $result = $ViewRecord->getList($userId, $page, $pagesize);
        if($result === false) {
            $this->showError($ViewRecord);
        }
        $this->showOk($result);
    }
    
    /**
     * 删除观影记录
     * @return void
     */
    public function actionDel() {
        $filmIds = $this->getParam('film_ids');
        $ViewRecord = new ViewRecord();
        $result = $ViewRecord->del($filmIds);
        if(!$result) {
            $this->showError($ViewRecord);
        }
        $this->showOk();
        
    }
    
}

