$("#client_id").clientSel({add:1});

var html = "<TABLE cellpadding=0 cellspacing=0>";
var half = Math.ceil(G.fault_spisok.length / 2);
for (var n = 0; n < half; n++) {
  html += "<TR><TD><INPUT type=hidden id=fault_" + G.fault_spisok[n].uid + " value=0><TD>";
  if (G.fault_spisok[n + half]) {html += "<INPUT type=hidden id=fault_" + G.fault_spisok[n + half].uid + " value=0>"; }
}
html += "</TABLE>";
$("#fault").html(html);
for (var k in G.fault_ass) { $("#fault_" + k).myCheck({title:G.fault_ass[k], top:5, func:articleCreate}); }

$("#category").vkSel({width:150, spisok:G.category_spisok});

// создание нового списка устройств, которые выбраны для этой мастерской
G.device_spisok = [];
for (var n = 0; n < G.ws.devs.length; n++) {
  var uid = G.ws.devs[n];
  G.device_spisok.push({uid:uid, title:G.device_ass[uid]});
}
$("#dev").device({width:190, add:1, func:modelView});

G.device_place_spisok.push({uid:0, title:"другое: <DIV id=place_other_div><INPUT type=text id=place_other maxlength=20></DIV>"});
$("#place").vkRadio({
  spisok:G.device_place_spisok,
  top:6,
  func:function (val) {
    $("#place_other_div").css('display', val == 0 ? 'inline' : 'none');
    if (val == 0) { $("#place_other").val('').focus(); }
  }
});


$("#color_id").vkSel({width:170, title0:'Цвет не указан', spisok:G.color_spisok});
$("#comm").autosize();

$("#reminder").myCheck({func:function (id) {
  $("#reminder_tab").toggle();
  if (id == 1) {$("#reminder_txt").focus(); }
  frameBodyHeightSet();
}});
$("#reminder_day").vkCalendar();

function modelView() {
  var MOD = $("#dev_model").val();
  $("#table_id").val(MOD);
  if(MOD>0) {
    $.getJSON("/remont/zayavki/add/AjaxModelImgGet.php?" + G.values + "&model_id=" + MOD, function (res) { $("#dev_view").html(res.img); });
  } else {
    $("#dev_view").html('');
  }
}


// формирование заменки при нажатии на галочки с неисправностями
function articleCreate() {
  var i = $("#fault INPUT");
  var arr = [];
  for( var n = 0; n < i.length; n++) {
    if(i.eq(n).val() == 1) {
      var uid = i.eq(n).attr('id').split('_')[1];
      arr.push(G.fault_ass[uid]);
    }
  }
  $("#comm").val(arr.join(', '));
}


$("#ms BUTTON:first").click(function () {
  var obj = {
    client:$("#client_id").val(),
    category:$("#category").val(),
    device:$("#dev_device").val(),
    vendor:$("#dev_vendor").val(),
    model:$("#dev_model").val(),
    place:$("#place").val(),
    place_other:$("#place_other").val(),
    imei:$("#imei").val(),
    serial:$("#serial").val(),
    color:$("#color_id").val(),
    comm:$("#comm").val(),
    reminder:$("#reminder").val()
  };
  obj.reminder_txt = obj.reminder == 1 ? $("#reminder_txt").val() : '';
  obj.reminder_day = obj.reminder == 1 ? $("#reminder_day").val() : '';

  var msg = '';
  if (obj.client == 0) { msg = 'Не выбран клиент'; }
  else if (obj.device == 0) { msg = 'Не выбрано устройство'; }
  else if (obj.place == '' || obj.place == 0 && !obj.place_other) { msg = 'Не указано местонахождение устройства'; }
  else if (obj.reminder == 1 && !obj.reminder_txt) { msg = 'Не указан текст напоминания'; }
  else {
    if (obj.place > 0) { obj.place_other = ''; }
    $(this).butProcess();
    $.post("/remont/zayavki/add/AjaxZayavkiAdd.php?" + G.values, obj, function (res) {
      location.href = "/index.php?" + G.values + "&my_page=remZayavkiInfo&id=" + res.id;
    }, 'json');
  }

  if (msg) { $("#ms").vkHint({msg:"<SPAN class=red>" + msg + "</SPAN>", top:-48, left:215, indent:'left', remove:1, show:1}); }
});

