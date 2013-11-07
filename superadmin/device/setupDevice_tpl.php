<?php
$spisok = $VK->QueryObjectArray("select * from base_device order by sort");
if(count($spisok) > 0) {
  foreach ($spisok as $n => $sp)  {
    $dd[$n]->id = $sp->id;
    $dd[$n]->name = iconv("WINDOWS-1251","UTF-8",$sp->name);
    $dd[$n]->name_rod = iconv("WINDOWS-1251","UTF-8",$sp->name_rod);
    $dd[$n]->name_mn = iconv("WINDOWS-1251","UTF-8",$sp->name_mn);
    $dd[$n]->vendor=$VK->QRow("select count(id) from base_vendor where device_id=".$sp->id);
    $dd[$n]->model=$VK->QRow("select count(id) from base_model where device_id=".$sp->id);
    $dd[$n]->zayav=$VK->QRow("select count(id) from zayavki where zayav_status>0 and base_device_id=".$sp->id);
    $dd[$n]->insert = 1;
  }
}
include('incHeader.php');
?>

<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">Администрирование</A> » 
  Устройства
</DIV>

<DIV id=setupZpName>
  <DIV class=headName id=hTab>Список устройств<EM onclick=setupDeviceAdd();>Новое устройство</EM></DIV>
  <TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
     <TR><TH class=device>Наименование устройства
             <TH class=vendor>Кол-во<BR>производителей
             <TH class=model>Кол-во<BR>моделей
             <TH class=zayav>Кол-во<BR>заявок
             <TH class=edit>
  </TABLE>
  <DL id=drag></DL>
</DIV>



<?php include('incFooter.php'); ?>


<SCRIPT type="text/javascript">
var spisok = <?php echo json_encode($dd); ?>;
for(var n = 0;n < spisok.length; n++) { tableInsert(spisok[n]); }
frameBodyHeightSet();

$("#drag").sortable({
  axis:'y',
  update:function () {
    var DD = $(this).find("DD");
    var LEN = DD.length;
    var VAL = DD.eq(0).attr('id').split('dd')[1];
    if (LEN>1) {
      $("#hTab").find("IMG").remove().end().append("<IMG src=/img/upload.gif>");
      for(var n=1; n < LEN; n++) { VAL += "," + DD.eq(n).attr('id').split('dd')[1]; }
     $.getJSON("/superadmin/device/AjaxDeviceSort.php?" + VALUES + "&val=" + VAL,function(){ $("#hTab IMG").remove(); });
    }
  }
});




// формирование элемента данных устройств
function tableInsert(obj) {
  if (!obj.vendor) { obj.vendor = 0; }
  if (!obj.model) { obj.model = 0; }
  if (!obj.zayav) { obj.zayav = 0; }
  var TAB = "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok><TR>";
  TAB += "<TD class=device><A href=<?php echo $URL; ?>&my_page=saVendor&id=" + obj.id + ">" + obj.name + "</A>";
  TAB += "<TD class=vendor align=center>" + (obj.vendor > 0 ? obj.vendor : '');
  TAB += "<TD class=model align=center>" + (obj.model > 0 ? obj.model : '');
  TAB += "<TD class=zayav align=center>" + (obj.zayav > 0 ? obj.zayav : '');
  TAB += "<TD class=edit><DIV class=img_edit onclick=setupDeviceEdit(" + obj.id + ");>&nbsp;</DIV>";
  if(obj.vendor == 0 && obj.model == 0 && obj.zayav == 0) { TAB += "<DIV class=img_del onclick=setupDeviceDel(" + obj.id + ");>&nbsp;</DIV>"; }
  TAB += "</TABLE>";
  if(obj.insert == 1) {
    $("#drag").append("<DD id=dd" + obj.id + ">" + TAB);
  } else {
    $("#dd" + obj.id).html(TAB);
  }
}




// внесение нового устройства
function setupDeviceAdd() {
  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>Наименование:<TD><INPUT type=text id=device_name>";
  HTML += "<TR><TD class=tdAbout>Родительный падеж (кого?):<TD><INPUT type=text id=name_rod>";
  HTML += "<TR><TD class=tdAbout id=ms>Множественное число:<TD><INPUT type=text id=name_mn>";
  HTML += "</TABLE>";
  dialogShow({
    top:110,
    width:430,
    head:'Внесение нового устройства',
    content:HTML,
    submit:function () {
      var obj = {
        name:$("#device_name").val(),
        name_rod:$("#name_rod").val(),
        name_mn:$("#name_mn").val(),
        insert:1
      };
      if (!obj.name) {
        $("#ms").alertShow({txt:'<SPAN class=red>Не указано наименование.</SPAN>',top:13,left:125});
      } else {
        $("#butDialog").butProcess();
        $.post("/superadmin/device/AjaxDeviceAdd.php?"+VALUES,obj,function(res){
          dialogHide();
          obj.id = res.id;
          tableInsert(obj);
          _msg("Новое устройство внесёно!");
          frameBodyHeightSet();
          },'json');
      }
    },
    focus:'#device_name'
  });
  $("#dialog INPUT").css('width','200px');
}



// редактирование данных устройства
function setupDeviceEdit(id) {
  for (var n = 0; n < spisok.length; n++) {
    if (id == spisok[n].id) { break; }
  }
  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>Наименование:<TD><INPUT type=text id=device_name value='" + spisok[n].name + "'>";
  HTML += "<TR><TD class=tdAbout>Родительный падеж (кого?):<TD><INPUT type=text id=name_rod value='" + spisok[n].name_rod + "'>";
  HTML += "<TR><TD class=tdAbout id=ms>Множественное число:<TD><INPUT type=text id=name_mn value='" + spisok[n].name_mn + "'>";
  HTML += "</TABLE>";
  dialogShow({
    top:110,
    width:430,
    head:'Редактирование данных устройства',
    content:HTML,
    butSubmit:'Сохранить',
    submit:function () {
      var obj = {
        id:id,
        name:$("#device_name").val(),
        name_rod:$("#name_rod").val(),
        name_mn:$("#name_mn").val(),
      };
      if (!obj.name) {
        $("#ms").alertShow({txt:'<SPAN class=red>Не указано наименование.</SPAN>',top:13,left:125});
      } else {
        $("#butDialog").butProcess();
        $.post("/superadmin/device/AjaxDeviceNameSave.php?"+VALUES,obj,function(res){
          dialogHide();
          spisok[n].name = obj.name;
          spisok[n].name_rod = obj.name_rod;
          spisok[n].name_mn = obj.name_mn;
          obj.vendor = spisok[n].vendor;
          obj.model = spisok[n].model;
          obj.zayav = spisok[n].zayav;
          tableInsert(obj);
          vkM_msgанные изменены!");
          },'json');
      }
    }
  });
  $("#dialog INPUT").css('width','200px');
}



// удаление устройства
function setupDeviceDel(id) {
  dialogShow({
    top:110,
    width:250,
    head:'Удаление',
    content:"<CENTER>Подтвердите удаление устройства<BR><B>" + $("#dd" + id + " A:first").html() + "</B>.</CENTER>",
    butSubmit:'Удалить',
    submit:function () {
      $("#butDialog").butProcess();
      $.post("/superadmin/device/AjaxDeviceDel.php?"+VALUES,{id:id},function(res){
        dialogHide();
        if(res.result == 0) {
          tableInsert(res);
          vkMsgO_msgение невозможно!");
        } else {
          $("#dd" + id).remove();
          vkMsgOk("Удалено!");
          frameBodyHeightSet();
        }
      },'json');
    }
  });
}

</SCRIPT>
