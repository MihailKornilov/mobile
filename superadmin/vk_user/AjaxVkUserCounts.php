<?php
require_once('../../include/AjaxHeader.php');

$tables = array(
'accrual' => "����������",
'base_device' => "����������",
'base_model' => "������",
'base_vendor' => "�������������",
'chem_catalog' => "�����",
'client' => "�������",
'device_specific' => "�������������� ���������",
'fw_catalog' => "���������",
'images' => "�����������",
'money' => "������",
'setup_color_name' => "��������� ������",
'setup_device_place' => "��������������� ���������",
'setup_device_specific_item' => "�������� �������������",
'setup_device_specific_razdel' => "������� �������������",
'setup_device_status' => "��������� ���������",
'setup_fault' => "�������������",
'setup_zayavki_category' => "��������� ������",
'setup_zayavki_status' => "������� ������",
'setup_zp_name' => "������������ ���������",
'vk_comment' => "�����������",
'zayavki' => "������",
'zp_catalog' => "��������",
'zp_move' => "�������� ���������",
'zp_zakaz' => "����� ���������"
);

$send = array();
foreach ($tables as $tab => $about) {
  array_push($send, array(
	'table' => $tab,
   	out' => utf8($about),
    'count' => $VK->QRow("select count(id) from ".$tab." where viewer_id_add=".$_POST['viewer_id'])
  ));
}
echo json_encode($send);
?>



