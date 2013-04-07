<?php
if(!$SA[$_GET['viewer_id']]) header("Location:".$URL);

$ven = $VK->QueryObjectOne("select * from base_vendor where id=".$_GET['id']);
$devName = $VK->QRow("select name from base_device where id=".$ven->device_id);
$spisok=$VK->QueryObjectArray("select * from base_model where vendor_id=".$ven->id." order by name");
if (count($spisok) > 0) {
  foreach($spisok as $n => $sp) {
    $tr[$n]->id = $sp->id;
    $tr[$n]->name = iconv("WINDOWS-1251","UTF-8",$sp->name);
    $tr[$n]->img = $sp->img;

 //   $tr[$n]->zayav = $VK->QRow("select count(id) from zayavki where zayav_status>0 and base_model_id=".$sp->id);
    }
  }
include('incHeader.php');
?>
<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">Администрирование</A> » 
  <A HREF="<?php echo $URL; ?>&my_page=saDevice">Устройства</A> » 
  <A HREF="<?php echo $URL."&my_page=saVendor&id=".$ven->device_id; ?>"><?php echo $devName; ?></A> » 
  <?php echo $ven->name; ?>
</DIV>

<DIV id=saModel>
  <DIV class=headName>Список моделей <?php echo $ven->name; ?><EM onclick=setupModelAdd();>Новая модель</EM></DIV>
  <DIV id=modelTable></DIV>
</DIV>

<?php include('incFooter.php'); ?>





<SCRIPT type="text/javascript">
var spisok = <?php echo json_encode($tr); ?>;
tableCreate();

// формирование таблицы с моделями
function tableCreate() {
  var TAB = "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok><TR><TH><TH>Модель<TH>Заявки<TH>";
  for(var n = 0;n < spisok.length; n++) {
    TAB += "<TR id=tr" + spisok[n].id + ">";
    TAB += "<TD align=center><IMG src=" + (spisok[n].img ? "/files/images/base_model/" + spisok[n].img + "s.jpg" : "/img/nofoto.gif") + " height=30>";
    TAB += "<TD><A href=<?php echo $URL; ?>&my_page=saModel&id=" + spisok[n].id + " val=" + spisok[n].id + "," + n + ">" + spisok[n].name + "</A>";
    TAB += "<TD align=center>" + (spisok[n].zayav > 0 ? spisok[n].zayav : '');
    TAB += "<TD><DIV class=img_edit onclick=setupModelEdit(" + spisok[n].id + ");>&nbsp;</DIV>";
    if(spisok[n].zayav == 0) { TAB += "<DIV class=img_del onclick=setupModelDel(" + spisok[n].id + ");>&nbsp;</DIV>"; }
  }
  TAB += "</TABLE>";
  $("#modelTable").html(TAB);
  frameBodyHeightSet();
}
</SCRIPT>
