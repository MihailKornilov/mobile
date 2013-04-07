<?php
require_once('../../../include/AjaxHeader.php');

$zp = $VK->QueryObjectOne("select id,compat_id from zp_catalog where id=".$_GET['zid']);
$zid = $zp->compat_id ? $zp->compat_id : $zp->id;

$send->id = $VK->Query("insert into zp_zakaz (
ws_id,
zp_catalog_id,
zayav_id,
viewer_id_add
) values (
".$vku->ws_id.",
".$zid.",
".$_GET['zayav_id'].",
".$vku->viewer_id.")");

$send->ok = 1;
echo json_encode($send);
?>
