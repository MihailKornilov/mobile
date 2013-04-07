<?php
/*
 * Удаление совместимости
*/
require_once('../../../include/AjaxHeader.php');


$compat_id = $VK->QRow("SELECT compat_id FROM zp_catalog WHERE id=".$_GET['id']);
$VK->Query("UPDATE zp_catalog SET compat_id=0 WHERE id=".$_GET['id']);

// Если удаляется из совместимости главная запчасть...
if ($compat_id == $_GET['id']) {
    // Запрос id всей группы совместимости
    $spisok = $VK->QueryObjectArray("SELECT id FROM zp_catalog WHERE compat_id=".$compat_id);
    // Если в группе осталась всего одна запчасть, то её совместимость ставится 0, иначе главой группы становится первая по списку
    $VK->Query("UPDATE zp_catalog SET compat_id=".(count($spisok) > 1 ? $spisok[0]->id : 0)." WHERE compat_id=".$compat_id);
    // Перенос наличия, движения и заказа на новую главную запчасть
    $VK->Query("UPDATE zp_available SET zp_catalog_id=".$spisok[0]->id." where zp_catalog_id=".$compat_id);
    $VK->Query("UPDATE zp_move SET zp_catalog_id=".$spisok[0]->id." where zp_catalog_id=".$compat_id);
    $VK->Query("UPDATE zp_zakaz SET zp_catalog_id=".$spisok[0]->id." where zp_catalog_id=".$compat_id);
}

$send->id = $_GET['id'];
echo json_encode($send);
?>
