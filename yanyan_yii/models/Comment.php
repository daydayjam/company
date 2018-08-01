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
use app\models\News;
use app\models\Area;

class Comment extends ActiveRecord {
    const CTYPE = ['comment'=>1, 'question'=>2];
    const SINGLE_PRAISE_LIMIT = 10;    //单人点赞上限
    const ASSOCTYPE = ['film'=>0, 'news'=>1, 'news_trans'=>11]; //关联业务，0=影视剧评论；1=资讯评论；11=资讯评论的转发
    private $_Transaction = null;  //事务处理对象


    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%comment}}';
    }
    
    /**
     * 获取影视剧评论列表
     * @param int $filmId 影视剧ID
     * @param int $page 当前页码
     * @param int $pageSize 每页显示条目数
     * @return array
     */
    public function getListByFilm($filmId, $page = 1, $pagesize = 10) {
        $sql = 'select * from ' . $this->tableName() . ' where assoc_id=:assoc_id and assoc_type=:assoc_type and mcid=0 and is_delete!=1';
        // 去黑名单 去除被封号用户
        $CommentHelper = new CommentHelper();
        $sql = $CommentHelper->removeAbnormal($sql);
        if($sql === false) {
            $this->addError('', '-11:服务器数据异常1');
            return false;
        }
        $offset = ($page - 1) * $pagesize;
        $sqlOffset = $sql . ' order by create_time desc limit ' . $offset . ', ' . $pagesize;
        // （头像、昵称、签名）、发表时间、评论内容、被评论次数、被点赞次数、图片列表、
        
        $records = $this->findBySql($sqlOffset, [':assoc_id'=>$filmId, ':assoc_type'=>Yii::$app->params['state_code']['comment_film']])
                        ->asArray()
                        ->all();
        foreach($records as $key=>$item) {
            $records[$key]['create_time'] = strtotime($item['create_time']);
            $records[$key]['ep_title'] = $item['ep_num'] ? '第' . $item['ep_num'] . '集' : '';
            $userInfo = $CommentHelper->getUserInfo($item['uid']);
            if($userInfo === false) {
                $this->addError('', '-11:服务器数据异常2');
                return false;
            }
            $records[$key] = array_merge($records[$key], $userInfo);
        }
        // 处理图片 增加我是否点赞字段is_praise和是否满赞字段full_praise 等所有列表都有的常规操作
        if($records) {
            $CommentHelper->addCommon($records);
            $CommentHelper->deleteField($records, ['status','mcid','comment_id','reply_uid','assoc_type','is_agree']);
        }
       
        $fromIndex = strpos($sql, 'from');
        $sql = 'select count(1) as cnt ' . substr($sql, $fromIndex);
        $countRecord = $this->findBySql($sql, [':assoc_id'=>$filmId, 'assoc_type'=>Yii::$app->params['state_code']['comment_film']])->asArray()->one();
        $result = [
            'page'     => (int)$page,
            'pagesize' => (int)$pagesize,
            'total'    => $countRecord['cnt'],
            'rows'     => $records
        ];
        return $result;
    }
    
    /**
     * 发表评论,已加入黑名单的用户不能评论把自己加入黑名单的用户的评论（包括评论内回复其他人）
     * @param int $uid 评论人用户ID
     * @param string $comment 评论内容
     * @param int $filmId 评论电影ID，如果$filmId有值则$ctype必为1
     * @param int $ctype 类型，1=评论2=提问
     * @param int $mcid 主评论ID
     * @param int $cmtId 回复的评论ID
     * @param int $from $from=0 回复转发的资讯评论，$from=1 回复资讯的评论
     * @param string $pic1 图片1，没有可以不传也可以传空字符串,base64字符串
     * @param string $pic2 图片2，没有可以不传也可以传空字符串,base64字符串
     * @param string $pic3 图片3，没有可以不传也可以传空字符串,base64字符串
     * @return mixed array=评论成功
     */
    public function add($comment, $filmId = 0, $epNum = 0, $ctype = 1, $mcid = 0, $cmtId = 0, $assoctype = 0, $from = 0, $picArr = []) {
        $loginUid = Cache::hget('id') ? Cache::hget('id') : 0;
        //参数判断
        if(!in_array($ctype, self::CTYPE)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if($assoctype == 0 && $ctype == 1 && !is_numeric($filmId)) {
            $this->addError('', '-4:请选择相关影视剧');
            return false;
        }
        if(($assoctype == 0 && $filmId > 0) && !is_numeric($epNum)) {
            $this->addError('', '-4:请确认剧集信息无误');
            return false;
        }
        if(!is_numeric($mcid) || !is_numeric($cmtId)) {
            $this->addError('', '-4:请确认评论内容无误');
            return false;
        }
        if(empty($comment)) {
            $this->addError('', '-3:请输入您的评论');
            return false;
        }
        //防止重复
        //限制5分钟仅可访问10次
        $key = 'COMMENT_ADD_'.$loginUid.'_'.$assoctype.'_'.$ctype.'_'.$filmId.'_'.$mcid.'_'.$cmtId;
        if(Safety::ipTimes($key, 60) > 10) {
            $this->addError('', '-6:太快啦，请稍后再试哦');
            return false;
        }
        //加缓存限制，确保不会连续发送两条相同数据
        if(Cache::get($key)) {//缓存如果存在，则说明连续发送
            $this->addError('', '-6:请稍后再试');
            return false;
        }
        Cache::setex($key, 2, 1);
        
        //图片处理
        $imgArr = [];
        foreach($picArr as $key=>$pic) {
            if(!empty($pic)) {
                $Attachment = new Attachment();
                $uploadResult = $Attachment->uploadBase64Img($pic, 'normal');
                if(!$uploadResult) {
                    $error = $Attachment->getCodeError();
                    $this->addError('', $error['code'].':'.$error['msg']);
                    return false;
                }
                $imgArr[$key]['path'] = $uploadResult['path'];
                $imgArr[$key]['width'] = $uploadResult['width'];
                $imgArr[$key]['height'] = $uploadResult['height'];
            }
        }
        $pics = empty($imgArr) ? '' : json_encode($imgArr);
        $this->beginTransaction();
        //提问模块
        $addResult = [];
        if($ctype == self::CTYPE['question']) { 
            if(!$addResult = $this->addQuesCmt($comment, $pics, $mcid, $cmtId)) {
                $this->rollback();
                $error = $this->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
        } else {
            //评论模块
            if($assoctype == self::ASSOCTYPE['film']) { //影视剧相关
                if(!$addResult = $this->addFilmCmt($filmId, $comment, $pics, $epNum, $mcid, $cmtId)) {
                    $this->rollback();
                    $error = $this->getCodeError();
                    $this->addError('', $error['code'] . ':' . $error['msg']);
                    return false;
                }
            } else {    //资讯模块
                if(!$addResult = $this->addNewsCmt($filmId, $comment, $assoctype, $cmtId, $from)) {
                    $this->rollback();
                    $error = $this->getCodeError();
                    $this->addError('', $error['code'] . ':' . $error['msg']);
                    return false;
                }
            }
        }
        
        $newCmtInfo = $this->getNewCmtInfo($addResult['new_cmtid']);
        $replyUid = $newCmtInfo['reply_uid'];
        $replyNick = $newCmtInfo['reply_nick'];
        $replyGender = $newCmtInfo['reply_gender'];
        $replyEaseUid = $newCmtInfo['reply_ease_uid'];
        
        //回复主评论、回复转发的评论不用加@
        if($cmtId == $mcid || (isset($addResult['cmt_info']) && $from == 0 && $addResult['cmt_info']['assoc_type'] == 11)) {
            $newCmtInfo['reply_uid'] = 0;
            $newCmtInfo['reply_nick'] = '';
            $newCmtInfo['reply_gender'] = 0;
            $newCmtInfo['reply_ease_uid'] = '';
        }
        //发送透传
        //如果自己评论自己或者评论的是资讯则不发送透传
        if($loginUid == $replyUid || ($assoctype != self::ASSOCTYPE['film'] && $cmtId == 0)) {
            $this->commit();
            return $newCmtInfo;
        }
        //如果是主评论，则返回新评论信息的同时返回寄语内容
        $sourceType = 0;
        $videoInfo = [];
        if($assoctype == 0 && !$mcid) {
            $Motto = new Motto();
            $result['motto'] = $Motto->getRandom($filmId);
            $result['info'] = $newCmtInfo;
            $this->commit();
            return $result;
        }
        if($assoctype != 0 ) {
            if($cmtId > 0) {
                $CmtRecord = $this->findOne($cmtId);
                if($CmtRecord) {
                    $filmId = $CmtRecord->assoc_id;
                }
            }
            $News = new News();
            $NewsRecord = $News->findOne($filmId);
            $sourceType = $NewsRecord->source_type;
            $videoInfo = $NewsRecord->video_info ? json_decode($NewsRecord->video_info, true) : [];
        }
        //否则发送透传
        $cmdInfo = ['cmd_type'=>3, 'desc'=>'有人给您评论了'];
        $record = [
            'from'         => $from,
            'comment_id'   => $addResult['new_cmtid'],
            'from_uid'     => $loginUid,
            'from_name'    => Cache::hget('nick'),
            'from_avatar'  => Tool::connectPath(Yii::$app->params['image_domain'], Cache::hget('avatar')),
            'from_gender'  => Cache::hget('gender'),
            'from_comment' => $comment,
            'film_id'      => $filmId,
            'ep_num'       => $epNum,
            'ep_title'     => $epNum ? $newCmtInfo['ep_title'] : '',
            'reply_uid'    => $replyUid,
            'reply_name'   => $replyNick,
            'reply_gender' => $replyGender,
            'reply_cmt'    => isset($addResult['cmt_info']) ? $addResult['cmt_info']['cmt'] : (isset($addResult['mcmt_info']) ? $addResult['mcmt_info']['cmt'] : ''),
            'reply_cmt_id' => $cmtId,
            'reply_mcmt_id'=> !$from ? ($assoctype == 0 ? $mcid : $newCmtInfo['mcid']) : $filmId,
            'assoc_type'   => isset($addResult['cmt_info']) ? $addResult['cmt_info']['assoc_type'] : 0,
            'source_type'  => $sourceType,
            'video_url'    => !empty($videoInfo) ? $videoInfo['url'] : '',
            'video_cookie' => !empty($videoInfo) ? News::BCOOKIE : '',
            'muid'         => ($assoctype != 0 && $from==0 && isset($addResult['cmt_info'])) ? $addResult['cmt_info']['uid'] : (isset($addResult['mcmt_info']) ? $addResult['mcmt_info']['uid'] : 0),
            'reply_mcmt'   => isset($addResult['mcmt_info']) && $cmtId != $mcid ? $addResult['mcmt_info']['cmt'] : '',
            'reply_mctype' => isset($addResult['mcmt_info']) ? $addResult['mcmt_info']['ctype'] : 1,
            'add_time'     => time()
        ];
        $ext = array_merge($cmdInfo, array('record'=>$record));
        //添加透传记录
        $Cmd = new Cmd();
        $cmdId = $Cmd->add($ext);
        if(!$cmdId) {
            $this->rollback();
            $this->addError('', '0:评论失败');
            return false;
        }
        $ext['cmd_id'] = $cmdId;
        $CmdHelper = new CmdHelper();
        $CmdHelper->sendCmdMessageToUsers([$replyEaseUid], $cmdInfo['desc'], $ext);
        $this->commit();
        return $newCmtInfo;
    }
    
    /**
     * 添加影视剧评论或回复
     * @param int $filmId 影视剧ID
     * @param string $comment 评论内容
     * @param string $pics 图片信息
     * @param int $epNum 影视剧剧集编号
     * @param int $mcid 主评论ID
     * @param int $cmtId 次评论ID
     * @return boolean 是否添加成功，true=添加成功
     */
    public function addFilmCmt($filmId, $comment, $pics = '', $epNum = 0, $mcid = 0, $cmtId = 0) {
        $loginUid = Cache::hget('id');
        $result = [];
        $replyUid = 0;
        if($mcid) {
            $MCmtRecord = $this->findByCondition(['id'=>$mcid, 'ctype'=>self::CTYPE['comment']])->one();
            if(!$MCmtRecord) {
                $this->addError('', '-7:您要回复的顶层评论信息不存在或已被删除');
                return false;
            }
            $Black = new Black();
            $BlackRecord = $Black->findByCondition(['uid'=>$MCmtRecord->uid, 'to_uid'=>$loginUid])->one();
            if($BlackRecord) {
                $this->addError('', '-300:该用户关闭了评论');
                return false;
            }
            $filmId = $MCmtRecord->assoc_id;
            $epNum = $epNum == 0 ? $MCmtRecord->ep_num : 0;
            $replyUid = $MCmtRecord->uid;
            $result['mcmt_info']['uid'] = $MCmtRecord->uid;
            $result['mcmt_info']['cmt'] = $MCmtRecord->comment;
            $result['mcmt_info']['ctype'] = $MCmtRecord->ctype;
        }
        if($cmtId > $mcid) {
            $CmtRecord = $this->findByCondition(['id'=>$cmtId, 'ep_num'=>$epNum, 'mcid'=>$mcid, 'ctype'=>self::CTYPE['comment']])->one();
            if(!$CmtRecord) {
                $this->addError('', '-7:您要回复的评论信息不存在或已被删除');
                return false;
            }
            $Black = new Black();
            $BlackRecord = $Black->findByCondition(['uid'=>$CmtRecord->uid, 'to_uid'=>$loginUid])->one();
            if($BlackRecord) {
                $this->addError('', '-300:该用户关闭了评论');
                return false;
            }
            $filmId = $CmtRecord->assoc_id;
            $epNum = $epNum == 0 ? $CmtRecord->ep_num : $epNum;
            $replyUid = $CmtRecord->uid;
            $result['cmt_info']['uid'] = $CmtRecord->uid;
            $result['cmt_info']['assoc_type'] = $CmtRecord->assoc_type;
            $result['cmt_info']['cmt'] = $CmtRecord->comment;
            $result['cmt_info']['ctype'] = $CmtRecord->ctype;
        }
        //直接评论影视剧 or 回复他人评论
        $Film = new Film();
        $FilmRecord = $Film->findOne($filmId);
        if(!$FilmRecord) {
            $this->addError('', '-7:您要评论的影视剧信息不存在或已被删除');
            return false;
        }
        
        if($epNum > 0 && $epNum > $FilmRecord->episode_number) {
            return $this->addError('', '-7:您要评论的剧集信息不存在或未更新');
        }
        $params = [
            'assoc_id'   => $filmId,
            'ep_num'     => $epNum,
            'mcid'       => $mcid,
            'cmt_id'     => $cmtId,
            'reply_uid'  => $replyUid,
            'comment'    => $comment,
            'pics'       => $pics
        ];
        if(!$result['new_cmtid'] = $this->addCmt($params)) {
            $this->addError('', '0:评论失败');
            return false;
        }
        if($mcid) {
            $MCmtRecord->comment_cnt = $MCmtRecord->comment_cnt + 1;
            if(!$MCmtRecord->save()) {
                $this->addError('', '0:评论信息更新失败');
                return false;
            }
        }
        if($cmtId > $mcid) {
            $CmtRecord->comment_cnt = $CmtRecord->comment_cnt + 1;
            if(!$CmtRecord->save()) {
                $this->addError('', '0:评论信息更新失败');
                return false;
            }
        }
        if($epNum > 0) {
            $FilmFollow = new FilmFollow();
            $FilmFollowRecord = $FilmFollow->findByCondition(['film_id'=>$filmId, 'user_id'=>$loginUid])->one();
            if(!$FilmFollowRecord || $FilmFollowRecord->number < $epNum) {
                Yii::$app->db->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 1);
                if($FilmFollow->updateFollow($filmId, $epNum) === false) {
                    $this->addError('', '0:追剧失败');
                    return false;
                }
                Yii::$app->db->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);
            }
        }
        return $result;
    }
    
    /**
     * 添加碎碎念或回复
     * @param string $comment 评论内容
     * @param string $pics 图片字符串
     * @param int $mcid 主评论ID
     * @param int $cmtId 次评论ID
     * @return boolean 是否添加成功，true=添加成功
     */
    public function addQuesCmt($comment, $pics = '', $mcid = 0, $cmtId = 0) {
        $loginUid = Cache::hget('id');
        $result = [];
        //直接提问 or 回复他人提问
        $replyUid = 0;
        if($mcid) {
            $MCmtRecord = $this->findByCondition(['id'=>$mcid, 'ctype'=>self::CTYPE['question']])->one();
            if(!$MCmtRecord) {
                $this->addError('', '-7:您要回复的顶层评论信息不存在或已被删除');
                return false;
            }
            $Black = new Black();
            $BlackRecord = $Black->findByCondition(['uid'=>$MCmtRecord->uid, 'to_uid'=>$loginUid])->one();
            if($BlackRecord) {
                $this->addError('', '-300:该用户关闭了评论');
                return false;
            }
            $replyUid = $MCmtRecord->uid;
            $result['mcmt_info']['uid'] = $MCmtRecord->uid;
            $result['mcmt_info']['cmt'] = $MCmtRecord->comment;
            $result['mcmt_info']['ctype'] = $MCmtRecord->ctype;
        }
        if($cmtId > $mcid) {
            $CmtRecord = $this->findByCondition(['id'=>$cmtId, 'ctype'=>self::CTYPE['question'], 'mcid'=>$mcid])->one();
            if(!$CmtRecord) {
                $this->addError('', '-7:您要回复的评论信息不存在或已被删除');
                return false;
            }
            $Black = new Black();
            $BlackRecord = $Black->findByCondition(['uid'=>$CmtRecord->uid, 'to_uid'=>$loginUid])->one();
            if($BlackRecord) {
                $this->addError('', '-300:该用户关闭了评论');
                return false;
            }
            $replyUid = $CmtRecord->uid;
            $result['cmt_info']['uid'] = $CmtRecord->uid;
            $result['cmt_info']['assoc_type'] = $CmtRecord->assoc_type;
            $result['cmt_info']['cmt'] = $CmtRecord->comment;
            $result['cmt_info']['ctype'] = $CmtRecord->ctype;
        }
        $params = [
            'ctype'     => self::CTYPE['question'],
            'mcid'       => $mcid,
            'cmt_id'     => $cmtId,
            'reply_uid'  => $replyUid,
            'comment'    => $comment,
            'pics'       => $pics
        ];
        if(!$result['new_cmtid'] = $this->addCmt($params)) {
            $this->addError('', '0:评论失败');
            return false;
        }
        if($mcid) {
            $MCmtRecord->comment_cnt = $MCmtRecord->comment_cnt + 1;
            if(!$MCmtRecord->save()) {
                $this->addError('', '0:评论信息更新失败');
                return false;
            }
        }
        if($cmtId > $mcid) {
            $CmtRecord->comment_cnt = $CmtRecord->comment_cnt + 1;
            if(!$CmtRecord->save()) {
                $this->addError('', '0:评论信息更新失败');
                return false;
            }
        }
        return $result;
    }
    
    /**
     * 添加资讯评论或回复资讯评论
     * @param int $newsId 资讯ID
     * @param string $comment 评论内容
     * @param int $assoctype 关联业务
     * @param int $cmtId 次评论ID
     * @param int $from $from=0 回复转发的资讯的评论的评论 $from=1回复资讯
     * @return boolean 是否添加成功，true=添加成功
     */
    public function addNewsCmt($newsId, $comment, $assoctype = 1, $cmtId = 0, $from = 0) {
        $loginUid = Cache::hget('id');
        //直接评论 or 回复他人评论
        $result = [];
        $result['mcmt_info']['uid'] = 0;
        $result['mcmt_info']['cmt'] = '';
        $result['mcmt_info']['ctype'] = 1;
        if($newsId > 0) {
            $News = new News();
            $NewsRecord = $News->findOne($newsId);
            if(!$NewsRecord) {
                $this->addError('', '-7:您要评论的资讯信息不存在或已被删除');
                return false;
            }
            $result['mcmt_info']['cmt'] = $NewsRecord->description;
        }
        $replyUid = 0;
        if($cmtId) {
            $sql = 'select * from ' . $this->tableName() . ' where id=:id and assoc_type like "1%"';
            $CmtRecord = $this->findBySql($sql, [':id'=>$cmtId])->one();
            if(!$CmtRecord) {
                $this->addError('', '-7:您要回复的评论信息不存在或已被删除');
                return false;
            }
            $News = new News();
            $NewsRecord = $News->findOne($CmtRecord->assoc_id);
            if(!$NewsRecord) {
                $this->addError('', '-7:您要评论的资讯信息不存在或已被删除');
                return false;
            }
            $newsId = $CmtRecord->assoc_id;
            $result['mcmt_info']['cmt'] = $NewsRecord->description;
            $Black = new Black();
            $BlackRecord = $Black->findByCondition(['uid'=>$CmtRecord->uid, 'to_uid'=>$loginUid])->one();
            if($BlackRecord) {
                $this->addError('', '-300:该用户关闭了评论');
                return false;
            }
            $replyUid = $CmtRecord->uid;
            $result['cmt_info']['uid'] = $CmtRecord->uid;
            $result['cmt_info']['assoc_type'] = $CmtRecord->assoc_type;
            $result['cmt_info']['cmt'] = $CmtRecord->comment;
            $result['cmt_info']['ctype'] = $CmtRecord->ctype;
        }
        $mcid = 0;
        if($cmtId && $from == 0 && ($CmtRecord->assoc_type == 11 || ($CmtRecord->assoc_type == 1 && $CmtRecord->mcid))) {
            $mcid = $cmtId ? ($CmtRecord->mcid ? $CmtRecord->mcid : $cmtId) : 0;
        }
        $params = [
            'assoc_type' => $assoctype,
            'assoc_id'      => $newsId,
            'cmt_id'     => $cmtId,
            'mcid'       => $mcid,
            'reply_uid'  => $replyUid,
            'comment'    => $comment
        ];
        if(!$result['new_cmtid'] = $this->addCmt($params)) {
            $this->addError('', '0:评论失败');
            return false;
        }
        if($cmtId && $cmtId != $newsId && $from == 0) {
            if($CmtRecord->assoc_type == 11) {
                $CmtRecord->comment_cnt = $CmtRecord->comment_cnt + 1;
                if(!$CmtRecord->save()) {
                    $this->addError('', '0:评论信息更新失败');
                    return false;
                }
            }
            if($CmtRecord->mcid != 0) {
                $McmtRecord = $this->findOne($CmtRecord->mcid);
                $McmtRecord->comment_cnt = $McmtRecord->comment_cnt + 1;
                if(!$McmtRecord->save()) {
                    $this->addError('', '0:评论信息更新失败');
                    return false;
                }
            }
        }
        if(!($cmtId && $from == 0 && ($CmtRecord->assoc_type == 11 || ($CmtRecord->assoc_type == 1 && $CmtRecord->mcid)))) {
            $NewsRecord->comment_cnt = $NewsRecord->comment_cnt + 1;
            if(!$NewsRecord->save()) {
                $this->addError('', '0:资讯信息更新失败');
                return false;
            }
        }
        return $result;
    }
    
    /**
     * 创建新评论/回复
     * @param array $params 基本评论信息
     * @return boolean|int 是否创建成功, int=创建成功
     */
    public function addCmt($params) {
        $loginUid = Cache::hget('id') ? Cache::hget('id') : 0;
        $this->uid = $loginUid;
        $this->assoc_type = isset($params['assoc_type']) ? $params['assoc_type'] : 0;
        $this->assoc_id = isset($params['assoc_id']) ? $params['assoc_id'] : 0;
        $this->ep_num = isset($params['ep_num']) ? $params['ep_num'] : 0;
        $this->ctype = isset($params['ctype']) ? $params['ctype'] : 1;
        $this->reply_uid = isset($params['reply_uid']) ? $params['reply_uid'] : 0;
        $this->comment_id = isset($params['cmt_id']) ? $params['cmt_id'] : 0;
        $this->comment = $params['comment'];
        $this->pics = isset($params['pics']) ? $params['pics'] : '';
        if(!$this->save()) {
            $this->addError('', '0:创建新评论失败');
            return false;
        }
        $mcid = isset($params['mcid']) ? $params['mcid'] : 0;
        $this->mcid = $this->assoc_type == 11 ? 0 : $mcid;
        if(!$this->save()) {
            $this->addError('', '0:创建新评论失败');
            return false;
        }
        return $this->id;
    }
    
    /**
     * 获取评论详情
     * @param int $uid 用户ID
     * @param int $id 评论ID
     * @return mixed array=获取成功
     */
