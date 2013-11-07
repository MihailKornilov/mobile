<?php
if(!$SA[$_GET['viewer_id']]) header("Location:".$URL);

$dev = $VK->QueryObjectOne("select * from base_device where id=".$_GET['id']);

$spisok=$VK->QueryObjectArray("select * from base_vendor where device_id=".$dev->id." order by sort");
if(count($spisok)>0) {
  foreach ($spisok as $n => $sp) {
    $dd[$n]->id = $sp->id;
    $dd[$n]->name = iconv("WINDOWS-1251","UTF-8",$sp->name);
    $dd[$n]->bold = $sp->bold;
    $dd[$n]->model = $VK->QRow("select count(id) from base_model where vendor_id=".$sp->id);
    $dd[$n]->zayav = $VK->QRow("select count(id) from zayavki where zayav_status>0 and base_vendor_id=".$sp->id);
    $dd[$n]->insert = 1;
  }
}
include('incHeader.php');
?>
<DIV class=path>
  <?php include('superadmin/incBack.php'); ?>
  <A HREF="<?php echo $URL; ?>&my_page=superAdmin">�����������������</A> � 
  <A HREF="<?php echo $URL; ?>&my_page=saDevice">����������</A> � 
  <?php echo $dev->name; ?>
</DIV>

<DIV id=setupZpName>
  <DIV class=headName>��������� ������������ ����������</DIV>
  <TABLE cellpadding=0 cellspacing=7 style="margin:0px 0px 20px 40px;">
  <TR><TD class=tdAbout>������������:<TD><INPUT type=text id=name value='<?php echo $dev->name; ?>'>
  <TR><TD class=tdAbout>����������� ����� (����?):<TD><INPUT type=text id=name_rod value='<?php echo $dev->name_rod; ?>'>
  <TR><TD class=tdAbout>������������� �����:<TD><INPUT type=text id=name_mn value='<?php echo $dev->name_mn; ?>'>
  <TR><TD><TD id=ds><DIV class=vkButton ><BUTTON onclick=devSave(this);>���������</BUTTON></DIV>
  </TABLE>

  <DIV class=headName id=hTab>������ ��������������<EM onclick=setupVendorAdd();>����� �������������</EM></DIV>
  <TABLE cellpadding=0 cellspacing=0 class=tabSpisok>
    <TR><TH class=vendor>�������������
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
     $.getJSON("/superadmin/vendor/AjaxVendorSort.php?" + VALUES + "&val=" + VAL,function(){ $("#hTab IMG").remove(); });
    }
  }
});



// ���������� ��������� ������������ ����������
function devSave(OBJ) {
  if(!$("#name").val()) {
    $("#ds").alertShow({txt:'<SPAN class=red>�� ������� ������������ ����������</SPAN>',top:-42,left:-55,otstup:90});
  } else {
    $(OBJ).butProcess();
    $.post("/superadmin/device/AjaxDeviceNameSave.php?"+VALUES,{
      id:<?php echo $dev->id; ?>,
      name:$("#name").val(),
      name_rod:$("#name_rod").val(),
      name_mn:$("#name_mn").val()
      },
      function (res) {
        _msg("���������!");
        $(OBJ).butRestore();
    });
  }
}


// ������������ �������� ������ �������������
function tableInsert(obj) {
  if (!obj.model) { obj.model = 0; }
  if (!obj.zayav) { obj.zayav = 0; }
  var TAB = "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok><TR>";
  TAB += "<TD class=vendor><A href=<?php echo $URL; ?>&my_page=saModel&id=" + obj.id + (obj.bold == 1 ? ' style=font-weight:bold;':'') +" val=" + obj.bold + ">" + obj.name + "</A>";
  TAB += "<TD  class=model align=center>" + (obj.model > 0 ? obj.model : '');
  TAB += "<TD  class=zayav align=center>" + (obj.zayav > 0 ? obj.zayav : '');
  TAB += "<TD class=edit><DIV class=img_edit onclick=setupVendorEdit(" + obj.id + ");>&nbsp;</DIV>";
  if(obj.model == 0 && obj.zayav == 0) { TAB += "<DIV class=img_del onclick=setupVendorDel(" + obj.id + ");>&nbsp;</DIV>"; }
  TAB += "</TABLE>";
  if(obj.insert == 1) {
    $("#drag").append("<DD id=dd" + obj.id + ">" + TAB);
  } else {
    $("#dd" + obj.id).html(TAB);
  }
}



// �������� ������ �������������
function setupVendorAdd() {
  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>������������:<TD><INPUT type=text id=vendor_name style=width:200px;>";
  HTML += "<TR><TD class=tdAbout>��������:<TD id=ms><INPUT type=hidden id=vendor_bold value=0>";
  HTML += "</TABLE>";
  dialogShow({
    top:110,
    head:'�������� ������ �������������',
    content:HTML,
    submit:function () {
      var obj = {
        device_id:<?php echo $dev->id; ?>,
        name:$("#vendor_name").val(),
        bold:$("#vendor_bold").val()
      };
      if (!obj.name) {
        $("#ms").alertShow({txt:'<SPAN class=red>�� ������� ������������.</SPAN>',top:8,left:-3});
      } else {
        $("#butDialog").butProcess();
        $.post("/superadmin/vendor/AjaxVendorAdd.php?"+VALUES,obj,function(res){
          dialogHide();
          obj.id = res.id;
          obj.insert = 1;
          tableInsert(obj);
          vkM_msg���� ������������� �����!");
          frameBodyHeightSet();
          },'json');
      }
    },
    focus:'#vendor_name'
  });
  $("#vendor_bold").myCheck();
}




// �������������� ������ �������������
function setupVendorEdit(id) {
  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>������������:<TD><INPUT type=text id=vendor_name style=width:200px; value='" + $("#dd" + id + " A:first").html() + "'>";
  HTML += "<TR><TD class=tdAbout>��������:<TD id=ms><INPUT type=hidden id=vendor_bold value=" + $("#dd" + id + " A:first").attr('val') + ">";
  HTML += "</TABLE>";
  dialogShow({
    top:110,
    head:'�������������� �������� �������������',
    content:HTML,
    butSubmit:'���������',
    submit:function () {
      var obj = {
        id:id,
        name:$("#vendor_name").val(),
        bold:$("#vendor_bold").val(),
        model:$("#dd" + id + " .model").html(),
        zayav:$("#dd" + id + " .zayav").html()
      };
      if (!obj.name) {
        $("#ms").alertShow({txt:'<SPAN class=red>�� ������� ������������.</SPAN>',top:8,left:-3});
      } else {
        $("#butDialog").butProcess();
        $.post("/superadmin/vendor/AjaxVendorEdit.php?"+VALUES,obj,function(res){
          dialogHide();
          tableInsert(obj);
          vkMsgO_msg�� ��������!");
          },'json');
      }
    }
  });
  $("#vendor_bold").myCheck();
}




// �������� �������������
function setupVendorDel(id) {
  dialogShow({
    top:110,
    width:250,
    head:'��������',
    content:"<CENTER>����������� �������� �������������<BR><B>" + $("#dd" + id + " A:first").html() + "</B>.</CENTER>",
    butSubmit:'�������',
    submit:function () {
      $("#butDialog").butProcess();
      $.post("/superadmin/vendor/AjaxVendorDel.php?"+VALUES,{id:id},function(res){
        dialogHide();
        if(res.result == 0) {
          tableInsert(res);
          vkMsgOk("_msg� ����������!");
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

