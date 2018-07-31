<?php
namespace app\components;

use yii\web\ErrorHandler;

class Exception extends ErrorHandler {
    
    /**
     * 重写渲染异常页面方法
     * @param type $exception
     */
//    public function renderException($exception) {
//        $data = [
//            'code' => 500,
//            'msg' => $exception->getMessage(),
//            'data' => [
//                'file' => $exception->getFile(),
//                'line' => $exception->getLine()
//            ]
//        ];
//        echo json_encode($data);
//        die;
//    }
}
