<?php
/**
 * 资讯模型类
 * @author ztt
 * @date 2018/03/12
 */
namespace app\models;

use app\components\ActiveRecord;

use Yii;
use app\models\Comment;
use app\models\CommentHelper;
use app\models\Tool;

class News extends ActiveRecord {
    public static $assocType = 1;  //资讯所属类别，0=评论类，1=资讯类
    public static $sourceType = [1=>'微信公众号', 2=>'哔哩哔哩', 3=>'新浪微博'];
    const BCOOKIE = 'buvid3=54C6FD14-0125-4E73-802F-9765FE33551B12637infoc;';

    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%news}}';
    }
    
    /**
     * 获取资讯页列表
     * @param int $page 当前页码
     * @param int $pagesize 每页显示记录数
     * @return array
     */
    public function getList($page = 1, $pagesize = 10) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($page) || !is_numeric($pagesize)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $andParams = ['is_delete'=>0];
        $result = $this->getListData('*', $page, $pagesize, $andParams, $order = 'pubdate desc,create_time desc');
        
        foreach($result['rows'] as $key=>$item) {
            $this->getNewsMixInfo($result['rows'][$key], $item);
        }
        $CommentHelper = new CommentHelper();
        $CommentHelper->addListIsPraise($result['rows'], $loginUid, 1);
        $result['page'] = $page;
        $result['page_size'] = $pagesize;
        return $result;
    }
    
    /**
     * 获取资讯详情
     * @param int $id
     * @return array
     */
    public function getInfo($id) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record && $Record->is_delete == 1) {
            $this->addError('', '-7:要访问的资讯不存在哦');
            return false;
        }
        //详情
        $records = [];
        $records['id'] = $Record->id;
        $records['title'] = $Record->title;
        $records['source_type'] = $Record->source_type;
        $records['source_from'] = '来自' . self::$sourceType[$Record->source_type];
        $records['author'] = json_decode($Record->author, true);
        $records['cover'] = Tool::startWith($Record->cover, 'http') ? $Record->cover : 'http:' . $Record->cover;
        $records['description'] = $Record->description;
        $records['content'] = $Record->source_type == 1 ? $this->concatHtml($Record->title, $Record->content, $Record->pubdate, $Record->source_type) : $Record->content;
        $records['video_info'] = $Record->video_info ? json_decode($Record->video_info, true) : new \stdClass();
        $records['pics'] = $Record->pics ? json_decode($Record->pics, true) : [];
        $records['source_url'] = $Record->source_url;
        $records['praise_cnt'] = $Record->praise_cnt;
        $records['pubdate'] = $Record->pubdate;
        $records['comment_cnt'] = $Record->comment_cnt;
        if($Record->video_info) {
            $records['video_info']['cookie'] = $Record->video_info ? self::BCOOKIE : '';
        }
        //第一页评论列表
        $CommentHelper = new CommentHelper();
        $CommentHelper->addIsPraise($records, $loginUid, 1);
        $Comment = new Comment();
        $list = $Comment->getCmtList($id, 1);
        $result = ['comment_info'=>$records, 'reply_list'=>$list, 'page_size'=>10];
        return $result;
    }
    
    /**
     * 拼接html
     * @param type $title
     * @param type $content
     * @param type $pubdate
     * @param type $sourceType
     */
    public function concatHtml($title, $content, $pubdate, $sourceType) {
        $html = '';
        $html .= '<h2>' . $title . '</h2>';
        $html .= '<span style="color:#b3b3b3">' . $pubdate . '</span> ';
        $html .= '<span style="color:#b3b3b3">来自' . self::$sourceType[$sourceType] . '</span>';
        $html .= $content;
        $replaceHtml = '<img width="100%"  src="' . Yii::$app->params['image_domain']  . '/system/default_video.png" />';
        $html = preg_replace('/<iframe.+?>/', "", $html);
        $html = preg_replace('/<\/iframe>/', $replaceHtml, $html);
        return $html;
    }
    
    /**
     * 获取资讯混合信息
     * @param type $record
     * @param type $item
     */
    public function getNewsMixInfo(&$record, $item) {
        $record['author'] = $item['author'] ? json_decode($item['author'], true) : new \stdClass();
        $record['video_info'] = $item['video_info'] ? json_decode($item['video_info'], true) : new \stdClass();
        $record['pics'] = $item['pics'] ? json_decode($item['pics'], true) : [];
        unset($record['content']);
        if($item['video_info']) {
            $record['video_info']['cookie'] = self::BCOOKIE;
        }
        if($item['author']) {
            $record['author']['name'] = '来自-' . $record['author']['name'];
        }
    }
}
