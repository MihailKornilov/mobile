<?php
require_once('../../../include/AjaxHeader.php');


$send = $VK->QueryObjectOne("select client_id,nomer from zayavki where id=".$_GET['id']);
$VK->Query("delete from zayavki where id=".$_GET['id']);
$VK->Query("update client set zayav_count=zayav_count-1 where id=".$send->client_id);

$VK->Query("insert into history (ws_id,type,value,viewer_id_add) values (".$vku->ws_id.",2,".$send->nomer.",".$_GET['viewer_id'].")");

echo json_encode($send);
?>
