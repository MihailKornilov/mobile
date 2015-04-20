<?php
//Первая буква строки заглавная
mb_internal_encoding('UTF-8');
function mb_ucfirst($text) {
	return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

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
	$book->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

	//Ориентация страницы и  размер листа
	$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT)
		->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	//Поля документа
	$sheet->getPageMargins()->setTop(0.9)
		->setRight(0.2)
		->setLeft(0.8)
		->setBottom(0.2);

	//Масштаб страницы
	$sheet->getSheetView()->setZoomScale(100);

	//Название страницы
	$sheet->setTitle($title);
}
function xls_schet_width() {//установка ширины столбцов
	global $sheet;

	$sheet->getColumnDimension('A')->setWidth(3);
	$sheet->getColumnDimension('B')->setWidth(40);
	$sheet->getColumnDimension('C')->setWidth(9);
	$sheet->getColumnDimension('D')->setWidth(8);
	$sheet->getColumnDimension('E')->setWidth(14);
	$sheet->getColumnDimension('F')->setWidth(14);
}//xls_schet_width()
function xls_schet_top() {
	global $sheet, $ws;

	$sheet->setCellValue('A1', utf8($ws['org_name']));
	$sheet->getStyle('A1')->getFont()->setBold(true)->setUnderline(true);

	$sheet->setCellValue('A3', 'Адрес: '.utf8($ws['adres_yur']));
	$sheet->getStyle('A3')->getFont()->setBold(true);

	$sheet->setCellValue('A4', 'Телефон: '.utf8($ws['telefon']));
	$sheet->getStyle('A4')->getFont()->setBold(true);
}//xls_schet_top()
function xls_schet_rekvisit() {
	global $sheet, $ws;

	$sheet->setCellValue('A6', 'Образец заполнения платежного поручения');
	$sheet->getStyle('A6')->getFont()->setBold(true);
	$sheet->mergeCells('A6:F6');
	$sheet->getStyle('A6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


	$ram = array(
		'borders' => array(
			'outline' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('argb' => 'FF000000'),
			),
		),
	);
	$sheet->getStyle('A7:F11')->applyFromArray($ram); //общая рамка

	$sheet->setCellValue('A7', 'ИНН '.utf8($ws['inn']).'                      КПП');
	$sheet->getStyle('A7:C7')->applyFromArray($ram);

	$sheet->setCellValue('A8', 'Получатель');
	$sheet->setCellValue('A9', utf8($ws['org_name']));
	$sheet->getStyle('A8:C9')->applyFromArray($ram);

	$sheet->setCellValue('A10', 'Банк получателя');
	$sheet->setCellValue('A11', utf8($ws['bank_name']));
	$sheet->setCellValue('D9', 'Сч. №');
	$sheet->setCellValue('E9', utf8($ws['schet']).' ');
	$sheet->getStyle('E7:F9')->applyFromArray($ram);

	$sheet->setCellValue('D10', 'БИК');
	$sheet->getStyle('D10:D10')->applyFromArray($ram);

	$sheet->setCellValue('E10', utf8($ws['bik']).' ');
	$sheet->setCellValue('D11', 'Сч. №');
	$sheet->getStyle('D11:D11')->applyFromArray($ram);
	$sheet->setCellValue('E11', utf8($ws['kor_schet']).' ');

	$sheet->getStyle('D9:D11')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
}//xls_schet_rekvisit()
function xls_schet_head() {
	global $sheet, $c, $s;

	$sheet->mergeCells('A13:F13');
	$sheet->setCellValue('A13', 'СЧЕТ № СЦ'.$s['nomer'].' от '.utf8(FullData($s['date_create'])).' г.');
	$sheet->getStyle('A13')->getFont()->setBold(true)->setSize(14);
	$sheet->getStyle('A13')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$client = utf8(htmlspecialchars_decode(_clientName($c)));
	$sheet->setCellValue('A16', 'Плательщик:        '.$client);
	$sheet->setCellValue('A17', 'Грузополучатель: '.$client);
}//xls_schet_head()
function xls_schet_tabHead() {//заголовок колонок таблицы
	global $sheet;

	$ram = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		),
		'alignment' => array(
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'wrap' => true
		)
	);
	$sheet->getStyle('A19:F19')->applyFromArray($ram);

	$sheet->getRowDimension('19')->setRowHeight(51);

	$sheet->setCellValue('A19', '№');
	$sheet->setCellValue('B19', "Наименование\nтовара");
	$sheet->setCellValue('C19', "Единица\nизме-\nрения");
	$sheet->setCellValue('D19', "Коли-\nчество");
	$sheet->setCellValue('E19', 'Цена');
	$sheet->setCellValue('F19', 'Сумма');
}//xls_schet_tabHead()
function xls_tabContent($line) {
	global $sheet, $z, $s;

	$sql = "SELECT *
			FROM `zayav_cartridge`
			WHERE `zayav_id`=".$z['id']."
			  AND (`filling` OR `restore` OR `chip`)
			  AND `cost`
			  AND `schet_id`=".$s['id']."
			ORDER BY `id`";
	$q = query($sql);
	$start = $line;
	$n = 1;
	$sum = 0;
	while($r = mysql_fetch_assoc($q)) {
		$prim = array();
		if($r['filling'])
			$prim[] = 'заправка';
		if($r['restore'])
			$prim[] = 'восстановление';
		if($r['chip'])
			$prim[] = 'замена чипа у';

		$txt = implode(', ', $prim).' картриджа '._cartridgeName($r['cartridge_id']).($r['prim'] ? ', '.utf8($r['prim']) : '');

		$sheet->getCell('A'.$line)->setValue($n);
		$sheet->getCell('B'.$line)->setValue(mb_ucfirst($txt));
		$sheet->getCell('C'.$line)->setValue('шт');
		$sheet->getCell('D'.$line)->setValue('1');
		$sheet->getCell('E'.$line)->setValue($r['cost'].',00');
		$sheet->getCell('F'.$line)->setValue($r['cost'].',00');
		$line++;
		$n++;
		$sum += $r['cost'];
	}

	$sheet->getStyle('B'.$start.':B'.($line - 1))->getAlignment()->setWrapText(true);

	//рамка для списка
	$ram = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		),
		'alignment' => array(
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	);
	$sheet->getStyle('A'.$start.':F'.($line - 1))->applyFromArray($ram);

	//центрирование шт в списке
	$sheet->getStyle('C'.$start.':C'.($line - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	//цены и стоимость вправо
	$sheet->getStyle('E'.$start.':F'.($line - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	return array(
		'line' => $line,
		'n' => $n,
		'sum' => $sum
	);
}//xls_tabContent()
function xls_schet_tab() {
	global $sheet;

	$start = 20;
	$arr = xls_tabContent($start);
	$line = $arr['line'];
	$n = $arr['n'];
	$sum = $arr['sum'];

	//итог
	$sheet->getCell('E'.$line)->setValue('Итого:');
	$sheet->getCell('F'.$line)->setValue($sum.',00');

	$sheet->getCell('E'.($line + 1))->setValue('Без налога (НДС).');
	$sheet->getCell('F'.($line + 1))->setValue('-              ');

	$sheet->getCell('E'.($line + 2))->setValue('Всего к оплате:');
	$sheet->getCell('F'.($line + 2))->setValue($sum.',00');

	$sheet->getStyle('E'.$line.':F'.($line + 2))->getFont()->setBold(true);

	//цены, сумма и итог вправо
	$sheet->getStyle('E'.$start.':F'.($line + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	//рамка для итога
	$sheet->getStyle('F'.$line.':F'.($line + 2))->applyFromArray(array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	));

	$line += 4;
	$sheet->getCell('A'.$line)->setValue('Всего наименований '.($n - 1).', на сумму '.$sum.'.00');
	$line++;
	$sheet->getCell('A'.$line)->setValue(mb_ucfirst(utf8(_numToWord($sum))).' рублей 00 копеек');
	$sheet->getStyle('A'.$line)->getFont()->setBold(true);

	return $line;
}//xls_schet_tab()
function xls_schet_podpis($line) {
	global $sheet;

	$line += 3;
	$sheet->getCell('A'.$line)->setValue('Руководитель предприятия_____________________ (Шерстянников С.Ю.)');
	$line += 3;
	$sheet->getCell('A'.$line)->setValue('Главный бухгалтер____________________________ (Шерстянникова Я.В.)');
}//xls_schet_podpis()


function xls_act_width() {//установка ширины столбцов
	global $sheet;

	$sheet->getColumnDimension('A')->setWidth(3);
	$sheet->getColumnDimension('B')->setWidth(39);
	$sheet->getColumnDimension('C')->setWidth(9);
	$sheet->getColumnDimension('D')->setWidth(12);
	$sheet->getColumnDimension('E')->setWidth(12);
	$sheet->getColumnDimension('F')->setWidth(13);
}//xls_act_width()
function xls_act_top() {
	global $sheet, $ws;

	$sheet->setCellValue('A1', utf8($ws['org_name']));
	$sheet->getStyle('A1')->getFont()->setBold(true)->setUnderline(true);

	$sheet->setCellValue('A2', 'Адрес: '.utf8($ws['adres_yur']));
	$sheet->getStyle('A2')->getFont()->setBold(true);
}//xls_act_top()
function xls_act_head() {
	global $sheet, $c, $s;

	$sheet->mergeCells('A4:F4');
	$sheet->setCellValue('A4', 'Акт № СЦ'.$s['nomer'].' от '.utf8(FullData($s['date_create'])).' г.');
	$sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
	$sheet->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$client = utf8(htmlspecialchars_decode(_clientName($c)));
	$sheet->setCellValue('A6', 'Заказчик: '.$client);
}//xls_act_head()
function xls_act_tabHead() {//заголовок колонок таблицы
	global $sheet;

	$line = 8;

	$ram = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		),
		'alignment' => array(
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'wrap' => true
		)
	);
	$sheet->getStyle('A'.$line.':F'.$line)->applyFromArray($ram);

	$sheet->setCellValue('A'.$line, '№');
	$sheet->setCellValue('B'.$line, 'Наименование работы (услуги)');
	$sheet->setCellValue('C'.$line, 'Ед. изм.');
	$sheet->setCellValue('D'.$line, 'Количество');
	$sheet->setCellValue('E'.$line, 'Цена');
	$sheet->setCellValue('F'.$line, 'Сумма');
}//xls_act_tabHead()
function xls_act_tab() {
	global $sheet;

	$start = 9;
	$arr = xls_tabContent($start);
	$line = $arr['line'];
	$sum = $arr['sum'];

	//итого
	$sheet->getCell('E'.$line)->setValue('Итого:');
	$sheet->getCell('F'.$line)->setValue($sum.',00');

	$sheet->getStyle('E'.$line.':F'.$line)->getFont()->setBold(true);

	//цены, сумма и итог вправо
	$sheet->getStyle('E'.$start.':F'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	//рамка для итога
	$sheet->getStyle('F'.$line.':F'.$line)->applyFromArray(array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array('rgb' => '000000')
			)
		)
	));

	$line += 2;
	$sheet->getCell('A'.$line)->setValue('Всего оказано услуг на сумму: '.mb_ucfirst(utf8(_numToWord($sum))).' рублей 00 копеек.');
	$sheet->getStyle('A'.$line)->getFont()->setItalic(true);

	return $line;
}//xls_act_tab()
function xls_act_podpis($line) {
	global $sheet;

	$line++;
	$sheet->getCell('A'.$line)->setValue(
		'Вышеперечисленные услуги выполнены полностью и в срок. '.
		'Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.'
	);
	$sheet->mergeCells('A'.$line.':F'.$line);
	$sheet->getRowDimension($line)->setRowHeight(40);
	$sheet->getStyle('A'.$line)->getAlignment()->setWrapText(true);

	$line += 2;
	$sheet->getCell('A'.$line)->setValue('Исполнитель:');
	$sheet->getCell('C'.$line)->setValue('Заказчик:');

	$line++;
	$sheet->getRowDimension($line)->setRowHeight(9);
	$sheet->getCell('B'.$line)->setValue('подпись');
	$sheet->getStyle('B'.$line)->getFont()->setSize(6);
	$sheet->getStyle('B'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet->getCell('E'.$line)->setValue('подпись');
	$sheet->getStyle('E'.$line)->getFont()->setSize(6);

	$line += 2;
	$sheet->getCell('B'.$line)->setValue('                   М.П.');
	$sheet->getCell('D'.$line)->setValue('      М.П.');
}//xls_act_podpis()

function xls_act_podpis2($line) {
	global $sheet;

	$line++;
	$sheet->getCell('A'.$line)->setValue(
		'Вышеперечисленные услуги выполнены полностью и в срок. '.
		'Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.'
	);
	$sheet->mergeCells('A'.$line.':F'.$line);
	$sheet->getRowDimension($line)->setRowHeight(40);
	$sheet->getStyle('A'.$line)->getAlignment()->setWrapText(true);

	$line += 2;
	$sheet->getCell('A'.$line)->setValue('Исполнитель:');
	$sheet->getCell('C'.$line)->setValue('Заказчик:');

	$line++;
	$sheet->getRowDimension($line)->setRowHeight(9);
	$sheet->getCell('B'.$line)->setValue('подпись');
	$sheet->getStyle('B'.$line)->getFont()->setSize(6);
	$sheet->getStyle('B'.$line)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet->getCell('E'.$line)->setValue('подпись');
	$sheet->getStyle('E'.$line)->getFont()->setSize(6);

	$line += 2;
	$sheet->getCell('B'.$line)->setValue('                   М.П.');
	$sheet->getCell('C'.$line)->setValue('Расшифровка подписи (ФИО): __________________');
	$line++;
	$sheet->getCell('C'.$line)->setValue('_____________________________________________');
	$line += 2;
	$sheet->getCell('C'.$line)->setValue('Дата и время выдачи: ___/___/______      ____:____');
	$line += 2;
	$sheet->getCell('D'.$line)->setValue('      М.П.');
}//xls_act_podpis2()

require_once '../config.php';
require_once API_PATH.'/excel/PHPExcel.php';
set_time_limit(10);

if(!$schet_id = _num(@$_GET['schet_id']))
	die(win1251('Неверный id счёта.'));

$sql = "SELECT *
		FROM `zayav_schet`
		WHERE !`deleted`
		  AND `id`=".$schet_id;
if(!$s = query_assoc($sql))
	die(win1251('Счёта не существует.'));

$sql = "SELECT *
		FROM `zayav`
		WHERE `ws_id`=".WS_ID."
		  AND !`deleted`
		  AND `cartridge`
		  AND `zayav_status`
		  AND `id`=".$s['zayav_id'];
if(!$z = query_assoc($sql))
	die(win1251('Заявки не существует.'));

$c = query_assoc("SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$z['client_id']);
$ws = query_assoc("SELECT * FROM `workshop` WHERE `id`=".WS_ID);



$book = new PHPExcel();
$book->setActiveSheetIndex(0);
$sheet = $book->getActiveSheet();
pageSetup('Счёт');
xls_schet_width();
xls_schet_top();
xls_schet_rekvisit();
xls_schet_head();
xls_schet_tabHead();
$line = xls_schet_tab();
xls_schet_podpis($line);

$book->createSheet();
$book->setActiveSheetIndex(1);
$sheet = $book->getActiveSheet();
pageSetup('Акт выполненных работ');
xls_act_width();
xls_act_top();
xls_act_head();
xls_act_tabHead();
$line = xls_act_tab();
xls_act_podpis($line);

$book->createSheet();
$book->setActiveSheetIndex(2);
$sheet = $book->getActiveSheet();
pageSetup('Акт (передаётся в бухгалтерию)');
xls_act_width();
xls_act_top();
xls_act_head();
xls_act_tabHead();
$line = xls_act_tab();
xls_act_podpis2($line);


$book->setActiveSheetIndex(0);






header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="schet-cartridge'.$z['nomer'].'_'.TODAY.'.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;


