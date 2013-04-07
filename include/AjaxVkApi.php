<?php
require_once('AjaxHeader.php');
require_once('vkapi.class.php');

$VKAPI = new vkapi(2031819,'RjnCrjnbyfRjczr');
$send=$VKAPI->api($_GET['method'],array('country'=>'4'));//
echo json_encode($send);
?>
