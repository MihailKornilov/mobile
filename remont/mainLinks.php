<?php
if (!isset($WS->id)) header("Location:".$URL."&my_page=wsIndex");

$remindActive = $VK->QRow("select count(id) from reminder where ws_id=".$vku->ws_id." and day<date_add(current_timestamp, interval 1 day) and status=1 and (private=0 or private=1 and viewer_id_add=".$vku->viewer_id.")");
$remindActive = $remindActive > 0 ? " (<B>".$remindActive."</B>)" : '';

if (!isset($sel)) { $sel = ''; }
$page = array('remClient',   'remZayavki',  'remDevice',     'remZp',        'remReport',                              'remSetup');
$name = array('Клиенты',  'Заявки',        'Устройства',  'Запчасти',  "Отчёты".$remindActive,         'Установки');
$show = array(1,                 1,                    0,                     1,                 $vku->admin,                           $vku->admin);

$links = "<DIV id=mainLinks>";
for ($n = 0; $n < count($page); $n++) {
  if ($show[$n] > 0) {
    $links .= "<A HREF='".$URL."&my_page=".$page[$n]."' class='la".($page[$n] == $sel ? ' sel' : '')."'>".
      "<DIV class=l1></DIV>".
      "<DIV class=l2></DIV>".
      "<DIV class=l3>".$name[$n]."</DIV>".
    "</A>";
  }
}
$links .= "</DIV>";

echo $links;
?>

