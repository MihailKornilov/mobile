/* ����� select ��� ��������� */
$.fn.device = function(obj) {
  var obj = $.extend({
    width:150,          /* ��������� ������ �� ��������� */
    func:function () {},              /* �������, ����������� ��� ������ �������� */
    device_no:['���������� �� �������','����� ����������'],
    vendor_no:['������������� �� ������','����� �������������'],
    model_no:['������ �� �������','����� ������'],
    type_no:0,
    device_id:0,
    vendor_id:0,
    model_id:0,
    device_ids:null, // ������ id, ������� ����� �������� � ������ ��� ���������
    vendor_ids:null, // ��� ��������������
    model_ids:null,  // ��� �������
    add:0,              /* ��������� ��� ��� ��������� ����� �������� � ������ */
    device_funcAdd:null,    /* ������� �����, ���� ������ ��������� ����� �������� */
    vendor_funcAdd:null,
    model_funcAdd:null
  },obj);

  var NAME = $(this).attr('id');
  var HID = "<INPUT TYPE=hidden id=" + NAME + "_device name=" + NAME + "_device value=" + obj.device_id + ">";
  HID += "<INPUT TYPE=hidden id=" + NAME + "_vendor name=" + NAME + "_vendor value=" + obj.vendor_id + ">";
  HID += "<INPUT TYPE=hidden id=" + NAME + "_model name=" + NAME + "_model value=" + obj.model_id + ">";
  $(this).html(HID);

  // �������� ����� ��� ���������� ������� � �������� ����� ���������
  $("#device_dialog").remove();
  $("BODY").append("<DIV id=device_dialog></DIV>");
  var dialog = $("#device_dialog");

  var vk_device, vk_vendor, vk_model; // ��������� �� ������� �������

  // ���������� ����� ���������
  if (obj.add > 0) {
    var devD;
    obj.device_funcAdd = function () {
      var HTML = "<TABLE cellpadding=0 cellspacing=10>";
      HTML+="<TR><TD class=tdAbout>��������:<TD><INPUT TYPE=text id=device_name>";
      HTML+="</TABLE>";
      devD = dialog.vkDialog({
        width:300,
        head:"���������� �o���� ����������",
        content:HTML,
        submit:deviceAddSubmit,
        focus:'#device_name'
      }).o;
      $("#device_name")
        .css('width','170px')
        .keydown(function () {
          if (event.keyCode == 13) { deviceAddSubmit(); }
        });
    };
    function deviceAddSubmit() {
      var name = $("#device_name").val();
      $("#device_name").focus();      
      if (!name) {
          dialog.find('.bottom').alertShow({txt:"<SPAN class=red>�� ������� �������� ����������.</SPAN>",left:75,top:-44});
      } else if (name_test(vk_device.spisok(), name)) {
        dialog.find('.bottom').alertShow({txt:"<SPAN class=red>����� �������� ��� ���� � ������.</SPAN>",left:75,top:-44});
      } else {
        devD.process();
        $.post("/include/device/AjaxDeviceAdd.php?"+G.values, {name:name}, function (res) {
          devD.close();
          vk_device.add({uid:res.id, title:name}).val(res.id);
          G.device_ass[res.id] = name;
          getVendor(0);
          if (vk_model) { vk_model.val(0).remove(); } // ��������� ������ ������ � ��������������� � 0
          obj.func(getIds());
        } ,'json');
      }
    }


    // ���������� �������������
    obj.vendor_funcAdd = function () {
      var HTML="<TABLE cellpadding=0 cellspacing=10>";
      HTML+="<TR><TD class=tdAbout>��������:<TD><INPUT TYPE=text id=vendor_name>";
      HTML+="</TABLE>";
      devD = dialog.vkDialog({
        width:300,
        head:"���������� �o���� �������������",
        content:HTML,
        submit:vendorAddSubmit,
        focus:'#vendor_name'
      }).o;
      $("#vendor_name")
        .css('width','170px')
        .keydown(function () {
          if (event.keyCode == 13) { vendorAddSubmit(); }
        });
    };
    function vendorAddSubmit() {
      var send = {
        device_id:vk_device.val(),
        name:$("#vendor_name").val()
      };
      $("#vendor_name").focus();
      if(!send.name) {
        dialog.find('.bottom').alertShow({txt:"<SPAN class=red>�� ������� �������� �������������.</SPAN>", left:75, top:-44});
      } else if (name_test(vk_vendor.spisok(), send.name)) {
        dialog.find('.bottom').alertShow({txt:"<SPAN class=red>����� �������� ��� ���� � ������.</SPAN>", left:75, top:-44});
      } else {
        devD.process();
        $.post("/include/device/AjaxVendorAdd.php?" + G.values, send, function (res) {
          devD.close();
          // ���� � ���������� ��� ��������������, ������� �������� ������ ������
          if (!G.vendor_spisok[vk_device.val()]) {
            G.vendor_spisok[vk_device.val()] = [];
            G.vendor_spisok[vk_device.val()].unshift({uid:res.id, title:send.name});
          }
          vk_vendor.add({uid:res.id, title:send.name}).val(res.id);
          G.vendor_ass[res.id] = send.name;
          getModel();
        }, 'json');
      }
    }


    
    // ���������� ����� ������
    obj.model_funcAdd = function(){
      var HTML="<TABLE cellpadding=0 cellspacing=10>";
      HTML+="<TR><TD class=tdAbout>��������:<TD><INPUT TYPE=text id=model_name>";
      HTML+="</TABLE>";
      devD = dialog.vkDialog({
       width:300,
       head:"���������� �o��� ������",
        content:HTML,
        submit:modelAddSubmit,
        focus:'#model_name'
      }).o;
      $("#model_name")
        .css('width','170px')
        .keydown(function () {
          if (event.keyCode == 13) { modelAddSubmit(); }
        });
    };
    function modelAddSubmit() {
      var send = {
        device_id:vk_device.val(),
        vendor_id:vk_vendor.val(),
        name:$("#model_name").val()
      };
      $("#model_name").focus();
      if (!send.name) {
        dialog.find('.bottom').alertShow({txt:"<SPAN class=red>�� ������� �������� ������.</SPAN>", left:75, top:-44});
      } else if (name_test(vk_model.spisok(), send.name)) {
        dialog.find('.bottom').alertShow({txt:"<SPAN class=red>����� �������� ��� ���� � ������.</SPAN>", left:75, top:-44});
      } else {
        devD.process();
        $.post("/include/device/AjaxModelAdd.php?" + G.values, send, function (res) {
          devD.close();
          // ���� � ������������� ��� �������, ������� �������� ������ ������
          if (!G.model_spisok[vk_vendor.val()]) {
            G.model_spisok[vk_vendor.val()] = [];
            G.model_spisok[vk_vendor.val()].unshift({uid:res.id, title:send.name});
          }
          vk_model.add({uid:res.id, title:send.name}).val(res.id);
          G.model_ass[res.id] = send.name;
        }, 'json');
      }
    }
  } // end add







  // �������� ������ ������ ���������, ������� ����� �������� � ������
  if (obj.device_ids) {
    G.device_spisok = [];
    for (var n = 0; n < obj.device_ids.length; n++) {
      var uid = obj.device_ids[n];
      G.device_spisok.push({uid:uid, title:G.device_ass[uid]});
    }
  }


  // �������� ������ ������ ��������������, ������� ����� �������� � ������
  if (obj.vendor_ids) {
    var vendors = {};
    for (var k in G.vendor_spisok) {
      for (var n = 0; n < G.vendor_spisok[k].length; n++) {
        var sp = G.vendor_spisok[k][n];
        if (obj.vendor_ids.indexOf(sp.uid) >= 0) {
          if (vendors[k] == undefined) { vendors[k] = []; }
          vendors[k].push(sp);
        }
      }
    }
    G.vendor_spisok = vendors;
  }



  // �������� ������ ������ �������, ������� ����� �������� � ������
  if (obj.model_ids) {
    var models = {};
    for (var k in G.model_spisok) {
      for (var n = 0; n < G.model_spisok[k].length; n++) {
        var sp = G.model_spisok[k][n];
        if (obj.model_ids.indexOf(sp.uid) >= 0) {
          if (models[k] == undefined) { models[k] = []; }
          models[k].push(sp);
        }
      }
    }
    G.model_spisok = models;
  }











  // ����� ������ ���������
  vk_device = $("#" + NAME + "_device").vkSel({
    width:obj.width,
    title0:obj.device_no[obj.type_no],
    value:obj.device_id,
    spisok:G.device_spisok,
    func:function (id) {
      if (id == 0) {
        if (vk_vendor) { vk_vendor.val(0).remove(); }
      } else { getVendor(0); }
      if (vk_model) { vk_model.val(0).remove(); } // ��������� ������ ������ � ��������������� � 0, ���� ��� �����
      obj.func(getIds());
    },
    funcAdd:obj.device_funcAdd,
    bottom:3
  }).o;
  if (obj.device_id > 0) { getVendor(); }




  // ����� ������ ��������������
  function getVendor(vendor_id) {
    if (vendor_id != undefined) { obj.vendor_id = vendor_id; } // ���������� �������� �������������, ���� �����
    vk_vendor = $("#" + NAME + "_vendor").vkSel({
      width:obj.width,
      title0:obj.vendor_no[obj.type_no],
      value:obj.vendor_id,
      spisok:G.vendor_spisok[vk_device.val()], // �������� ���������� �������� �� ��� �������
      func:function (id) {
        if (id == 0) {
          if (vk_model) { vk_model.val(0).remove(); } // ��������� ������ ������ � ��������������� � 0
        } else { getModel(0); }
        obj.func(getIds());
      },
      funcAdd:obj.vendor_funcAdd,
      bottom:3
    }).o;
    if (obj.vendor_id > 0) { getModel(); }
  }


  // ����� ������ �������
  function getModel(model_id) {
    if (model_id != undefined) { obj.model_id = model_id; } // ���������� �������� ������, ���� �����
    vk_model = $("#" + NAME + "_model").vkSel({
      width:obj.width,
      ro:0,
      title0:obj.model_no[obj.type_no],
      value:obj.model_id,
      spisok:G.model_spisok[vk_vendor.val()],
      limit:50,
      funcAdd:obj.model_funcAdd,
      bottom:10,
      func:function () { obj.func(getIds()); }
    }).o;
  }


  // �������� �� ���������� ����� ��� �������� ������ ��������
  function name_test(spisok, name) {
    name = name.toLowerCase();
    for (var n = 0; n < spisok.length; n++) {
      if (spisok[n].title.toLowerCase() == name) return true;
    }
    return false;
  }


  function getIds() {
    return {
      device_id:vk_device.val(),
      vendor_id:vk_vendor ? vk_vendor.val() : 0,
      model_id:vk_model ? vk_model.val() : 0
    };
  }
};


