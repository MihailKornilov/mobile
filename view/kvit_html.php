<?php
function kvit_head() {
	return
	'<div class="head">'.
		'<h1>Мастерская «<b>Ремонт мобильных телефонов в Няндоме</b>»</h1>'.
		'<h2>Адрес: г.Няндома, ул.Североморская, рядом с магазином "Уют".</h2>'.
		'<h2>Телефон: 8 964 299 94 89. Время работы: пн-пт, 10:00-19:00.</h2>'.
	'</div>';
}//kvit_head()
function kvit_name($nomer) {
	return '<div class="name">Квитанция №'.$nomer.'</div>';
}//kvit_name()
function kvit_content($z) {
	$c = query_assoc("SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$z['client_id']);
	return
	'<table class="content">'.
		'<tr><td class="label">Дата приёма:<td>'.FullData($z['dtime_add']).
		'<tr><td class="label">Устройство:'.
			'<td>'._deviceName($z['base_device_id']).
				'<b>'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</b>'.
		($z['color_id'] ? '<tr><td class="label">Цвет:<td>'._color($z['color_id'], $z['color_dop']) : '').
		($z['imei'] ? '<tr><td class="label">IMEI:<td>'.$z['imei'] : '').
		($z['serial'] ? '<tr><td class="label">Серийный номер:<td>'.$z['serial'] : '').
		($z['equip'] ? '<tr><td class="label">Комплектация:<td>'.zayavEquipSpisok($z['equip']) : '').
	'</table>'.
	'<div class="line"></div>'.
	'<table class="content">'.
		'<tr><td class="label">Заказчик:<td>'.$c['fio'].
		'<tr><td class="label">Контактные телефоны:<td>'.$c['telefon'].
	'</table>'.
	'<div class="line"></div>'.
	'<table class="content">'.
		'<tr><td class="label fault">Неисправность со слов Заказчика:'.
		'<tr><td>не включается'.
	'</table>';
}//kvit_content()
function kvit_conditions() {
	return
	'<div class="conditions">'.
		'<div class="label">Условия проведения ремонта:</div>'.
		'<ul><li>Диагностика оборудования, принятого в ремонт, производится бесплатно;'.
			'<li>Стороны предварительно договариваются о стоимости ремонта в устной форме;'.
			'<li>Мастерская устраняет только заявленные неисправности;'.
			'<li>Настоятельно рекомендуем Вам сохранять все данные, хранящиеся в памяти изделия, на других носителях;'.
			'<li>Мастерская не несет ответственности за возможную потерю информации на устройствах хранения и записи данных;'.
			'<li>После окончания ремонта сотрудник Мастерской сообщает Заказчику о готовности;'.
			'<li>Аппараты, невостребованные в течение 3 месяцев после уведомления Заказчика о готовности или невозможности ремонта, '.
				'могут быть реализованы в установленном законом порядке для погашения задолженности Заказчика перед Мастерской;'.
			'<li>Срок гарантии составляет 30 дней с момента выдачи изделия Заказчику;'.
			'<li>На аппараты, подвергшиеся воздействию влаги, удару, гарантийные обязательства не распространяются.'.
		'</ul>'.
	'</div>';
}//kvit_conditions()
function kvit_podpis($bottom=0) {
	return
	'<div class="podpis'.($bottom ? ' bottom' : '').'">'.
		'<h1>С условиями ремонта согласен(а).<span>Подпись Заказчика: ________________________________</span></h1>'.
		'<h2>Аппарат принял: __________________________ ('._viewer(VIEWER_ID, 'name').')'.
			'<em>'.FullData(curTime()).'</em>'.
		'</h2>'.
	'</div>';
}//kvit_podpis()
function kvit_cut() {
	return '<div class="cut">линия отреза</div>';
}//kvit_cut()

require_once '../config.php';
require_once(DOCUMENT_ROOT.'/view/ws.php');

if(!$id = _isnum($_GET['id']))
	die(win1251('Неверный id заявки.'));

$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `zayav_status` AND `id`=".$id;
if(!$z = query_assoc($sql))
	die(win1251('Заявки не существует.'));


echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
	'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
	'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>Квитанция по заявке №'.$z['nomer'].'</title>'.
		'<link href="'.SITE.'/css/kvit_html'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
	'</head>'.
	'<body>'.
		kvit_head().
		kvit_name($z['nomer']).
		kvit_content($z).
		kvit_conditions().
		kvit_podpis().
		kvit_cut().
		kvit_name($z['nomer']).
		kvit_content($z).
		kvit_podpis(1).
		'<a onclick="this.style.display=\'none\';window.print()">Печать</a>'.
	'</body>'.
	'</html>';


mysql_close();
exit;


