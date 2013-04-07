<?php
require_once('../../include/AjaxHeader.php');

$VK->Query("update base_vendor set
  name='".iconv("UTF-8","WINDOWS-1251",$_POST['name'])."',
  bold=".$_POST['bold']."
where id=".$_POST['id']);

$send->id = $_POST['id'];

GvaluesCreate();

echo json_encode($send);
?>



