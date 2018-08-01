<?php
/**
 * 工具类，包含数据处理公共方法
 * @author ztt
 * @date 2017/10/31
 */
namespace app\models;

use Yii;

class Tool {
    
    /**
     * 域名路径组合
     * @param string $domain 域名
     * @param string $path 路径
     * @return string 连接后的路径地址
     */
    public static function connectPath($domain, $path) {
        $newStr = $path;
        if(!empty($path) && !self::startWith($path, 'http')) {
            $newStr = $domain . $path;
        }
        return $newStr;
    }
    
    /**
     * 获取MD5双重加密字符串
     * @param string $str 待加密的字符串
     * @return string 加密后的字符串
     */
    public static function md5Double($str) {
        return md5(md5($str));
    }
    
    /**
     * 检测手机号码是否合法
     * @param string $mobile 手机号
     * @return int 0=不合法；1=合法
     */
    public static function isMobile($mobile) {
        $regx1 = '/^1[3|4|5|6|7|8|9]\d{9}$/';
        $regx2 = '/^926\d{8}$/';
        return preg_match($regx1, $mobile) || preg_match($regx2, $mobile);
    }
    
    /**
     * 检测密码是否合法
     * @param string $pwd 密码
     * @return int 0=不合法；1=合法
     */
    public static function isPwd($pwd) {
        $regx = '/^[\w\-\/:;\(\)\$&@"\[\]\{\}#%\^\*\+=_\|~<>€£¥•\.,\?\!\']{8,}$/';
        return preg_match($regx, $pwd);
    }
    
    /**
     * 验证验证码是否合法
     * @param string $code 验证码
     * @param int $length 验证码长度
     * @param int $type 验证码类型，1=仅数字，2=数字和英文组合
     * @return int 0=不合法；1=合法
     */
    public static function isCode($code, $length = 4, $type = 1) {
        $regx = '';
        if($type == 1) {
            $regx = '/^\d{' . $length . '}$/';
        }else {
            $regx = '/^\w{' . $length .'}$/';
        }
        return preg_match($regx, $code);
    }
    
    /**
     * 获取随机字符串
     * @param int $length 字符串长度
     * @param int $type 验证码类型，1=仅数字，2=数字和英文组合
     * @return string 随机字符串
     */
    public static function getRandom($length = 4, $type = 1) {
        $str = '';
        if($type == 1) {
            $str = '0123456789';
        }else {
            $str = '0123456789abcdefghijklmnopqistuvwxyzABCDEFGHIJKLMNOPQISTUVWXYZ';
        }
        $random = '';
        $count = strlen($str) - 1;
        for($i=0; $i<$length; $i++) {
            $random .= substr($str, rand(0, $count), 1);
        }
        return $random;
    }
    
    /**
     * 获取两时间段的时间差
     * @param datetime $date1 时间1 格式： 0000-00-00 00:00:00
     * @param datetime $date2 时间2 格式： 0000-00-00 00:00:00
     * @return int 时间差，秒数
     */
    public static function getTimeDiff($date1, $date2) {
        return abs(strtotime($date1) - strtotime($date2));
    }
    
    /**
     * 判断图片字符串是否为base64编码字符串
     * @param string $path 图片字符串
     * @return int 0=不合法；1=合法
     */
    public static function isBase64Img($path) {
        return preg_match('/^data:image\/.*$/', $path);
    }
    
    /**
     * 检查日期是否合法
     * @param string $date 年月日时间
     * @param string $format 格式
     * @return boolen
     */
    public static function isDate($date, $format = 'Y-m-d') {
        $unixTime = strtotime($date);
        if(!$unixTime) {
            return false;
        }
        if(date($format, $unixTime) != $date) {
            return false;
        }
        return true;
    }
    
