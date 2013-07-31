var REGEXP_NUMERIC = /^\d+$/,
    AJAX_MAIN = 'http://' + G.domain + '/ajax/main.php?' + G.values,
    reportHistoryLoad = function() {
        var send = {
            op:'report_history_load',
            worker:$('#report_history_worker').val(),
            action:$('#report_history_action').val()
        };
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_history').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
    reportRemindLoad = function() {
        var send = {
            op:'report_remind_load',
            status:$('#remind_status').val(),
            private:$('#remind_private').val()
        };
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_remind #spisok').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
    reportPrihodLoad = function() {
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
    },
    reportRashodLoad = function() {
        var send = {
            op:'report_rashod_load',
            category:$('#rashod_category').val(),
            worker:$('#rashod_worker').val(),
            year:$('#rashod_year').val(),
            month:$('#rashod_monthSum').val()
        };
        if(send.month < 10) send.month = '0' + send.month;
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            monthSum = res.summ.split(',');
            reportRashodMonthPrint();
            $('#report_rashod #spisok').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    },
    reportRashodMonthPrint = function() {
        var spisok = [];
        for(var n = 1; n <= 12; n++)
            spisok.push({uid:n, title:G.months_ass[n] + (monthSum[n - 1] > 0 ? '<div class="sum">' + monthSum[n - 1] + '</div>' : '')});
        $('#rashod_monthSum').vkRadio({
            top:5,
            light:1,
            spisok:spisok,
            func:reportRashodLoad
        });
    },
    rashodCategoryAdd = function(spisok, obj) {
        var html = '<TABLE>' +
            '<TR><TD class="tdAbout">������������:<TD><INPUT type="text" id="rashod_category_name">' +
            '</TABLE>',
            dialog = vkDialog({
                width:320,
                head:"����� ��������� ��� ��������",
                content:html,
                submit:submit
            }),
            name = $('#rashod_category_name');
        name.focus();

        function submit() {
            var send = {
                op:'setup_rashod_category_add',
                name:name.val()
            };
            if(!send.name) {
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">�� ������� ������������.</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-51,
                    left:73,
                    correct:0
                });
                name.focus();
            } else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("����� ��������� �������.");
                        obj.add({uid:res.id,title:send.name}).val(res.id);
                    }
                }, 'json');
            }
        }
    },
    reportKassaLoad = function() {
        var send = {
            op:'report_kassa_load',
            del_show:$('#kassaShowDel').val() == 1 ? 1 : 0
        };
        $('#mainLinks')
            .find('img').remove().end()
            .append('<img src="/img/upload.gif">');
        $.post(AJAX_MAIN, send, function (res) {
            $('#report_kassa #spisok').html(res.html);
            $('#mainLinks img').remove();
        }, 'json');
    };


$(document).ajaxError(function(event, request, settings) {
    if(!request.responseText)
        return;
    alert('������:\n\n' + request.responseText);
});

