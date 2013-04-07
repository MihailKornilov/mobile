G.reminder = { data:[] }

// �����������
function reminderSpisok() {
  var info_hide = "<A>�������� ����������</A>";
  var info_show = "<B>�������</B> - ��� �� ����������� " +
    "���������� ��� ������������ � ����� �������� ��� ��������, " +
    "��������, ������ ��������, " +
    "����������� ������������� ������� ����������, " +
    "���������� ����� � ��.<BR><BR>" +

  "<B>Ƹ����</B> ������ ���������� �������, ������� ������� ������� � ��������� 2 ���, �� ���� ������� � ������. " +
    "�� ���������� ������ ������������ � ������� �������� ������� '������' � ������� '�������' � �������.<BR>" +
    "����� - �������, ��������� ���������� ����� ���� ����.<BR>" +
    "������ - �������, ����� - ��������, � <B>�������</B> - ������������ �������.<BR><BR>" +

    "<B>����� �����</B> ��� �������� ������ ������� ����� �������� ��������� ��� ����������. " +
    "��� ��������, � �������, ����� ����� ����������� � ������ ��� \"<I>���������</I>\" �� � ��� �� �������. " +
    "����� ������ \"<I>��������� � �������� ��������� �����������</I>\".<BR><BR>" +

    "��� ���������� �������� ��� ��������� ����������� ����� �������� � ���������. " +
    "��������� ������ ��������� <B>�������</B> �������� ������� �� ������ ���� ��� ������� ��� ������. " +
    "����������� � ������������ ������� �� ����������. <BR><BR>" +
    
    "��� ��������� ������� <B>������</B> ������� ����� ����� ������ ��� ������.<BR><BR>" +
     
    "�� ������ '�������' ��������� ��������������� ������ ���� �������� ��� ��������.<BR><BR>" +
    "<A>C�����.</A>";

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

  var html = "<DIV class=findHead>��������� �������</DIV>" +
    "<INPUT type=hidden id=remind_type value=1>" +
    "<INPUT type=hidden id=remind_private>";
  $("#podmenu").html(html);

  $("#remind_type").vkRadio({
    top:6,
    light:1,
    spisok:[
      {uid:1, title:'��������'},
      {uid:2, title:'���������'},
      {uid:0, title:'��������'}
    ],
    func:function (id) { G.spisok.print({status:id}); }
  });

  $("#remind_private").myCheck({title:'������', func:function (id) { G.spisok.print({private:id}); }});

  G.spisok.unit = function (sp) {
    var leave = '';
    if (sp.day_leave < 0) { leave = "���������� " + (sp.day_leave * -1) + G.end(sp.day_leave * -1, [' ����', ' ���', ' ����']); }
    else if (sp.day_leave > 2) { leave = "�����" + G.end(sp.day_leave, ['�� ', '��� ']) + sp.day_leave + G.end(sp.day_leave, [' ����', ' ���', ' ����']); }
    else {
      switch (sp.day_leave) {
        case 0: leave = '�������'; break;
        case 1: leave = '������'; break;
        case 2: leave = '�����������'; break;
      }
    }

    // ��������� ������
    var rem_cond;
    if (sp.status == 1) {
      rem_cond = "<EM>��������� " + (sp.day_leave == 0 ? '' : "�� ") + "</EM>" + 
        (sp.day_leave >= 0 && sp.day_leave < 3 ? leave : sp.day) + 
        (sp.day_leave > 2 || sp.day_leave < 0 ? "<SPAN>, " + leave + "</SPAN>" : '');
    }
    if (sp.status == 2) { rem_cond = "<EM>���������.</EM>"; }
    if (sp.status == 0) { rem_cond = "<EM>��������.</EM>"; }

    return "<DIV class=txt>" +
      (sp.private == 1 ? "<I>������.</I>" : '') +
      (sp.client_id ? "������ <A href='/index.php?" + G.values + "&my_page=remClientInfo&id=" + sp.client_id + "' class=client>" + sp.client_fio + "</A>: " : '') +
      (sp.zayav_id ? "������ <A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "' class=zayav>�" + sp.zayav_nomer + "</A>: " : '') +
      "<B>" + sp.txt + "</B>" + 
      "</DIV>" +
      "<DIV class=day>" + 
        "<DIV class=action>" + (sp.status == 1 ? "<A onclick=reminderAction(" + sp.num + ");>��������</A> :: " : '') + "<A onclick=reminderHistoryShow(" + sp.num + ");>�������</A></DIV>" +
        rem_cond +
        "<DIV class=hist>" + sp.history + "</DIV>" +
      "</DIV>";
  };

  G.spisok.create({
    url:"/remont/report/reminder/AjaxReminderGet.php",
    limit:20,
    view:$("#content #remind_spisok"),
    nofind:"������� �� �������.",
//    a:1,
    values:{ private:0, status:1 },
    callback:function (data) {
      G.reminder.data = data;
      for (var n = 0; n < data.length; n++) {
        // ������� � ������
        var sp = data[n],
              unit = '#EED',
              txt = '#FFC',
              day = '#FFFFF4',
              em = '#884';

        // ���� ����� 1 ���
        if (sp.day_leave > 1) { unit = '#DDE'; txt = '#DDF'; day = '#F7F7FF'; em = '#884'; }

        // ����������
        if (sp.day_leave < 0) { unit = '#EDD'; txt = '#FCC'; day = '#FFF7F7'; em = '#844'; }

        // ��������
        if (sp.status == 0) { unit = '#DDD'; txt = '#DDD'; day = '#F7F7F7'; em = '#444'; }

        // ���������
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







// ���������� ������ �����������
function reminderAdd(e) {
  e.stopPropagation();
  var html = "<TABLE cellpadding=0 cellspacing=0 class=remind_add_tab>" +
    "<TR><TD class=tdAbout>����������:<TD><INPUT type=hidden id=destination>" +
    "<TR><TD class=tdAbout id=target_name><TD id=target>" +
    "</TABLE>" +

    "<TABLE cellpadding=0 cellspacing=0 class=remind_add_tab id=tab_content>" +
      "<TR><TD class=tdAbout>�������:<TD><TEXTAREA id=txt></TEXTAREA>" +
      "<TR><TD class=tdAbout>������� ���� ����������:<TD><INPUT type=hidden id=data>" +
      "<TR><TD class=tdAbout>������:<TD><INPUT type=hidden id=private>" +
    "</TABLE>";
  var dialog = $("#report_dialog").vkDialog({
    top:30,
    width:480,
    head:"���������� ������ �������",
    content:html,
    butSubmit:"��������",
    submit:submit
  }).o;

  var dest = $("#destination").vkSel({
    width:150,
    title0:'�� �������',
    spisok:[{uid:1,title:'������'},{uid:2,title:'������'},{uid:3,title:'������������ �������'}],
    func:function (id) {
      $("#target").html('');
      $("#target_name").html('');
      $("#tab_content #txt").val('');
      $("#tab_content").css('display', id > 0 ? 'block' : 'none');
      // ���� ������ ������, ����������� ������ � ���������
      if (id == 1) {
        $("#target_name").html("������:");
        $("#target").html("<DIV id=client_id></DIV>");
        $("#client_id").clientSel();
      }
      // ���� ������� ������
      if (id == 2) {
        $("#target_name").html("����� ������:");
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
                $("#img").html('������ �� �������.');
              }
            });
          } else {
            $("#img").html('������������ ����');
          }
        });
      }
    }
  }).o;

  $("#tab_content #txt").autosize();

  $("#tab_content #data").vkCalendar();

  $("#tab_content #private").myCheck();
  $("#tab_content #check_private").vkHint({msg:"������� �������<BR>������ ������ ��.", top:-71, left:-11, indent:'left', delayShow:1000});

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
    if (dest.val() == 0) { msg = "�� ������� ����������."; }
    else if ($("#client_id").length > 0 && send.client_id == 0) { msg = "�� ������ ������."; }
    else if ($("#zayav_id").length > 0 && send.zayav_id == 0) { msg = "�� ������ ����� ������."; }
    else if (!send.txt) { msg = "�� ������� ���������� �����������."; }
    else {
      dialog.process();
      $.post("/remont/report/reminder/AjaxReminderAdd.php?" + G.values, send, function (res) {
        dialog.close();
        vkMsgOk("����� ������� ������� ���������.");
        reminderSpisok();
      }, 'html');
    }
    if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:150}); }
  }
} // end reminderAdd









