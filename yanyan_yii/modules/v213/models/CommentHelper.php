<?php
/**
 * 评论帮助模型类
 * @author ztt
 * @date 2017/11/24
 */
namespace app\models;

use Yii;

class CommentHelper {
    
    /**
     * 去除非正常用户数据
     * @param string $sql sql语句
     * @return str
     */
    public function removeAbnormal($sql) {
        $loginUid = Cache::hget('id');
        // 去黑名单
        if($loginUid) {
            $Black = new Black();
            $blacks = $Black->getBlacks($loginUid);
            if($blacks === false) {
                $error = $Black->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            if(!empty($blacks)) {
                $blacksStr = implode(',', $blacks);
                $sql .= ' and uid not in (' . $blacksStr . ')';
            }
        }
        // 去除被封号用户
        $User = new User();
        $outUserIds = $User->getOutUser();
        if($outUserIds === false) {
            $error = $User->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        if(!empty($outUserIds)) {
            $outUserIdsStr = implode(',', $outUserIds);
            $sql .= ' and uid not in (' . $outUserIdsStr . ')';
        }
        return $sql;
    }
    
    /**
     * 获取用户头像、昵称、签名（职业兴趣）
     * @param int $uid
     * @return array
     */
    public function getUserInfo($uid) {
        $result = [];
        $User = new User();
        $UserRecord = $User->findOne($uid);
        $result['nickname']  = '';
        $result['gender']    = 0;
        $result['avatar']    = '';
        $result['signature'] = '';
        if($UserRecord) {
            $result['nickname']  = $UserRecord->nickname;
            $result['gender']    = $UserRecord->gender;
            $result['avatar']    = Tool::connectPath(Yii::$app->params['image_domain'], $UserRecord->avatar);
            $result['signature'] = $UserRecord->signature;
            return $result;
        }
        return $result;
    }
    
    /**
     * 加上所有列表都会有的常规操作
     * @param type $records
     */
    public function addCommon(&$records) {
        $loginUid = Cache::hget('id');
        // 处理图片
        $this->addListDomain($records, 'pics');
        // 增加我是否点赞字段is_praise和是否满赞字段full_praise
        $this->addListIsPraise($records, $loginUid);
    }
    
    /**
     * 处理list里面的pics字符串，加上域名
     * @param array $list 列表
     * @param string $field 字段名
     * @return void
     */
    public function addDomain(&$item, $field) {
        if($item[$field]) {
            $picArr = json_decode($item[$field], true);
            foreach($picArr as $i=>$pic) {
                if(!Tool::startWith($pic['path'], 'http')) {
                   $picArr[$i]['path'] = Tool::connectPath(Yii::$app->params['image_domain'], $pic['path']);
                }
            }
            $item[$field] = $picArr;
        }
    }
    
    /**
     * 处理list里面的pics字符串，加上域名
     * @param array $list 列表
     * @param string $field 字段名
     * @return void
     */
    public function addListDomain(&$list, $field) {
        foreach($list as $key=>$value) {
            if(empty($value[$field])) {
                $list[$key][$field] = [];
                continue;
            }
            $picArr = json_decode($value[$field], true);
            foreach($picArr as $i=>$pic) {
                $picArr[$i]['path'] = Tool::connectPath(Yii::$app->params['image_domain'], $pic['path']);
            }
             $list[$key][$field] = $picArr;
        }
    }
    
    /**
     * 给评论加上我是否评论的字段
     * @param array $comment 评论记录
     * @param int $uid 需要判断是都点赞的用户ID
     * @param int $assocType 点赞内容类别，0=给评论点赞；1=给资讯点赞
     * @return void
     */
    public function addIsPraise(&$comment,$uid, $assocType = 0){
        $praiseRecord = [];
        if($uid > 0) {
            $Praise = new Praise();
            $praiseRecord = $Praise->findByCondition(['uid'=>$uid, 'comment_id'=>$comment['id'], 'assoc_type'=>$assocType])->one();
        }
        $comment['is_praise'] = Yii::$app->params['state_code']['praise_no'];
        $comment['full_praise'] = Yii::$app->params['state_code']['full_praise_no'];
        if($praiseRecord) {
            $comment['is_praise'] = Yii::$app->params['state_code']['praise_yes'];
            if($praiseRecord->cnt == Yii::$app->params['state_code']['full_praise_cnt']) {
                $comment['full_praise'] = Yii::$app->params['state_code']['full_praise_yes'];
            }
        }
    }
    
    /**
     * 给评论加上当前登录用户是否点赞及是否点赞满的字段
     * @param array $list 评论列表
     * @param int $uid 用户ID
     * @return void
     */
    public function addListIsPraise(&$list, $uid = 0, $assocType = 0) {
        $idArr = [];
        $praiseCntArr = [];
        if($uid > 0) {
            $Praise = new Praise();
            $sql = 'select comment_id,cnt from ' . $Praise->tableName() . ' where uid=:uid and assoc_type=:assoc_type';
            $idArrs = $Praise->findBySql($sql, [':uid'=>$uid, ':assoc_type'=>$assocType])->asArray()->all();
            foreach($idArrs as $item) {
                $idArr[] = $item['comment_id'];
                $praiseCntArr[$item['comment_id']] = $item['cnt'];
            }
        }
        foreach($list as $key=>$value) {
            $cmtId = $value['id'];
            $list[$key]['is_praise'] = Yii::$app->params['state_code']['praise_no'];
            $list[$key]['full_praise'] = Yii::$app->params['state_code']['full_praise_no'];
            if(in_array($cmtId, $idArr)) {
                $list[$key]['is_praise'] = Yii::$app->params['state_code']['praise_yes'];
                if($praiseCntArr[$cmtId] == Yii::$app->params['state_code']['full_praise_cnt']) {
                    $list[$key]['full_praise'] = Yii::$app->params['state_code']['full_praise_yes'];
                }
            }
        }
    }
    
    /**
     * 给评论列表加上剧集标题
     * @param array $comment 评论列表
     * @param int $filmId 影视剧ID
     * @param int $epNum 剧集编号
     * @return void
     */
    public function addListEpTitle(&$list){
        foreach($list as $key=>$value) {
            $list[$key]['ep_title'] = '';
            if($value['ep_num']) {
                $Episode = new Episode();
                $EpisodeRecord = $Episode->findByCondition(['assoc_id'=>$value['assoc_id'], 'num'=>$value['ep_num']])->one();
                if($EpisodeRecord) {
                    $list[$key]['ep_title'] = $EpisodeRecord->title;
                }
            }
        }
    }
    
    /**
     * 删除不需要的字段名
     * @param array $list 引用
     * @param array $fields 字段名
     */
    public function deleteField(&$list, $fields) {
        foreach($list as $key=>$value) {
            foreach($fields as $field) {
                unset($list[$key][$field]);
            }
        }
    }
    
    /**
     * 给评论配上影视剧信息
     * @param array $list 评论列表
     * @return void
     */
    public function mergeToFilm(&$item) {
        if($item['assoc_id'] > 0 && $item['assoc_type'] == 0) {
            $Film = new Film();
            $sql = "select id,kind,type,genre,title,cover,main_actor,year,area from " . $Film->tableName() . " where id=:id";
            $filmRecord = $Film->findBySql($sql, [':id'=>$item['assoc_id']])->asArray()->one();
            $filmRecord['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $filmRecord['cover'], Yii::$app->params['text_desc']['default_cover']);
            $filmRecord['year'] = $filmRecord['year'] != Yii::$app->params['state_code']['year_unknown'] ? (string)$filmRecord['year'] : Yii::$app->params['text_desc']['year_unknown'];
            $filmRecord['main_actor'] = $filmRecord['main_actor'] ? $filmRecord['main_actor'] : Yii::$app->params['text_desc']['no_main_actor'];
            
            $filmRecord['tag'] = $filmRecord['genre'] ? $filmRecord['genre'] : Yii::$app->params['text_desc']['film_kind'][$filmRecord['kind']];
            $filmRecord['area'] = !$filmRecord['area'] ? Yii::$app->params['text_desc']['area_unknown'] : $filmRecord['area'];
            unset($filmRecord['type']);
            unset($filmRecord['kind']);
            $item['film_info'] = $filmRecord;
        }
    }
    
    /**
     * 给评论配上影视剧信息
     * @param array $list 评论列表
     * @return void
     */
    public function mergeToListFilm(&$list) {
        $idArr = [];
        foreach($list as $value) {
            if($value['assoc_id'] > 0 && !in_array($value['assoc_id'], $idArr) && ($value['assoc_type'] == 0)) {
                $idArr[] = $value['assoc_id'];
            }
        }
        $filmList = [];
        $Film = new Film();
        $sql = "select id,kind,type,genre,title,cover,main_actor,year,area from " . $Film->tableName() . " where id=:id";
        foreach($idArr as $item) {
            $filmRecord = $Film->findBySql($sql, [':id'=>$item])->asArray()->one();
            $filmRecord['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $filmRecord['cover'], Yii::$app->params['text_desc']['default_cover']);
            $filmRecord['year'] = $filmRecord['year'] != Yii::$app->params['state_code']['year_unknown'] ? (string)$filmRecord['year'] : Yii::$app->params['text_desc']['year_unknown'];
            $filmRecord['main_actor'] = $filmRecord['main_actor'] ? $filmRecord['main_actor'] : Yii::$app->params['text_desc']['no_main_actor'];
            
            $filmRecord['tag'] = $filmRecord['genre'] ? $filmRecord['genre'] : Yii::$app->params['text_desc']['film_kind'][$filmRecord['kind']];
            $filmRecord['area'] = !$filmRecord['area'] ? Yii::$app->params['text_desc']['area_unknown'] : $filmRecord['area'];
            unset($filmRecord['kind']);
            unset($filmRecord['type']);
            $filmList[$item] = $filmRecord;
        }
        foreach($list as $m=>$cmt) {
            if(array_key_exists($cmt['assoc_id'], $filmList) && $filmList[$cmt['assoc_id']]) {
                $list[$m]['film_info'] = $filmList[$cmt['assoc_id']];
            }
        }
    }
    
    /**
     * 组合资讯信息
     * @param type $list
     */
    public function mergeToListNews(&$list) {
        $idArr = [];
        foreach($list as $value) {
            if($value['assoc_id'] > 0 && !in_array($value['assoc_id'], $idArr) && $value['assoc_type'] == 11) {
                $idArr[] = $value['assoc_id'];
            }
        }
        $newsList = [];
        $News = new News();
        $sql = "select * from ".$News->tableName()." where id=:id";
        foreach($idArr as $item) {
            $newsList[$item] = $News->findBySql($sql, [':id'=>$item])->asArray()->one();
            if($newsList[$item] && $newsList[$item]['is_delete'] != 1) {
                $News->getNewsMixInfo($newsList[$item], $newsList[$item]);
            } else {
                $newsList[$item] = new \stdClass();
            }
        }
        foreach($list as $m=>$cmt) {
            if(array_key_exists($cmt['assoc_id'], $newsList) && $newsList[$cmt['assoc_id']]) {
                $list[$m]['news_info'] = $newsList[$cmt['assoc_id']];
            }
        }
    }
    
    /**
     * 组合一项资讯信息
     * @param type $item
     */
    public function mergeToNews(&$item) {
        if($item['assoc_id'] > 0 && $item['assoc_type'] == 11) {
            $News = new News();
            $sql = "select * from ".$News->tableName()." where id=:id";
            $newsRecord = $News->findBySql($sql, [':id'=>$item['assoc_id']])->asArray()->one();
            if($newsRecord && $newsRecord['is_delete'] != 1) {
                $News->getNewsMixInfo($newsRecord, $newsRecord);
                $item['news_info'] = $newsRecord;
            } else {
                $item['news_info'] = new \stdClass();
            }
        }
    }
    
     /**
     * 将图片字符串处理成带域名的字符串返回
     * @param string $picJson 图片json字符串
     * @return array
     */
    public function dealPics($picJson){
        $picArr = json_decode($picJson, true);
        if(empty($picArr)) {
            $picArr = array();
        }else {
            for($i = 0; $i < count($picArr); $i++){
                $picArr[$i]['path'] = Yii::$app->params['image_domain'].$picArr[$i]['path'];
            }
        }
        return $picArr;
    }
    
    /**
     * 添加用户基本信息
     * @param type $cmtInfo
     */
    public function mergeToUser(&$cmtInfo) {
        $UserRecord = null;
        if($cmtInfo['uid'] > 0) {
            $User = new User();
            $UserRecord = $User->findOne($cmtInfo['uid']);
        }
        $cmtInfo['nickname'] = $UserRecord ? $UserRecord->nickname : '';
        $cmtInfo['avatar'] = $UserRecord ? Tool::connectPath(Yii::$app->params['image_domain'], $UserRecord->avatar, Yii::$app->params['text_desc']['default_avatar']) : Tool::connectPath(Yii::$app->params['image_domain'], Yii::$app->params['text_desc']['default_avatar']);
        $cmtInfo['gender'] = $UserRecord ? $UserRecord->gender : 0;
        $cmtInfo['ease_uid'] = $UserRecord ? $UserRecord->ease_uid : '';
        $cmtInfo['signature'] = $UserRecord ? $UserRecord->signature : '';
        $cmtInfo['freeze_time'] = $UserRecord ? $UserRecord->freeze_time : '';
        
        $ReplyUserRecord = null;
        if($cmtInfo['reply_uid'] > 0) {
            $User = new User();
            $ReplyUserRecord = $User->findOne($cmtInfo['reply_uid']);
        }
        $cmtInfo['reply_nick'] = $ReplyUserRecord ? $ReplyUserRecord->nickname : '';
        
        $ReplyCmtRecord = null;
        if($cmtInfo['comment_id']) {
            $Comment = new Comment();
            $ReplyCmtRecord = $Comment->findOne($cmtInfo['comment_id']);
        }
        $cmtInfo['reply_cmt'] = $ReplyCmtRecord ? $ReplyCmtRecord->comment : '';
        
        
    }
}