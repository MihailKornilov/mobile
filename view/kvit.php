<?php
require_once('../config.php');
require_once(DOCUMENT_ROOT.'/include/clsMsDocGenerator.php');

if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
    echo 'Неверный id заявки';
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `id`=".$id." LIMIT 1";
if(!$zayav = mysql_fetch_assoc(query($sql))) {
    echo 'Заявки не существует';
    exit;
}

$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav['client_id']." LIMIT 1";
$client = mysql_fetch_assoc(query($sql));

$send =
    '<div class="org-name">Мастерская «<b>Ремонт мобильных телефонов в Няндоме</b>»</div>'.
    '<div class="org-adres">Адрес: г.Няндома, ул.Североморская, рядом с магазином "Уют".</div>'.
    '<div class="org-telefon">Телефон: 8 964 299 94 89. Время работы: пн-пт, 15:00-19:00.</div>'.

    '<div class="kvit-head">Квитанция №'.$zayav['nomer'].'</div>'.

    '<table class="device-about">'.
        '<tr><td class="label">Дата приёма:<td>'.FullData($zayav['dtime_add']).
        '<tr><td class="label">Устройство:<td>'._deviceName($zayav['base_device_id'])._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).
        '<tr><td class="label">Цвет:<td>'._colorName($zayav['color_id']).
        ($zayav['imei'] ? '<tr><td class="label">IMEI:<td>'.$zayav['imei'] : '').
        ($zayav['serial'] ? '<tr><td class="label">Serial:<td>'.$zayav['serial'] : '').
    '</table>'.

    '<div class="hr-small">&nbsp;</div>'.

    '<table class="device-about">'.
        '<tr><td class="label">Владелец:<td>'.$client['fio'].
        ($client['telefon'] ? '<tr><td class="label">Контактные телефоны:<td>'.$client['telefon'] : '').
    '</table>'.

    '<p>Неисправность со слов владельца:<br /></p>';

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
