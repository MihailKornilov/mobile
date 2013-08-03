<?php
include('incHeader.php');
$sel = 'remZayavki'; include('remont/mainLinks.php');
?>

<DIV id=zayavki>
  <DIV id=findResult>&nbsp;</DIV>
  <TABLE cellpadding=0 cellspacing=0 id=tab>
    <TR>
      <TD id=spisok>&nbsp;
      <TD id=right>
          <DIV id=buttonCreate><A HREF="<?= $URL; ?>&p=zayav&d=add&back=remZayavki">Новая заявка</A></DIV><?php //todo заменить back
            ?>


          <DIV id=fast></DIV>

          <DIV class=findHead>Порядок</DIV>
          <DIV class=findContent>
            <INPUT TYPE=hidden id=sort value=1>
            <h2><INPUT TYPE=hidden id=desc></h2>
          </DIV>

          <DIV class=findHead>Статус заявки</DIV><DIV id=status></DIV>
          <DIV class=findHead>Устройство</DIV><DIV class=findContent id=dev></DIV>
          <DIV class=findHead>Нахождение устройства</DIV><INPUT TYPE=hidden id=device_place>
          <DIV class=findHead>Состояние устройства</DIV><INPUT TYPE=hidden id=device_status>
  </TABLE>
</DIV>

<SCRIPT type="text/javascript">
G.device_ids = <?php echo $VK->idsJson("select distinct(base_device_id) from zayavki where base_device_id>0 and zayav_status>0 and ws_id=".$vku->ws_id); ?>;
G.vendor_ids = <?php echo $VK->idsJson("select distinct(base_vendor_id) from zayavki where base_vendor_id>0 and zayav_status>0 and ws_id=".$vku->ws_id); ?>;
G.model_ids = <?php echo $VK->idsJson("select distinct(base_model_id) from zayavki where base_model_id>0 and zayav_status>0 and ws_id=".$vku->ws_id); ?>;
G.device_place_other = <?php echo $VK->idsJson("select distinct(device_place_other) from zayavki where length(device_place_other)>0"); ?>;
</SCRIPT>
<SCRIPT type="text/javascript" src="/include/device/device.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/zayavki/spisok/zayavki.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>


