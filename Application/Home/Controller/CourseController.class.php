<?php
namespace Home\Controller;
use Think\Controller;
class CourseController extends BaseController {

    public function index(){
    	$Course = D('course');
        $this_week = I('this_week',$Course->get_this_week());
        $team = (int)I('team');
        $dif = (int)I('dif',0);

        $result = $Course->get_all_info($this_week,$team,$dif);

        $team_info = M()->table('team_info')->select();
    	$this->assign('this_week',$this_week);
        $this->assign('team',$team);
        $this->assign('dif',$dif);
    	$this->assign('result',$result);
        $this->assign('team_info',$team_info);
    	$this->display();
    }

    public function do_login(){
    	$name = I('user_name');
    	if(empty($name)){
    		$this->response("请填写姓名",1);
    		return;
    	}
    	$Course = D('course');
    	$user_info = $Course->get_user_info($name);
    	if( empty($user_info) ){
    		$this->response("查无此人",1);
			return;
		}
		session('user_id',$user_info['id']);
		session('user_name',$user_info['name']);
		$this->response("",0,U('Course/edit'));
    }

    public function edit(){
    	$user_id = session('user_id');
        $Course = D('course');
    	$this_week = I('this_week',$Course->get_this_week());
    	if(empty($user_id)){
    		$this->redirect('index');
    		return;
    	}
    	$Course = D('course');
    	$result = $Course->get_one_info($user_id);
    	//var_dump($result);
    	$this->assign('result',$result);
    	$this->assign('this_week',$this_week);
    	$this->display();
    }

    public function save(){
    	$Course = D('course');
    	$result = $Course->save_course();
    	if($result === false){
    		$this->response($Course->getError(),1);
    	}else{
    		$this->response("保存成功",0);
    	}
    }


    public function do_copy(){
        $Course = D('course');
        $result = $Course->do_copy();
        if($result === false){
            $this->response($Course->getError(),1);
        }else{
            $this->response("复制成功",0);
        }
    }

    public function to_excel(){
        $Course = D('course');
        $this_week = I('this_week',$this->get_this_week());
        $team = (int)I('team');
        if($team > 0){
            $team_info = M()->table('team_info')->where(array('id' => $team))->field('name')->find();
            $team_name = $team_info['name'];
        }else{
            $team_name = "全体成员";
        }
        $dif = (int)I('dif');
        $jury = $Course->get_all_info($this_week,$team,$dif,0);

        //var_dump(I('team'));return;

        $sheetTitle = "课表";
        $file_name ="三月".$team_name."第".$this_week ."周".($dif?"无":"有")."课表";
        // print_r($jury);
        // return ;
        //删除以前生成的
        $check_file = glob("Public/excel/*.xls");
        foreach ($check_file as $check_filename) {
            $file_time = filectime($check_filename);
            $now_time = time();
            if ($now_time - $file_time > 30) {
                unlink($check_filename);
            } else {
                $var_time = "error";
            }
        }
        /* 实例化类 */
        Vendor("PHPExcel.PHPExcel");
        Vendor("PHPExcel.PHPExcel.IOFactory");
        $objPHPExcel = new \PHPExcel();

        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);

        /* 设置当前的sheet */
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();

        /* sheet标题 */
        $objActSheet->setTitle($sheetTitle);
        $objActSheet->setCellValue('B' . '1', "周日");
        $objPHPExcel->getActiveSheet()->getStyle('B' . '1')->getFont()->setBold(true);
        $objActSheet->setCellValue('C' . '1', "周一"); //设置第一行的值
        $objPHPExcel->getActiveSheet()->getStyle('C' . '1')->getFont()->setBold(true);
        $objActSheet->setCellValue('D' . '1', "周二");
        $objPHPExcel->getActiveSheet()->getStyle('D' . '1')->getFont()->setBold(true);
        $objActSheet->setCellValue('E' . '1', "周三");
        $objPHPExcel->getActiveSheet()->getStyle('E' . '1')->getFont()->setBold(true);
        $objActSheet->setCellValue('F' . '1', "周四"); //设置第一行的值
        $objPHPExcel->getActiveSheet()->getStyle('F' . '1')->getFont()->setBold(true);
        $objActSheet->setCellValue('G' . '1', "周五");
        $objPHPExcel->getActiveSheet()->getStyle('G' . '1')->getFont()->setBold(true);
        $objActSheet->setCellValue('H' . '1', "周六");
        $objPHPExcel->getActiveSheet()->getStyle('H' . '1')->getFont()->setBold(true);

        $string = "";
        $index = 0;
        for($i = 0 ; $i < 5; $i ++){
            $objActSheet->setCellValue('A' . ($i + 2), ($i+1));
            for($j = 'B'; $j <= 'H'; $j ++){
                $string = "";
                foreach ($jury[$index] as $key => $value) {
                    $string .= $value . ",";
                }
                if(empty($string)){
                    $string = "";
                }else{
                    $string = substr($string,0,-1);
                }

                $objActSheet->setCellValue($j . ($i + 2), $string);
                $index++;
            }
        }

        date_default_timezone_set("Asia/Shanghai");
        $date = date("YmdHi");
        $file_name = $file_name . $date . ".xls";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$file_name.'"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
        //$objWriter->save($path);
        //echo $path;
    }
}
