$("#catFind").topSearch({focus:1,width:430});
$("#dev").device({ width:150});

// ������� ����-������
$("#menu").linkMenu({
	head:'����',
	spisok:[{uid:1,title:(G.vku.ws_id > 0 ? '������� �' : '�������') + " ����������"}],
	func:function () { location.href="/index.php?" + G.values + "&my_page=" + (G.vku.ws_id > 0  ? 'remClient' : 'wsIndex'); },
  right:1
});

// ������ ����
$("#links").infoLink({
  spisok:[
    {uid:1,title:'��������'},
    {uid:2,title:'�����'}
  ],
  func:function (id) {}
});



/*
catSet('fw');

function catSet(TYPE)
	{
	$("#clientRightDiv .infoLinkSel").attr('class','infoLink');
	var N=0;
	switch(TYPE){
		case 'zp':		N=1; break;
		case 'fw':		N=0; break;
		case 'chem':	N=1; break;
		case 'video':	N=4; break;
		case 'note':	N=5; break;
		case 'game':	N=6; break;
		}
	$("#clientRightDiv .infoLink:eq("+N+")").attr('class','infoLinkSel');
	catSpisokGet({type:TYPE,page:1});
	}

function catSpisokGet(OBJ)
	{
	var OBJ = $.extend({
		type:'fw',
		page:1,
		view:$("#clientSpisok"),
		url:''
		},OBJ);

	OBJ.url="&page="+OBJ.page;

	switch(OBJ.type){
		case 'device':	deviceGet(OBJ); break;
		case 'zp':			zpGet(OBJ); break;
		case 'fw':			fwGet(OBJ); break;
		case 'chem':		chemGet(OBJ); break;
		case 'video':	N=4; break;
		case 'note':	N=5; break;
		case 'game':	N=6; break;
		}
	}

// ����������
function deviceGet(OBJ)
	{
	$.getJSON("/catalog/device/AjaxDeviceSpisok.php?"+ G.values +OBJ.url,function(data){
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
			if(data[0].page>0) HTML+="<DIV><DIV id=ajaxNext onclick=zayavNext("+data[0].page+");>��������� 20 ���������</DIV></DIV>";
			$("#findResult").html(data[0].result);
			OBJ.view.html(HTML);
			}
		else
			{
			$("#findResult").html("������ �� ��� �����������.");
			OBJ.view.html("<DIV class=findEmpty>������ �� ��� �����������.</DIV>");
			}
		frameBodyHeightSet();
		});
	}


// ��������
function zpGet(OBJ)
	{
	$.getJSON("/catalog/zp/AjaxZpSpisok.php?" + G.values + OBJ.url,function(data){
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
			if(data[0].page>0) HTML+="<DIV><DIV id=ajaxNext onclick=zayavNext("+data[0].page+");>��������� 20 ���������</DIV></DIV>";
			$("#findResult").html(data[0].result);
			OBJ.view.html(HTML);
			}
		else
			{
			$("#findResult").html("������ �� ��� �����������.");
			OBJ.html("<DIV class=findEmpty>������ �� ��� �����������.</DIV>");
			}
		frameBodyHeightSet();
		});
	
	}

// ��������
function fwGet(OBJ)
	{
	$.getJSON("/catalog/fw/AjaxFwSpisok.php?"+ G.values + OBJ.url,function(data){
		if(data[0].count>0)
			{
			var HTML='';
			for(var n=0;n<data.length;n++)
				{
				HTML+="<DIV class=fwUnit>";
				HTML+="<H3><A href='"+data[n].link+"'>"+data[n].name+"</A></H3>";
				HTML+="<H4>"+data[n].dev+" <B>"+data[n].dev_name+"</B></H4>";
				if(data[n].about) HTML+="<H5>"+data[n].about+"</H5>";
				HTML+="</DIV>";
				}
			if(data[0].page>0) HTML+="<DIV><DIV id=ajaxNext onclick=zayavNext("+data[0].page+");>��������� 20 ��������</DIV></DIV>";
			$("#findResult").html(data[0].result);
			OBJ.view.html(HTML);
			}
		else
			{
			$("#findResult").html("<H6><A href='javascript:' onclick=fwAdd();>������ ����� �������� � �������</A></H6>������ �� ��� �����������.");
			OBJ.view.html("<DIV class=findEmpty>������ �� ��� �����������.</DIV>");
			}
		frameBodyHeightSet();
		});
	}

function fwAdd()
	{
	var HTML="<TABLE cellpadding=0 cellspacing=10 width=100%>";
	HTML+="<TR><TD class=tdAbout>������������:<TD><INPUT TYPE=text id=fwName style=width:260px;>";
	HTML+="<TR><TD class=tdAbout>��������:<TD><TEXTAREA id=fwAbout style=width:260px;height:50px;></TEXTAREA>";
	HTML+="<TR><TD class=tdAbout>����������:<TD><DIV id=fwDev></DIV>";
	HTML+="<TR><TD class=tdAbout>������ ������:<TD><INPUT TYPE=text id=fwLink style=width:260px;>";
	HTML+="</TABLE>";
	dialogShow({
		width:420,
		head:"�������� ����� �������� � �������",
		content:HTML,
		submit:function(){
			if(!$("#fwName").val()) alert("���������� ����������� ������� �������� ��������.")
			else
				if(!$("#fwLink").val()) alert("���������� ����������� ������� ������ ������.")
				else
					if($("#fwDev_model").val()==0) alert("���������� ����������� ������� ����������.")
					else
						{
						$("#butDialog").butProcess();
						$.post("/catalog/fw/AjaxFwAdd.php?"+G.values,{
							name:$("#fwName").val(),
							about:$("#fwAbout").val(),
							device:$("#fwDev_device").val(),
							vendor:$("#fwDev_vendor").val(),
							model:$("#fwDev_model").val(),
							link:$("#fwLink").val()
							},function(res){ dialogHide(); catSet('fw'); },'json');
						}
			},
		focus:'#fwName'
		});

	$("#fwDev").device({ width:200});
	}





// �����
function chemGet(OBJ)
	{
	$.getJSON("/catalog/chem/AjaxChemSpisok.php?" + G.values +OBJ.url,function(data){
		if(data[0].count>0)
			{
			var HTML='';
			for(var n=0;n<data.length;n++)
				{
				HTML+="<DIV class=fwUnit>";
				HTML+="<H3><A href='"+data[n].link+"'>"+data[n].name+"</A></H3>";
				HTML+="<H4>"+data[n].dev+" <B>"+data[n].dev_name+"</B></H4>";
				if(data[n].about) HTML+="<H5>"+data[n].about+"</H5>";
				HTML+="</DIV>";
				}
			if(data[0].page>0) HTML+="<DIV><DIV id=ajaxNext onclick=zayavNext("+data[0].page+");>��������� 20 ����</DIV></DIV>";
			$("#findResult").html(data[0].result);
			OBJ.view.html(HTML);
			}
		else
			{
			$("#findResult").html("<H6><A href='javascript:' onclick=chemAdd();>������ ����� ����� � �������</A></H6>������ �� ��� �����������.");
			OBJ.view.html("<DIV class=findEmpty>������ �� ��� �����������.</DIV>");
			}
		frameBodyHeightSet();
		});
	}

function chemAdd()
	{
	var HTML="<TABLE cellpadding=0 cellspacing=10 width=100%>";
	HTML+="<TR><TD class=tdAbout>������������:<TD><INPUT TYPE=text id=fwName style=width:260px;>";
	HTML+="<TR><TD class=tdAbout>��������:<TD><TEXTAREA id=fwAbout style=width:260px;height:50px;></TEXTAREA>";
	HTML+="<TR><TD class=tdAbout>����������:<TD><DIV id=fwDev></DIV>";
	HTML+="<TR><TD class=tdAbout>������ ������:<TD><INPUT TYPE=text id=fwLink style=width:260px;>";
	HTML+="</TABLE>";
	dialogShow({
		width:420,
		head:"�������� ����� ����� � �������",
		content:HTML,
		submit:function(){
			if(!$("#fwName").val()) alert("���������� ����������� ������� �������� ��������.")
			else
				if(!$("#fwLink").val()) alert("���������� ����������� ������� ������ ������.")
				else
					if($("#fwDev_model").val()==0) alert("���������� ����������� ������� ����������.")
					else
						{
						$("#butDialog").butProcess();
						$.post("/catalog/chem/AjaxChemAdd.php?"+G.values,{
							name:$("#fwName").val(),
							about:$("#fwAbout").val(),
							device:$("#fwDev_device").val(),
							vendor:$("#fwDev_vendor").val(),
							model:$("#fwDev_model").val(),
							link:$("#fwLink").val()
							},function(res){ dialogHide(); catSet('chem'); },'json');
						}
			},
		focus:'#fwName'
		});

	$("#fwDev").device({ width:200});
	}



/*
function catSpisokGet(page)
	{
	devSpisokGet(page,$("#ajaxNext").css("padding","10px 0px 9px 0px").html("<IMG SRC=/img/upload.gif>").parent());
	}
*/
