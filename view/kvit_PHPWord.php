<?php
function mm($millimeters){
	return floor($millimeters*56.7); //1 твип равен 1/567 сантиметра
}//mm()
function _top($v=0) {
	return array('spaceBefore' => mm($v));
}
function _bot($v=0) {
	return array('spaceAfter' => mm($v));
}
function _size($v=0) {
	return array('size' => $v);
}
function _clr($v='FFFFFF') {
	if(strlen($v) == 3)
		$v = $v[0].$v[0].$v[1].$v[1].$v[2].$v[2];
	return array('color' => $v);
}
function tabInfo() {
	global $section, $zayav, $client, $i;
	$label = _clr('555');
	$table = $section->addTable();
	$table->addRow();
	$table->addCell(W_LABEL)->addText('Дата приёма:', $label, _bot());
	$table->addCell(W_TEXT)->addText(utf8(FullData($zayav['dtime_add'])), null, _bot());
	$table->addRow();
	$table->addCell(W_LABEL)->addText('Устройство:', $label, _bot());
	$table->addCell(W_TEXT)->addText(utf8(_deviceName($zayav['base_device_id'])._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id'])), null, _bot());
	if($zayav['color_id']) {
		$table->addRow();
		$table->addCell(W_LABEL)->addText('Цвет:', $label, _bot());
		$table->addCell(W_TEXT)->addText(utf8(_color($zayav['color_id'])), null, _bot());
	}
	if($zayav['imei']) {
		$table->addRow();
		$table->addCell(W_LABEL)->addText('IMEI:', $label, _bot());
		$table->addCell(W_TEXT)->addText(utf8($zayav['imei']), null, _bot());
	}
	if($zayav['serial']) {
		$table->addRow();
		$table->addCell(W_LABEL)->addText('Серийный номер:', $label, _bot());
		$table->addCell(W_TEXT)->addText(utf8($zayav['serial']), null, _bot());
	}
	if($zayav['equip']) {
		$table->addRow();
		$table->addCell(W_LABEL)->addText('Комплектация:', $label, _bot());
		$table->addCell(W_TEXT)->addText(utf8(zayavEquipSpisok($zayav['equip'])), null, _bot());
	}
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




require_once('../config.php');
require_once API_PATH.'/word/PHPWord.php';
require_once(DOCUMENT_ROOT.'/view/ws.php');

if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
	echo 'Неверный id заявки';
	exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `id`=".$id." LIMIT 1";
if(!$zayav = mysql_fetch_assoc(query($sql))) {
	echo 'Заявки не существует';
	exit;
}

$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `deleted`=0 AND `id`=".$zayav['client_id']." LIMIT 1";
$client = mysql_fetch_assoc(query($sql));

$word = new PHPWord();
$word->setDefaultFontName('Times New Roman');
$word->setDefaultFontSize(12);

$section = $word->createSection(array(
	'orientation' => null,
	'marginLeft' => mm(5),
	'marginRight' => mm(5),
	'marginTop' => mm(5),
	'marginBottom' => mm(5)
));

$empty = array();
$b = array('bold' => true);
$i = array('italic' => true);
$center = array('align' => 'center');
$right = array('align' => 'right');

$run = $section->createTextRun($right + _bot());
$run->addText('Мастерская «');
$run->addText('Ремонт мобильных телефонов в Няндоме', $b);
$run->addText('»');

$section->addText('Адрес: г.Няндома, ул.Североморская, рядом с магазином "Уют".', _size(9), $right + _bot());
$section->addText('Телефон: 8 964 299 94 89. Время работы: пн-пт, 10:00-19:00.', _size(9), $right + _bot(2));





$word->addTableStyle('styleHead', array(
	'borderTopSize' => 6,
	'borderTopColor' => '777777',
	'cellMarginTop' => mm(3)
));
$table = $section->addTable('styleHead');
$table->addRow();
$cell = $table->addCell(mm(200));
$cell->addText('Квитанция №'.$zayav['nomer'], $b + _size(18), $center + _bot());



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



//$section->addImage('http://mobile.nyandoma.ru/files/images/dev522-ymgf4ngsyu-b.jpg');


mysql_close();

header('Content-Type:application/vnd.openxmlformats-officedocument.wordprocessingml.document');
//header('Content-Type:application/vnd.ms-word');
header('Content-Disposition:attachment;filename="kvit_'.$id.'.doc"');
header('Cache-Control:max-age=0');
$writer = PHPWord_IOFactory::createWriter($word, 'Word2007');
$writer->save('php://output');

exit;
