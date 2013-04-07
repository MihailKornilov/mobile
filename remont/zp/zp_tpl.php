<?php
/*
//УСТАНОВКА ПОЛЯ БЫСТРОГО ПОИСКА
$model=$VK->QueryPtPArray("select id,name from base_model");
$spisok=$VK->QueryObjectArray("select id,name_dop,base_model_id from zp_catalog");
foreach($spisok as $sp)
  $VK->Query("update zp_catalog set find='".$model[$sp->base_model_id]." ".$sp->name_dop."' where id=".$sp->id);
*/

include('incHeader.php');
$sel = 'remZp'; include('remont/mainLinks.php');
?>
<DIV id=zp></DIV>

<SCRIPT type="text/javascript">
G.get = eval("<?php echo preg_match('|^[\d,\[\]]+$|', $_GET['id']) ? $_GET['id'] : ''; ?>") || [];
G.zp = {
  id:typeof G.get == 'number' ? G.get : 0, // id запчасти, которую нужно показать
  fast:'',
  type:G.get[0] || 1, // тип вывода списка (общий каталог, наличие, заказ...)
  name_id:G.get[1] || 0,
  device_id:G.get[2] || 0,
  vendor_id:G.get[3] || 0,
  model_id:G.get[4] || 0,
  data:null // сохраняется весь список и восстанавливается при возвращении из просмотра запчасти
};

// создание нового списка устройств, которые выбраны для этой мастерской
G.device_ids = <?php echo $VK->idsJson("select distinct(base_device_id) from zayavki where base_device_id>0 and zayav_status>0 and ws_id=".$vku->ws_id); ?>;
</SCRIPT>
<SCRIPT type="text/javascript" src="/include/device/device.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/G_clients_<?php echo $vku->ws_id; ?>.js?<?php echo $WS->g_clients; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/clients/clients.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/include/foto/foto.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/zp/view/zpView.js?<?php echo $G->script_style; ?>"></SCRIPT>
<SCRIPT type="text/javascript" src="/remont/zp/zp.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>
