<?php
namespace Home\Controller;
use Think\Controller;
class SearchController extends BaseController {

    public $week_days = array('日','一','二','三','四','五','六');


    public function index(){
        //var_dump($data = I('get.'));
        $History = D('history');
        $search_type = (int)I('get.search_type');

        if($search_type == 0){
            $now_in = $History->get_in_people(time());
            $should_in = D('Course')->get_should_be();
            $team_info = M('team_info')->field('name')->select();
            $absent_count = 0;
            //var_dump($should_in);
            //var_dump($now_in);
            foreach ($team_info  as $key => $value) {
                $t_name = $value['name'];
                $item_in = [];
                $item_should = [];
                if(isset($now_in['data'][$t_name])){
                    $item_in = $now_in['data'][$t_name];
                }
                if(isset($should_in['data'][$t_name])){
                    $item_should = $should_in['data'][$t_name];
                }
                for ($i = 0; $i < count($item_should); $i++) {
                    if(!in_array($item_should[$i], $item_in)){
                        $result['result']['data'][$t_name]['absent'][] = $item_should[$i];
                        $absent_count ++;
                    }
                }
                $result['result']['data'][$t_name]['now'] = $item_in;
            }
            $result['result']['absent_count'] = $absent_count;
            $result['result']['now_count'] = $now_in['count'];
        }else{

            $search_date_type = (int)I('get.search_date_type');
            $search_time = (int)I('get.search_time',"");
            $user_name = trim(I('get.title'));
            if (empty($user_name)) {
                 $this->error("请填写组名或人名");
                 return;
            }
            $this->assign('search_type',$search_type);
            $this->assign('search_date_type',$search_date_type);
            $this->assign('week_days',$this->week_days);

            if($search_date_type == 0){
                $search_time = empty($search_time) ? strtotime("today") : $search_time;
                if($search_type == 2){
                    $result = $History->get_one_day_log($user_name, $search_time);
                } elseif($search_type == 1){
                    $result = $History->get_team_day_log($user_name, $search_time);
                }
                $this->get_week($search_time,0);
            }elseif($search_date_type == 1){
                if(empty($search_time)){
                    $t = strtotime('today');
                    $w = date('w');
                    $search_time = $t - $w * 24 * 3600;
                }
                if($search_type == 2){
                    $result = $History->get_one_week_log($user_name, $search_time);
                } elseif($search_type == 1){
                    $result = $History->get_team_week_log($user_name, $search_time);
                }
                $this->get_week($search_time,1);
            }elseif($search_date_type == 2){
                $search_time = empty($search_time) ? strtotime(date("Y/m/01",time())) : $search_time;
                if($search_type == 2){
                    $result = $History->get_one_month_log($user_name, $search_time);
                }elseif($search_type == 1){
                    $result = $History->get_team_month_log($user_name, $search_time);
                }
                $result['result'] = $this->get_month($search_time,$result['result']);
            }

            // if($search_type == 2){
            //     if($search_date_type == 0){
            //         $search_time = empty($search_time) ? strtotime("today") : $search_time;
            //         $result = $History->get_one_day_log($user_name, $search_time);
            //         $this->get_week($search_time,0);
            //     }elseif($search_date_type == 1){
            //         if(empty($search_time)){
            //             $t = strtotime('today');
            //             $w = date('w');
            //             $search_time = $t - $w * 24 * 3600;
            //         }
            //         $result = $History->get_one_week_log($user_name, $search_time);
            //         $this->get_week($search_time,1);
            //     }elseif($search_date_type == 2){
            //         $search_time = empty($search_time) ? strtotime(date("Y/m/01",time())) : $search_time;
            //         $result = $History->get_one_month_log($user_name, $search_time);
            //         $result['result'] = $this->get_month($search_time,$result['result']);
            //     }
            // }
            // if($search_type == 1){
            //     if($search_date_type == 0){
            //         $search_time = empty($search_time) ? strtotime("today") : $search_time;
            //         $result = $History->get_team_day_log($user_name, $search_time);
            //         $this->get_week($search_time,0);
            //     }elseif($search_date_type == 1){
            //         if(empty($search_time)){
            //             $t = strtotime('today');
            //             $w = date('w');
            //             $search_time = $t - $w * 24 * 3600;
            //         }
            //         $result = $History->get_team_week_log($user_name, $search_time);
            //         $this->get_week($search_time,1
            //             );
            //     }elseif($search_date_type == 2){
            //         $search_time = empty($search_time) ? strtotime(date("Y/m/01",time())) : $search_time;
            //         $result = $History->get_team_month_log($user_name, $search_time);
            //         $result['result'] = $this->get_month($search_time,$result['result']);
            //     }
            // }

        }
        if( $result === false){
          $this->error($History->getError());
          //echo $History->getError();
          return;
        }
        $this->assign('result',$result['result']);
        $this->assign('gather',$result['gather']);
        //print_r($result['gather']);
        //print_r($result['result']);
        // $today = (int)date("w", time());
       $this->assign('search_time',$search_time);

       $user_info = M()->table('user_info')->field('name')->select();
                    $this->assign("user_info",$user_info);
        //print_r($result);
    	$this->display();
    }

    public function get_week($search_time,$type){

        $week_date = array();
        $thatday = (int)date("w", $search_time);

        $first_time = $search_time - $thatday * 24 * 3600;
        if($first_time + 7 * 24 * 3600 > time()){
            $today = (int)date("w", time());
            $this->assign('today',$today);
        }

        $first_day = (int)date("j", $first_time);
        $month_end = (int)date("t", $first_time);

        for($i = 0; $i < 7; $i ++){
            if( ($week_date[] = $first_day + $i) >= $month_end){
                $first_day = - $i;
            }
        }
        if($type == 0){
            $this->assign('thatday',$thatday);
        }
        $this->assign('week_date',$week_date);
    }

    public function get_month($search_time,$result){
        $arr = array();
        $pad = (int)date("w", $search_time);
        $i = 0;
        for($i = 0; $i < $pad; $i ++){
            $arr[$i] = -1;
        }
        foreach ($result as $key => $value) {
            $arr[$i] = $value;
            $i ++;
        }
        $extr = ceil(count($arr) / 7) * 7;
        return array_pad($arr,$extr,-1);
    }
}
