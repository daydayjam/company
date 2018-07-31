<?php
/**
 * 版本兼容类
 * @author ztt
 * @date 2018/01/05
 */

namespace app\models;

use Yii;

class Compatible {
    
    /**
     * 更改list中的字段名
     * @param array $info 包含字段的数组
     * @param string $version 版本号
     * @param string $oldFieldName 旧版本字段名
     * @param string $newFieldName 新版本字段名
     * @return void
     */
    public function updateListField(&$list, $version, $oldFieldName, $newFieldName) {
        if($version == Yii::$app->params['old_version_01']) {
            foreach($list as $key=>$item) {
                if(isset($list[$key][$newFieldName]) && $list[$key][$newFieldName] == 0) {
                    $list[$key][$oldFieldName] = $list[$key][$newFieldName];
                    unset($list[$key][$newFieldName]);
                }
            }
        }
    }
    
    /**
     * 更改info中的字段名
     * @param array $info 包含字段的数组
     * @param string $version 版本号
     * @param string $oldFieldName 旧版本字段名
     * @param string $newFieldName 新版本字段名
     * @return void
     */
    public function updateField(&$info, $version, $oldFieldName, $newFieldName) {
        if($version == Yii::$app->params['old_version_01']) {
            if(isset($info[$newFieldName]) && $info[$newFieldName] == 0) {
                $info[$oldFieldName] = $info[$newFieldName];
                unset($info[$newFieldName]);
            }
        }
    }
}
