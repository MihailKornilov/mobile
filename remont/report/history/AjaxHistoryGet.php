<?php
require_once('../../../include/AjaxHeader.php');

$find="where ws_id=".$vku->ws_id;

$send->all = $VK->QRow("select count(id) from history ".$find);
$send->next = 0;
$send->spisok = array();

$spisok = $VK->QueryObjectArray("select * from history ".$find." order by id desc limit ".$_GET['start'].",".$_GET['limit']);
if(count($spisok) > 0) {
  $fio = array();
  $zayav_nomer = array();
  $zp_ids = array();
  foreach ($spisok as $sp) {
    if ($sp->client_id > 0) { array_push($fio, $sp->client_id); }
    if ($sp->zayav_id > 0) { array_push($zayav_nomer, $sp->zayav_id); }
    if ($sp->zp_id > 0) { array_push($zp_ids, $sp->zp_id); }
 }

  if (count($fio) > 0) { $fio = $VK->QueryPtPArray("select id,fio from client where id in (".implode(',', array_unique($fio)).")"); }
  if (count($zayav_nomer) > 0) { $zayav_nomer = $VK->QueryPtPArray("select id,nomer from zayavki where id in (".implode(',', array_unique($zayav_nomer)).")"); }

  $zp_name = '';
  $zp_device = '';
  $zp_vendor = '';
  $zp_model = '';
  if (count($zp_ids) > 0) {
    $zp_ids = implode(',', array_unique($zp_ids));
    $zp_name = $VK->QueryPtPArray("select id,name_id from zp_catalog where id in (".$zp_ids.")");
    $zp_device = $VK->QueryPtPArray("select id,base_device_id from zp_catalog where id in (".$zp_ids.")");
    $zp_vendor = $VK->QueryPtPArray("select id,base_vendor_id from zp_catalog where id in (".$zp_ids.")");
    $zp_model = $VK->QueryPtPArray("select id,base_model_id from zp_catalog where id in (".$zp_ids.")");
  }

  foreach ($spisok as $sp) {
    $unit = array(
      'id' => $sp->id,
      'type' => $sp->type,
      'dtime' => utf8(FullDataTime($sp->dtime_add, 1)),
      'viewer_id' => $sp->viewer_id_add
    );

    if ($sp->client_id > 0) { $unit['client_id'] = $sp->client_id; }
    if(isset($fio[$sp->client_id])) { $unit['client_fio'] =  utf8($fio[$sp->client_id]); }

    if ($sp->zayav_id > 0) { $unit['zayav_id'] = $sp->zayav_id; }
    if(isset($zayav_nomer[$sp->zayav_id])) { $unit['zayav_nomer'] =  utf8($zayav_nomer[$sp->zayav_id]); }

    if ($sp->value) { $unit['value'] = utf8($sp->value); }

    if ($sp->zp_id > 0) {
      $unit['zp_id'] = $sp->zp_id;
      $unit['zp_name'] =  $zp_name[$sp->zp_id];
      $unit['zp_device'] = $zp_device[$sp->zp_id];
      $unit['zp_vendor'] = $zp_vendor[$sp->zp_id];
      $unit['zp_model'] = $zp_model[$sp->zp_id];
    }

    array_push($send->spisok, $unit);
  }
  if (count($spisok) == $_GET['limit']) {
    if ($VK->QNumRows("select id from history ".$find." limit ".($_GET['start'] + $_GET['limit']).",".$_GET['limit']) > 0) {
      $send->next = 1;
    }
  }
}

$send->time = getTime($T);

echo json_encode($send);;
?>



