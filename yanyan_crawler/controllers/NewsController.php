<?php
/**
 * 文章操作控制器
 * @author ztt
 * @date 2018/03/09
 */

namespace app\controllers;

use app\components\Controller;
use app\models\News;

class NewsController extends Controller {
    
    /**
     * 添加资讯信息
     * @return void
     */
    public function actionAdd() {
        $data = $_POST['data'];
        $News = new News();
        $result = $News->add($data);
        if(!$result) {
            $this->showError($News);
        }
        $this->showOk();
    }
}
