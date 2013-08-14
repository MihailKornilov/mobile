// ���������� ������ � ������ � ���������
G.zayav.update = function () {
  $("#zayav_category").html(G.category_ass[G.zayav.category]);

  var model = (G.zayav.vendor > 0 ? G.vendor_ass[G.zayav.vendor] : '') + " " + (G.zayav.model > 0 ? G.model_ass[G.zayav.model] : '');

  var dev = G.device_ass[G.zayav.device];
  dev += " <A href='/index.php?" + G.values+ "&my_page=remDeviceView&id=" + G.zayav.model + "'><B>" + model + "</B></A>";
  $("#zayav_device").html(dev);
  $("#info_device").html(dev.replace("<A href", "<BR><A href"));

  $("#zayav_client").html("<A href='/index.php?" + G.values+ "&my_page=remClientInfo&id=" + G.zayav.client_id + "'>" + G.zayav.client_fio + "</a>");

  $("#zayav_status").html(G.status_ass[G.zayav.status]).css('background-color','#' + G.status_color_ass[G.zayav.status]);
  $("#zayav_status_dtime").html(G.zayav.status_dtime);

  $("#tr_imei").attr('class', G.zayav.imei ? '' : 'none');
  $("#info_imei").html(G.zayav.imei);

  $("#tr_serial").attr('class', G.zayav.serial ? '' : 'none');
  $("#info_serial").html(G.zayav.serial);

  $("#tr_color").attr('class', G.zayav.color > 0 ? '' : 'none');
  $("#info_color").html(G.color_ass[G.zayav.color]);

  $("#info_place").html(G.zayav.place > 0 ? G.device_place_ass[G.zayav.place] : G.zayav.place_other);
  $("#info_status").html(G.device_status_ass[G.zayav.device_status]);

  if (G.zayav.zp.length > 0) {
    var html = '';
    for (var n = 0; n < G.zayav.zp.length; n++) {
      var sp = G.zayav.zp[n];
      var dop = [];
      if (sp.name_dop) { dop.push(sp.name_dop); }
      if (sp.color_id > 0) { dop.push("����: " + G.color_ass[sp.color_id]); }
      html += "<DIV class=unit>";
      html += "<A href='/index.php?" + G.values + "&my_page=remZp&id=" + sp.id + "'><B>" + G.zp_name_ass[sp.name_id] + "</B> <EM>" + model + "</EM></A>";
      if (dop.length > 0) { html += "<DIV class=dop>" + dop.join(', ') + "</DIV>"; }
      html += "<DIV class=nal>" + (sp.zakaz > 0 ? "<TT val=zakazok_>��������!</TT>" : "<A val=zakaz_" + n + ">��������</A>") + "<B>" + (sp.avai > 0 ? "<A val=set_" + n + ">����������</A> �������: " + sp.avai : '') + "</B></DIV>";
      html += "</DIV>";
    }
    $("#zayav_zp")
      .html(html)
      .find(".unit").bind({
        mouseenter:function () { $(this).find(".nal A").css('visibility', 'visible'); },
        mouseleave:function () { $(this).find(".nal A").css('visibility', 'hidden'); }
      });
  } else {
    $("#zayav_zp").html("<DIV class=findEmpty>��� " + model + " ��� ���������.</DIV>");
  }

  if (G.zayav.foto.length > 0) { $("#foto").fotoSet({foto:G.zayav.foto[0], max_x:200, max_y:320, val:'zayavFoto_'}); }







  // ������ �����������
  var html = '';
  for (var n = 0; n < G.zayav.reminder.length; n++) {
    var sp = G.zayav.reminder[n];
    sp.num = n;
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
    
    // ������� - ������
    var unit = '#EED', txt = '#FFC', day = '#FFFFF4', em = '#884';
    // ����� 1 ���
    if (sp.day_leave > 1) { unit = '#DDE'; txt = '#DDF'; day = '#F7F7FF'; em = '#884'; }
    // ����������
    if (sp.day_leave < 0) { unit = '#EDD'; txt = '#FCC'; day = '#FFF7F7'; em = '#844'; }

    html += "<DIV class=unit id=unit_" + sp.id +" style='border:" + unit + " solid 1px;'>" +
      "<DIV class=txt style=background-color:" + txt + ";>" + (sp.private == 1 ? "<I>������.</I>" : '') + "<B>" + sp.txt + "</B>" + "</DIV>" +
      "<DIV class=day style=background-color:" + day + ";>" + 
        "<DIV class=action><A onclick=reminderAction(" + sp.num + ");>��������</A> :: <A onclick=reminderHistoryShow(" + sp.num + ");>�������</A></DIV>" +
        "<EM style=color:" + em + ";>��������� " + (sp.day_leave == 0 ? '' : "�� ") + "</EM>" + 
          (sp.day_leave >= 0 && sp.day_leave < 3 ? leave : sp.day) + 
          (sp.day_leave > 2 || sp.day_leave < 0 ? "<SPAN>, " + leave + "</SPAN>" : '') +
        "<DIV class=hist>" + sp.history + "</DIV>" +
      "</DIV></DIV>";
  }
  $("#zayav_reminder").html(html);
  if (html) {
    for (var n = 0; n < G.zayav.reminder.length; n++) {
      var sp = G.zayav.reminder[n];
      $("#unit_" + sp.id)
        .on('mouseenter', function () { $(this).find('.action:first').show(); })
        .on('mouseleave', function () { $(this).find('.action:first').hide(); });
    }    
  }


  frameBodyHeightSet();
};




