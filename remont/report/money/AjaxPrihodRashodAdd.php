<?php
require_once('../../../include/AjaxHeader.php');

$send->id = $VK->Query("insert into money (
ws_id,
summa,
prim,
kassa,
viewer_id_add
) values (
".$vku->ws_id.",
".$_POST['sum'].",
'".textFormat(win1251($_POST['txt']))."',
".$_POST['kassa'].",
".$_GET['viewer_id']."
)");

// Внесение в кассу
if ($_POST['kassa'] == 1) {
  $VK->Query("insert into kassa (
ws_id,
sum,
txt,
money_id,
viewer_id_add
) values (
".$vku->ws_id.",
".$_POST['sum'].",
'".textFormat(win1251($_POST['txt']))."',
".$send->id.",
".$_GET['viewer_id']."
)");
}

echo json_encode($send);;
?>



