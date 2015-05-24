var AJAX_SETUP = APP_HTML + '/ajax/setup.php?' + VALUES;

$(document)
	.on('click', '#setup_info #devs div', function() {
		var t = $(this),
			inp = t.parent().find('input'),
			devs = [];
		for(var n = 0; n < inp.length; n++) {
			var u = inp.eq(n);
			if(u.val() == 1)
				devs.push(u.attr('id'));
		}
		if(!devs.length) {
			spanShow('Не сохранено!<br />Необходимо выбрать<br />минимум одну категорию', true);
			return;
		}
		var send = {
			op:'info_devs_set',
			devs:devs.join()
		};
		$.post(AJAX_SETUP, send, function(res) {
			if(res.success)
				spanShow('Изменения сохранены');
		}, 'json');

		function spanShow(msg, err) {
			$('#devs span').remove();
			err = err ? ' class="err"' : '';
			t.prepend('<span><em' + err + '>' + msg + '</em></span>')
				.find('span')
				.delay(1500)
				.fadeOut(1500, function() {
					$(this).remove();
				});
		}
	})

	.on('click', '#setup_worker .add', function() {
		var html =
			'<div id="setup_worker_add">' +
				'<h1>Укажите адрес страницы пользователя или его<br />ID ВКонтакте:</h1>' +
				'<h2>Формат адреса может быть следующих видов:<br />' +
					'<u>http://vk.com/id12345</u>, <u>http://vk.com/durov</u>.<br />' +
					'Либо используйте ID пользователя: <u>id12345</u>, <u>durov</u>, <u>12345</u>.' +
				'</h2>' +
				'<input type="text" id="viewer_id" />' +
				'<div class="vkButton"><button>Найти</button></div>' +
				'<a class="manual">Или заполните данные вручную..</a>' +
				'<table class="manual_tab">' +
					'<tr><td class="label">Имя:<td><input type="text" id="first_name" />' +
					'<tr><td class="label">Фамилия:<td><input type="text" id="last_name" />' +
					'<tr><td class="label">Пол:<td><input type="hidden" id="sex" />' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				top:50,
				width:350,
				head:'Добавление нового сотрудника',
				content:html,
				butSubmit:'Добавить',
				submit:submit
			}),
			viewer_id = 0,
			but = $('#viewer_id').focus().keyEnter(user_find).next();
		but.click(user_find);
		$('.manual').click(function() {
			$(this)
				.hide()
				.next().show();
			$('.res').remove();
			viewer_id = 0;
			$('#viewer_id').val('');
			$('#first_name').focus();
		});
		$('#sex')._radio({
			spisok:[
				{uid:2, title:'М'},
				{uid:1, title:'Ж'}
			]
		});
		function user_find() {
			if(but.hasClass('busy'))
				return;
			viewer_id = 0;
			$('.res').remove();
			var send = {
				user_ids:$.trim($('#viewer_id').val()),
				fields:'photo_50',
				v:5.2
			};
			if(!send.user_ids)
				return;
			if(/vk.com/.test(send.user_ids))
				send.user_ids = send.user_ids.split('vk.com/')[1];
			if(/\?/.test(send.user_ids))
				send.user_ids = send.user_ids.split('?')[0];
			if(/#/.test(send.user_ids))
				send.user_ids = send.user_ids.split('#')[0];
			but.addClass('busy');
			VK.api('users.get', send, function(data) {
				but.removeClass('busy');
				console.log(data);
				if(data.response) {
					var u = data.response[0],
						html =
							'<table class="res">' +
								'<tr><td class="photo"><img src=' + u.photo_50 + '>' +
									'<td class="name">' + u.first_name + ' ' + u.last_name +
							'</table>';
					but.after(html);
					viewer_id = u.id;
				}
			});
		}
		function submit() {
			var send = {
				op:'worker_add',
				viewer_id:viewer_id,
				first_name:$('#first_name').val(),
				last_name:$('#last_name').val(),
				sex:$('#sex').val()
			};
			if(!send.viewer_id && !send.first_name && !send.last_name) err('Произведите поиск пользователя<br>или укажите вручную имя и фамилию', -60);
			else if(send.first_name && send.last_name && send.sex == 0) err('Не указан пол', -47);
			else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Новый сотрудник успешно добавлен.');
						$('#spisok').html(res.html);
					} else {
						dialog.abort();
						err(res.text, -60);
					}
				}, 'json');
			}
		}
		function err(msg, top) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:top,
				left:90
			});
		}
	})
	.on('click', '#setup_worker .img_del', function() {
		var u = $(this);
		while(!u.hasClass('unit'))
			u = u.parent();
		var dialog = _dialog({
			top:110,
			width:250,
			head:'Удаление сотрудника',
			content:'<center>Подтвердите удаление сотрудника.</center>',
			butSubmit:'Удалить',
			submit:submit
		});
		function submit() {
			var send = {
				op:'worker_del',
				viewer_id:u.attr('val')
			};
			dialog.process();
			$.post(AJAX_SETUP, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Сотрудник удален.');
					$('#spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup-service-cartridge .add', cartridgeNew)
	.on('click', '#setup-service-cartridge .img_edit', function() {
		var t = $(this),
			id = t.attr('val');
		while(t[0].tagName != 'TR')
			t = t.parent();
		var name = t.find('.name').html(),
			type_id = t.find('.type_id').val(),
			filling = t.find('.filling').val(),
			restore = t.find('.restore').val(),
			chip = t.find('.chip').val(),
			html = '<table class="setup-tab">' +
				'<tr><td class="label">Вид:<td><input type="hidden" id="type_id" value="' + type_id + '" />' +
				'<tr><td class="label"><b>Модель картриджа:</b><td><input type="text" id="name" value="' + name + '" />' +
				'<tr><td class="label">Заправка:<td><input type="text" id="cost_filling" class="money" maxlength="11" value="' + filling + '" /> руб.' +
				'<tr><td class="label">Восстановление:<td><input type="text" id="cost_restore" class="money" maxlength="11" value="' + restore + '" /> руб.' +
				'<tr><td class="label">Замена чипа:<td><input type="text" id="cost_chip" class="money" maxlength="11" value="' + chip + '" /> руб.' +
				'<tr><td><td>' +
				'<tr><td class="label">Объединить:<td><input type="hidden" id="join" />' +
				'<tr class="tr-join dn"><td class="label">С картриджем:<td><input type="hidden" id="join_id" />' +
				'</table>',
			dialog = _dialog({
				top:40,
				width:400,
				head:'Редактирование данных картриджа',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#type_id')._select({
			spisok:CARTRIDGE_TYPE
		});
		$('#name').focus();
		$('#name,#cost_filling,#cost_restore,#cost_chip').keyEnter(submit);
		$('#join')._check({
			func:function(v) {
				$('.tr-join')[(v ? 'remove' : 'add') + 'Class']('dn');
			}
		});
		var spisok = [];
		for(var n = 0; n < CARTRIDGE_SPISOK.length; n++) {
			var sp = CARTRIDGE_SPISOK[n];
			if(sp.uid == id)
				continue;
			spisok.push(sp);
		}
		$('#join_id')._select({
			width:218,
			write:1,
			title0:'Не выбрано',
			spisok:spisok
		});
		function submit() {
			var join = _num($('#join').val()),
				send = {
					op:'cartridge_edit',
					id:id,
					type_id:$('#type_id').val(),
					name:$('#name').val(),
					cost_filling:_num($('#cost_filling').val()),
					cost_restore:_num($('#cost_restore').val()),
					cost_chip:_num($('#cost_chip').val()),
					join_id:join ? _num($('#join_id').val()) : 0
				};
			if(!send.name) {
				dialog.err('Не указано наименование');
				$('#name').focus();
			} else if(join && !send.join_id)
				dialog.err('Не выбран картридж для объединения');
			else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('#spisok').html(res.html);
						CARTRIDGE_SPISOK = res.cart;
						dialog.close();
						_msg('Изменено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('mouseleave', '#setup-service-cartridge .edited', function() {//удаление подсветки отредактированного картриджа
		$(this).css('background-color', '#fff');
	})

	.on('click', '#setup_invoice .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="50" />' +
				'<tr><td class="label topi">Описание:<td><textarea id="about"></textarea>' +
				'</table>',
			dialog = _dialog({
				width:400,
				head:'Добавление нового счёта',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'invoice_add',
				name:$('#name').val(),
				about:$('#about').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
					} else {
						dialog.abort();
						err(res.text);
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:100,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#setup_invoice .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name div').html(),
			about = t.find('.name pre').html(),
			types = t.find('.type_id').val(),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'<tr><td class="label r top">Описание:<td><textarea id="about">' + about + '</textarea>' +
				'</table>',
			dialog = _dialog({
				width:400,
				head:'Редактирование данных счёта',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'invoice_edit',
				id:id,
				name:$('#name').val(),
				about:$('#about').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
					} else {
						dialog.abort();
						err(res.text);
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:100,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#setup_invoice .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление счёта',
				content:'<center><b>Подтвердите удаление счёта.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'setup_invoice_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SETUP, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_expense .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'<tr><td class="label r">Список сотрудников:<td><input id="show_worker" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				width:400,
				head:'Добавление новой категории расхода организации',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#show_worker')._check();
		function submit() {
			var send = {
				op:'expense_add',
				name:$('#name').val(),
				show_worker:$('#show_worker').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<span class=red>Не указано наименование</span>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('#spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_expense .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			worker = t.find('.worker').html() ? 1 : 0,
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="50" value="' + name + '" />' +
				'<tr><td class="label r">Список сотрудников:<td><input id="show_worker" type="hidden" value="' + worker + '" />' +
				'</table>',
			dialog = _dialog({
				width:400,
				head:'Редактирование категории расхода организации',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#show_worker')._check();
		function submit() {
			var send = {
				op:'expense_edit',
				id:id,
				name:$('#name').val(),
				show_worker:$('#show_worker').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<span class="red">Не указано наименование</span>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('#spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_expense .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				head:'Удаление категории расхода организации',
				content:'<center><b>Подтвердите удаление.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'expense_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SETUP, send, function(res) {
				if(res.success) {
					$('#spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_zayav_expense .add', function() {
		var t = $(this),
			html =
			'<table class="setup-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="50" />' +
				'<tr><td class="label topi">Дополнительное поле:<td><input id="dop" type="hidden" />' +
			'</table>',
			dialog = _dialog({
				width:440,
				head:'Добавление новой категории расхода заявки',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#dop')._radio({light:1,spisok:ZE_DOP});
		function submit() {
			var send = {
				op:'zayav_expense_add',
				name: $.trim($('#name').val()),
				dop:$('#dop').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_zayav_expense .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			dop = t.find('.hdop').val(),
			html =
				'<table class="setup-tab">' +
					'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="50" value="' + name + '" />' +
					'<tr><td class="label topi">Дополнительное поле:<td><input id="dop" type="hidden" value="' + dop + '" />' +
				'</table>',
			dialog = _dialog({
				width:440,
				head:'Редактирование категории расхода заявки',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#dop')._radio({light:1,spisok:ZE_DOP});

		function submit() {
			var send = {
				op:'zayav_expense_edit',
				id:id,
				name:$.trim($('#name').val()),
				dop:$('#dop').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_zayav_expense .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление категории расхода заявки',
				content:'<center><b>Подтвердите удаление<br />категории расхода заявки.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'zayav_expense_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SETUP, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.ready(function() {
		if($('#setup_info').length) {
			$('#type')._select({
				width:200,
				spisok:WS_TYPE
			});
			$('#info_save').click(function() {
				var t = $(this),
					send = {
						op:'info_save',
						type:$('#type').val()
					};
				t.addClass('_busy');
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						_msg('Данные изменены');
						document.location.reload();
					} else
						t.removeClass('_busy');
				}, 'json');
			});
			$('#info_del').click(function() {
				var dialog = _dialog({
					top:150,
					width:300,
					head:'Удаление организации',
					content:'<center>Вы действительно хотите<BR>удалить организацию и все данные?</center>',
					butSubmit:'&nbsp;&nbsp;&nbsp;&nbsp;Да&nbsp;&nbsp;&nbsp;&nbsp;',
					submit:function() {
						dialog.process();
						$.post(AJAX_SETUP, {op:'info_del'}, function(res) {
							if(res.success)
								location.href = URL;
							else
								dialog.abort();
						}, 'json');
					}
				});
			});
		}
		if($('#setup_rekvisit').length) {
			$('textarea').autosize();
			$('.vkButton').click(function() {
				var t = $(this),
					send = {
						op:'rekvisit',
						org_name:$('#org_name').val(),
						ogrn:$('#ogrn').val(),
						inn:$('#inn').val(),
						kpp:$('#kpp').val(),
						adres_yur:$('#adres_yur').val(),
						telefon:$('#telefon').val(),
						adres_ofice:$('#adres_ofice').val(),
						time_work:$('#time_work').val(),
						schet:$('#schet').val(),
						bank_name:$('#bank_name').val(),
						bik:$('#bik').val(),
						kor_schet:$('#kor_schet').val()
					};
				t.addClass('busy');
				$.post(AJAX_SETUP, send, function(res) {
					t.removeClass('busy');
					if(res.success)
						_msg('Информация сохранена.');
				}, 'json');
			});
		}
		if($('#setup-service').length) {
			$('.s-cartridge-toggle').click(function() {
				var t = $(this),
					p = t,
					send = {
						op:'cartridge_toggle',
						v:t.hasClass('off') ? 0 : 1
					};
				while(!p.hasClass('unit'))
					p = p.parent();
				var h1 = p.find('h1');
				if(h1.hasClass('_busy'))
					return;
				h1.addClass('_busy');
				$.post(AJAX_SETUP, send, function(res) {
					h1.removeClass('_busy');
					if(res.success) {
						p[(send.v ? 'add' : 'remove') + 'Class']('on');
						_msg('Выполнено!');
					}
				}, 'json');
			});
		}
		if($('#setup_rules').length) {
			$('.g-save').click(function() {
				var send = {
						op:'worker_name_save',
						viewer_id:RULES_VIEWER_ID,
						first_name:$('#first_name').val(),
						last_name:$('#last_name').val(),
						post:$('#post').val()
					},
					but = $(this);
				if(!send.first_name) { err('Не указано имя'); $('#first_name').focus(); }
				else if(!send.last_name) { err('Не указана фамилия'); $('#last_name').focus(); }
				else {
					but.addClass('busy');
					$.post(AJAX_SETUP, send, function(res) {
						but.removeClass('busy');
						if(res.success)
							_msg('Сохранено.');
					}, 'json');
				}
				function err(msg) {
					but.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-57,
						left:-6,
						indent:40,
						show:1,
						remove:1
					});
				}
			});
			$('#rules_appenter')._check(function(v) {
				$('.app-div')[(v == 0 ? 'add' : 'remove') + 'Class']('dn');
				$('#rules_info')._check(0);
				$('#rules_worker')._check(0);
				$('#rules_rules')._check(0);
				$('#rules_income')._check(0);
				$('#rules_historyshow')._check(0);
				$('#rules_money')._dropdown(0);
			});
			$('#rules_money')._dropdown({
				spisok:[
					{uid:0,title:'только свои'},
					{uid:1,title:'все платежи'}
				]
			});
			$('.rules-save').click(function() {
				var send = {
						op:'worker_rules_save',
						viewer_id:RULES_VIEWER_ID,
						rules_appenter:$('#rules_appenter').val(),
						rules_info:$('#rules_info').val(),
						rules_worker:$('#rules_worker').val(),
						rules_rules:$('#rules_rules').val(),
						rules_income:$('#rules_income').val(),
						rules_historyshow:$('#rules_historyshow').val(),
						rules_historytransfer:$('#rules_historytransfer').val(),
						rules_money:$('#rules_money').val()
					},
					but = $(this);
				if(but.hasClass('busy'))
					return;
				but.addClass('busy');
				$.post(AJAX_SETUP, send, function(res) {
					but.removeClass('busy');
					if(res.success)
						_msg('Права сохранены.');
				}, 'json');
			});
			$('.dop-save').click(function() {
				var send = {
						op:'worker_dop_save',
						viewer_id:RULES_VIEWER_ID,
						rules_money_procent:$('#rules_money_procent').val()
					},
					but = $(this);
				if(but.hasClass('busy'))
					return;
				but.addClass('busy');
				$.post(AJAX_SETUP, send, function(res) {
					but.removeClass('busy');
					if(res.success)
						_msg('Дополнительные настройки сохранены.');
				}, 'json');
			});
		}
	});
