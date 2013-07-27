var REGEXP_NUMERIC = /^\d+$/,
    AJAX_MAIN = 'http://' + G.domain + '/ajax/main.php?' + G.values,
    reportHistoryLoad = function(data) {
        var send = {
            op:'report_history_load',
            worker:$('#report_history_worker').val(),
            action:$('#report_history_action').val()
        };
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_history').parent().html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
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
    alert('������:\n\n' + request.responseText);
});

$(document).on('click', '.check0,.check1', function() {
    var cl = Math.abs($(this).attr('class').split('check')[1] - 1);
    $(this)
        .attr('class', 'check' + cl)
        .prev().val(cl);
});

$(document)
    .on('click', '#report_history_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_history_next',
                worker:$('#report_history_worker').val(),
                action:$('#report_history_action').val(),
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_history').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    });

$(document)
    .on('click', '#report_remind .info a', function() {
        var info = $(this).parent(),
            show = info.hasClass('show');
        info[(show ? 'remove' : 'add') + 'Class']('show');
    })
    .on('mouseenter', '#report_remind .unit', function() {
        $(this).find('.action').show();
    })
    .on('mouseleave', '#report_remind .unit', function() {
        $(this).find('.action').hide();
    })
    .on('click', '#report_remind .hist_a', function() {
        $(this).parent().parent().find('.hist').slideToggle();
    })
    .on('click', '.report_remind_add', function() {
        var html = '<TABLE class="remind_add_tab">' +
            '<TR><TD class=tdAbout>����������:<TD><INPUT type=hidden id=destination>' +
            '<TR><TD class=tdAbout id=target_name><TD id=target>' +
            '</TABLE>' +

            '<TABLE class=remind_add_tab id=tab_content>' +
            '<TR><TD class=tdAbout>�������:<TD><TEXTAREA id=txt></TEXTAREA>' +
            '<TR><TD class=tdAbout>������� ���� ����������:<TD><INPUT type=hidden id=data>' +
            '<TR><TD class=tdAbout>������:<TD><INPUT type=hidden id=private>' +
            '</TABLE>';
        var dialog = vkDialog({
            top:30,
            width:480,
            head:'���������� ������ �������',
            content:html,
            butSubmit:'��������',
            submit:submit
        });

        var dest = $('#destination').vkSel({
            width:150,
            title0:'�� �������',
            spisok:[
                {uid:1,title:'������'},
                {uid:2,title:'������'},
                {uid:3,title:'������������ �������'}
            ],
            func:destination
        }).o;

        $('#tab_content #txt').autosize();
        $('#tab_content #data').vkCalendar();
        $('#tab_content #private').myCheck();
        $('#tab_content #check_private').vkHint({
            msg:'������� �������<BR>������ ������ ��.',
            top:-71,
            left:-11,
            indent:'left',
            delayShow:1000
        });

        function destination(id) {
            $('#target').html('');
            $('#target_name').html('');
            $('#tab_content #txt').val('');
            $('#tab_content').css('display', id > 0 ? 'block' : 'none');
            // ���� ������ ������, ����������� ������ � ���������
            if (id == 1) {
                $('#target_name').html('������:');
                $('#target').html('<DIV id=client_id></DIV>');
                $('#client_id').clientSel();
            }
            // ���� ������� ������
            if (id == 2) {
                $('#target_name').html('����� ������:');
                $('#target').html('<INPUT type=text id=zayav_nomer><INPUT type=hidden id=zayav_id value=0><SPAN id=img></SPAN><DIV id=zayav_find></DIV>');
                $('#zayav_nomer').focus().on('keyup', function () {
                    $('#zayav_id').val(0);
                    $('#zayav_find').html('');
                    var val = $(this).val();
                    if (REGEXP_NUMERIC.test(val)) {
                        $('#img').imgUp();
                        $.getJSON('/remont/zp/view/AjaxZayavFind.php?' + G.values + '&nomer=' + val, function (res) {
                            if(res.id > 0) {
                                html = '<TABLE cellpadding=0 cellspacing=5><TR>' +
                                    '<TD><A href="index.php?' + G.values + '&my_page=remZayavkiInfo&id=' + res.id + '"><IMG src="' + res.img + '" height=40></A>' +
                                    '<TD><A href="index.php?' + G.values + '&my_page=remZayavkiInfo&id=' + res.id + '">' +
                                        G.category_ass[res.category] + '<BR>' +
                                        G.device_ass[res.device_id] + '<BR>' +
                                        G.vendor_ass[res.vendor_id] + ' ' +
                                        G.model_ass[res.model_id] + '</A>' +
                                    '</TABLE>';
                                $('#zayav_id').val(res.id);
                                $('#zayav_find').html(html);
                                $('#img').html('');
                            } else {
                                $('#img').html('������ �� �������.');
                            }
                        });
                    } else {
                        $('#img').html('������������ ����');
                    }
                });
            }
        }//destination()

        function submit() {
            var client_id = dest.val() == 1 ? $('#client_id').val() : 0,
                zayav_id = dest.val() == 2 ? $('#zayav_id').val() : 0,
                send = {
                    op:'report_remind_add',
                    client_id:client_id,
                    zayav_id:zayav_id,
                    txt:$('#tab_content #txt').val(),
                    day:$('#tab_content #data').val(),
                    private:$('#tab_content #private').val()
                },
                msg;
            if(dest.val() == 0) msg = '�� ������� ����������.';
            else if($('#client_id').length > 0 && send.client_id == 0) msg = '�� ������ ������.';
            else if($('#zayav_id').length > 0 && send.zayav_id == 0) msg = '�� ������ ����� ������.';
            else if(!send.txt) msg = '�� ������� ���������� �����������.';
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk('����� ������� ������� ���������.');
                        $('#report_remind #spisok').html(res.html);
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class=red>' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-48,
                    left:150
                });
        }//submit()
        return false;
    })
    .on('click', '#report_remind_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_remind_next',
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_remind #spisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');

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
            '<TR><TD class=tdAbout>����������:<TD><INPUT type="text" id="about" maxlength="100">' +
            '<TR><TD class=tdAbout>�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> ���.' +
            '<TR><TD class=tdAbout>������ ��������� � �����?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
            '</TABLE>';
        var dialog = vkDialog({
                width:380,
                head:"�������� ����������� �������",
                content:html,
                submit:submit
            }),
            kassa = $('#report_prihod_add #kassa'),
            sum = $("#report_prihod_add #sum"),
            about = $("#report_prihod_add #about");

        kassa.vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'��'},{uid:0, title:'���'}]
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
            if(!send.about) { msg = "�� ������� ����������."; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = "����������� ������� �����."; sum.focus(); }
            else if(send.kassa == -1) { msg = "�������, ������ ����� �� ����� ��� ���."; }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("����� ����������� �������.");
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
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">��������... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                vkMsgOk("�������� �����������.");
                if($('#prihodShowDel').val() == 1)
                    tr.addClass('deleted')
                      .html(trSave)
                      .find('.del').attr('title', '������������ �����').attr('class', 'rest')
                      .find('.img_del').attr('class', 'img_rest');
                else
                    tr.remove();
            }
        }, 'json');
    })
    .on('click', '#report_prihod .img_rest', function() {
        var send = {
            op:'report_prihod_rest',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">��������������... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                vkMsgOk("�������������� �����������.");
                tr.removeClass('deleted')
                    .html(trSave)
                    .find('.rest').attr('title', '������� �����').attr('class', 'del')
                    .find('.img_rest').attr('class', 'img_del');
            }
        }, 'json');
    })
    .on('click', '#prihodShowDel_check', reportPrihodLoad);

$(document).ready(function() {
    $('#report_prihod_day_begin').vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
    $("#report_prihod_day_end").vkCalendar({lost:1, place:'left', func:reportPrihodLoad});

    if($('#report_history').length > 0) {
        $('#report_history_worker').vkSel({
            width:140,
            title0:'�� ������',
            spisok:workers,
            func:reportHistoryLoad
        });
        $('#report_history_action').vkSel({
            width:140,
            title0:'�� �������',
            spisok:[
                {uid:1, title:'�������'},
                {uid:2, title:'������'},
                {uid:3, title:'��������'},
                {uid:4, title:'�������'}
            ],
            func:reportHistoryLoad
        });
    }
});