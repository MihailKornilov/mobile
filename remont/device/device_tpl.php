<?php
include('incHeader.php');
?>

<SCRIPT type="text/javascript">
/*
  if(!getCookie('zFindSort')) findDrop();
	$("#dev").device({
		width:190,
		type_no:1,
		add:1,
		func:function(){ devSpisokGet(1,$("#spisok")); },
		device_id:getCookie('zFindDevice'),
		vendor_id:getCookie('zFindVendor'),
		model_id:getCookie('zFindModel')
		});


	$("#empty").val(getCookie('zFindEmpty')).myCheck({name:'Пустые',func:function(){ devSpisokGet(1,$("#spisok")); }});

	devSpisokGet(1,$("#spisok"));

// СБРОС УСЛОВИЙ ПОИСКА
function findDrop()
	{
	setCookie('zFindDesc',0);
	setCookie('zFindDevice',0);
	setCookie('zFindVendor',0);
	setCookie('zFindModel',0);
	setCookie('zFindEmpty',0);
	setCookie('zFindSort',1);
	}

function devSpisokGet(page,OBJ)
	{
	var URL="&page="+page;
	URL+="&device="+$("#dev_device").val();		setCookie('zFindDevice',$("#dev_device").val());
	URL+="&vendor="+$("#dev_vendor").val();		setCookie('zFindVendor',$("#dev_vendor").val());
	URL+="&model="+$("#dev_model").val();			setCookie('zFindModel',$("#dev_model").val());
	URL+="&empty="+$("#empty").val();					setCookie('zFindEmpty',$("#empty").val());
	$.getJSON("/remont/device/AjaxDeviceSpisok.php?"+ G.values + URL,"",function(data){
		if(data[0].count>0)
			{
			var HTML='';
			for(var n=0;n<data.length;n++)
				{
				HTML+="<DIV class=unit id=unit"+data[n].id+">";
				HTML+="<TABLE cellpadding=0 cellspacing=0><TR>";
				HTML+="<TD class=image><IMG src="+(data[n].img?"/files/images/base_model/"+data[n].img+"s.jpg":"/img/nofoto.gif")+" onclick=location.href='<?php echo $URL; ?>&my_page=remDeviceView&id="+data[n].id+"'>";
				HTML+="<TD><H2>"+data[n].dev+"</H2><H3><A href='<?php echo $URL; ?>&my_page=remDeviceView&id="+data[n].id+"'>"+data[n].dev_name+"</A></H3>";
				HTML+="<H4><INPUT type=hidden id="+data[n].id+" value="+data[n].hide+"></H4>";
				HTML+="</TABLE>";
				HTML+="</DIV>";
				}
			if(data[0].page>0) HTML+="<DIV><DIV id=ajaxNext onclick=zayavNext("+data[0].page+");>Следующие 20 устройств</DIV></DIV>";
			$("#findResult").html(data[0].result);
			OBJ.html(HTML);
			var LEN=$("#spisok H4").length;
			for(var n=0;n<LEN;n++)
				$("#spisok H4:eq("+n+") INPUT").myCheck({
					name:'Скрыть',
					func:function(id){
						$("#"+id).next().after("<IMG src=/img/upload.gif>");
						$.getJSON("/remont/device/AjaxDeviceHide.php?" + G.values + "&id="+id+"&hide="+$("#"+id).val(),function(){
							$("#"+id).parent().find("IMG").remove();
							if($("#"+id).val()==1) $("#unit"+id).hide();
							});
					}
				});
			}
		else
			{
			$("#findResult").html("Запрос не дал результатов.");
			OBJ.html("<DIV class=findEmpty>Запрос не дал результатов.</DIV>");
			}

		frameBodyHeightSet();
		});
	}

function zayavNext(page)
	{
	devSpisokGet(page,$("#ajaxNext").css("padding","10px 0px 9px 0px").html("<IMG SRC=/img/upload.gif>").parent());
	}

*/
</SCRIPT>




<?php $sel = 'remDevice'; include('remont/mainLinks.php'); ?>

в разработке
<!-- 
<DIV id=findResult>&nbsp;</DIV>
<TABLE cellpadding=0 cellspacing=0 id=remDevices>
	<TR>
		<TD id=spisok>&nbsp;
		<TD id=find>
			<DIV class=findHead>Устройство</DIV>
			<DIV class=findContent id=dev></DIV>
			<INPUT TYPE=hidden id=empty>
			<DIV class=findContent>&nbsp;</DIV>
			<DIV class=findContent><?php echo $VK->QRow("select count(id) from base_model where viewer_id_edit=".$vku->viewer_id); ?></DIV>
</TABLE>
 -->




<?php include('incFooter.php'); ?>

