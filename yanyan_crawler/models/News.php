<?php
/**
 * 文章模型类
 * @author ztt
 * @date 2018/03/09
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\Video;

class News extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%news}}';
    }
    
    /**
     * 添加B站资讯信息
     * @param type $data
     */
    public function add($data) {
        $data = json_decode($data, true);
//        print_r($data);die;
        if(empty($data)) {
           die(); 
        }
        if(isset($data['source_type']) && $data['source_type'] == 2) {  //哔哩哔哩
            $data['cover'] = 'http:' . $data['cover'];
            $data['author'] = $data['author'] ? json_encode($data['author']) : '';
            $data['video_info'] = $data['video_info'] ? json_encode($data['video_info']) : '';
            $data['content'] = '';
            $data['pics'] = '';
        } else if(!isset($data['source_type'])) {   //微信公众号
            $data['source_type'] = 1;
            $data['title'] = $data['article_title'];
            $data['cover'] = $data['article_thumbnail'];
            $data['author'] = json_encode(['name'=>$data['article_author'],'avatar'=>$data['weixin_avatar']], true);
            $data['video_info'] = '';
            $data['description'] = str_replace('/max-width: \d+px;/', "", $data['article_brief']);
            $data['content'] = $data['article_content'];
            $data['source_url'] = $data['weixin_tmp_url'];
            $data['pubdate'] = date('Y-m-d', $data['article_publish_time']);
            $data['pics'] = '';
        } else if(isset($data['source_type']) && $data['source_type'] == 3) {
            if($data['video_info'] && $data['video_info']['type'] != 'video') {
                return;
            }
            $data['author'] = $data['author'] ? json_encode($data['author']) : '';
            $data['cover'] = $data['cover'] ? $data['cover'] : '';
            if($data['video_info']['url'] && empty($data['pics'])) {
                $data['video_info']['duration'] = Video::getVideoDuration($data['video_info']['url']);
                $data['video_info']['url'] = Yii::$app->params['domain'] . '/news/getwbvideo?url=' . $data['source_url'];
            } else {
                $data['video_info']['url'] = '';
            }
            $data['video_info'] = $data['video_info']['url'] ? json_encode($data['video_info']) : '';
            $data['pics'] = $data['pics'] ? json_encode($data['pics']) : '';
        }
        if($data['pubdate'] >= date('Y-m-d')) {
            if($data['author']) {
                $this->addToTb($data);
            }
        }
        
    }
    
    /**
     * 保存数据入表
     * @param type $params
     * @return type
     */
    public function addToTb($params) {
        $sql = "insert into ".$this->tableName()
                . "(title,source_type,author,cover,description,content,video_info,source_url,pics,pubdate,create_time) "
                . "values(:title,:source_type,:author,:cover,:description,:content,:video_info,:source_url,:pics,:pubdate,now()) "
                . "on duplicate key update author=values(author),cover=values(cover),description=values(description),content=values(content),"
                . "video_info=values(video_info),source_url=values(source_url),pics=values(pics),pubdate=values(pubdate)";
  	$bindParams = array(
                        ":title"	  =>$params['title'],
                        ":source_type"    =>$params['source_type'],
                        ":author"	  =>$params['author'],
                        ":cover"	  =>$params['cover'],
                        ":description"	  =>$params['description'],
                        ":content"	  =>$params['content'],
                        ":video_info"	  =>$params['video_info'],
                        ":source_url"	  =>$params['source_url'],
                        ":pics"           =>$params['pics'],
                        ":pubdate"	  =>$params['pubdate']
                );	
  	$conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql, $bindParams);
        return $cmd->execute();
    }
    
}
