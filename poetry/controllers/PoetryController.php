<?php
/**
 * 诗词控制器
 * @date 2018/06/06
 * @author ztt
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Poetry;

class PoetryController extends Controller {
    
    /**
     * 获取诗词列表
     * @return void
     */
    public function actionSearchindex() {
        $keyword = $this->getParam('keyword');
        $Poetry = new Poetry();
        $result = $Poetry->getSearchIndexList($keyword);
        if($result == false) {
            $this->showError($Poetry);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取搜索详细列表信息
     * @return void
     */
    public function actionSearchlist() {
        $searchBy = $this->getParam('search_by');
        $keyword = $this->getParam('keyword');
        $relId = $this->getParam('rel_id', 1);
        $page = $this->getParam('page', Yii::$app->params['page']);
        $pagesize = $this->getParam('pagesize', Yii::$app->params['pagesize']);
        $Poetry = new Poetry();
        $result = $Poetry->getSearchList($searchBy, $keyword, $relId, $page, $pagesize);
        if($result == false) {
            $this->showError($Poetry);
        }
        $this->showOkN($result);
    }
    
    
    
}


