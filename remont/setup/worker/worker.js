$("#find_button").click(findUser);
$("#worker_id").keyup(function (e) { if (e.keyCode == 13) { findUser(); } });
workersPrint();

function findUser() {
  var user = $("#worker_id").val();
  if (user) {
    var host = user.split('http://vk.com/')[1];
    if (host) { user = host; }
    $("#process").html("<IMG src=/img/upload.gif>");
    VK.api('users.get', {uids:user, fields:"uid, first_name, last_name, photo, sex, country, city"}, function (data) {
      $("#process").html('');
      if (data.response) {
        var u = data.response[0];
        var html = "<TABLE cellpadding=0 cellspacing=8 class=w_user><TR>" +
          "<TD class=photo><IMG src=" + u.photo + ">" +
          "<TD class=name>" + u.first_name + " " + u.last_name +
          "<DIV class=but><DIV class=vkButton><BUTTON>Добавить</BUTTON></DIV><DIV class=vkCancel><BUTTON>Отмена</BUTTON></DIV></DIV>" +
        "</TABLE>";
        $("#worker_finded").html(html);
        frameBodyHeightSet();
        $("#worker_finded .vkCancel:first").click(function () { $("#worker_finded").html(''); frameBodyHeightSet(); });

        $("#worker_finded .vkButton:first BUTTON:first").click(function () {
          $(this).butProcess();
          if (u.country) {
            VK.api('places.getCountryById', {cids:u.country}, function (data) {
              u.country_name = data.response[0].name;
              if (u.city != undefined && u.city > 0) {
                VK.api('places.getCityById', {cids:u.city}, function (data) { u.city_name = data.response[0].name; userSave(u); });
              } else { userSave(u); }
            });
          } else { userSave(u); }
        });

      }  else { $("#worker_finded").html(''); frameBodyHeightSet(); }
      // добавление сотрудника к мастерской
      function userSave(u) {
        if (!u.country) { u.country = 0; }
        if (!u.country_name) { u.country_name = ''; }
        if (!u.city) { u.city = 0; }
        if (!u.city_name) { u.city_name = ''; }
        u.viewer_id = u.uid;
        $.post("/remont/setup/worker/AjaxWorkerAdd.php?" + G.values, u, function (res) {
          if (res.res == 'no') {
            $("#worker_finded .but:first").html("Невозможно добавить этого сотрудника, так как он " + (res.ws_id == G.vku.ws_id ? "уже состоит в этой" : "состоит в другой") + " мастерской.");
          } else {
            $("#worker_finded").html('');
            $("#worker_id").val('');
            u.name = u.first_name + " " + u.last_name;
            workers.push(u);
            workersPrint();
            vkMsgOk("Новый сторудник успешно добавлен.");
          }
        }, 'json');    
      }
    });
  }
}




// Вывод списка сотрудников
function workersPrint() {
  var html = '';
  for (var n = 0; n < workers.length; n++) {
    var u = workers[n];
    html += "<TABLE cellpadding=0 cellspacing=8 class=w_user><TR>" +
      "<TD class=photo><IMG src=" + u.photo + ">" +
      "<TD class=name>" + 
      (ws_admin == G.vku.viewer_id ? "<DIV class=img_del val=" + u.viewer_id + "></DIV>" : '') +
      u.name +
      (u.country_name ? "<DIV class=place>"+ u.country_name + (u.city_name ? ", " + u.city_name : '') + "</DIV>" : '') +
      (u.admin == 1 ?
        "<DIV class=admin>Админ" + 
        (ws_admin != u.viewer_id && ws_admin == G.vku.viewer_id ? " <A val=noadmin_" + u.viewer_id + ">отменить</A>" : '') +
        "</DIV>" :
        (ws_admin == G.vku.viewer_id ? "<DIV class=admin><A val=admin_" + u.viewer_id + ">Назначить администратором</A></DIV>" : '')) +
    "</TABLE>";
  }
  $("#workers").html(html);
  // удаление из мастерской
  $("#workers .img_del").click(function (e) {
    var viewer_id = $(e.target).attr('val');
    $.post("/remont/setup/worker/AjaxWorkerDel.php?" + G.values, {viewer_id:viewer_id}, function () {
      for (var n = 0; n < workers.length; n++) {
        var u = workers[n];
        if (u.viewer_id == viewer_id) {
          workers.splice(n, 1);
          workersPrint();
          break;
        }
      }
    });
  });
  // назначение или удаление из администраторов
  $("#workers .admin A").click(function (e) {
    var arr = $(e.target).attr('val').split('_');
    var admin = arr[0] == 'admin' ? 1 : 0;
    $.post("/remont/setup/worker/AjaxAdminSet.php?" + G.values, {viewer_id:arr[1], admin:admin}, function () {
      for (var n = 0; n < workers.length; n++) {
        var u = workers[n];
        if (u.viewer_id == arr[1]) {
          u.admin = admin;
          workersPrint();
          break;
        }
      }
    });
  });
  frameBodyHeightSet();
}






