<?php
/*
 * �������� �������������
*/
require_once('../../../include/AjaxHeader.php');


$compat_id = $VK->QRow("SELECT compat_id FROM zp_catalog WHERE id=".$_GET['id']);
$VK->Query("UPDATE zp_catalog SET compat_id=0 WHERE id=".$_GET['id']);

// ���� ��������� �� ������������� ������� ��������...
if ($compat_id == $_GET['id']) {
    // ������ id ���� ������ �������������
    $spisok = $VK->QueryObjectArray("SELECT id FROM zp_catalog WHERE compat_id=".$compat_id);
    // ���� � ������ �������� ����� ���� ��������, �� � ������������� �������� 0, ����� ������ ������ ���������� ������ �� ������
    $VK->Query("UPDATE zp_catalog SET compat_id=".(count($spisok) > 1 ? $spisok[0]->id : 0)." WHERE compat_id=".$compat_id);
    // ������� �������, �������� � ������ �� ����� ������� ��������
    $VK->Query("UPDATE zp_avai SET zp_catalog_id=".$spisok[0]->id." where zp_catalog_id=".$compat_id);
    $VK->Query("UPDATE zp_move SET zp_catalog_id=".$spisok[0]->id." where zp_catalog_id=".$compat_id);
    $VK->Query("UPDATE zp_zakaz SET zp_catalog_id=".$spisok[0]->id." where zp_catalog_id=".$compat_id);
}

$send->id = $_GET['id'];
echo json_encode($send);
?>
