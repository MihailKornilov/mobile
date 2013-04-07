<?php
require_once('../../include/AjaxHeader.php');

// составление списка id для наличия и заказов
function findType($arr) {
  $ids = 0;
  if(count($arr) > 0) {
    foreach($arr as $sp) {
      $ids .= ",".$sp[0];
    }
  }
  return $ids;
}

$find = "where id";
if ($_GET['id'] > 0) {
  $find .= " and id=".$_GET['id'];
} else {
  if ($_GET['name_id'] > 0) $find .= " and name_id=".$_GET['name_id'];
  if ($_GET['device_id'] > 0) $find .= " and base_device_id=".$_GET['device_id'];
  if ($_GET['vendor_id'] > 0) $find .= " and base_vendor_id=".$_GET['vendor_id'];
  if ($_GET['model_id'] > 0) $find .= " and base_model_id=".$_GET['model_id'];
  if ($_GET['fast']) $find .= " and find like '%".$_GET['fast']."%'";
  if ($_GET['type'] == 2) { $find .= " and id in (".findType($VK->QueryRowArray("select zp_catalog_id from zp_available where ws_id=".$vku->ws_id)).")"; }
  if ($_GET['type'] == 3) { $find .= " and id not in (".findType($VK->QueryRowArray("select zp_catalog_id from zp_available where ws_id=".$vku->ws_id)).")"; }
  if ($_GET['type'] == 4) { $find .= " and id in (".findType($VK->QueryRowArray("select zp_catalog_id from zp_zakaz where ws_id=".$vku->ws_id)).")"; }
}

$send->all = $VK->QRow("select count(id) from zp_catalog ".$find);
$send->next = 0;
$send->spisok = array();

$spisok = $VK->QueryObjectArray("select * from zp_catalog ".$find." order by id desc limit ".$_GET['start'].",".$_GET['limit']);
if(count($spisok) > 0) {
  $ids = array();
  $ids_images = array();
  foreach($spisok as $n => $sp) {
    array_push($ids, $sp->id);
    if ($sp->compat_id > 0) { array_push($ids, $sp->compat_id); }
    array_push($ids_images, "'zp".$sp->id."'");
    if ($sp->compat_id) { array_push($ids_images, "'zp".$sp->compat_id."'"); }
  }
  array_push($ids, 0);
  $ids = implode(',', array_unique($ids));

  $avai = $VK->QueryPtPArray("select zp_catalog_id,count from zp_available where ws_id=".$vku->ws_id." and zp_catalog_id in (".$ids.")");
  $zakaz = $VK->QueryPtPArray("select zp_catalog_id,count from zp_zakaz where ws_id=".$vku->ws_id." and zayav_id=0 and zp_catalog_id in (".$ids.")"); // заказы без заявок и клиентов
  $zakaz_zayav = $VK->QueryPtPArray("select zp_catalog_id,count from zp_zakaz where ws_id=".$vku->ws_id." and zayav_id>0 and zp_catalog_id in (".$ids.")"); // заказы по заявкам
  $images = $VK->QueryPtPArray("select owner,link from images where status=1 and sort=0 and owner in (".implode(',', array_unique($ids_images)).")");

  foreach($spisok as $sp) {
    $id = $sp->compat_id > 0 ? $sp->compat_id : $sp->id;
    $zp_zakaz = 0;
    if (isset($zakaz[$id])) { $zp_zakaz += $zakaz[$id]; }
    if (isset($zakaz_zayav[$id])) { $zp_zakaz += $zakaz_zayav[$id]; }
    array_push($send->spisok, array(
      'id' => $sp->id,
      'img' => isset($images["zp".$id]) ? $images["zp".$id] : '',
      'name_id' => $sp->name_id,
      'name_dop' => utf8($sp->name_dop),
      'color_id' => $sp->color_id,
      'device_id' => $sp->base_device_id,
      'vendor_id' => $sp->base_vendor_id,
      'model_id' => $sp->base_model_id,
      'avai' => isset($avai[$id]) ? $avai[$id] : 0,
      'zakaz' => $zp_zakaz,
      'dtime' => utf8(FullData($sp->dtime_add)),
      'compat_id' => $sp->compat_id
    ));
  }
  if (count($spisok) == $_GET['limit']) {
    if ($VK->QNumRows("select id from zp_catalog ".$find." limit ".($_GET['start'] + $_GET['limit']).",".$_GET['limit']) > 0) {
      $send->next = 1;
    }
  }
}


$send->time = getTime($T);

echo json_encode($send);
?>
