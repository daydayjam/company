<?php
/**
 * 测试控制器类
 * @author ztt
 * @date 2017/10/30
 */
namespace app\controllers;

use app\components\Controller;
use app\models\Black;
use app\models\CommentT;
use app\models\FilmSpider;

class TestController extends Controller {
    public function actionA() {
        $connomains = array(
        'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
             'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
            'http://yytest.leiyu.tv/index.php?r=comment/del&safe_code=44f249b517ecadd309b2ba890797447b&code=6fd2841e3915e85e12bf8945fffd2a0f&from=1&cid=9',
        );
        $mh = curl_multi_init();
        foreach ($connomains as $i => $url) {
             $conn[$i]=curl_init($url);
              curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER,1);
              curl_multi_add_handle ($mh,$conn[$i]);
        }
        do { $n=curl_multi_exec($mh,$active); } while ($active);
        foreach ($connomains as $i => $url) {
              $res[$i]=curl_multi_getcontent($conn[$i]);
              curl_close($conn[$i]);
        }
        print_r($res);
        
    }
    
    public function actionC() {
        $url = 'http://crawler.yy.leiyu.tv/news/add';
        $data = [
            'data'=>'{
    "article_title":"最新电影资源推荐:黑暗之中",
    "article_author":"终极电影网",
    "weixin_name":"btdygod",
    "weixin_nickname":"终极电影网",
    "article_publish_time":"1529240435",
    "article_thumbnail":"http://static.shenjianshou.cn/image/508022-37b7798946ae83131b12961367ca396f.jpeg?realurl=http%3A%2F%2Fmmbiz.qpic.cn%2Fmmbiz_jpg%2FVJbRP1daDibW6QhRicptHnDRCanSiczOBktRsMgEDH0OwGdlAFTM5V66p1JLHVobqShPNHaulJe3SuhJBd9cfPbeA%2F0%3Fwx_fmt%3Djpeg",
    "article_brief":"热门电影电视剧资源在线免费观看最新电影，VlP电视剧，欢迎关注✔黑暗之中导演: 安东尼·拜恩编剧: 安东尼·",
    "article_content":"",
    "article_images":[
        "http://static.shenjianshou.cn/image/508022-dd9c8a19f892f80d3e730fd9345e137c?realurl=https%3A%2F%2Fmmbiz.qpic.cn%2Fmmbiz_jpg%2FVJbRP1daDibVuqxRKVrdpo4sxv1IJGQHpYN7Fib6DuGxulAwRYnKMvJTV29ubOQ6uMsWoHjOKsSNDN6zecF7HoEw%2F640%3Fwx_fmt%3Djpeg",
        "http://static.shenjianshou.cn/image/508022-09ed0e6736b764709e91ad8628c0080e?realurl=https%3A%2F%2Fmmbiz.qpic.cn%2Fmmbiz_gif%2FibXD5NiaOv6d3dHS5Xiaat2fzBxOylFFNQqTJ9BEFdWTUn9EG14R2HFE3DrzIfDPo0guwnJvayKZPlS9eMcmPicA5g%2F640%3Fwx_fmt%3Dgif"
    ],
    "article_origin_url":"https://mp.weixin.qq.com/s?timestamp=1533104306&src=3&ver=1&signature=mJ0kX3B7VBWXbAoTEnPmA42O8mVyNmeEoqxAWgQR*5j-wwzG1ipviZuEkEd0IAgIcR3krvXrVBQTLmtGbBbORlT8-AE5XeY3bMom9pD8gj0u7SDZQFOvGZAFA47COcUJUJJ-PL0us-3l-gI6afAGg*ZSJDkEq-rYZL99nbkaquo=&devicetype=Windows-QQBrowser&version=61030004&pass_ticket=qMx7ntinAtmqhVn+C23mCuwc9ZRyUp20kIusGgbFLi0=&uin=MTc1MDA1NjU1&ascene=1",
    "weixin_avatar":"http://static.shenjianshou.cn/image/508022-d4cecf609fee89eda17c043cd17c01bc?realurl=http%3A%2F%2Fwx.qlogo.cn%2Fmmhead%2FQ3auHgzwzM4vhSotvQapXcnJWHRfxlxMBSc1SfT1z1gmW38sxZvUmQ%2F0",
    "weixin_introduce":"这里是 科幻，奇幻，悬疑，惊悚的世界",
    "weixin_qr_code":"http://static.shenjianshou.cn/image/508022-4a20fbe18a99ba1beea7480ec68471f1?realurl=http%3A%2F%2Fopen.weixin.qq.com%2Fqr%2Fcode%2F%3Fusername%3Dbtdygod",
    "weixin_tmp_url":"http://mp.weixin.qq.com/s?timestamp=1533104306&src=3&ver=1&signature=mJ0kX3B7VBWXbAoTEnPmA42O8mVyNmeEoqxAWgQR*5j-wwzG1ipviZuEkEd0IAgIcR3krvXrVBQTLmtGbBbORlT8-AE5XeY3bMom9pD8gj0u7SDZQFOvGZAFA47COcUJUJJ-PL0us-3l-gI6afAGg*ZSJDkEq-rYZL99nbkaquo="
}'
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
        $str = $this->getParam('str');
        $arr = explode(',', $str);
        $kind = 0;
        $areaArr = [];
        $year = 0;
        $genre = '';
        foreach($arr as $key=>$item) {
            if($item == '电影') {
                $kind = 1;
            }else if($item == '电视剧') {
                $kind = 2;
            }else if($item == '综艺') {
                $kind = 3;
            }else if($item == '动画') {
                $kind = 4;
            }else if($item == '港台') {
                $areaArr = ['香港', '台湾'];
            }else if($item == '日韩') {
                $areaArr = ['日本', '韩国'];
            }else if($item == '欧美') {
                $areaArr = ['德国', '法国', '英国', '西班牙', '瑞典', '瑞士', '挪威', '奥地利', '意大利', '芬兰', '美国', '加拿大'];
            }else if(is_numeric($item)) {
                $year = $item;
            }else {
                $genre = $item;
            }
        }
        $result = [
            'area' => $areaArr,
            'kind' => $kind,
            'year' => $year,
            'genre' => $genre
        ];
        print_r($result);
    }
    
    public function actionE() {
        $host = '127.0.0.1';
        $port = '3306';
        $user = 'root1';
        $pwd = '';
        $dbName = 'yanyan';

        try{
            $Pdo = new PDO('mysql:$host;', $user, $pwd);
        }catch (Exception $e){        //捕获异常
            echo $e->getMessage();    //打印异常信息
        }
//        error_reporting(0);
        try{
            $Mysqli = new Mysqli($host, $user, $pwd, $dbName);
            if($Mysqli->connect_errno) {
                throw new Exception('数据库连接失败');
            }
        }catch (Exception $e){        //捕获异常
            echo $e->getMessage();    //打印异常信息
        }
    }
    
    
    
}

