<?php
/**
 * 用户控制器
 * @date 2018/0605
 * @author ztt
 */

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Userdefined;

class UserdefinedController extends Controller {
    /**
     * 创建文档
     * @return void
     */
    public function actionAdd() {
        $from = $this->getParam('from', 1);
        $title = $this->getParam('title');
        $content = $this->getParam('content');
        $Userdefined = new Userdefined();
        $result = $Userdefined->add($title, $content, $from);
        if(!$result) {
            $this->showError($Userdefined);
        }
        $this->showOk($result);
    }
    
    /**
     * 编辑文档
     * @return void
     */
    public function actionEdit() {
        $id = $this->getParam('id');
        $title = $this->getParam('title');
        $content = $this->getParam('content');
        $Userdefined = new Userdefined();
        $result = $Userdefined->edit($id, $title, $content);
        if(!$result) {
            $this->showError($Userdefined);
        }
        $this->showOk();
    }
    
    /**
     * 获取自定义文档列表
     * @return void
     */
    public function actionList() {
        $page = $this->getParam('page', Yii::$app->params['page']);
        $pagesize = $this->getParam('pagesize', Yii::$app->params['pagesize']);
        $Userdefined = new Userdefined();
        $result = $Userdefined->getList($page, $pagesize);
        if($result == false) {
            $this->showError($Userdefined);
        }
        $this->showOk($result);
    }
    
    /**
     * 删除自定义文档
     * @return void
     */
    public function actionRemove() {
        $id = $this->getParam('id');
        $Userdefined = new Userdefined();
        $result = $Userdefined->remove($id);
        if(!$result) {
            $this->showError($Userdefined);
        }
        $this->showOk();
    }
    
    
}