$(document).on('mouseenter', '.zayav_link', function(e) {
    var t = $(this),
        tooltip = t.find('.tooltip');
    if(!tooltip.hasClass('empty'))
        return;
    var send = {
        op:'tooltip_zayav_info_get',
        id:t.attr('val')
    };
    $.post(AJAX_MAIN, send, function(res) {
        tooltip
            .html(res.html)
            .removeClass('empty');
    }, 'json');
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
        var html = '<TABLE class="remind_add_tab" cellspacing="6">' +
            '<TR><TD class="label r">����������:<TD><INPUT type=hidden id=destination>' +
            '<TR><TD class="label r" id=target_name><TD id=target>' +
            '</TABLE>' +

            '<TABLE class="remind_add_tab" id="tab_content" cellspacing="6">' +
            '<TR><TD class="label r top">�������:<TD><TEXTAREA id=txt></TEXTAREA>' +
            '<TR><TD class="label r">������� ���� ����������:<TD><INPUT type=hidden id=data>' +
            '<TR><TD class="label r">������:<TD><INPUT type=hidden id=private>' +
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
    })
    .on('click', '#report_remind .edit', function() {
        var dialog = vkDialog({
                top:30,
                width:400,
                head:"����� �������� ��� �����������",
                content:'<center><img src="/img/upload.gif"></center>',
                butSubmit:"���������",
                submit:submit
            }),
            curDay,
            id = $(this).attr('val'),
            send = {
                op:'report_remind_get',
                id:id
            };
        $.post(AJAX_MAIN, send, function(res) {
            curDay = res.day;
            var html = "<TABLE class=remind_action_tab>" +
                "<TR><TD class=tdAbout>" + (res.client ? "������:" : '') + (res.zayav ? "������:" : '') +
                    "<TD>" + (res.client ? res.client : '') + (res.zayav ? res.zayav : '') +
                "<TR><TD class=tdAbout>�������:<TD><B>" + res.txt + "</B>" +
                "<TR><TD class=tdAbout>����:<TD>" + res.viewer + ", " + res.dtime +
                '<TR><TD class=tdAbout>��������:<TD><INPUT type=hidden id=action value="0">' +
                "</TABLE>" +

                "<TABLE class=remind_action_tab id=new_action>" +
                "<TR><TD class=tdAbout id=new_about><TD id=new_title>" +
                "<TR><TD class=tdAbout id=new_comm><TD><TEXTAREA id=comment></TEXTAREA>" +
                "</TABLE>";
            dialog.content.html(html);

            $("#action").vkRadio({
                top:6,
                spisok:[
                    {uid:1, title:'��������� �� ������ ����'},
                    {uid:2, title:'���������'},
                    {uid:3, title:'��������'}
                ],
                func:function (id) {
                    $("#new_action").show();
                    $("#comment").val('');
                    $("#new_about").html('');
                    $("#new_title").html('');
                    if (id == 1) {
                        $("#new_about").html("����:");
                        $("#new_title").html("<INPUT type=hidden id=data>");
                        $("#new_comm").html("�������:");
                        $("#new_action #data").vkCalendar();
                    }
                    if(id == 2) $("#new_comm").html("�����������:");
                    if(id == 3) $("#new_comm").html("�������:");
                }
            });

            $("#comment").autosize();
        }, 'json');

        function submit () {
            var send = {
                op:'report_remind_edit',
                id:id,
                action:parseInt($("#action").val()),
                day:curDay,
                status:1,
                history:$("#comment").val()
            };
            switch(send.action) {
                case 1: send.day = $("#data").val(); break;
                case 2: send.status = 2; break; // ���������
                case 3: send.status = 0;        // ��������
            }

            var msg;
            if(!send.action) msg = "������� ����� ��������.";
            else if((send.action == 1 || send.action == 3) && !send.history) msg = "�� ������� �������.";
            else if(send.action == 1 && send.day == curDay) msg = "�������� ����� ����.";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function(res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("������� ���������������.");
                        $('#report_remind #spisok').html(res.html);
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:"<SPAN class=red>" + msg + "</SPAN>",
                    remove:1,
                    indent:40,
                    show:1,
                    top:-48,
                    left:115
                });
        }
    })
    .on('click', '#remind_private_check', reportRemindLoad);

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
            else if(send.kassa == -1) msg = "�������, ������ ��������� � �����?";
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
                      .find('.img_del').attr('class', 'img_rest').attr('title', '������������ �����');
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
                  .find('.img_rest').attr('class', 'img_del').attr('title', '������� �����');
            }
        }, 'json');
    })
    .on('click', '#prihodShowDel_check', reportPrihodLoad);

