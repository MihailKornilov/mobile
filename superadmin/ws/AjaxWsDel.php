<?php
require_once('../../include/AjaxHeader.php');

$tables = array(
'accrual',
'client',
'money',
'zayavki',
'zp_available',
'zp_move',
'zp_zakaz'
);

// удаление данных мастерской из базы
foreach ($tables as $tab) {
  $VK->Query("delete from ".$tab." where ws_id=".$_POST['ws_id']);
}
$VK->Query("delete from workshop where id=".$_POST['ws_id']); // удаление самой мастерской
$VK->Query("update vk_user set ws_id=0,admin=0 where ws_id=".$_POST['ws_id']); // снятие пользователя с мастерской

// удаление json-файла с клиентами
$g_clients = $PATH_FILES."../include/clients/G_clients_".$_POST['ws_id'].".js";
if (file_exists($g_clients)) { unlink($g_clients); };

$send->time = 1;

echo json_encode($send);
?>



