<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
    	$log_info = D('Log')->get_tobay_log_info();
             $user_info = M()->table('user_info')->field('name')->select();
             $this->assign("user_info",$user_info);
    	$this->assign("log_info",$log_info);
    	$this->display();
    }

    /**
     * 签到或签退
     * @param  int    type  0:签到 1:签退
     * @param  string name  用户名或账号
     * @return array 返回信息
     */
    public function sign_handle(){
    	$data = I('post.');
    	if(empty($data)){
    		$this->response("请填写数据",1);
    		return;
    	}
    	$Log = D('Log');
    	$type = (int)$data['type'];
    	$name = trim($data["name"]);
        if(empty($name)){
            $this->response("请填写姓名",1);
            return;
        }
    	$time = time();
    	if($type == 0){
    		$result = $Log->sign_in($name,$time);
    	}else{
    		$result = $Log->sign_out($name,$time);
    	}
    	if($result){
    		$this->response(null,0,$result);
    	}else{
    		// $this->response($Log->getError(),1);
            $this->response($Log->getError(),1);
            
    	}
    }

    /**
     * 签到或签退
     * @param  string name   用户名或账号
     * @param  int in_time   签到时间戳
     * @param  int out_time  签退时间戳
     * @return array 返回信息
     */
    public function offset_handle(){
    	$data = I('post.');
    	if(empty($data)){
    		$this->response("请填写数据",1);
    		return;
    	}
    	$Log = D('Log');
    	$name = trim($data["name"]);
    	$in_time = (int)$data["in_time"];
    	$out_time =  (int)$data["out_time"];
    	if($data['have_in'] == "true"){
    		$result = $Log->sign_out($name,$out_time,1);
    	}else{
    		$result = $Log->sign_offset($name,$in_time,$out_time);
    	}
    	if($result){
    		$this->response("补签成功",0,$result);
    	}else{
    		$this->response($Log->getError(),1);
    	}
    }

    public function had_sign_in(){
    	$Log = D('Log');
    	$name = I('post.name','','trim');
    	if(empty($name)){
    		$this->response("请填写姓名",1);
    		return;
    	}
    	$result = $Log->had_sign_in( $name );
    	//var_dump($result);
    	if($result === false){
    		$this->response($Log->getError(),1);
    	}else{
    		$this->response((int)empty($result),0,$result);
    	}
    }

    /**
    * 一键签退
    * @return 
    */
    public function allSignOut(){
            header("Content-type: text/html; charset=utf-8");
            $History = D('history');
            $online_peopel = $History->get_in_people(time());
            $Log = D('Log');
            $time = time();
            foreach ($online_peopel as $key => $group) {
                    $success_num = 0;
                    $success_names = "";
                    $failed_num = 0;
                    $failed_names = "";
                    echo '<h4>'.$key.'&nbsp;在线'.count($group).'人</h1>';
                    foreach ($group as $key => $people) {
                        $result = $Log->sign_out($people,$time);
                        if ($result) {
                            $success_names = $success_names . $people . '&nbsp;,&nbsp;';
                            $success_num ++;
                        } else {
                            $failed_names = $failed_names . $people . '&nbsp;,&nbsp;';
                            $failed_num ++;
                        }
                    }
                    echo "<p style='text-indent=2px'>下线成功".$success_num."人:&nbsp;".$success_names."</p>";
                    echo "<p style='color:red; text-indent:2px'>下线失败".$failed_num."人:&nbsp;".$failed_names."</p>";
            }
    }
}