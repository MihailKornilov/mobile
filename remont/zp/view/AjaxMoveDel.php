<?php
require_once('../../../include/AjaxHeader.php');

$move = $VK->QueryObjectOne("select * from zp_move where id=".(preg_match("|^[\d]+$|",$_GET['id'])?$_GET['id']:0));
if ($move->id) {
  $VK->Query("delete from zp_move where id=".$move->id);
}

$avai = $VK->QueryObjectOne("select * from zp_available where zp_catalog_id=".$move->zp_catalog_id." and ws_id=".$vku->ws_id." limit 1");
$send->count = $avai->count + $move->count * ($move->prihod ? -1 : 1);
if ($send->count > 0) {
  $VK->Query("update zp_available set count=".$send->count." where id=".$avai->id); 
} else {
  $VK->Query("delete from zp_available where id=".$avai->id); 
}

$send->time = getTime($T);

echo json_encode($send);
?>
