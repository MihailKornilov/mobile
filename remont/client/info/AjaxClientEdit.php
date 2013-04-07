<?php
require_once('../../../include/AjaxHeader.php');

$VK->Query("update client set fio='".win1251($_POST['fio'])."',telefon='".win1251($_POST['telefon'])."' where id=".$_POST['id']);

if ($_POST['client2'] > 0) {
  $VK->Query("update accrual set client_id=".$_POST['id']." where client_id=".$_POST['client2']);
  $VK->Query("update money set client_id=".$_POST['id']." where client_id=".$_POST['client2']);
  $VK->Query("update vk_comment set table_id=".$_POST['id']." where table_name='client' and table_id=".$_POST['client2']);
  $VK->Query("update zayavki set client_id=".$_POST['id']." where client_id=".$_POST['client2']);
  $VK->Query("update zp_move set client_id=".$_POST['id']." where client_id=".$_POST['client2']);
  $VK->Query("update zp_zakaz set client_id=".$_POST['id']." where client_id=".$_POST['client2']);
  $VK->Query("delete from client where id=".$_POST['client2']);
  $count = $VK->QRow("select count(id) from zayavki where client_id=".$_POST['id']);
  $VK->Query("update client set zayav_count='".$count."' where id=".$_POST['id']);
  setClientBalans($_POST['id']);
}

$VK->Query("insert into history (ws_id,type,client_id,viewer_id_add) values (".$vku->ws_id.",".($_POST['client2'] > 0 ? 11 : 10).",".$_POST['id'].",".$_GET['viewer_id'].")");

GclientsCreate();

$send->id = $_POST['id'];
echo json_encode($send);
?>
