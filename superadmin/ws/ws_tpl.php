<?php
include('incHeader.php');

$spisok = $VK->QueryObjectArray("select * from workshop order by id");
$ws = array();
if(count($spisok) > 0) {
  $ids = array();
  foreach($spisok as $sp) { array_push($ids, $sp->admin_id); }
  $admin = $VK->QueryPtPArray("select viewer_id, concat(first_name,' ',last_name) from vk_user where viewer_id in (".implode(',', $ids).")");
  foreach($spisok as $sp) {
    array_push($ws, array(
      'id' => $sp->id,
      'org_name' => utf8($sp->org_name),
      'status' => $sp->status,
      'admin_id' => $sp->admin_id,
      'admin_name' => strlen(str_replace(' ','',$admin[$sp->admin_id])) > 0 ? utf8($admin[$sp->admin_id]) : $sp->admin_id,
      'dtime_add' => utf8(FullData($sp->dtime_add)),
      'dtime_del' => $sp->status == 0 ? utf8(FullData($sp->dtime_del)) : '',
      'country_id' => $sp->country_id,
      'city_id' => $sp->city_id,
      'country_name' => utf8($sp->country_name),
      'city_name' => utf8($sp->city_name)
    ));
  }
}
?>

<DIV id=sa_ws>
  <DIV id=result></DIV>
  <TABLE cellpadding=0 cellspacing=0>
  <TR><TD id=spisok>
           <TD id=right>
  </TABLE>
  <DIV id=ws_dialog></DIV>
</DIV>

<SCRIPT type="text/javascript">
G.sa = {
  ws:<?php echo json_encode($ws); ?>
};
</SCRIPT>
<SCRIPT type="text/javascript" src="/superadmin/ws/ws.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>


