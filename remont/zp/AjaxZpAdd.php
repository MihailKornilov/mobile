<?php
require_once('../../include/AjaxHeader.php');

$modelName = $VK->QRow("select name from base_model where id=".$_POST['model_id']);

if (!isset($_POST['id'])) { $_POST['id'] = 0; }
if ($_POST['id'] == 0) {
  $send->id = $VK->Query("insert into zp_catalog (
name_id,
name_dop,
color_id,
base_device_id,
base_vendor_id,
base_model_id,
viewer_id_add,
find
) values (
".$_POST['name_id'].",
'".win1251(textFormat($_POST['name_dop']))."',
".$_POST['color_id'].",
".$_POST['device_id'].",
".$_POST['vendor_id'].",
".$_POST['model_id'].",
".$_GET['viewer_id'].",
'".win1251($modelName." ".textFormat($_POST['name_dop']))."'
  )");
} else {
  $send->id = $_POST['id'];
  $VK->Query("update zp_catalog set
name_id=".$_POST['name_id'].",
name_dop='".win1251(textFormat($_POST['name_dop']))."',
color_id=".$_POST['color_id'].",
base_device_id=".$_POST['device_id'].",
base_vendor_id=".$_POST['vendor_id'].",
base_model_id=".$_POST['model_id'].",
viewer_id_add=".$_GET['viewer_id'].",
find='".win1251($modelName." ".textFormat($_POST['name_dop']))."'
where id=".$send->id);
}

echo json_encode($send);
?>
