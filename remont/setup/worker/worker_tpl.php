<?php
if($vku->admin==0) header("Location:".$URL);

/*
if($_POST['worker_id'])
  {
  $count=$VK->QRow("select count(id) from worker where active=1 and ws_id=".$vku->ws_id." and viewer_id=".$_POST['worker_id']);
  if($count==0)
    {
    $hash=md5("ws".$vku->ws_id."_".$_POST['worker_id']."_123abc");
    $VK->Query("insert into worker (ws_id,viewer_id,hash,active) values (".$vku->ws_id.",".$_POST['worker_id'].",'".$hash."',1)");
    setcookie("work","ok",time()+300,"/");
    }
  else setcookie("work","exist",time()+300,"/");

  header("Location: ".$URL."&my_page=remSetupWorker");
  }

if($_COOKIE['work'])
  {
//  if($_COOKIE['work']=='ok') $msg="<DIV class=msgOk>Ќовый сотрудник успешно добавлен.</DIV>";
//  if($_COOKIE['work']=='exist') $msg="<DIV class=msgErr>Ётот сотрудник уже был добавлен ранее.</DIV>";
  setcookie("work","exist",time()-4000,"/");
  }

$spisok=$VK->QueryObjectArray("select * from worker where ws_id=".$vku->ws_id." and active=1 order by id");
foreach($spisok as $sp)
  {
  $wids.=$sp->viewer_id.",";
  $wAdm.=$sp->admin.",";
  $wData.="'".FullData($sp->dtime_add)."',";
  }
$wids=substr($wids,0,strlen($wids)-1);
$wAdm=substr($wAdm,0,strlen($wAdm)-1);
$wData=substr($wData,0,strlen($wData)-1);
*/
include('incHeader.php');
$sel = 'remSetup'; include('remont/mainLinks.php');
$dLink2 = 'Sel'; include('remont/setup/dopLinks.php');

$workers = array();
$spisok = $VK->QueryObjectArray("select * from vk_user where ws_id=".$vku->ws_id." order by dtime_add");
foreach ($spisok as $sp) {
  array_push($workers, array(
    'viewer_id' => $sp->viewer_id,
    'name' => utf8($sp->first_name." ".$sp->last_name),
    'photo' => $sp->photo,
    'admin' => $sp->admin,
    'country_name' => utf8($sp->country_name),
    'city_name' => utf8($sp->city_name)
  ));
}
?>
<DIV id=setup_worker>
  <DIV class=headName>ƒобавление сотрудника</DIV>
  <TABLE cellpadding=0 cellspacing=6 class=add_tab>
  <TR><TD class=tdAbout>—сылка на страницу:<TD><INPUT type=text id=worker_id maxlength=100><TD><A id=find_button>поиск</A><TD id=process>
  </TABLE>
  <DIV id=worker_finded></DIV>

  <DIV class=headName id=h-workers>—отрудники мастерской</DIV>
  <DIV id=workers></DIV>
</DIV>

<SCRIPT type="text/javascript">
var workers = <?php echo json_encode($workers); ?>;
var ws_admin = <?php echo $WS->admin_id; ?>;
</SCRIPT>
<SCRIPT type="text/javascript" src="/remont/setup/worker/worker.js?<?php echo $G->script_style; ?>"></SCRIPT>

<?php include('incFooter.php'); ?>