//    public function getInfo($id) {
//        $loginUid = Cache::hget('id');
//        if(!is_numeric($id)) {
//            $this->addError('', '-4:参数格式有误');
//            return false;
//        }
//        $Record = $this->findOne($id);
//        if(!$Record || $Record->is_delete == 1) {
//            $this->addError('', '-7:请求数据不存在或已被删除');
//            return false;
//        }
//        $sql = "select "
//                . "c.id,c.assoc_id,c.ep_num,if(c.ep_num>0,concat('第',c.ep_num,'集'),'') as ep_title,c.ctype,c.assoc_type,c.uid,c.mcid,c.comment,c.pics,c.praise_cnt,c.comment_cnt,unix_timestamp(c.create_time) as dt,"
//                . "if(c.uid>0,u.nickname,'') as nickname,if(c.uid>0,u.gender,3) as gender,"
//                . "if(c.uid>0,concat('".Yii::$app->params['image_domain']."',u.avatar),'') as avatar,if(c.uid>0,u.ease_uid,'') as ease_uid,u.status,"
//                . "u.freeze_time,if(c.uid>0,u.signature,'') as signature,c.reply_uid,if(c.reply_uid>0,u2.nickname,'') as reply_nick,if(c.reply_uid>0,c2.comment,'') as reply_cmt,if(c.mcid>0 && c.assoc_type=0,mc.comment,'') as mcmt,if(c.mcid>0 && c.assoc_type=0,mc.ctype,c.ctype) as mctype,"
//                . "if(c.comment_id>0,subc.comment,'') as subcmt,if(c.assoc_id>0 && c.assoc_type=0,f.`title`,'') as title,if(c.assoc_id>0 && c.assoc_type=0,f.cover,'') as cover,if(c.assoc_id>0 && c.assoc_type=0,f.main_actor,'') as main_actor,"
//                . "if(c.assoc_id>0 && c.assoc_type=0,f.release_date,'') as release_date, if(c.assoc_id>0 && c.assoc_type=0,f.area,'') as area "
//                . "from ".$this->tableName()." c "
//                . "left join ".Comment::tableName()." c2 on c2.id=c.comment_id "
//                . "left join ".Film::tableName()." f on c.assoc_id=f.id "
//                . "left join ".User::tableName()." u on u.id=c.uid "
//                . "left join ".User::tableName()." u2 on u2.id=c.reply_uid "
//                . "left join ".$this->tableName()." mc on mc.id=c.mcid "
//                . "left join ".$this->tableName()." subc on subc.id=c.comment_id "
//                . "where c.id=:id";
//        $params = [':id'=>$id];
//        $records = $this->findBySql($sql, $params)->asArray()->one();
//        if(!$records) {
//            $this->addError('', '-7:请求数据不存在');
//            return false;
//        }
//        if($records['status'] == -1) {
//            $this->addError('', '-103:该用户已被停封');
//            return false;
//        }
//        $records['pics'] = $this->dealPics($records['pics']);
//        $records['release_date'] = $records['area'] ? $records['release_date'] . '('.$records['area'].')' : $records['release_date'];
//        $CommentHelper = new CommentHelper();
//        $CommentHelper->mergeToNews($records);
//        $CommentHelper->addIsPraise($records, $loginUid);
//        $replyList = $this->getCmtList($id, $Record->assoc_type);
//        $result = ['comment_info'=>$records, 'reply_list'=>$replyList, 'page_size'=>10];
//        return $result;     
//    }
    
    /**
     * 获取评论详情
     * @param type $id
     * @return boolean
     */
    public function getInfo($id) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $cmtInfo = $this->getBrief($id);
        if(!$cmtInfo) {
            $error = $this->getCodeError();
            return $this->addError('', $error['code'] . ':' . $error['msg']);
        }
        // 添加用户昵称头像性别等
        $CommentHelper = new CommentHelper();
        $CommentHelper->mergeToUser($cmtInfo);
        // 添加影视剧or资讯
        $CommentHelper->mergeToFilm($cmtInfo);
        $CommentHelper->mergeToNews($cmtInfo);
        $CommentHelper->addIsPraise($cmtInfo, $loginUid);
        
        
        $replyList = $this->getCmtList($id, $cmtInfo['assoc_type']);
        $result = [
            'comment_info' => $cmtInfo,
            'reply_list' => $replyList,
            'pagesize' => 10
        ];
        return $result;
    }
    
    /**
     * 获取概要信息
     * @param int $id 影视剧ID
     * @return array 影视剧概要信息
     */
    public function getBrief($id) {
        if(!is_numeric($id)) {
            return $this->addError('', '-4:参数格式有误');            
        }
        $result = [];
        $Record = $this->findOne($id);
        if(!$Record) {
            return $this->addError('', '-7:您要访问的评论信息不存在');
        }
        if($Record->is_delete == Yii::$app->params['state_code']['status_delete']) {
            return $this->addError('', '-7:您要访问的评论信息不存在或已被删除');
        }
        
        $result['id']          = $Record->id;
        $result['uid']         = $Record->uid;
        $result['assoc_type']  = $Record->assoc_type;
        $result['assoc_id']    = $Record->assoc_id;
        $result['ep_num']      = $Record->ep_num;
        $result['ep_title']    = $Record->ep_num ? '第' . $Record->ep_num . '集' : '';
        $result['ctype']       = $Record->ctype;    // 暂无用
        $result['mcid']        = $Record->mcid;   // 暂无用
        $result['reply_uid']   = $Record->reply_uid;
        $result['comment_id']  = $Record->comment_id;   // 暂无用
        $result['comment']     = $Record->comment;
        $result['praise_cnt']  = $Record->praise_cnt;
        $result['comment_cnt'] = $Record->comment_cnt;
        $result['create_time'] = strtotime($Record->create_time);
        $CommentHelper = new CommentHelper();
        $result['pics']        = $CommentHelper->dealPics($Record->pics);
        return $result; 
    }
    
    /**
     * 获取某用户评论列表
     * @param int $uid 用户ID
     * @param int $page 当前页码，0=第一页
     * @param int $pageSize 每页显示记录数
     * @return mixed array=评论列表数组
     */
    public function getUserCmtList($uid, $page = 1, $pageSize = 10) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($uid)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!is_numeric($page) || !is_numeric($pageSize) || $page < 0 ) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $sql = "select "
                . "c.id,c.uid,c.ctype,c.assoc_type,c.assoc_id,c.ep_num,if(c.ep_num>0,concat('第',c.ep_num,'集'),'') as ep_title,c.comment_id,c.mcid,c.comment,unix_timestamp(c.create_time) as create_time,"
                . "if(c.uid>0,u.nickname,'') as nickname,if(c.uid>0,concat('".Yii::$app->params['image_domain']."',u.avatar),'') as avatar,"
                . "if(c.uid>0,u.signature,'') as signature,if(c.uid>0,u.gender,1) as gender,"
                . "c.reply_uid,if(c.reply_uid>0,u2.nickname,'') as reply_nick,if(c.reply_uid>0,c2.comment,'') as reply_cmt,c.pics,c.praise_cnt,c.comment_cnt "
                . "from ".$this->tableName()." c "
                . "left join ".Comment::tableName()." c2 on c2.id=c.comment_id "
                . "left join ".User::tableName()." u on u.id=c.uid "
                . "left join ".User::tableName()." u2 on u2.id=c.reply_uid "
                . "where c.uid=:uid and ((c.mcid=0 and c.assoc_type=0) or c.assoc_type=11) and (c.is_delete=0 or c.is_delete=11) "
                . "order by c.create_time desc "
                . "limit ".(($page-1)*$pageSize).",".$pageSize;
        $params = [':uid'=>$uid];
        $records = $this->findBySql($sql, $params)->asArray()->all();
        $cmtTotal = $this->findBySql('select count(*) as cnt from ' . $this->tableName() . ' where uid=:id and ((mcid=0 and assoc_type=0) or assoc_type=11)', [':id'=>$uid])->asArray()->one();
        if($cmtTotal['cnt'] > 0) {
            $CommentHelper = new CommentHelper();
            $CommentHelper->addListIsPraise($records, $loginUid);
            $CommentHelper->addListDomain($records, 'pics');
            $CommentHelper->mergeToListFilm($records);
            $CommentHelper->mergeToListNews($records);
        }
        $result = ['page'=>(int)$page, 'pagesize'=>(int)$pageSize, 'total'=>$cmtTotal['cnt'], 'rows'=>$records];
	return $result;
    }
    
    /**
     * 获取最新评论列表
     * @param int $page 当前页码，0=第一页
     * @param int $pagesize 每页显示记录数
     * @return mixed array=最新评论列表数组
     */
    public function getNewCmtList($page = 1, $pagesize = 10) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 0 ) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        
        $sql = "select " 
                    . "c.id,c.uid,c.assoc_type,c.ctype,c.assoc_id,c.mcid,c.ep_num,if(c.ep_num>0,concat('第',c.ep_num,'集'),'') as ep_title,c.comment_id,c.comment,"
                    . "unix_timestamp(c.create_time) as create_time,if(c.uid>0,u.nickname,'') as nickname,"
                    . "if(c.uid>0,concat('".Yii::$app->params['image_domain']."',u.avatar),'') as avatar,"
                    . "if(c.uid>0,u.signature,'') as signature,if(c.uid>0,u.gender,1) as gender,"
                    . "c.reply_uid,if(c.reply_uid>0,u2.nickname,'') as reply_nick,if(c.reply_uid>0,c2.comment,'') as reply_cmt,c.pics,"
                    . "c.praise_cnt,c.comment_cnt "
                    . "from ".$this->tableName()." c "
                    . "left join ". Comment::tableName()." c2 on c2.id=c.comment_id "
                    . "left join ".User::tableName()." u on u.id=c.uid "
                    . "left join ".User::tableName()." u2 on u2.id=c.reply_uid ";
        $isBlack = '';  			
  	if($loginUid > 0) {	//登录去除黑名单信息
            $sql .= "left join " . Black::tableName() . " as black on black.to_uid=c.uid and black.uid=" . $loginUid;
            $isBlack = 'and black.to_uid is null';
  	}
  	$sql .= " where ((c.mcid=0 and c.assoc_type=0) or c.assoc_type=11) and u.status!=-1 and (c.is_delete=0 or c.is_delete=11) " . $isBlack . " order by c.create_time desc limit ".(($page-1)*$pagesize).",".$pagesize;
        
        $records = Yii::$app->db->createCommand($sql)
                ->queryAll();
        if($records) {
            $CommentHelper = new CommentHelper();
            $CommentHelper->addListIsPraise($records, $loginUid);
            $CommentHelper->addListDomain($records, 'pics');
            $CommentHelper->mergeToListFilm($records);
            $CommentHelper->mergeToListNews($records);
        }
        $result = ['page'=>(int)$page, 'pagesize'=>(int)$pagesize, 'rows'=>$records];
	return $result;
    }
    
    /**
     * 获取最新评论列表
     * @param int $page 当前页码，0=第一页
     * @param int $pageSize 每页显示记录数
     * @return mixed array=最新评论列表数组
     */
    public function getConcernCmtList($page = 1, $pageSize = 10) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($page) || !is_numeric($pageSize) || $page < 1 ) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $sql = "select " 
                    . "c.id,c.uid,c.ctype,c.assoc_type,c.assoc_id,c.ep_num,if(c.ep_num>0,concat('第',c.ep_num,'集'),'') as ep_title,c.comment_id,c.mcid,c.comment,"
                    . "unix_timestamp(c.create_time) as create_time,if(c.uid>0,u.nickname,'') as nickname,"
                    . "if(c.uid>0,concat('".Yii::$app->params['image_domain']."',u.avatar),'') as avatar,"
                    . "if(c.uid>0,u.signature,'') as signature,if(c.uid>0,u.gender,1) as gender,"
                    . "c.reply_uid,if(c.reply_uid>0,u2.nickname,'') as reply_nick,if(c.reply_uid>0,c2.comment,'') as reply_cmt,c.pics,"
                    . "c.praise_cnt,c.comment_cnt "
                    . "from ".$this->tableName()." c "
                    . "left join ".Comment::tableName()." c2 on c2.id=c.comment_id "
                    . "left join ".User::tableName()." u on u.id=c.uid "
                    . "left join ".User::tableName()." u2 on u2.id=c.reply_uid "
                    . "left join " . Friend::tableName() . " as friend on friend.fuid=c.uid "
                    . "where ((c.mcid=0 and c.assoc_type=0) or c.assoc_type=11) and u.status!=-1 and (c.is_delete=0 or c.is_delete=11) and friend.fuid=c.uid and friend.uid=".$loginUid." order by c.create_time desc limit ".(($page-1)*$pageSize).",".$pageSize;
        $records = $this->findBySql($sql)->asArray()->all();
        if($records) {
            $CommentHelper = new CommentHelper();
            $CommentHelper->addListIsPraise($records, $loginUid);
            $CommentHelper->addListDomain($records, 'pics');
            $CommentHelper->mergeToListFilm($records);
            $CommentHelper->mergeToListNews($records);
        }
        $result = ['page'=>(int)$page, 'pagesize'=>(int)$pageSize, 'rows'=>$records];
	return $result;
    }
    
    /**
     * 获取影视剧评论列表
     * @param int $filmId 影视剧ID
     * @param int $page 当前页码，0=第一页
     * @param int $pageSize 每页显示记录数
     * @return mixed array=影视剧评论列表数组
     */
    public function getFilmCmtList($filmId, $assocType = 0, $page = 0, $pageSize = 10) {
        $loginUid = Cache::hget('id');
        if(!$filmId) {
            $this->addError('', '-3:参数不可为空');
            return false;
        }
        if(!is_numeric($filmId) || !is_numeric($page) || !is_numeric($pageSize) || $page < 0 ) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $sql = "select "
                    . "c.id,c.uid,c.ctype,c.assoc_id,c.ep_num,c.comment_id,c.comment,unix_timestamp(c.create_time) as dt,"
                    . "if(c.uid>0,u.nickname,'') as nickname,"
                    . "if(c.uid>0,concat('".Yii::$app->params['image_domain']."',u.avatar),'') as avatar,"
                    . "if(c.uid>0,u.signature,'') as signature,if(c.uid>0,u.gender,1) as gender,c.pics,"
                    . "c.praise_cnt,c.comment_cnt "
                    . "from ".$this->tableName()." c "
                    . "left join ".User::tableName()." u on u.id=c.uid ";			
  	$isBlack = '';  			
  	if($loginUid > 0) {	//登录去除黑名单信息
            $sql .= "left join " . Black::tableName() . " as black on black.to_uid=c.uid and black.uid=" . $loginUid;
            $isBlack = 'and black.to_uid is null';
  	}
  	$sql .= " where c.assoc_type=:assocType and c.assoc_id=:id and c.mcid=0 and u.status!=-1 and (c.is_delete=0 or c.is_delete=11) " . $isBlack . " order by c.id desc limit ".($page*$pageSize).",".$pageSize;
        $records = $this->findBySql($sql, [':id'=>$filmId])->asArray()->all();
        if($records) {
            if($assocType) {
                $this->mergeToFilm($records);
                $this->addListEpTitle($records);
            }
            $this->addListIsPraise($records, $loginUid);
            $this->addDomain($records, 'pics');
        }
        $result = ['page'=>$page, 'page_size'=>$pageSize, 'data_list'=>$records];
	return $result;
    }
    
    /**
     * 获取追加评论列表
     * @param int $page 当前页码，0=第一页
     * @param int $pageSize 每页显示记录数
     * @return mixed array=影视剧评论列表数组
     */
    public function getCmtList($cmtId, $assocType = 0, $page = 1, $pageSize = 10) {
        $loginUid = Cache::hget('id');
        if(!$cmtId) {
            return $this->addError('', '-3:参数不可为空');
        }
        if(!is_numeric($cmtId) || !is_numeric($page) || !is_numeric($pageSize) || $page < 0 ) {
            return $this->addError('', '-4:参数格式有误');
        }
        $replyCondition = '';
        if($assocType == 1) {   //资讯相关评论
            $replyCondition = 'c.assoc_type!=0 && c.comment_id!=0';
        } else if($assocType == 11) { //转发的评论底下的评论
            $replyCondition = 'c.assoc_type!=0 && c2.assoc_type!=11';
        } else {
            $replyCondition = 'c.assoc_type = 0 && c.reply_uid>0 && c.comment_id!=c.mcid';
        }
        
        $sql = "select "
                . "c.id,c.uid,c.assoc_type,c.ctype,"
                . "if(" . $replyCondition . ",c.reply_uid,0) as reply_uid,"
                . "c.`comment`,c.praise_cnt,c.comment_cnt,c.is_agree,u.nickname,u.gender,c.pics,"
                . "concat('".Yii::$app->params['image_domain']."',u.avatar) as avatar,"
                . "if(" . $replyCondition . ",u2.nickname,'') as reply_nick,"
                . "if(" . $replyCondition . ",u2.gender,0) as reply_gender,"
                . "unix_timestamp(c.create_time) as create_time "
                . "from ".$this->tableName()." c "
                . "left join ".Comment::tableName()." c2 on c2.id=c.comment_id "
                . "left join ".User::tableName()." u on u.id=c.uid "
                . "left join ".User::tableName()." u2 on u2.id=c.reply_uid ";	
  	$isBlack = '';  			
  	if($loginUid > 0) {	//登录去除黑名单信息
            $sql .= "left join " . Black::tableName() . " as black on black.to_uid=c.uid and black.uid=" . $loginUid;
            $isBlack = ' and black.to_uid is null';
  	}
        if($assocType == 1) { //获取资讯底下评论
            $sql .= ' where (c.assoc_type!=0 and c.assoc_id=:id and c.mcid=0) ';
            $params = [':id'=>$cmtId];
        } else if($assocType == 11) {
            $sql .= ' where c.assoc_type=1 and c.mcid=:mcid';
            $params = [':mcid'=>$cmtId];
        } else {
            $sql .= ' where c.assoc_type=0 and c.mcid=:id';
            $params = [':id'=>$cmtId];
        }
  	$sql .= " and (c.is_delete=0) " . $isBlack . " order by c.is_agree desc,c.id desc limit ".(($page-1)*$pageSize).",".$pageSize;
        $records = $this->findBySql($sql, $params)->asArray()->all();
        if($records) {
            $CommentHelper = new CommentHelper();
            $CommentHelper->addListIsPraise($records, $loginUid);
            $CommentHelper->addListDomain($records, 'pics');
        }
	return $records;
    }
    
    /**
     * 点赞
     * @param int $id   评论/资讯ID
     * @param int $assocType    是资讯点赞还是评论点赞
     * @param int $from 来源资讯页还是非资讯页
     */
    public function praise($id, $assocType = 0, $from = 0) {
        $loginUid = Cache::hget('id') ? Cache::hget('id') : 0;
        if(empty($id)) {
            $this->addError('', '-3:参数不可为空');
            return false;
        }
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if(!in_array($assocType, self::ASSOCTYPE)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $key = 'COMMENT_PRAISE_'.$loginUid.'_'.$assocType.'_'.$id;
        if(!Lock::addlock($key)) {
            $this->addError('', '-10:点太快了哟');
            return false;
        }
        $this->beginTransaction();
        //给资讯点赞
        if($assocType == 1) {   //资讯页点赞
            if(!$NewsRecord = $this->praiseNews($id)) {
                Lock::unLock($key);
                $this->rollback();
                $error = $this->getCodeError();
                $this->addError('', $error['code'] . ':' . $error['msg']);
                return false;
            }
            $result = ['praised_cnt' => $NewsRecord->praise_cnt];
            Lock::unLock($key);
            $this->commit();
            return $result;
        }
        if(!$CmtRecord = $this->praiseCmt($id)) {
            Lock::unLock($key);
            $this->rollback();
            $error = $this->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        
        //给自己点赞不发送透传
        if($CmtRecord->uid == $loginUid) {
            $result = ['praised_cnt' => $CmtRecord->praise_cnt];
            Lock::unLock($key);
            $this->commit();
            return $result;
        }
        
        //发送透传
        $User = new User();
        $toUserInfo = $User->getSimpleList($CmtRecord->uid);
        $McmtRecord = $CmtRecord->mcid ? $this->findOne($CmtRecord->mcid) : $CmtRecord;
        $mcmt = '';
        $videoInfo = [];
        $sourceType = 0;
        if($CmtRecord->assoc_type !=0) {
            $News = new News();
            $NewsRecord = $News->findOne($CmtRecord->assoc_id);
            $mcmt = $NewsRecord->description;
            $sourceType = $NewsRecord->source_type;
            $videoInfo = $NewsRecord->video_info ? json_decode($NewsRecord->video_info, true) : [];
        } else {
            if($CmtRecord->mcid != 0) {
                $mcmt = $McmtRecord->comment;
            }
        }
        $cmdInfo = ['cmd_type'=>4, 'desc'=>'有人给您点赞了'];
        $record = [
            'from'       => $from,
            'to_uid'     => $CmtRecord->uid,
            'to_name'    => $toUserInfo['nickname'],
            'to_gender'  => $toUserInfo['gender'],
            'from_uid'   => $loginUid,
            'from_name'  => Cache::hget('nick'),
            'from_avatar'=> Cache::hget('avatar'),
            'from_gender'=> Cache::hget('gender'),
            'cmt_id'     => $id,
            'comment'    => $CmtRecord->comment,
            'ctype'      => $CmtRecord->ctype,
            'mcid'       => !$from ? ($CmtRecord->mcid ? $CmtRecord->mcid : $id) : $CmtRecord->assoc_id,    //from=0,来自普通评论；from=1来自资讯
            'assoc_type' => $CmtRecord->assoc_type,
            'source_type'=> $sourceType,
            'video_url'  => $CmtRecord->assoc_type && !empty($videoInfo) ? $videoInfo['url'] : '',
            'video_cookie'=> $CmtRecord->assoc_type && !empty($videoInfo) ? News::BCOOKIE : '',
            'muid'       => !$from ? $McmtRecord->uid : 0,
            'mcmt'       => $mcmt,
            'add_time'   => time()
        ];
        $ext = array_merge($cmdInfo, array('record'=>$record));
        //添加透传记录
        $Cmd = new Cmd();
        $cmdId = $Cmd->add($ext);
        if(!$cmdId) {
            Lock::unLock($key);
            $this->rollback();
            $this->addError('', '0:点赞失败');
            return false;
        }
        $ext['cmd_id'] = $cmdId;
        $CmdHelper = new CmdHelper();
        $CmdHelper->sendCmdMessageToUsers([$toUserInfo['ease_uid']], $cmdInfo['desc'], $ext);
        $this->commit();
        $result = ['to_nickname'=>$toUserInfo['nickname'], 'praised_cnt' => $CmtRecord->praise_cnt];
        Lock::unLock($key);
        return $result;
    }
    
    /**
     * 点赞资讯
     * @param int $id 资讯ID
     * @return boolean 
     */
    public function praiseNews($id) {
        $News = new News();
        $NewsRecord = $News->findOne($id);
        if(!$NewsRecord || $NewsRecord->is_delete) {
            $this->addError('', '-7:您要点赞的相关资讯不存在或已被删除');
            return false;
        }
        $Praise = new Praise();
        if(!$praiseCnt = $Praise->addAndGetPraiseCnt($id, 1)) {
            $error = $Praise->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        $NewsRecord->praise_cnt = $NewsRecord->praise_cnt + 1;
        if(!$NewsRecord->save()) {
            $this->addError('', '0:点赞失败');
            return false;
        }
        return $NewsRecord;
    }
    
    /**
     * 点赞评论
     * @param int $id 资讯ID
     * @return boolean 
     */
    public function praiseCmt($id) {
        $CmtRecord = $this->findOne($id);
        if(!$CmtRecord || $CmtRecord->is_delete == 1) {
            $this->addError('', '-7:您要回复的相关内容不存在或已被删除');
            return false;
        }
        $Praise = new Praise();
        if(!$praiseCnt = $Praise->addAndGetPraiseCnt($id)) {
            $error = $Praise->getCodeError();
            $this->addError('', $error['code'] . ':' . $error['msg']);
            return false;
        }
        $CmtRecord->praise_cnt = $CmtRecord->praise_cnt + 1;
        if(!$CmtRecord->save()) {
            $this->addError('', '0:点赞失败');
            return false;
        }
        return $CmtRecord;
    }
    
    /**
     * 赞同某回答
     * @param int $id 某评论ID
     * @return boolean
     */
    public function agree($id) {
        $loginUid = Cache::hget('id');
        if(empty($id)) {
            $this->addError('', '-3:参数不可为空');
            return false;
        }
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:请求数据不存在');
            return false;
        }
        if($Record->ctype != 2) { //不是提问的提示
            $this->addError('', '-7:请求数据不存在');
            return false;
        }
        $key = 'COMMENT_AGREE_' . $Record->mcid . '_' . $loginUid;
        if(!Lock::addlock($key)) {
            $this->addError('', '-10:赞同太快了哟，请稍后再试');
            return false;
        }
        //已经赞同过的直接成功
        if($Record->is_agree) {
            Lock::unLock($key);
            return true;
        }
        //赞同条数最多10个
        $agreeCnt = $this->findBySql('select count(*) as cnt from ' . $this->tableName() . ' where mcid=:id and is_agree=1 and ctype=2', [':id'=>$Record->mcid])->asArray()->one();
        if($agreeCnt['cnt'] >= 10) {
            Lock::unLock($key);
            $this->addError('', '-6:赞同次数超限');
            return false;
        }
        $this->beginTransaction();
        $Record->is_agree = 1;
        if(!$Record->save()) {
            Lock::unLock($key);
            $this->rollback();
            $this->addError('', '0:赞同失败');
            return false;
        }
        
        //赞同人是自己则不发送透传
        if($loginUid == $Record->uid) {
            Lock::unLock($key);
            $this->commit();
            return true;
        }
        $User = new User();
        $UserRecord = $User->findOne($Record->uid);
        $mcid = 0;
        $McmtRecord = null;
        if($Record->mcid == 0) {
            $mcid = $id;
            $McmtRecord = $Record;
        } else {
            $mcid = $Record->mcid;
            $McmtRecord = $this->findOne($mcid);
        }
        $cmdInfo = ['cmd_type'=>5, 'desc'=>'有人赞同了你的回答'];
        $record = [
            'from'       => 0,
            'to_uid'     =>$Record->uid,
            'to_name'    =>$UserRecord->nickname,
            'to_gender'  =>$UserRecord->gender,
            'from_uid'   =>$loginUid,
            'from_name'  =>Cache::hget('nick'),
            'from_avatar'=>Cache::hget('avatar'),
            'from_gender'=>Cache::hget('gender'),
            'cmt_id'     =>$id,
            'comment'    =>$Record->comment,
            'ctype'      =>$Record->ctype,
            'assoc_type' =>$Record->assoc_type,
            'source_type'=>0,
            'video_url'  =>'',
            'video_cookie'=>'',
            'mcid'       =>$mcid,
            'muid'       =>$McmtRecord->uid,
            'mcmt'       =>$Record->mcid ? $McmtRecord->comment : '',
            'add_time'   =>time()
        ];
        $ext = array_merge($cmdInfo, array('record'=>$record));
        //添加透传记录
        $Cmd = new Cmd();
        $cmdId = $Cmd->add($ext);
        if(!$cmdId) {
            Lock::unLock($key);
            $this->rollback();
            $this->addError('', '0:点赞失败');
            return false;
        }
        $ext['cmd_id'] = $cmdId;
        $CmdHelper = new CmdHelper();
        $CmdHelper->sendCmdMessageToUsers([$UserRecord->ease_uid], $cmdInfo['desc'], $ext);
        $this->commit();
        $result = ['to_nickname'=>$UserRecord->nickname];
        Lock::unLock($key);
        return $result;
    }
    
    /**
     * 取消赞同某回答
     * @param int $id 回答的ID
     * @return boolean true=取消成功
     */
    public function delAgree($id) {
        if(empty($id)) {
            $this->addError('', '-3:参数不可为空');
            return false;
        }
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:该回答不存在');
            return false;
        }
        if($Record->ctype != 2) {
            $this->addError('', '-7:该回答不存在');
            return false;
        }
        if($Record->is_agree) {
            $Record->is_agree = 0;
            if(!$Record->save()) {
                $this->addError('', '0:取消失败');
                return false;
            }
        }
        return true;
    }
    
    /**
     * 当删除主评论时可删除主评论及子评论，也可删除单个子评论
     * @param int $id 评论ID
     * @return boolean true=删除成功
     */
    public function del($id, $from = 0) {
        $loginUid = Cache::hget('id');
        if(empty($id)) {
            $this->addError('', '-3:参数不可为空');
            return false;
        }
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $key = __CLASS__ . '_' . __FUNCTION__ . '_USER' . $loginUid . '_CMT' . $id . '_FROM' . $from;
        if(!Lock::addlock($key)) {
            $this->addError('', '-6:操作异常，请稍后再试');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            Lock::unLock($key);
            $this->addError('', '-7:请求数据不存在');
            return false;
        }
        $this->beginTransaction();
        if($Record->assoc_type == 0 && $Record->mcid == 0) { //如果是主评论并且不是资讯的话，则判断是否是自己的评论
            if($Record->uid != $loginUid) {
                Lock::unLock($key);
                $this->rollback();
                $this->addError('', '-107:您无法删除非己评论');
                return false;
            }
            if($Record->is_delete == 1) {
                Lock::unLock($key);
                $this->rollback();
                $this->addError('', '-9:该评论已被删除，请勿重复操作');
                return false;
            }
            $Record->is_delete = 1;
            if(!$Record->save()) {
                Lock::unLock($key);
                $this->rollback();
                $this->addError('', '0:删除失败');
                return false;
            }
            $subRecord = $this->findByCondition(['mcid'=>$id])->one();
            if($subRecord && !$this->updateAll(['is_delete'=>1], ['mcid'=>$id])) {
                Lock::unLock($key);
                $this->rollback();
                $this->addError('', '0:子评论删除失败');
                return false;
            }
        }else { //非主评论，删除该评论即关联子评论，并更新主评论被评论次数
            if($Record->assoc_type == 0) { //普通评论
               if(!$this->delCommonCmt($Record)) {
                   Lock::unLock($key);
                    $this->rollback();
                    $error = $this->getCodeError();
                    $this->addError('', $error['code'] . ':' . $error['msg']);
                    return false;
               } 
            } else {
                if(!$this->delNewsComment($Record, $from)) {
                    Lock::unLock($key);
                    $this->rollback();
                    $error = $this->getCodeError();
                    $this->addError('', $error['code'] . ':' . $error['msg']);
                    return false;
               } 
            }
        }
        Lock::unLock($key);
        $this->commit();
        return true;
    }
    
    /**
     * 删除普通评论
     * @param type $Record
     * @return boolean
     */
    public function delCommonCmt($Record) {
        $McmtRecord = $this->findOne($Record->mcid);
        if(!$McmtRecord) {
            $this->addError('', '-7:关联主评论不存在');
            return false;
        }
        if($Record->is_delete == 1) {
            $this->addError('', '-9:该评论已被删除，请勿重复操作');
            return false;
        }
        $Record->is_delete = 1;
        if(!$Record->save()) {
            $this->addError('', '0:删除失败');
            return false;
        }
        $McmtRecord->comment_cnt = $McmtRecord->comment_cnt - 1;
        if(!$McmtRecord->save()) {
            $this->addError('', '0:评论删除失败');
            return false;
        }
        return true;
    }
    
    /**
     * 删除资讯相关评论
     * @param type $Record
     * @return boolean
     */
    public function delNewsComment($Record, $from = 0) {
        $News = new News();
        $NewsRecord = $News->findOne($Record->assoc_id);
        if(!$NewsRecord) {
            $this->addError('', '0:相关资讯不存在');
            return false;
        }
        if($from && $Record->assoc_type == 11) { // 资讯处删除评论
            if($Record->is_delete == 11) {
                $this->addError('', '-9:该评论已被删除，请勿重复操作');
                return false;
            }
            $Record->is_delete = 11;
            if(!$Record->save()) {
                $this->addError('', '0:删除失败');
                return false;
            }
        }else {
            if($Record->is_delete == 1) {
                $this->addError('', '-9:该评论已被删除，请勿重复操作');
                return false;
            }
            $Record->is_delete = 1;
            if(!$Record->save()) {
                $this->addError('', '0:删除失败');
                return false;
            }
        }
        
        if($Record->mcid) {
            $McmtRecord = $this->findOne($Record->mcid);
            if(!$McmtRecord) {
                $this->addError('', '0:相关主评论不存在');
                return false;
            }
            $McmtRecord->comment_cnt = $McmtRecord->comment_cnt - 1;
            if(!$McmtRecord->save()) {
                $this->addError('', '0:评论删除失败');
                return false;
            }
        }else if($Record->comment_id && $Record->assoc_type == 11) {
            $CmtRecord = $this->findOne($Record->comment_id);
            if(!$CmtRecord) {
                $this->addError('', '0:相关评论不存在');
                return false;
            }
            if($CmtRecord->assoc_type == 11) {
                $CmtRecord->comment_cnt = $CmtRecord->comment_cnt - 1;
                if(!$CmtRecord->save()) {
                    $this->addError('', '0:评论删除失败');
                    return false;
                }
            }
        }
        $NewsRecord->comment_cnt = $NewsRecord->comment_cnt - 1;
        if(!$NewsRecord->save()) {
            $this->addError('', '0:评论删除失败');
            return false;
        }
        return true;
    }
    
    /**
     * 获取主评论ID
     * @param type $Record
     * @return boolean
     */
    public function getMcid($Record) {
        $CmtRecord = $this->findOne($Record->comment_id);
        if(!$CmtRecord) {
            $this->addError('', '0:数据异常');
            return false;
        }
        if($CmtRecord->assoc_type == 11) {
            return $CmtRecord->id;
        }else {
            return $CmtRecord->mcid;
        }
    }
    
    /**
     * 获取我的评论详情，用于写好影评及提问时返回给客户端
     * @param int $id
     * @return array
     */
    public function getNewCmtInfo($id) {
        $uid = Cache::hget('id');
        $sql = "select "
                . "c.id,c.uid,c.ctype,c.assoc_type,c.assoc_id,c.ep_num,if(c.ep_num>0,concat('第',c.ep_num,'集'),'') as ep_title,c.comment_id,c.mcid,c.comment,unix_timestamp(c.create_time) as create_time,"
                . "if(c.uid>0,u.nickname,'') as nickname,if(c.uid>0,concat('".Yii::$app->params['image_domain']."',u.avatar),'') as avatar,"
                . "if(c.uid>0,u.signature,'') as signature,if(c.uid>0,u.gender,1) as gender,c.reply_uid,if(c.reply_uid>0,u2.ease_uid,'') as reply_ease_uid,if(c.reply_uid>0,u2.nickname,'') as reply_nick,if(c.reply_uid>0,u2.gender,0) as reply_gender,pics,praise_cnt,comment_cnt "
                . "from ".$this->tableName()." c "
                . "left join ".User::tableName()." u on u.id=c.uid "
                . "left join ".User::tableName()." u2 on u2.id=c.reply_uid "
                . "where c.id=:id";
        $params = [':id'=>$id];
        $record = $this->findBySql($sql, $params)->asArray()->one();
        $CommentHelper = new CommentHelper();
        $CommentHelper->addIsPraise($record, $uid);
        $CommentHelper->addDomain($record, 'pics');
        $CommentHelper->mergeToFilm($record);
        $CommentHelper->mergeToNews($record);
        return $record;
    }
    
    /**
     * 获取用户评论列表
     * @param int $uid 用户ID
     * @param int $page 当前页码
     * @param int $pageSize 每页显示记录数
     * @return array
     */
    public function getListByUid($uid, $page = 0, $pageSize = 10) {
        $Query = new Query();
        $result = $Query->select(['c.id','c.uid','c.ctype','c.assoc_type','c.assoc_id','c.ep_num','if(c.ep_num>0,concat("第",c.ep_num,"集"),"") as ep_title','c.mcid','c.comment_id','c.comment','c.pics','c.praise_cnt','c.comment_cnt','unix_timestamp(c.create_time) as create_time','if(c.uid>0,u.nickname,"") as nickname','if(c.uid>0,concat("'.Yii::$app->params['image_domain'].'",u.avatar),"") as avatar','if(c.uid>0,u.signature,"") as signature','if(c.uid>0,u.gender,1) as gender','c.reply_uid','if(c.reply_uid>0,u2.nickname,"") as reply_nick','if(c.reply_uid>0,c2.comment,"") as reply_cmt'])
                        ->from($this->tableName() . ' as c')
                        ->leftJoin(Comment::tableName() . ' as c2', 'c2.id=c.comment_id')
                        ->leftJoin(User::tableName() . ' as u', 'u.id=c.uid')
                        ->leftJoin(User::tableName() . ' as u2', 'u2.id=c.reply_uid')
                        ->where(['c.uid'=>$uid, 'c.comment_id'=>0, 'c.assoc_type'=>0, 'c.is_delete'=>0])
                        ->orWhere(['c.uid'=>$uid, 'c.comment_id'=>0, 'c.assoc_type'=>0, 'c.is_delete'=>11])
                        ->orWhere(['c.uid'=>$uid, 'c.assoc_type'=>11, 'c.is_delete'=>0])
                        ->orWhere(['c.uid'=>$uid, 'c.assoc_type'=>11, 'c.is_delete'=>11])
                        ->orderBy(['c.create_time'=>SORT_DESC])
                        ->limit($pageSize)
                        ->offset($page*$pageSize)
                        ->all();
        return $result;
    }
    
    /**
     * 获取某个影片评论总数
     * @param int $filmId 影视剧ID
     * @return int 总数
     */
    public function getFilmCommentTotal($filmId) {
        $sql = "select count(c.id) as cnt from " . $this->tableName() . ' as c ';
        $isBlack = '';  	
        $uid = Cache::hget('id');
  	if($uid) {	//登录去除黑名单信息
            $Black = new Black();
            $sql .= "left join " . $Black->tableName() . " as black on black.to_uid=c.uid and black.uid=" . $uid;
            $isBlack = 'and black.to_uid is null';
        }
        $sql .= ' where c.assoc_id=:id and c.mcid=0 and c.is_delete=0 ' . $isBlack;
        $record = $this->findBySql($sql, [':id'=>$filmId])->asArray()->one();
        return $record['cnt'];
    }
    
    /**
     * 根据影视剧ID获取评论列表
     * @param int $id 影视剧ID
     * @param int $page 当前页码
     * @param int $pageSize 记录数
     * @return mixed array=评论列表
     */
    public function getListByEp($filmId, $epNum, $page = 1, $pageSize = 10) {
        if(!is_numeric($filmId) || !is_numeric($epNum) || !is_numeric($page) || $page < 1 || !is_numeric($pageSize)) {
            return $this->addError('', '-4:参数格式有误');
        }
        $Film = new Film();
        $FilmRecord = $Film->findOne($filmId);
        if(!$FilmRecord) {
            return $this->addError('', '-7:相关影视剧不存在');
        }
        $epInfo = [];
        $epInfo['film_id'] = (int)$filmId;
        $epInfo['ep_num'] = (int)$epNum;
        $epInfo['title'] = '第' . $epNum . '集';
        $epInfo['desc'] = '';
        $User = new User();
        $sql = "select "
                . "c.id,c.uid,c.ctype,c.comment_id,c.comment,unix_timestamp(c.create_time) as create_time,"
                . "if(c.uid>0,u.nickname,'') as nickname,"
                . "if(c.uid>0,concat('".Yii::$app->params['image_domain']."',u.avatar),'') as avatar,"
                . "if(c.uid>0,u.signature,'') as signature,if(c.uid>0,u.gender,1) as gender,c.pics,"
                . "c.praise_cnt,c.comment_cnt "
                . "from ".$this->tableName()." c "
                . "left join ".$User->tableName()." u on u.id=c.uid ";
        $isBlack = '';  
        $uid = Cache::hget('id');
  	if($uid) {	//登录去除黑名单信息
            $Black = new Black();
            $sql .= "left join " . $Black->tableName() . " as black on black.to_uid=c.uid and black.uid=" . $uid;
            $isBlack = 'and black.to_uid is null';
  	}
        $sql .= " where c.assoc_id=:filmId and c.ep_num=:epNum and c.mcid=0 and u.status!=-1 and c.is_delete=0 " . $isBlack . " order by c.id desc limit ".(($page-1)*$pageSize).",".$pageSize;
        $records = $this->findBySql($sql, [':filmId'=>$filmId, ':epNum'=>$epNum])->asArray()->all();
        if(!empty($records)){
            $CommentHelper = new CommentHelper();
            $CommentHelper->addListDomain($records, 'pics');
            $CommentHelper->addListIsPraise($records, $uid);
        }
        $result = ['cmt_list'=>$records, 'page'=>(int)$page, 'pagesize'=>(int)$pageSize];
        if($page == 1) {
            $epFollow = 0;
            $FilmFollow = new FilmFollow();
            $FollowRecord = $FilmFollow->findByCondition(['film_id'=>$filmId, 'user_id'=>$uid])->one();
            if($FollowRecord) {
                $epFollow = $FollowRecord->number;
            }
            $epInfo['fmain_actor'] = $FilmRecord->main_actor;
            $epInfo['fcover'] = $FilmRecord->cover;
            $epInfo['ftitle'] = $FilmRecord->title;
            $epInfo['fepisode_number'] = $FilmRecord->episode_number;
            
            $epInfo['ftag'] = $FilmRecord->genre ? $FilmRecord->genre : Yii::$app->params['text_desc']['film_kind'][$FilmRecord->kind];
            $year = $FilmRecord->year != Yii::$app->params['state_code']['year_unknown'] ? (string)$FilmRecord->year : Yii::$app->params['text_desc']['year_unknown'];
            
            $area = $FilmRecord->area ? $FilmRecord->area : '';
            $releaseDate = !$area ? $year : $year . '(' . $area . ')';
            $epInfo['fyear'] = $releaseDate;
            $epInfo['ep_follow'] = $epFollow;
            $result['ep_info'] = $epInfo;
        }
        return $result;
    }
    
    /**
     * 获取某个影片评论总数
     * @param int $filmId 影视剧ID
     * @return int 总数
     */
    public function getEpCmtTotal($filmId, $epNum) {
        $sql = "select count(c.id) as cnt from " . $this->tableName() . ' as c ';
        $isBlack = '';  	
        $uid = Cache::hget('id');
  	if($uid) {	//登录去除黑名单信息
            $Black = new Black();
            $sql .= "left join " . $Black->tableName() . " as black on black.to_uid=c.uid and black.uid=" . $uid;
            $isBlack = 'and black.to_uid is null';
        }
        $sql .= ' where c.assoc_id=:filmId and c.ep_num=:epNum and c.mcid=0 and c.is_delete=0 ' . $isBlack;
        $record = $this->findBySql($sql, [':filmId'=>$filmId, ':epNum'=>$epNum])->asArray()->one();
        return $record['cnt'];
    }
    
    /**
     * 获取黑名单用户评论数
     * @param int $commentId 主评论ID
     * @return int 黑名单用户评论数
     */
    public function getBlackCmtCnt($commentId) {
        $loginUid = Cache::hget('id');
        $Black = new Black();
        $sql = 'select count(*) as cnt from ' . $this->tableName() . ' as c inner join ' . $Black->tableName() . ' as black on black.to_uid=c.uid and black.uid='.$loginUid.' where c.mcid=:cmtId';
        $Record = $this->findBySql($sql, [':cmtId'=>$commentId])->one();
        return $Record->cnt;
    }
    
    /**
     * 级联删除关联评论
     * @param int $cmtId 回复的评论ID
     * @return boolen true=级联删除成功
     */
    public function cascadeDel($cmtId) {
        static $delCount = 0;
        $records = $this->findByCondition(['comment_id'=>$cmtId])->all();
        if($records) {
            foreach($records as $item) {
                $delCount ++;
                if(!$item->delete()) {
                    return false;
                }
                $this->cascadeDel($item->id);
            }
        }
        return $delCount;
    }
    
    /**
     * 启动事务
     * @return void 无返回值
     */
    protected function beginTransaction() {
        $this->_Transaction = Yii::$app->db->beginTransaction();
    }
    
    /**
     * 回滚事务
     * @return void 无返回值
     */
    protected function rollback() {
        if($this->_Transaction != null) {
            $this->_Transaction->rollBack();
        }
    }
    
    /**
     * 提交事务
     * @return void 无返回值
     */
    protected function commit() {
        if($this->_Transaction != null) {
            $this->_Transaction->commit();
        }
    }

    
}

