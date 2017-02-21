<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午1:14
 * Brief: 测试phpexcel类
 */
require_once dirname(__DIR__) . '/../config/defines.php';
require_once PHPEXCEL . '/PHPExcel.php';

class phpExcelTest {
    public function run() {
        $data = [];
        $index = 0;
        $objPHPExcel = new PHPExcel();

        foreach ($data as $tableType => $item) {
            if (!empty($item) && is_array($item)) {
                $index = isset($index) ? $index : 0;
                $objActSheet = $objPHPExcel->createSheet($index);
                $sheetsTitle = '测试';
                $objActSheet->setTitle($sheetsTitle);

                $objPHPExcel = $this->setDataSheet($objPHPExcel, $tableType, $index, $item);
                $index++;
            }
        }
        if (!$objPHPExcel) {
            echo "excel表出错";
            exit;
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        unset($objPHPExcel);
        $dirName = dirname(__FILE__) . "/temp/";
        if (!file_exists($dirName)) {
            mkdir($dirName);
        }

        $filePath = $dirName . $this->title . ' - ' . date('Y-m-d', strtotime('-1 day')) . ".xls";
        $objWriter->save($filePath);

    }

    /**
     * @param $objPHPExcel object excel对象
     * @param $tableType string 可以根据这个设置sheet的title
     * @param $index int 活动sheet
     * @param $data array 数据
     * @return object
     */
    public function setDataSheet($objPHPExcel, $tableType, $index, $data) {
        $objPHPExcel->setActiveSheetIndex($index)
            ->setCellValue('A1', '城市')
            ->setCellValue('B1', '预约客服')
            ->setCellValue('C1', '线索ID')
            ->setCellValue('D1', '线索状态')
            ->setCellValue('E1', '入库时间')
            ->setCellValue('F1', '客服申请时间');

        $i = 2;
        foreach($data as $item) {
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $item[0])
                ->setCellValue('B' . $i, $item[1])
                ->setCellValue('C' . $i, $item[2])
                ->setCellValue('D' . $i, $item[3])
                ->setCellValue('E' . $i, $item[4])
                ->setCellValue('F' . $i, $item[5]);
            $i++;
        }

        return $objPHPExcel;
    }
}

$obj  = new phpExcelTest();
$obj->run();

?>