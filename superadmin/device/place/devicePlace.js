devicePlacePrint();

function devicePlacePrint() {
  var html ="";
  for (var n = 0; n < G.device_place_spisok.length; n++) {
    var sp = G.device_place_spisok[n];
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
} // end devicePlacePrint


$("#drag").sortable({
  axis:'y',
  update:function () {
    var uids = $(this).find(".uid");
    if (uids.length > 1) {
      var arr = [];
      for(var n = 0; n < uids.length; n++) { arr.push(uids.eq(n).html()); }
      $.getJSON("/superadmin/device/place/AjaxDevicePlaceSort.php?" + G.values + "&val=" + arr.join(','), function () {  });
    }
  }
});


$("#setup_device_place").click(function (e) {
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
    var dialog = $("#device_place_dialog").vkDialog({
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
        $.post("/superadmin/device/place/AjaxDevicePlaceEdit.php?" + G.values, send, function (res) {
          dialog.close();
          vkMsgOk("Изменено!");
          $("#table_" + id + " .name:first").html(send.name)
        }, 'json');
      }
    }).o;
  } // end edit

  // Внесение нового местонахождения
  function add() {
    var dialog = $("#device_place_dialog").vkDialog({
      width:300,
      head:'Внесение нового местонахождения',
      content:"Наименование: <INPUT type=text id=place_name>",
      submit:function () {
        var send = {
          name:$("#place_name").val()
        };
        dialog.process();
        $.post("/superadmin/device/place/AjaxDevicePlaceAdd.php?" + G.values, send, function (res) {
          G.zayav_count.push(0);
          G.device_place_spisok.push({uid:res.id, title:send.name});
          devicePlacePrint();
          dialog.close();
          vkMsgOk("Внесено!");
        }, 'json');
      }
    }).o;
  } // end edit

  // удаление местоположения
  function del(id) {
    var dialog = $("#device_place_dialog").vkDialog({
      width:240,
      head:'Удаление местоположения',
      content:"<B>Подтвердите удаление</B><BR>'" + $("#table_" + id + " .name:first").html() + "'",
      butSubmit:'Удалить',
      submit:function () {
        dialog.process();
        $.post("/superadmin/device/place/AjaxDevicePlaceDel.php?" + G.values, {id:id}, function (res) {
          dialog.close();
          vkMsgOk("Удалено!");
          $("#table_" + id).remove();
        }, 'json');
      }
    }).o;
  } // end edit
}); // end click





