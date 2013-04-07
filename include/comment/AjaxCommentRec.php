<?php
require_once('../AjaxHeader.php');

$child = $VK->QRow("select child_del from vk_comment where id=".$_POST['rec']);

if ($child != null) {
  if (preg_match("/,/",$child) > 0) {
    $childId = explode(',',$child);
  } else {
    $childId[0] = $child;  
  }
}

if (count($childId) > 0) {
  foreach($childId as $c) {
    $VK->Query("update vk_comment set status=1,viewer_id_del=0,dtime_del='0000-00-00 00:00:00' where id=".$c);
  }
}


$VK->Query("update vk_comment set status=1,viewer_id_del=0,dtime_del='0000-00-00 00:00:00',child_del=NULL where id=".$_POST['rec']);
$comm=$VK->QueryObjectOne("select table_name,table_id from vk_comment where id=".$_POST['rec']);
$send->count=$VK->QRow("select count(id) from vk_comment where parent_id=0 and status=1 and table_name='".$comm->table_name."' and table_id=".$comm->table_id);

echo json_encode($send);
?>
