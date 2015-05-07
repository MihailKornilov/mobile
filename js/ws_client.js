var clientAdd = function(callback) {
		var html =
			'<div id="client-add-tab">' +
				'<div id="dopLinks">';
		for(var i in CLIENT_CATEGORY_ASS)
			html += '<a class="link' + (i == 1 ? ' sel' : '') + '" val="' + i + '">' + CLIENT_CATEGORY_ASS[i] + '</a>';
		html += '</div>' +
				'<table class="ca-table" id="people">' +
					'<tr><td class="label">Ф.И.О.:<td><input type="text" id="fio" />' +
					'<tr><td class="label">Телефон:<td><input type="text" id="telefon" />' +
					'<tr><td class="label top">Дополнительная<br />информация:<td><textarea id="info_people"></textarea>' +
				'</table>' +
				'<table class="ca-table dn" id="org">' +
					'<tr><td class="label">Название организации:<td><input type="text" id="org_name" />' +
					'<tr><td class="label">Телефон:<td><input type="text" id="org_telefon" />' +
					'<tr><td class="label">Факс<td><input type="text" id="org_fax" />' +
					'<tr><td class="label">Адрес:<td><input type="text" id="org_adres" />' +
					'<tr><td class="label">ИНН:<td><input type="text" id="org_inn" />' +
					'<tr><td class="label">КПП:<td><input type="text" id="org_kpp" />' +
					'<tr><td class="label top">Дополнительная<br />информация:<td><textarea id="info_org"></textarea>' +
				'</table>' +
				'<a id="post-add">Добавить доверенное лицо</a>' +
			'</div>';
		var post = 1,
			category_id = 1,
			dialog = _dialog({
				width:450,
				top:30,
				padding:0,
				head:'Добавление нoвого клиента',
				content:html,
				submit:submit
			});
		postAdd();
		$('#fio').focus();
		$('#info_people,#info_org').autosize();
		$('#post-add').hide().click(postAdd);
		$('#dopLinks .link').click(function() {
			var t = $(this),
				p = t.parent();
			category_id = _num(t.attr('val'));
			p.find('.sel').removeClass('sel');
			t.addClass('sel');
			$('#people')[(category_id != 1 ? 'add' : 'remove') + 'Class']('dn');
			$('#org')[(category_id == 1 ? 'add' : 'remove') + 'Class']('dn');
			$('#post-add')[(category_id != 1 ? 'show' : 'hide')]();
			$(category_id == 1 ? '#fio' : '#org_name').focus();
		});
		function postAdd() {
			$('#org').append(
				'<tr><td><td><b>Доверенное лицо ' + post + ':</b>' +
				'<tr><td class="label">Ф.И.О.:<td><input type="text" id="fio' + post + '" />' +
				'<tr><td class="label">Телефон:<td><input type="text" id="telefon' + post + '" />' +
				'<tr><td class="label">Должность:<td><input type="text" id="post' + post + '" />'
			);
			post++;
			if(post > 3)
				$(this).remove();
		}
		function submit() {
			var send = {
				op:'client_add',
				category_id:category_id,
				org_name:$('#org_name').val(),
				org_telefon:$('#org_telefon').val(),
				org_fax:$('#org_fax').val(),
				org_adres:$('#org_adres').val(),
				org_inn:$('#org_inn').val(),
				org_kpp:$('#org_kpp').val(),
				info_dop:$(category_id == 1 ? '#info_people' : '#info_org').val(),
				fio1:$('#fio' + (category_id == 1 ? '' : 1)).val(),
				fio2:post > 2 ? $('#fio2').val() : '',
				fio3:post > 3 ? $('#fio3').val() : '',
				telefon1:$('#telefon' + (category_id == 1 ? '' : 1)).val(),
				telefon2:post > 2 ? $('#telefon2').val() : '',
				telefon3:post > 3 ? $('#telefon3').val() : '',
				post1:$('#post1').val(),
				post2:post > 2 ? $('#post2').val() : '',
				post3:post > 3 ? $('#post3').val() : ''
			};
			if(category_id == 1 && !send.fio1) {
				dialog.err('Не указаны ФИО');
				$('#fio').focus();
			} else if(category_id > 1 && !send.org_name) {
				dialog.err('Не указано название организации');
				$('#org_name').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Новый клиент внесён.');
						if(typeof callback == 'function')
							callback(res);
						else
							document.location.href = URL + '&p=client&d=info&id=' + res.uid;
					} else dialog.abort();
				}, 'json');
			}
		}
	},
	clientEdit = function() {
		var html =
			'<div id="client-add-tab">' +
				'<div id="dopLinks">';
		for(var i in CLIENT_CATEGORY_ASS)
			html += '<a class="link' + (i == CLIENT.category_id ? ' sel' : '') + '" val="' + i + '">' + CLIENT_CATEGORY_ASS[i] + '</a>';
		html += '</div>' +
				'<table class="ca-table" id="people">' +
					'<tr><td class="label">Ф.И.О.:<td><input type="text" id="fio" value="' + CLIENT.fio1 + '" />' +
					'<tr><td class="label">Телефон:<td><input type="text" id="telefon" value="' + CLIENT.telefon1 + '" />' +
					'<tr><td class="label top">Дополнительная<br />информация:<td><textarea id="info_people">' + $('#info-dop').val() + '</textarea>' +
				'</table>' +
				'<table class="ca-table" id="org">' +
					'<tr><td class="label">Название организации:<td><input type="text" id="org_name" value="' + CLIENT.org_name + '" />' +
					'<tr><td class="label">Телефон:<td><input type="text" id="org_telefon" value="' + CLIENT.org_telefon + '" />' +
					'<tr><td class="label">Факс:<td><input type="text" id="org_fax" value="' + CLIENT.org_fax + '" />' +
					'<tr><td class="label">Адрес:<td><input type="text" id="org_adres" value="' + CLIENT.org_adres + '" />' +
					'<tr><td class="label">ИНН:<td><input type="text" id="org_inn" value="' + CLIENT.org_inn + '" />' +
					'<tr><td class="label">КПП:<td><input type="text" id="org_kpp" value="' + CLIENT.org_kpp + '" />' +
					'<tr><td class="label top">Дополнительная<br />информация:<td><textarea id="info_org">' + $('#info-dop').val() + '</textarea>' +
				'</table>' +
				'<a id="post-add">Добавить доверенное лицо</a>' +
				'<table class="ca-table" id="org">' +
					'<tr><td class="label">Объединить:<td><input type="hidden" id="join" />' +
					'<tr id="tr_join"><td class="label">с клиентом:<td><input type="hidden" id="client2" />' +
				'</table>' +
			'</div>';
		var post = 1,
			category_id = CLIENT.category_id,
			dialog = _dialog({
				width:450,
				top:30,
				padding:0,
				head:'Редактирование данных клиента',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		categorySet(category_id);
		postAdd();
		if(CLIENT.fio2 || CLIENT.telefon2)
			postAdd();
		if(CLIENT.fio3 || CLIENT.telefon3)
			postAdd();
		$('#fio').focus();
		$('#info_people,#info_org').autosize();
		$('#post-add').click(postAdd);
		$('#dopLinks .link').click(function() {
			var t = $(this),
				p = t.parent();
			category_id = _num(t.attr('val'));
			p.find('.sel').removeClass('sel');
			t.addClass('sel');
			categorySet(category_id);
		});
		$('#client2').clientSel({width:258});
		$('#join')
			._check()
			._check(function() {
				$('#tr_join').toggle();
			});
		$('#join_check').vkHint({
			msg:'<b>Объединение клиентов.</b><br />' +
				'Необходимо, если один клиент был внесён в базу дважды.<br /><br />' +
				'Текущий клиент будет получателем.<br />Выберите второго клиента.<br />' +
				'Все заявки, начисления и платежи станут общими после<br />объединения.<br /><br />' +
				'Внимание, операция необратима!',
			width:330,
			delayShow:1500,
			top:-162,
			left:-81,
			indent:80
		});
		function categorySet(v) {
			$('#people')[(v != 1 ? 'add' : 'remove') + 'Class']('dn');
			$('#org')[(v == 1 ? 'add' : 'remove') + 'Class']('dn');
			$('#post-add')[(v != 1 ? 'show' : 'hide')]();
			$(v == 1 ? '#fio' : '#org_name').focus();
		}
		function postAdd() {
			$('#org').append(
				'<tr><td><td><b>Доверенное лицо ' + post + ':</b>' +
				'<tr><td class="label">Ф.И.О.:<td><input type="text" id="fio' + post + '" value="' + CLIENT['fio' + post] + '" />' +
				'<tr><td class="label">Телефон:<td><input type="text" id="telefon' + post + '" value="' + CLIENT['telefon' + post] + '" />' +
				'<tr><td class="label">Должность:<td><input type="text" id="post' + post + '" value="' + CLIENT['post' + post] + '" />'
			);
			post++;
			if(post > 3)
				$('#post-add').remove();
		}
		function submit() {
			var send = {
				op:'client_edit',
				id:CLIENT.id,
				category_id:category_id,
				org_name:$('#org_name').val(),
				org_telefon:$('#org_telefon').val(),
				org_fax:$('#org_fax').val(),
				org_adres:$('#org_adres').val(),
				org_inn:$('#org_inn').val(),
				org_kpp:$('#org_kpp').val(),
				info_dop:$(category_id == 1 ? '#info_people' : '#info_org').val(),
				fio1:$('#fio' + (category_id == 1 ? '' : 1)).val(),
				fio2:post > 2 ? $('#fio2').val() : '',
				fio3:post > 3 ? $('#fio3').val() : '',
				telefon1:$('#telefon' + (category_id == 1 ? '' : 1)).val(),
				telefon2:post > 2 ? $('#telefon2').val() : '',
				telefon3:post > 3 ? $('#telefon3').val() : '',
				post1:$('#post1').val(),
				post2:post > 2 ? $('#post2').val() : '',
				post3:post > 3 ? $('#post3').val() : '',
				join:_num($('#join').val()),
				client2:_num($('#client2').val())
			};
			if(!send.join)
				send.client2 = 0;

			if(category_id == 1 && !send.fio1) {
				dialog.err('Не указаны ФИО');
				$('#fio').focus();
			} else if(category_id > 1 && !send.org_name) {
				dialog.err('Не указано название организации');
				$('#org_name').focus();
			} else if(send.join && !send.client2)
				dialog.err('Укажите второго клиента');
			else if(send.join && send.client2 == CLIENT.id)
				dialog.err('Выберите другого клиента');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Данные клиента изменены');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},

	clientFilter = function() {
		var v = {
				op:'client_spisok',
				find:$('#find')._search('val'),
				dolg:$('#dolg').val(),
				active:$('#active').val(),
				comm:$('#comm').val(),
				opl:$('#opl').val()
			},
			loc = '';
		$('.filter')[v.find ? 'hide' : 'show']();

		if(v.find) loc += '.find=' + escape(v.find);
		else {
			if(v.dolg > 0) loc += '.dolg=' + v.dolg;
			if(v.active > 0) loc += '.active=' + v.active;
			if(v.comm > 0) loc += '.comm=' + v.comm;
			if(v.opl > 0) loc += '.opl=' + v.opl;
		}
		VK.callMethod('setLocation', hashLoc + loc);

		_cookie('client_find', escape(v.find));
		_cookie('client_dolg', v.dolg);
		_cookie('client_active', v.active);
		_cookie('client_comm', v.comm);
		_cookie('client_opl', v.opl);

		return v;
	},
	clientSpisok = function() {
		var result = $('.result');
		if(result.hasClass('busy'))
			return;
		result.addClass('busy');
		$.post(AJAX_WS, clientFilter(), function (res) {
			result.removeClass('busy');
			if(res.success) {
				result.html(res.all);
				$('.left').html(res.spisok);
			}
		}, 'json');
	},
	clientZayavFilter = function() {
		return {
			op:'client_zayav_spisok',
			client_id:CLIENT.id,
			status:$('#status').val(),
			diff:$('#diff').val(),
			device:$('#dev_device').val(),
			vendor:$('#dev_vendor').val(),
			model:$('#dev_model').val()
		};
	},
	clientZayavSpisok = function() {
		$('#dopLinks').addClass('busy');
		$.post(AJAX_WS, clientZayavFilter(), function (res) {
			$('#dopLinks').removeClass('busy');
			$('#zayav_result').html(res.all);
			$('#zayav_spisok').html(res.html);
		}, 'json');
	};

$.fn.clientSel = function(o) {
	var t = $(this);
	o = $.extend({
		width:260,
		add:null,
		client_id:t.val() || 0,
		func:function() {}
	}, o);

	if(o.add)
		o.add = function() {
			clientAdd(function(res) {
				var arr = [];
				arr.push(res);
				t._select(arr);
				t._select(res.uid);
			});
		};

	t._select({
		width:o.width,
		title0:'Начните вводить данные клиента...',
		spisok:[],
		write:1,
		nofind:'Клиентов не найдено',
		func:o.func,
		funcAdd:o.add,
		funcKeyup:clientsGet
	});
	clientsGet();

	function clientsGet(val) {
		var send = {
			op:'client_sel',
			val:val || '',
			client_id:o.client_id
		};
		t._select('process');
		$.post(AJAX_WS, send, function(res) {
			t._select('cancel');
			if(res.success) {
				t._select(res.spisok);
				if(o.client_id) {
					t._select(o.client_id);
					o.client_id = 0;
				}
			}
		}, 'json');
	}
	return t;
};

$(document)
	.on('click', '#client ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = clientFilter();
		send.page = next.attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				next.after(res.spisok).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#client #filter_clear', function() {
		$('#find')._search('clear');
		$('#dolg')._check(0);
		$('#active')._check(0);
		$('#comm')._check(0);
		$('#opl')._check(0);
		clientSpisok();
	})
	.on('mouseenter', '#client .comm', function() {
		var t = $(this),
			v = t.attr('val');
		t.vkHint({
			msg:v,
			width:200,
			ugol:'right',
			top:-2,
			left:-227,
			indent:'top',
			show:1
		})
	})

	.on('click', '#clientInfo #zayav_spisok ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = clientZayavFilter();
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.go-client-info', function(e) {
		e.stopPropagation();
		location.href = URL + '&p=client&d=info&id=' + $(this).attr('val');
	})

	.ready(function() {
		if($('#client').length) {
			$('#find')._search({
				width:602,
				focus:1,
				enter:1,
				txt:'Начните вводить данные клиента',
				func:clientSpisok
			}).inp(C.find);
			$('#buttonCreate').vkHint({
				msg:'<B>Внесение нового клиента в базу.</B><br /><br />' +
					'После внесения Вы попадаете на страницу с информацией о клиенте для дальнейших действий.<br /><br />' +
					'Клиентов также можно добавлять при <A href="' + URL + '&p=zayav&d=add&back=client">создании новой заявки</A>.',
				ugol:'right',
				width:215,
				top:-38,
				left:-250,
				indent:40,
				delayShow:1000
			}).click(clientAdd);
			$('#dolg')._check(clientSpisok);
			$('#active')._check(clientSpisok);
			$('#comm')._check(clientSpisok);
			$('#opl')._check(clientSpisok);
			$('#dolg_check').vkHint({
				msg:'<b>Список должников.</b><br /><br />' +
					'Выводятся клиенты, у которых баланс менее 0. Также в результате отображается общая сумма долга.',
				ugol:'right',
				width:150,
				top:-6,
				left:-185,
				indent:20,
				delayShow:1000
			});
		}
		if($('#clientInfo').length) {
			$('#client-edit').click(clientEdit);
			$('#dopLinks .link').click(function() {
				$('#dopLinks .link').removeClass('sel');
				$(this).addClass('sel');
				var val = $(this).attr('val');
				$('.res').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_filter').css('display', val == 'zayav' ? 'block' : 'none');
				$('#zayav_spisok').css('display', val == 'zayav' ? 'block' : 'none');
				$('#money_spisok').css('display', val == 'money' ? 'block' : 'none');
				$('#remind-spisok').css('display', val == 'remind' ? 'block' : 'none');
				//$('#remind_spisok').css('display', val == 'remind' ? 'block' : 'none');
				$('#comments').css('display', val == 'comm' ? 'block' : 'none');
				$('#histories').css('display', val == 'hist' ? 'block' : 'none');
			});
			$('#status').rightLink(clientZayavSpisok);
			$('#diff')._check(clientZayavSpisok);
			$('#dev').device({
				width:145,
				type_no:1,
				device_ids:DEVICE_IDS,
				vendor_ids:VENDOR_IDS,
				model_ids:MODEL_IDS,
				func:clientZayavSpisok
			});
		}
	});
