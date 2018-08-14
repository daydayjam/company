<?php
/**
 * 附件类
 * @author ztt
 * @date 2017/11/3
 */
namespace app\models;

use Yii;
use app\components\ActiveRecord;

class Attachment extends ActiveRecord {
    
    /**
     * 上传base64编码图片
     * @param string $imgBase64 base64编码图片字符串
     * @param string $type 图片类型，avatar=头像；normal=普通图片；chatroom=聊天室相关
     * @return array|boolen 图片信息包含长宽路径
     */
    public function uploadBase64Img($imgBase64, $type) {
        $uploadPath = Yii::$app->params['upload']['path'];
        $config = Yii::$app->params['upload']['image'];
        //判断上传目录是否存在
        $subDir = '/' . $type . '/' . date('Y/m');
        $uploadDir = $uploadPath . '/uploadImage' .  $subDir;
        $this->writeLog($uploadDir);
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        //检查目录是否可写
        if(!is_writable($uploadDir)) {
            $this->addError('', '-400:当前目录不可用');
            return false;
        }
        //判断大小是否超限
        $size = strlen(@file_get_contents($imgBase64))/1024;
        if($size > $config[$type]['max_size']) {
            $this->addError('', '-401:上传图片大小超限，应小于'.$config[$type]['max_size'].'m');
            return false;
        }
        //判断图片类型
//        if(!preg_match('/^(data:\s*image\/(\w+);base64,)/', $imgBase64, $match)){
//           $this->addError('', '-4:当前上传图片格式不正确');
//           return false;
//        }
        $ext = 'jpg';
        $imgTypes = explode(',', $config['img_type']);
        if(!in_array($ext, $imgTypes)) {
            $this->addError('', '-402:当前上传图片类型不正确，允许类型为' . $config['img_type']);
            return false;
        }
        //判断图片大小是否超限，否则缩略
        $newName = $this->getRandName($uploadDir, $ext) . '.' . $ext;
        $imgPath = $uploadDir . $newName;
        $img = base64_decode($imgBase64);
        if(!file_put_contents($imgPath, $img)) {
            $this->addError('', '-403:图片上传失败');
            return false;
        }
//        $this->writeLog('125:'.time());
        list($width, $height) = getimagesize($imgPath);
        if($width > $config[$type]['max_width'] || $height > $config[$type]['max_height']) {
            $thumbDir = $uploadPath . '/thumbImage'  . $subDir;
            if(!is_dir($thumbDir)) {
                mkdir($thumbDir, 0777, true);
            }
            //检查目录是否可写
            if(!is_writable($thumbDir)) {
                $this->addError('', '-400:当前目录不可用');
                return false;
            }
            $dest = $thumbDir . '/' . $newName;
//            $this->writeLog('126:'.time());
            if($this->createThumb($imgPath, $dest, $config[$type]['max_width'], $config[$type]['max_height'])) {
//                $this->writeLog('127:'.time());
                list($picWidth, $picHeight) = getimagesize($dest);
                $result = [
                    'path' => '/thumbImage' . $subDir . $newName,
                    'width' => $picWidth,
                    'height' => $picHeight
                ];
                return $result;
            }
//            $this->writeLog('127:'.time());
        }
        
        $result = [
            'path' => '/uploadImage' . $subDir . $newName,
            'width' => $width,
            'height' => $height
        ];
        return $result;  
    }
    
    /**
     * 上传网络图片
     * @param type $urlPath
     * @param type $type
     * @return boolean|string
     */
    public function saveUrlImg($urlPath, $type) {
        $ch = curl_init($urlPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $img = curl_exec($ch);
        curl_close($ch);
        
        $uploadPath = Yii::$app->params['upload']['path'];
        //判断上传目录是否存在
        $subDir = '/' . $type . '/' . date('Y/m');
        $uploadDir = $uploadPath . '/uploadImage' .  $subDir;
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        //检查目录是否可写
        if(!is_writable($uploadDir)) {
            $this->addError('', '-400:当前目录不可用');
            return false;
        }
        $ext = 'jpg';
        $newName = $this->getRandName($uploadDir, $ext) . '.' . $ext;
        $imgPath = $uploadDir . $newName;
        if($fp = fopen($imgPath,'w')){
                $finalPath = '/uploadImage' .  $subDir . $newName;
                fwrite($fp, $img);
                fclose($fp);
        }else{
                $finalPath = "system/default_avatar.png";
        }
        return $finalPath;
    }
    
   /** 
    *	按比例生成缩略图
    *	$source：原图片路径
    *	$dest：缩略图保存路径
    *	$width：缩略图的最大宽度
    *	$height：缩略图的最大高度
    */
    private function createThumb($source, $dest, $width, $height){
        $param = array(
            'type' => 'fit',
            'width' => $width,
            'height' => $height,
        );
        
        $obj = new PicThumb(Yii::$app->params['upload']['path']."/PicThumb.log");
        $obj->set_config($param);
        $flag = $obj->create_thumb($source, $dest);
        return $flag;
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
    
    /**
     * 获取文件扩展名
     * @param string $fileName 文件名
     * @return string 扩展名
     */
    private function getExt($fileName) {
        return substr($fileName, strrpos($fileName, '.'));
    }
    
}

