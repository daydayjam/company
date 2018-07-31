<?php

/**
 * 背诵记录操作类
 * @date 2018/06/27
 * @author ztt
 */
namespace app\models;
use Yii;
use app\components\ActiveRecord;

class Keep extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%keep}}';
    }
    
    /**
     * 添加程序停留时长
     * @param int $keepTime 分钟数
     * @return boolen true=添加成功
     */
    public function add($keepTime, $keepDate) {
        $loginUserId = Cache::hget('user_id');
        if(empty($keepTime)) {
            return $this->addError('', '301:停留时间不可为空');
        }
        if(!is_numeric($keepTime) || $keepTime < 0) {
            return $this->addError('', '300:参数格式有误');
        }
        if($keepTime > 1440) {
            return $this->addError('', '300:当日停留时间有误');
        }
        $todayDate = strtotime($keepDate);
        $Record = $this->findByCondition(['user_id'=>$loginUserId, 'date_today'=>$todayDate])->one();
        if(!$Record) {
            $this->user_id = $loginUserId;
            $this->date_today = $todayDate;
            $this->keep_time = $keepTime;
            if(!$this->save()) {
                return $this->addError('', '400:更新时间失败');
            }
            return true;
        }
        if($Record->keep_time + $keepTime > 1440) {
            return $this->addError('', '300:当日停留时间有误');
        }
        $Record->keep_time = $Record->keep_time + $keepTime;
        if(!$Record->save()) {
            return $this->addError('', '400:更新时间失败');
        }
        return true;
    }
    
    /**
     * 
     * @param type $page
     * @param type $pagesize
     * @return type
     */
    public function getList($page = 1, $pagesize = 10) {
        $loginUserId = Cache::hget('user_id');
        if(!is_numeric($page) || !is_numeric($pagesize) || $page < 1 || $pagesize < 1) {
            return $this->addError('', '300:参数格式有误，请重试');
        }
        $limit = 'LIMIT ' . ($page - 1) * $pagesize . ',' . $pagesize;
        $sql = $this->getRankSql(self::tableName(), $limit);
        $rows = Yii::$app->db->createCommand($sql)->queryAll();
        $total = Yii::$app->db->createCommand($this->getRankSql(self::tableName()))->queryAll();
        
        $selfRowSql = 'SELECT * FROM (' . $this->getRankSql(self::tableName()) . ') as row_total WHERE row_total.user_id=' . $loginUserId;
        $selfInfo = Yii::$app->db->createCommand($selfRowSql)->queryOne();
        
        // 整合用户昵称头像
        Helper::mergeUserInfoToList($rows);
        Helper::mergeUserInfoToItem($selfInfo);
        $result = [
            'current_user' => $selfInfo,
            'total' => count($total),
            'rows' => $rows
        ];
        return $result;
    }
    
    /**
     * 获取统计数据
     * @param int $periodUint 周期单位 1=周；2=月；3=年
     * @param int $timeUint 时间单位 1=分钟；2=小时
     * @return array
     */
    public function getStatistic($periodUint = 1, $timeUint = 1) {
        $loginUserId = Cache::hget('user_id');
        // 获取签到天数、背诵总时间、已记住诗词、已背诵字数
        $result = [];
        $SignLogRecord = SignLog::find()->where(['user_id'=>$loginUserId])->orderBy('create_time desc')->limit(1)->one();
        $result['sign_day'] = isset($SignLogRecord) ? $SignLogRecord->sign_day : 0;
        $keepSql = 'select sum(keep_time) as total_time from tb_keep where user_id=:user_id';
        $keepRecord = $this->findBySql($keepSql, [':user_id'=>$loginUserId])->asArray()->one();
        $totalTime = $keepRecord ? $keepRecord['total_time'] : 0;
        $unit = $totalTime > 999 ? '小时' : '分钟';
        $totalTime = $totalTime > 999 ? number_format($totalTime/60, 1) : number_format($totalTime, 1);
        $result['total_time'] = ['time'=>(float)$totalTime, 'unit' => $unit];
        $reciteSql = 'select count(*) as count, sum(word_number) as word_number_total from tb_recite where user_id=:user_id and status=:status;';
        $reciteRecord = Recite::findBySql($reciteSql, [':user_id'=>$loginUserId, ':status'=>Yii::$app->params['code']['recite_status']['recited']])->asArray()->one();
//        print_r($reciteRecord);die;
        $result['recite_total'] = $reciteRecord ?(int)$reciteRecord['count'] : 0;
        $result['recite_word_total'] = $reciteRecord && $reciteRecord['word_number_total'] ? (int) $reciteRecord['word_number_total'] : 0;
        
        // 获取统计图表信息
        $result['chart_data']['week'] = $this->getData($loginUserId, Yii::$app->params['code']['period_unit']['week']);
        $result['chart_data']['month'] = $this->getData($loginUserId, Yii::$app->params['code']['period_unit']['month']);
        $result['chart_data']['year'] = $this->getData($loginUserId, Yii::$app->params['code']['period_unit']['year']);
        return $result;
    }
    
    /**
     * 获取图标数据
     * @param int $userId 用户ID
     * @param int $periodUint 周期单位 1=周；2=月；3=年
     * @param int $timeUint 时间单位 1=分钟；2=小时
     * @return array
     */
    public function getData($userId, $periodUint = 1) {
        $periodResult = [];
        $period = Tool::getPeriod($periodUint);
        
        $timeSql = 'select sum(keep_time) as total_time from '.self::tableName().' where user_id='.$userId.' and (date_today >= '.$period['start_time'].' and date_today <=  '.$period['end_time'].')';
        $timeResult = $this->findBySql($timeSql)->asArray()->one();
        $totalTime = $timeResult && $timeResult['total_time'] ? (float)$timeResult['total_time'] : 0;
        $periodResult['total_time'] = $totalTime > 999 ? (float)number_format($totalTime/60, 1) : (float)number_format($totalTime, 1);
        $periodResult['period_unit'] = Yii::$app->params['text']['period'][$periodUint];
        $periodResult['time_unit'] = $totalTime > 999 ? '小时' : '分钟'; // 1分钟；2小时
        
        $sql = 'select keep_time, date_today from '.self::tableName().' where user_id='.$userId.' and (date_today >= '.$period['start_time'].' and date_today <=  '.$period['end_time'].')';
        $result = $this->findBySql($sql)->asArray()->all();
//        print_r($result);die;
        switch ($periodUint) {
            case Yii::$app->params['code']['period_unit']['week']: // 周
                $dayResult = ['time'=>[], 'period'=>[]];
                $currentWeek = date('w') == 0 ? 7 : date('w');
                for($i=1; $i<=$currentWeek; $i++) {
                    $dayResult['time'][$i] = 0;
                    $dayResult['period'][$i] = strtotime(date('Y-m-d', strtotime('-' . ($currentWeek - $i) . ' days')));
                }
                foreach($result as $key=>$item) {
                    $currentDay = date('w', $item['date_today']) == 0 ? 7 : date('w', $item['date_today']);
//$currentDay = date('w', $item['date_today']);
                    $dayResult['time'][$currentDay] = $totalTime > 999 ? (float)number_format($item['keep_time']/60, 1) : (float)$item['keep_time'];
                }
		
                ksort($dayResult['time']);
                $dayResult['time'] = array_values($dayResult['time']);
                ksort($dayResult['period']);
                $dayResult['period'] = array_values($dayResult['period']);
                $periodResult['rows'] = $dayResult;
                break;
            case Yii::$app->params['code']['period_unit']['month']: // 月
                $monthResult = ['time'=>[], 'period'=>[]];  
                for($i=1; $i<date('j')+1; $i++) {
                    $monthResult['time'][$i] = 0;
                    $monthResult['period'][$i] = strtotime(date('Y').'-'.date('m').'-'.$i);
                }
                foreach($result as $key=>$item) {
                    $currentDay = date('j', $item['date_today']);
                    $monthResult['time'][$currentDay] = $totalTime > 999 ? (float)number_format($item['keep_time']/60, 1) : (float)$item['keep_time'];
                }
                
                ksort($monthResult['time']);
                $monthResult['time'] = array_values($monthResult['time']);
                ksort($monthResult['period']);
                $monthResult['period'] = array_values($monthResult['period']);
                $periodResult['rows'] = $monthResult;
                break;
            case Yii::$app->params['code']['period_unit']['year']:  // 年
                $yearResult = ['time'=>[], 'period'=>[]];
                $month = range(1, 12);
                foreach($result as $key=>$item) {
                    $currentMonth = date('n', $item['date_today']);
                    if(array_key_exists($currentMonth, $yearResult['time'])) {
                        $keepTime = $totalTime > 999 ? (float)number_format(($yearResult['time'][$currentMonth] + (float)number_format($item['keep_time']/60, 1)), 1) : (float)number_format(($yearResult['time'][$currentMonth] + $item['keep_time']), 1);
                        $yearResult['time'][$currentMonth] =  (float)number_format($keepTime, 1);
                    }else {
                        $yearResult['time'][$currentMonth] = $totalTime > 999 ? (float)number_format($item['keep_time']/60, 1) : (float)number_format($item['keep_time'], 1);
                        $yearResult['period'][$currentMonth] = strtotime(date('Y').'-'.$currentMonth);
                    }
                }
                foreach($month as $value) {
                    if(!array_key_exists($value, $yearResult['time']) && $value <= date('n')) {
                        $yearResult['time'][$value] = 0;
                        $yearResult['period'][$value] = strtotime(date('Y').'-'.$value);
                    }
                }
                ksort($yearResult['time']);
                $yearResult['time'] = array_values($yearResult['time']);
                ksort($yearResult['period']);
                $yearResult['period'] = array_values($yearResult['period']);
                $periodResult['rows'] = $yearResult;
                break;
        }
        return $periodResult;
    }
}

