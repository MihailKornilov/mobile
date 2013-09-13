<?php
/*
 * ����������� ����������� ������� � ������
*/

require_once('../../../include/AjaxHeader.php');

// ���� ������������� �������� �������� �� �����������, �� ��������������� ���� id
if ($_POST['compat_id'] > 0) {
  $send->compat = $_POST['compat_id'];
} else {
  $send->compat = $_POST['zid'];
  $VK->Query("update zp_catalog set compat_id=id where id=".$_POST['zid']);
}

// ��� ������ �� id �� ���� �������
$send->model = $VK->QRow("select name from base_model where id=".$_POST['model_id']);

if ($_POST['add'] == 1) {
  // ���� ���������� - ������ ����� �������� � �������
  $send->id = $VK->Query("insert into zp_catalog (
name_id,
name_dop,
color_id,
base_device_id,
base_vendor_id,
base_model_id,
viewer_id_add,
find,
compat_id
) values (
".$_POST['name_id'].",
'".win1251($_POST['name_dop'])."',
".$_POST['color_id'].",
".$_POST['device_id'].",
".$_POST['vendor_id'].",
".$_POST['model_id'].",
".$_GET['viewer_id'].",
'".win1251($send->model." ".textFormat($_POST['name_dop']))."',
".$send->compat."
  )");
} else {
  $compat_id = $VK->QRow("select compat_id from zp_catalog where id=".$_POST['new_id']);
  if ($compat_id == 0) {
    // ���� ������������ �������� �� ����� ��������������, �� ����������� ��� �������������
    $send->id = $_POST['new_id'];
    $VK->Query("update zp_catalog set compat_id=".$send->compat." where id=".$send->id);
  } else {
    // ����� ����������� ����� ������������� ���� � �������������� � ����� ��������������� � ������, ������� � ��������
    $send->id = $compat_id;
    $VK->Query("update zp_catalog set compat_id=".$send->compat." where compat_id=".$send->id);
  }

  // �����, ���� �� ��� � ������
  $oldZakaz = $VK->QRow("select ifnull(sum(count),0) from zp_zakaz where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->id);
  $compatZakaz = 0;
  if ($oldZakaz > 0) {
    // ���� ��, �� ����������� �������
    $VK->Query("delete from zp_zakaz where zp_catalog_id=".$send->id);
    $compatZakaz = $VK->QRow("select ifnull(sum(count),0) from zp_zakaz where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->compat);
    $VK->Query("delete from zp_zakaz where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->compat);
    $VK->Query("insert into zp_zakaz (
ws_id,
zp_catalog_id,
count,
viewer_id_add
) values (
".$vku->ws_id.",
".$send->compat.",
".($oldZakaz + $compatZakaz).",
".$vku->viewer_id.")");
  }

  // �����, ���� �� ��� � �������
  $oldAvai = $VK->QRow("select ifnull(sum(count),0) from zp_avai where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->id);
  $compatAvai = 0;
  if ($oldAvai > 0) {
    // ���� ��, �� ����������� �������
    $VK->Query("delete from zp_avai where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->id);
    $compatAvai = $VK->QRow("select ifnull(sum(count),0) from zp_avai where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->compat);
    $VK->Query("delete from zp_avai where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->compat);
    $VK->Query("insert into zp_avai (
ws_id,
zp_catalog_id,
count
) values (
".$vku->ws_id.",
".$send->compat.",
".($oldAvai + $compatAvai).")");
  }

  // ����������� ��������
  $VK->Query("update zp_move set zp_catalog_id=".$send->compat." where ws_id=".$vku->ws_id." and zp_catalog_id=".$send->id);
}

$send->time = getTime($T);

echo json_encode($send);
?>
