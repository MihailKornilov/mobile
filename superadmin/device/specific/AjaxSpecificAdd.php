<?php
require_once('../../../include/AjaxHeader.php');

$send=$VK->Query("insert into setup_device_specific_item (
base_device_id,
razdel_id,
name,
info,
viewer_id_add
) values (
".$_POST['device_id'].",
".$_POST['razdel_id'].",
'".iconv("UTF-8", "WINDOWS-1251",textFormat($_POST['name']))."',
'".iconv("UTF-8", "WINDOWS-1251",textFormat($_POST['info']))."',
".$_GET['viewer_id'].")");

echo json_encode($send);
?>



