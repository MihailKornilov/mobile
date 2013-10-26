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

$kvit =
    '<table class="device-about">'.
        '<tr><td class="label">Дата приёма:<td>'.FullData($zayav['dtime_add']).
        '<tr><td class="label">Устройство:<td>'._deviceName($zayav['base_device_id'])._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).
        '<tr><td class="label">Цвет:<td>'._colorName($zayav['color_id']).
        ($zayav['imei'] ? '<tr><td class="label">IMEI:<td>'.$zayav['imei'] : '').
        ($zayav['serial'] ? '<tr><td class="label">Serial:<td>'.$zayav['serial'] : '').
    '</table>'.

    '<div class="hr-small">&nbsp;</div>'.

    '<table class="client-about">'.
        '<tr><td class="label">Заказчик:<td>'.$client['fio'].
        ($client['telefon'] ? '<tr><td class="label">Контактные телефоны:<td>'.$client['telefon'] : '').
    '</table>'.

    '<div class="hr-small">&nbsp;</div>'.

    '<div class="label" id="brake">Неисправность со слов Заказчика:</div>'.
    '<div class="brake-about">Не включается</div>';

$send =
    '<div class="org-name">Мастерская «<b>Ремонт мобильных телефонов в Няндоме</b>»</div>'.
    '<div class="org-adres">Адрес: г.Няндома, ул.Североморская, рядом с магазином "Уют".</div>'.
    '<div class="org-telefon">Телефон: 8 964 299 94 89. Время работы: пн-пт, 15:00-19:00.</div>'.

    '<div class="kvit-head">Квитанция №'.$zayav['nomer'].'</div>'.

    '<table class="device-tab"><tr><td>'.$kvit.'<td class="image">'._zayavImg($id, 'big', 200, 220).'</table>'.

    '<div class="label" id="conditions">Условия проведения ремонта:</div>'.
    '<ul class="conditions-about">'.
        '<li>Диагностика оборудования, принятого в ремонт, производится бесплатно;'.
        '<li>Стороны предварительно договариваются о стоимости ремонта в устной форме;'.
        '<li>Мастерская устраняет только заявленные неисправности;'.
        '<li>Настоятельно рекомендуем Вам сохранять все данные, хранящиеся в памяти изделия, на других носителях;'.
        '<li>Мастерская не несет ответственности за возможную потерю информации на устройствах хранения и записи данных;'.
        '<li>После окончания ремонта сотрудник Мастерской сообщает Заказчику о готовности;'.
        '<li>Аппараты, невостребованные в течение 3 месяцев после уведомления Заказчика о готовности или невозможности ремонта, '.
            'могут быть реализованы в установленном законом порядке для погашения задолженности Заказчика перед Мастерской;'.
        '<li>Срок гарантии составляет 30 дней с момента выдачи изделия Заказчику;'.
        '<li>На аппараты, подвергшиеся воздействию влаги, удару, гарантийные обязательства не распространяются;'.
    '</ul>'.
    '<div class="vk-app">'.
        'Вы можете самостоятельно отслеживать через интернет процесс ремонта своих устройств, сданных в нашу Мастерскую. '.
        'Для этого необходимо пройти по адресу <u>vk.com/app2031819</u> и ввести этот код: <b>0438675482</b>. После данного действия '.
        'Ваша учётная запись ВКонтакте будет привязана к данным, которые хранятся в Мастерской и Вы сможете получать '.
        'уведомления об окончании ремонта и другую информацию.'.
    '</div>'.
    '<div class="sign-client">С условиями ремонта согласен(а). Подпись Заказчика: ________________________________________________</div>'.
    '<table class="sign-master">'.
        '<tr><td>Аппарат принял: <span>________________________________</span> ('._viewerName().')'.
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
