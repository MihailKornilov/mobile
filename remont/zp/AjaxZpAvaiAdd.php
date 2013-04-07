<?php
/*
 * Внесение наличия запчасти.
 * Если запчасть совместимая, то наличие прибавляется к главной
*/
require_once('../../include/AjaxHeader.php');

$zp = $VK->QueryObjectOne("select id,compat_id from zp_catalog where id=".$_POST['zp_id']);
$zp_id = $zp->compat_id ? $zp->compat_id : $zp->id;

if(!$_POST['cena']) { $_POST['cena'] = 0; }

$send->summa = round($_POST['kolvo'] * $_POST['cena'],2);

$send->id = $VK->Query("insert into zp_move (
ws_id,
zp_catalog_id,
count,
cena,
summa,
viewer_id_add
) values (
".$vku->ws_id.",
".$zp_id.",
".$_POST['kolvo'].",
'".$_POST['cena']."',
'".$send->summa."',
".$vku->viewer_id.")");


$VK->Query("insert into history (
ws_id,
type,
zp_id,
value,
viewer_id_add
) values (
".$vku->ws_id.",
18,
".$zp->id.",
".$_POST['kolvo'].",
".$_GET['viewer_id']."
)");



$prihod = $VK->QRow("select sum(count) from zp_move where ws_id=".$vku->ws_id." and zp_catalog_id=".$zp_id." and prihod=1");
$rashod = $VK->QRow("select sum(count) from zp_move where ws_id=".$vku->ws_id." and zp_catalog_id=".$zp_id." and prihod=0");
$count = $prihod - $rashod;

$VK->Query("delete from zp_available where ws_id=".$vku->ws_id." and zp_catalog_id=".$zp_id);
$VK->Query("insert zp_available (ws_id,zp_catalog_id,count) values (".$vku->ws_id.",".$zp_id.",".$count.")");

$send->count = $count;
$send->kolvo = $_POST['kolvo'];
$send->dtime = utf8(FullDataTime(strftime("%Y-%m-%d %H:%M:%S",time())));

echo json_encode($send);
?>
