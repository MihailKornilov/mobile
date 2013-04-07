<?php
/*
 * поиск запчасти в каталоге для совместимости
*/
require_once('../../../include/AjaxHeader.php');

$spisok = $VK->QueryObjectArray("select * from zp_catalog where
name_id=".$_POST['name_id']." and
base_device_id=".$_POST['device_id']." and
base_vendor_id=".$_POST['vendor_id']." and
base_model_id=".$_POST['model_id']);

$send->spisok = array();
if(count($spisok) > 0) {
  foreach ($spisok as $sp) {
    array_push($send->spisok,array(
      'id' => $sp->id,
      'color_id' => $sp->color_id,
      'compat_id' => $sp->compat_id
    ));
  }
}

$send->time = getTime($T);

echo json_encode($send);
?>
