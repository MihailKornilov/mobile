<?php
require_once('../config.php');
require_once(DOCUMENT_ROOT.'/include/clsMsDocGenerator.php');
require_once(DOCUMENT_ROOT.'/view/ws.php');

if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
	echo '�������� id ������';
   	t;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `id`=".$id." LIMIT 1";
if(!$zayav = mysql_fetch_assoc(query($sql))) {
    ec	������ �� ����������';
    exit;	$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav['client_id']." LIMIT 1";
$client = mysql_fetch_assoc(query($sql));

$kvit =
    '<table 	s="device-about">'.
        '<tr><t	ass	bel">���� �����:<td>'.FullData($zayav['dtime_add']).
        '<tr><td clas	abe	���������:<td>'._deviceName($zayav['base_device_id'])._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).
        ($zayav['color_id']	<tr	 class="label">����:<td>'._colorName($zayav['color_id']) : '').
        ($zayav['imei'] ? '<tr><t	ass	bel">IMEI:<td>'.$zayav['imei'] : '').
        ($zayav['serial'] ? '<tr><td cl	"la	>Serial:<td>'.$zayav['serial'] : '').
        ($zayav['equip'] ? '<tr><td class="la	>��	������:<td>'.zayavEquipSpisok($zayav['equip']) : '').
    '</table>'.

    '<div class="hr-small">&nbsp;<	>'.

    '<table	ss="client-about">'.
        '<tr><td clas	abel">��������:<td>'.$client['fio']	   	$client['telefon'] ? '<tr><td class="label">���������� ��	��:	'.$client['telefon'] : '').
    '</table>'.

    '<div class="hr-small">&nbsp;</div>'.

    '<div cl	"label" id="brak	������������ �� ���� ���������:</div>'.
  	div class="brake-about">�� ����������</div>';

$send =
    '<div class="org	e">���������� �<b>������ ��������� ��������� � �������</b>�<	>'.
    '<div class="org-adres">�����: �.�������, ��.�������������, ����� � ��������� "���"	iv>'.
    '<div class="org-telefon">�������: 8 964 299 94 89. ����� ������: ��-��, 15:00-19:00.<	>'.

    '<div class="kvit-head">��������� �'.$zayav['nomer'].'</div>'.

    '<table class="device	"><tr><td>'.$kvit.'<td class="image">'._zayavImg($id, 'big', 200, 2	'</table>'.

    '<div class="label" id="conditions">������� ���������� �������:</div>'.
    '<ul class="conditi	about">'.
        '<li>����������� ������������, ��������� � ������, ������	�� ���������;'.
        '<li>�������	���	���� �������������� � ��������� ������� � ������ �����;'.
        '<li>���������	���	 ������ ���������� �������������;'.
        '<li>������������ ����������� ��� ������	���	���, ���������� � ������ �������, �� ������ ���������;'.
       	i>�	����� �� ����� ��������������� �� ��������� ������ ���������� �� ����������� �������� � ������ ������;'.
      	li>	� ��������� ������� ��������� ���������� �������� ��������� � ����������;'.
        '<li>��������, ���������������� � 	���	������ ����� ����������� ��������� � ���������� ��� ������������� �������, '.
          	���	�� ����������� � ������������� ������� ������� ��� ��������� ������������� ��������� ����� ����������;'.
        '<li>���� ���	� �	���	0 ���� � ������� ������ ������� ���������;'.
        '<li>�� ��������, ������������ ����������� �����, �����, �������	 ��	������� �� ����������������;'.
    '</ul>'.
    '<div class="vk-app">'.
       	 ��	 �������������� ����������� ����� �������� ������� ������� ����� ���������, ������� � ���� ����������. '.
   	 '��� ����� 	������ ������ �� ������ <u>	om/	031819</u> � ������ ���� ���: <b>0438675482</b>. ����� ������� �������� '.
        '���� ������� ������ ��������� �����	���	� ������, ������� �������� � ���������� � �� ������� �������� '.
        '����������� �� ��������� ������� � ������ ����������.'.
 	</d	.
    '<div class="sign-client">� ��������� ������� ��������(�). ������� ���������: _______________________________	___	______</div>'.
    '<table class="sign-master">'.
        '<	td>������� ��	: <span>________________________________</span> ('._viewer(VIEWER_ID, 'name').')'.
            '<td class="cur-data">'.FullData(curTime())	/table>';

$doc = new clsMsDocGene	r(
	$pageOrientation = 'PORTRAIT',
    $pageType = 'A4',
    $cssFile = DOCUMENT_ROOT.'/css/kvit.css',
    $topMa	 =
  	ightMargin = 0.5,
    $bottomMargin = 0.5,
    $leftMargin = 0.5);
$doc->addParagraph($send);
$doc->output('kvit_'.$zayav['nomer']);
