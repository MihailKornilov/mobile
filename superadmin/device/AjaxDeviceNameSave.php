<?php
require_once('../../include/AjaxHeader.php');

$VK->Query("update base_device set
  name='".iconv("UTF-8","WINDOWS-1251",$_POST['name'])."',
  name_rod='".iconv("UTF-8","WINDOWS-1251",$_POST['name_rod'])."',
  name_mn='".iconv("UTF-8","WINDOWS-1251",$_POST['name_mn'])."'
where id=".$_POST['id']);

$send->id = $_POST['id'];

GvaluesCreate();

echo json_encode($send);
?>



