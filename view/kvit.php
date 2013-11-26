<?php
require_once('../config.php');
require_once(DOCUMENT_ROOT.'/include/clsMsDocGenerator.php');
require_once(DOCUMENT_ROOT.'/view/ws.php');

if(!preg_match(REGEXP_NUMERIC, $_GET['id'])) {
	echo 'Неверный id заявки';
   	t;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `id`=".$id." LIMIT 1";
if(!$zayav = mysql_fetch_assoc(query($sql))) {
    ec	Заявки не существует';
    exit;	$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav['client_id']." LIMIT 1";
$client = mysql_fetch_assoc(query($sql));

$kvit =
    '<table 	s="device-about">'.
        '<tr><t	ass	bel">Дата приёма:<td>'.FullData($zayav['dtime_add']).
        '<tr><td clas	abe	стройство:<td>'._deviceName($zayav['base_device_id'])._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).
        ($zayav['color_id']	<tr	 class="label">Цвет:<td>'._colorName($zayav['color_id']) : '').
        ($zayav['imei'] ? '<tr><t	ass	bel">IMEI:<td>'.$zayav['imei'] : '').
        ($zayav['serial'] ? '<tr><td cl	"la	>Serial:<td>'.$zayav['serial'] : '').
        ($zayav['equip'] ? '<tr><td class="la	>Ко	ктация:<td>'.zayavEquipSpisok($zayav['equip']) : '').
    '</table>'.

    '<div class="hr-small">&nbsp;<	>'.

    '<table	ss="client-about">'.
        '<tr><td clas	abel">Заказчик:<td>'.$client['fio']	   	$client['telefon'] ? '<tr><td class="label">Контактные те	ны:	'.$client['telefon'] : '').
    '</table>'.

    '<div class="hr-small">&nbsp;</div>'.

    '<div cl	"label" id="brak	еисправность со слов Заказчика:</div>'.
  	div class="brake-about">Не включается</div>';

$send =
    '<div class="org	e">Мастерская «<b>Ремонт мобильных телефонов в Няндоме</b>»<	>'.
    '<div class="org-adres">Адрес: г.Няндома, ул.Североморская, рядом с магазином "Уют"	iv>'.
    '<div class="org-telefon">Телефон: 8 964 299 94 89. Время работы: пн-пт, 15:00-19:00.<	>'.

    '<div class="kvit-head">Квитанция №'.$zayav['nomer'].'</div>'.

    '<table class="device	"><tr><td>'.$kvit.'<td class="image">'._zayavImg($id, 'big', 200, 2	'</table>'.

    '<div class="label" id="conditions">Условия проведения ремонта:</div>'.
    '<ul class="conditi	about">'.
        '<li>Диагностика оборудования, принятого в ремонт, произв	ся бесплатно;'.
        '<li>Стороны	два	льно договариваются о стоимости ремонта в устной форме;'.
        '<li>Мастерска	тра	 только заявленные неисправности;'.
        '<li>Настоятельно рекомендуем Вам сохран	все	ные, хранящиеся в памяти изделия, на других носителях;'.
       	i>М	рская не несет ответственности за возможную потерю информации на устройствах хранения и записи данных;'.
      	li>	е окончания ремонта сотрудник Мастерской сообщает Заказчику о готовности;'.
        '<li>Аппараты, невостребованные в 	ние	есяцев после уведомления Заказчика о готовности или невозможности ремонта, '.
          	огу	ть реализованы в установленном законом порядке для погашения задолженности Заказчика перед Мастерской;'.
        '<li>Срок гар	и с	вля	0 дней с момента выдачи изделия Заказчику;'.
        '<li>На аппараты, подвергшиеся воздействию влаги, удару, гаранти	 об	ельства не распространяются;'.
    '</ul>'.
    '<div class="vk-app">'.
       	 мо	 самостоятельно отслеживать через интернет процесс ремонта своих устройств, сданных в нашу Мастерскую. '.
   	 'Для этого 	ходимо пройти по адресу <u>	om/	031819</u> и ввести этот код: <b>0438675482</b>. После данного действия '.
        'Ваша учётная запись ВКонтакте будет	вяз	к данным, которые хранятся в Мастерской и Вы сможете получать '.
        'уведомления об окончании ремонта и другую информацию.'.
 	</d	.
    '<div class="sign-client">С условиями ремонта согласен(а). Подпись Заказчика: _______________________________	___	______</div>'.
    '<table class="sign-master">'.
        '<	td>Аппарат пр	: <span>________________________________</span> ('._viewer(VIEWER_ID, 'name').')'.
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
