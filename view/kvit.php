<?php
function _clr($v='FFFFFF') {
	if(strlen($v) == 3)
		$v = $v[0].$v[0].$v[1].$v[1].$v[2].$v[2];
	return array('color' => $v);
}
function tabInfo() {
	global $section, $zayav, $client, $i;
	$label = _clr('555');
	$table = $section->addTable();
	$section->addText('-', _size(4) + _clr(), _bot());

	$table = $section->addTable('tabStyle');
	$table->addRow();
	$table->addCell(W_LABEL)->addText('Заказчик:', $label, _bot() + _top(2));
	$table->addCell(W_TEXT)->addText(utf8($client['fio']), null, _bot() + _top(2));
	if($client['telefon']) {
		$table->addRow();
		$table->addCell(W_LABEL)->addText('Контактный телефон:', $label, _bot());
		$table->addCell(W_TEXT)->addText(utf8($client['telefon']), null, _bot());
	}
	$section->addText('-', _size(4) + _clr(), _bot());


	$table = $section->addTable('tabStyle');
	$table->addRow();
	$table->addCell(W_LABEL + W_TEXT)->addText('Неисправность со слов Заказчика:', $label, _bot() + _top(2));
	$table->addRow();
	$table->addCell(W_LABEL + W_TEXT)->addText('Не включается', $i, _bot());
}

function pageSetup($title) {
	global $book;

	$sheet = $book->getActiveSheet();

	//Глобальные стили для ячеек
	$book->getDefaultStyle()
		->getFont()->setName('Times New Roman')
		->setSize(11);

	//Ориентация страницы и  размер листа
	$sheet->getPageSetup()
		->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT)
		->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

	//Поля документа
	$sheet->getPageMargins()
		->setTop(0.5)
		->setRight(0.5)
		->setLeft(0.5)
		->setBottom(0.5);

	//Масштаб страницы
	$sheet->getSheetView()->setZoomScale(100);

	//Название страницы
	$sheet->setTitle($title);
}
function kvit_head() {
	global $book, $ln;
	$sheet = $book->getActiveSheet();

	$sheet->mergeCells('A'.$ln.':I'.$ln);

	$txt = new PHPExcel_RichText();
	$txt->createText('Мастерская «');
	$t = $txt->createTextRun('Ремонт мобильных телефонов в Няндоме');
	$t->getFont()->setBold(true);
	$txt->createTextRun('»');
	$sheet->setCellValue('A'.$ln, $txt);
	$sheet->getStyle('A'.$ln)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$ln++;

	$sheet->mergeCells('A'.$ln.':I'.$ln);
	$sheet->setCellValue('A'.$ln, 'Адрес: г.Няндома, ул.Североморская, рядом с магазином "Уют".');
	$sheet->getStyle('A'.$ln)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A'.$ln)->getFont()->setSize(9);
	$ln++;

	$sheet->mergeCells('A'.$ln.':I'.$ln);
	$sheet->setCellValue('A'.$ln, 'Телефон: 8 964 299 94 89. Время работы: пн-пт, 10:00-19:00.');
	$sheet->getStyle('A'.$ln)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A'.$ln)->getFont()->setSize(9);
	$sheet->getStyle('A'.$ln.':I'.$ln)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	$ln++;
}//kvit_head()
function kvit_name($nomer) {
	global $book, $ln;
	$sheet = $book->getActiveSheet();

	$sheet->mergeCells('A'.$ln.':I'.$ln);
	$sheet->setCellValue('A'.$ln, 'Квитанция №'.$nomer);
	$sheet->getStyle('A'.$ln)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('A'.$ln)->getFont()->setSize(18);
	$sheet->getStyle('A'.$ln)->getFont()->setBold(true);
	$ln++;
}//kvit_name()
function kvit_content() {
	global $book, $ln, $z, $c;
	$sheet = $book->getActiveSheet();

	define('COL', 'C');
	define('START', $ln);

	$sheet->setCellValue('A'.$ln, 'Дата приёма:');
	$sheet->setCellValue(COL.$ln, utf8(FullData($z['dtime_add'])));
	$ln++;

	$sheet->setCellValue('A'.$ln, 'Устройство:');
	$sheet->setCellValue(COL.$ln, utf8(_deviceName($z['base_device_id'])._vendorName($z['base_vendor_id'])._modelName($z['base_model_id'])));
	$ln++;

	if($z['color_id']) {
		$sheet->setCellValue('A'.$ln, 'Цвет:');
		$sheet->setCellValue(COL.$ln, utf8(_color($z['color_id'], $z['color_dop'])));
		$ln++;
	}

	if($z['imei']) {
		$sheet->setCellValue('A'.$ln, 'IMEI:');
		$sheet->setCellValue(COL.$ln, utf8($z['imei']));
		$ln++;
	}

	if($z['serial']) {
		$sheet->setCellValue('A'.$ln, 'Серийный номер:');
		$sheet->setCellValue(COL.$ln, utf8($z['serial']));
		$ln++;
	}

	if($z['equip']) {
		$sheet->setCellValue('A'.$ln, 'Комплектация:');
		$sheet->setCellValue(COL.$ln, utf8(zayavEquipSpisok($z['equip'])));
		$ln++;
	}

	$sheet->getStyle('A'.START.':A'.($ln - 1))
		->getFont()
		->getColor()->setRGB('555555');
}//kvit_content()



