<?php
namespace Home\Model;
use Think\Model;

class CourseModel extends BaseModel{

	protected $tableName = 'course'; 


	public function get_all_info($this_week,$team){
		$where['user_info.status'] = 0;
		$where['course.status'] = 0;
		if($team > 0){
			$where['user_info.team'] = $team;
		}
		$result = $this
				->field('name,data')
				->join('user_info on user_id = user_info.id')
				->where($where)
				->select();
				// echo $this->getLastSql();
		$data = array();
		$data = array_pad($data , 20 ,array());
		$temp = array();
		foreach ($result as $key1 => $user) {
			$temp = json_decode($user['data'],true);
			foreach ($temp as $key2 => $value) {
				if(is_array($value)){
					if( in_array($this_week-1, $value['weeks']) ){
						$data[$value['index']][] = $user['name'];
					}
				}
			}
		}
		return $data;
	}
	
	public function get_one_info($user_id){
		$where['user_id'] = $user_id;
		$where['status'] = 0;
		$result = $this->field('id,data')->where($where)->find();
		if(empty($result)){
			$result['data'] = "[]";
			$result['id'] = 0;
		}elseif(!json_decode($result['data'])){
            $result['data'] = "[]";
		}
		return $result;
	}

	public function save_course(){
		$user_id = session('user_id');
    	if(empty($user_id)){
    		$this->error = "填写姓名";
    		return false;
    	}
    	$map['id'] = $user_id;
		$map['status'] = 0;
		$user_info = M()->table('user_info')->field("id")->where($map)->find();
		if(empty($user_info)){
    		$this->error = "查无此人";
    		return false;
    	}

    	$data = I('data');
		$data = json_encode($data);
		$r_data['update_time'] = time();
    	$r_data['data'] = $data;
    	

    	$course_info = $this
    				->field('id,data')
    				->where(array('user_id' => $user_id,'status' => 0))
    				->find();
    
    	if(empty($course_info)){
    		$r_data['create_time'] = time();
    		$r_data['user_id'] = $user_id ; 
    		if( $this->create($r_data) && $this->add()){
    			return true;
    		}
    	}else{

    		if($data == $course_info['data']){
    			return true;
    		}
    		$r_data['id'] = $course_info['id'];
    		if ( $this->save($r_data) ){
    			return true;
    		}
    	}
		return false;
	}	

	public function do_copy(){
		$source_user = session('user_id');
		if(empty($source_user)){
    		$this->error = "选择源成员";
    		return false;
    	}
    	$user_name = trim(I('user_name'));
		if(empty($source_user)){
    		$this->error = "选择目标成员";
    		return false;
    	}
    	$user_info = $this->get_user_info($user_name);
    	if(empty($user_info)){
    		$this->error = "没有找到目标成员";
    		return false;
    	}
    	$target_user = $user_info['id'];

    	$source = $this->field('data')->where(array('user_id' => $source_user,'status' => 0) )->find();

    	if(empty($source)){
    		$this->error = "没有找到源课表";
    		return false;
    	}

    	$target = $this->field('id')->where(array('user_id' => $target_user,'status' => 0) )->find();

    	$data['data'] = $source['data'];
    	$data['update_time'] = time();
    	if(empty($target)){
			$data['create_time'] = $data['update_time'];
    		$data['user_id'] = $target_user ; 
    		if( $this->create($data) && $this->add()){
    			return true;
    		}
    	}else{
    		$data['id'] = $target['id'];
    		if ( $this->save($data) !== false){
    			return true;
    		}
    	}
    	return false;

	}
}