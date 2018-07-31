<?php
/**
 * 附件类
 * @author ztt
 * @date 2017/11/3
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;
use app\models\Cache;

class Attachment extends ActiveRecord {
    
    /**
     * 获取当前表名
     * @return string 表名
     */
    public static function tableName() {
       return '{{%attachment}}';
    }
    
    /**
     * 上传base64编码图片
     * @param string $imgBase64 base64编码图片字符串
     * @param string $type 图片类型，avatar=头像；normal=普通图片；chatroom=聊天室相关
     * @return array|boolen 图片信息包含长宽路径
     */
    public function uploadImg($inputName, $type = 'ocr') {
        $loginUserId = Cache::hget('user_id') ? Cache::hget('user_id') : 0;
        $uploadPath = Yii::$app->params['upload']['path'];
        $config = Yii::$app->params['upload']['image'];
        //判断上传目录是否存在
        $subDir = '/' . $type . '/' . date('Y/m');
        $uploadDir = $uploadPath . '/uploadImage' .  $subDir;
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        //检查目录是否可写
        if(!is_writable($uploadDir)) {
            return $this->addError('', '600:当前目录不可用');
        }
        $fileInfo = pathinfo($_FILES[$inputName]['name']);
        $ext = strtolower($fileInfo['extension']);
        $fileTypeArr = explode(',', $config['img_type']);
        if (!in_array($ext, $fileTypeArr)) {
            return $this->addError('', '601:附件格式错误:' . $ext . ';类型列表：' . json_encode($fileTypeArr));
        }
        $fileSize = $_FILES[$inputName]['size'] / 1024;
        if($fileSize > $config['ocr']['max_size']) {
            return $this->addError('', '603:图片尺寸过大');
        }
        $newName = $this->getRandName($uploadDir, $fileInfo['extension']);
        if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $uploadDir . $newName . '.' . $fileInfo['extension'])) {
            return $this->addError('', '602:附件移动失败');
        }
        $this->user_id = $loginUserId;
        $this->ext = $fileInfo['extension'];
        $this->size = $_FILES[$inputName]['size'];
        $this->name = $fileInfo['filename'];
        $this->path = $uploadDir . $newName . '.' . $fileInfo['extension'];
        if (!$this->save()) {
            return $this->addError('', '400:数据保存失败');
        }
        return $this->path;  
    }
    
    /**
     * 文件重命名
     * @param string $dir 附件目录
     * @param string $ext 附件扩展名
     * @return string 新文件名
     */
    private function getRandName($dir, $ext) {
        $time = explode('.', microtime());
        $time = end($time);
        $newName = '/' . date('dHis') . $time . rand(1000, 9999);
        $newName = str_replace(' ', '', $newName);
        if (is_file($dir . $newName . '.' . $ext)) {
            return $this->getRandName($dir, $ext);
        } else {
            return $newName;
        }
    }
    
}


