<?php
require_once('../AjaxHeader.php');

$child = $VK->QueryRowArray("select id from vk_comment where parent_id=".$_POST['del']);
if (count($child) > 0) {
  foreach($child as $c) {
    $childId .= $c[0] . ',';
  }
}

$VK->Query("update vk_comment set status=0,viewer_id_del=".$_GET['viewer_id'].",dtime_del=current_timestamp where parent_id=".$_POST['del']);

$cLen = strlen($childId);
$VK->Query("update vk_comment set
status=0,
viewer_id_del=".$_GET['viewer_id'].",
".($cLen > 0 ? "child_del='".substr($childId,0,$cLen - 1)."'," : '')."
dtime_del=current_timestamp
where id=".$_POST['del']);

$VK->Query("update vk_comment set status=0,viewer_id_del=".$_GET['viewer_id'].",dtime_del=current_timestamp where id=".$_POST['del']);
$comm=$VK->QueryObjectOne("select table_name,table_id from vk_comment where id=".$_POST['del']);
$send->count=$VK->QRow("select count(id) from vk_comment where parent_id=0 and status=1 and table_name='".$comm->table_name."' and table_id=".$comm->table_id);

echo json_encode($send);
?>
