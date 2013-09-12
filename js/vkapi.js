$(document).ready(function() {
    if($('.vkComment').length > 0) {
        $(document)
            .on('click focus', '.vkComment .add TEXTAREA,.vkComment .cadd TEXTAREA', function() {
                var t = $(this),
                    but = t.next(),
                    val = t.val();
                if(but.is(':hidden')) {
                    t.val('')
                     .attr('val', val)
                     .css('color','#000')
                     .height(26)
                     .autosize();
                    but.show()
                        .css('display','inline-block');
                }
            })
            .on('blur', '.vkComment .add TEXTAREA,.vkComment .cadd TEXTAREA', function() {
                var t = $(this);
                if(!t.val()) {
                    if(t.parent().parent().hasClass('empty')) {
                        t.parent().parent().hide()
                         .parent().find('span').show();
                        return;
                    }
                    var val = t.attr('val');
                    t.val(val)
                        .css('color','#777')
                        .height(13)
                        .next().hide();
                }
            })
            .on('click', '.vkComment span a', function() {
                var t = $(this),
                    cdop = t.parent().parent().next();
                t.parent().hide();
                cdop.show();
                if(cdop.hasClass('empty'))
                    cdop.find('textarea').focus()
            })
            .on('click', '.vkComment .add .vkButton', function() {
                var t = $(this);
                if(t.hasClass('busy'))
                    return;
                var val = t.parent().parent().attr('val').split('_'),
                    send = {
                        op:'vkcomment_add',
                        table:val[0],
                        id:val[1],
                        txt:$.trim(t.prev().val())
                    };
                if(!send.txt)
                    return;
                t.addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    t.removeClass('busy').hide();
                    var val = t.prev().attr('val');
                    t.prev()
                        .val(val)
                        .css('color', '#777')
                        .height(13);
                    t.parent().after(res.html);
                }, 'json');
            })
            .on('click', '.vkComment .cadd .vkButton', function() {
                var t = $(this);
                if(t.hasClass('busy'))
                    return;
                var p = t.parent(),
                    pid,
                    val;
                for(var n = 0; n < 10; n++) {
                    p = p.parent();
                    if(p.hasClass('cunit'))
                        pid = p.attr('val');
                    if(p.hasClass('vkComment')) {
                        val = p.attr('val').split('_');
                        break;
                    }
                }
                var send = {
                        op:'vkcomment_add_child',
                        table:val[0],
                        id:val[1],
                        txt:$.trim(t.prev().val()),
                        parent:pid
                    };
                if(!send.txt)
                    return;
                t.addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    t.removeClass('busy').hide();
                    var val = t.prev().attr('val');
                    t.prev()
                        .val(val)
                        .css('color', '#777')
                        .height(13);
                    t.parent().before(res.html)
                     .parent().removeClass('empty');
                }, 'json');
            })
            .on('click', '.vkComment .unit_del', function() {
                var u = $(this);
                while(!u.hasClass('cunit'))
                    u = u.parent();
                if(u.hasClass('busy'))
                    return;
                var id = u.attr('val'),
                    send = {
                        op:'vkcomment_del',
                        id:id
                    };
                u.addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    u.removeClass('busy');
                    if(res.success)
                        u.find('table:first').hide()
                         .before('<div class="deleted">Заметка удалена. <a class="unit_rest" val="' + id + '">Восстановить</a></div>');
                }, 'json');
            })
            .on('click', '.vkComment .unit_rest,.vkComment .child_rest', function() {
                var t = $(this);
                if(t.hasClass('busy'))
                    return;
                var send = {
                    op:'vkcomment_rest',
                    id:t.attr('val')
                };
                t.addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    t.parent().next().show();
                    t.parent().remove()
                }, 'json');
            })
            .on('click', '.vkComment .child_del', function() {
                var p = $(this);
                while(!p.hasClass('child'))
                    p = p.parent();
                if(p.hasClass('busy'))
                    return;
                var id = p.attr('val'),
                    send = {
                        op:'vkcomment_del',
                        id:id
                    };
                p.addClass('busy');
                $.post(AJAX_MAIN, send, function(res) {
                    p.removeClass('busy');
                    if(res.success)
                        p.find('table:first').hide()
                         .before('<div class="deleted">Комментарий удалён. <a class="child_rest" val="' + id + '">Восстановить</a></div>');
                }, 'json');
            });
    }
});