// ����� ������� �����������
function reminderHistoryShow(num) {
  var sp = G.zayav.reminder[num];
  if (sp.down == 1) {
    $("#unit_" + sp.id).find(".hist:first").slideUp(200);
    sp.down = 0;
  } else {
    $("#unit_" + sp.id).find(".hist:first").slideDown(200);
    sp.down = 1;
  }
}










function reminderAction(num) {
  var sp = G.zayav.reminder[num];
  var html = "<TABLE cellpadding=0 cellspacing=0 class=remind_action_tab>" +
      "<TR><TD class=tdAbout>�������:<TD><B>" + sp.txt + "</B>" +
      "<TR><TD class=tdAbout>��������:<TD><INPUT type=hidden id=action>" +
      "</TABLE>" +

      "<TABLE cellpadding=0 cellspacing=0 class=remind_action_tab id=new_action>" +
        "<TR><TD class=tdAbout id=new_about><TD id=new_title>" +
        "<TR><TD class=tdAbout id=new_comm><TD><TEXTAREA id=comment></TEXTAREA>" +
      "</TABLE>";
  var dialog = $("#zayav_dialog").vkDialog({
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
    var msg;
    var send = {
      id:sp.id,
      zayav_id:G.zayav.id,
      action:$("#action").val(),
      day:sp.day_real,
      status:1,
      history:$("#comment").val()
    };
    if (send.action == 1) { send.day = $("#data").val(); }
    if (send.action == 2) { send.status = 2; } // ���������
    if (send.action == 3) { send.status = 0; } // ��������

    if (!send.action) { msg = "������� ����� ��������."; }
    else if ((send.action == 1 || send.action == 3) && !send.history) { msg = "�� ������� �������."; }
    else if (send.action == 1 && send.day == sp.day_real) { msg = "�������� ����� ����."; }
    else {
      dialog.process();
      $.post("/remont/zayavki/info/AjaxReminderEdit.php?" + G.values, send, function (res) {
        G.zayav.reminder = res.reminder;
        dialog.close();
        vkMsgOk("������� ���������������.");
        G.zayav.update();
      }, 'json');
    }
    if (msg) { $("#zayav_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:115}); }
  }
}











G.zayav.update();
tablePrint('accrual', G.zayav.accrual);
tablePrint('oplata', G.zayav.oplata);

$("#foto_upload").fotoUpload({
  owner:'zayav' + G.zayav.id,
  max_x:200,
  max_y:320,
  func:function (obj) { G.zayav.foto.push(obj); G.zayav.update(); }
});




// ���� �� ���� ������� ���������� � ������
$("#zayavInfo").click(function (e) {
  var val = $(e.target).attr('val');
  var n = 0;
  while (val == undefined) {
    val = $(e.target).parent().attr('val');
    n--;
    if (n < 0) break;
  }
  if (val) {
    val = val.split('_');
    switch (val[0]) {
    case 'zayavEdit': zayavEdit(); break;
    case 'accrualAdd': accrualAdd(); break;
    case 'oplataAdd': oplataAdd(); break;
    case 'zayavDel': zayavDel(); break;
    case 'zayavStatus': zayavStatus(); break;
    case 'zayavFoto': G.fotoView({spisok:G.zayav.foto}); break;
    }
  }

  // �������������� ������
  function zayavEdit() {
    var HTML = "<TABLE cellpadding=0 cellspacing=8 id=zayavEdit>";
    HTML += "<TR><TD class=tdAbout>������:             <TD colspan=2><INPUT TYPE=hidden id=client_id value=" + G.zayav.client_id + ">";
    HTML += "<TR><TD class=tdAbout>���������:       <TD colspan=2><INPUT TYPE=hidden id=category value=" + G.zayav.category + ">";
    HTML += "<TR><TD class=tdAbout>����������:     <TD id=dev><TD id=devView>";
    HTML += "<TR><TD class=tdAbout>IMEI:                  <TD colspan=2><INPUT type=text id=imei maxlength=20 value='" + G.zayav.imei + "'>";
    HTML += "<TR><TD class=tdAbout>�������� �����: <TD colspan=2><INPUT type=text id=serial maxlength=30 value='" + G.zayav.serial + "'>";
    HTML += "<TR><TD class=tdAbout>����:                  <TD colspan=2><INPUT TYPE=hidden id=color_id value=" + G.zayav.color + ">";
    HTML += "<TR><TD colspan=3 id=ms>";
    HTML += "</TABLE>";
    var dialog = $("#zayav_dialog").vkDialog({
      width:400,
      top:30,
      head:"������ �" + G.zayav.nomer + " - ��������������",
      content:HTML,
      focus:'',
      butSubmit:'���������',
      submit:save
    }).o;

    $("#client_id").clientSel();
    $("#category").vkSel({width:150, spisok:G.category_spisok});

    $("#dev").device({
      width:190,
      device_id:G.zayav.device,
      vendor_id:G.zayav.vendor,
      model_id:G.zayav.model,
      device_ids:G.device_ids,
      add:1
   //   func:modelView
    });
    $("#color_id").vkSel({width:170, title0:'���� �� ������', spisok:G.color_spisok});

    function save() {
      var msg = '';
      if ($("#dev_device").val() ==0) { msg = '�� ������� ����������'; }
      if ($("#client_id").val() == 0) { msg = '�� ������ ������'; }
      if (msg) {
        $("#ms").alertShow({txt:"<SPAN class=red>" + msg + "</SPAN>", top:5, left:110, delayHide:3000});
      } else {
        G.zayav.client_id = $("#client_id").val();
        G.zayav.category = $("#category").val();
        G.zayav.device = $("#dev_device").val();
        G.zayav.vendor = $("#dev_vendor").val();
        G.zayav.model = $("#dev_model").val();
        G.zayav.imei = $("#imei").val();
        G.zayav.serial = $("#serial").val();
        G.zayav.color = $("#color_id").val();
        dialog.process();
        $.post("/remont/zayavki/info/AjaxZayavEdit.php?" + G.values, G.zayav, function (res) {
          dialog.close();
          vkMsgOk("������ ��������!");
          G.zayav.update();
        }, 'json');
      }
    }
  } // end zayavEdit


  // ��������� ������� ������, ������� � ���������� ����������
  function zayavStatus() {
    var HTML = "<TABLE cellpadding=0 cellspacing=6 id=zayavStatus>";
    HTML += "<TR><TD class=tdAbout>������ ������:<TD><INPUT TYPE=hidden id=edit_zayav_status value=" + G.zayav.status + ">";
    HTML += "<TR><TD class=tdAbout>��������������� ����������:<TD><input type=hidden id=edit_device_place value=" + G.zayav.place + ">";
    HTML += "<TR><TD class=tdAbout>��������� ����������:<TD><INPUT TYPE=hidden id=edit_device_status value=" + G.zayav.device_status + ">";
    HTML += "</TABLE>";
    var dialog = $("#zayav_dialog").vkDialog({
      width:400,
      top:30,
      head:"��������� ������� ������ � ��������� ����������",
      content:HTML,
      butSubmit:'���������',
      submit:submit
    }).o;

    $("#edit_zayav_status").vkRadio({spisok:G.status_spisok, top:1, bottom:4, light:1});


    var spisok = [];
    for (var n = 0; n < G.device_place_spisok.length; spisok.push(G.device_place_spisok[n]), n++);
    spisok.push({uid:0, title:"������: <DIV id=place_other_div><INPUT type=text id=place_other maxlength=20 value='" + G.zayav.place_other + "'></DIV>"});
    $("#edit_device_place").vkRadio({
      spisok:spisok,
      top:1,
      bottom:4,
      light:1,
      func:function (val) {
        $("#place_other_div").css('display', val == 0 ? 'inline' : 'none');
        if (val == 0) { $("#place_other").val('').focus(); }
      }
    });
    if (G.zayav.place == 0) { $("#place_other_div").css('display', 'inline'); }

    $("#edit_device_status").vkRadio({spisok:G.device_status_spisok, top:1, bottom:4, light:1});

    function submit() {
      var obj = {
        zayav_id:G.zayav.id,
        zayav_status:$("#edit_zayav_status").val(),
        zayav_status_new:$("#edit_zayav_status").val() == G.zayav.status ? 0 : 1, // ��������� ������ ��� ��� (��� ��������� ������� �������)
        device_status:$("#edit_device_status").val(),
        device_place:$("#edit_device_place").val(),
        device_place_other:$("#place_other").val()
      };
      if (obj.device_place > 0) {  obj.device_place_other = ''; }
      if (obj.device_place == 0 && obj.device_place_other == '') {
        $("#zayav_dialog .bottom").alertShow({txt:"<SPAN class=red>�� ������� ��������������� ����������</SPAN>", top:-44, left:127, delayHide:3000});
        $("#place_other").focus();
      } else if (obj.device_status == 0) {
        $("#zayav_dialog .bottom").alertShow({txt:"<SPAN class=red>�� ������� ��������� ����������</SPAN>", top:-44, left:127, delayHide:3000});
      } else {
        dialog.process();
        $.post("/remont/zayavki/info/AjaxZayavStatus.php?" + G.values, obj, function (res) {
          vkMsgOk("��������� ���������.");
          G.zayav.status = obj.zayav_status;
          if (res.status_dtime) { G.zayav.status_dtime = res.status_dtime; } 
          G.zayav.device_status = obj.device_status;
          G.zayav.place = obj.device_place;
          G.zayav.place_other = obj.device_place_other;
          G.zayav.update();
          dialog.close();
        }, 'json');
      }
    } // end submit  
  } // end zayavStatus

  // �������� ������
  function zayavDel() {
    var dialog = $("#zayav_dialog").vkDialog({
      top:110,
      width:250,
      head:'��������',
      content:"<CENTER>����������� �������� ������.</CENTER>",
      butSubmit:'�������',
      submit:function () {
        dialog.process();
        $.getJSON("/remont/zayavki/info/AjaxZayavDel.php?" + G.values + "&id=" + G.zayav.id, function(res){
          location.href = "/index.php?" + G.values + "&my_page=remClientInfo&id=" + res.client_id;
        });
      }
    }).o;
  } // end zayavDel
});




$("#zayav_zp_spisok").click(function () { location.href = "/index.php?" + G.values + "&my_page=remZp&id=[1,0," + G.zayav.device + "," + G.zayav.vendor + "," + G.zayav.model + "]" });

// ���������� ����� ��������
$("#zayav_zp_add").click(function () {
  var html = "<CENTER style=font-size:12px;>���������� �������� � ����������<BR>";
  html += "<B style=font-size:12px;>" + G.device_ass[G.zayav.device] + " " + G.vendor_ass[G.zayav.vendor] + " " + G.model_ass[G.zayav.model] + "</B>.</CENTER><BR>";
  html += "<TABLE cellpadding=0 cellspacing=5>";
  html += "<TR><TD class=tdAbout>������������ ��������:<TD><INPUT TYPE=hidden id=zpAdd_name_id value=0>";
  html += "<TR><TD class=tdAbout>�������������� ����������:<TD><INPUT TYPE=text id=zpAdd_name_dop maxlength=30 style=width:188px;>";
  html += "<TR><TD class=tdAbout>����:<TD><INPUT TYPE=hidden id=zpAdd_color_id value=0>";
  html += "</TABLE>";

  var dialog = $("#zayav_dialog").vkDialog({
    top:40,
    width:420,
    head:"�������� ����� ��������",
    content:html,
    submit:submit
  }).o;

    frameBodyHeightSet();

  $("#zayav_dialog .tdAbout").css({
    width:'155px',
    'text-align':'right',
    'padding-top':'4px'
  });

  $("#zpAdd_name_id").vkSel({
    width:200,
    title0:'������������ �� �������',
    spisok:G.zp_name_spisok
  });

  $("#zpAdd_color_id").vkSel({
    width:130,
    title0:'���� �� ������',
    spisok:G.color_spisok
  });
  
  function submit() {
    var obj = {
      name_id:$("#zpAdd_name_id").val(),
      name_dop:$("#zpAdd_name_dop").val(),
      color_id:$("#zpAdd_color_id").val(),
      device_id:G.zayav.device,
      vendor_id:G.zayav.vendor,
      model_id:G.zayav.model
    };
    if (obj.name_id == 0) {
      $("#zayav_dialog .bottom").alertShow({txt:"<SPAN class=red>�� ������� ������������ ��������.</SPAN>", left:135, top:-43});
    } else {
      dialog.process();
      $.post("/remont/zp/AjaxZpAdd.php?" + G.values, obj, function (res) {
        vkMsgOk("�������� �������� �����������.");
        G.zayav.zp.push({
          id:res.id,
          name_id:obj.name_id,
          name_dop:obj.name_dop,
          color_id:obj.color_id,
          avai:0,
          zakaz:0
        });
        G.zayav.update();
        dialog.close();
      }, 'json');
    }
  } // end submit
});



// ����������� � ����������
$("#zayav_zp").click(function (e) {
  var val = $(e.target).attr('val');
  if (val) {
    val = val.split('_');
    var sp = G.zayav.zp[val[1]];
    switch (val[0]) {
    case 'set': set(sp); break;
    case 'zakaz': zakaz(sp); break;
    case 'zakazok': location.href = "/index.php?" + G.values + "&my_page=remZp&id=[4]"; break;
    }
  }
  
  // ��������� ��������
  function set(sp) {
    var html = "<CENTER style=font-size:12px;>��������� �������� <B style=font-size:12px;>" + G.zp_name_ass[sp.name_id] + "</B> ��� " + G.vendor_ass[G.zayav.vendor] + " " + G.model_ass[G.zayav.model] + ".<BR>";
     if (sp.color_id > 0) { html += "����: " + G.color_ass[sp.color_id] + ".<BR>"; }
    html += "<BR>���������� �� ��������� �����<BR>����� ��������� � ������� � ������.</CENTER>";
    dialogShow({
      top:150,
      width:400,
      head:"��������� ��������",
      content:html,
      butSubmit:'����������',
      submit:setSubmit
    });
    function setSubmit() {
      $("#butDialog").butProcess();
      $.post("/remont/zayavki/info/AjaxZpSet.php?" + G.values, {zp_id:sp.id, zayav_id:G.zayav.id}, function (res) {
        dialogHide();
        vkMsgOk("��������� �������� �����������.");
        sp.avai -= 1;
        G.zayav.update();
      },'json');
    }
  }

  // ����� ��������
  function zakaz(sp) {
    $.getJSON("/remont/zayavki/info/AjaxZpZakaz.php?" + G.values + "&zid=" + sp.id + "&zayav_id=" + G.zayav.id, function (res) {
      vkMsgOk("�������� <B>" + G.zp_name_ass[sp.name_id] + "</B> ��� " + G.vendor_ass[G.zayav.vendor] + " " + G.model_ass[G.zayav.model] + " ��������� � ������.");
      sp.zakaz = 1;
      G.zayav.update();
    });
  }
});







// ����� ������������
$("#comm").vkComment({table_name:'zayav', table_id:G.zayav.id});























// ����� ������ ���������� ��� ��������
function tablePrint(table,obj) {
  if (obj.length > 0) {
    var HTML = "<DIV class=headBlue>" + (table == 'accrual' ? "����������" : "�������") + "<EM></EM><A onclick=" + table + "Add();>" + (table == 'accrual' ? "���������" : "������� �����") + "</A></DIV>";
    HTML += "<TABLE cellpadding=0 cellspacing=0 class=tabSpisok>";
    var summa = 0;
    for (var n = 0; n < obj.length; n++) {
      HTML += "<TR id=tr" + obj[n].id + "><TD class=sum>" + obj[n].summa;
      HTML += "<TD>" + obj[n].prim;
      HTML += "<TD class=dtime>" + obj[n].dtime;
      HTML += "<TD class=del><DIV val=" + obj[n].id + " class=img_del></DIV>";
      summa += parseInt(obj[n].summa);
    }
    HTML += "</TABLE>";
    $("#" + table)
      .html(HTML)
      .find(".img_del").click(function () { AMdel(table,$(this).attr('val')); });
    if (obj.length > 1) {
      $("#" + table + " EM").html("(����� " + summa + " ���.)");
    }
  frameBodyHeightSet();
  }
}








// �������� ���������� ��� �������
function AMdel(table,id) {
  var obj = $("#" + table);
  obj
    .find("#tr" + id).hide()
    .after("<TR id=del" + id + "><TD colspan=4 class=deleted><IMG src=/img/upload.gif>");
  $.post("/remont/zayavki/info/AjaxAMdel.php?"+G.values,{
      table:table == 'accrual' ? table : 'money',
      id:id,
      client_id:G.zayav.client_id,
      zid:G.zayav.id
    },function (res) {
 //    obj.find("#del" + id + " TD").html((table == 'accrual' ? '���������� �������' : '����� �����') + ". <A onclick=AMrec('" + table + "'," + id + ");>������������</A>");
      obj.find("#del" + id).remove();
      if (res.count == 0) { $("#dopMenu .del").show(); }
    },'json');
}





/*

// �������������� ���������� ��� �������
function AMrec(table,id) {
  var obj = $("#" + table);
  obj.find("#del" + id + " TD").html("<IMG src=/img/upload.gif>");
  $.post("/remont/zayavki/info/AjaxAMrec.php?"+G.values,{table:table == 'accrual' ? table : 'money',id:id,client_id:G.zayav.client_id},function () {
    obj
      .find("#del" + id).remove().end()
      .find("#tr" + id).show();
    $("#dopMenu .del").hide();
  });
}
*/







G.device_status_spisok.splice(0,1); // �������� �� ������� '�� ��������'
// �������� ����������
function accrualAdd() {
  var html = "<TABLE cellpadding=0 cellspacing=8 class=accrual_dialog>" +
      "<TR><TD class='tdAbout tdSum'>�����:      <TD><input type=text id=summa class=oplata maxlength=5> ���." +
      "<TR><TD class=tdAbout>����������:<EM>(�� �����������)</EM><TD><input type=text id=prim maxlength=100>" +
      "<TR><TD class=tdAbout>������ ������: <TD><INPUT type=hidden id=zStatus value=2>" +
      "<TR><TD class=tdAbout>��������� ����������:<TD><INPUT type=hidden id=accrual_device_status value=5>" +
      "<TR><TD class=tdAbout>�������� �����������:<TD><INPUT type=hidden id=reminder>" +
    "</TABLE>" +

    "<TABLE cellpadding=0 cellspacing=8 class=accrual_dialog id=reminder_tab>" +
      "<TR><TD class=tdAbout>����������:<TD><input type=text id=reminder_txt value='��������� � �������� � ����������.'>" +
      "<TR><TD class=tdAbout>����:<TD><INPUT type=hidden id=reminder_day>" +
    "</TABLE>";
  var dialog = $("#zayav_dialog").vkDialog({
    width:420,
    top:60,
    head:"������ �" + G.zayav.nomer + " - ���������� �� ����������� ������",
    content:html,
    focus:'#summa',
    submit:submit
  }).o;
  $("#zStatus").linkMenu({spisok:G.status_spisok, func:function () { $("#summa").focus(); }});
  $("#accrual_device_status").linkMenu({
    spisok:G.device_status_spisok,
    func:function(){ $("#summa").focus(); }
  });
  $("#summa").keydown(function (e) { if (e.keyCode == 13) submit(); });
  $("#prim").keydown(function (e) { if (e.keyCode == 13) submit(); });

  $("#reminder").myCheck({func:function (id) { $("#reminder_tab").toggle(); }});
  $("#reminder_day").vkCalendar();


  function submit() {
    var obj = {
      cid:G.zayav.client_id,
      zid:G.zayav.id,
      summa:$("#summa").val(),
      prim:$("#prim").val(),
      status:$("#zStatus").val(),
      status_new:$("#zStatus").val() == G.zayav.status ? 0 : 1, // ��������� ������ ��� ��� (��� ��������� ������� �������)
      device_status:$("#accrual_device_status").val(),
      reminder:$("#reminder").val()
    };
    obj.reminder_txt = obj.reminder == 1 ? $("#reminder_txt").val() : '';
    obj.reminder_day = obj.reminder == 1 ? $("#reminder_day").val() : '';

    var msg;
    if (!/\d$/.test(obj.summa)) { msg = "�� ��������� ������� �����."; $("#summa").focus(); }
    else if (obj.reminder == 1 && !obj.reminder_txt) { msg = '�� ������ ����� �����������'; }
    else {
      dialog.process();
      $.post("/remont/zayavki/info/AjaxAccrualAdd.php?" + G.values, obj, function (res) {
        tablePrint('accrual', res);
        $("#dopMenu .del").hide();
        dialog.close();
        vkMsgOk("���������� ������� �����������!");
        G.zayav.status = obj.status;
        G.zayav.device_status = obj.rdevice_status;
        if (obj.status_new == 1) { G.zayav.status_dtime = res[res.length - 1].dtime; }
        G.zayav.update();
      }, 'json');
    }

    if (msg) { $("#zayav_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", top:-48, left:152, indent:'left', remove:1, show:1}); }
  } // end submit
}





/* �������� ������� */
function oplataAdd() {
  var html = "  <TABLE cellpadding=0 cellspacing=8 id=oplata_dialog>" +
    "<TR><TD class='tdAbout tdSum'>�����:<TD><input type=text id=summa class=oplata maxlength=5> ���." +
    "<TR><TD class=tdAbout>������ ��������� � �����?:<TD><input type=hidden id=kassa value='-1'>" +
    "<TR><TD class=tdAbout>��������������� ����������:</EM><TD><input type=hidden id=oplata_device_place value=2>" +
    "<TR><TD class=tdAbout>����������:<EM>(�� �����������)</EM><TD><input type=text id=prim>" +
    "</TABLE>";
  var dialog = $("#zayav_dialog").vkDialog({
    width:440,
    top:60,
    head:"������ �" + G.zayav.nomer + " - �������� �������",
    content:html,
    submit:submit,
    focus:'#summa'
  }).o;
  $("#summa").keydown(function (e) { if (e.keyCode == 13) submit(); });
  $("#prim").keydown(function (e) { if (e.keyCode == 13) submit(); });

  $("#kassa").vkRadio({
    display:'inline-block',
    right:15,
    spisok:[{uid:1, title:'��'},{uid:0, title:'���'}]
  });
  $("#kassa_radio").vkHint({msg:"���� ��� �������� �����<BR>� ������ �������� � ����������,<BR>������� '��'.", top:-83, left:-60});
  


  $("#oplata_device_place").linkMenu({
    spisok:G.device_place_spisok,
    func:function(){ $("#summa").focus(); }
  });

  function submit() {
    var obj = {
      client_id:G.zayav.client_id,
      zayav_id:G.zayav.id,
      summa:$("#summa").val(),
      kassa:$("#kassa").val(),
      prim:$("#prim").val(),
      device_place:$("#oplata_device_place").val()
    };

    var msg;
    if (!/^\d+$/.test(obj.summa)) { msg = "����������� ������� �����."; $("#summa").focus(); }
    else if (obj.kassa == -1) { msg = "�������, ��������� ������ � ����� ��� ���."; }
    else {
      dialog.process();
      $.post("/remont/zayavki/info/AjaxOplataAdd.php?" + G.values, obj, function (res) {
        tablePrint('oplata',res);
        $("#dopMenu .del").hide();
        dialog.close();
        vkMsgOk("����� ������� �����!");
        G.zayav.place = obj.device_place;
        G.zayav.update();
      }, 'json');
    }

    if (msg) { $("#zayav_dialog .bottom:first").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", remove:1, indent:40, show:1, top:-48, left:135}); }
  } // end submit
}







