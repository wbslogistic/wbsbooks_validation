<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');
/* Here there will be some code where you create $objPHPExcel */
// redirect output to client browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
header('Content-Disposition: attachment;filename="user_statistics.xlsx"'); 
header('Cache-Control: max-age=0');

require_once("./include/functions.php");
require_once('./include/PHPExcel/Classes/PHPExcel.php');

if (isset($_GET['days'])) {
   $days = $_GET['days'];
} else {
   $days = 7;
}

$objPHPExcel = new PHPExcel();

$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri'); 
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

$header_styleArray = array( 	'font' => array( 'bold' => true, ), 
				'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, ), 
				'alignment' => array( 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, ), 
				'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, ), ), 
				'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array( 'argb' => 'FCD5B4', ), ), );

$body_styleArray = array( 	'font' => array( 'bold' => false, ), 
				'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, ), 
				'alignment' => array( 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, ), 
				'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, ), ), 
				'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array( 'argb' => 'FFFFFF', ), ), );

$footer_styleArray = array( 	'font' => array( 'bold' => true, ), 
				'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT, ), 
				'alignment' => array( 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, ), 
				'borders' => array( 'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, ), ), 
				'fill' => array( 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array( 'argb' => 'EAF1DD', ), ), );

$sql = "SELECT userID, userName, SUM(NoBooks) AS NoBooks, SEC_TO_TIME(SUM(TimeWorked)) AS TimeWorked, CURDATE() AS EndDate, DATE_ADD(CURDATE(), INTERVAL -" . $days . " DAY) AS StartDate
       FROM
       (
       SELECT UserHistoryID, userID, userName, LoginDate, CASE WHEN LogoffDate is null THEN LastActivity ELSE LogoffDate END AS SessionEnd, COUNT(id) AS NoBooks,
       TIME_TO_SEC(CASE WHEN LogoffDate is null THEN LastActivity ELSE LogoffDate END) - TIME_TO_SEC(LoginDate) AS TimeWorked
       FROM UserSessions
       INNER JOIN Books ON UserSessions.UserHistoryID = Books.SessionID
       WHERE LoginDate > DATE_ADD(CURDATE(), INTERVAL -" . $days . " DAY)
       GROUP BY UserHistoryID, userID, userName, LoginDate, LogoffDate, LastActivity) AS T1
       GROUP BY userName";

$sql_result = GetDatabaseRecords($sql);
$sql_num=mysql_num_rows($sql_result);

$sheetNum = 0;
$rowCount = 1;

while($sql_row=mysql_fetch_array($sql_result))
{

$sheetNum ++;
$rowCount ++;

$objPHPExcel->createSheet();

$objPHPExcel->setActiveSheetIndex($sheetNum)
            ->setTitle($sql_row["userName"]);

$objPHPExcel->setActiveSheetIndex($sheetNum)
            ->setCellValue('A1', 'Last ' . $days . ' Days')
            ->setCellValue('B1', 'Start Date: ' . $sql_row["StartDate"])
            ->setCellValue('C1', 'End Date: ' . $sql_row["EndDate"]);

$objPHPExcel->setActiveSheetIndex($sheetNum)
            ->setCellValue('A2', 'User')
            ->setCellValue('B2', 'Login Date')
            ->setCellValue('C2', 'Session End')
            ->setCellValue('D2', 'No Books')
            ->setCellValue('E2', "Time Worked");

$objPHPExcel->setActiveSheetIndex($sheetNum)->getColumnDimension('A')->setWidth(10);
$objPHPExcel->setActiveSheetIndex($sheetNum)->getColumnDimension('B')->setWidth(19);
$objPHPExcel->setActiveSheetIndex($sheetNum)->getColumnDimension('C')->setWidth(19);
$objPHPExcel->setActiveSheetIndex($sheetNum)->getColumnDimension('D')->setWidth(8.30);
$objPHPExcel->setActiveSheetIndex($sheetNum)->getColumnDimension('E')->setWidth(11);


$objPHPExcel->setActiveSheetIndex($sheetNum)->getStyle("A2:E2")->applyFromArray($header_styleArray);



$detailRow = 2;


	$sql_details = "SELECT userName, LoginDate, CASE WHEN LogoffDate is null THEN LastActivity ELSE LogoffDate END AS SessionEnd, COUNT(id) AS NoBooks,
	SEC_TO_TIME(TIME_TO_SEC(CASE WHEN LogoffDate is null THEN LastActivity ELSE LogoffDate END) - TIME_TO_SEC(LoginDate)) AS TimeWorked
	FROM UserSessions
	INNER JOIN Books ON UserSessions.UserHistoryID = Books.SessionID
	WHERE userID = {$sql_row["userID"]} AND LoginDate > DATE_ADD(CURDATE(), INTERVAL -" . $days . " DAY)
	GROUP BY UserHistoryID, userName, LoginDate, LogoffDate, LastActivity
	ORDER BY userName, LoginDate DESC";


	$sql_details_result = GetDatabaseRecords($sql_details);

	while($sql_details_row=mysql_fetch_array($sql_details_result))
	{

	$detailRow++;

	$objPHPExcel->setActiveSheetIndex($sheetNum)
            ->setCellValue("A{$detailRow}", $sql_details_row["userName"])
            ->setCellValue("B{$detailRow}", $sql_details_row["LoginDate"])
            ->setCellValue("C{$detailRow}", $sql_details_row["SessionEnd"])
            ->setCellValue("D{$detailRow}", $sql_details_row["NoBooks"])
            ->setCellValue("E{$detailRow}", $sql_details_row["TimeWorked"]);

	$objPHPExcel->setActiveSheetIndex($sheetNum)->getStyle("A{$detailRow}:E{$detailRow}")->applyFromArray($body_styleArray);


	}

//Output Summary Details
$objPHPExcel->setActiveSheetIndex(0)
	    ->setCellValue("A{$rowCount}", $sql_row["userName"])
	    ->setCellValue("B{$rowCount}", $sql_row["NoBooks"])
	    ->setCellValue("C{$rowCount}", $sql_row["TimeWorked"]); 

$objPHPExcel->setActiveSheetIndex(0)->getStyle("A{$rowCount}:C{$rowCount}")->applyFromArray($body_styleArray);

}

$objPHPExcel->setActiveSheetIndex(0)
	    ->setCellValue('A1', 'Username')
	    ->setCellValue('B1', 'No Books')
	    ->setCellValue('C1', 'Time Worked')
	    ->setTitle('Summary');

$objPHPExcel->setActiveSheetIndex(0);


$objPHPExcel->setActiveSheetIndex(0)->getStyle("A1:C1")->applyFromArray($header_styleArray);

$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(10);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(8.30);
$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(11);


$objPHPExcel->setActiveSheetIndex(0);
//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter = new PHPExcel_Writer_Excel2007 (  $objPHPExcel  );
$objWriter->save('php://output');
?>