// диалог 2013-07-23 14:46
function vkDialog(obj) {
    var t = $(this);
    var id = t.attr('id');
    obj = $.extend({
        width:360,
        top:100,              // отступ сверху с учётом скрола
        head:'head: Название заголовка',
        content:'content: содержимое центрального поля',
        submit:function() {}, // функция, выполняющаяся при нажатии синей кнопки
        cancel:function() {}, // функция, выполняющаяся при нажатии кнопки отмена
        butSubmit:'Внести',
        butCancel:'Отмена'
    }, obj);

    var html = '<DIV class="vk_dialog">' +
            '<DIV class="head"><DIV><A class="img_del"></A>' + obj.head + '</DIV></DIV>' +
            '<DIV class="content">' + obj.content + '</DIV>' +
            '<DIV class="bottom">' +
                (obj.butSubmit ? '<DIV class="vkButton img_upload"><button>' + obj.butSubmit + '</button></DIV>' : '') +
                (obj.butCancel ? '<DIV class="vkCancel"><BUTTON>' + obj.butCancel + '</BUTTON></DIV>' : '') +
            "</DIV>" +
        "</DIV>";

    var dialog = $('body').append(html).find('.vk_dialog:last');
    dialog.find('.img_del').click(dialogClose);
    var butSubmit = dialog.find('.vkButton');
    butSubmit.find('button').click(obj.submit);
    dialog.find('.vkCancel').click(function() { obj.cancel(); dialogClose(); });

    G.backfon();

    dialog
        .css({
            width:obj.width + 'px',
            top:$(window).scrollTop() + G.vkScroll + obj.top + 'px',
            left:313 - Math.round(obj.width / 2) + 'px',
            'z-index':G.zindex + 5
        });


    function dialogClose() {
        dialog.remove();
        G.backfon(false);
    }

    return {
        close:dialogClose,
        process:function() {
            butSubmit.addClass('busy');
        },
        abort:function() {
            butSubmit.removeClass('busy');
        },
        bottom:(function() {
            return dialog.find('.bottom');
        })(),
        content:(function() {
            return dialog.find('.content');
        })()
    }
}

$(document).on('click', '.check0,.check1', function() {
    var t = $(this),
        cl = Math.abs(t.attr('class').split('check')[1] - 1),
        inp = $('#' + t.attr('id').split('_check')[0]);
    t.attr('class', 'check' + cl);
    inp.val(cl);
});

