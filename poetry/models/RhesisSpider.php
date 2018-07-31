<?php
/**
 * 文章模型类
 * @author ztt
 * @date 2018/03/09
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\AuthorSpider;
use app\models\PoetrySpider;
use app\models\Tool;

class RhesisSpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%rhesis}}';
    }
    
    /**
     * 添加B站资讯信息
     * @param type $data
     */
    public function add($data) {
        $data = json_decode($data, true);
        if(empty($data)) {
           die(); 
        }
        $result = [];
        $result['poetry_title'] = $data['title'];
        $result['content'] = $data['content'];
        $PoetrySpider = new PoetrySpider();
        $author = $data['author'];
        if($author == '乐府诗集') {
            $author = '';
        }
        if($spaceIndex = mb_strpos($author, ' 撰') !== false || $spaceIndex = mb_strpos($author, ' 编') !== false) {
            $author = mb_substr($author, 0, $spaceIndex);
        }
        if(mb_strpos($author, '无名氏' !== false)) {
            $author = '';
        }
        if($author == '北宋·张载') {
            $author = '张载';
        }
        if($author == '佚名') {
            $author = '';
        }
        $PoetrySpiderRecord = $PoetrySpider->findByCondition(['title'=>$data['title'], 'author_name'=>$data['author']])->one();
        if(!$PoetrySpiderRecord) {
            return false;
        }
        $this->addToTb($result, $PoetrySpiderRecord->id);
    }
    
    /**
     * 保存数据入表
     * @param type $params
     * @return type
     */
    public function addToTb($params, $poetryId) {
        $sql = "insert into ".$this->tableName()
                . "(poetry_id,poetry_title,content,create_time,update_time) "
                . "values(:poetry_id,:poetry_title,:content,unix_timestamp(now()),unix_timestamp(now()))"
                . "on duplicate key update poetry_title=values(poetry_title),content=values(content),create_time=values(create_time),update_time=values(update_time)";
		$bindParams = array(
                        ":poetry_title"	  => $params['poetry_title'],
                        ":content"        => $params['content'],
                        ":poetry_id"      => $poetryId
                );	
        $conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql, $bindParams);
        $cmd->execute();
    }
    
}