$(document)
    .on('click', '#report_rashod_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_rashod_next',
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_rashod .tabSpisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_rashod #add', function() {
        var html = '<TABLE cellpadding="0" cellspacing="8" id="report_rashod_add">' +
                '<TR><TD class=tdAbout>���������:<TD><INPUT type="hidden" id="category" value="0">' +
                '<TR><TD class=tdAbout>��������:<TD><INPUT type="text" id="about" maxlength="100">' +
                '<TR><TD class=tdAbout>���������:<TD><INPUT type="hidden" id="worker" value="0">' +
                '<TR><TD class=tdAbout>�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8"> ���.' +
                '<TR><TD class=tdAbout>������ ����� �� �����?:<TD><INPUT type="hidden" id="kassa" value="-1">' +
            '</TABLE>',
            dialog = vkDialog({
                top:60,
                width:380,
                head:"�������� �������",
                content:html,
                submit:submit
            }),
            category = $("#report_rashod_add #category"),
            worker = $("#report_rashod_add #worker"),
            kassa = $('#report_rashod_add #kassa'),
            sum = $("#report_rashod_add #sum"),
            about = $("#report_rashod_add #about");

        category.vkSel({
            width:180,
            title0:'�� �������',
            spisok:rashodCaregory,
            funcAdd:rashodCategoryAdd
        });

        worker.vkSel({
            title0:'�� ������',
            spisok:rashodViewers
        });

        kassa.vkRadio({
            display:'inline-block',
            right:15,
            spisok:[{uid:1, title:'��'},{uid:0, title:'���'}]
        });
        about.focus();

        function submit() {
            var send = {
                op:'report_rashod_add',
                category:category.val(),
                about:about.val(),
                worker:worker.val(),
                sum:sum.val(),
                kassa:kassa.val()
            };
            var msg;
            if(!send.about && send.category == 0) { msg = "�������� ��������� ��� ������� ��������."; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = "����������� ������� �����."; sum.focus(); }
            else if(send.kassa == -1) msg = "�������, ������ ����� �� ����� ��� ���.";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("����� ������ �����.");
                        reportRashodLoad();
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
    .on('click', '#report_rashod .img_del', function() {
        var send = {
            op:'report_rashod_del',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent();
        tr.html('<td colspan="4" class="deleting">��������... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                vkMsgOk("�������� �����������.");
                tr.remove();
            }
        }, 'json');
    })
    .on('click', '#report_rashod .img_edit', function() {
        var dialog = vkDialog({
                top:60,
                width:380,
                head:"�������������� �������",
                content:'<center><img src="/img/upload.gif"></center>',
                butSubmit:'���������',
                submit:submit
            }),
            id = $(this).attr('val'),
            category,
            worker,
            kassa,
            sum,
            about,
            send = {
                op:'report_rashod_get',
                id:id
            };
        $.post(AJAX_MAIN, send, function(res) {
            var html = '<TABLE cellpadding="0" cellspacing="8" id="report_rashod_add">' +
                '<TR><TD class=tdAbout>���������:<TD><INPUT type="hidden" id="category" value="' + res.category + '">' +
                '<TR><TD class=tdAbout>��������:<TD><INPUT type="text" id="about" maxlength="150" value="' + res.about + '">' +
                '<TR><TD class=tdAbout>���������:<TD><INPUT type="hidden" id="worker" value="' + res.worker_id + '">' +
                '<TR><TD class=tdAbout>�����:<TD><INPUT type="text" id="sum" class="money" maxlength="8" value="' + res.sum + '"> ���.' +
                '<TR><TD class=tdAbout>������ ����� �� �����?:<TD><INPUT type="hidden" id="kassa" value="' + res.kassa + '">' +
                '</TABLE>';
            dialog.content.html(html);
            category = $("#report_rashod_add #category");
            worker = $("#report_rashod_add #worker");
            kassa = $('#report_rashod_add #kassa');
            sum = $("#report_rashod_add #sum");
            about = $("#report_rashod_add #about");

            category.vkSel({
                width:180,
                title0:'�� �������',
                spisok:rashodCaregory,
                funcAdd:rashodCategoryAdd
            });

            worker.vkSel({
                title0:'�� ������',
                spisok:rashodViewers
            });

            kassa.vkRadio({
                display:'inline-block',
                right:15,
                spisok:[{uid:1, title:'��'},{uid:0, title:'���'}]
            });
            about.focus();
        }, 'json');

        function submit() {
            var send = {
                id:id,
                op:'report_rashod_edit',
                category:category.val(),
                about:about.val(),
                worker:worker.val(),
                sum:sum.val(),
                kassa:kassa.val()
            };
            var msg;
            if(!send.about && send.category == 0) { msg = "�������� ��������� ��� ������� ��������."; about.focus(); }
            else if(!REGEXP_NUMERIC.test(send.sum)) { msg = "����������� ������� �����."; sum.focus(); }
            else if(send.kassa == -1) msg = "�������, ������ ����� �� ����� ��� ���.";
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("������ �������.");
                        reportRashodLoad();
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
    });

$(document)
    .on('click', '#report_kassa #set_go', function() {
        if($(this).hasClass('busy'))
            return;
        var t = $(this),
            sum = $('#set_summa'),
            send = {
                op:'report_kassa_set',
                sum:sum.val()
            };
        if(!REGEXP_NUMERIC.test(send.sum)) {
            sum.vkHint({
                msg:'<SPAN class=red>����������� ������� �����.</SPAN>',
                remove:1,
                indent:40,
                show:1,
                top:-70,
                left:16,
                correct:0
            });
            sum.focus();
        } else {
            t.addClass('busy');
            $.post(AJAX_MAIN, send, function (res) {
                if(res.success) {
                    location.reload();
                } else
                    t.removeClass('busy');
            }, 'json');
        }
    })
    .on('click', '#report_kassa_next', function() {
        if($(this).hasClass('busy'))
            return;
        var next = $(this),
            send = {
                op:'report_kassa_next',
                page:$(this).attr('val')
            };
        next.addClass('busy');
        $.post(AJAX_MAIN, send, function (res) {
            if(res.success) {
                next.remove();
                $('#report_kassa .tabSpisok').append(res.html);
            } else
                next.removeClass('busy');
        }, 'json');
    })
    .on('click', '#report_kassa .actions a:first', function() {
        var kassa = $('#kassa_summa'),
            kassa_sum = parseInt(kassa.html()),
            html = '<TABLE cellspacing="8">' +
                '<TR><TD class="label r">�����:<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" />' +
                '<TR><TD class="label r">�����������:<TD><INPUT type="text" id="kassa_down_txt" />' +
                '</TABLE>',
            dialog = vkDialog({
                head:"�������� ����� � �����",
                content:html,
                submit:submit
            }),
            sum = $("#kassa_down_sum"),
            txt = $("#kassa_down_txt");

        sum.focus();

        function submit() {
            var send = {
                op:'report_kassa_action',
                txt:txt.val(),
                sum:sum.val(),
                down:0
            };
            var msg;
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = "����������� ������� �����."; sum.focus(); }
            else if(!send.txt) { msg = "�� ������ �����������."; txt.focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("����� ������ ������.");
                        kassa.html(kassa_sum += parseInt(send.sum));
                        reportKassaLoad();
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-47,
                    left:92,
                    correct:0
                });
        }
    })
    .on('click', '#report_kassa .actions a:last', function() {
        var kassa = $('#kassa_summa'),
            kassa_sum = kassa.html(),
            html = '<TABLE cellspacing="8">' +
                '<TR><TD class="label r">�����:' +
                    '<TD><INPUT type="text" class="money" id="kassa_down_sum" maxlength="8" /> max: ' + kassa_sum +
                '<TR><TD class="label r">�����������:<TD><INPUT type="text" id="kassa_down_txt" />' +
                '</TABLE>',
            dialog = vkDialog({
                head:"������ ����� �� �����",
                content:html,
                submit:submit
            }),
            sum = $("#kassa_down_sum"),
            txt = $("#kassa_down_txt");

        sum.focus();

        function submit() {
            var send = {
                op:'report_kassa_action',
                txt:txt.val(),
                sum:sum.val(),
                down:1
            };
            var msg;
            if(!REGEXP_NUMERIC.test(send.sum)) { msg = "����������� ������� �����."; sum.focus(); }
            else if(send.sum > kassa_sum) { msg = "�������� ����� ��������� ����� � �����."; sum.focus(); }
            else if(!send.txt) { msg = "�� ������ �����������."; txt.focus(); }
            else {
                dialog.process();
                $.post(AJAX_MAIN, send, function (res) {
                    if(res.success) {
                        dialog.close();
                        vkMsgOk("����� ������ ������.");
                        kassa.html(kassa_sum -= send.sum);
                        reportKassaLoad();
                    }
                }, 'json');
            }
            if(msg)
                dialog.bottom.vkHint({
                    msg:'<SPAN class="red">' + msg + '</SPAN>',
                    remove:1,
                    indent:40,
                    show:1,
                    top:-47,
                    left:92,
                    correct:0
                });
        }
    })
    .on('click', '#report_kassa .img_del', function() {
        var send = {
            op:'report_kassa_del',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">��������... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                vkMsgOk("�������� �����������.");
                if($('#kassaShowDel').val() == 1)
                    tr.addClass('deleted')
                        .html(trSave)
                        .find('.img_del').attr('class', 'img_rest').attr('title', '������������');
                else
                    tr.remove();
                $('#kassa_summa').html(res.sum);
            }
        }, 'json');
    })
    .on('click', '#report_kassa .img_rest', function() {
        var send = {
            op:'report_kassa_rest',
            id:$(this).attr('val')
        };
        var tr = $(this).parent().parent(),
            trSave = tr.html();
        tr.html('<td colspan="4" class="deleting">��������������... <img src=/img/upload.gif></td>');
        $.post(AJAX_MAIN, send, function(res) {
            if(res.success) {
                vkMsgOk("�������������� �����������.");
                tr.removeClass('deleted')
                    .html(trSave)
                    .find('.img_rest').attr('class', 'img_del').attr('title', '������� �����');
                $('#kassa_summa').html(res.sum);
            }
        }, 'json');
    })
    .on('click', '#kassaShowDel_check', reportKassaLoad);

$(document).ready(function() {
    $('#report_prihod_day_begin').vkCalendar({lost:1, place:'left', func:reportPrihodLoad});
    $('#report_prihod_day_end').vkCalendar({lost:1, place:'left', func:reportPrihodLoad});

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
    if($('#report_remind').length > 0) {
        $('#remind_status').vkRadio({
            top:6,
            light:1,
            spisok:[
                {uid:1, title:'��������'},
                {uid:2, title:'���������'},
                {uid:0, title:'��������'}
            ],
            func:reportRemindLoad
        });
    }
    if($('#report_rashod').length > 0) {
        $('#rashod_category').vkSel({
            width:140,
            title0:'����� ���������',
            spisok:rashodCaregory,
            func:reportRashodLoad
        });
        $('#rashod_worker').vkSel({
            width:140,
            title0:'��� ����������',
            spisok:rashodViewers,
            func:reportRashodLoad
        });
        $('#rashod_year').years({func:reportRashodLoad});
        reportRashodMonthPrint();
    }
});