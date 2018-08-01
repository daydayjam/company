<?php
/**
 * 视频操作类
 * @date 2017/09/14
 * @author zhang
 */
namespace app\models;

class Video{
	const FFMPEG_PATH = 'D:\PhpSystem\Ffmpeg\ffmpeg -i "%s" 2>&1';
	
	/**
	 * 获取视频详细信息
	 * @param string $file 视频地址
	 * @return array 视频详细信息
	 */
	public static function getVideoInfo($file) {
		$command = sprintf(self::FFMPEG_PATH, $file);
		ob_start();
		passthru($command);
		$info = ob_get_contents();
		ob_end_clean();
		$data = array();
		if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
			$data['duration'] = $match[1]; //播放时间
			$arr_duration = explode(':', $match[1]);
			$data['seconds'] = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
			$data['start'] = $match[2]; //开始时间
			$data['bitrate'] = $match[3]; //码率(kb)
		}
		if (preg_match("/Video: (.*?), (.*?(\\(.*, .*\\))?), (.*?)[,\s]/", $info, $match)) {
			$data['vcodec'] = $match[1]; //视频编码格式
			$data['vformat'] = $match[2]; //视频格式
			$data['resolution'] = $match[4]; //视频分辨率
			$arr_resolution = explode('x', $match[4]);
			$data['width'] = $arr_resolution[0];
			$data['height'] = $arr_resolution[1];
		}
		if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
			$data['acodec'] = $match[1]; //音频编码
			$data['asamplerate'] = $match[2]; //音频采样频率
		}
		if (isset($data['seconds']) && isset($data['start'])) {
			$data['play_time'] = $data['seconds'] + $data['start'];
		}

		return $data;
	}
        
        /**
         * 获取播放时长
         * @param type $file
         * @return string 格式03:03,，3分3秒
         */
        static public function getVideoDuration($file) {
            $command = sprintf(self::FFMPEG_PATH, $file);
            ob_start();
            passthru($command);
            $info = ob_get_contents();
            ob_end_clean();
            $duration = '';
            if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
                $time = $match[1];
                $arr_duration = explode(':', $time);
                $seconds = substr($arr_duration[2], 0, 2);
                $duration = $arr_duration[0] >1 ? (($arr_duration[0] * 60) + $arr_duration[1]) . ':' . $seconds : $arr_duration[1] . ':' . $seconds;
            }
            return $duration;
        }
	
	/**
	 * 上传视频文件
	 * @param $file 视频文件
	 * @return boolen true=上传成功
	 */
	 public static function uploadVideo($file, $uploadConfig, $upName) {
	 	$rs = array('code'=>1);
		/** 参数检测  **/
		//检测文件是否为空
		if(empty($file)) {
			$rs['code'] = -1;
			$rs['msg'] = '未找到该文件';
			return $rs;
		}
		//检测上传途径是否正确，是否是post上传
		$tmpName = $file['tmp_name'];
		if(!is_uploaded_file($tmpName)) {
			$rs['code'] = -2;
			$rs['msg'] = '上传途径不正确';
			return $rs;
		}
	 	//检测文件类型
	 	$name = $file['name'];
	 	$type = self::getExt($name);
	 	if(isset($uploadConfig['allowType']) && !in_array($type, $uploadConfig['allowType'])) {
	 		$rs['code'] = -3;
			$rs["msg"] = '文件类型有误';
			return $rs;
	 	}
	 	//检测文件大小
	 	$size = $file['size'] / (1024*1024);
		if(isset($uploadConfig['limitSize']) && $size > $uploadConfig['limitSize']) {
			$rs['code'] = -4;
			$rs['msg'] = '大小超限('.$size.'->'.$uploadConfig['limitSize'].')';
			return $rs;
		}
		//检测文件分辨率是否超限
		$fileInfo = self::getVideoInfo($tmpName);
		if(isset($uploadConfig['maxWidth']) && $fileInfo['width'] > $uploadConfig['maxWidth']) {
			$rs['code'] = -5;
			$rs['msg'] = '宽度应为' . $uploadConfig['maxWidth'] . 'px(' . $fileInfo['width'] . 'px)';
			return $rs;
		}
	 	if(isset($uploadConfig['maxHeight']) && $fileInfo['height'] > $uploadConfig['maxHeight']) {
			$rs['code'] = -6;
			$rs['msg'] = '高度应为' . $uploadConfig['maxHeight'] . 'px(' . $fileInfo['height'] . 'px)';
			return $rs;
		}
		/** 上传文件 **/
		//判断上传文件目录是否存在
		$uploadPath = $uploadConfig['uploadPath'];
		if(!is_dir($uploadPath)) {
			mkdir($uploadPath, 0777, true);
		}
		//检查目录是否可写
		if(!is_writeable($uploadPath)) {
			$rs['code'] = -7;
			$rs['msg'] = '上传目录不可写';
			return $rs;
		}
		$upName = $upName . '.' . $type;
		$videoPath = $uploadPath . $upName;
		$result = move_uploaded_file($tmpName, $videoPath);
		if(!$result) {
			$rs['code'] = -8;
			$rs['msg'] = '视频上传失败';
			return $rs;
		}
		$rs['path'] = $uploadConfig['domain'] . $upName;
		$rs['width'] = $fileInfo['width'];
		$rs['height'] = $fileInfo['height'];
		$rs['length'] = $fileInfo['seconds'];
		return $rs;
	 }
	 
	 /**
	  * 获取文件类型，后缀
	  * @param string $file 文件地址
	  * @return string 文件后缀
	  */
	  public static function getExt($file) {
	  	return substr($file, strrpos($file, '.') + 1) ;
	  }
	
}