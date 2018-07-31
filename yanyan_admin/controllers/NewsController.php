<?php
/**
 * 资讯控制器类
 * @author ztt
 * @date 2018/04/02
 */
namespace app\controllers;

use app\components\Controller;
use app\models\News;

class NewsController extends Controller {
    
    /**
     * 渲染资讯列表
     * @return 
     */
    public function actionList() {
        $params = [];
        $params['title'] = $this->getParam('title');
        $params['source_type'] = $this->getParam('source_type');
        $params['description'] = $this->getParam('description');
        $params['author'] = $this->getParam('author');
        $page = $this->getParam('page', 1);
        $record = 20;
        $News = new News();
        $result = $News->getList($params, $page, $record);
        return $this->render('list', $result, $params);
    }
  
    /**
     * 获取影视剧详情
     * @return void
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $News = new News();
        $result = $News->getInfo($id);
        return $this->render('info', $result);
    }
    
    
}

