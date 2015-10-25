<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function _initialize(){
    	//echo "base";
    	if(IS_GET){
            $_POST = $_GET;
        }
    }

    /**
     * ajacreturn 封装操作
     * @param  string  $info   返回信息
     * @param  integer $status 返回状态  0:成功  1:失败
     * @param  string  $data   返回数据
     */
    public function response($info = "" , $status = 0 , $data = ""){
    	$this->ajaxReturn(array('info' => $info, "status" => $status, "data" => $data));
    }
}