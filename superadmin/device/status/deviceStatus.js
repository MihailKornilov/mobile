deviceStatusPrint();

function deviceStatusPrint() {
  var html ="";
  for (var n = 0; n < G.device_status_spisok.length; n++) {
    var sp = G.device_status_spisok[n];
    html += "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok id=table_" + sp.uid + ">";
    html += "<TR><TD class=uid>" + sp.uid;
    html += "<TD class=name>" + sp.title;
    html += "<TD class=zayav_count>" + (G.zayav_count[n] > 0 ? G.zayav_count[n] : '');
    html += "<TD class=edit>";
    if (sp.uid > 0) { html += "<DIV class=img_edit val=edit_" + sp.uid + "></DIV>"; }
    if (sp.uid > 0 && G.zayav_count[n] == 0) { html += "<DIV class=img_del val=del_" + sp.uid + "></DIV>"; }
    html += "</TABLE>";
  }
  $("#drag").html(html);
} // end deviceStatusPrint


$("#drag").sortable({
  axis:'y',
  update:function () {
    var uids = $(this).find(".uid");
    if (uids.length > 1) {
      var arr = [];
      for(var n = 0; n < uids.length; n++) { arr.push(uids.eq(n).html()); }
      $.getJSON("/superadmin/device/status/AjaxDeviceStatusSort.php?" + VALUES + "&val=" + arr.join(','), function () {  });
    }
  }
});


$("#setup_device_status").click(function (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    switch (val[0]) {
    case 'edit': edit(val[1]); break;
    case 'add': add(); break;
    case 'del': del(val[1]); break;
    }
  }

  // �������������� ������������
  function edit(id) {
    var dialog = $("#device_status_dialog")._dialog({
      width:240,
      head:'��������� ������������',
      content:"������������: <INPUT type=text id=status_name value='" + $("#table_" + id + " .name:first").html() + "'>",
      butSubmit:'���������',
      submit:function () {
        var send = {
          id:id,
          name:$("#status_name").val()
        };
        dialog.process();
        $.post("/superadmin/device/status/AjaxDeviceStatusEdit.php?" + G.VALUESsend, function (res) {
          dialog.close();
          _msg("��������!");
          $("#table_" + id + " .name:first").html(send.name)
        }, 'json');
      }
    }).o;
  } // end edit

  // �������� ������ �������
  function add() {
    var dialog = $("#device_status_dialog")._dialog({
      width:240,
      head:'�������� ������ �������',
      content:"������������: <INPUT type=text id=status_name>",
      submit:function () {
        var send = {
          name:$("#status_name").val()
        };
        dialog.process();
        $.post("/superadmin/device/status/AjaxDeviceStatusAdd.php?" + G.vaVALUESnd, function (res) {
          G.zayav_count.push(0);
          G.device_status_spisok.push({uid:res.id, title:send.name});
          deviceStatusPrint();
          dialog.close();
          vkM_msg������!");
        }, 'json');
      }
    }).o;
  } // end edit

  // �������� �������
  function del(id) {
    var dialog = $("#device_status_dialog")._dialog({
      width:240,
      head:'�������� �������',
      content:"<B>����������� ��������</B><BR>'" + $("#table_" + id + " .name:first").html() + "'",
      butSubmit:'�������',
      submit:function () {
        dialog.process();
        $.post("/superadmin/device/status/AjaxDeviceStatusDel.php?" + G.values, {id:id}, function (res) {
          dialog.close();
          vkMsgOk("�������!");
          $("#table_" + id).remove();
        }, 'json');
      }
    }).o;
  } // end edit
}); // end click





