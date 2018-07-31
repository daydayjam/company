<?php

/**
 * 诗词操作类
 * @date 2018/06/06
 * @author ztt
 */
namespace app\models;
use Yii;
use app\models\Poetry;
use app\models\Author;
use yii\db\Query;

class Helper {
    
    /**
     * 整合诗文详情到列表中
     * @param array $list 需要整合诗文详情的列表
     */
    public static function mergePoetryInfoToList(&$list) {
        foreach($list as $key=>$value) {
            $list[$key]['poetry_title']   = '';
            $list[$key]['poetry_content'] = '';
            $list[$key]['poetry_year']    = '';
            $list[$key]['poetry_author']  = '';
            $Poetry = new Poetry();
            $Record = $Poetry->findOne($value['poetry_id']);
            if($Record) {
                $list[$key]['poetry_title']   = $Record->title;
                $list[$key]['poetry_content'] = preg_replace('/\n|\r/', '', $Record->content);
                self::removeItemN($list[$key], 'poetry_content');
                $year = $Record->year;
                if($index = mb_strpos($Record->year, '代') !== false && !in_array($Record->year, ['现代', '五代'])) {
                    $year = mb_substr($Record->year, 0, $index);
                }
                $list[$key]['poetry_year']    = $year;
                $list[$key]['poetry_author']  = $Record->author_name == '' ? '佚名' : $Record->author_name;
            }
        }
    }
    
    /**
     * 获取最新文档编号
     * @return int 文档编号
     */
    public static function getNumber() {
        $loginUserId = Cache::hget('user_id');
        $result = (new Query())->select(['number'])
                                ->from(Userdefined::tableName())
                                ->where(['user_id'=>$loginUserId])
                                ->orderBy('number desc')
                                ->limit(1)
                                ->one();
        return $result['number'];
    }
    
    /**
     * 移除\n
     * @param array $list
     */
    public static function removeN(&$list) {
        foreach($list as $key=>$value) {
            $content = $value['content'];
            $content = Tool::mb_ltrim($content);
            $index = [];
            $len = mb_strlen($content);
            $index[] = mb_strpos($content, '；') !== false ? mb_strpos($content, '；') : $len;
            $index[] = mb_strpos($content, '。') !== false ? mb_strpos($content, '。') : $len;
            $index[] = mb_strpos($content, '？') !== false ? mb_strpos($content, '？') : $len;
            $index[] = mb_strpos($content, '！') !== false ? mb_strpos($content, '！') : $len;
            $contentIndex = min($index);
            $content = preg_replace('/\n|\r/', '', $content);
            
            $list[$key]['content'] = $contentIndex !== false ? mb_substr($content, 0, $contentIndex+1) : $content;
        }
    }
    
    public static function removeItemN(&$item, $field = 'content') {
        $content = $item[$field];
        $content = Tool::mb_ltrim($content);
        $index = [];
        $len = mb_strlen($content);
        $index[] = mb_strpos($content, '；') !== false ? mb_strpos($content, '；') : $len;
        $index[] = mb_strpos($content, '。') !== false ? mb_strpos($content, '。') : $len;
        $index[] = mb_strpos($content, '？') !== false ? mb_strpos($content, '？') : $len;
        $index[] = mb_strpos($content, '！') !== false ? mb_strpos($content, '！') : $len;
        $contentIndex = min($index);
        $content = preg_replace('/\n|\r/', '', $content);
        
        $item[$field] = $contentIndex !== false ? mb_substr($content, 0, $contentIndex+1) : $content;
    }
    
    /**
     * 去除列表代
     * @param type $list
     */
    public static function removeListDai(&$list) {
        foreach($list as $key=>$value) {
            if($index = mb_strpos($value['year'], '代') !== false && !in_array($value['year'], ['现代', '五代'])) {
                $list[$key]['year'] = mb_substr($value['year'], 0, $index);
            }
        }
    }
    
    /**
     * 去除代
     * @param type $item
     */
    public static function removeDai(&$item) {
        if($index = mb_strpos($item['year'], '代') !== false && !in_array($item['year'], ['现代', '五代'])) {
            $item['year'] = mb_substr($item['year'], 0, $index);
        }
    }
    
    /**
     * 去除列表代
     * @param type $list
     */
    public static function addEmptyAuthorList(&$list, $fieldName = 'author') {
        foreach($list as $key=>$value) {
            if($value[$fieldName] == '') {
                $list[$key][$fieldName] = '佚名';
            }
        }
    }
    
     /**
     * 去除代
     * @param type $item
     */
    public static function addEmptyAuthorItem(&$item, $fieldName = 'author') {
        if($item[$fieldName] == '') {
            $item[$fieldName] = '佚名';
        }
    }
    