require_once '../config.php';
require_once API_PATH.'/excel/PHPExcel.php';
require_once(DOCUMENT_ROOT.'/view/ws.php');

set_time_limit(10);

if(!$id = _isnum($_GET['id']))
	die(win1251('Неверный id заявки.'));

$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `zayav_status` AND `id`=".$id;
if(!$z = query_assoc($sql))
	die(win1251('Заявки не существует.'));

$c = query_assoc("SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$z['client_id']);

$book = new PHPExcel();
$book->setActiveSheetIndex(0);

pageSetup('Квитанция');

$ln = 1;
kvit_head();
kvit_name($z['nomer']);
kvit_content();

header('Content-Type:application/vnd.ms-excel');
header('Content-Disposition:attachment;filename="kvit_'.$id.'.xls"');
$writer = PHPExcel_IOFactory::createWriter($book, 'Excel5');
$writer->save('php://output');

mysql_close();
exit;






$section->addText('-', _size(4) + _clr(), _bot());
define('W_LABEL', mm(42));
define('W_TEXT', mm(90));
$word->addTableStyle('tabStyle', array(
	'borderTopSize' => 6,
	'borderTopColor' => '777777'
));
tabInfo();
$section->addText('-', _size(4) + _clr(), _bot());






$table = $section->addTable('tabStyle');
$table->addRow();
$table->addCell(mm(200))->addText('Условия проведения ремонта:', _clr(555), _bot() + _top(2));
$listStyle = array('listType' => PHPWord_Style_ListItem::TYPE_BULLET_FILLED);
$section->addListItem('Диагностика оборудования, принятого в ремонт, производится бесплатно;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('Стороны предварительно договариваются о стоимости ремонта в устной форме;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('Мастерская устраняет только заявленные неисправности;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('Настоятельно рекомендуем Вам сохранять все данные, хранящиеся в памяти изделия, на других носителях;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('Мастерская не несет ответственности за возможную потерю информации на устройствах хранения и записи данных;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('После окончания ремонта сотрудник Мастерской сообщает Заказчику о готовности;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('Аппараты, невостребованные в течение 3 месяцев после уведомления Заказчика о готовности или невозможности ремонта, могут быть реализованы в установленном законом порядке для погашения задолженности Заказчика перед Мастерской;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('Срок гарантии составляет 30 дней с момента выдачи изделия Заказчику;', 0, $i + _size(9), $listStyle, _bot() + _top());
$section->addListItem('На аппараты, подвергшиеся воздействию влаги, удару, гарантийные обязательства не распространяются;', 0, $i + _size(9), $listStyle, _bot() + _top());


$section->addText('С условиями ремонта согласен(а).   Подпись Заказчика: ________________________________', null, _bot() + _top(4));
$table = $section->addTable();
$table->addRow();
$table->addCell(mm(160))->addText('Аппарат принял: __________________________ ('.utf8(_viewer(VIEWER_ID, 'name')).')', null, _bot() + _top(4));
$table->addCell(mm(40))->addText(utf8(FullData(curTime())), null, _bot() + _top(4) + $right);


//----------------------------------------------------------------------------------------------------------------------
$section->addText('-', _size(9) + _clr(), _bot());
$word->addTableStyle('cutLine', array(
	'borderTopSize' => 20,
	'borderTopColor' => '000000'
));
$table = $section->addTable('cutLine');
$table->addRow();
$table->addCell(mm(200))->addText('линия отреза', _size(8) + _clr('777'), $center + _bot(3) + _top());


// Заголовок
$table = $section->addTable();
$table->addRow();
$cell = $table->addCell(mm(200));
$cell->addText('Квитанция №'.$zayav['nomer'], $b + _size(18), $center + _bot());


$section->addText('-', _size(4) + _clr(), _bot());
tabInfo();
$section->addText('С условиями ремонта согласен(а).   Подпись Заказчика: ________________________________', null, _bot() + _top(4));
$table = $section->addTable();
$table->addRow();
$table->addCell(mm(160))->addText('Аппарат принял: __________________________ ('.utf8(_viewer(VIEWER_ID, 'name')).')', null, _bot() + _top(4));
$table->addCell(mm(40))->addText(utf8(FullData(curTime())), null, _bot() + _top(4) + $right);



$section->addImage(APP_PATH.'/files/images/zayav3563-w3td2ojt0a-big.jpg');


mysql_close();

header('Content-Type:application/vnd.openxmlformats-officedocument.wordprocessingml.document');
//header('Content-Type:application/vnd.ms-word');
header('Content-Disposition:attachment;filename="kvit_'.$id.'.doc"');
header('Cache-Control:max-age=0');
$writer = PHPWord_IOFactory::createWriter($word, 'Word2007');
$writer->save('php://output');

exit;
