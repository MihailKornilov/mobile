<?php
require_once('../../../include/AjaxHeader.php');

$zp = $VK->QueryObjectOne("select id,compat_id from zp_catalog where id=".$_POST['zp_id']);
$zid = $zp->compat_id ? $zp->compat_id : $zp->id;

$send->id = $VK->Query("insert into zp_move (
ws_id,
zp_catalog_id,
prihod,
count,
type,
zayav_id,
viewer_id_add
) values (
".$vku->ws_id.",
".$zid.",
0,
1,
'set',
".$_POST['zayav_id'].",
".$_GET['viewer_id']."
)");

$prihod = $VK->QRow("select sum(count) from zp_move where ws_id=".$vku->ws_id." and zp_catalog_id=".$zid." and prihod=1");
$rashod = $VK->QRow("select sum(count) from zp_move where ws_id=".$vku->ws_id." and zp_catalog_id=".$zid." and prihod=0");
$zp_count = $prihod - $rashod;
$VK->Query("delete from zp_available where ws_id=".$vku->ws_id." and zp_catalog_id=".$zid);
if($zp_count>0) $VK->Query("insert zp_available (ws_id,zp_catalog_id,count) values (".$vku->ws_id.",".$zid.",".$zp_count.")");

$VK->Query("delete from zp_zakaz where ws_id=".$vku->ws_id." and zayav_id=".$_POST['zayav_id']." and zp_catalog_id=".$_POST['zp_id']);
$art = $VK->QueryObjectOne("select id,parent_id from vk_comment where table_name='zayav' and table_id=".$_POST['zayav_id']." and status=1 order by id desc limit 1");
if($art->parent_id) $parent=$art->parent_id;
else
	if($art->id) $parent=$art->id;
	else
		$parent=0;
$zp = $VK->QueryObjectOne("select * from zp_catalog where id=".$_POST['zp_id']);
$name = $VK->QRow("select name from setup_zp_name where id=".$zp->name_id);
$vendor = $VK->QRow("select name from base_vendor where id=".$zp->base_vendor_id);
$model = $VK->QRow("select name from base_model where id=".$zp->base_model_id);
$txt = "��������� ��������: <A href='".$URL."&my_page=remZpView&id=".$_POST['zp_id']."'>".$name." ".$vendor." ".$model."</A>";
$VK->Query("insert into vk_comment (table_name,parent_id,txt,table_id,viewer_id_add) values ('zayav',".$parent.",\"".$txt."\",".$_POST['zayav_id'].",".$vku->viewer_id.")");
if ($parent > 0) {
	$count=$VK->QRow("select count(id) from vk_comment where status=1 and parent_id=".$parent);
	$VK->Query("update vk_comment set child_count=".$count." where id=".$parent);
}

echo json_encode($send);
?>
