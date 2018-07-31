<?php

/**
 * 工具类
 * @date 2018/06/05
 * @author ztt
 */
namespace app\models;
use Yii;

class Tool {
    
    /**
     * 生成token
     * @param $prestr 需要加密的字符串
     * @param $key 私钥
     * @return 签名结果
     */
    public static function md5Sign($prestr, $key) {
        $prestr = $prestr . $key;
        return md5($prestr);
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
     * 移除首位空白字符，包括全角空格
     * @param type $str
     * @return type
     */
    public static function mb_trim($str) {
        $str = mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $str);  
        $str = mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $str);  
        return $str; 
    }
    
    /**
     * 移除首空白字符，包括全角空格
     * @param type $str
     * @return type
     */
    public static function mb_ltrim($str) {
        return mb_ereg_replace('^(([ \r\n\t])*(　)*)*', '', $str); 
    }
    
    /**
     * 移除尾空白字符，包括全角空格
     * @param type $str
     * @return type
     */
    public static function mb_rtrim($str) {
        return mb_ereg_replace('(([ \r\n\t])*(　)*)*$', '', $str); 
    }
    
    public static function trimAll($str) {
        return mb_ereg_replace('(([ \r\n\t])*(　)*)*', '', $str); 
    }
    
    /**
     * 移除标点符号及注释
     * @param type $str
     * @return type
     */
    public static function trimMarkAndAnno($str, $isAnno = true) {
        $str = $isAnno ? mb_ereg_replace('\(.*\)|（.*）', '', $str) : $str; 
        $char = "·?(),，。、！？：;．.；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）～（）#";
        $reg = "/[[:punct:]]|['.$char.']|[ ]{1,}/iu";
        return mb_ereg_replace($reg, '', $str); 
    }
    
    public static function splitByMark($str){ 
        $char = "，。、！？：；﹑•＂…‘’“”〝〞∕¦‖—　〈〉﹞﹝「」‹›〖〗】【»«』『〕〔》《﹐¸﹕︰﹔！¡？¿﹖﹌﹏﹋＇´ˊˋ―﹫︳︴¯＿￣﹢﹦﹤‐­˜﹟﹩﹠﹪﹡﹨﹍﹉﹎﹊ˇ︵︶︷︸︹︿﹀︺︽︾ˉ﹁﹂﹃﹄︻︼（）～（）#";
        $reg = "/[[:punct:]]|['.$char.']|[ ]{1,}/iu";
        $arr = preg_split($reg, $str);
        return array_values(array_filter($arr));
    } 
    
    /**
     * 分词
     * @param type $str
     * @return type
     */
    public static function scwsWord($str) {
        $so =scws_new();  
        $so->set_charset('utf8');  
        $so->set_dict('/usr/local/scws/etc/dict.utf8.xdb');  
        $so->set_rule('/usr/local/scws/etc/rules.utf8.ini');  
        $so->set_ignore(1);  
        $so->set_duality(1);
        $so->set_multi(1);  
        $so->send_text($str);  
        $data=array();  
        while ($tmp = $so->get_result())  
        {  
              $data[]=$tmp;  
        }  
        $so->close();  
        $arr = [];
        foreach ($data as $key=>$value) {  
            foreach($value as $k=>$v){  
                $arr[] = $v['word'];
            }  
        } 
        return $arr;
    }
    
    /**
     * 判断是否是今天
     * @param int $time 是否是今天
     * @return boolen true=是
     */
    public static function isToday($time) {
        $date = date('Y-m-d', $time);
        return $date == date('Y-m-d');
    }
    
    /**
     * 获取周期开始结束时间戳
     * @param int $periodUnit
     */
    public static function getPeriod($periodUnit = 1) {
        $result = [];
        switch ($periodUnit) {
            case Yii::$app->params['code']['period_unit']['week']: // 周
                //当前日期  
                $sdefaultDate = date("Y-m-d");  
                //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期  
                $first=1;  
                //获取当前周的第几天 周日是 0 周一到周六是 1 - 6  
                $w=date('w',strtotime($sdefaultDate));  
                $startTime = date('Y-m-d', strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
                $result['start_time'] = strtotime($startTime);  
                $result['end_time'] = strtotime(date('Y-m-d', strtotime($startTime . '+ 6 days')));
                break;
            case Yii::$app->params['code']['period_unit']['month']: // 月
                $result['start_time'] = mktime(0,0,0,date('m'),1,date('Y'));
                $result['end_time'] = mktime(23,59,59,date('m'),date('t'),date('Y'));
                break;
            case Yii::$app->params['code']['period_unit']['year']:  // 年
                $result['start_time'] = mktime(0,0,0,1,1,date('Y'));
                $result['end_time'] = mktime(23,59,59,12,31,date('Y'));
                break;
        }
        return $result;
    }
    
    // 添加书名号
    public static function addBookTitleMark($title) {
        return '《' . $title . '》';
    }
    
    /**
     * 去除代
     * @param type $year
     * @return type
     */
    public static function removeDai($year) {
        if($year) {
           if($index = mb_strpos($year, '代') !== false && !in_array($year, ['现代', '五代'])) {
                $year = mb_substr($year, 0, $index);
            } 
        }else {
            $year = '不详';
        }
        return $year;
    }
    
    /**
     * 竖排显示
     */
    public static function verticalRow($content) {
        $contentArr = Tool::splitByMark($content);
        foreach($contentArr as $key=>$item) {
            if(mb_strlen($item) > 10) {
                $contentArr[$key] = mb_substr($item, 0, 10);
            }
        }
        return implode('\n', $contentArr);
    }
    
}

