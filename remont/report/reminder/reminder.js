G.reminder = { data:[] }

// Напоминания
function reminderSpisok() {
  var info_hide = "<A>Показать информацию</A>";
  var info_show = "<B>Задания</B> - они же напоминания " +
    "необходимы для отслеживания и учёта действий над заявками, " +
    "обещаний, данных клиентам, " +
    "организации периодических звонков должниками, " +
    "постановки задач и тп.<BR><BR>" +

  "<B>Жёлтым</B> цветом помечаются задания, которые требуют решения в ближайшие 2 дня, то есть сегодня и завтра. " +
    "Их количество всегда отображается в скобках напротив вкладки 'Отчёты' и раздела 'Задания' в отчётах.<BR>" +
    "Синим - задания, ожидающие выполнения более двух дней.<BR>" +
    "Зелёные - готовые, серые - отменены, и <B>красные</B> - просроченные задания.<BR><BR>" +

    "<B>Очень важно</B> при внесении нового задания более подробно указывать его содержание. " +
    "Это означает, к примеру, такой текст напоминания к заявке как \"<I>Позвонить</I>\" ни о чём не говорит. " +
    "Лучше писать \"<I>Позвонить и сообщить результат диагностики</I>\".<BR><BR>" +

    "Все дальнейшие действия над заданиями обязательно нужно отмечать в программе. " +
    "Требуется всегда указывать <B>причину</B> переноса задания на другой день или причину его отмены. " +
    "Комментарий к выполненному заданию не обязателен. <BR><BR>" +
    
    "При установке галочки <B>Личное</B> задание будет видно только его автору.<BR><BR>" +
     
    "По ссылке 'История' выводится хронологический список всех действий над заданием.<BR><BR>" +
    "<A>Cкрыть.</A>";

  var info, info_cookie = getCookie('reminderInfo');
  $("#content").html("<DIV class=remind_info>" + (info_cookie == 'yes' ? info_show : info_hide) + "</DIV><DIV id=remind_spisok></DIV>");
  var infoShow = function infoShow() {
    info_cookie = getCookie('reminderInfo');
    setCookie('reminderInfo', info_cookie == 'no' ? 'yes' : 'no');
    $("#content .remind_info").html(info_cookie != 'no' ? info_hide : info_show);
    $("#content .remind_info A").on('click', infoShow);
    frameBodyHeightSet();
  };
  $("#content .remind_info A").on('click', infoShow);

  var html = "<DIV class=findHead>Категории заданий</DIV>" +
    "<INPUT type=hidden id=remind_type value=1>" +
    "<INPUT type=hidden id=remind_private>";
  $("#podmenu").html(html);

  $("#remind_type").vkRadio({
    top:6,
    light:1,
    spisok:[
      {uid:1, title:'Активные'},
      {uid:2, title:'Выполнены'},
      {uid:0, title:'Отменены'}
    ],
    func:function (id) { G.spisok.print({status:id}); }
  });

  $("#remind_private").myCheck({title:'Личное', func:function (id) { G.spisok.print({private:id}); }});

  G.spisok.unit = function (sp) {
    var leave = '';
    if (sp.day_leave < 0) { leave = "просрочено " + (sp.day_leave * -1) + G.end(sp.day_leave * -1, [' день', ' дня', ' дней']); }
    else if (sp.day_leave > 2) { leave = "остал" + G.end(sp.day_leave, ['ся ', 'ось ']) + sp.day_leave + G.end(sp.day_leave, [' день', ' дня', ' дней']); }
    else {
      switch (sp.day_leave) {
        case 0: leave = 'сегодня'; break;
        case 1: leave = 'завтра'; break;
        case 2: leave = 'послезавтра'; break;
      }
    }

    // состояние задачи
    var rem_cond;
    if (sp.status == 1) {
      rem_cond = "<EM>Выполнить " + (sp.day_leave == 0 ? '' : "до ") + "</EM>" + 
        (sp.day_leave >= 0 && sp.day_leave < 3 ? leave : sp.day) + 
        (sp.day_leave > 2 || sp.day_leave < 0 ? "<SPAN>, " + leave + "</SPAN>" : '');
    }
    if (sp.status == 2) { rem_cond = "<EM>Выполнено.</EM>"; }
    if (sp.status == 0) { rem_cond = "<EM>Отменено.</EM>"; }

    return "<DIV class=txt>" +
      (sp.private == 1 ? "<I>Личное.</I>" : '') +
      (sp.client_id ? "Клиент <A href='/index.php?" + G.values + "&my_page=remClientInfo&id=" + sp.client_id + "' class=client>" + sp.client_fio + "</A>: " : '') +
      (sp.zayav_id ? "Заявка <A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "' class=zayav>№" + sp.zayav_nomer + "</A>: " : '') +
      "<B>" + sp.txt + "</B>" + 
      "</DIV>" +
      "<DIV class=day>" + 
        "<DIV class=action>" + (sp.status == 1 ? "<A onclick=reminderAction(" + sp.num + ");>Действие</A> :: " : '') + "<A onclick=reminderHistoryShow(" + sp.num + ");>История</A></DIV>" +
        rem_cond +
        "<DIV class=hist>" + sp.history + "</DIV>" +
      "</DIV>";
  };

  G.spisok.create({
    url:"/remont/report/reminder/AjaxReminderGet.php",
    limit:20,
    view:$("#content #remind_spisok"),
    nofind:"Заданий не найдено.",
//    a:1,
    values:{ private:0, status:1 },
    callback:function (data) {
      G.reminder.data = data;
      for (var n = 0; n < data.length; n++) {
        // сегодня и завтра
        var sp = data[n],
              unit = '#EED',
              txt = '#FFC',
              day = '#FFFFF4',
              em = '#884';

        // если более 1 дня
        if (sp.day_leave > 1) { unit = '#DDE'; txt = '#DDF'; day = '#F7F7FF'; em = '#884'; }

        // просрочено
        if (sp.day_leave < 0) { unit = '#EDD'; txt = '#FCC'; day = '#FFF7F7'; em = '#844'; }

        // отменено
        if (sp.status == 0) { unit = '#DDD'; txt = '#DDD'; day = '#F7F7F7'; em = '#444'; }

        // выполнено
        if (sp.status == 2) { unit = '#DED'; txt = '#CFC'; day = '#F7FFF7'; em = '#484'; }

        $("#unit_" + sp.id)
          .css('border', unit + " solid 1px")
          .find('.txt:first').css('background-color', txt).end()
          .find('.day').css('background-color', day)
          .find('EM:first').css('color', em).end().end()
          .on('mouseenter', function () { $(this).find('.action:first').show(); })
          .on('mouseleave', function () { $(this).find('.action:first').hide(); });
      }
    }
  });
} // end reminderSpisok








function reminderHistoryShow(num) {
  var sp = G.reminder.data[num];
  if (sp.down == 1) {
    $("#unit_" + sp.id).find(".hist:first").slideUp(200, frameBodyHeightSet);
    sp.down = 0;
  } else {
    $("#unit_" + sp.id).find(".hist:first").slideDown(200, frameBodyHeightSet);
    sp.down = 1;
  }
}







// Добавление нового напоминания
function reminderAdd(e) {
  e.stopPropagation();
  var html = "<TABLE cellpadding=0 cellspacing=0 class=remind_add_tab>" +
    "<TR><TD class=tdAbout>Назначение:<TD><INPUT type=hidden id=destination>" +
    "<TR><TD class=tdAbout id=target_name><TD id=target>" +
    "</TABLE>" +

    "<TABLE cellpadding=0 cellspacing=0 class=remind_add_tab id=tab_content>" +
      "<TR><TD class=tdAbout>Задание:<TD><TEXTAREA id=txt></TEXTAREA>" +
      "<TR><TD class=tdAbout>Крайний день выполнения:<TD><INPUT type=hidden id=data>" +
      "<TR><TD class=tdAbout>Личное:<TD><INPUT type=hidden id=private>" +
    "</TABLE>";
  var dialog = $("#report_dialog").vkDialog({
    top:30,
    width:480,
    head:"Добавление нового задания",
    content:html,
    butSubmit:"Добавить",
    submit:submit
  }).o;

  var dest = $("#destination").vkSel({
    width:150,
    title0:'Не указано',
    spisok:[{uid:1,title:'Клиент'},{uid:2,title:'Заявка'},{uid:3,title:'Произвольное задание'}],
    func:function (id) {
      $("#target").html('');
      $("#target_name").html('');
      $("#tab_content #txt").val('');
      $("#tab_content").css('display', id > 0 ? 'block' : 'none');
      // если выбран клиент, вставляется селект с клиентами
      if (id == 1) {
        $("#target_name").html("Клиент:");
        $("#target").html("<DIV id=client_id></DIV>");
        $("#client_id").clientSel();
      }
      // если выбрана заявка
      if (id == 2) {
        $("#target_name").html("Номер заявки:");
        $("#target").html("<INPUT type=text id=zayav_nomer><INPUT type=hidden id=zayav_id value=0><SPAN id=img></SPAN><DIV id=zayav_find></DIV>");
        $("#zayav_nomer").focus().on('keyup', function () {
          $("#zayav_id").val(0);
          $("#zayav_find").html('');
          var val = $(this).val();
          if (/[0-9]$/.test(val)) {
            $("#img").imgUp();
            $.getJSON("/remont/zp/view/AjaxZayavFind.php?" + G.values + "&nomer=" + val, function (res) {
              if(res.id > 0) {
                html = "<TABLE cellpadding=0 cellspacing=5><TR>" +
                  "<TD><A href='index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + res.id + "'><IMG src='" + res.img + "' height=40></A>" +
                  "<TD><A href='index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + res.id + "'>" + G.category_ass[res.category] + "<BR>" + G.device_ass[res.device_id] + "<BR>" + G.vendor_ass[res.vendor_id] + " " + G.model_ass[res.model_id] + "</A>" +
                  "</TABLE>";
                $("#zayav_id").val(res.id);
                $("#zayav_find").html(html);
                $("#img").html('');
              } else {
                $("#img").html('Заявка не найдена.');
              }
            });
          } else {
            $("#img").html('некорректный ввод');
          }
        });
      }
    }
  }).o;

  $("#tab_content #txt").autosize();

  $("#tab_content #data").vkCalendar();

  $("#tab_content #private").myCheck();
  $("#tab_content #check_private").vkHint({msg:"Задание сможете<BR>видеть только Вы.", top:-71, left:-11, indent:'left', delayShow:1000});

  function submit() {
    var client_id = 0;  if (dest.val() == 1) { client_id = $("#client_id").val(); }
    var zayav_id = 0; if (dest.val() == 2) { zayav_id = $("#zayav_id").val(); }
    var send = {
      client_id:client_id,
      zayav_id:zayav_id,
      txt:$("#tab_content #txt").val(),
      day:$("#tab_content #data").val(),
      private:$("#tab_content #private").val()
    };
    var msg;
    if (dest.val() == 0) { msg = "Не выбрано назначение."; }
    else if ($("#client_id").length > 0 && send.client_id == 0) { msg = "Не выбран клиент."; }
    else if ($("#zayav_id").length > 0 && send.zayav_id == 0) { msg = "Не указан номер заявки."; }
    else if (!send.txt) { msg = "Не указано содержание напоминания."; }
    else {
      dialog.process();
      $.post("/remont/report/reminder/AjaxReminderAdd.php?" + G.values, send, function (res) {
        dialog.close();
        vkMsgOk("Новое задание успешно добавлено.");
        reminderSpisok();
      }, 'html');
    }
    if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:150}); }
  }
} // end reminderAdd









