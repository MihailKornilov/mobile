$(document)
	.on('blur', '#setup_main #org_name', function() {
		var t = $(this),
			send = {
				op:'setup_org_name_save',
				name:t.val()
			};
		if(t.hasClass('busy') || G.org_name == send.name)
			return;
		t.addClass('busy').next('span').remove();
		t.after('<img src="/img/upload.gif">');
		$.post(AJAX_WS, send, function(res) {
			t.removeClass('busy').next('img').remove();
			if(res.success) {
				G.org_name = send.name;
				t.after('<span class="saved">Сохранено.</span>')
				 .next('span')
				 .delay(1500)
				 .fadeOut(1500, function() {
					 $(this).remove();
				 });
			}
		}, 'json');
	})
	.on('click', '#setup_main #devs div', function() {
		var t = $(this),
			inp = t.parent().find('input'),
			devs = [];
		for(var n = 0; n < inp.length; n++) {
			var u = inp.eq(n);
			if(u.val() == 1)
				devs.push(u.attr('id'));
		}
		if(devs.length == 0) {
			spanShow('Не сохранено!<br />Необходимо выбрать<br />минимум одну категорию', true);
			return;
		}
		var send = {
			op:'setup_devs_set',
			devs:devs.join()
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				spanShow('Изменения сохранены');
		}, 'json');

		function spanShow(msg, err) {
			$('#setup_main #devs span').remove();
			err = err ? ' class="err"' : '';
			t.prepend('<span><em' + err + '>' + msg + '</em></span>')
			 .find('span')
			 .delay(1500)
			 .fadeOut(1500, function() {
				 $(this).remove();
			 });
		}
	})
	.on('click', '#setup_main #ws_del', function() {
		var dialog = _dialog({
			top:150,
			width:300,
			head:'Удаление мастерской',
			content:'<center>Вы действительно хотите<BR>удалить мастерскую и все данные?</center>',
			butSubmit:'&nbsp;&nbsp;&nbsp;&nbsp;Да&nbsp;&nbsp;&nbsp;&nbsp;',
			submit:function() {
				dialog.process();
				$.post(AJAX_WS, {op:'setup_ws_del'}, function(res) {
					if(res.success)
						location.href = URL;
					else
						dialog.abort();
				}, 'json');
			}
		});
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
				op:'setup_worker_add',
				viewer_id:viewer_id,
				first_name:$('#first_name').val(),
				last_name:$('#last_name').val(),
				sex:$('#sex').val()
			};
			if(!send.viewer_id && !send.first_name && !send.last_name) err('Произведите поиск пользователя<br>или укажите вручную имя и фамилию', -60);
			else if(send.first_name && send.last_name && send.sex == 0) err('Не указан пол', -47);
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
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
				op:'setup_worker_del',
				viewer_id:u.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Сотрудник удален.');
					$('#spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_workers .add', function() {
		var html = '<div id="setup_worker_add">' +
				'<h1>Ссылка на страницу или ID пользователя ВКонтакте:</h1>' +
				'<input type="text" />' +
				'<DIV class="vkButton"><BUTTON>Найти</BUTTON></DIV>' +
			'</div>',
			dialog = _dialog({
				top:50,
				width:360,
				head:'Добавление нового сотрудника',
				content:html,
				butSubmit:'Добавить',
				submit:submit
			}),
			user_id,
			input = dialog.content.find('input'),
			but = input.next();
		input.focus().keyEnter(user_find);
		but.click(user_find);

		function user_find() {
			if(but.hasClass('busy'))
				return;
			user_id = false;
			var send = {
				user_ids:$.trim(input.val()),
				fields:'photo_50',
				v:5.2
			};
			if(!send.user_ids)
				return;
			but.addClass('busy').next('.res').remove();
			VK.api('users.get', send, function(data) {
				but.removeClass('busy');
				if(data.response) {
					var u = data.response[0],
						html = '<TABLE class="res">' +
							'<TR><TD class="photo"><IMG src=' + u.photo_50 + '>' +
							'<TD class="name">' + u.first_name + ' ' + u.last_name +
							'</TABLE>';
					but.after(html);
					user_id = u.id;
				}
			});
		}
		function submit() {
			if(!user_id) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">Не выбран пользователь</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:92
				});
				return;
			}
			var send = {
				op:'setup_worker_add',
				id:user_id
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				dialog.abort();
				if(res.success) {
					dialog.close();
					_msg('Новый сотрудник успешно добавлен.');
					$('#spisok').html(res.html);
				} else
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + res.text + '</SPAN>',
						remove:1,
						indent:40,
						show:1,
						top:-60,
						left:92
					});
			}, 'json');
		}
	})
	.on('click', '#setup_workers .img_del', function() {
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
				op:'setup_worker_del',
				viewer_id:u.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('Сотрудник удален.');
					$('#spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '#setup_workers .adm_set', function() {
		var u = $(this),
			adm = $(this).parent();
		while(!u.hasClass('unit'))
			u = u.parent();
		var send = {
			op:'setup_worker_admin_set',
			viewer_id:u.attr('val')
		};
		if(adm.hasClass('busy'))
			return;
		adm.html('&nbsp;').addClass('busy');
		$.post(AJAX_WS, send, function(res) {
			adm.removeClass('busy')
			if(res.success)
				adm.html('Администратор <a class="adm_cancel">отменить</a>');
		}, 'json');
	})
	.on('click', '#setup_workers .adm_cancel', function() {
		var u = $(this),
			adm = $(this).parent();
		while(!u.hasClass('unit'))
			u = u.parent();
		var send = {
			op:'setup_worker_admin_cancel',
			viewer_id:u.attr('val')
		};
		if(adm.hasClass('busy'))
			return;
		adm.html('&nbsp;').addClass('busy');
		$.post(AJAX_WS, send, function(res) {
			adm.removeClass('busy')
			if(res.success)
				adm.html('<a class="adm_set">Назначить администратором</a>');
		}, 'json');
	})

	.ready(function() {
	});