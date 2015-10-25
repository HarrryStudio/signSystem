<?php
namespace Home\Model;
use Think\Model;

/**
* 签到日志模型
*/
class BaseModel extends Model{

	public function _initialize(){
    	
    }

    /**
	 * 根据姓名或账户得到用户信息
	 * @param  string $name 姓名或账户
	 * @return array        用户信息
	 */
	public function get_user_info($name){
		$where['name'] = $name;
		$where['account'] = $name;
		$where['_logic'] = 'or';
		$map['_complex'] = $where;
		$map['status'] = 0;
		$user_info = M()->table('user_info')->field("id,name")->where($map)->find();
		return $user_info;
	}

	public function get_user_info_by_id($id){
		$map['id'] = $id;
		$map['status'] = 0;
		$user_info = M()->table('user_info')->field("id,name")->where($map)->find();
		return $user_info;
	}
}