<?php
/**
 * 聊天室类
 * @author ztt
 * @date 2017/12/13
 */
namespace app\models;

use Yii;
use \yii\db\Query;
use app\components\ActiveRecord;
use app\models\Role;
use app\models\UserRole;
use app\models\Tipoff;
use app\models\Forbid;


class Chatroom extends ActiveRecord {
    private $_Transaction = null;  //事务处理对象
    public static $reason = [0,1,2,3,4,5];    //举报原因，0=其他；1=暴力色情；2=人身攻击；3=广告骚扰；4=谣言及虚假信息；5=政治敏感，默认为0
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%chatroom}}';
    }
    
    /**
     * 获取聊天室详情
     * @param int $id 影视剧ID
     * @return array 聊天室详情数组
     */
    public function getInfo($id) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:聊天室不存在');
            return false;
        }
        if(strtotime($Record->end_time) <= time()) {
            $this->addError('', '-200:聊天室已过期');
            return false;
        }
        $Film = new Film();
        $FilmRecord = $Film->findOne($id);
        $result = [];
        $result['id'] = $id;
        $result['name'] = $FilmRecord->name;
        $result['cover'] = $FilmRecord->cover;
        $result['summary'] = '<div style="font-size:16px;line-height:28px;">' . $FilmRecord->summary . '</div>';
        $result['ease_id'] = $Record->ease_id;
        $result['dt_end'] = strtotime($Record->end_time);
        $Forbid = new Forbid();
        $ForbidInfo = $Forbid->findByCondition(['room_id'=>$id, 'uid'=>$loginUid])->one();
        $result['forbid_end'] = 0;
        if($ForbidInfo) {
            $result['forbid_end'] = strtotime($ForbidInfo->end_time);
        }
        $Shield = new Shield();
        $result['shield_list'] = $Shield->findBySql('select to_uid as uid from ' . $Shield->tableName() . ' where uid=:uid', [':uid'=>$loginUid])->asArray()->all();
        $result['member_list'] = $this->getRoomMembers($id, $Record->ease_id, $id, 0);
        $result['all_member_list'] = $this->getRoomMembers($id, $Record->ease_id, $id);
        return $result;
    }
    
    /**
     * 获取聊天室简单信息
     * @param int $id 聊天室ID
     * @return 
     */
    public function getDetail($id) {
        $loginUid = Cache::hget('id');
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Record = $this->findOne($id);
        if(!$Record) {
            $this->addError('', '-7:聊天室不存在');
            return false;
        }
        
        if(strtotime($Record->end_time) <= time()) {
            $this->addError('', '-200:聊天室已过期');
            return false;
        }
        $Film = new Film();
        $FilmRecord = $Film->findOne($id);
        $result = [];
        $result['id'] = $id;
        $result['name'] = $FilmRecord->name;
        $result['cover'] = $FilmRecord->cover;
        $result['acts_main'] = $FilmRecord->acts_main;
        $result['summary'] =  '<div style="font-size:16px;line-height:28px;">' . $FilmRecord->summary . '</div>';
        $result['types'] = $FilmRecord->types;
        $result['release_date'] = $FilmRecord['release_date'];
        $result['img_top'] = Tool::connectPath(Yii::$app->params['image_domain'], $Record->img_top);
        $result['ease_id'] = $Record->ease_id;
        $result['member_list'] = $this->getRoomMembers($id, $Record->ease_id, 0);
        $Role = new Role();
        $result['role_list'] = $Role->findByCondition(['tv_id'=>$id])->asArray()->all();
        foreach($result['role_list'] as $key=>$item) {
            $result['role_list'][$key]['avatar'] = Tool::connectPath(Yii::$app->params['image_domain'], $item['avatar']);
        }
        return $result;
    }
    
    /**
     * 获取聊天室用户详情
     * @param string $easeUid 用户环信ID
     * @param string $easeRoomId 聊天室环信ID
     * @return mixed array=用户详细信息
     */
    public function getUserinfo($easeUid, $easeRoomId) {
        if(empty($easeUid) || empty($easeRoomId)) {
            $this->addError('', '-3:参数不可为空');
            return false;
        }
        $Chatroom = new Chatroom();
        $RoomRecord = $Chatroom->findByCondition(['ease_id'=>$easeRoomId])->one();
        if(!$RoomRecord) {
            $this->addError('', '-7:聊天室不存在');
            return false;
        }
        $rid = $RoomRecord->film_id;
        $sql = "select "
                . "u.id,concat('".Yii::$app->params['image_domain']."',u.avatar) as avatar,u.nickname,u.gender,u.ease_uid,ur.num as role_num,"
                . "concat('".Yii::$app->params['image_domain']."',r.avatar) as role_avatar,r.role_name,if(f.end_time!='',unix_timestamp(f.end_time),'') as forbid_end "
                . "from ".UserRole::tableName()." ur "
                . "inner join ".Role::tableName()." r on ur.role_id=r.id "
                . "inner join ".User::tableName()." u on u.id=ur.uid "
                . "left join ". Forbid::tableName()." f on f.uid=ur.uid and f.room_id={$rid} "
                . "where ur.rease_id=:reaseId and u.ease_uid=:easeUid";
        
        $result = $this->findBySql($sql, [':reaseId'=>$easeRoomId, ':easeUid'=>$easeUid])->asArray()->one();
        if(!$result) {
            $this->addError('', '-7:该用户不存在');
            return false;
        }
        return $result;
    }
    
    /**
     * 获取聊天室角色列表
     * @param type $id
     * @return mixed array=角色列表数组
     */
    public function getRoles($id) {
        if(!is_numeric($id)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Roles = new Role();
        $records = $Roles->findByCondition(['tv_id'=>$id])->asArray()->all();
        foreach($records as $key=>$item) {
            $records[$key]['avatar'] = Tool::connectPath(Yii::$app->params['image_domain'], $item['avatar']);
        }
        return $records;   
    }
    
    /**
     * 选择角色
     * @param int $roomId 聊天室ID，即影视剧ID
     * @param int $roleId 角色ID 
     * @return mixed int=角色编号
     */
    public function setRole($roomId, $roleId) {
        $loginUid = Cache::hget('id');
        if(empty($roomId) || empty($roleId)) {
            $this->addError('', '-3:参数不可为空');
            return false;
        }
        if(!is_numeric($roomId) || !is_numeric($roleId)) {
            $this->addError('', '-4:参数格式不正确');
            return false;
        }
        $Record = $this->findOne($roomId);
        if(!$Record) {
            $this->addError('', '-7:该聊天室不存在');
            return false;
        }
        $Role = new Role();
        $RoleRecord = $Role->findByCondition(['tv_id'=>$roomId, 'id'=>$roleId])->one();
        if(!$RoleRecord) {
            $this->addError('', '-7:角色信息不存在');
            return false;
        }
        $UserRole = new UserRole();
        //是否已经选过角色，有则返还角色编号
        $UserRoleRecord = $UserRole->findByCondition(['uid'=>$loginUid, 'rease_id'=>$Record->ease_id])->one();
        if($UserRoleRecord && $UserRoleRecord->role_id == $roleId) {
            return $UserRoleRecord->num;
        }
        //如果没有选角色，则查看有多少个选个该角色的人，增加编号
        $count = $UserRole->findBySql('select count(*) as cnt from ' . $UserRole->tableName() . ' where role_id=:roleId and rease_id=:reaseId', [':roleId'=>$roleId, ':reaseId'=>$Record->ease_id])->asArray()->one();
        $cnt = 0;
        if($count) {
            $cnt =  $count['cnt'];
        }
        $cnt = $count['cnt'] + 1;
        if($UserRoleRecord) {
            $UserRoleRecord->role_id = $roleId;
            $UserRoleRecord->num = $cnt;
            if(!$UserRoleRecord->save()) {
                $this->addError('', '0:角色用户更新失败');
                return false;
            }
        }
        if(!$UserRoleRecord && !$UserRole->add($loginUid, $Record->ease_id, $roleId, $cnt)) {
            $this->addError('', '0:角色用户添加失败');
            return false;
        }
        return $cnt;
    }
    
    /**
     * 聊天室举报用户，多人同意后用户将被禁言，同一用户举报时间间隔应大于300秒
     * @param int $roomId 聊天室ID
     * @param int $uid 用户ID
     * @param string $reason 举报原因
     * @param int $needNum 需要同意的人数
     * @return boolean array
     */
    public function tipoff($roomId, $uid, $reason = 0, $needNum = 3) {
        $loginUid = Cache::hget('id');
        if(empty($roomId) || empty($uid) || $reason == '') {
            $this->addError('', '-3:聊天室信息及举报原因不可为空');
            return false;
        }
        if(!is_numeric($roomId) || !is_numeric($uid) || !is_numeric($needNum) || !in_array($reason, self::$reason)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        if($uid == $loginUid) {
            $this->addError('', '-107:无法举报自己');
            return false;
        }
        $Record = $this->findOne($roomId);
        if(!$Record) {
            $this->addError('', '-7:您请求的聊天室不存在');
            return false;
        }
        $Film = new Film();
        $FilmRecord = $Film->findOne($roomId);
        if(!$FilmRecord) {
            $this->addError('', '-7:相关联的影视剧不存在');
            return false;
        }
        $User = new User();
        $UserRecord = $User->findOne($uid);
        if(!$UserRecord) {
            $this->addError('', '-7:您要举报的用户不存在');
            return false;
        }
        $UserRole = new UserRole();
        $UserRoleRecord = $UserRole->findByCondition(['rease_id'=>$Record->ease_id, 'uid'=>$uid])->one();
        if(!$UserRoleRecord) {
            $this->addError('', '-7:您要举报的用户不在该聊天室');
            return false;
        }
        //查看该用户是否已经被举报禁言，若未被禁言则300秒内不能再次举报（举报的动作不同聊天室之间最少300秒），300秒后可再次举报，即更新举报时间
        $Forbid = new Forbid();
        $ForbidRecord = $Forbid->findByCondition(['room_id'=>$roomId, 'uid'=>$uid])->one();
        if($ForbidRecord && strtotime($ForbidRecord->end_time) > time()) {
            $this->addError('', '-9:该用户已经被禁言');
            return false;
        }
        $Tipoff = new Tipoff();
        $sql = "select unix_timestamp(create_time) as dt_create from ".$Tipoff->tableName()." where uid=:uid and room_id=:roomId order by create_time desc limit 1";
        $TipoffLastRecord = $Tipoff->findBySql($sql, [':roomId'=>$roomId, ':uid'=>$loginUid])->asArray()->one();
//        if($TipoffLastRecord && $TipoffLastRecord['dt_create'] + 300 > time()) {
//            $this->addError('', '-9:举报过于频繁了');
//            return false;
//        }
        $this->beginTransaction();
        $TipoffRecord = $Tipoff->findByCondition(['uid'=>$loginUid, 'to_uid'=>$uid, 'room_id'=>$roomId])->one();
        if($TipoffRecord) {
            if($TipoffRecord->reason != $reason) {
                $TipoffRecord->reason = $reason;
            }
            $TipoffRecord->create_time = date('Y-m-d H:i:s');
            if(!$TipoffRecord->save()) {
                $this->rollback();
                $this->addError('', '0:举报失败');
                return false;
            }
        }else {
            if(!$Tipoff->add($loginUid, $roomId, $uid, $reason, $needNum)) {
                $this->rollback();
                $this->addError('', '0:举报失败');
                return false;
            }
            $TipoffRecord = $Tipoff;
        }
        //给被举报人发送透传消息
        $cmdInfo = ['cmd_type'=>1, 'desc'=>'有人发起了对'.$UserRecord->nickname.'的举报，'.$needNum.'人同意将禁言该用户，您是否同意？'];
        $record = [
            'tipoff_id'    =>$TipoffRecord->id,
            'room_id'      =>$roomId,
            'ease_id'      =>$Record->ease_id,
            'room_name'    =>$FilmRecord->name,
            'member_uid'   =>$uid,
            'member_name'  =>$UserRecord->nickname,
            'member_gender'=>$UserRecord->gender,
            'need_num'     =>$needNum,
            'from_uid'     =>$loginUid,
            'from_name'    =>Cache::hget('nick'),
            'from_gender'  =>Cache::hget('gender'),
            'add_time'     =>time()
        ];
        $ext = array_merge($cmdInfo, array('record'=>$record));
        //添加透传记录
        $Cmd = new Cmd();
        $cmdId = $Cmd->add($ext);
        if(!$cmdId) {
            $this->rollback();
            $this->addError('', '0:举报失败');
            return false;
        }
        $ext['cmd_id'] = $cmdId;
        $CmdHelper = new CmdHelper();
        $CmdHelper->sendMsgToEaseChatRoom([$Record->ease_id], $cmdInfo['desc'], $ext);
        $result = ['to_nickname'=>$UserRecord->nickname, 'need_num'=>$needNum];
        $this->commit();
        return $result;
    }
    
    /**
     * 聊天室同意举报某用户
     * @param type $tipoffId
     * @return boolean
     */
    public function agreeTipoff($tipoffId) {
        $loginUid = Cache::hget('id');
        if(empty($tipoffId)) {
            $this->addError('', '-3:请选择相应举报信息');
            return false;
        }
        if(!is_numeric($tipoffId)) {
            $this->addError('', '-4:参数格式有误');
            return false;
        }
        $Tipoff = new Tipoff();
        $TipoffRecord = $Tipoff->findOne($tipoffId);
        if(!$TipoffRecord) {
            $this->addError('', '-7:举报信息不存在');
            return false;
        }
        if($TipoffRecord->to_uid == $loginUid) {
            $this->addError('', '-107:举报人本人无法处理');
            return false;
        }
        $User = new User();
        $UserRecord = $User->findOne($TipoffRecord->to_uid);
        if(!$UserRecord) {
            $this->addError('', '-7:举报信息不存在');
            return false;
        }
        $Film = new Film();
        $FilmRecord = $Film->findOne($TipoffRecord->room_id);
        if(!$FilmRecord) {
            $this->addError('', '-7:相关影视剧信息不存在');
            return false;
        }
        $Chatroom = new Chatroom();
        $RoomRecord = $Chatroom->findOne($TipoffRecord->room_id);
        if(!$RoomRecord) {
            $this->addError('', '-7:相关聊天室信息不存在');
            return false;
        }
        if($TipoffRecord->to_uid == $loginUid) {
            $this->addError('', '-107:被举报人无法处理');
            return false;
        }
        //30分钟内的举报才可以同意
        if(time() - strtotime($TipoffRecord->create_time) >= 1800) {
            $this->addError('', '-201:当前举报已失效');
            return false;
        }
        //查看该用户是否已经被禁言,如果已经禁言，则说明同意举报人数已达上限
        $Forbid = new Forbid();
        $ForbidRecord = $Forbid->findByCondition(['room_id'=>$TipoffRecord->room_id, 'uid'=>$TipoffRecord->to_uid])->one();
        if($ForbidRecord && strtotime($ForbidRecord->end_time) > time()) {
            $this->addError('', '-9:该用户已经被禁言');
            return false;
        }
        //多少人已同意
        $agreeUidsArr = empty($TipoffRecord->agree_uids) ? [] : explode(',', $TipoffRecord->agree_uids);
        if(in_array($loginUid, $agreeUidsArr)) {
            $this->addError('', '-107:您已同意该举报');
            return false;
       }
       $agreeUidsArr[] = $loginUid;
       $agreeUids = implode(',', $agreeUidsArr);
       $this->beginTransaction();
       if(!$Tipoff->updateAgreeNum($tipoffId, $agreeUids)) {
           $this->rollback();
           $this->addError('', '0:同意举报失败');
           return false;
       }
       //如果达到同意举报人数，则添加禁言，并发送透传
       $result = [
               'tid'        =>$tipoffId,
               'room_id'    =>$TipoffRecord->room_id,
               'to_uid'     =>$TipoffRecord->to_uid,
               'to_nickname'=>$UserRecord->nickname,
               'need_num'   =>$TipoffRecord->need_num
           ];
       if(count($agreeUidsArr) < $TipoffRecord->need_num) {
           $this->commit();
           return $result;
       }
       if(!$Forbid->add($TipoffRecord->to_uid, $TipoffRecord->room_id)) {
            $this->rollback();
            $this->addError('', '0:同意举报失败');
            return false;
       }
       //给被禁言用户发送透传
       $cmdInfo = ['cmd_type'=>2, 'desc'=> $UserRecord->nickname.'被禁言一个小时'];
       $record = [
           'tipoff_id'    =>$tipoffId,
           'room_id'      =>$TipoffRecord->room_id,
           'ease_id'      =>$RoomRecord->ease_id,
           'room_name'    =>$UserRecord->nickname,
           'room_avatar'  =>$FilmRecord->cover,
           'member_uid'   =>$TipoffRecord->to_uid,
           'member_name'  =>$UserRecord->nickname,
           'member_gender'=>$UserRecord->gender,
//           'remain_time'  =>3600,
//           'finish_time'  =>time()+3600,
           'remain_time'  =>600,
           'finish_time'  =>time()+600,
           'add_time'     =>time()
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
       $CmdHelper->sendMsgToEaseChatRoom([$RoomRecord->ease_id], $cmdInfo['desc'], $ext); 
       $this->commit();
       return $result;
    }
    
    /**
     * 获取聊天室成员列表
     * @param string $easeRoomId
     * @param type $roomId
     * @param type $isAll
     * @return array
     */
    public function getRoomMembers($rid, $easeRoomId, $isAll = 1){
        $response = Easemob::getInstance()->getChatRoomDetail($easeRoomId);
        $members = [];
        if( $response['code'] != 200) {
            sleep(1);
            $response = Easemob::getInstance()->getChatRoomDetail($easeRoomId);
            if( $response['code'] == 200){
                $members = $response['result']['data'][0]['affiliations'];
            }
        }else{
            $members = $response['result']['data'][0]['affiliations'];
        }
        if(!empty($members) && count($members) > 0) {
            $memberArr = [];
            foreach($members as &$mem){
                if(isset($mem['member'])){
                    $memberArr[] = $mem['member'];
                }
            }
            if(!empty($memberArr)) {
                $UserRole = new UserRole();
                $memberList = $UserRole->getMemberList($rid, $easeRoomId, $memberArr, $isAll);
            }
        }

        if(empty($memberList)){
            $memberList = array();
        }
        return $memberList;
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

