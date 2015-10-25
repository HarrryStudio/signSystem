<?php
namespace Home\Controller;
use Think\Controller;
class CollectController extends BaseController {
	public function index(){
		$History = D('history');
		$data = $History->get_collect_data();
		// print_r($data);
		$this->assign('collect_data',$data);
		$this->assign('week_date',$this->index_week());
		
		$this->display();
	}

	public function index_week(){
		$time = time();
		return date("W",$time + 24 * 3600);
	}

	public function to_excel(){
		$History = D('history');
		$jury = $History->get_collect_data();
		$sheetTitle = "考勤统计";
		$file_name ="考勤统计";
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
		$objActSheet->setCellValue('A' . '1', "组名"); //设置第一行的值
		$objPHPExcel->getActiveSheet()->getStyle('A' . '1')->getFont()->setBold(true);
		$objActSheet->setCellValue('B' . '1', "成员");
		$objPHPExcel->getActiveSheet()->getStyle('B' . '1')->getFont()->setBold(true);
		$objActSheet->setCellValue('C' . '1', "时长(分钟)");
		$objPHPExcel->getActiveSheet()->getStyle('C' . '1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
		$i = 2;
		foreach ($jury as $value) {
			/* excel文件内容 */
			$merge = $i;
			foreach ($value['member'] as $value2) {
	
				$objActSheet->setCellValue( 'A' . $i, $value['name']);
				$objActSheet->setCellValue( 'B' . $i, $value2['name']);
				$objActSheet->setCellValue( 'C' . $i, (int)($value2['length'] / 60));
				$objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				$i++;
			}
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$merge.':A'.($i - 1));
			$objPHPExcel->getActiveSheet()->getStyle('A'.$merge)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
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