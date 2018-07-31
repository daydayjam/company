<?php
/**
 * 程序停留时间控制器
 * @date 2018/06/27
 * @author ztt
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Keep;

class KeepController extends Controller {
    
    /**
     * 添加停留时间
     * @return void
     */
    public function actionAdd() {
        $keepTime = $this->getParam('keep_time');
        $keepDate = $this->getParam('keep_date');
        $Keep = new Keep();
        $result = $Keep->add($keepTime, $keepDate);
        if(!$result) {
            $this->showError($Keep);
        }
        $this->showOk();
    }
    
    /**
     * 获取背诵时长排行榜
     * @return void
     */
    public function actionList() {
        $page = $this->getParam('page', Yii::$app->params['page']);
        $pagesize = $this->getParam('pagesize', Yii::$app->params['pagesize']);
        $Keep = new Keep();
        $result = $Keep->getList($page, $pagesize);
        if(!$result) {
            $this->showError($Keep);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取统计数据
     * @return void
     */
    public function actionStatistic() {
        $Keep = new Keep();
        $result = $Keep->getStatistic();
        if(!$result) {
            $this->showError($Keep);
        }
        $this->showOk($result);
    }
}


