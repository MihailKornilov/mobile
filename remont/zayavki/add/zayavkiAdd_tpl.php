<?php
if(!isset($_GET['id'])) { $_GET['id'] = 0; }
if(!isset($_GET['back'])) { $_GET['back'] = ''; }

include('incHeader.php');

$sel = 'remZayavki'; include('remont/mainLinks.php');
?>


<DIV id=zayavkiAdd>
  <DIV class=headName>Внесение новой заявки</DIV>
  <TABLE cellpadding=0 cellspacing=8>
    <TR><TD class=tdAbout>Клиент:                  <TD><INPUT TYPE=hidden id=client_id value="<?php echo $_GET['id']; ?>">
    <TR><TD class=tdAbout>Категория:            <TD><INPUT TYPE=hidden id=category value=1>
    <TR><TD class=tdAbout>Устройство:          <TD><TABLE cellpadding=0 cellspacing=0><TD id=dev><TD id=dev_view></TABLE>
    <TR><TD class=tdAbout>Местонахождение<BR>устройства после<BR>внесения заявки:<TD><INPUT type=hidden id=place>
    <TR><TD class=tdAbout>IMEI:                      <TD><INPUT type=text id=imei maxlength=20>
    <TR><TD class=tdAbout>Серийный номер: <TD><INPUT type=text id=serial maxlength=30>
    <TR><TD class=tdAbout>Цвет:                     <TD><INPUT TYPE=hidden id=color_id value=0>
    <TR><TD class=tdAbout>Неисправности:   <TD id=fault>
    <TR><TD class=tdAbout>Заметка:               <TD><textarea id=comm></textarea>
    <TR><TD class=tdAbout>Добавить напоминание:<TD><INPUT TYPE=hidden id=reminder>
  </TABLE>

  <TABLE cellpadding=0 cellspacing=8 id=reminder_tab>
    <TR><TD class=tdAbout>Содержание:<TD><INPUT TYPE=text id=reminder_txt>
    <TR><TD class=tdAbout>Дата:<TD><INPUT TYPE=hidden id=reminder_day>
  </TABLE>

  <DIV id=ms>
    <DIV class=vkButton><BUTTON>Внести</BUTTON></DIV>
    <DIV class=vkCancel><BUTTON onclick="location.href='<?php echo $URL."&my_page=".$_GET['back']."&id=".$_GET['id']; ?>'">Отмена</BUTTON></DIV>
  </DIV>
</DIV>

<SCRIPT type="text/javascript" src="/include/clients/G_clients_<?php echo $vku->ws_id; ?>.js?<?php echo $WS->g_clients; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/clients.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/foto/foto.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/device/device.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/zayavki/add/zayavkiAdd.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>
