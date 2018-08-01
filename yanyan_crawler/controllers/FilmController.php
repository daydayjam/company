<?php
/**
 * 影视剧控制器类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Film;

class FilmController extends Controller {
    
    /**
     * 影视剧入库
     * @return void
     */
    public function actionSavefilm() {
        $data = $_POST['data'];
        $Film = new Film();
        $result = $Film->add($data);
        if(!$result) {
            $this->showError($Film);
        }
        $this->showOk();
    }
    
    
}

