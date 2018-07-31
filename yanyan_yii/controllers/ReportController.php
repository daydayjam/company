<?php
/**
 * 举报控制器类
 * @author ztt
 * @date 2017/11/20
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Report;
use app\models\Cache;

class ReportController extends Controller {
    
    /**
     * 添加举报信息
     * @return void
     */
    public function actionAdd() {
        $uid = Cache::hget('id');
        $assocId = $this->getParam('assoc_id');
        $type = $this->getParam('type', 0);
        $reason = $this->getParam('reason', 0);
        $Report = new Report();
        $result = $Report->add($uid, $assocId, $type, $reason);
        if(!$result) {
            $this->showError($Report);
        }
        $this->showOk();
    }
    
    
}

