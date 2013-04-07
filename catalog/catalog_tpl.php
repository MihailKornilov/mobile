<?php
include('incHeader.php');
?>
<DIV id=catalog>
  <DIV id=catMenu><INPUT type=hidden id=menu></DIV>
  <DIV id=catFind></DIV>
  <DIV id=findResult>&nbsp;</DIV>

  <TABLE cellpadding=0 cellspacing=0>
  <TR>
    <TD id=spisok>&nbsp;
    <TD id=right>
      <DIV id=links></DIV>
      <DIV class=findHead>Устройство</DIV><DIV id=dev></DIV>
  </TABLE>
</DIV>

<SCRIPT type="text/javascript" src="/include/device/device.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/catalog/catalog.js?<?php echo $G->script_style; ?>"></SCRIPT>


<?php include('incFooter.php'); ?>

