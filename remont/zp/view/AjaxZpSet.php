<?php
require_once('../../../include/AjaxHeader.php');

$zp = $VK->QueryObjectOne("select id,compat_id from zp_catalog where id=".$_POST['zp_id']);
$zp_id = $zp->compat_id ? $zp->compat_id : $zp->id;

if(!isset($_POST['zayav_id'])) $_POST['zayav_id'] = 0;
if(!isset($_POST['client_id'])) $_POST['client_id'] = 0;
if(!isset($_POST['cena'])) $_POST['cena'] = 0;

$send->summa = round($_POST['count'] * $_POST['cena'],2);

$send->id = $VK->Query("insert into zp_move (
ws_id,
zp_catalog_id,
prihod,
count,
type,
zayav_id,
client_id,
cena,
summa,
viewer_id_add,
prim
) values (
".$vku->ws_id.",
".$zp_id.",
0,
".$_POST['count'].",
'".$_POST['type']."',
".$_POST['zayav_id'].",
".$_POST['client_id'].",
'".$_POST['cena']."',
'".$send->summa."',
".$_GET['viewer_id'].",
'".win1251($_POST['prim'])."'
)");

// �������� �������
$type = 0;
$value = 0;
switch ($_POST['type']) {
  case 'set': $type = 13; break;
  case 'sale': $type = 14; $value = $send->summa; break;
  case 'write-off': $type = 15; break;
  case 'return': $type = 16; break;
  case 'defect': $type = 17; break;
}
if ($type > 0) {
  $VK->Query("insert into history (
ws_id,
type,
zp_id,
zayav_id,
value,
viewer_id_add
) values (
".$vku->ws_id.",
".$type.",
".$zp->id.",
".$_POST['zayav_id'].",
".$value.",
".$_GET['viewer_id']."
)");
}

// �������� ����������� �����
if ($_POST['type'] == 'sale') {
  $money_id = $VK->Query("insert into money (
ws_id,
zp_id,
summa,
kassa,
viewer_id_add
) values (
".$vku->ws_id.",
".$zp_id.",
".$_POST['cena'].",
".$_POST['kassa'].",
".$_GET['viewer_id']."
)");

  // �������� � �����
  if ($_POST['kassa'] == 1) {
    $VK->Query("insert into kassa (
type,
ws_id,
sum,
zp_id,
money_id,
viewer_id_add
) values (
2,
".$vku->ws_id.",
".$_POST['cena'].",
".$zp_id.",
".$money_id.",
".$_GET['viewer_id']."
)");
  }
}




$prihod = $VK->QRow("select sum(count) from zp_move where ws_id=".$vku->ws_id." and zp_catalog_id=".$zp_id." and prihod=1");
$rashod = $VK->QRow("select sum(count) from zp_move where ws_id=".$vku->ws_id." and zp_catalog_id=".$zp_id." and prihod=0");
$zp_count = $prihod-$rashod;
$VK->Query("delete from zp_avai where ws_id=".$vku->ws_id." and zp_catalog_id=".$zp_id);
if($zp_count>0) $VK->Query("insert zp_avai (ws_id,zp_catalog_id,count) values (".$vku->ws_id.",".$zp_id.",".$zp_count.")");

if($_POST['zayav_id']>0) {
  $VK->Query("delete from zp_zakaz where ws_id=".$vku->ws_id." and zayav_id=".$_POST['zayav_id']." and zp_catalog_id=".$_POST['zp_id']);
  $art=$VK->QueryObjectOne("select id,parent_id from vk_comment where table_name='zayav' and table_id=".$_POST['zayav_id']." and status=1 order by id desc limit 1");
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
    $count = $VK->QRow("select count(id) from vk_comment where status=1 and parent_id=".$parent);
    $VK->Query("update vk_comment set child_count=".$count." where id=".$parent);
  }
}

$send->dtime = utf8(FullDataTime(strftime("%Y-%m-%d %H:%M:%S",time())));

echo json_encode($send);
?>
