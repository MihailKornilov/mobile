<?php
require_once('../../../include/AjaxHeader.php');

$send->spisok = array();
$spisok = $VK->QueryObjectArray("select * from money
                                where ws_id=".$vku->ws_id." and
                                      status=1 and
                                      summa<0 and
                                     dtime_add LIKE '".$_GET['mon']."%'");
if (count($spisok) > 0) {
  foreach ($spisok as $sp) {
    array_push($send->spisok, array(
      'id' => $sp->id,
      'sum' => $sp->summa * -1,
      'txt' => utf8($sp->prim),
      'dtime' => utf8(FullDataTime($sp->dtime_add)),
      'viewer_id_add' => $sp->viewer_id_add
    ));
  }
}

echo json_encode($send);;
?>



