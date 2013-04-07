<?php
include('incHeader.php');
$client = $VK->QueryObjectOne("select * from client where ws_id=".$vku->ws_id." and id=".(preg_match("|^[\d]+$|",$_GET['id'])?$_GET['id']:0));
if(!$client->id)  header("Location: ". $URL."&my_page=nopage&parent=remClient");

$sel = 'remClient'; include('remont/mainLinks.php');

$money = $VK->QRow("select count(id) from money where status=1 and client_id=".$client->id);
$money = $money > 0 ? " (".$money.")" : '';

$zayavki = array();
$device_ids = array(); $vendor_ids = array(); $model_ids = array();
$spisok = $VK->QueryObjectArray("select * from zayavki where zayav_status>0 and ws_id=".$vku->ws_id." and client_id=".$client->id." order by id desc");
if(count($spisok) > 0) {
  // составление массива фото
  $images_zayav = array();
  $images_dev = array();
  foreach ($spisok as $sp) {
    if ($sp->base_device_id > 0) { array_push($device_ids, $sp->base_device_id); } // выбор устройств, которые сдавал в ремонт этот клиент
    if ($sp->base_vendor_id > 0) { array_push($vendor_ids, $sp->base_vendor_id); } // выбор устройств, которые сдавал в ремонт этот клиент
    if ($sp->base_model_id > 0) { array_push($model_ids, $sp->base_model_id); } // выбор устройств, которые сдавал в ремонт этот клиент
    array_push($images_zayav, "'zayav".$sp->id."'");
    array_push($images_dev, "'dev".$sp->base_model_id."'");
  }
  $images_zayav = $VK->QueryPtPArray("select owner,link from images where status=1 and sort=0 and owner in (".implode(',',$images_zayav).")");
  $images_dev = $VK->QueryPtPArray("select owner,link from images where status=1 and sort=0 and owner in (".implode(',',array_unique($images_dev)).")");

  foreach ($spisok as $sp) {
    $img = '';
    if (isset($images_zayav["zayav".$sp->id])) {
      $img = $images_zayav["zayav".$sp->id];
    } else if (isset($images_dev["dev".$sp->base_model_id])) {
      $img = $images_dev["dev".$sp->base_model_id];
    }
    array_push($zayavki, array(
      'id' => $sp->id,
      'status' => $sp->zayav_status,
      'nomer' => $sp->nomer,
      'category' => $sp->category,
      'device_id' => $sp->base_device_id,
      'vendor_id' => $sp->base_vendor_id,
      'model_id' => $sp->base_model_id,
      'dtime' => utf8(FullData($sp->dtime_add)),
      'img' => $img,
      'article' => utf8($VK->QRow("select txt from vk_comment where table_name='zayav' and table_id=".$sp->id." and status=1 order by id desc limit 1"))
    ));
  }
}

$spisok = $VK->QueryObjectArray("select * from money where status=1 and client_id=".$client->id." order by id");
if (count($spisok) > 0) {
  $i = array();
  foreach ($spisok as $sp) { array_push($i, $sp->viewer_id_add); }
  $v = $VK->QueryPtPArray("select viewer_id,concat(first_name,' ',last_name) from vk_user where viewer_id in (".implode(',',$i).")");

  $money_spisok = "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok>";
  $money_spisok .= "<TR><TH>Сумма<TH>Примечание<TH>Дата<TH>Принял";
  foreach ($spisok as $sp) {
    $money_spisok .= "<TR><TD align=center width=40><B>".$sp->summa."</B><TD>".$sp->prim."<TD class=dtime>".FullDataTime($sp->dtime_add,1)."<TD width=90><A href='http://vk.com/id".$sp->viewer_id_add."'>".$v[$sp->viewer_id_add]."</A>";
  }
  $money_spisok .= "</TABLE>";
} else { $money_spisok = "<DIV class=findEmpty>Платежей нет</DIV>"; }
?>

<DIV id=clientInfo>
  <TABLE cellpadding=0 cellspacing=0>
  <TR><TD id=left>
              <H4><?php echo $client->fio; ?></H4>
              <DIV id=tab>
              <TABLE cellpadding=0 cellspacing=3>
                <TR><TD class=tdAbout>Телефон:  <TD id=edit_telefon><?php echo $client->telefon; ?></TD>
                <TR><TD class=tdAbout>Баланс:    <TD><B style=color:#<?php echo ($client->balans<0?'A00':'090'); ?>><?php echo $client->balans; ?></B>
              </TABLE>
              </DIV>

              <DIV id=dopMenu>
                <DIV id=result></DIV>
                <A class=linkSel onclick=menu(0);><I></I><B></B><DIV>Заявки<?php echo $client->zayav_count > 0 ? " (".$client->zayav_count.")" : ''; ?></DIV><B></B><I></I></A>
                <A class=link onclick=menu(1);><I></I><B></B><DIV>Платежи<?php echo $money; ?></DIV><B></B><I></I></A>
                <A class=link onclick=menu(2);><I></I><B></B><DIV>Заметки</DIV><B></B><I></I></A>
                <DIV style=clear:both;></DIV>
              </DIV>

              <DIV id=zayavki></DIV>
              <DIV id=client_money><?php echo $money_spisok; ?></DIV>
              <DIV id=client_comment></DIV>

    <TD id=right>
      <DIV id=cLinks></DIV>
      <DIV id=zDop>
        <DIV class=findHead>Статус</DIV><DIV id=status></DIV>
        <DIV class=findHead>Устройство</DIV><DIV id=dev></DIV>
      </DIV>
  </TABLE>
  <DIV id=client_dialog>
</DIV>

<SCRIPT type="text/javascript">
G.client = {
  id:<?php echo $client->id; ?>,
  fio:"<?php echo $client->fio; ?>",
  telefon:"<?php echo $client->telefon; ?>"
}
G.zayavki = <?php echo json_encode($zayavki); ?>;
G.device_ids = [<?php echo implode(',', array_unique($device_ids)); ?>];
G.vendor_ids = [<?php echo implode(',', array_unique($vendor_ids)); ?>];
G.model_ids = [<?php echo implode(',', array_unique($model_ids)); ?>];
</SCRIPT>
<SCRIPT type="text/javascript" src="/include/device/device.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/G_clients_<?php echo $vku->ws_id; ?>.js?<?php echo $WS->g_clients; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/clients.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/client/info/clientInfo.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>

