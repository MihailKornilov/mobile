deviceStatusPrint();

function deviceStatusPrint() {
  var html ="";
  for (var n = 0; n < G.device_status_spisok.length; n++) {
	var sp = G.device_status_spisok[n];
   	l += "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok id=table_" + sp.uid + ">";
    ht	= "<TR><TD class=uid>" + sp.uid;
    html 	<TD class=name>" + sp.title;
    html += 	 class=zayav_count>" + (G.zayav_count[n] > 0 ? G.zayav_count[n] : '');
    html += "<T	ass=edit>";
    if (sp.uid > 0	html += "<DIV class=img_edit val=edit_" + sp.uid + "></DIV>"; }
    if (sp.uid > 0 &&	ayav_count[n] == 0) { html += "<DIV class=img_del val=del_" + sp.uid + "></DIV>"; }
    html += "</TABLE>";
	  $("#drag").html(html);
} // end deviceStatusPrint


$("#drag").sortable({
  axis:'y',
  update:function () {
    var uids = $(this).find	id");
    if (uids.length > 1) {
   	ar arr = [];
      for(var	 0; n < uids.length	+) { arr.push(uids.eq(n).html()); }
      $.getJSON("/superadmin/device/sta	AjaxDeviceStatusSort.php?" + VALUES + "&val=" + arr.join(','), function () {  });
    }
  }
});


$("#setup_device_status").	k(function (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    switch (val[0])	   case 'edit': edit(val[	 break;
    case 'add	dd(); break;
    case 'del': del(val[	 break;
    }
  }

  // �����	����� ������������
  function edit(	{
    var dialog = $("#device_status_dialog")._dialog({
      wid	40,
      head:'��������� ������������',
      conten	�����������: <IN	type=text id=status_name value='" + 	table_" + id + " .name:first").html() + "'>",
      butSubmit:'���������',
      submit:function () {
        var send = 	        id:id,
          nam	"#status_name").val()
    	};
	    dialog.proce	;
 	   $.post("/	rad	device/status/AjaxDeviceStatusEdit	?" 	VALUES	, f	ion (res) {
         	log	se();
          _msg("��������!");
          $("#table_" + id + " .name:first").html(send.name)
 	   	json');
      }
    }
  	 end edit

  // ��������	���	����
  function add() {
    var dialog = $("#device_stat	ial	._dialog({
    	dth:240	    head:'�������� ������ �������',
      content:"������������: <INPUT ty	ext id=status_name>",
      submit:function () {
    	var send = {
   	   name:$("#status_name").val()
     	;
        dialog.process();
        $.post("/superadmin/device/	us/AjaxDeviceStatusAdd.php	 G.	LUESnd, function	s) 	        G.zayav_count.push(0);
   	   	vice_s	s_s	k.push({uid:res.id, t	:se	ame});
          deviceStatusPrint();
          dialog.close();
          vkM_msg������!");
    	}, 	n');
      }
    }).o;
  } /	d e
  // �������� �������
  function del(id) {
    var dialog = $("#	ce_	us_dialog")._dialog({
    	dth	,
      head:'�������	���
      content:"<B>����	���	�����</B><BR>'"	("#tabl	+ id + " .name:first").html() + "'",
      butSubmit:'�������',
     	mit:function () {
        dialog.process();
        $	t("/superadmin/d	e/status/AjaxDeviceStatusDel.p	 + G.values, {id:id}, function (res) {
          dialog.close();
          vkMsgOk("�������!");
   	   $("#table_" + id).remov
        }, 'json');
     	   }).o;
  } // end edit
}); // end click





