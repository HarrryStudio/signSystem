<?php
namespace Home\Model;
use Think\Model;

/**
* 签到日志模型
*/
class HistoryModel extends BaseModel{

	protected $tableName = 'history_log';

	/**
	 * 根据姓名或账户得到用户信息
	 * @param  string $name 姓名或账户
	 * @return array        用户信息
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
	 * 根据组名得到组信息
	 * @param  string $name 组名
	 * @return array        组信息
	 */
	public function get_team_info($name){
		$where['name'] = $name;
		$team_info = M()->table('team_info')->field("id,name")->where($where)->find();
		return $team_info;
	}

	public function get_one_day_log($user_name,$time){
		$user_info = $this->get_user_info($user_name);
		if( empty($user_info) ){
			$this->error = "查无此人";
			return false;
		}
		$result =  $this
				->field('user_id,in_time,out_time')
				->where(array(
					'user_id' => $user_info['id'],
					'in_time' => array(array('gt',$time) , array('lt',$time + 24 * 3600))) )
				->order('in_time')
				->select();
				//echo $this->getLastSql();
		$thatdaytime = $time + C("DAY_START_HOUR") * 3600;

		$gather = array(array('name'=>$user_info['name'],'id'=>$user_info['id'],'length'=>0));

		$index = (int)date("w", $time+1);
		foreach ($result as $key => $value) {
			$result[$key]['col'] = $index;
			$result[$key]['length'] = (int)$value['out_time'] - (int)$value['in_time'];
			$gather[0]['length'] += $result[$key]['length'];
			$result[$key]['in_time'] = (int)$value['in_time'] - $thatdaytime;
			unset($result[$key]['out_time']);
		}
		return array('result'=>$result,'gather'=>$gather);
	}

	public function get_one_week_log($user_name,$time){
		//echo date("Y-m-d",$time);
		$user_info = $this->get_user_info($user_name);
		if( empty($user_info) ){
			$this->error = "查无此人";
			return false;
		}
		$result =  $this
				->field('user_id,in_time,out_time')
				->where(array(
					'user_id' => $user_info['id'],
					'in_time' => array(array('gt',$time) , array('lt',$time + 7 * 24 * 3600))) )
				->order('in_time')
				->select();

			//	echo $this->getLastSql();
		$gather = array(array('name'=>$user_info['name'],'id'=>$user_info['id'],'length'=>0,'item'=>array()));
		$length = 0;
		$w = 0;
		foreach ($result as $key => $value) {
			$w = (int)date("w", $value['in_time']);
			$length = (int)$value['out_time'] - (int)$value['in_time'];

			$result[$key]['col'] = $w;
			$result[$key]['length'] = $length;

			$gather[0]['item'][$w] += $length;
			$gather[0]['length'] += $length;

			$result[$key]['in_time'] = (int)$value['in_time'] - $time - ($w * 24 + C("DAY_START_HOUR")) * 3600;

			unset($result[$key]['out_time']);
		}
		return array('result'=>$result,'gather'=>$gather);
	}

	public function get_one_month_log($user_name,$time){
		$user_info = $this->get_user_info($user_name);
		if( empty($user_info) ){
			$this->error = "查无此人";
			return false;
		}
		$week_start = $time - (int)date("w", $time) * 24 * 3600;
		$month_end = (int)date("t", $time);
		$result =  $this->alias("a")
				->field('user_id,in_time,out_time')
				->where(array(
					'user_id' => $user_info['id'],
					'in_time' => array(array('gt',$time) , array('lt',$month_end * 24 * 3600 + $time)),
					) )
				->select();
				//echo $this->getLastSql();
		$gather = array(array('name'=>$user_info['name'],'length'=>0,'item'=>array()));
		$month = array();
		$month = array_pad($month,$month_end,0);
		$length = 0;
		foreach ($result as $key => $value) {
			$length = (int)$value['out_time'] - (int)$value['in_time'];
			$col = (int)date("j",(int)$value['in_time']);
			$month[$col - 1] += $length;
			$gather[0]['length'] += $length;
			$col = (int)(((int)$value['in_time'] - $week_start) / (7*24*3600));
			$gather[0]['item'][$col] +=  $length;
		}
		return array('result'=>$month,'gather'=>$gather);
	}

	public function get_team_day_log($team,$time){
		$team_info = $this->get_team_info($team);
		if( empty($team_info) ){
			$this->error = "查无此组";
			return false;
		}
		$result =  $this
				->alias('a')
				->field('a.user_id,b.name,in_time,out_time')
				->join('right join user_info as b on a.user_id = b.id')
				->join('team_info as c on b.team = c.id')
				->where(array(
					'b.team' => $team_info['id'],
					'in_time' => array(array('gt',$time) , array('lt',$time + 24 * 3600))) )
				->order('in_time')
				->select();
				//echo $this->getLastSql();
		$thatdaytime = $time + C("DAY_START_HOUR") * 3600;
		$users_info = M('user_info')->field('id, name')->where(array('team' => $team_info['id']))->select();
		$gather = array();
		foreach ($users_info as $key => $value) {
			array_push($gather, array('name'=>$value['name'], 'id'=>$value['id'], 'length'=>0));
		}

		$index = (int)date("w", $time+1);
		$length = 0;
		foreach ($result as $key => $value) {
			$length = (int)$value['out_time'] - (int)$value['in_time'];
			$result[$key]['col'] = $index;
			$result[$key]['length'] = $length;
			$result[$key]['in_time'] = (int)$value['in_time'] - $thatdaytime;
			unset($result[$key]['out_time']);
			$this->write_gather($result[$key],$gather,0);
		}
		return array('result'=>$result,'gather'=>$gather);
	}

