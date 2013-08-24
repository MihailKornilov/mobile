// обновление данных о заявке и запчастях
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






















// клик по всей области информации о заявке
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

  // Изменение статуса заявки, статуса и нахождения устройства
  function zayavStatus() {
    var HTML = "<TABLE cellpadding=0 cellspacing=6 id=zayavStatus>";
    HTML += "<TR><TD class=tdAbout>Статус заявки:<TD><INPUT TYPE=hidden id=edit_zayav_status value=" + G.zayav.status + ">";
    HTML += "<TR><TD class=tdAbout>Местонахождение устройства:<TD><input type=hidden id=edit_device_place value=" + G.zayav.place + ">";
    HTML += "<TR><TD class=tdAbout>Состояние устройства:<TD><INPUT TYPE=hidden id=edit_device_status value=" + G.zayav.device_status + ">";
    HTML += "</TABLE>";
    var dialog = $("#zayav_dialog").vkDialog({
      width:400,
      top:30,
      head:"Изменение статуса заявки и состояния устройства",
      content:HTML,
      butSubmit:'Сохранить',
      submit:submit
    }).o;

    $("#edit_zayav_status").vkRadio({spisok:G.status_spisok, top:1, bottom:4, light:1});


    var spisok = [];
    for (var n = 0; n < G.device_place_spisok.length; spisok.push(G.device_place_spisok[n]), n++);
    spisok.push({uid:0, title:"другое: <DIV id=place_other_div><INPUT type=text id=place_other maxlength=20 value='" + G.zayav.place_other + "'></DIV>"});
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
        zayav_status_new:$("#edit_zayav_status").val() == G.zayav.status ? 0 : 1, // обновился статус или нет (для изменения времени статуса)
        device_status:$("#edit_device_status").val(),
        device_place:$("#edit_device_place").val(),
        device_place_other:$("#place_other").val()
      };
      if (obj.device_place > 0) {  obj.device_place_other = ''; }
      if (obj.device_place == 0 && obj.device_place_other == '') {
        $("#zayav_dialog .bottom").alertShow({txt:"<SPAN class=red>Не указано местонахождение устройства</SPAN>", top:-44, left:127, delayHide:3000});
        $("#place_other").focus();
      } else if (obj.device_status == 0) {
        $("#zayav_dialog .bottom").alertShow({txt:"<SPAN class=red>Не указано состояние устройства</SPAN>", top:-44, left:127, delayHide:3000});
      } else {
        dialog.process();
        $.post("/remont/zayavki/info/AjaxZayavStatus.php?" + G.values, obj, function (res) {
          vkMsgOk("Изменения сохранены.");
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

  // удаление заявки
  function zayavDel() {
    var dialog = $("#zayav_dialog").vkDialog({
      top:110,
      width:250,
      head:'Удаление',
      content:"<CENTER>Подтвердите удаление заявки.</CENTER>",
      butSubmit:'Удалить',
      submit:function () {
        dialog.process();
        $.getJSON("/remont/zayavki/info/AjaxZayavDel.php?" + G.values + "&id=" + G.zayav.id, function(res){
          location.href = "/index.php?" + G.values + "&my_page=remClientInfo&id=" + res.client_id;
        });
      }
    }).o;
  } // end zayavDel
});

