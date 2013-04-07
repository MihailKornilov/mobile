<?php
require_once('../../../include/AjaxHeader.php');

$find = "where ws_id=".$vku->ws_id." and status=1 and summa>0";
$find .= " and dtime_add>='".$_GET['day_begin']." 00:00:00'";
$find .= " and dtime_add<='".$_GET['day_end']." 23:59:59'";

$send->all = $VK->QRow("select count(id) from money ".$find);
$send->next = 0;
$send->spisok = array();
$send->sum = 0;

$spisok = $VK->QueryObjectArray("select * from money ".$find." order by id limit ".$_GET['start'].",".$_GET['limit']);
if (count($spisok) > 0) {
  $send->sum = $VK->QRow("select ifnull(sum(summa),0) from money ".$find);

  $zayav_nomer = array();
  $zp_ids = array();
  foreach ($spisok as $sp) {
    array_push($zayav_nomer, $sp->zayav_id);
    array_push($zp_ids, $sp->zp_id);
  }

  $zayav_nomer = $VK->QueryPtPArray("select id,nomer from zayavki where id in (".implode(',', array_unique($zayav_nomer)).")");

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

  foreach($spisok as $sp) {
    array_push($send->spisok, array( 
      'zayav_id' => $sp->zayav_id,
      'zayav_nomer' => $sp->zayav_id > 0 ? $zayav_nomer[$sp->zayav_id] : '',
      'zp_id' => $sp->zp_id,
      'zp_name' => $sp->zp_id > 0 ? $zp_name[$sp->zp_id] : '',
      'zp_device' => $sp->zp_id > 0 ? $zp_device[$sp->zp_id] : '',
      'zp_vendor' => $sp->zp_id > 0 ? $zp_vendor[$sp->zp_id] : '',
      'zp_model' => $sp->zp_id > 0 ? $zp_model[$sp->zp_id] : '',
      'sum' => $sp->summa,
      'txt' => utf8($sp->prim),
      'dtime_add' => utf8(FullDataTime($sp->dtime_add)),
      'viewer_id' => $sp->viewer_id_add
    ));
  }
  if (count($spisok) == $_GET['limit']) {
    if ($VK->QNumRows("select id from money ".$find." limit ".($_GET['start'] + $_GET['limit']).",".$_GET['limit']) > 0) {
      $send->next = 1;
    }
  }
}

$send->time = getTime($T);

echo json_encode($send);;
?>
