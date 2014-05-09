<?php
function kvit_head() {
	return
	'<div class="head">'.
		'<h1>���������� �<b>������ ��������� ��������� � �������</b>�</h1>'.
		'<h2>�����: �.�������, ��.�������������, ����� � ��������� "���".</h2>'.
		'<h2>�������: 8 964 299 94 89. ����� ������: ��-��, 10:00-19:00.</h2>'.
	'</div>';
}//kvit_head()
function kvit_name($nomer) {
	return '<div class="name">��������� �'.$nomer.'</div>';
}//kvit_name()
function kvit_content($k) {
	return
	'<table class="content">'.
		'<tr><td class="label">���� �����:<td>'.FullData($k['dtime']).
		'<tr><td class="label">����������:'.
			'<td>'._deviceName($k['device_id']).
				'<b>'._vendorName($k['vendor_id'])._modelName($k['model_id']).'</b>'.
		($k['color_id'] ? '<tr><td class="label">����:<td>'._color($k['color_id'], $k['color_dop']) : '').
		($k['imei'] ? '<tr><td class="label">IMEI:<td>'.$k['imei'] : '').
		($k['serial'] ? '<tr><td class="label">�������� �����:<td>'.$k['serial'] : '').
		($k['equip'] ? '<tr><td class="label">������������:<td>'.zayavEquipSpisok($k['equip']) : '').
	'</table>'.
	'<div class="line"></div>'.
	'<table class="content">'.
		'<tr><td class="label">��������:<td>'.$k['client_fio'].
		'<tr><td class="label">���������� ��������:<td>'.$k['client_telefon'].
	'</table>'.
	'<div class="line"></div>'.
	'<table class="content">'.
		'<tr><td class="label fault">������������� �� ���� ���������:'.
		'<tr><td>�� ����������'.
	'</table>';
}//kvit_content()
function kvit_conditions() {
	return
	'<div class="conditions">'.
		'<div class="label">������� ���������� �������:</div>'.
		'<ul><li>����������� ������������, ��������� � ������, ������������ ���������;'.
			'<li>������� �������������� �������������� � ��������� ������� � ������ �����;'.
			'<li>���������� ��������� ������ ���������� �������������;'.
			'<li>������������ ����������� ��� ��������� ��� ������, ���������� � ������ �������, �� ������ ���������;'.
			'<li>���������� �� ����� ��������������� �� ��������� ������ ���������� �� ����������� �������� � ������ ������;'.
			'<li>����� ��������� ������� ��������� ���������� �������� ��������� � ����������;'.
			'<li>��������, ���������������� � ������� 3 ������� ����� ����������� ��������� � ���������� ��� ������������� �������, '.
				'����� ���� ����������� � ������������� ������� ������� ��� ��������� ������������� ��������� ����� ����������;'.
			'<li>���� �������� ���������� 30 ���� � ������� ������ ������� ���������;'.
			'<li>�� ��������, ������������ ����������� �����, �����, ����������� ������������� �� ����������������.'.
		'</ul>'.
	'</div>';
}//kvit_conditions()
function kvit_podpis($bottom=0) {
	return
	'<div class="podpis'.($bottom ? ' bottom' : '').'">'.
		'<h1>� ��������� ������� ��������(�).<span>������� ���������: ________________________________</span></h1>'.
		'<h2>������� ������: __________________________ ('._viewer(VIEWER_ID, 'name').')'.
			'<em>'.FullData(curTime()).'</em>'.
		'</h2>'.
	'</div>';
}//kvit_podpis()
function kvit_cut() {
	return '<div class="cut">����� ������</div>';
}//kvit_cut()

require_once '../config.php';
require_once(DOCUMENT_ROOT.'/view/ws.php');

if(!$id = _isnum($_GET['id']))
	die(win1251('�������� id ���������.'));

$sql = "SELECT * FROM `zayav_kvit` WHERE `ws_id`=".WS_ID." AND `id`=".$id;
if(!$k = query_assoc($sql))
	die(win1251('��������� �� ����������.'));

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
	'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
	'<head>'.
		'<meta http-equiv="content-type" content="text/html; charset=windows-1251" />'.
		'<title>��������� �� ������ �'.$k['nomer'].'</title>'.
		'<link href="'.SITE.'/css/kvit_html'.(DEBUG ? '' : '.min').'.css?'.VERSION.'" rel="stylesheet" type="text/css" />'.
	'</head>'.
	'<body>'.
		kvit_head().
		kvit_name($k['nomer']).
		kvit_content($k).
		kvit_conditions().
		kvit_podpis().
		kvit_cut().
		kvit_name($k['nomer']).
		kvit_content($k).
		kvit_podpis(1).
		'<a onclick="this.style.display=\'none\';window.print()">������</a>'.
	'</body>'.
	'</html>';


mysql_close();
exit;