$(document).on('click', '.fotoView', function() {
    $('#foto_view').remove();
    var t = $(this),
        html ='<DIV id="foto_view">' +
            '<DIV class="head"><EM><img src="/img/upload.gif"></EM><A>Закрыть</A></DIV>' +
            '<table class="image"><tr><td><img src="' + t.attr('src').replace('small', 'big') + '"></table>' +
            '<DIV class="about"><DIV class="dtime"></DIV></DIV>' +
            '<DIV class="hide"></DIV>' +
            '</DIV>';
    $("#frameBody").append(html);

    var f = $('#foto_view');
    fotoHeightSet();
    f.find('.head a').on('click', fotoClose);

    var owner = t.attr('val'),
        send = {
            op:'foto_load',
            owner:owner
        };
    if(!window.fotoViewImages || window.fotoViewOwner != owner) {
        $.post(AJAX_MAIN, send, function(res) {
            window.fotoViewImages = res.img;
            window.fotoViewNum = 0;
            window.fotoViewOwner = owner;
            fotoShow();
            fotoClick();
        }, 'json');
    } else {
        fotoShow();
        fotoClick();
    }


    function fotoShow() {
        var len = window.fotoViewImages.length,
            num = window.fotoViewNum,
            nextNum = num + 1 >= len ? 0 : num + 1,
            img = window.fotoViewImages[num];
        f.find('.head em').html(len > 1 ? 'Фотография ' + (num + 1) + ' из ' + len : 'Просмотр фотографии');
        f.find('.dtime').html('Добавлена ' + img.dtime);
        f.find('.image img')
            .attr('src', img.link)
            .attr('width', img.x)
            .attr('height', img.y)
            .on('load', fotoHeightSet);
        f.find('.hide').html('<img src="' + window.fotoViewImages[nextNum].link + '">');
    }
    function fotoClick() {
        f.find('.image').on('click', function() {
            var len = window.fotoViewImages.length;
            if(len == 1)
                fotoClose();
            else {
                window.fotoViewNum++;
                if(window.fotoViewNum >= len)
                    window.fotoViewNum = 0;
                fotoShow();
            }
        });
    }
    function fotoClose() {
        window.fotoViewNum = 0;
        f.remove();
        frameBodyHeightSet();
    }
    function fotoHeightSet() {
        var h = f.height();
        $("#frameBody").height(h);
        frameBodyHeightSet(h);
    }
});
$.fn.fotoUpload = function(obj) {
    obj = $.extend({
        owner:false,
        func:function() {}
    }, obj);

    if(!obj.owner)
        throw new Error('Не указан владелец изображения - <b>owner</b>');

    var t = $(this),
        IMAGE_UPLOAD = 'http://' + G.domain + '/include/imageUpload.php?' + G.values + "&owner=" + obj.owner,
        dialog,
        webDialog,
        timer,
        choose,
        direct,
        direct_a,
        webcam = {
            screen:null, // тег, в который помещается изображение с камеры
            show:function(width, height) { // вывод изображения на экран
                var flashvars = 'shutter_enabled=0&width=' + width + '&height=' + height + '&server_width=' + width + '&server_height=' + height;
                var html = '<embed ' +
                    'id="webcam_movie" ' +
                    'width="' + width + '" ' +
                    'height="' + height + '" ' +
                    'src="http://' + G.domain + '/include/webcam.swf" ' +
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
                    'flashvars="' + flashvars + '" />';
                this.screen.html(html);
            },
            reset:function() { this.screen.html(''); }
        };

    t.on('click', function() {
        var html = '<DIV id="fotoUpload">' +
            '<DIV class="info">Поддерживаются форматы JPG, PNG и GIF.</DIV>' +
            '<FORM method="post" action="' + IMAGE_UPLOAD + '" enctype="multipart/form-data" target="upload_frame">' +
                '<INPUT type="file" id="file_name" name="file_name" />' +
                '<INPUT type="hidden" name="op" value="file" />' +
            '</FORM>' +

            '<DIV id="choose_file">Выберите файл</DIV>' +
            '<IFRAME name="upload_frame"></IFRAME>' +
            '<DIV id="direct"><INPUT type="text" id="direct_input" placeholder="или укажите прямую ссылку на изображение.."><a><span>oтправить</span></a></DIV>' +
            '<DIV class="webcam">Вы также можете <A>сделать фотографию с вебкамеры »</A></DIV>' +
        '</DIV>';
        dialog = vkDialog({
            top:80,
            head:"Загрузка изображения",
            content:html,
            butSubmit:null,
            butCancel:'Закрыть'
        });
        var form = $("#fotoUpload form"),
            name = $("#file_name");
        choose = $("#choose_file");
        direct = $('#direct_input');
        direct_a = direct.next();

        if(/MSIE/.test(window.navigator.userAgent)) {
            name.on({
                mouseenter:function () { choose.css('background-color','#e9edf1'); },
                mouseleave:function () { choose.css('background-color','#eff1f3'); }
            });
        } else {
            choose
                .addClass('no_msie')
                .on('click', function() { name.click(); });
            form.hide();
        }

        name.change(function () {
            choose.html('&nbsp;<IMG src=/img/upload.gif>');
            setCookie('fotoUpload', 'process');
            timer = setInterval(uploadStart, 500);
            form.submit();
        });

        // действие при загрузке изображения по прямой ссылке
        direct.keyEnter(fotoLinkSend);
        direct_a.click(fotoLinkSend);

        $('#fotoUpload .webcam a').click(camera);
    });

    function uploadStart() {
        var cookie = getCookie('fotoUpload');
        if(cookie != 'process') {
            if(webDialog)
                webDialog.close();
            choose.html("Выберите файл");
            clearInterval(timer);
            var arr = cookie.split('_');
            switch(arr[0]) {
                case 'uploaded':
                    var param = getCookie('fotoParam').split('_');
                    uploaded(param[0].replace(/%3A/, ':').replace(/%2F/g, '/'), param[1], param[2]);
                    break;
                case 'error': error_print(arr[1]); break;
            }
        }
    }
    // действие при успешном сохранении изображения на сервер
    function uploaded(link, x, y) {
        dialog.close();
        vkMsgOk("Изображение успешно загружено!");
        window.fotoViewImages = false;
        var send = {
            link:link,
            x:x,
            y:y,
            dtime:'сегодня'
        };
        if(obj.max_x && x > obj.max_x) {
            x = obj.max_x;
            y = Math.round(send.y / send.x * obj.max_x);
        }
        if(obj.max_y && y > obj.max_y) {
            y = obj.max_y;
            x = Math.round(send.x / send.y * obj.max_y);
        }
        send.img = '<IMG src="' + send.link + '-big.jpg" width="' + x + '" height="' + y + '">';
        obj.func(send);
    }
    // вывод информации об ошибке в диалоговом окне
    function error_print(num) {
        $("#error_msg").remove();
        var cause = "не известна";
        if(num == 1) cause = 'неверный формат файла';
        if(num == 2) cause = 'слишком маленький размер изображения.<BR>Допустимый размер не менее 100x100 px';
        $('#fotoUpload .webcam').after('<DIV id="error_msg">Не удалось загрузить изображение.<BR>Причина: ' + cause + '.</DIV>');
    }

    function fotoLinkSend() {
        if(direct_a.hasClass('busy'))
            return;
        var link = direct.val();
        if(!link)
            return;
        var send = {
            op:'link',
            link:link
        };
        direct_a.addClass('busy');
        $.post(IMAGE_UPLOAD, send, function (res) {
            direct_a.removeClass('busy');
            if(res.error)
                error_print(res.error);
            else
                uploaded(res.link, res.x, res.y);
        }, 'json');
    }
    // диалог с вебкамерой
    function camera() {
        webDialog = vkDialog({
            top:20,
            width:610,
            head:"Создание снимка с вебкамеры",
            content:'<DIV id="screen"></DIV>',
            butSubmit:'Сделать снимок',
            butCancel:'Закрыть',
            submit:submit
        });
        webDialog.content.css({
            padding:0,
            height:457 + 'px'
        });
        webcam.screen = $('#screen');
        webcam.show(608, 457);
        webDialog.content.resizable({
            minWidth: 322,
            maxWidth: 608,
            minHeight: 240,
            maxHeight: 457,
            resize:function(b, a) {
                var w = a.size.width;
                var diff = a.originalSize.width - w;
                if(diff != 0) {
                    w -= diff;
                    if(w < 322) w = 322;
                    if(w > 608) w = 608;
                    a.size.width = w;
                    webDialog.content.parent().css({
                        left:(313 - Math.round(a.size.width / 2)) + 'px',
                        width:w + 'px'
                    });
                    $(this).width('auto');
                }
            },
            start:function() { webcam.reset(); },
            stop:function() {
                var h = webDialog.content.height();
                var w = webDialog.content.width();
                webcam.screen.height(h);
                webcam.show(w, h);
            }
        });

        function submit() {
            webDialog.process();
            setCookie('fotoUpload', 'process');
            timer = setInterval(uploadStart, 500);
            document.getElementById('webcam_movie')._snap(IMAGE_UPLOAD, 100, 0, 0);
        }
    }
};


