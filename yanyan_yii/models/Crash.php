<?php
/**
 * 评论模型类
 * @author ztt
 * @date 2017/11/9
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\Tool;
use app\models\Praise;
use app\models\Safety;
use app\models\Attachment;
use app\models\Cmd;
use app\models\CmdHelper;
use app\models\Film;
use app\models\User;

class Crash extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%crash_info}}';
    }
    
    /**
     * 发表评论,已加入黑名单的用户不能评论把自己加入黑名单的用户的评论（包括评论内回复其他人）
     * @param int $uid 评论人用户ID
     * @param string $comment 评论内容
     * @param int $filmId 评论电影ID，如果$filmId有值则$ctype必为1
     * @param int $ctype 类型，1=评论2=提问
     * @param int $mcid 主评论ID
     * @param int $cmtId 回复的评论ID
     * @param string $pic1 图片1，没有可以不传也可以传空字符串,base64字符串
     * @param string $pic2 图片2，没有可以不传也可以传空字符串,base64字符串
     * @param string $pic3 图片3，没有可以不传也可以传空字符串,base64字符串
     * @return mixed array=评论成功
     */
    public function add($source, $ex) {
        $this->source = $source;
        $this->info = $ex;
        return $this->save();
    }
    
    /**
     * 获取崩溃列表
     * @param type $page
     * @param type $record
     * @return type
     */
    public function getlist($page = 0, $record = 10) {
        $result = $this->getListData();
        return $result['rows'];
    }
    
    

    
}

