<?php
/**
 * 用户角色模型类
 * @author ztt
 * @date 2017/12/13
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\User;
use app\models\Black;
use app\models\Cmd;

class UserRole extends ActiveRecord {
    
    private $_Transaction = null;  //事务处理对象
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%user_roles}}';
    }
    
    /**
     * 查找聊天室成员集合
     * @param int $reaseId 聊天室环信ID
     * @param int $isAll  是否查找全部成员包括黑名单，0=不包括，1=包括
     * @return array
     */
    public function getMemberList($rid, $reaseId, $memberArr, $isAll = 1) {
        $loginUid = Cache::hget('id');
        foreach($memberArr as $key=>$member) {
            $memberArr[$key] = "'" . $member . "'";
        }
        $sql = "select "
                . "u.id,concat('".Yii::$app->params['image_domain']."',u.avatar) as avatar,u.nickname,u.gender,u.ease_uid,"
                . "ur.num as role_num,concat('".Yii::$app->params['image_domain']."',r.avatar) as role_avatar,r.role_name,if(f.end_time!='', unix_timestamp(f.end_time), '') as forbid_end "
                . "from ".$this->tableName()." ur "
                . "inner join ".Role::tableName()." r on ur.role_id=r.id "
                . "inner join ".User::tableName()." u on u.id=ur.uid "
                . "left join ". Forbid::tableName()." f on f.uid=ur.uid and f.room_id={$rid} "
                . "where ur.rease_id=:reaseId and u.ease_uid in(". implode(',', $memberArr).")";
        $records = $this->findBySql($sql, [':reaseId'=>$reaseId])->asArray()->all();
        if(!$isAll) {   //去除黑名单
            $Black = new Black();
            $BlackRecords = $Black->findByCondition(['uid'=>$loginUid])->all();
            $blackIds = [];
            foreach($BlackRecords as $item) {
                $blackIds[] = $item->to_uid;
            }
            foreach($records as $key=>$value) {
                if(in_array($value['id'], $blackIds)) {
                    unset($records[$key]);
                }
            }
        }
        return $records;
    }
    
    /**
     * 添加聊天室角色用户
     * @param int $uid 用户ID
     * @param string $reaseId 聊天室环信ID
     * @param int $roleId 角色ID
     * @param int $num 编号
     * @return boolen 是否添加成功
     */
    public function add($uid, $reaseId, $roleId, $num) {
        $this->uid = $uid;
        $this->rease_id = $reaseId;
        $this->role_id = $roleId;
        $this->num = $num;
        return $this->save();
    }
    
    
    
    
}

