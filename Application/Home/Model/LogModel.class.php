<?php
namespace Home\Model;
use Think\Model;

/**
* 签到日志模型
*/
class LogModel extends BaseModel{	

	protected $tableName = 'sign_log'; 
	/**
	 * 根据姓名或账户得到用户信息
	 * @param  string $name 姓名或账户
	 * @return int          user_id
	 */
	// public function get_user_info($name){
	// 	$where['name'] = $name;
	// 	$where['account'] = $name;
	// 	$where['_logic'] = 'or';
	// 	$map['_complex'] = $where;
	// 	$map['status'] = 0;
	// 	$user_info = M()->table('user_info')->field("id,name")->where($map)->find();
	// 	return $user_info;
	// }

	/**
	 * 验证时间的合法性
	 * @param  int $time     时间戳
	 * @param  int $user_id  用户id
	 * @return Boolean       是否合法
	 */
	public function check_time($time,$user_id){
		if($time > time()){
			$this->error = "提交时间大于目前时间";
		}else{
			$where['user_id'] = $user_id;
			$where["_string"] = "in_time < " . $time . " and out_time > " . $time;
			$result = M('history_log')->where($where)->find();
			//echo M('history_log')->getLastSql();
			if(empty($result)){
				return true;
			}
			$this->error = "与其他签到时间重叠";
		}
		return false;
	}

	/**
	 * 得到今天的签到信息
	 * @return array 信息
	 */
	public function get_tobay_log_info(){
		return M()->table('sign_log as a')
				  ->field('a.time,a.type,b.name')
				  ->join("user_info as b on a.user_id = b.id")
				  ->where( array("time" => array( 'gt', strtotime("today") ) ) )
				  ->order('time desc')
				  ->limit('50')
				  ->select();
	}

	/**
	 * 签到
	 * @param  string $name 姓名或账户
	 * @param  int    $time 签到时间戳
	 * @return Boolean      成功与否
	 */
	public function sign_in($name,$time){
		$user_info = $this->get_user_info($name);
		if( empty($user_info) ){
			$this->error = "查无此人";
			return false;
		}
		//检查之前有无签到信息
		$this->startTrans();
		$result = $this
				->where( array("user_id" => $user_info['id'], "time" => array('elt', $time), "status" => 0) )
				->order('time desc')
				->lock(true)
				->find(false);
		//echo $result;
		if(!empty($result)){
			$this->error = "重复签到";
			$this->rollback();
			return false;
		}
		$data['user_id'] = $user_info['id'];
		$data['time'] = $time;
		$data['type'] = 0;  // 表示签到
		if($this->create($data) &&  ($result = $this->add())){
			$this->commit();
			return $user_info;
		}else{
			$this->rollback();
			$this->error = "签到失败";
			return false;
		}
	}

	/**
	 * 签退
	 * @param  string $name 姓名或账户
	 * @param  int    $time 签退时间戳
	 * @return Boolean      成功与否
	 */
	public function sign_out($name,$time,$offset = 0){
		$user_info = $this->get_user_info($name);
		if( empty($user_info) ){
			$this->error = "查无此人";
			return false;
		}
		//获取与之对应的签到信息(最近的一条)
		$in_info = $this
					->where( array("user_id" => $user_info['id'], "time" => array('lt', $time),"status" => 0) )
					->order('time desc')
					->find();
		if(empty($in_info)){
			$this->error = "还未签到";
			return false;
		}elseif( $time - $in_info['time'] > C('MAX_CONTINUE_HOUR') * 3600 ){
			$this->error = "连续时间超过".C('MAX_CONTINUE_HOUR')."个小时,请先补签";
			return false;
		}

		if($offset){
			if( !$this->check_time($time,$user_info['id']) ){
				return false;
			}
		}
		
		$this->startTrans();

		//插入 签退数据
		$data['user_id'] = $user_info['id'];
		$data['time'] = $time;
		$data['type'] = 1;
		$data['status'] = 1;
		$data['offset'] = $offset;
		if( !$this->create($data) || !($id = $this->add())){
			$this->error = "签到失败";
			$this->rollback();
			return false;
		}
		//将对应的签到数据status置1
		if( ($result = $this->where(array('id' => $in_info['id']))->save(array('status' => 1))) == null){
			$this->error = "签到失败";
			$this->rollback();
			return false;
		}
		//插入 到history_log表中
		$history_data['user_id'] = $user_info['id'];
		$history_data['in_time'] = $in_info['time'];
		$history_data['out_time'] = $data['time'];
		$history_data['offset'] = $offset;
		if(! M("history_log")->add($history_data) ){
			$this->error = "签到失败";
			$this->rollback();
			return false;
		}
		$this->commit();
		return $user_info;
	}

	/**
	 * 补签
	 * @param  string   $name       姓名或账户
	 * @param  int      $in_time    签到时间戳
	 * @param  int      $out_time   签退时间戳
	 * @return Boolean              成功与否
	 */
	public function sign_offset($name,$in_time,$out_time){
		$user_info = $this->get_user_info($name);
		if( empty($user_info) ){
			$this->error = "查无此人";
			return false;
		}
		if($out_time < $in_time){
			$this->error = "签到时间大于签退时间";
			return false;
		}
		if($out_time - $in_time > C("MAX_CONTINUE_HOUR") * 3600 ){
			$this->error = "连续时间超过".C('MAX_CONTINUE_HOUR')."个小时,请先补签";
			return false;
		}
		if( !$this->check_time($in_time,$user_info['id']) || !$this->check_time($out_time,$user_info['id']) ){
			return false;
		}
		//直接插入到历史记录中
		$m = M();
		$m->startTrans();
		$this->error = "补签失败";

		$dataList[] = array('user_id' => $user_info['id'], "time" => $in_time,  'type' => 0, 'status' => 1, 'offset' => 1);
		$dataList[] = array('user_id' => $user_info['id'], "time" => $out_time, 'type' => 1, 'status' => 1, 'offset' => 1);
		if( !$m->table('sign_log')->addAll($dataList) ){
			$m->rollback();
			return fasle;
		}

		$data = array('user_id' => $user_info['id'], "in_time" => $in_time, "out_time" => $out_time, "offset" => 1);
		if( !$m->table('history_log')->add($data) ){
			$m->rollback();
			return false;
		}
		$m->commit();
		return $user_info;
	}

	/**
	 * 判断之前是否有签到信息
	 * @param  string $name 账号或名字
	 * @return false 失败  array 签到数据
	 */
	public function had_sign_in($name){
		$user_info = $this->get_user_info($name);
		if( empty($user_info) ){
			$this->error = "查无此人";
			return false;
		}
		return $result = $this
				->where( array("user_id" => $user_info['id'], "time" => array('lt', time()), "status" => 0) )
				->order('time desc')
				->find();
	}
}