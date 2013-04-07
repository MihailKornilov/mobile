<?php
require_once('../../../include/AjaxHeader.php');

$send=$VK->Query("insert into setup_device_specific_razdel (base_device_id,name,viewer_id_add) values (".$_POST['device_id'].",'".iconv("UTF-8", "WINDOWS-1251",$_POST['name'])."',".$_GET['viewer_id'].")");

echo json_encode($send);
?>



