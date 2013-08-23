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

  if (G.zayav.foto.length > 0) { $("#foto").fotoSet({foto:G.zayav.foto[0], max_x:200, max_y:320, val:'zayavFoto_'}); }
};






















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