function reminderAction(num) {
  var sp = G.reminder.data[num];
  var html = "<TABLE cellpadding=0 cellspacing=0 class=remind_action_tab>" +
      "<TR><TD class=tdAbout>" + (sp.client_id ? "Клиент:" : '') + (sp.zayav_id ? "Заявка:" : '') + "<TD>" +
        (sp.client_id ? "<A href='/index.php?" + G.values + "&my_page=remClientInfo&id=" + sp.client_id + "' class=client>" + sp.client_fio + "</A>" : '') +
        (sp.zayav_id ? "<A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "' class=zayav>№" + sp.zayav_nomer + "</A>" : '') +
      "<TR><TD class=tdAbout>Задание:<TD><B>" + sp.txt + "</B>" +
      "<TR><TD class=tdAbout>Внёс:<TD><A href='http://vk.com/id" + sp.viewer_id + "'>" + G.vkusers[sp.viewer_id] + "</A>, " + sp.dtime +
      "<TR><TD class=tdAbout>Действие:<TD><INPUT type=hidden id=action>" +
      "</TABLE>" +

      "<TABLE cellpadding=0 cellspacing=0 class=remind_action_tab id=new_action>" +
        "<TR><TD class=tdAbout id=new_about><TD id=new_title>" +
        "<TR><TD class=tdAbout id=new_comm><TD><TEXTAREA id=comment></TEXTAREA>" +
      "</TABLE>";
  var dialog = $("#report_dialog").vkDialog({
    top:30,
    width:400,
    head:"Новое действие для напоминания",
    content:html,
    butSubmit:"Применить",
    submit:submit
  }).o;

  $("#action").vkRadio({
    top:6,
    spisok:[
      {uid:1, title:'Перенести на другую дату'},
      {uid:2, title:'Задание выполнено'},
      {uid:3, title:'Отменить'}
    ],
    func:function (id) {
      $("#new_action").show();
      $("#comment").val('');
      $("#new_about").html('');
      $("#new_title").html('');
      if (id == 1) {
        $("#new_about").html("Дата:");
        $("#new_title").html("<INPUT type=hidden id=data>");
        $("#new_comm").html("Причина:");
        $("#new_action #data").vkCalendar();
      }
      if (id == 2) { $("#new_comm").html("Комментарий:"); }
      if (id == 3) { $("#new_comm").html("Причина:"); }
    }
  });

  $("#comment").autosize();

  function submit () {
    var send = {
      id:sp.id,
      action:$("#action").val(),
      day:sp.day_real,
      status:1,
      history:$("#comment").val()
    };
    if (send.action == 1) { send.day = $("#data").val(); }
    if (send.action == 2) { send.status = 2; } // выполнено
    if (send.action == 3) { send.status = 0; } // отменено

    var msg;
    if (!send.action) { msg = "Укажите новое действие."; }
    else if ((send.action == 1 || send.action == 3) && !send.history) { msg = "Не указана причина."; }
    else if (send.action == 1 && send.day == sp.day_real) { msg = "Выберите новую дату."; }
    else {
      dialog.process();
      $.post("/remont/report/reminder/AjaxReminderEdit.php?" + G.values, send, function (res) {
        dialog.close();
        vkMsgOk("Задание отредактировано.");
        reminderSpisok();
      }, 'json');
    }
    if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:115}); }
  }
}





