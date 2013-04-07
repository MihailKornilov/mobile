<?php
require_once('../../include/AjaxHeader.php');

$count=$VK->QRow("select count(id) from base_model where vendor_id=".$_GET['vendor_id']." and name='".iconv("UTF-8", "WINDOWS-1251",$_GET['name'])."'");
echo json_encode($count);
?>



