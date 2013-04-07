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
      $.getJSON("/superadmin/device/status/AjaxDeviceStatusSort.php?" + G.values + "&val=" + arr.join(','), function () {  });
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

  // редактирование наименования
  function edit(id) {
    var dialog = $("#device_status_dialog").vkDialog({
      width:240,
      head:'Изменение наименования',
      content:"Наименование: <INPUT type=text id=status_name value='" + $("#table_" + id + " .name:first").html() + "'>",
      butSubmit:'Сохранить',
      submit:function () {
        var send = {
          id:id,
          name:$("#status_name").val()
        };
        dialog.process();
        $.post("/superadmin/device/status/AjaxDeviceStatusEdit.php?" + G.values, send, function (res) {
          dialog.close();
          vkMsgOk("Изменено!");
          $("#table_" + id + " .name:first").html(send.name)
        }, 'json');
      }
    }).o;
  } // end edit

  // Внесение нового статуса
  function add() {
    var dialog = $("#device_status_dialog").vkDialog({
      width:240,
      head:'Внесение нового статуса',
      content:"Наименование: <INPUT type=text id=status_name>",
      submit:function () {
        var send = {
          name:$("#status_name").val()
        };
        dialog.process();
        $.post("/superadmin/device/status/AjaxDeviceStatusAdd.php?" + G.values, send, function (res) {
          G.zayav_count.push(0);
          G.device_status_spisok.push({uid:res.id, title:send.name});
          deviceStatusPrint();
          dialog.close();
          vkMsgOk("Внесено!");
        }, 'json');
      }
    }).o;
  } // end edit

  // удаление статуса
  function del(id) {
    var dialog = $("#device_status_dialog").vkDialog({
      width:240,
      head:'Удаление статуса',
      content:"<B>Подтвердите удаление</B><BR>'" + $("#table_" + id + " .name:first").html() + "'",
      butSubmit:'Удалить',
      submit:function () {
        dialog.process();
        $.post("/superadmin/device/status/AjaxDeviceStatusDel.php?" + G.values, {id:id}, function (res) {
          dialog.close();
          vkMsgOk("Удалено!");
          $("#table_" + id).remove();
        }, 'json');
      }
    }).o;
  } // end edit
}); // end click





