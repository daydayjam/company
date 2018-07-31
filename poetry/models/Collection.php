<?php

/**
 * 收藏操作类
 * @date 2018/06/06
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;
use app\models\Helper;

class Collection extends ActiveRecord {
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%collection}}';
    }
    
    /**
     * 获取当前登录用户的收藏列表及总数
     * @param int $page 当前页码 1=第一页
     * @param int $pagesize 每页显示记录数
     * @return array 收藏列表结果集
     */
    public function getList($page = 1, $pagesize = 10) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '300:参数格式有误，请重试');
        }
        $select = 'poetry_id';
        $params = ['status'=>Yii::$app->params['code']['collect_yes'], 'user_id'=>$loginUserId];
        $order = 'update_time desc, create_time desc';
        $result = $this->getListData($select, $page, $pagesize, $params, $order);
        if($result['total'] == 0) {
            return $result;
        }
        // 整合诗词
        try{
            Helper::mergePoetryInfoToList($result['rows']);
        }catch(\Exception $e) {
            return $this->addError('', '500:' . $e->getMessage());
        }
        return $result;
    }
    
    /**
     * 获取总数
     * @return int
     */
    public function getTotal($userId = 0) {
        $loginUserId = $userId ? $userId : (Cache::hget('user_id') ? Cache::hget('user_id') : 0);
        $countRow = (new \yii\db\Query())
                    ->select('count(*) as count')
                    ->from($this->tableName())
                    ->where('user_id=:user_id and status=1', [':user_id'=>$loginUserId])
                    ->one();
        return $countRow['count'];
    }
    
    /**
     * 添加收藏
     * @param int $poetryId 诗词ID
     * @return boolen true=收藏成功
     */
    public function add($poetryId) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($poetryId) || $poetryId < 1) {
            return $this->addError('', '300:参数格式有误');
        }
        // 判断当前是否已收藏该诗词
        $Record = $this->findByCondition(['user_id'=>$loginUserId, 'poetry_id'=>$poetryId])->one();
        if(!$Record) {
            $this->user_id = $loginUserId;
            $this->poetry_id = $poetryId;
            if(!$this->save()) {
                return $this->addError('', '400:收藏失败，请稍后重试');
            }
            return $this->getTotal();
        }
        if($Record->status == Yii::$app->params['code']['collect_no']) {
            $Record->status = Yii::$app->params['code']['collect_yes'];
            if(!$Record->save()) {
                return $this->addError('', '400:收藏失败，请稍后重试');
            }
            return $this->getTotal();
        }
        if($Record->status == Yii::$app->params['code']['collect_yes']) {
            return $this->addError('', '401:您已收藏该诗词');
        }
        return $this->getTotal();
    }
    
    /**
     * 取消收藏某诗词
     * @param int $poetryId 诗词ID
     * @return boolen true=取消收藏成功
     */
    public function remove($poetryId) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($poetryId) || $poetryId < 1) {
            return $this->addError('', '300:参数格式有误');
        }
        // 判断当前是否已收藏该诗词
        $Record = $this->findByCondition(['user_id'=>$loginUserId, 'poetry_id'=>$poetryId])->one();
        if(!$Record) {
            return $this->addError('', '402:您还未收藏该诗词，赶紧去收藏吧');
        }
        if($Record->status == Yii::$app->params['code']['collect_no']) {
            return $this->addError('', '402:您已取消收藏，请勿重复操作哦');
        }
        $Record->status = Yii::$app->params['code']['collect_no'];
        if(!$Record->save()) {
            return $this->addError('', '400:取消收藏该诗词未成功，请稍后重试哦');
        }
        return $this->getTotal();
    }
}

