// диалог 2013-07-23 14:46
function vkDialog(obj) {
    G.zindex += 10;
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
        })()
    }
}