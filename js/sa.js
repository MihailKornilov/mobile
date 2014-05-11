var AJAX_SA = SITE + '/ajax/sa.php?' + VALUES;

$(document)
	.on('click', '.sa-user .action', function() {
		var t = $(this),
			un = t;
		while(!un.hasClass('un'))
			un = un.parent();
		var send = {
			op:'user_action',
			viewer_id:un.attr('val')
		};
		t.html('&nbsp;').addClass('_busy');
		$.post(AJAX_SA, send, function(res) {
			if(res.success)
				t.after(res.html).remove();
			else
				t.html('Действие').removeClass('_busy');
		}, 'json');
	})

	.on('click', '.sa-ws-info .ws_status_change', function() {
		var t = $(this),
			send = {
				op:'ws_status_change',
				ws_id:t.attr('val')
			};
		$.post(AJAX_SA, send, function(res) {
			if(res.success)
				document.location.reload();
			else
				t.vkHint({
					msg:'<SPAN class=red>' + res.text + '</SPAN>',
					top:-47,
					left:27,
					indent:50,
					show:1,
					remove:1
				});
		}, 'json');
	})
	.on('click', '.sa-ws-info .ws_enter', function() {
		setCookie('sa_viewer_id', $(this).attr('val'));
		document.location.reload();
	})
	.on('click', '.sa-ws-info .ws_del', function() {
		var t = $(this);
		var dialog = _dialog({
			top:110,
			width:250,
			head:'Удаление мастерской',
			content:'<center>Подтвердите удаление мастерской.</center>',
			butSubmit:'Удалить',
			submit:submit
		});
		function submit() {
			var send = {
				op:'ws_del',
				ws_id:t.attr('val')
			};
			$.post(AJAX_SA, send, function(res) {
				if(res.success)
					document.location.reload();
			}, 'json');
		}
	})
	.on('click', '.sa-ws-info .ws_client_balans', function() {
		var t = $(this),
			send = {
				op:'ws_client_balans',
				ws_id:t.attr('val')
			};
		t.addClass('busy');
		$.post(AJAX_SA, send, function(res) {
			t.removeClass('busy');
			if(res.success) {
				t.next().remove('span');
				t.after('<span> Изменено: ' + res.count + '. Время: ' + res.time + '</span>');
			}
		}, 'json');
	})
	.on('click', '.sa-ws-info .ws_zayav_balans', function() {
		var t = $(this),
			send = {
				op:'ws_zayav_balans',
				ws_id:t.attr('val')
			};
		t.addClass('busy');
		$.post(AJAX_SA, send, function(res) {
			t.removeClass('busy');
			if(res.success) {
				t.next().remove('span');
				t.after('<span> Изменено: ' + res.count + '. Время: ' + res.time + '</span>');
			}
		}, 'json');
	})

	.on('click', '.sa-device .add', function() {
		var t = $(this),
			html = '<table class="sa-device-add">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'<tr><td class="label r">Родительный падеж (кого?):<td><input id="name_rod" type="text" maxlength="100" />' +
				'<tr><td class="label r">Множественное число:<td><input id="name_mn" type="text" maxlength="100" />' +
				'<tr><td class="label r top">Возможная комплектация:<td class="equip">' + devEquip +
				'</table>',
			dialog = _dialog({
				top:60,
				width:460,
				head:'Добавление нового наименования устройства',
				content:html,
				submit:submit
			});
		$('#name,#name_rod,#name_mn').keyEnter(submit);
		$('#name').focus();
		function submit() {
			var inp = $('.equip input'),
				arr = [];
			for(var n = 0; n < inp.length; n++) {
				var eq = inp.eq(n);
				if(eq.val() == 1)
					arr.push(eq.attr('id').split('_')[1]);
			}
			var send = {
				op:'device_add',
				name:$('#name').val(),
				rod:$('#name_rod').val(),
				mn:$('#name_mn').val(),
				equip:arr.join()
			};
			if(!send.name || !send.rod || !send.mn) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Необходимо заполнить все поля</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				if(!send.name)
					$('#name').focus();
				else if(!send.rod)
					$('#name_rod').focus();
				else if(!send.mn)
					$('#name_mn').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
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
	.on('click', '.sa-device .img_edit', function() {
		var t = $(this),
			id = t,
			dialog = _dialog({
				top:60,
				width:460,
				head:'Изменение данных устройства',
				load:1,
				butSubmit:'Сохранить',
				submit:submit
			});
		while(id[0].tagName != 'DD')
			id = id.parent();
		id = id.attr('val');
		var send = {
			op:'device_get',
			id:id
		};
		$.post(AJAX_SA, send, function(res) {
			if(res.success) {
				var html = '<table class="sa-device-add">' +
					'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" value="' + res.name + '" />' +
					'<tr><td class="label r">Родительный падеж (кого?):<td><input id="name_rod" type="text" maxlength="100" value="' + res.name_rod + '" />' +
					'<tr><td class="label r">Множественное число:<td><input id="name_mn" type="text" maxlength="100" value="' + res.name_mn + '" />' +
					'<tr><td class="label r top">Возможная комплектация:<td class="equip">' + res.equip +
				'</table>';
				dialog.content.html(html);
				$('#name,#name_rod,#name_mn').keyEnter(submit);
				$('#name').focus();
			} else
				dialog.loadError();
		}, 'json');
		function submit() {
			var inp = $('.equip input'),
				arr = [];
			for(var n = 0; n < inp.length; n++) {
				var eq = inp.eq(n);
				if(eq.val() == 1)
					arr.push(eq.attr('id').split('_')[1]);
			}
			var send = {
				op:'device_edit',
				id:id,
				name:$('#name').val(),
				rod:$('#name_rod').val(),
				mn:$('#name_mn').val(),
				equip:arr.join()
			};
			if(!send.name || !send.rod || !send.mn) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Необходимо заполнить все поля</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				if(!send.name)
					$('#name').focus();
				else if(!send.rod)
					$('#name_rod').focus();
				else if(!send.mn)
					$('#name_mn').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Изменено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.sa-device .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление устройства',
				content:'<center><b>Подтвердите удаление устройства.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'device_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
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

	.on('click', '.sa-vendor .add', function() {
		var t = $(this),
			html = '<table class="sa-vendor-add">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'<tr><td class="label r">Выделить:<td><input id="bold" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Добавление нового наименования производителя',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#bold')._check();
		function submit() {
			var send = {
				op:'vendor_add',
				device_id:DEVICE_ID,
				name:$('#name').val(),
				bold:$('#bold').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:99,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
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
	.on('click', '.sa-vendor .img_edit', function() {
		var t = $(this),
			ven = t;
		while(ven[0].tagName != 'DD')
			ven = ven.parent();
		var bold = ven.find('.name').hasClass('b') ? 1 : 0,
			html = '<table class="sa-vendor-add">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" value="' + ven.find('.name a').html() + '" />' +
				'<tr><td class="label r">Выделить:<td><input id="bold" type="hidden" value="' + bold + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Изменение данных производителя',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		$('#bold')._check();
		function submit() {
			var send = {
				op:'vendor_edit',
				vendor_id:ven.attr('val'),
				name:$('#name').val(),
				bold:$('#bold').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:99,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Изменено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.sa-vendor .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление производителя',
				content:'<center><b>Подтвердите удаление производителя.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'vendor_del',
				vendor_id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
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

	.on('click', '.sa-model ._next', function() {
		var t = $(this);
		if(t.hasClass('busy'))
			return;
		var send = {
			op:'model_spisok',
			vendor_id:VENDOR_ID,
			page:t.attr('val'),
			find:$('#find')._search('val')
		};
		t.addClass('busy');
		$.post(AJAX_SA, send, function(res) {
			if(res.success) {
				t.parent().remove();
				$('._spisok').append(res.html);
			} else
				t.removeClass('busy');
		}, 'json');
	})
	.on('click', '.sa-model .add', function() {
		var t = $(this),
			html = '<table class="sa-model-add">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Добавление нового наименования модели',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'model_add',
				vendor_id:VENDOR_ID,
				name:$('#name').val()
			};
			if(!send.name)
				hint('Не указано наименование');
			else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('._spisok').find('.tr').remove().end().append(res.html);
						dialog.close();
						_msg('Внесено!');
					} else {
						dialog.abort();
						hint(res.text);
					}
				}, 'json');
			}
		}
		function hint(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:99,
				indent:50,
				show:1,
				remove:1
			});
			$('#name').focus();
		}
	})
	.on('click', '.sa-model .img_edit', function() {
		var t = $(this),
			mod = t;
		while(mod[0].tagName != 'TR')
			mod = mod.parent();
		var html = '<table class="sa-model-add">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="100" value="' + mod.find('.name .dn').html() + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'Изменение данных модели',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'model_edit',
				model_id:mod.attr('val'),
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:99,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						mod.find('.name b').html(send.name);
						dialog.close();
						_msg('Изменено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.sa-model ._spisok .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление модели',
				content:'<center><b>Подтвердите удаление модели.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'model_del',
				model_id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
				if(res.success) {
					t.remove();
					dialog.close();
					_msg('Удалено!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '.sa-equip .rightLink a', function() {
		$('.sa-equip .rightLink a.sel').removeClass('sel');
		$(this).addClass('sel');
		var send = {
			op:'equip_show',
			device_id:$('.sa-equip .rightLink .sel').attr('val')
		};
		$('.path').addClass('busy');
		$.post(AJAX_SA, send, function(res) {
			$('.path').removeClass('busy');
			$('#eq-spisok').html(res.html);
			sortable();
		}, 'json');
	})
	.on('click', '.sa-equip .add', function() {
		var t = $(this),
			html = '<table class="sa-equip-add">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'<tr><td class="label">Описание:<td><input id="title" type="text" maxlength="200" />' +
			'</table>',
			dialog = _dialog({
				top:90,
				width:350,
				head:'Добавление новой комплектации',
				content:html,
				submit:submit
			});
		$('#name,#title').keyEnter(submit);
		$('#name').focus();
		function submit() {
			var send = {
				op:'equip_add',
				name:$('#name').val(),
				title:$('#title').val(),
				device_id:$('.sa-equip .rightLink .sel').attr('val')
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:77,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('#eq-spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.sa-equip .img_edit', function() {
		var t = $(this),
			id = t,
			dialog = _dialog({
				top:90,
				width:350,
				head:'Редактирование комплектации',
				load:1,
				butSubmit:'Сохранить',
				submit:submit
			});
		while(id[0].tagName != 'DD')
			id = id.parent();
		id = id.attr('val');
		var send = {
			op:'equip_get',
			id:id
		};
		$.post(AJAX_SA, send, function(res) {
			if(res.success) {
				var html = '<table class="sa-equip-add">' +
					'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" value="' + res.name + '" />' +
					'<tr><td class="label">Описание:<td><input id="title" type="text" maxlength="200" value="' + res.title + '" />' +
					'</table>';
				dialog.content.html(html);
				$('#name,#title').keyEnter(submit);
				$('#name').focus();
			} else
				dialog.loadError();
		}, 'json');

		function submit() {
			var send = {
				op:'equip_edit',
				id:id,
				name:$('#name').val(),
				title:$('#title').val(),
				device_id:$('.sa-equip .rightLink .sel').attr('val')
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:77,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('#eq-spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.sa-equip .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление комплектации',
				content:'<center><b>Подтвердите удаление комплектации.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'equip_del',
				id:t.attr('val'),
				device_id:$('.sa-equip .rightLink .sel').attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
				if(res.success) {
					$('#eq-spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.sa-equip .check0,.sa-equip .check1', function() {
		var inp = $('.sa-equip ._sort input'),
			arr = [];
		for(var n = 0; n < inp.length; n++) {
			var eq = inp.eq(n);
			if(eq.val() == 1)
				arr.push(eq.attr('id').split('_')[1]);
		}
		var send = {
			op:'equip_set',
			device_id:$('.sa-equip .rightLink .sel').attr('val'),
			ids:arr.join()
		};
		$('.path').addClass('busy');
		$.post(AJAX_SA, send, function(res) {
			$('.path').removeClass('busy');
		}, 'json');
	})

	.on('click', '.sa-fault .add', function() {
		var html = '<table class="sa-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'Добавление новой неисправности',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'fault_add',
				name:$('#name').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
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
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:99,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-fault .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var name = t.find('.name').html(),
			html = '<table class="sa-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'Редактирование неисправности',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').keyEnter(submit).focus();
		function submit() {
			var send = {
				op:'fault_edit',
				id:t.attr('val'),
				name:$('#name').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Изменено.');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:57,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-fault .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление неисправности',
				content:'<center><b>Подтвердите удаление неисправности.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'fault_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
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

	.on('click', '.sa-devstatus .add', function() {
		var html = '<table class="sa-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'Добавление нового статуса устройства',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'devstatus_add',
				name:$('#name').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
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
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:99,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-devstatus .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var name = t.find('.name').html(),
			html = '<table class="sa-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'Редактирование статуса устройства',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').keyEnter(submit).focus();
		function submit() {
			var send = {
				op:'devstatus_edit',
				id:t.attr('val'),
				name:$('#name').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Изменено.');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:57,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-devstatus .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление статуса устройства',
				content:'<center><b>Подтвердите удаление статуса устройства.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'devstatus_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
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

	.on('click', '.sa-color .add', function() {
		var html = '<table class="sa-color-add">' +
				'<tr><td class="label">Предлог:<td><input id="predlog" type="text" maxlength="100" />' +
				'<tr><td class="label">Цвет:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				top:90,
				width:310,
				head:'Добавление нового цвета',
				content:html,
				submit:submit
			});
		$('#name,#predlog').keyEnter(submit);
		$('#predlog').focus();
		function submit() {
			var send = {
				op:'color_add',
				predlog:$('#predlog').val(),
				name:$('#name').val()
			};
			if(!send.predlog) {
				err('Не указан предлог');
				$('#predlog').focus();
			} else if(!send.name) {
				err('Не указан цвет');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:57,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-color .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var predlog = t.find('.pre').html(),
			name = t.find('.name').html(),
			html = '<table class="sa-color-add">' +
				'<tr><td class="label">Предлог:<td><input id="predlog" type="text" maxlength="100" value="' + predlog + '" />' +
				'<tr><td class="label">Цвет:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				top:90,
				width:310,
				head:'Редактирование цвета',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name,#predlog').keyEnter(submit);
		$('#predlog').focus();
		function submit() {
			var send = {
				op:'color_edit',
				id:t.attr('val'),
				predlog:$('#predlog').val(),
				name:$('#name').val()
			};
			if(!send.predlog) {
				err('Не указан предлог');
				$('#predlog').focus();
			} else if(!send.name) {
				err('Не указан цвет');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Изменено.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:57,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-color .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление цвета',
				content:'<center><b>Подтвердите удаление цвета.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'color_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '.sa-zpname .add', function() {
		var html = '<table class="sa-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'Добавление нового наименования запчасти',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'zpname_add',
				name:$('#name').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:99,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-zpname .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var name = t.find('.name').html(),
			html = '<table class="sa-tab">' +
				'<tr><td class="label">Наименование:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'Редактирование',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').keyEnter(submit).focus();
		function submit() {
			var send = {
				op:'zpname_edit',
				id:t.attr('val'),
				name:$('#name').val()
			};
			if(!send.name) {
				err('Не указано наименование');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Изменено.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:57,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '.sa-zpname .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление',
				content:'<center><b>Подтвердите удаление наименования запчасти.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'zpname_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SA, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.ready(function() {
		if($('.sa-model').length) {
			$('#find')._search({
				width:300,
				txt:'Поиск по названию модели',
				enter:1,
				focus:1,
				func:function(val) {
					var path = $('.path');
					if(path.hasClass('busy'))
						return;
					var send = {
						op:'model_spisok',
						vendor_id:VENDOR_ID,
						find:val
					};
					path.addClass('busy');
					$.post(AJAX_SA, send, function(res) {
						path.removeClass('busy');
						if(res.success)
							$('.spisok').html(res.html);
					}, 'json');
				}
			});
		}
	});
