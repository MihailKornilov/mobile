<?php include('incHeader.php'); ?>
<LINK href='/superadmin/fault/fault.css?<?php echo $G->script_style; ?>' rel='stylesheet' type='text/css'>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">Администрирование</A> » 
  Виды неисправностей
</DIV>

<DIV id=setup_fault>
  <DIV class=headName>Виды неисправностей<A class=add val=add_>Новая неисправность</A></DIV>
  <TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
     <TR><TH class=uid>id
              <TH class=name>Наименование
              <TH class=edit>
  </TABLE>
  <DL id=drag></DL>
  <DL id=fault_dialog></DL>
</DIV>


<SCRIPT type="text/javascript" src="/superadmin/fault/fault.js?<?php echo $G->script_style; ?>"></SCRIPT>



<?php include('incFooter.php'); ?>


