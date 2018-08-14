<?php
/**
 * 资讯控制器类
 * @author ztt
 * @date 2018/03/12
 */

namespace app\controllers;

use app\components\Controller;
use app\models\News;
use app\models\Tool;

class NewsController extends Controller {
    
    /**
     * 热门页列表
     * @return void
     */
    public function actionList() {
        $page = $this->getParam('page', 1);
        $pagesize = $this->getParam('pagesize', 10);
        $newsId = $this->getParam('news_id', 0);
        $News = new News();
        $result = $News->getList($newsId, $page, $pagesize);
        if($result === false) {
            $this->showError($News);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取资讯详情
     * @return array
     */
    public function actionInfo() {
        $id = $this->getParam('id');
        $News = new News();
        $result = $News->getInfo($id);
        if(!$result) {
            $this->showError($News);
        }
        $this->showOk($result);
    }
    
    /**
     * 获取微博视频
     * @return void
     */
    public function actionGetwbvideo() {
        $url = $this->getParam('url');
        $result = Tool::phpCurl($url);
        $ret = preg_match('/\"stream_url\":\s*\"(.*)\"/', $result, $match);
        if(!$result || !isset($match[1])) {
            $url = Yii::$app->params['image_domain'] . '/system/' . 'not_found.mp4';
            $this->showOk($url);
        }
        $this->showOk($match[1]);
    }
}