// перелистывание годов
$.fn.years = function(obj) {
    obj = $.extend({
        year:(new Date()).getFullYear(),
        start:function () {},
        func:function () {}
    }, obj);

    var t = $(this);
    var id = t.attr('id');

    var html = "<DIV class=years id=years_" + id + ">" +
        "<TABLE>" +
        "<TR><TD class=but>&laquo;<TD id=ycenter><SPAN>" + obj.year + "</SPAN><TD class=but>&raquo;" +
        "</TABLE></DIV>";
    t.after(html);
    t.val(obj.year);

    var years = {
        left:0,
        speed:2,
        span:$("#years_" + id + " #ycenter SPAN"),
        width:Math.round($("#years_" + id + " #ycenter").css('width').split(/px/)[0] / 2),  // ширина центральной части, где год
        ismove:0
    };
    years.next = function (side) {
        obj.start();
        var y = years;
        if (y.ismove == 0) {
            y.ismove = 1;
            var changed = 0;
            var timer = setInterval(function () {
                var span = y.span;
                y.left -= y.speed * side;

                if (y.left > 0 && changed == 1 && side == -1 ||
                    y.left < 0 && changed == 1 && side == 1) {
                    y.left = 0;
                    y.ismove = 0;
                    y.speed = 0;
                    clearInterval(timer);
                }

                span[0].style.left = y.left + 'px';
                y.speed += 2;

                if (y.left > y.width && changed == 0 && side == -1 ||
                    y.left < -y.width && changed == 0 && side == 1) {
                    changed = 1;
                    obj.year += side;
                    span.html(obj.year);
                    y.left = y.width * side;
                    t.val(obj.year);
                    obj.func(obj.year);
                }
            }, 25);
        }
    };

    $("#years_" + id + " .but:first").mousedown(function () { allmon = 1; years.next(-1); });
    $("#years_" + id + " .but:eq(1)").mousedown(function () { allmon = 1; years.next(1); });
};//end of years

$.fn.keyEnter = function(func) {
    $(this).keydown(function(e) {
        if(e.keyCode == 13)
            func();
    });
    return $(this);
};