$.fn.fotoUpload = function (obj) {
  var t = $(this);
  var id = t.attr('id');

  if (!obj) { var obj = {}; }
  obj.name = obj.name || "Добавить изображение";
  if (!obj.owner) { throw new Error('Не указан владелец изображения - owner'); }
  obj.func = obj.func || function () {};
  obj.max_x = obj.max_x || null; // новое изображение выводится с размерами, ограниченными этими переменными
  obj.max_y = obj.max_y || null;

  t.html("<DIV class=foto_create_button id=foto_upload_" + id + ">" + obj.name + "</DIV>");

  var dialog; // общий диалог для всех видов загрузок


  // открытие диалога
  $("#foto_upload_" + id).click(function () {
    var dialog_place = "dialog_place_" + id;
    $(dialog_place).remove();
    $("#frameBody").append("<DIV id=" + dialog_place + "></DIV>");

    var html = "<DIV id=foto_upload_dialog>";
    html += "<DIV class=info>Поддерживаются форматы JPG, PNG, GIF и TIF.</DIV>";

    html += "<FORM method=post action='/include/foto/fotoUploadFile.php?" + G.values + "&owner=" + obj.owner + "' enctype='multipart/form-data' target=dialog_upload_frame id=dialog_form>";
    html += "<INPUT type=file id=file_name name=file_name>";
    html += "</FORM>";

    html += "<DIV id=choose_file>Выберите файл</DIV>";
    html += "<IFRAME src='' name=dialog_upload_frame></IFRAME>";

    var direct_value = 'или укажите прямую ссылку на изображение..';
    html += "<DIV id=direct><INPUT type=text id=direct_input value='" + direct_value + "'><A>oтправить</A></DIV>";
    html += "<DIV class=webcam>Вы также можете <A>сделать фотографию с вебкамеры »</A></DIV>";
    html += "</DIV>";

    dialog = $("#" + dialog_place).vkDialog({
      top:80,
      head:"Загрузка изображения",
      content:html,
      butSubmit:null,
      butCancel:'Закрыть'
    }).o;

    $("#foto_upload_dialog").click(function () { $("#error_msg").remove(); });




    // действие привыборе файла
    if (/MSIE/.test(window.navigator.userAgent)) {
      $("#file_name").hover(function () { $("#choose_file").css('background-color','#e9edf1'); }, function () { $("#choose_file").css('background-color','#eff1f3'); });
    } else {
      $("#choose_file").addClass('no_msie').click(function () { $("#file_name").click(); });
      $("#dialog_form").hide();
    }

    $("#file_name").change(function () {
      $("#choose_file").html("&nbsp;<IMG src=/img/upload.gif>");
      setCookie('fotoUpload', 'process');
      var timer = setInterval(uploadStart, 500);
      $("#dialog_form").submit();
      function uploadStart() {
        var cookie = getCookie('fotoUpload');
        if (cookie != 'process') {
          $("#choose_file").html("Выберите файл");
          clearInterval(timer);
          var arr = cookie.split('_');
          switch (arr[0]) {
          case 'uploaded':
            var param = getCookie('fotoParam').split('_');
            uploaded(param[0].replace(/%3A/, ':').replace(/%2F/g, '/'), param[1], param[2]);
            break;
          case 'error': error_print(arr[1]); break;
          }
        }
      }
    });








    // действие при загрузке изображения по прямой ссылке
    $("#direct_input").on({
      focus:function () { if ($(this).val() == direct_value) { $(this).val('').css('color', '#000'); } },
      blur:function () { if (!$(this).val()) { $(this).val(direct_value).css('color', '#777'); } },
      keydown:function (e) { var val = $(this).val(); if(val && e.keyCode == 13) { fotoLinkSend(); } }
    });

    $("#direct A:first").click(fotoLinkSend);

    function fotoLinkSend() {
      var link = $("#direct_input").val();
      if (link && link != direct_value) {
        $("#direct A:first").html("<IMG src=/img/upload.gif>&nbsp;");
        $.post("/include/foto/fotoUploadLink.php?" + G.values + "&owner=" + obj.owner, {link:link}, function (res) {
          $("#direct A:first").html("отправить");
          if (res.error) { error_print(res.error); } else { uploaded(res.link, res.x, res.y); }
        }, 'json');
      }
    }





    // вывод информации об ошибке в диалоговом окне
    function error_print(num) {
      $("#error_msg").remove();
      var cause = "не известна";
      if (num == 1) cause = "неверный формат файла";
      if (num == 2) cause = "слишком маленький размер изображения.<BR>Допустимый размер не менее 100x100 px";
      $("#foto_upload_dialog .webcam").after("<DIV id=error_msg>Не удалось загрузить изображение.<BR>Причина: " + cause + ".</DIV>");
    }

    $("#foto_upload_dialog .webcam A:first").click(function () { dialog.close(); camera(); });
  }); // открытие диалога






  // действие при успешном сохранении изображения на сервер
  function uploaded(link, x, y) {
    dialog.close();
    vkMsgOk("Изображение успешно загружено!");
    var send = {link:link, x:x, y:y, dtime:'сегодня'};
    if (obj.max_x && x > obj.max_x) { x = obj.max_x; y = Math.round(send.y / send.x * obj.max_x); }
    if (obj.max_y && y > obj.max_y) { y = obj.max_y; x = Math.round(send.x / send.y * obj.max_y); }
    send.img = "<IMG src='" + send.link + "-big.jpg' width="+ x +" height=" + y + ">";
    obj.func(send);
  }





  var webcam = {
    screen:null, // тег, в который помещается изображение с камеры
    show:function (width, height) { // вывод изображения на экран
      var flashvars = 'shutter_enabled=0&width=' + width + '&height=' + height + '&server_width=' + width + '&server_height=' + height;
      var html;
      if (false) {
    /*    html = '<object ' +
          'id="webcam_movie" ' +
          'width="' + width + '" ' +
          'height="' + height + '" ' +
          'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ' +
          'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" ' +
          'align="middle">' +
            '<param name="allowScriptAccess" value="always" />' +
            '<param name="allowFullScreen" value="false" />' +
            '<param name="movie" value="/include/foto/webcam.swf" />' +
            '<param name="loop" value="false" />' +
            '<param name="menu" value="false" />' +
            '<param name="quality" value="best" />' +
            '<param name="bgcolor" value="#ffffff" />' +
            '<param name="flashvars" value="' + flashvars + '"/>' +
          '</object>';*/
      }
      else {
        html = '<embed ' +
          'id="webcam_movie" ' +
          'width="' + width + '" ' +
          'height="' + height + '" ' +
          'src="/include/foto/webcam.swf" ' +
          'loop="false" ' +
          'menu="false" ' +
          'quality="best" ' +
          'bgcolor="#ffffff" ' +
          'name="webcam_movie" ' +
          'align="middle" ' +
          'allowScriptAccess="always" ' +
          'allowFullScreen="false" ' +
          'type="application/x-shockwave-flash" ' +
          'pluginspage="http://www.macromedia.com/go/getflashplayer" ' +
          'flashvars="'+flashvars+'" />';
      }
    this.screen.html(html);
    },
    reset:function () { this.screen.html(''); }
  };





  // диалог с вебкамерой
  function camera() {
    $("#webcam").remove();
    $("#frameBody").append("<DIV id=webcam></DIV>");

    dialog = $("#webcam").vkDialog({
      top:20,
      width:610,
      head:"Создание снимка с вебкамеры",
      content:"<DIV id=screen></DIV>",
      butSubmit:'Сделать снимок',
      butCancel:'Закрыть',
      submit:submit
    }).o;

    webcam.screen =  $("#screen");
    webcam.show(608, 457);

    var dialogWeb = $("#vk_dialog_webcam");

    $("#webcam .content:first").resizable({
      minWidth: 322,
      maxWidth: 608,
      minHeight: 240,
      maxHeight: 457,
      resize:function (b, a) {
        var w = a.size.width;
        var diff = a.originalSize.width - w;
        if (diff != 0) {
          w -= diff;
          if (w < 322) w = 322;
          if (w > 608) w = 608;
          a.size.width = w;
          dialogWeb.css({left:(313 - Math.round(a.size.width / 2)) + 'px', width:w + 'px'});
          $(this).width('auto');
        }
      },
      start:function () { webcam.reset(); },
      stop:function () { webcam.screen.height($(this).height()); webcam.show($(this).width(), $(this).height()); }
    });

    function submit() {
      dialog.process();
      setCookie('fotoUpload', 'process');
      setCookie('fotoParam', '12312');
      var timer = setInterval(uploadStart, 500);
      document.getElementById('webcam_movie')._snap("/include/foto/fotoUploadWebcam.php?" + G.values + "&owner=" + obj.owner, 100, 0, 0);
      function uploadStart() {
        var cookie = getCookie('fotoUpload');
        if (cookie != 'process') {
          clearInterval(timer);
          var arr = cookie.split('_');
          switch (arr[0]) {
          case 'uploaded':
            var param = getCookie('fotoParam').split('_');
            uploaded(param[0].replace(/%3A/, ':').replace(/%2F/g, '/'), param[1], param[2]);
            break;
          case 'error': break;
          }
        }
      }
    } // end submit
  } // end camera
};
















