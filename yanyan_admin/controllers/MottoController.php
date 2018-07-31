<?php
/**
 * 演员控制器类
 * @author ztt
 * @date 2018/01/16
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Motto;

class MottoController extends Controller {
    
    /**
     * 渲染聊天室列表
     * @return 
     */
    public function actionList() {
        $params = [];
        $params['id'] = $this->getParam('id');
        $params['fname'] = $this->getParam('fname');
        $params['content'] = $this->getParam('content');
        $page = $this->getParam('page', 1);
        $record = 20;
        $Motto = new Motto();
        $result = $Motto->getList($params, $page, $record);
        return $this->render('list', $result, $params);
    }
    
    /**
     * 渲染聊天室创建界面
     * @return 
     */
    public function actionAdd() {
        $params = [];
        $params['fid'] = $this->getParam('fid');
        $params['fname'] = $this->getParam('fname');
        return $this->render('add', $params);
    }
    
    /**
     * 保存信息
     * @return void
     */
    public function actionSave() {
        $fid = $this->getParam('fid', 0);
        $content = $this->getParam('content');
        $Motto = new Motto();
        $result = $Motto->saveMotto($content, $fid);
        if(!$result) {
            $this->showError($Motto);
        }
        $this->showOk();
    }
    
    /**
     * 删除寄语
     * @return void
     */
    public function actionDel() {
        $ids = $this->getParam('ids');
        $Motto = new Motto();
        $result = $Motto->del($ids);
        if(!$result) {
            $this->showError($Motto);
        }
        $this->showOk();
    }
    
    
}

