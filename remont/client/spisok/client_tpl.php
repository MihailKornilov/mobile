<?php
/*
// установка zayav_count у клиентов.
$spisok=$VK->QueryObjectArray("select * from client order by id");
foreach ($spisok as $sp) {
  $count=$VK->QRow("select count(id) from zayavki where zayav_status>0 and client_id=".$sp->id);
  $VK->Query("update client set zayav_count=".$count." where id=".$sp->id);
  }
*/
include('incHeader.php');
$sel = 'remClient'; include('remont/mainLinks.php');
?>

<DIV id=client>
  <DIV id=find></DIV>
  <DIV id=result></DIV>
  <TABLE cellpadding=0 cellspacing=0>
  <TR><TD id=spisok>
           <TD id=right>
              <DIV id=buttonCreate><A>Новый клиент</A></DIV>
              <BR><BR>
              <INPUT TYPE=hidden id=dolg>
  </TABLE>
</DIV>

<SCRIPT type="text/javascript" src="/include/clients/G_clients_<?php echo $vku->ws_id; ?>.js?<?php echo $WS->g_clients; ?>"></SCRIPT>
<SCRIPT type="text/javascript">G.balans = <?php echo $VK->ptpJson("select id,balans from client where balans!=0 and ws_id=".$vku->ws_id." order by id desc"); ?>;</SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/clients.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/client/spisok/client.js?<?php echo $G->script_style; ?>"></SCRIPT>
<?php include('incFooter.php'); ?>



