// установка галочек ранее выбранным устройствам
for(var n = 0; n < G.device_mn_spisok.length; n++) {
  var sp = G.device_mn_spisok[n];
  if (G.ws.devs.indexOf(sp.uid) >= 0) { sp.value = 1; }
}



$("#devs").myCheck({
  spisok:G.device_mn_spisok,
  br:1,
  top:5,
  func:function (obj) {
    // удаление информации об изменении, если было
    obj.target.next(".check_info").remove();
    var inp = $("#devs INPUT");
    var checked = [];
    for(var n = 0; n < inp.length; n++) {
      var sp = inp.eq(n);
      if (sp.val() == 1) { checked.push(sp.attr('id').split('_')[1]); }
    }
    checked = checked.join(',');
    if (!checked) {
      obj.target
      .after("<DIV class=check_info><EM style=color:#F44;>Ќе сохранено!<BR>Ќеобходимо выбрать<BR>минимум одну категорию</EM></DIV>")
      .next().delay(2000).fadeOut(1500, function () { $(this).remove() });
    } else {
      $.post("/remont/setup/ws/AjaxDevsSave.php?" + G.values, {devs:checked}, function (res) {
        obj.target
          .after("<DIV class=check_info><EM>изменени€ сохранены</EM></DIV>")
          .next().delay(2000).fadeOut(1500, function () { $(this).remove() });
      }, 'json');
    }
  }
});





// сохранение названи€ мастерской
$("#org_name").blur(function () {
  var info = $(this).next();
  var val = $(this).val();
  if (!val) {
    info.html("<EM style=color:#E22>введите название</EM>");
  } else if (G.ws.org_name != val) {
    info.html("<IMG src=/img/upload.gif>");
    $.post("/remont/setup/ws/AjaxOrgNameSave.php?" + G.values, {org_name:val}, function (res) {
      info.html("изменени€ сохранены").delay(2000).fadeOut(1500, function () { info.html('').show() });
    }, 'json');
  }
});




// удаление мастерской
$("#ws_del").click(function () {
  dialog = $("#ws_dialog").vkDialog({
    top:150,
    width:350,
    head:"”даление мастерской",
    content:"¬ы действительно хотите<BR>удалить мастерскую и все данные?",
    butSubmit:'&nbsp;&nbsp;&nbsp;&nbsp;ƒа&nbsp;&nbsp;&nbsp;&nbsp;',
    submit:function () {
      dialog.process();
      $.post("/remont/setup/ws/AjaxWSdel.php?" + G.values, {}, function (res) {
        location.href = "/index.php?" + G.values + "&my_page=catalog";
      }, 'json');
    }
  }).o;
});




