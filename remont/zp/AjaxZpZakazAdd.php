<?php
require_once('../../include/AjaxHeader.php');

$zp = $VK->QueryObjectOne("select id,compat_id from zp_catalog where id=".$_POST['zid']);
$zid = $zp->compat_id ? $zp->compat_id : $zp->id;


if($_POST['count'] > 0) {
  $zakaz_id = $VK->QRow("select id from zp_zakaz where ws_id=".$vku->ws_id." and zp_catalog_id=".$zid." and zayav_id=0 and client_id=0 limit 1");
  $zakaz_zayav = $VK->QRow("select count from zp_zakaz where ws_id=".$vku->ws_id." and zp_catalog_id=".$zid." and zayav_id>0 and client_id=0 limit 1");
  if (isset($zakaz_zayav)) { $_POST['count'] -= $zakaz_zayav; }
  if (isset($zakaz_id)) {
    $VK->Query("update zp_zakaz set count=".$_POST['count']." where id=".$zakaz_id);
  } else {
    $VK->Query("insert into zp_zakaz (
ws_id,
zp_catalog_id,
count,
viewer_id_add
) values (
".$vku->ws_id.",
".$zid.",
".$_POST['count'].",
".$vku->viewer_id.")");
  }
} else {
  $VK->Query("delete from zp_zakaz where ws_id=".$vku->ws_id." and zp_catalog_id=".$zid);
}

$send->count = $_POST['count'];

echo json_encode($send);
?>
