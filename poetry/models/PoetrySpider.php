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
use app\models\Tool;
use app\models\TagSpider;

class PoetrySpider extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%poetry}}';
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
        $result = [];
        $result['title'] = Tool::trimMarkAndAnno($data['title'], false, ' ');
        $content = $data['content'];
        $result['content'] = preg_replace('/(<\/?strong.*?>)/', '', $content);
        $translation = $data['translation'];
        $result['translation'] = preg_replace('/(<\/?strong.*?>)/', '', $translation);
        
        // 标签
        $tagArr = $data['tag'];
        $tagIds = [];
        $TagSpider = new TagSpider();
        foreach($tagArr as $key=>$item) {
            if(mb_strpos($item, '小学') !== false) {
                $TagRecord = $TagSpider->findByCondition(['type'=>1, 'title'=>'小学'])->one();
                $tagIds[] = $TagRecord->id;
            }else if(mb_strpos($item, '初中') !== false) {
                $TagRecord = $TagSpider->findByCondition(['type'=>1, 'title'=>'初中'])->one();
                $tagIds[] = $TagRecord->id;
            }else if(mb_strpos($item, '高中') !== false) {
                $TagRecord = $TagSpider->findByCondition(['type'=>1, 'title'=>'高中'])->one();
                $tagIds[] = $TagRecord->id;
            }else if(mb_strpos($item, '唐诗') !== false) {
                $TagRecord = $TagSpider->findByCondition(['type'=>1, 'title'=>'唐诗三百首'])->one();
                $tagIds[] = $TagRecord->id;
            }else if(mb_strpos($item, '宋词') !== false) {
                $TagRecord = $TagSpider->findByCondition(['type'=>1, 'title'=>'宋词三百首'])->one();
                $tagIds[] = $TagRecord->id;
            }else if(mb_strpos($item, '古诗') != false) {
                $TagRecord = $TagSpider->findByCondition(['type'=>1, 'title'=>'古诗三百首'])->one();
                $tagIds[] = $TagRecord->id;
            }else {
                $TagSpider = new TagSpider();
                $TagRecord = $TagSpider->findByCondition(['type'=>1, 'title'=>$item])->one();
                if(!$TagRecord) {
                    $TagSpider->type = 1;
                    $TagSpider->title = $item;
                    $TagSpider->save();
                    $tagIds[] = $TagSpider->id;
                }else {
                    $tagIds[] = $TagRecord->id;
                }
            }
        }
        
        // 注解
        $annotation = $data['annotation'];
        $annotationIndex = mb_strpos($annotation, '参考资料');
        if($annotationIndex !== false) {
            $result['annotation'] = mb_substr($annotation, 0, $annotationIndex);
        }else {
            $result['annotation'] = $annotation;
        }
        $annotation = $result['annotation'];
        $result['$annotation'] = preg_replace('/(<\/?strong.*?>)/', '', $annotation);

        // 赏析
        $appreciation = $data['appreciation'];
        $appreciationIndex = mb_strpos($appreciation, '译赏内容整理自网络');
        if($appreciationIndex !== false) {
            $appreciation = mb_substr($appreciation, 0, $appreciationIndex);
        }else {
            $appreciation = $appreciation;
        }

        $appreciationCKIndex = mb_strpos($appreciation, '参考资料');
        if($appreciationCKIndex !== false) {
            $result['appreciation'] = mb_substr($appreciation, 0, $appreciationCKIndex);
        }else {
            $result['appreciation'] = $appreciation;
        }
        
        $appreciation = $result['appreciation'];
        $result['appreciation'] = preg_replace('/(<\/?strong.*?>)/', '', $appreciation);
        
        $authorDesc = '';
        if($data['author_info']['author_desc']) {
            $authorDesc = $data['author_info']['author_desc'];
            $arrowIndex = mb_strpos($authorDesc, '►');
            if($arrowIndex !== false) {
                $authorDesc = mb_substr($authorDesc, 0, $arrowIndex);
            }
            $authorDesc = preg_replace('/(<\/?strong.*?>)/', '', $authorDesc);
        }
        
        $authorId = 0;
        if($data['author']) {
            $AuthorSpider = new AuthorSpider();
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
            $data['author'] = $author;
            if($author) {
                 $authorId = $AuthorSpider->add($data['author'], $data['year'], $data['author_info']['author_avatar'], $authorDesc);
            }
        }
        $result['year'] = $data['year'];
        $result['author_id'] = $authorId;
        $result['author_name'] = $data['author'];
        $result['title_space'] = Tool::scwsWord($result['title']) ? implode(' ', Tool::scwsWord($result['title'])) : $result['title'];
        $result['content_space'] = Tool::scwsWord($result['content']) ? implode(' ', Tool::scwsWord($result['content'])) : $result['content'];
        $poetryId = $this->addToTb($result);
        if($poetryId) {
            $TagPoetrySpider = new TagPoetrySpider();
            $TagPoetrySpider->add($tagIds, $poetryId);
        }
    }
    
    /**
     * 保存数据入表
     * @param type $params
     * @return type
     */
    public function addToTb($params) {
        $sql = "insert into ".$this->tableName()
                . "(title,title_space,content,content_space,author_id,author_name,year,translation,annotation,appreciation,create_time,update_time) "
                . "values(:title,:title_space,:content,:content_space,:author_id,:author_name,:year,:translation,:annotation,:appreciation,unix_timestamp(now()),unix_timestamp(now())) "
                . "on duplicate key update author_name=values(author_name),year=values(year),content=values(content),content_space=values(content_space),translation=values(translation),annotation=values(annotation),appreciation=values(appreciation),update_time=values(update_time)";
		$bindParams = array(
                        ":title"	  => $params['title'],
                        ":title_space"	  => $params['title_space'],
                        ":content"        => $params['content'],
                        ":content_space"  => $params['content_space'],
                        ":author_id"	  => $params['author_id'],
                        ":author_name"	  => $params['author_name'],
                        ":year"           => $params['year'],
                        ":translation"	  => $params['translation'],
                        ":annotation"	  => $params['annotation'],
                        ":appreciation"	  => $params['appreciation']
                );	
		$conn = Yii::$app->db;
        $cmd = $conn->createCommand($sql, $bindParams);
        if($cmd->execute()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        }else {
            $Record = $this->findByCondition(['author_id'=>$params['author_id'], 'title'=>$params['title']])->one();
            if(!$Record) {
               return false;
            }
            return $Record->id;
        }
    }
    
}
