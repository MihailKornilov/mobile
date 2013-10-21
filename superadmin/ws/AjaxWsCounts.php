<?php
require_once('../../include/AjaxHeader.php');

$tables = array(
'accrual' => "Начисления",
'client' => "Клиенты",
'money' => "Оплаты",
'zayavki' => "Заявки",
'zp_available' => "Наличие запчастей",
'zp_move' => "Движения запчастей",
'zp_zakaz' => "Заказ запчастей"
);

$send = array();
foreach ($tables as $tab => $about) {
  array_push($send, array(
    'table' => $tab,
    'about' => utf8($about),
    'count' => $VK->QRow("select count(id) from ".$tab." where ws_id=".$_POST['ws_id'])
  ));
}
echo json_encode($send);
?>