// Вставка изображения в определённый тег
$.fn.fotoSet = function (obj) {
  if (!obj) { throw new Error("Отсутствуют переменные для fotoSet"); }
  if (!obj.foto) { throw new Error("Отсутствуют данные фотографии для fotoSet"); }

  var foto = obj.foto;                   // данные фотографии
  var max_x = obj.max_x || null;  // уменьшить до размера по X, если нужно
  var max_y = obj.max_y || null;  // то же по Y
  var val = obj.val || '';                // вставить val для клика
  var click = obj.click || null;       // действие при клике на фотографию

  var x = foto.x;
  var y = foto.y;
  if (max_x && x > max_x) { x = max_x; y = Math.round(foto.y / foto.x * max_x); }
  if (max_y && y > max_y) { y = max_y; x = Math.round(foto.x / foto.y * max_y); }
  $(this).html("<IMG src='" + foto.link + "-big.jpg' width=" + x +" height=" + y + " val='"+ val +"'>");
  if (click) { $(this).find('IMG:first').click(click); }
};
















// просмотр фотографий
G.fotoView = function (obj) {
  if (!obj) { var obj = {}; }
  obj.spisok = obj.spisok || null;
  num = obj.num || 0; // текущий номер в мессиве выводимого изображения

  $("#foto_view").remove();
  $("#frameBody").append("<DIV id=foto_view></DIV>");

  var html ="<DIV id=foto_content val=end_>";
  html +="<DIV id=foto_head><EM></EM><A val=fotoClose_>Закрыть</A></DIV>";
  html +="<DIV id=foto_image val=" + (obj.spisok.length > 1 ? "fotoNext_" : "fotoClose_") + "></DIV>";
  html +="<DIV id=foto_hide></DIV>";
  html +="<DIV id=foto_about></DIV>";
  html +="</DIV>";

  $("#foto_view").html(html);

  var foto = $("#foto_content");

  fotoPrint();

  foto
    .css({top:0 + 'px', 'z-index':G.zindex + 5})
    .on('click', function (e) {
        var val = $(e.target).attr('val');
        var n = 1;
        while (val == undefined) {
          val = $(e.target).parent().attr('val');
          n--;
          if (n < 0) break;
        }
        if (val) {
          val = val.split('_');
          switch (val[0]) {
          case 'fotoClose': $(this).remove(); frameBodyHeightSet(); break;
          case 'fotoNext': fotoPrint(); break;
          }
        }
    });


  function fotoPrint() {
    var sp = obj.spisok[num]; 
    $("#foto_image").html("<IMG src='" + sp.link + "-big.jpg' width=" + sp.x +" height=" + sp.y + ">");
    var len = obj.spisok.length;
    $("#foto_head EM:first").html(len > 1 ? "Фотография " + (num + 1) + " из " + len : "Просмотр фотографии");
    $("#foto_about").html("<DIV class=dtime>Добавлена " + sp.dtime + "</DIV>");

    var h = foto.height();
    $("#frameBody").height(h);
    frameBodyHeightSet(h);

    num++;
    if (num == len) { num = 0; }
    if (len > 1) { // если фотографий больше одной, прячется следующая для предзагрузки
      sp = obj.spisok[num];
      $("#foto_hide").html("<IMG src='" + sp.link + "-big.jpg' width=" + sp.x +" height=" + sp.y + ">");
    }
  }
};







