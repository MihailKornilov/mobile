<?php
/*
// ВНЕСЕНИЕ НАЛИЧИЯ ЗАПЧАСТЕЙ
$spisok=$VK->QueryRowArray("select distinct(zp_catalog_id) from zp_move");
foreach($spisok as $sp) {
  $cPrihod=$VK->QRow("select ifnull(sum(count),0) from zp_move where prihod=1 and zp_catalog_id=".$sp[0]);
  $cRashod=$VK->QRow("select ifnull(sum(count),0) from zp_move where prihod=0 and zp_catalog_id=".$sp[0]);
  $VK->Query("insert into zp_available (ws_id,zp_catalog_id,count) values (".$vku->ws_id.",".$sp[0].",".($cPrihod-$cRashod).")");
}

//УСТАНОВКА ГЛАВНЫХ ФОТОГРАФИЙ
$VK->Query("update files set main=0");
$spisok=$VK->QueryRowArray("select distinct table_id from files where table_name='zp' order by id");
foreach($spisok as $sp)
  $VK->Query("update files set main=1 where table_name='zp' and table_id=".$sp[0]." order by id limit 1");

$spisok=$VK->QueryRowArray("select distinct table_id from files where table_name='zayav' order by id");
foreach($spisok as $sp)
  $VK->Query("update files set main=1 where table_name='zayav' and table_id=".$sp[0]." order by id limit 1");


//УСТАНОВКА ИМЁН ГЛАВНЫХ ФОТОГРАФИЙ В ТАБЛИЦЫ zayavki и zp_catalog
$spisok=$VK->QueryObjectArray("select table_id,name from files where table_name='zp' and main=1");
foreach($spisok as $sp)
  $VK->Query("update zp_catalog set img='".$sp->name."' where id=".$sp->table_id);

$spisok=$VK->QueryObjectArray("select table_id,name from files where table_name='zayav' and main=1");
foreach($spisok as $sp)
  $VK->Query("update zayavki set img='".$sp->name."' where id=".$sp->table_id);


// УСТАНОВКА БАЛАНСА КЛИЕНТОВ ИЗ ЗАЯВОК
$spisok=$VK->QueryObjectArray("select * from client order by id");
foreach($spisok as $sp) {
  $zSpisok=$VK->QueryObjectArray("select id from zayavki where zayav_status>0 and client_id=".$sp->id);
  if(count($zSpisok)>0)
    foreach($zSpisok as $z) {
      $money=$VK->QRow("select ifnull(sum(summa),0) from money where table_tip=1 and table_id=".$z->id);
      $VK->Query("update client set balans=balans+".$money." where id=".$sp->id);
      echo $z->id." - ".$money."<BR>";
    }
}
//УСТАНОВКА client_id В oplata
$spisok=$VK->QueryObjectArray("select * from money order by id");
foreach($spisok as $sp) {
  $client=$VK->QRow("select client_id from zayavki where id=".$sp->table_id);
  $VK->Query("update money set client_id=".$client." where id=".$sp->id);
  echo "money_id=".$sp->id." - client_id=".$client." zayav=".$sp->table_id."<BR>";
}
*/

$zayav = $VK->QueryObjectOne("select * from zayavki where ws_id=".$vku->ws_id." and id=".number($_GET['id']));
if(!$zayav->id)  header("Location: ". $URL."&my_page=nopage&parent=remZayavki");

$zayavDel = 0;  // выводить ссылку об удалении заявки либо нет




// Запчасти
$zp = array();
$spisok = $VK->QueryObjectArray("select * from zp_catalog where base_device_id=".$zayav->base_device_id." and base_vendor_id=".$zayav->base_vendor_id." and base_model_id=".$zayav->base_model_id);
if(count($spisok) > 0) {
  // составление массива id для запроса по наличию
  $ids = array();
  foreach($spisok as $sp) {
    array_push($ids, $sp->id);
    array_push($ids, $sp->compat_id);
  }
  if (count($ids) > 0) {
    $ids = implode(',',array_unique($ids));
    $avai = $VK->QueryPtPArray("select zp_catalog_id,count from zp_available where zp_catalog_id in (".$ids.")");
    $zakaz = $VK->QueryPtPArray("select zp_catalog_id,count from zp_zakaz where zayav_id=".$zayav->id." and zp_catalog_id in (".$ids.")");
  }

  foreach($spisok as $sp) {
    $id = $sp->compat_id ? $sp->compat_id : $sp->id;
    array_push($zp, array(
      'id' => $id,
      'name_id' => $sp->name_id,
      'name_dop' => $sp->name_dop,
      'color_id' => $sp->color_id,
      'avai' => isset($avai[$id]) ? $avai[$id] : 0,
      'zakaz' => isset($zakaz[$id]) ? $zakaz[$id] : 0
    ));
  }
}

// Изображения
$spisok = $VK->QueryObjectArray("select * from images where status=1 and owner='zayav".$zayav->id."' order by sort");
if(count($spisok) == 0) {
  $spisok = $VK->QueryObjectArray("select * from images where status=1 and owner='dev".$zayav->base_model_id."' order by sort");
}
$foto = array();
if (count($spisok) > 0) {
  foreach($spisok as $sp) {
    array_push($foto, array(
      'id' => $sp->id,
      'link' => $sp->link,
      'x' => $sp->big_x,
      'y' => $sp->big_y,
      'dtime' => utf8(FullData($sp->dtime_add))
    ));
  }
}




