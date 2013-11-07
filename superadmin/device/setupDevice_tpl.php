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
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">�����������������</A> � 
  ����������
</DIV>

<DIV id=setupZpName>
  <DIV class=headName id=hTab>������ ���������<EM onclick=setupDeviceAdd();>����� ����������</EM></DIV>
  <TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
     <TR><TH class=device>������������ ����������
             <TH class=vendor>���-��<BR>��������������
             <TH class=model>���-��<BR>�������
             <TH class=zayav>���-��<BR>������
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




// ������������ �������� ������ ���������
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




// �������� ������ ����������
function setupDeviceAdd() {
  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>������������:<TD><INPUT type=text id=device_name>";
  HTML += "<TR><TD class=tdAbout>����������� ����� (����?):<TD><INPUT type=text id=name_rod>";
  HTML += "<TR><TD class=tdAbout id=ms>������������� �����:<TD><INPUT type=text id=name_mn>";
  HTML += "</TABLE>";
  dialogShow({
    top:110,
    width:430,
    head:'�������� ������ ����������',
    content:HTML,
    submit:function () {
      var obj = {
        name:$("#device_name").val(),
        name_rod:$("#name_rod").val(),
        name_mn:$("#name_mn").val(),
        insert:1
      };
      if (!obj.name) {
        $("#ms").alertShow({txt:'<SPAN class=red>�� ������� ������������.</SPAN>',top:13,left:125});
      } else {
        $("#butDialog").butProcess();
        $.post("/superadmin/device/AjaxDeviceAdd.php?"+VALUES,obj,function(res){
          dialogHide();
          obj.id = res.id;
          tableInsert(obj);
          _msg("����� ���������� ������!");
          frameBodyHeightSet();
          },'json');
      }
    },
    focus:'#device_name'
  });
  $("#dialog INPUT").css('width','200px');
}



// �������������� ������ ����������
function setupDeviceEdit(id) {
  for (var n = 0; n < spisok.length; n++) {
    if (id == spisok[n].id) { break; }
  }
  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>������������:<TD><INPUT type=text id=device_name value='" + spisok[n].name + "'>";
  HTML += "<TR><TD class=tdAbout>����������� ����� (����?):<TD><INPUT type=text id=name_rod value='" + spisok[n].name_rod + "'>";
  HTML += "<TR><TD class=tdAbout id=ms>������������� �����:<TD><INPUT type=text id=name_mn value='" + spisok[n].name_mn + "'>";
  HTML += "</TABLE>";
  dialogShow({
    top:110,
    width:430,
    head:'�������������� ������ ����������',
    content:HTML,
    butSubmit:'���������',
    submit:function () {
      var obj = {
        id:id,
        name:$("#device_name").val(),
        name_rod:$("#name_rod").val(),
        name_mn:$("#name_mn").val(),
      };
      if (!obj.name) {
        $("#ms").alertShow({txt:'<SPAN class=red>�� ������� ������������.</SPAN>',top:13,left:125});
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
          vkM_msg����� ��������!");
          },'json');
      }
    }
  });
  $("#dialog INPUT").css('width','200px');
}



// �������� ����������
function setupDeviceDel(id) {
  dialogShow({
    top:110,
    width:250,
    head:'��������',
    content:"<CENTER>����������� �������� ����������<BR><B>" + $("#dd" + id + " A:first").html() + "</B>.</CENTER>",
    butSubmit:'�������',
    submit:function () {
      $("#butDialog").butProcess();
      $.post("/superadmin/device/AjaxDeviceDel.php?"+VALUES,{id:id},function(res){
        dialogHide();
        if(res.result == 0) {
          tableInsert(res);
          vkMsgO_msg���� ����������!");
        } else {
          $("#dd" + id).remove();
          vkMsgOk("�������!");
          frameBodyHeightSet();
        }
      },'json');
    }
  });
}

</SCRIPT>
