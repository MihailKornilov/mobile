<?php
require_once('../AjaxHeader.php');

$send->uid = $VK->Query("insert into client (
fio,
telefon,
ws_id,
viewer_id_add
) values (
'".win1251($_POST['fio'])."',
'".win1251($_POST['telefon'])."',
".$vku->ws_id.",
".$_GET['viewer_id'].")");

GclientsCreate();

$send->title = $_POST['fio'];
if ($_POST['telefon']) { $send->content = $_POST['fio']."<DIV class=pole2>".$_POST['telefon']."</DIV>"; }

$VK->Query("insert into history (ws_id,type,client_id,viewer_id_add) values (".$vku->ws_id.",3,".$send->uid.",".$_GET['viewer_id'].")");

echo json_encode($send);
?>



