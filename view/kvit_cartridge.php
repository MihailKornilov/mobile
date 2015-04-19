<?php
function pageNum($n) {
	$arr = array(
		1 => 'A',
		2 => 'B',
		3 => 'C',
		4 => 'D',
		5 => 'E',
		6 => 'F',
		7 => 'G',
		8 => 'H',
		9 => 'I',
		10 => 'J',
		11 => 'K',
		12 => 'L',
		13 => 'M',
		14 => 'N',
		15 => 'O',
		16 => 'P',
		17 => 'Q',
		18 => 'R',
		19 => 'S',
		20 => 'T',
		21 => 'U',
		22 => 'V',
		23 => 'W',
		24 => 'X',
		25 => 'Y',
		26 => 'Z'
	);

	$res = '';
	if($n > 26) {
		$res = 'A';
		$n -= 26;
	}

	return $res.$arr[$n];
}//pageNum()
function pageSetup($title) {
	global $book;

	$sheet = $book->getActiveSheet();

	//Глобальные стили для ячеек
	$book->getDefaultStyle()->getFont()->setName('Arial')->setSize(9);

	//Ориентация страницы и  размер листа
	$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT)
		->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	//Поля документа
	$sheet->getPageMargins()->setTop(0.2)
		->setRight(0.2)
		->setLeft(0.2)
		->setBottom(0.2);

	//Масштаб страницы
	$sheet->getSheetView()->setZoomScale(90);

	//Название страницы
	$sheet->setTitle($title);

	//Размеры ячеек
	for($n = 1; $n <= 32; $n++)
		$sheet->getColumnDimension(pageNum($n))->setWidth(3.5);
	for($n = 1; $n <= 32; $n++)
		$sheet->getRowDimension($n)->setRowHeight(13);
}
function xls_cartridge_head($n) {//заголовок с реквизитами
	global $sheet, $z;

	$y = pageNum($n);

	$sheet->setCellValue($y.'2', 'Сервисный центр "КОМТЕКС"');
	$sheet->getStyle($y.'2')->getFont()->setBold(true)->setSize(11);

	$sheet->setCellValue($y.'3', 'Телефоны: 8 (81838) 6 39 91, 8 911 657 86 63');
	$sheet->getStyle($y.'3')->getFont()->setSize(8);

	$sheet->setCellValue($y.'4', 'Адрес: г.Няндома, ул.60 лет Октября 18');
	$sheet->getStyle($y.'4')->getFont()->setSize(8);

	$sheet->setCellValue($y.'5', 'Время работы: Пн-Пт 9-18, без обеда       Сб 10-14');
	$sheet->getStyle($y.'5')->getFont()->setSize(8);

	$sheet->getStyle($y.'6:'.pageNum($n + 13).'6')->applyFromArray(
		array(
			'borders' => array(
				'bottom'     => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			)
		)
	);

	$sheet->setCellValue($y.'8', 'КВИТАНЦИЯ');
	$sheet->getStyle($y.'8')->getFont()->setBold(true)->setSize(10);
	$adr = $y.'8:'.pageNum($n + 13).'8';
	$sheet->mergeCells($adr); //объединение ячеек
	$sheet->getStyle($adr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet->setCellValue($y.'9', 'на заправку-восстановление картриджей № '.$z['nomer']);
	$sheet->getStyle($y.'9')->getFont()->setSize(10);
	$adr = $y.'9:'.pageNum($n + 13).'9';
	$sheet->mergeCells($adr); //объединение ячеек
	$sheet->getStyle($adr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

}//xls_cartridge_head()
function xls_cartridge_border() {//рамки для обоих сторон
	global $sheet;

	$sheet->getStyle('A1:'.pageNum(16).'20')->applyFromArray(array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'FF000000'),
			),
		),
	));

	$sheet->getStyle('A1:'.pageNum(32).'20')->applyFromArray(array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'FF000000'),
			),
		),
	));

}//xls_cartridge_border()
function xls_cartridge_content($col) {//левая сторона
	global $sheet, $z, $c;

	$colLabel = pageNum($col);
	$colItem = pageNum($col + 5);

	$y = array(11, 12, 13, 14, 16, 19, 18);

	$sheet->setCellValue($colLabel.$y[0], 'Заказчик');
	$sheet->getStyle($colLabel.$y[0])->getFont()->setSize(8);
	$sheet->setCellValue($colItem.$y[0], utf8(htmlspecialchars_decode(_clientName($c))));
	$sheet->getStyle($colItem.$y[0])->getFont()->setSize(8);
	xls_comtex_item_border($col, $y[0]);

	$sheet->setCellValue($colLabel.$y[1], 'Контактный тел.');
	$sheet->getStyle($colLabel.$y[1])->getFont()->setSize(8);
	$sheet->setCellValue($colItem.$y[1], utf8(htmlspecialchars_decode(_clientTelefon($c))));
	$sheet->getStyle($colItem.$y[1])->getFont()->setSize(8);
	xls_comtex_item_border($col, $y[1]);

	$sheet->setCellValue($colLabel.$y[2], 'Кол-во картриджей');
	$sheet->setCellValue($colItem.$y[2], $z['cartridge_count']);
	$sheet->getStyle($colLabel.$y[2])->getFont()->setSize(8);
	xls_comtex_item_border($col, $y[2]);

	$sheet->setCellValue($colLabel.$y[3], 'Дата приёма');
	$sheet->setCellValue($colItem.$y[3], utf8(FullData($z['dtime_add'])));
	$sheet->getStyle($colLabel.$y[3])->getFont()->setSize(8);
	xls_comtex_item_border($col, $y[3]);

	$sheet->setCellValue(pageNum($col).$y[4], '____________________ (подпись приёмщика)');
	$sheet->setCellValue(pageNum($col).$y[5], '____________________ (подпись клиента)');
	$sheet->setCellValue(pageNum($col + 12).$y[6], 'М.П.');

}//xls_cartridge_content();
function xls_comtex_item_border($col, $row) {//бордюры для элементов-значений
	global $sheet;

	$colStart = pageNum($col + 5);
	$colEnd = pageNum($col + 13);

	$adr = $colStart.$row.':'.$colEnd.$row;

	$sheet->mergeCells($adr); //объединение ячеек
	$sheet->getStyle($adr)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); //сдвиг вправо
	$sheet->getStyle($adr)->applyFromArray(array( //рисование рамки
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'FF000000'),
			),
		),
	));
}//xls_comtex_item_border()

require_once '../config.php';
require_once API_PATH.'/excel/PHPExcel.php';
set_time_limit(10);

if(!$id = _num($_GET['id']))
	die(win1251('Неверный id заявки.'));

$sql = "SELECT *
		FROM `zayav`
		WHERE `ws_id`=".WS_ID."
		  AND !`deleted`
		  AND `cartridge`
		  AND `zayav_status`
		  AND `id`=".$id;
if(!$z = query_assoc($sql))
	die(win1251('Заявки не существует.'));

$c = query_assoc("SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$z['client_id']);

$book = new PHPExcel();
$book->setActiveSheetIndex(0);
$sheet = $book->getActiveSheet();

pageSetup('Квитанция');
xls_cartridge_border();
xls_cartridge_head(2);
xls_cartridge_head(18);
xls_cartridge_content(2);
xls_cartridge_content(18);

header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="cartridge'.$z['nomer'].'_'.strftime('%Y-%m-%d').'.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;