function reminderAction(num) {
  var sp = G.reminder.data[num];
  var html = "<TABLE cellpadding=0 cellspacing=0 class=remind_action_tab>" +
      "<TR><TD class=tdAbout>" + (sp.client_id ? "������:" : '') + (sp.zayav_id ? "������:" : '') + "<TD>" +
        (sp.client_id ? "<A href='/index.php?" + G.values + "&my_page=remClientInfo&id=" + sp.client_id + "' class=client>" + sp.client_fio + "</A>" : '') +
        (sp.zayav_id ? "<A href='/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + sp.zayav_id + "' class=zayav>�" + sp.zayav_nomer + "</A>" : '') +
      "<TR><TD class=tdAbout>�������:<TD><B>" + sp.txt + "</B>" +
      "<TR><TD class=tdAbout>���:<TD><A href='http://vk.com/id" + sp.viewer_id + "'>" + G.vkusers[sp.viewer_id] + "</A>, " + sp.dtime +
      "<TR><TD class=tdAbout>��������:<TD><INPUT type=hidden id=action>" +
      "</TABLE>" +

      "<TABLE cellpadding=0 cellspacing=0 class=remind_action_tab id=new_action>" +
        "<TR><TD class=tdAbout id=new_about><TD id=new_title>" +
        "<TR><TD class=tdAbout id=new_comm><TD><TEXTAREA id=comment></TEXTAREA>" +
      "</TABLE>";
  var dialog = $("#report_dialog").vkDialog({
    top:30,
    width:400,
    head:"����� �������� ��� �����������",
    content:html,
    butSubmit:"���������",
    submit:submit
  }).o;

  $("#action").vkRadio({
    top:6,
    spisok:[
      {uid:1, title:'��������� �� ������ ����'},
      {uid:2, title:'������� ���������'},
      {uid:3, title:'��������'}
    ],
    func:function (id) {
      $("#new_action").show();
      $("#comment").val('');
      $("#new_about").html('');
      $("#new_title").html('');
      if (id == 1) {
        $("#new_about").html("����:");
        $("#new_title").html("<INPUT type=hidden id=data>");
        $("#new_comm").html("�������:");
        $("#new_action #data").vkCalendar();
      }
      if (id == 2) { $("#new_comm").html("�����������:"); }
      if (id == 3) { $("#new_comm").html("�������:"); }
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
    if (send.action == 2) { send.status = 2; } // ���������
    if (send.action == 3) { send.status = 0; } // ��������

    var msg;
    if (!send.action) { msg = "������� ����� ��������."; }
    else if ((send.action == 1 || send.action == 3) && !send.history) { msg = "�� ������� �������."; }
    else if (send.action == 1 && send.day == sp.day_real) { msg = "�������� ����� ����."; }
    else {
      dialog.process();
      $.post("/remont/report/reminder/AjaxReminderEdit.php?" + G.values, send, function (res) {
        dialog.close();
        vkMsgOk("������� ���������������.");
        reminderSpisok();
      }, 'json');
    }
    if (msg) { $("#report_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:115}); }
  }
}





