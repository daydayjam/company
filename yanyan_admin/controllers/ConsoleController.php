<?php
/**
 * 首页控制器类
 * @author ztt
 * @date 2018/01/08
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Index;
use app\models\User;
use app\models\Comment;
use app\models\Chatroom;
use app\models\Feedback;
use app\models\Report;
use app\models\Film;
use app\models\Actor;
use app\models\Motto;

class ConsoleController extends Controller {
    
    /**
     * 渲染首页
     * @return 
     */
    public function actionIndex() {
        $Index = new Index();
        $result = [
            'user_cnt'    =>$Index->getCnt(new User),
            'cmt_cnt'     =>$Index->getCnt(new Comment),
//            'room_cnt'    =>$Index->getCnt(new Chatroom, 0),
            'feedback_cnt'=>$Index->getCnt(new Feedback),
            'report_cnt'  =>$Index->getCnt(new Report),
            'film_cnt'    =>$Index->getCnt(new Film, 0),
//            'actor_cnt'   =>$Index->getCnt(new Actor, 0),
            'motto_cnt'   =>$Index->getCnt(new Motto, 0)
        ];
        $params = [];
        $params['ac'] = 'console';
        $params['op'] = 'index';
        $params['ac_name'] = '控制台';
        $params['op_name'] = '首页';
        return $this->render('index', $result, $params);
    }
    
    
    
}

