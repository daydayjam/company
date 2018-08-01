<?php
/**
 * 测试控制器类
 * @author ztt
 * @date 2018/03/12
 */

namespace app\controllers;

use app\components\Controller;

class TestController extends Controller {
    public function actionA() {
        $url = 'http://dev.crawler.leiyu.tv/film/savefilm';
        $data = [
            'data'=>'{"resource":"1","title":"她很漂亮","type":"2","status":"更新到20集","main_actor":[],"director":[],"tag":["国产剧"],"cover":"http://i3.letvimg.com/lc04_isvrs/201606/14/12/48/1cfcf7c2-678b-4687-960e-1f8f6067d8f4.jpg#err2018-04-27","area":"","year":"2017","summary":"","play_route":["乐视视频","优酷视频","腾讯视频","无双云播④","无双视频③","芒果视频"],"play_source":[{"way":[{"num":"第1集","url":"http://v.wsyx668.com/play/23159-1-1.html"},{"num":"第2集","url":"http://v.wsyx668.com/play/23159-1-2.html"},{"num":"第3集","url":"http://v.wsyx668.com/play/23159-1-3.html"},{"num":"第4集","url":"http://v.wsyx668.com/play/23159-1-4.html"},{"num":"第5集","url":"http://v.wsyx668.com/play/23159-1-5.html"},{"num":"第6集","url":"http://v.wsyx668.com/play/23159-1-6.html"},{"num":"第7集","url":"http://v.wsyx668.com/play/23159-1-7.html"},{"num":"第8集","url":"http://v.wsyx668.com/play/23159-1-8.html"},{"num":"第9集","url":"http://v.wsyx668.com/play/23159-1-9.html"},{"num":"第10集","url":"http://v.wsyx668.com/play/23159-1-10.html"},{"num":"第11集","url":"http://v.wsyx668.com/play/23159-1-11.html"},{"num":"第12集","url":"http://v.wsyx668.com/play/23159-1-12.html"},{"num":"第13集","url":"http://v.wsyx668.com/play/23159-1-13.html"},{"num":"第14集","url":"http://v.wsyx668.com/play/23159-1-14.html"},{"num":"第15集","url":"http://v.wsyx668.com/play/23159-1-15.html"},{"num":"第16集","url":"http://v.wsyx668.com/play/23159-1-16.html"}]},{"way":[{"num":"第01集","url":"http://v.wsyx668.com/play/23159-2-1.html"},{"num":"第02集","url":"http://v.wsyx668.com/play/23159-2-2.html"},{"num":"第03集","url":"http://v.wsyx668.com/play/23159-2-3.html"},{"num":"第04集","url":"http://v.wsyx668.com/play/23159-2-4.html"},{"num":"第05集","url":"http://v.wsyx668.com/play/23159-2-5.html"},{"num":"第06集","url":"http://v.wsyx668.com/play/23159-2-6.html"},{"num":"第07集","url":"http://v.wsyx668.com/play/23159-2-7.html"},{"num":"第08集","url":"http://v.wsyx668.com/play/23159-2-8.html"}]},{"way":[{"num":"《她很漂亮》首支预告片 郭京飞壁咚张歆艺","url":"http://v.wsyx668.com/play/23159-3-1.html"}]},{"way":[{"num":"第01集","url":"http://v.wsyx668.com/play/23159-4-1.html"},{"num":"第02集","url":"http://v.wsyx668.com/play/23159-4-2.html"},{"num":"第03集","url":"http://v.wsyx668.com/play/23159-4-3.html"},{"num":"第04集","url":"http://v.wsyx668.com/play/23159-4-4.html"},{"num":"第05集","url":"http://v.wsyx668.com/play/23159-4-5.html"},{"num":"第06集","url":"http://v.wsyx668.com/play/23159-4-6.html"},{"num":"第07集","url":"http://v.wsyx668.com/play/23159-4-7.html"},{"num":"第08集","url":"http://v.wsyx668.com/play/23159-4-8.html"},{"num":"第09集","url":"http://v.wsyx668.com/play/23159-4-9.html"},{"num":"第10集","url":"http://v.wsyx668.com/play/23159-4-10.html"},{"num":"第11集","url":"http://v.wsyx668.com/play/23159-4-11.html"},{"num":"第12集","url":"http://v.wsyx668.com/play/23159-4-12.html"},{"num":"第13集","url":"http://v.wsyx668.com/play/23159-4-13.html"},{"num":"第14集","url":"http://v.wsyx668.com/play/23159-4-14.html"},{"num":"第15集","url":"http://v.wsyx668.com/play/23159-4-15.html"},{"num":"第16集","url":"http://v.wsyx668.com/play/23159-4-16.html"},{"num":"第17集","url":"http://v.wsyx668.com/play/23159-4-17.html"},{"num":"第18集","url":"http://v.wsyx668.com/play/23159-4-18.html"}]},{"way":[{"num":"第01集","url":"http://v.wsyx668.com/play/23159-5-1.html"},{"num":"第02集","url":"http://v.wsyx668.com/play/23159-5-2.html"},{"num":"第03集","url":"http://v.wsyx668.com/play/23159-5-3.html"},{"num":"第04集","url":"http://v.wsyx668.com/play/23159-5-4.html"},{"num":"第05集","url":"http://v.wsyx668.com/play/23159-5-5.html"},{"num":"第06集","url":"http://v.wsyx668.com/play/23159-5-6.html"},{"num":"第07集","url":"http://v.wsyx668.com/play/23159-5-7.html"},{"num":"第08集","url":"http://v.wsyx668.com/play/23159-5-8.html"},{"num":"第09集","url":"http://v.wsyx668.com/play/23159-5-9.html"},{"num":"第10集","url":"http://v.wsyx668.com/play/23159-5-10.html"},{"num":"第11集","url":"http://v.wsyx668.com/play/23159-5-11.html"},{"num":"第12集","url":"http://v.wsyx668.com/play/23159-5-12.html"},{"num":"第13集","url":"http://v.wsyx668.com/play/23159-5-13.html"},{"num":"第14集","url":"http://v.wsyx668.com/play/23159-5-14.html"},{"num":"第15集","url":"http://v.wsyx668.com/play/23159-5-15.html"},{"num":"第16集","url":"http://v.wsyx668.com/play/23159-5-16.html"},{"num":"第17集","url":"http://v.wsyx668.com/play/23159-5-17.html"},{"num":"第18集","url":"http://v.wsyx668.com/play/23159-5-18.html"}]},{"way":[{"num":"第1集","url":"http://v.wsyx668.com/play/23159-6-1.html"},{"num":"第2集","url":"http://v.wsyx668.com/play/23159-6-2.html"},{"num":"第3集","url":"http://v.wsyx668.com/play/23159-6-3.html"},{"num":"第4集","url":"http://v.wsyx668.com/play/23159-6-4.html"},{"num":"第5集","url":"http://v.wsyx668.com/play/23159-6-5.html"},{"num":"第6集","url":"http://v.wsyx668.com/play/23159-6-6.html"},{"num":"预告第7集","url":"http://v.wsyx668.com/play/23159-6-7.html"},{"num":"预告第8集","url":"http://v.wsyx668.com/play/23159-6-8.html"}]}]}'
        ];
        $curl = curl_init(); // 启动一个CURL会话      
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址              
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容      
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转      
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer      
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求      
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包      
//        curl_setopt($curl, CURLOPT_TIMEOUT, 150); // 设置超时限制防止死循环      
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回      
        
        $tmpInfo = curl_exec($curl); // 执行操作      
        if (curl_errno($curl)) {      
           echo 'Errno'.curl_error($curl);      
        }      
//        print_r(curl_getinfo($curl));die;
        curl_close($curl); // 关键CURL会话      
        return $tmpInfo; // 返回数据      
    }
    
