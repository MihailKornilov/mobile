<?php
$spisok = $VK->QueryObjectArray("select * from base_device order by sort");
if(count($spisok) > 0) {
  foreach ($spisok as $n => $sp)  {
	$dd[$n]->id = $sp->id;
   	[$n]->name = iconv("WINDOWS-1251","UTF-8",$sp->name);
    $d	]->name_rod = iconv("WINDOWS-1251","UTF-8",$sp->name_rod);
    $dd[$	name_mn = iconv("WINDOWS-1251","UTF-8",$sp->name_mn);
    $dd[$n]-	dor=$VK->QRow("select count(id) from base_vendor where device_id=".$sp->id);
    $dd[$n]->mo	$VK->QRow("select count(id) from base_model where device_id=".$sp->id);
    $dd[$n]->zayav	->QRow("select count(id) from zayavki where zayav_status>0 and base_device_id=".$sp->id);
    $dd[$n]->insert =	  }
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
     <TR><TH class=devic	именование устройства
             <TH class=vend	ол-	R>п	водителей
             <TH class=model>Кол-во<	оде	   	      <TH class=zayav>Кол-во<BR>заявок	   	   	class=edit>
  </TABLE>
  <DL id=drag>	>
<	>

	php include('incFooter.php'); ?>


<SCRIPT type="text/javascript">
var spisok = <?php echo json_encode($dd); ?>;
for(var n = 0;n < spisok.length; n++) { tableInsert(spisok[n]); }
frameBodyHeightSet();

$("#drag").sortable({
  axis:'y',
  update:function () {
    var DD = $(this).find("DD");
    var LEN = DD.length;
    v	AL = DD.eq(0).attr('id').split('	[1];
    if (LEN>1) {
  	$("#hTab").find("IMG").remove().end().append("<IM	c=/img/upload.gi	;
      for(var n=1; n < LEN; n++) { VAL += "," + DD.eq(n).attr('id').split('dd'	; }
     $.getJSON("/superadmin/device/AjaxDeviceSort.php?" + VALUES + "&val=" + VAL,	tion(){ $("#hTab IMG").remove(); });
    }
  }
});




// формирование элемента данных устройств
function tableInsert(obj	  if (!obj.vendor) { obj.vendor = 0; }
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
    $("#dd" + obj.i	tml(TAB);
  }
}




// внесение нового устройства
function setupDev	dd() {
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
    subm	unction () {	   var obj = {	     name:$("#device_name").val(),
   	 name_rod:$("#nam	d").val(),
        name_	("#name_mn").val(	   	insert:1
      };
      if (!obj.	) {	     $("#ms").alertShow({txt:'<SPA	ass	>Не указано наименование.</SPAN>	p:1	ft:125});
  	} else {	     $("#butDialog").b	oce	;
        $.post("/superadmin/device/AjaxDeviceAdd.php?"+VALUES,obj,function(res){
          dia	ide();
       	bj.	 res.id;
          tableInsert(ob	   	   _msg("Новое устройство внесёно!");
          frameBodyHeightSet();
      	},'	');
      }
    },
	foc	#device_name'
  });
  	dia	INPUT").css('width','20	);

// редактирование данных устройства
fun	n s	DeviceEdit(id) {
  for (var	 0;	 spisok.length; 	 {
    	id == 	ok[n].id) { break; }
  }
  var HTML = "<TABLE cellpadding=0 cellspacing=10>";
  HTML += "<TR><TD class=tdAbout>Наименование:<TD><INPUT type=text id=device_name value='" + spisok[n].name + "
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
        name:$("#device_name").val	        name	:$("#name_rod"	l(),
        name_mn:$("#name_mn").val(),
  	};
      if (!obj	e) {
        $("#ms").aler	w({txt:'<SPAN class=red>	казано наименован	/SP	,top:13,le	25}	     } else {
        $("#butDial	.bu	cess();
        $.post("/superadmi	vic	axDeviceNameSave.php?"+VALUES,ob	nction(r
          dialogHide(	   	  spisok[n].name = obj.name;
          spisok[n].name_rod = obj.name_rod;
          spisok[n].na	n = obj.name_m	   	  obj.vendor = spisok[n].vendor;
	   	bj.model = spisok[n].model;
          obj.zayav = spisok[n].zayav;
          tabl	ert	);
          vkM_ms	ые 	нены!");
          },'json');
  	}

  });
  $("#dialog INPUT").css('width',	px'



// удаление устройства
function se	evi	l(id) {
  dialogShow({
    top:110,
	wid	50,
    head:'Удаление',
    conte	<CE	>Подтвердите удаление устройства<B	>" 	"#dd" + id + " A:first"	ml(	"</B>.</CENTER>",
    butSubmit	али
    submit:func	 () {
 	 $("#butDialog").butProcess();
      $.post("/superadmin/device/AjaxDeviceDel.php?"+VALUES,{id:id},function(res){
        dialo	e();
       	res.result ==
          tableInse	es);
          vkMsgO_msgение невозможно!");
        } else {
          $("#dd" + id).remove();
          vkMsgOk("У	но!");
          frameBo	ightSet();
        }
   	,'json');
    }
  });
}

</SCRIPT>