    /**
     * 判断是否以某字符串开头
     * @param string $haystack 要判断的字符串
     * @param string $needle 需要查找的字符串开头
     * @param boolen $case 是否区分大小写，false=否
     * @return boolen true=是
     */
    public static function startWith($haystack, $needle, $case = false) {
        if($case) {
            return strcmp(substr($haystack, 0, strlen($needle)), $needle) === 0;
        }
        return strcasecmp(substr($haystack, 0, strlen($needle)), $needle) === 0;
    }
    
    /**
     * 获取唯一码，用于验证操作是否合法，是否从上一页面来
     * @param type $tel
     * @param type $type
     */
    public static function getUniqueValue($tel, $type) {
        return 'yanyan926_' . $tel . '_' . $type;
    }
    
    /**
     * 透传过程中数组转字符串
     * @param array $recordArray 透传数组
     * @param string $split1 分隔符
     * @param string $split2 分隔符
     * @return string 
     */
    public static function array2string($recordArray, $split1 = '=', $split2 = ';'){
        $string = '';
        $i = 0;
        foreach($recordArray as $key => $value){
            $string .= $key . $split1 . $value;
            if( $i++ < count( $recordArray )-1 ){
                $string .= $split2;
            }
        }
        return $string;
    }
    
    /**
     * 截取UTF8编码字符串从首字节开始指定宽度(非长度), 适用于字符串长度有限的如新闻标题的等宽度截取
     * 中英文混排情况较理想. 全中文与全英文截取后对比显示宽度差异最大,且截取宽度远大越明显.
     * @param string $str	UTF-8 encoding
     * @param int[option] $width 截取宽度
     * @param string[option] $end 被截取后追加的尾字符
     * @param float[option] $x3<p>
     * 	3字节（中文）字符相当于希腊字母宽度的系数coefficient（小数）
     * 	中文通常固定用宋体,根据ascii字符字体宽度设定,不同浏览器可能会有不同显示效果</p>
     *
     * @return string
     * @author waiting
     * http://waiting.iteye.com
     */
    public static function u8_title_substr($str, $width = 0, $end = '...', $x3 = 2) {
        global $CFG; // 全局变量保存 x3 的值
        if ($width <= 0 || $width >= strlen($str)) {
                return $str;
        }
        $arr = str_split($str);
        $len = count($arr);
        $w = 0;
        $width *= 10;

        // 不同字节编码字符宽度系数
        $x1 = 11;	// ASCII
        $x2 = 16;
        $x3 = $x3===0 ? ( $CFG['cf3']  > 0 ? $CFG['cf3']*10 : $x3 = 21 ) : $x3*10;
        $x4 = $x3;
	
        // http://zh.wikipedia.org/zh-cn/UTF8
        $finalX = false;
        $e = $end;
        for ($i = 0; $i < $len; $i++) {
            if ($w >= $width) {
                $e = $end;
                break;
            }
            $c = ord($arr[$i]);
            if ($c <= 127) {
                $w += $x1;
            }
            elseif ($c >= 192 && $c <= 223) {	// 2字节头
                $w += $x2;
                $i += 1;
                $finalX = true;
            }
            elseif ($c >= 224 && $c <= 239) {	// 3字节头
               $finalX = true;
                $w += $x3;
                $i += 2;
            }
            elseif ($c >= 240 && $c <= 247) {	// 4字节头
                $finalX = true;
                $w += $x4;
                $i += 3;
            }
        }
        $i = !$finalX ? $i+1 : $i;
        return implode('', array_slice($arr, 0, $i) ). $e;
    }
    
   /**
    * 更改文字大小
    * @param string $text 需要变换的内容
    * @param int $fontSize 字体大小
    * @param int $lineHeight 行高
    * @return string 变换后的内容
    */
    public static function dealFont($text, $fontSize = 16, $lineHeight = 28) {
        if($text) {
            $text = str_replace('<br>', '', $text);
            $text = '<span style="font-size:' . $fontSize . 'px;line-height:' . $lineHeight . 'px;">' 
                    . $text . '</span>';
        }
        return $text;
    }
    
    
}

