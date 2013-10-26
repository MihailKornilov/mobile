<?php
require_once('../config.php');
require_once(DOCUMENT_ROOT.'/include/clsMsDocGenerator.php');

if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
    echo '�������� id ������';
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `id`=".$id." LIMIT 1";
if(!$zayav = mysql_fetch_assoc(query($sql))) {
    echo '������ �� ����������';
    exit;
}

$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav['client_id']." LIMIT 1";
$client = mysql_fetch_assoc(query($sql));

$kvit =
    '<table class="device-about">'.
        '<tr><td class="label">���� �����:<td>'.FullData($zayav['dtime_add']).
        '<tr><td class="label">����������:<td>'._deviceName($zayav['base_device_id'])._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).
        '<tr><td class="label">����:<td>'._colorName($zayav['color_id']).
        ($zayav['imei'] ? '<tr><td class="label">IMEI:<td>'.$zayav['imei'] : '').
        ($zayav['serial'] ? '<tr><td class="label">Serial:<td>'.$zayav['serial'] : '').
    '</table>'.

    '<div class="hr-small">&nbsp;</div>'.

    '<table class="client-about">'.
        '<tr><td class="label">��������:<td>'.$client['fio'].
        ($client['telefon'] ? '<tr><td class="label">���������� ��������:<td>'.$client['telefon'] : '').
    '</table>'.

    '<div class="hr-small">&nbsp;</div>'.

    '<div class="label" id="brake">������������� �� ���� ���������:</div>'.
    '<div class="brake-about">�� ����������</div>';

$send =
    '<div class="org-name">���������� �<b>������ ��������� ��������� � �������</b>�</div>'.
    '<div class="org-adres">�����: �.�������, ��.�������������, ����� � ��������� "���".</div>'.
    '<div class="org-telefon">�������: 8 964 299 94 89. ����� ������: ��-��, 15:00-19:00.</div>'.

    '<div class="kvit-head">��������� �'.$zayav['nomer'].'</div>'.

    '<table class="device-tab"><tr><td>'.$kvit.'<td class="image">'._zayavImg($id, 'big', 200, 220).'</table>'.

    '<div class="label" id="conditions">������� ���������� �������:</div>'.
    '<ul class="conditions-about">'.
        '<li>����������� ������������, ��������� � ������, ������������ ���������;'.
        '<li>������� �������������� �������������� � ��������� ������� � ������ �����;'.
        '<li>���������� ��������� ������ ���������� �������������;'.
        '<li>������������ ����������� ��� ��������� ��� ������, ���������� � ������ �������, �� ������ ���������;'.
        '<li>���������� �� ����� ��������������� �� ��������� ������ ���������� �� ����������� �������� � ������ ������;'.
        '<li>����� ��������� ������� ��������� ���������� �������� ��������� � ����������;'.
        '<li>��������, ���������������� � ������� 3 ������� ����� ����������� ��������� � ���������� ��� ������������� �������, '.
            '����� ���� ����������� � ������������� ������� ������� ��� ��������� ������������� ��������� ����� ����������;'.
        '<li>���� �������� ���������� 30 ���� � ������� ������ ������� ���������;'.
        '<li>�� ��������, ������������ ����������� �����, �����, ����������� ������������� �� ����������������;'.
    '</ul>'.
    '<div class="vk-app">'.
        '�� ������ �������������� ����������� ����� �������� ������� ������� ����� ���������, ������� � ���� ����������. '.
        '��� ����� ���������� ������ �� ������ <u>vk.com/app2031819</u> � ������ ���� ���: <b>0438675482</b>. ����� ������� �������� '.
        '���� ������� ������ ��������� ����� ��������� � ������, ������� �������� � ���������� � �� ������� �������� '.
        '����������� �� ��������� ������� � ������ ����������.'.
    '</div>'.
    '<div class="sign-client">� ��������� ������� ��������(�). ������� ���������: ________________________________________________</div>'.
    '<table class="sign-master">'.
        '<tr><td>������� ������: <span>________________________________</span> ('._viewerName().')'.
            '<td class="cur-data">'.FullData(curTime()).
'</table>';

$doc = new clsMsDocGenerator(
    $pageOrientation = 'PORTRAIT',
    $pageType = 'A4',
    $cssFile = DOCUMENT_ROOT.'/css/kvit.css',
    $topMargin = 0.5,
    $rightMargin = 0.5,
    $bottomMargin = 0.5,
    $leftMargin = 0.5);
$doc->addParagraph($send);
$doc->output('kvit_'.$zayav['nomer']);
