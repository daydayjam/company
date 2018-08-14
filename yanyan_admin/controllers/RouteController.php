<?php
/**
 * 用户控制器类
 * @author ztt
 * @date 2018/01/09
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Film;
use app\models\Actor;

class FilmController extends Controller {
    
    /**
     * 渲染用户列表
     * @return 
     */
    public function actionList() {
        $params = [];
        $params['id'] = $this->getParam('id');
        $params['title'] = $this->getParam('title');
        $params['kind'] = $this->getParam('kind');
        $params['area'] = $this->getParam('area');
        $params['genre'] = $this->getParam('genre');
        $params['is_hot'] = $this->getParam('is_hot');
        $params['year'] = $this->getParam('year');
        $page = $this->getParam('page', 1);
        $record = 20;
        $Film = new Film();
        $result = $Film->getList($params, $page, $record);
        return $this->render('list', $result, $params);
    }
    
    /**
     * 设置是否热门
     * @return void
     */
    public function actionSetishot() {
        $id = $this->getParam('id');
        $isHot = $this->getParam('is_hot');
        $Film = new Film();
        $result = $Film->setIsHot($id, $isHot);
        if(!$result) {
            $this->showError($Film);
        }
        $this->showOk();
    }
    
    /**
     * 获取影视剧详情
     * @return void
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $Film = new Film();
        $result = $Film->getInfo($id);
        return $this->render('info', $result);
    }
    
    /**
     * 更新影视剧信息
     * @return void
     */
    public function actionUpdate() {
        $params = [];
        $params['id'] = $this->getParam('id');
        $params['title'] = $this->getParam('title');
        $params['kind'] = $this->getParam('kind');
        $params['year'] = $this->getParam('year');
        $params['area'] = $this->getParam('area');
        $params['genre'] = $this->getParam('genre');
        $params['summary'] = $this->getParam('summary');
        $params['cover'] = $this->getParam('cover');
        $Film = new Film();
        $result = $Film->updateFilm($params);
        if(!$result) {
            $this->showError($Film);
        }
        $this->showOk();
    }
    
    /**
     * 删除影片
     * @return void
     */
    public function actionDel() {
        $id = $this->getParam('id');
        $Film = new Film();
        $result = $Film->delFilm($id);
        if(!$result) {
            $this->showError($Film);
        }
        $this->showOk();
    }
    
    /**
     * 添加影视剧
     * @return void
     */
    public function actionAdd() {
        return $this->render('add');
    }
    
    public function actionActor() {
        $params = [];
        $params['id'] = $this->getParam('id');
        $params['en_name'] = $this->getParam('en_name');
        $params['name'] = $this->getParam('name');
        $params['country'] = $this->getParam('country');
        $page = $this->getParam('page', 1);
        $record = 20;
        $Actor = new Actor();
        $params['ac_name'] = '添加影视剧';
        $params['op_name'] = '选择演职人员';
        $result = $Actor->getList($params, $page, $record);
        return $this->render('actor', $result, $params);
    }
    
    
    
    
}

