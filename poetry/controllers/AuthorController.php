<?php
/**
 * 诗词控制器
 * @date 2018/06/06
 * @author ztt
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Author;

class AuthorController extends Controller {
    
    /**
     * 获取作者列表
     * @return void
     */
    public function actionList() {
        $author = $this->getParam('author');
        $page = $this->getParam('page', Yii::$app->params['page']);
        $pagesize = $this->getParam('pagesize', Yii::$app->params['pagesize']);
        $Author = new Author();
        $result = $Author->getList($author, $page, $pagesize);
        if($result == false) {
            $this->showError($Author);
        }
        $this->showOk($result);
    }
    
}


