<?php
/**
 * 首页模型类
 * @author ztt
 * @date 2018/01/09
 */
namespace app\models;

class Index {
    
    /**
     * 获取数量
     * @param object $Object 对象
     * @param int $isAdd 是否为新增数据 1=是， 0=不是
     * @return type
     */
    public function getCnt($Object, $isAdd = 1) {
        $sql = 'select count(*) as cnt from ' . $Object->tableName();
        if($isAdd) {
            $sql .= ' where unix_timestamp(create_time) > unix_timestamp(DATE_FORMAT(NOW(),"%Y-%m-%d"))';
        }
        $record = $Object->findBySql($sql)->asArray()->one();
        return $record['cnt'];
    }
}