    public function actionB() {
        $str = '{"name":"拜托了冰箱第四季","ftype":"3","ep_status":"20180509期","main_actor":["何炅","王嘉尔","王大陆","马天宇"],"director":["未知"],"type":["综艺"],"cover":"http://img.wsyx668.com/upload/vod/2018-04-30-0/152504770931.jpg","area":"大陆","year":"2018","summary":"《拜托了冰箱4》是腾讯视频出品的一档明星美食脱口秀节目。节目由何炅与王嘉尔一起担任主持，每期2位明星大咖和自己的冰箱一起来到节目现场，通过揭秘冰箱来与6位性格各异的主厨畅聊美食生活、八卦趣事。每期两位主厨利用明星冰箱食材进行15分钟创意料理对决。","play_route":["无双云播④","无双视频③"],"play_source":[{"way":[{"num":"20180425期","url":"http://v.wsyx668.com/play/131695-1-1.html"},{"num":"20180502期","url":"http://v.wsyx668.com/play/131695-1-2.html"},{"num":"20180509期","url":"http://v.wsyx668.com/play/131695-1-3.html"}]},{"way":[{"num":"180502期","url":"http://v.wsyx668.com/play/131695-2-1.html"}]}]}';
        $data = json_decode($str, true);
        
        
        
        // 整合播放线路和播放源
        $playArr = array_combine($data['play_route'], $data['play_source']);
        $newPlayArr = [];
        foreach($playArr as $key=>$item) {
            $newPlayArr[$key] = $item['way'];
        }
        if($data['ftype'] == 2 || $data['ftype'] == 4) {
            $this->tvAndAnimation($newPlayArr);
        }else if($data['ftype'] == 1) {
            $this->film($newPlayArr);
        }else {
            $this->variety($newPlayArr);
        }
        print_r($newPlayArr);
    }
    
    /**
     * 连续剧和动漫
     * @param type $data
     */
    public function tvAndAnimation(&$data) {
        foreach($data as $key1=>$arr) {
            foreach($arr as $key2=>$value) {
                // 去除预告片
                if(strpos($value['num'], '预告') > -1) {    
                    unset($data[$key1][$key2]);
                }else {
                    // 提取纯数字为剧集编号
                    if(count($arr) == 1) {
                        $data[$key1][$key2]['num'] = 1;
                    }else {
                        if(preg_match('/(\d+)/', $value['num'], $matches)) {
                            if($matches[1] < 10) {
                                $matches[1] = trim($matches[1], '0');
                            }
                            $data[$key1][$key2]['num'] = $matches[1];
                        }
                    }
                    $data[$key1][$key2]['title'] = $value['num'];
                }
            }
        }
    }
    
    /**
     * 电影
     * @param type $data
     */
    public function film(&$data) {
        foreach($data as $key1=>$arr) {
            if(count($arr) > 1) {
                unset($data[$key1]);
            }else {
                foreach($arr as $key2=>$value) {
                    $data[$key1][$key2]['num'] = 1;
                    $data[$key1][$key2]['title'] = $value['num'];
                }
            }
        }
    }
    
    /**
     * 综艺
     * @param type $data
     */
    public function variety(&$data) {
        foreach($data as $key1=>$arr) {
            foreach($arr as $key2=>$value) {
                $data[$key1][$key2]['num'] = $key2 + 1;
                $data[$key1][$key2]['title'] = $value['num'];
            }
        }
    }
    
}