    public static function isAuthor($author) {
        $Author = new Author();
        $sql = 'select count(*) as count from ' .$Author->tableName() . ' where name=' . $author;
        $result = $Author->findBySql($sql)->one();
        return $result['count'];
    }
    
    /**
     * 整合用户信息到列表中
     * @param type $list
     */
    public static function mergeUserInfoToList(&$list) {
        foreach($list as $key=>$item) {
            $UserRecord = User::findOne($item['user_id']);
            if(!$UserRecord) {
                $list[$key]['user_nickname'] = '';
                $list[$key]['user_avatar'] = '';
            }else {
                $list[$key]['user_nickname'] = $UserRecord->nickname;
                $list[$key]['user_avatar'] = $UserRecord->avatar;
            }
        }
    }
    
    /**
     * 整合用户信息
     * @param type $item
     */
    public static function mergeUserInfoToItem(&$item) {
        $UserRecord = User::findOne($item['user_id']);
        if(!$UserRecord) {
            $item['user_nickname'] = '';
            $item['user_avatar'] = '';
        }else {
            $item['user_nickname'] = $UserRecord->nickname;
            $item['user_avatar'] = $UserRecord->avatar;
        }
    }
   
    
    /**
     * 获取排名列表
     * @param string $limit
     * @return string
     */
    public function getRankSql($tbName, $limit = '') {
        $sql = 'SELECT '
                    . ' keep.user_id, '
                    . ' keep.user_keep_time, '
                    . ' CASE '
                          . ' WHEN @rowtotal = keep.user_keep_time THEN '
                              . ' @rownum '
                          . ' WHEN @rowtotal := keep.user_keep_time THEN '
                              . ' @rownum :=@rownum + 1 '
                          . ' WHEN @rowtotal = 0 THEN'
                              . ' @rownum :=@rownum + 1 '
                   . ' END AS user_rank '
             . ' FROM '
                . ' ( '
                    . ' SELECT '
                        . 'user_id, '
                        . 'sum(keep_time) as user_keep_time '
                    . ' FROM '
                        . $tbName
                    . ' GROUP BY '
                        . ' user_id '
                    . ' ORDER BY '
                        . ' user_keep_time DESC '
                    . $limit
                . ' ) AS keep, '
                . ' (SELECT @rownum := 0 ,@rowtotal := NULL) r';
        return $sql;
    }
    
    /**
     * 增加是否已背送
     * @param type $item
     */
    public static function mergeIsReciteToItem(&$item) {
        $loginUserId = Cache::hget('user_id') ? Cache::hget('user_id') : 0;
        $Recite = new Recite();
        $sql = 'select status from ' . $Recite->tableName() . ' where user_id=' . $loginUserId . ' and poetry_id=' . $item['id'];
        $ReciteRecord = $Recite->findBySql($sql)->one();
        if(!$ReciteRecord) {
            $item['is_recite'] = Yii::$app->params['code']['recite_status']['unrecite'];
        }else {
            $item['is_recite'] = $ReciteRecord->status;
        }
    }
    
    /**
     * 整合相关推荐列表到诗文详情
     * @param array 
     */
    public static function mergeRecommendToItem(&$item) {
        // 诗名、作者、首句        
        $Poetry = new Poetry();
        $sql = 'SELECT t1.id,t1.title,t1.content,t1.year,t1.author_name as author, t2.tag_id FROM ' . Poetry::tableName() . ' as t1 left join '  . TagPoetry::tableName() . ' as t2 on t2.poetry_id=t1.id where t2.tag_id in(select t3.tag_id from ' . TagPoetry::tableName() . ' as t3 where t3.poetry_id=' . $item['id'] . ') and t2.tag_id not in(2,3,4,5,6,7,8,9,10) and t1.id!='.$item['id'].' order by rand() limit 10';
        $result = $Poetry->findBySql($sql)->asArray()->all();
        if(!$result) {
            $sql = 'SELECT t1.id,t1.title,t1.content,t1.year,t1.author_name as author, t2.tag_id FROM ' . Poetry::tableName() . ' as t1 left join '  . TagPoetry::tableName() . ' as t2 on t2.poetry_id=t1.id where t2.tag_id in(select t3.tag_id from ' . TagPoetry::tableName() . ' as t3 where t3.poetry_id=' . $item['id'] . ') and t1.id!='.$item['id'].' order by rand() limit 10';
            $result = $Poetry->findBySql($sql)->asArray()->all();
        }
        Helper::removeN($result);
        Helper::removeListDai($result);
        Helper::addEmptyAuthorList($result);
        $item['recommand_list'] = $result;
    }
    
    
    
    
}

