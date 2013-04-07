<?php
/*
 * удаление запчасти из каталога
*/
require_once('../../../include/AjaxHeader.php');

$VK->Query("delete from zp_catalog where id=".$_GET['zid']);

$send->time = getTime($T);

echo json_encode($send);
?>
