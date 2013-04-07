<?php
require_once('../../include/AjaxHeader.php');

$tables = array(
'accrual' => "Начисления",
'base_device' => "Устройства",
'base_model' => "Модели",
'base_vendor' => "Производители",
'chem_catalog' => "Схемы",
'client' => "Клиенты",
'device_specific' => "Характеристики устройств",
'fw_catalog' => "Прошивоки",
'images' => "Изображения",
'money' => "Оплаты",
'setup_color_name' => "Настройки цветов",
'setup_device_place' => "Местонахождения устройств",
'setup_device_specific_item' => "Элементы характеристик",
'setup_device_specific_razdel' => "Разделы характеристик",
'setup_device_status' => "Состояния устройств",
'setup_fault' => "Неисправности",
'setup_zayavki_category' => "Категории заявок",
'setup_zayavki_status' => "Статусы заявок",
'setup_zp_name' => "Наименования запчастей",
'vk_comment' => "Комментарии",
'zayavki' => "Заявки",
'zp_catalog' => "Запчасти",
'zp_move' => "Движения запчастей",
'zp_zakaz' => "Заказ запчастей"
);

$send = array();
foreach ($tables as $tab => $about) {
  array_push($send, array(
    'table' => $tab,
    'about' => utf8($about),
    'count' => $VK->QRow("select count(id) from ".$tab." where viewer_id_add=".$_POST['viewer_id'])
  ));
}
echo json_encode($send);
?>



