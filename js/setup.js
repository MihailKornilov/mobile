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
		$.post(AJAX_MAIN, send, function(res) {
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
				$.post(AJAX_MAIN, send, function(res) {
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
				$.post(AJAX_MAIN, send, function(res) {
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
						$.post(AJAX_MAIN, {op:'info_del'}, function(res) {
							if(res.success)
								location.href = URL;
							else
								dialog.abort();
						}, 'json');
					}
				});
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
				$.post(AJAX_MAIN, send, function(res) {
					h1.removeClass('_busy');
					if(res.success) {
						p[(send.v ? 'add' : 'remove') + 'Class']('on');
						_msg('Выполнено!');
					}
				}, 'json');
			});
		}
	});
