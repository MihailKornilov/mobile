<?php
require_once('../../../include/AjaxHeader.php');

$send->spisok = array();
 
$zp = $VK->QueryObjectOne("select id,compat_id from zp_catalog where id=".$_GET['zid']);

// получение движения запчасти
$spisok = $VK->QueryObjectArray("select * from zp_move where ws_id=".$vku->ws_id." and zp_catalog_id=".($zp->compat_id ? $zp->compat_id : $zp->id)." order by id desc");
if(count($spisok)) {
  $nomer = array();
  $fio = array();
  foreach($spisok as $n => $sp) {
    if ($sp->zayav_id) { $nomer[$n] = $sp->zayav_id; }
    if ($sp->client_id) { $fio[$n] = $sp->client_id; }
    $workerArr[$sp->viewer_id_add] = 1;
  }
  if (count($nomer) > 0) { $nomer = $VK->QueryPtPArray("select id,nomer from zayavki where id in (".implode(',',$nomer).")"); }
  if (count($fio) > 0) { $fio = $VK->QueryPtPArray("select id,fio from client where id in (".implode(',',$fio).")"); }

  // составление массива пользователей из VK
  $wid = "0";
  $send->w_name = array();
  $send->w_photo = array();
  foreach($workerArr as $n => $w) { $wid .= ','.$n; }
  $worker = $VK->QueryObjectArray("select * from vk_user where viewer_id in (".$wid.")");
  foreach($worker as $sp) {
    $send->w_name[$sp->viewer_id] = utf8($sp->last_name." ".$sp->first_name);
    $send->w_photo[$sp->viewer_id] = $sp->photo;
  }

  foreach($spisok as $sp) {
    array_push($send->spisok, array(
      'id' => $sp->id,
      'w_id' => $sp->viewer_id_add,
      'action' => $sp->type,
      'count' => $sp->count,
      'summa' => round($sp->summa,2),

      'zayav_id' => $sp->zayav_id,
      'nomer' => isset($nomer[$sp->zayav_id]) ? $nomer[$sp->zayav_id] : 0,

      'client_id' => $sp->client_id,
      'fio' => $sp->client_id > 0 ? utf8($fio[$sp->client_id]) : '',

      'prim' => utf8($sp->prim),
      'dtime' => utf8(FullDataTime($sp->dtime_add))
    ));
  }
}


// получение совместимости запчасти
$send->compatSpisok = array();
if ($zp->compat_id) {
  $spisok = $VK->QueryObjectArray("select * from zp_catalog where compat_id=".$zp->compat_id." and id!=".$zp->id);
  foreach($spisok as $n => $sp) {
    array_push($send->compatSpisok, array(
      'num' => $n,
      'id' => $sp->id,
      'color_id' => $sp->color_id,
      'name_id' => $sp->name_id,
      'name_dop' => utf8($sp->name_dop),
      'device_id' => $sp->base_device_id,
      'vendor_id' => $sp->base_vendor_id,
      'model_id' => $sp->base_model_id,
      'dtime' => utf8(FullData($sp->dtime_add)),
      'compat_id' => $sp->compat_id
    ));
  }
}



// получение изображений запчасти
$send->foto = array();
$spisok = $VK->QueryObjectArray("select * from images where status=1 and owner='zp".($zp->compat_id ? $zp->compat_id : $zp->id)."' order by sort");
if (count($spisok) > 0) {
  foreach($spisok as $sp) {
    array_push($send->foto, array(
      'id' => $sp->id,
      'link' => $sp->link,
      'x' => $sp->big_x,
      'y' => $sp->big_y,
      'dtime' => utf8(FullData($sp->dtime_add))
    ));
  }
}


$send->time = getTime($T);

echo json_encode($send);
?>
