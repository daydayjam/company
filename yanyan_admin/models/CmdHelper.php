<?php
/**
 * 透传帮助模型类
 * @author ztt
 * @date 2017/11/24
 */
namespace app\models;

class CmdHelper {
	
    //替换msg里面的参数
    private function replaceParams($str, $values){
        $pattern = array();
        for($i = 0; $i < count($values); $i++){
                array_push($pattern,'({param'.($i+1).'})');
        }
        $data = preg_replace($pattern, $values, $str , 1);
        return $data;
    }
    
    //给用户发推送$easeUsers为array，里面存放环信用户ID
    public function sendCmdMessageToUsers( $easeUsers, $msg, $ext=null ){
            // //调用小米推送
             // if(count($easeUsers) > 1){
        // $rs = mod_mipush::getInstance()->sendMessage2Users($easeUsers,$ext);
             // }else{
        // $rs =  mod_mipush::getInstance()->sendMessage2User($easeUsers[0],$ext);
             // }
        $response = '';
        if( empty( $ext )){
            $response = $this->hxSendCmd( 'users', $easeUsers, $msg );
        }else{
            $response = $this->hxSendCmd( 'users', $easeUsers, $msg, $ext );
        }
        //sysDebug($response);
        return $response;
    }
	
    //给聊天室发透传
    public function sendMsgToEaseChatRoom( $easeRooms, $msg, $ext=null ){
        //调用小米推送
        // if(count($easeRooms) > 1){
                // mod_mipush::getInstance()->sendMessage2Chatrooms($easeRooms,$ext);
        // }else{
                // mod_mipush::getInstance()->sendMessage2Chatroom($easeRooms[0],$ext);
        // }

        $response = '';
        if( empty( $ext )){
                $response = $this->hxSendCmd( 'chatrooms', $easeRooms, $msg );
        }else{
                $response = $this->hxSendCmd( 'chatrooms', $easeRooms, $msg, $ext );
        }
        //sysDebug($response);
        return $response;
    }
	
    /**
    *发送环信透传消息
    *$from 发送者
    *$target_type users 给用户发消息,  chatgroups 给群发消息, chatrooms 给聊天室发消息
    *$target 接收者数组
    *$action  消息内容
    *$ext 自定义 数组
    */
    function hxSendCmd( $target_type,$target,$action,$ext=null,$from=null ){
        $response = '';
        if( empty( $target_type ) || ( strcmp( $target_type, 'users' ) && strcmp( $target_type, 'chatgroups' ) && strcmp( $target_type, 'chatrooms' ))){
            $response = 'INVALID_TARGET_TYPE';
            return $response;
        }

        if( !empty( $ext) && !empty( $ext['record'] ) && is_array( $ext['record'] ) ){
            $ext['record'] = Tool::array2string( $ext['record'] );
        }

        // sysDebug( $target_type );
        // sysDebug( $target );
        // sysDebug( $action );
        // sysDebug( $ext );

        if( empty( $from )){
            return Easemob::getInstance()->sendCmd( 'admin',$target_type,$target,$action,$ext);
        }else{
            return Easemob::getInstance()->sendCmd( $from,$target_type,$target,$action,$ext);
        }
    }
    
    

    
}

