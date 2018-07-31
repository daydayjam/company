<?php
/**
 * 收藏控制器
 * @date 2018/06/06
 * @author ztt
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Collection;

class CollectController extends Controller {
    
    /**
     * 获取收藏列表
     * @return void
     */
    public function actionList() {
        $page = $this->getParam('page', Yii::$app->params['page']);
        $pagesize = $this->getParam('pagesize', Yii::$app->params['pagesize']);
        $Collection = new Collection();
        $result = $Collection->getList($page, $pagesize);
        if($result == false) {
            $this->showError($Collection);
        }
        $this->showOk($result);
    }
    
    /**
     * 添加收藏
     * @return void
     */
    public function actionAdd() {
        $poetryId = $this->getParam('poetry_id');
        $Collection = new Collection();
        $result = $Collection->add($poetryId);
        if(!$result) {
            $this->showError($Collection);
        }
        $this->showOk();
    }
    
    /**
     * 取消收藏诗词
     * @return void
     */
    public function actionRemove() {
        $poetryId = $this->getParam('poetry_id');
        $Collection = new Collection();
        $result = $Collection->remove($poetryId);
        if(!$result) {
            $this->showError($Collection);
        }
        $this->showOk();
    }
}


