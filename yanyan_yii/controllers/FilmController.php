<?php
/**
 * 影视剧控制器类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\Film;
use app\models\FilmSource;
use app\models\FilmFollow;
use app\models\ViewRecord;
use app\models\SearchTag;

class FilmController extends Controller {
    
    /**
     * 根据关键字获取影视剧列表
     * @return void
     */
    public function actionSearch() {
        $keyword = $this->getParam('keyword');
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 10);
        $Film = new Film();
        $result = $Film->searchByKeyword($keyword, $page, $pagesize);
        if($result === false) {
            $this->showError($Film);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取热门影视剧列表
     * @return void
     */
    public function actionHotlist() {
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 8);
        $Film = new Film();
        $result = $Film->getHotList($page, $pagesize);
        if($result === false) {
            $this->showError($Film);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取电影详情
     * @return void
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $Film = new Film();
        $result = $Film->getInfo($id);
        if(!$result) {
            $this->showError($Film);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取播放源信息
     * @return void
     */
    public function actionSource() {
        $id = $this->getParam('id');
        $num = $this->getParam('num', 1);
        $FilmSource = new FilmSource();
        $result = $FilmSource->getList($id, $num);
        if($result === false) {
            $this->showError($FilmSource);
        }
        $this->showOk($result);
    }
    
    /**
     * 更新追剧信息
     * @return void
     */
    public function actionUpdatefollow() {
        $id = $this->getParam('id');
        $num = $this->getParam('num', 1);
        $routeId = $this->getParam('route_id', 0);
        $FilmFollow = new FilmFollow();
        $result = $FilmFollow->updateFollow($id, $num, $routeId);
        if($result === false) {
            $this->showError($FilmFollow);
        }
        $this->showOk($result);
    }
    
    /**
     * 取消追剧
     * @return void
     */
    public function actionCancelfollow() {
        $id = $this->getParam('id');
        $FilmFollow = new FilmFollow();
        $result = $FilmFollow->cancelFollow($id);
        if(!$result) {
            $this->showError($FilmFollow);
        }
        $this->showOk();
    }
    
    /**
     * 获取最近更新的影视剧列表
     * @return void
     */
    public function actionNewlist() {
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 8);
        $Film = new Film();
        $result = $Film->getNewList($page, $pagesize);
        if($result === false) {
            $this->showError($Film);
        }
        $this->showOkIos($result);
    }
    
    /**
     * 获取搜索标签
     * @return void
     */
    public function actionSearchtag() {
        $SearchTag = new SearchTag();
        $result = $SearchTag->getList();
        if($result === false) {
            $this->showError($SearchTag);
        }
        $this->showOk($result);
    }
    
    /**
     * 根据搜索标签获取影视剧列表
     * @return void
     */
    public function actionSearchbytag() {
        $tags = $this->getParam('tags');
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 21);
        $Film = new Film();
        $result = $Film->searchByTag($tags, $page, $pagesize);
        if($result === false) {
            $this->showError($Film);
        }
        $this->showOk($result);
    }
    
}

