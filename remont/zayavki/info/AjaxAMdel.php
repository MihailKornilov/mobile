<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update ".$_POST['table']." set status=0,viewer_id_del=".$vku->viewer_id.",dtime_del=current_timestamp where id=".$_POST['id']);

// удаление записи из кассы
if ($_POST['table'] == 'money') {
  $VK->Query("delete from kassa where money_id=".$_POST['id']);
}

$send->count = $VK->QRow("select count(id) from accrual where ws_id=".$vku->ws_id." and zayavki_id=".$_POST['zid']." and status=1");
$send->count += $VK->QRow("select count(id) from money where ws_id=".$vku->ws_id." and zayav_id=".$_POST['zid']." and status=1");

$summa = $VK->QRow("select summa from ".$_POST['table']." where id=".$_POST['id']);
$VK->Query("insert into history (ws_id,type,zayav_id,value,viewer_id_add) values (".$vku->ws_id.",".($_POST['table'] == 'accrual' ? 8 : 9).",".$_POST['zid'].",".$summa.",".$_GET['viewer_id'].")");

setClientBalans($_POST['client_id']);
echo json_encode($send);
?>
