<?php
header("Location: ". $URL."&my_page=remZayavki");
if($_POST['model_id'])
  {
  $VK->Query("delete from device_specific where base_model_id=".$_POST['model_id']);
  $VK->Query("update base_model set viewer_id_edit=".$vku->viewer_id.",dtime_edit=current_timestamp where id=".$_POST['model_id']);
  $spec=$VK->QueryObjectArray("select * from setup_device_specific_item where base_device_id=".$_POST['device_id']);
  if(count($spec)>0)
    foreach($spec as $sp)
      if($_POST['spec'.$sp->id])
        $VK->Query("insert into device_specific (base_model_id,specific_id,value,viewer_id_add) values (".$_POST['model_id'].",".$sp->id.",'".textFormat($_POST['spec'.$sp->id])."',".$_GET['viewer_id'].")");
  header("Location:".$URL."&my_page=remDeviceView&id=".$_POST['model_id']);
  }


$model = $VK->QueryObjectOne("select * from base_model where id=".(preg_match("|^[\d]+$|",$_GET['id'])?$_GET['id']:0));

$devName=$VK->QRow("select name from base_device where id=".$model->device_id);
$vendorName=$VK->QRow("select name from base_vendor where id=".$model->vendor_id);

$devNameFull = $devName." ".$vendorName." ".$model->name;


$specArr=$VK->QueryPtPArray("select specific_id,value from device_specific where base_model_id=".$model->id);
$razdel=$VK->QueryObjectArray("select * from setup_device_specific_razdel where base_device_id=".$model->device_id." order by id");
if(count($razdel)>0)
  foreach($razdel as $n=>$raz)
    {
    $specific.="<H2>".$raz->name."</H2>";
    $spec=$VK->QueryObjectArray("select * from setup_device_specific_item where razdel_id=".$raz->id." order by id");
    if(count($spec)>0)
      {
      $specific.="<TABLE cellpadding=0 cellspacing=5 class=specific>";
      foreach($spec as $m=>$s)
        $specific.="<TR><TD class=tdAbout>".$s->name.":<TD><INPUT type=text name=spec".$s->id." id=spec".$s->id." value=\"".$specArr[$s->id]."\">";
      $specific.="</TABLE>";
      }
    }

include('incHeader.php');
$sel = 'remDevice'; include('remont/mainLinks.php');
$dLink2='Sel'; include('remont/device/view/dopLinks.php');
?>


<DIV class=remDeviceEdit>
  <DIV class=headName>Редактирование информации об устройстве <?php echo $devNameFull; ?></DIV>

  <DIV id=copy>
    <DIV class=market><A href='http://market.yandex.ru/search.xml?text=<?php echo $vendorName."%20".$model->name; ?>&hid=91491' target=ya>Посмотреть на маркете <B><?php echo $vendorName." ".$model->name; ?></B></A></DIV>
    <SPAN><?php echo $model->link_yandex?"<A href='".$model->link_yandex."' target=ya>Яндекс-маркет:</A>":"Яндекс-маркет:"; ?></SPAN>
    <INPUT type=text id=yandex value="<?php echo $model->link_yandex; ?>" onkeydown='if(event.keyCode==13)getValuesFromSite(<?php echo $model->id; ?>,1);'>
    <A href='javascript:' onclick=getValuesFromSite(<?php echo $model->id; ?>); class=go>go</A>
  </DIV>

  <DIV id=images style=margin-left:80px;></DIV>

  <FORM method="post" action="<?php echo $URL; ?>&my_page=remDeviceEdit&id=<?php echo $model->id; ?>" name=FormDeviceEdit>
  <?php echo $specific; ?>
  <input type=hidden name=model_id id=model_id value=<?php echo $model->id; ?>>
  <input type=hidden name=device_id value=<?php echo $model->device_id; ?>>
  </FORM>
  <DIV class=vkButton><BUTTON onclick=document.FormDeviceEdit.submit();>Сохранить</BUTTON></DIV><DIV class=vkCancel><BUTTON>Отмена</BUTTON></DIV>
</DIV>



<SCRIPT type="text/javascript">
$(".vkCancel").click(function(){ location.href="index.php?" + G.values + "&my_page=remDeviceView&id=" + $("#model_id").val(); });
$("#yandex").focus();

function getValuesFromSite(ID,ENTER) {
  var YA=$("#yandex").val();
  if (YA) {
    var yaid = YA.split('modelid=')[1].split('&hid')[0];
    $("#copy").append("<IMG src=/img/upload.gif>").find(".go").hide();

    window.enter = ENTER;
    window.ya = yaid;

    $.getJSON("/remont/device/view/AjaxFotoGet.php?" + G.values + "&model_id="+ ID + "&yaid=" + yaid,function (link) {
      window.arr = link;
      window.nn = 0;
      linkUpload(ID,window.arr[window.nn].link);
    });
  }
}

function linkUpload(id,link) {
  $.getJSON("/include/foto/fotoUpload.php?"+G.values+"&table_id="+id+"&table_name=base_model&nocamera=1&link="+link,function (up) {
    $("#images").append("<IMG src=/files/images/base_model/" + up.name + "s.jpg>&nbsp;");
    window.nn++;
    if(window.nn == window.arr.length) {
      $.post("/remont/device/view/AjaxInfoGet.php?" + G.values,{model_id:id,yaid:window.ya},function (res) {
        $("#yandex").val(res[0].link);
        for(var n=0;n<res.length;n++) {
          $("#spec"+res[n].id).val(res[n].value).css('border-color','#4D4');
        }
        if(window.enter == 1) { document.FormDeviceEdit.submit(); }
        $("#copy").find("IMG").remove();
        $("#copy .go").show();
        $("#copy SPAN").html("<A href='"+res[0].link+"' target=ya>Яндекс-маркет:</A>");
      },'json');
    } else {
      linkUpload(id,window.arr[window.nn].link)
    }
  });
}
</SCRIPT>


<?php include('incFooter.php'); ?>













