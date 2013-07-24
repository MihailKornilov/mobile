var REGEXP_NUMERIC = /^\d+$/,
    AJAX_MAIN = 'http://' + G.domain + '/ajax/main.php?' + G.values,
    reportPrihodLoad = function(data) {
        var send = {
            op:'report_prihod_load',
            day_begin:$('#report_prihod_day_begin').val(),
            day_end:$('#report_prihod_day_end').val(),
            del_show:$('#prihodShowDel').val() == 1 ? 1 : 0
        };
        $('.rightLinks a.sel').append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_prihod').html(res.html);
            $('.rightLinks a.sel img').remove();
        }, 'json');
    };


$(document).ajaxError(function(event, request, settings) {
    if(!request.responseText)
        return;
    alert('Ошибка:\n\n' + request.responseText);
});

$(document).on('click', '.check0,.check1', function() {
    var cl = Math.abs($(this).attr('class').split('check')[1] - 1);
    $(this)
        .attr('class', 'check' + cl)
        .prev().val(cl);
});

$(document)
    .on('click', '#report_prihod_next', function() {
    if($(this).hasClass('busy'))
        return;
    var next = $(this),
        send = {
            op:'report_prihod_next',
            day_begin:$('#report_prihod_day_begin').val(),
            day_end:$('#report_prihod_day_end').val(),
            del_show:$('#prihodShowDel').val() == 1 ? 1 : 0,
            page:$(this).attr('val')
        };
    next.addClass('busy');
    $.post(AJAX_MAIN, send, function (res) {
        if(res.success) {
            next.remove();
            $('#report_prihod .tabSpisok').append(res.html);
        } else
            next.removeClass('busy');
    }, 'json');
})
    .on('click', '#report_prihod .summa_add', function() {
            var html = '<TABLE cellpadding="0" cellspacing="8" id="report_prihod_add">' +
                '<TR><TD class=tdAbout>Содержание:<TD><INPUT type="text" id="about" maxlength="100">' +
                '<TR><TD class=tdAbout>Сумма:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> руб.' +
                '<TR><TD class=tdAbout>Деньги поступили в кассу?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
                '</TABLE>';
        var dialog = vkDialog({
                width:380,
                head:"Внесение поступления средств",
                content:html,
                submit:submit
            }),
            kassa = $('#report_prihod_add #kassa'),
            sum = $("#report_prihod_add #sum"),
            about = $("#report_prihod_add #about");

            kassa.vkRadio({
                display:'inline-block',
                right:15,
                spisok:[{uid:1, title:'да'},{uid:0, title:'нет'}]
            });
            about.focus();

            function submit() {
                var send = {
                    op:'report_prihod_add',
                    about:about.val(),
                    sum:sum.val(),
                    kassa:kassa.val()
                };
                var msg;
                if(!send.about) { msg = "Не указано содержание."; about.focus(); }
                else if(!REGEXP_NUMERIC.test(send.sum)) { msg = "Некорректно указана сумма."; sum.focus(); }
                else if(send.kassa == -1) { msg = "Укажите, деньги взяты из кассы или нет."; }
                else {
                    dialog.process();
                    $.post(AJAX_MAIN, send, function (res) {
                        if(res.success) {
                            dialog.close();
                            vkMsgOk("Новое поступление внесено.");
                            reportPrihodLoad();
                        }
                    }, 'json');
                }
                if(msg)
                    dialog.bottom.vkHint({
                        msg:'<SPAN class="red">' + msg + '</SPAN>',
                        remove:1,
                        indent:40,
                        show:1,
                        top:-53,
                        left:103,
                        correct:0
                    });
            }
    })
    .on('click', '#report_prihod .img_del', function() {
        var send = {
            op:'report_prihod_del',
            id:$(this).attr('val')
        };
        $(this).parent().parent().html('<td colspan="4" class="deleting">Удаление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                reportPrihodLoad();
                vkMsgOk("Удаление произведено.");
            }
        }, 'json');
    })
    .on('click', '#report_prihod .img_rest', function() {
        var send = {
            op:'report_prihod_rest',
            id:$(this).attr('val')
        };
        $(this).parent().parent().html('<td colspan="4" class="deleting">Восстановление... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                reportPrihodLoad();
                vkMsgOk("Восстановление произведено.");
            }
        }, 'json');
    })
    .on('click', '#prihodShowDel_check', reportPrihodLoad);

$(document).ready(function() {
    $('#report_prihod_day_begin').vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
    $("#report_prihod_day_end").vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
});