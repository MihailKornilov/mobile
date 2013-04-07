<?php
include('incHeader.php');

$spisok = $VK->QueryObjectArray("select * from vk_user order by enter_last desc limit 50");
$vk_user = array();
if(count($spisok) > 0) {
  foreach($spisok as $sp) {
    array_push($vk_user, array(
      'id' => $sp->viewer_id,
      'viewer_id' => $sp->viewer_id,
      'first_name' => utf8($sp->first_name),
      'last_name' => utf8($sp->last_name),
      'photo' => $sp->photo,
      'ws_id' => $sp->ws_id,
      'admin' => $sp->admin,
      'dtime_add' => utf8(FullData($sp->dtime_add)),
      'dtime' => $sp->dtime_add,
      'enter_last' => utf8(FullDataTime($sp->enter_last), 1)
    ));
  }
}
?>
<LINK href='/superadmin/vk_user/vk_user.css?<?php echo $G->script_style; ?>' rel='stylesheet' type='text/css'>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">Администрирование</A> » 
  Пользователи
</DIV>

<DIV id=sa_vk_user>
  <DIV id=result></DIV>
  <TABLE cellpadding=0 cellspacing=0>
  <TR><TD id=spisok>
           <TD id=right>
  </TABLE>
</DIV>

<SCRIPT type="text/javascript">
G.sa = {
  vk_user:<?php echo json_encode($vk_user); ?>
};
</SCRIPT>
<SCRIPT type="text/javascript" src="/superadmin/vk_user/vk_user.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>