?>


<DIV id=zayavInfo val=end_>
  <DIV id=dopMenu>
    <A class=linkSel><I></I><B></B><DIV>Информация</DIV><B></B><I></I></A>
    <A class=link val=zayavEdit_><I></I><B></B><DIV>Редактирование</DIV><B></B><I></I></A>
    <A class=link val=accrualAdd_><I></I><B></B><DIV>Начислить</DIV><B></B><I></I></A>
    <A class=link val=oplataAdd_><I></I><B></B><DIV>Принять платёж</DIV><B></B><I></I></A>
    <A class=del val=zayavDel_ style=display:<?php echo $zayavDel > 0 ? 'none' : 'block'; ?>;>Удалить заявку</A>
    <DIV style=clear:both;></DIV>
  </DIV>
 
  <TABLE cellpadding=0 cellspacing=10>
  <TR><TD id=left>
     <DIV class=headName>Заявка №<?php echo $zayav->nomer; ?></DIV>
    <TABLE cellpadding=0 cellspacing=4 class=tab>
      <TR><TD class=tdAbout>Категория:    <TD id=zayav_category>
      <TR><TD class=tdAbout>Устройство:  <TD id=zayav_device>
      <TR><TD class=tdAbout>Клиент:          <TD id=zayav_client>
      <TR><TD class=tdAbout>Дата приёма:<TD><?php echo FullDataTime($zayav->dtime_add); ?>
      <TR><TD class=tdAbout>Статус:           <TD><DIV id=zayav_status val=zayavStatus></DIV><DIV id=zayav_status_dtime></DIV>
    </TABLE>
    <DIV class=headBlue>Задачи</DIV><DIV id=zayav_reminder></DIV>
    <DIV id=comm></DIV>
    <DIV id=money><DIV id=accrual></DIV><DIV id=oplata></DIV></DIV>

    <TD id=right>
      <DIV id=foto></DIV>
      <DIV id=foto_upload></DIV>
      <DIV class=headBlue>Информация об устройстве</DIV>
      <DIV class=contentInfo>
        <DIV id=info_device></DIV>
        <TABLE cellpadding=0 cellspacing=1 class=devInfo>
          <TR id=tr_imei class=none><TD>imei:  <TH id=info_imei>
          <TR id=tr_serial class=none><TD>serial:<TH id=info_serial>
          <TR id=tr_color class=none><TD>Цвет:<TH id=info_color>
          <TR><TD>Нахождение:  <TH><A id=info_place val=zayavStatus></A>
          <TR><TD>Состояние:    <TH><A id=info_status val=zayavStatus></A>
        </TABLE>
      </DIV>

    <DIV class=headBlue><A id=zayav_zp_spisok>Список запчастей</A><A id=zayav_zp_add>добавить</A></DIV>
    <DIV class=contentInfo id=zayav_zp></DIV>

  </TABLE>

  <DIV id=zayav_dialog></DIV>

</DIV>

<SCRIPT type="text/javascript" src="/include/clients/G_clients_<?php echo $vku->ws_id; ?>.js?<?php echo $WS->g_clients; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/clients.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/device/device.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/foto/foto.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript">
G.zayav = {
  id:<?php echo $zayav->id; ?>,
  nomer:<?php echo $zayav->nomer; ?>,
  category:<?php echo $zayav->category; ?>,
  client_id:<?php echo $zayav->client_id; ?>,
  client_fio:"<?php echo $VK->QRow("select fio from client where ws_id=".$vku->ws_id." and id=".$zayav->client_id); ?>",
  device:<?php echo $zayav->base_device_id; ?>,
  vendor:<?php echo $zayav->base_vendor_id; ?>,
  model:<?php echo $zayav->base_model_id; ?>,
  status:<?php echo $zayav->zayav_status; ?>,
  status_dtime:"от <?php echo FullDataTime($zayav->zayav_status_dtime, 1); ?>",
  imei:"<?php echo $zayav->imei; ?>",
  serial:"<?php echo $zayav->serial; ?>",
  color:<?php echo $zayav->color_id; ?>,
  place:<?php echo $zayav->device_place; ?>,
  place_other:"<?php echo $zayav->device_place_other; ?>",
  device_status:<?php echo $zayav->device_status; ?>,
  accrual:<?php echo json_encode($accrual); ?>,   // список начислений
  oplata:<?php echo json_encode($oplata); ?>,       // список платежей
  zp:<?php echo json_encode($zp); ?>,                   // список запчастей
  foto:<?php echo json_encode($foto); ?>,               // список изображений
  reminder:<?php echo json_encode($reminder); ?> // список изображений
};

// создание нового списка устройств, которые выбраны для этой мастерской
G.device_ids = <?php echo $VK->idsJson("select distinct(base_device_id) from zayavki where base_device_id>0 and zayav_status>0 and ws_id=".$vku->ws_id); ?>;
</SCRIPT>
<SCRIPT type="text/javascript" src="/remont/zayavki/info/zayavkiInfo.js?<?php echo $G->script_style; ?>"></SCRIPT>


<?php include('incFooter.php'); ?>


