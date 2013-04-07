<?php
require_once('../../../include/AjaxHeader.php');

$find="where zayav_status>0 and ws_id=".$vku->ws_id;

if(!isset($_GET['fast'])) {
  $_GET['fast'] = '';
} else {
  $find.=" and find like '%".$_GET['fast']."%'";
}
if (preg_match("|^[\d]+$|",$_GET['fast'])) { $find.=" or nomer=".$_GET['fast']." and zayav_status>0"; }

if ($_GET['status'] > 0) { $find.=" and zayav_status=".$_GET['status']; }

if ($_GET['device_id'] > 0) { $find.=" and base_device_id=".$_GET['device_id']; }
if ($_GET['vendor_id'] > 0) { $find.=" and base_vendor_id=".$_GET['vendor_id']; }
if ($_GET['model_id'] > 0) { $find.=" and base_model_id=".$_GET['model_id']; }

if ($_GET['device_place'] == -1) {
  $find.=" and device_place=0 and length(device_place_other)=0";
} else if (number($_GET['device_place'])) {
  if ($_GET['device_place'] > 0) { $find.=" and device_place=".$_GET['device_place']; }
} else {
  if ($_GET['device_place']) { $find.=" and device_place=0 and device_place_other='".win1251(urldecode($_GET['device_place']))."'"; }
}

if ($_GET['device_status'] > 0) { $find.=" and device_status=".$_GET['device_status']; }
if ($_GET['device_status'] < 0) { $find.=" and device_status=0"; }

$sort = $_GET['sort'] ==2 ? 'zayav_status_dtime ' : 'dtime_add ';

$send->all = $VK->QRow("select count(id) from zayavki ".$find);
$send->next = 0;
$send->spisok = array();

if (!isset($_GET['desc'])) { $_GET['desc'] = ''; }
$spisok = $VK->QueryObjectArray("select * from zayavki ".$find." order by ".$sort.($_GET['desc'] == 0 ? 'desc':'')." limit ".$_GET['start'].",".$_GET['limit']);
if(count($spisok) > 0) {
  // составление массива клиентов, названий и фото моделей
  $client = array();
  $images_zayav = array();
  $images_dev = array();
  foreach ($spisok as $sp) {
    array_push($client, $sp->client_id);
    array_push($images_zayav, "'zayav".$sp->id."'");
    array_push($images_dev, "'dev".$sp->base_model_id."'");
  }

  $fio = $VK->QueryPtPArray("select id,fio from client where id in (".implode(',',$client).")");
  $images_zayav = $VK->QueryPtPArray("select owner,link from images where status=1 and sort=0 and owner in (".implode(',',$images_zayav).")");
  $images_dev = $VK->QueryPtPArray("select owner,link from images where status=1 and sort=0 and owner in (".implode(',',array_unique($images_dev)).")");

  foreach ($spisok as $sp) {
    $img = '';
    if (isset($images_zayav["zayav".$sp->id])) {
      $img = $images_zayav["zayav".$sp->id];
    } else if (isset($images_dev["dev".$sp->base_model_id])) {
      $img = $images_dev["dev".$sp->base_model_id];
    }
    array_push($send->spisok, array(
      'id' => $sp->id,
      'status' => $sp->zayav_status,
      'nomer' => $sp->nomer,
      'category' => $sp->category,
      'device_id' => $sp->base_device_id,
      'vendor_id' => $sp->base_vendor_id,
      'model_id' => $sp->base_model_id,
      'client_id' => $sp->client_id,
      'fio' => utf8($fio[$sp->client_id]),
      'dtime' => utf8(FullData($sp->dtime_add)),
      'img' => $img,
      'article' => utf8($VK->QRow("select txt from vk_comment where table_name='zayav' and table_id=".$sp->id." and status=1 order by id desc limit 1"))
    ));
  }
  if (count($spisok) == $_GET['limit']) {
    if ($VK->QNumRows("select id from zayavki ".$find." limit ".($_GET['start'] + $_GET['limit']).",".$_GET['limit']) > 0) {
      $send->next = 1;
    }
  }
}

$send->time = getTime($T);

echo json_encode($send);;
?>



