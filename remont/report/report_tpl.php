<?php
if($vku->admin == 0) { header("Location:".$URL); }

include('incHeader.php');
$sel = 'remReport'; include('remont/mainLinks.php');

$ids = $VK->ids("select distinct(viewer_id_add) from history where ws_id=".$vku->ws_id);
$ids_money = $VK->ids("select distinct(viewer_id_add) from money where ws_id=".$vku->ws_id);
$ids .= ($ids_money ? ',' : '').$ids_money;
$ids_remind = $VK->ids("select distinct(viewer_id_add) from reminder where ws_id=".$vku->ws_id);
$ids .= ($ids_remind ? ',' : '').$ids_remind;

$rashod = $VK->vkSelJson('SELECT
                             DISTINCT(DATE_FORMAT(`dtime_add`,"\"%Y-%m\"")),
                             DATE_FORMAT(`dtime_add`,"%Y-%m")
                          FROM `money` WHERE `summa`<0');
?>

<DIV id=report>

  <TABLE cellpadding=0 cellspacing=0>
  <TR><TD id=content>
           <TD id=right>
             <DIV id=menu></DIV>
             <DIV id=podmenu></DIV>
  </TABLE>

  <DIV id=report_dialog></DIV>
</DIV>

<SCRIPT type="text/javascript">
G.vkusers = <?php echo $ids ? $VK->ptpJson("select viewer_id,concat(first_name,' ',last_name) from vk_user where viewer_id in (".$ids.")") : 'null'; ?>;
G.remindActive = "<?php echo $remindActive; ?>";
G.kassa_sum = <?php echo $WS->kassa_start == -1 ? -1 : $WS->kassa_start + $VK->QRow("select sum(sum) from kassa where ws_id=".$vku->ws_id); ?>;
G.rashod_mon = <?=$rashod?>;
</SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/G_clients_<?php echo $vku->ws_id; ?>.js?<?php echo $WS->g_clients; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/clients.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/report/reminder/reminder.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/report/money/money.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/report/report.js?<?php echo $G->script_style; ?>"></SCRIPT>


<?php include('incFooter.php'); ?>

