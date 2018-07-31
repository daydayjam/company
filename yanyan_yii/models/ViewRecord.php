<?php
/**
 * 观看记录模型类
 * @author ztt
 * @date 2017/11/28
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class ViewRecord extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%view_record}}';
    }
    
    /**
     * 更新观影记录
     * @param int $uid
     * @param int $filmId
     * @param int $epNum
     * @param int $filmType
     * @param string $filmTitle
     * @param string $filmCover
     * @param int $routeId
     * @param string $url
     * @return boolean
     */
    public function add($userId, $filmId, $epNum, $filmType, $filmTitle, $filmCover, $routeId, $url) {
        $Record = $this->findByCondition(['user_id'=>$userId,'film_id'=>$filmId])->one();
        if(!$Record) {
            // 查找当前用户是否已有50条观影记录，有则自动删除最早一条数据
            $sql = 'select count(1) as cnt from ' . $this->tableName() . ' where user_id=:user_id';
            $CurrCnt = $this->findBySql($sql, [':user_id'=>$userId])->asArray()->one();
            if($CurrCnt['cnt'] >= 50) {
                $OldRecord = $this->find()->orderBy('id')->limit(1)->one();
                if(!$OldRecord->delete()) {
                    return $this->addError('', '0:观看记录更新失败，请稍后重试');
                }
            }
            
            $this->user_id = $userId;
            $this->film_id = $filmId;
            $this->type = $filmType;
            $this->title = $filmTitle;
            $this->cover = $filmCover;
            $this->number = $epNum;
            $this->route_id = $routeId;
            $this->url = $url;
            if(!$this->save()) {
                return $this->addError('', '0:观看记录添加失败，请稍后重试');
            }
        }else if($Record && $Record->status == Yii::$app->params['state_code']['status_delete']) {
            $Record->number = $epNum;
            $Record->status = Yii::$app->params['state_code']['status_normal'];
            if(!$Record->save()) {
                return $this->addError('', '0:观看记录更新失败，请稍后重试');
            }
        }
        else {
            $Record->number = $epNum;
            $Record->title = $filmTitle;
            $Record->cover = $filmCover;
            $Record->route_id = $routeId;
            $Record->url = $url;
            if(!$Record->save()) {
                return $this->addError('', '0:观看记录更新失败，请稍后重试');
            }
        }
        return true;
    }
    
    /**
     * 获取观影记录
     * @param int $userId 用户ID
     * @param int $page 当前页码
     * @param int $pagesize 每页显示记录数
     * @return array
     */
    public function getList($userId, $page = 1, $pagesize = 50) {
        if(!is_numeric($userId) || !is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '-4:参数格式有误');
        }
        $userId = $userId > 0 ? $userId : Cache::hget('id');
        $select = 'user_id,film_id,type,title,cover,number,route_id,url,create_time';
        $params = ['user_id'=>$userId, 'status'=>Yii::$app->params['state_code']['status_normal']];
        $order = 'update_time desc';
        $result = $this->getListData($select, $page, $pagesize, $params, $order);
        // 处理影视剧类型
        $Route = new Route();
        foreach($result['rows'] as $key=>$item) {
            $result['rows'][$key]['number'] = '已观看到' . $item['number'] . '集';
            $result['rows'][$key]['cover'] = Tool::connectPath(Yii::$app->params['image_domain'], $item['cover']);
            $result['rows'][$key]['create_time'] = substr($item['create_time'], 0, 4) == date('Y') ? substr($item['create_time'], 5, 5) : substr($item['create_time'], 0, 10);
            $result['rows'][$key]['route'] = $Route->getFullName($item['route_id']);
            unset($result['rows'][$key]['route_id']);
        }
        return $result;
    }
    
    /**
     * 删除观影历史记录
     * @param int $filmIds 影视剧ID
     * @return boolen
     */
    public function del($filmIds) {
        $loginUid = Cache::hget('id');
        if(empty($filmIds)) {
            return $this->addError('', '-3:请选择您要删除的观影记录');
        }
        $filmIdArr = explode('/', $filmIds);
        foreach($filmIdArr as $filmId) {
            if(empty($filmId)) {
                continue;
            }
            if(!is_numeric($filmId) || $filmId < 0) {
                return $this->addError('', '-4:参数格式有误，请确认');
            }
            $Record = $this->findByCondition(['user_id'=>$loginUid, 'film_id'=>$filmId, 'status'=>Yii::$app->params['state_code']['status_normal']])->one();
            if(!$Record || $Record->status == Yii::$app->params['state_code']['status_delete']) {
                return $this->addError('', '-7:观影历史不存在或已删除');
            }
            $Record->status = Yii::$app->params['state_code']['status_delete'];
            if(!$Record->save()) {
                return $this->addError('', '0:观影记录删除失败，请稍后重试');
            }
        }
        return true;
    }
    
    
    
    
    
    
    
}