	private function write_gather($data,&$gather,$has_item){
		$is_change = false;
		foreach ($gather as $key => $value) {
			if($gather[$key]['name'] == $data['name']){
				$is_change = true;
				$gather[$key]['length'] += $data['length'];
				if($has_item){
					$gather[$key]['item'][$data['col']] += $data['length'];
				}
			}
		}
		if(!$is_change){
			if($has_item){
				array_push($gather, array('name' => $data['name'],'length' => $data['length'],'item' => array($data['col'] => $data['length'])));
			}else{
				array_push($gather, array('name' => $data['name'],'length' => $data['length']));
			}
		}
	}

	public function get_team_week_log($team,$time){
		//echo date("Y-m-d",$time);
		$team_info = $this->get_team_info($team);
		if( empty($team_info) ){
			$this->error = "查无此组";
			return false;
		}
		$result =  $this->alias("a")
				->field('a.user_id,b.name,in_time,out_time')
				->join('user_info as b on a.user_id = b.id')
				->join('team_info as c on b.team = c.id')
				->where(array(
					'b.team' => $team_info['id'],
					'in_time' => array(array('gt',$time) , array('lt',$time + 7 * 24 * 3600))) )
				->order('in_time')
				->select();

			//	echo $this->getLastSql();
		$users_info = M('user_info')->field('id,name')->where(array('team' => $team_info['id']))->select();
		$gather = array();
		foreach ($users_info as $key => $value) {
			array_push($gather, array('id'=>$value['id'],'name'=>$value['name'],'length'=>0,'item'=>array()));
		}
		foreach ($result as $key => $value) {
			$result[$key]['col'] = (int)date("w", $value['in_time']);
			$result[$key]['length'] = (int)$value['out_time'] - (int)$value['in_time'];
			$result[$key]['in_time'] = (int)$value['in_time'] - $time - ($result[$key]['col'] * 24 + 7) * 3600;
			unset($result[$key]['out_time']);
			$this->write_gather($result[$key],$gather,1);
		}
		return array('result'=>$result,'gather'=>$gather);
	}

	public function get_team_month_log($team,$time){
		$team_info = $this->get_team_info($team);
		if( empty($team_info) ){
			$this->error = "查无此组";
			return false;
		}
		$week_start = $time - (int)date("w", $time) * 24 * 3600;
		$month_end = (int)date("t", $time);
		$result =  $this->alias("a")
				->field('a.user_id,b.name as name,in_time,out_time')
				->join('user_info as b on a.user_id = b.id')
				->join('team_info as c on b.team = c.id')
				->where(array(
					'b.team' => $team_info['id'],
					'in_time' => array(array('gt',$time) , array('lt',$month_end * 24 * 3600 + $time)),
					) )
				->order('user_id')
				->select();
				//echo $this->getLastSql();
		$gather = array();
		$month = array();
		$team = array();
		$mems = M('user_info')->field('id,name')->where(array('team' => $team_info['id']))->select();
		foreach ($mems as $key => $value) {
			$team[$value['id']]['name'] = $value['name'];
			$team[$value['id']]['length'] = 0;
			array_push($gather,array('name'=>$value['name'], 'id'=>$value['id'], 'length'=>0,'item'=>array()));
		}
		$month = array_pad($month,$month_end,$team);
		$length = 0;
		foreach ($result as $key => $value) {
			$length = (int)$value['out_time'] - (int)$value['in_time'] ;
			$col = (int)date("j",(int)$value['in_time']);
			$mam = (int)$value['user_id'];
			$month[$col - 1][$mam]['length'] += $length;
			$col = (int)(((int)$value['in_time'] - $week_start) / (7*24*3600));
			$this->write_gather(array('name'=>$value['name'],'length'=>$length,'col' => $col),$gather,1);
		}
		return array('result'=>$month,'gather'=>$gather);
	}

	/**
	 * 在线人数
	 * @return array   array("组名" => array(本组在线人员) ... );
	 */
	public function get_in_people(){
		$result =  M()->table("sign_log as a")
				  ->field('b.name,c.name as team')
				  ->join('user_info as b on a.user_id = b.id')
				  ->join('team_info as c on b.team = c.id')
				  ->where(array('a.status' => 0, 'b.status' => 0))
				  ->order('c.id,a.id')->select();
		$count = count($result);
		$data = array();
		foreach ($result as $key => $value) {
			if(!isset($data[$value['team']])){
				$data[$value['team']] = array();
			}
			array_push($data[$value['team']], $value['name']);
		}
		return array("data" => $data, "count" => $count);
	}

	public function get_collect_data(){
		$t = strtotime('today');
		$w = date('w');
		$time = $t - $w * 24 * 3600;
		$result =  $this->alias("a")
				->field('b.id as user_id,b.name as user_name,c.id as team_id,c.name as team_name,SUM(out_time)-SUM(in_time) as length')
				->join('right join user_info as b on a.user_id = b.id and (in_time > '.$time.' and in_time < '.(7 * 24 * 3600 + $time).')')
				->join('team_info as c on b.team = c.id')
				->group('b.id')
				->order('b.id')
				->select();
				//echo $this->getLastSql();
		$data = array();
		foreach ($result as $key => $value) {
			if(!isset($data[$value['team_id']])){
				$data[$value['team_id']] = array('name' => $value['team_name'],'member' => array());
			}
			if(isset($data[$value['team_id']]['member'][$value['user_id']])){
				$data[$value['team_id']]['member'][$value['user_id']]['length'] += (int)$value['length'];
			}else{
				$data[$value['team_id']]['member'][$value['user_id']] = array('name' => $value['user_name'],'length' => (int)$value['length']);
			}
		}
		return $data;
	}
}
