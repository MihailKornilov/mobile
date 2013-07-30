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
        bottom:(function() {
            return dialog.find('.bottom');
        })(),
        content:(function() {
            return dialog.find('.content');
        })()
    }
}

$(document).on('click', '.check0,.check1', function() {
    var cl = Math.abs($(this).attr('class').split('check')[1] - 1);
    $(this)
        .attr('class', 'check' + cl)
        .prev().val(cl);
});

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
        "<TABLE cellpadding=0 cellspacing=0>" +
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
}; // end of years
