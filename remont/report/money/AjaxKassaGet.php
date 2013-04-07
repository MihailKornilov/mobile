<?php
require_once('../../../include/AjaxHeader.php');

$send->spisok = array();
$spisok = $VK->QueryObjectArray("select * from kassa where ws_id=".$vku->ws_id." and dtime_add>='".strftime("%Y-%m", time())."-01 00:00:00'");
if (count($spisok) > 0) {
  $zayav_nomer = array();
  $zp_ids = array();
  foreach ($spisok as $sp) {
    array_push($zayav_nomer, $sp->zayav_id);
    array_push($zp_ids, $sp->zp_id);
  }

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
    array_push($send->spisok, array(
      'id' => $sp->id,
      'sum' => $sp->sum,
      'type' => $sp->type,
      'txt' => $sp->txt ? utf8($sp->txt) : '',
      'client_id' => $sp->client_id,

      'zayav_id' => $sp->zayav_id,
      'zayav_nomer' => $sp->zayav_id > 0 ? utf8($zayav_nomer[$sp->zayav_id]) : '',

      'zp_id' => $sp->zp_id,
      'zp_name' => $sp->zp_id > 0 ? $zp_name[$sp->zp_id] : '',
      'zp_device' => $sp->zp_id > 0 ? $zp_device[$sp->zp_id] : '',
      'zp_vendor' => $sp->zp_id > 0 ? $zp_vendor[$sp->zp_id] : '',
      'zp_model' => $sp->zp_id > 0 ? $zp_model[$sp->zp_id] : '',

      'dtime' => utf8(FullDataTime($sp->dtime_add)),
      'viewer_id_add' => $sp->viewer_id_add
    ));
  }
}

echo json_encode($send);;
?>



