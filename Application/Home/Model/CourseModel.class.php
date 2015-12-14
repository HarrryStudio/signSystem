<?php
namespace Home\Model;
use Think\Model;

class CourseModel extends BaseModel{

	protected $tableName = 'course';
    /**
     * 得到课表信息
     * @param  int $this_week 周数
     * @param  int $team      组id
     * @param  int $dif       0 为 有课表   1为无课表
     * @param  int $is_dis    是否分 "全部 , 除"   0: 为否  1: 为是
     * @return array          课表信息
     */
	public function get_all_info($this_week,$team,$dif,$is_dis = 1){
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
		$have_data = array();
		$have_data = array_pad($have_data , 35 ,array());
        $dif_data = array();
        $dif_data = array_pad($dif_data , 35 ,array());
		$temp = array();
		foreach ($result as $key1 => $user) {
			$temp = json_decode($user['data'],true);
            for($i = 0; $i < 35; $i ++){
				if(is_array($temp[$i])){
                    if( in_array($this_week-1, $temp[$i]['weeks']) ){
                        $have_data[$temp[$i]['index']][] = $user['name'];
                    }else{
                        $dif_data[$temp[$i]['index']][] = $user['name'];
                    }
				}else{
                    $dif_data[$i][] = $user['name'];
                }
			}
		}
        if(!$is_dis){
            return $dif ? $dif_data : $have_data ;
        }
        $count = count($result);
        $data = array();
        if($count > 0){
            if($dif){
                for($i = 0; $i < 35; $i ++){
                    if(count($dif_data[$i]) == $count){
                        $data[$i] = 0;
                        continue;
                    }
                    if(count($dif_data[$i]) <= ceil($count * C('PERSON_RATIO'))){
                        $data[$i] = array(1, $dif_data[$i]);
                    }else{
                        $data[$i] = array(-1, $have_data[$i]);
                    }
                }
            }else{
                for($i = 0; $i < 35; $i ++){
                    if(count($have_data[$i]) == $count){
                        $data[$i] = 0;
                        continue;
                    }
                    if(count($have_data[$i]) <= ceil($count * C('PERSON_RATIO'))){
                        $data[$i] = array(1, $have_data[$i]);
                    }else{
                        $data[$i] = array(-1, $dif_data[$i]);
                    }
                }
            }
        }
        //var_dump($data);
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

	public function get_this_week(){
		$start_time = strtotime( C('START_TIME') );
		$num = (int)((time() - $start_time) / (3600 * 24 * 7));
		return $num;
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

	public function get_should_be(){
		$section = $this->get_this_course();
		if($section == -1){
			return;
		}
		$week = $this->get_this_week();
		$data = $this->alias('a')
				->field("a.data,t.name as t_name,u.name as u_name")
				->join("user_info as u on a.user_id = u.id")
				->join("team_info as t on u.team = t.id")
				->where(array("u.status" => 0))
				->select();
		$count = 0;
		$result = array();
		foreach($data as $key => $value){
			if(!isset($result[$value['t_name']])){
				$result[$value['t_name']] = array();
			}
			$item = json_decode( $value['data'] , true);
			$course = $item[$section];
			if(is_array($course)){
                $flag = true;
				for ($i = 0; $i < count($course['weeks']); $i++) {
					if($course['weeks'][$i] == $week){
						$flag = false;
					}
				}
                if($flag){
                    $result[$value['t_name']][] = $value['u_name'];
                    $count ++;
                }
			}else{
				$result[$value['t_name']][] = $value['u_name'];
				$count ++;
			}
		}
		return array('data' => $result, 'count' => $count);
	}

	public function get_this_course(){
		$schedule = C("SCHEDULE");
		$time = time();
		$col = (int)date("w", $time+1);
		for ($i = count($schedule) - 1; $i >= 0 ; $i--) {
			if( strtotime($schedule[$i]) <  $time){
				return $i + $col * 5;
			}
		}
		return -1;
	}
}
