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
				t.html('��������').removeClass('_busy');
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
		_cookie('sa_viewer_id', $(this).attr('val'));
		document.location.reload();
	})
	.on('click', '.sa-ws-info .ws_del', function() {
		var t = $(this);
		var dialog = _dialog({
			top:110,
			width:250,
			head:'�������� �����������',
			content:'<center>����������� �������� �����������.</center>',
			butSubmit:'�������',
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
				t.after('<span> ��������: ' + res.count + '. �����: ' + res.time + '</span>');
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
				t.after('<span> ��������: ' + res.count + '. �����: ' + res.time + '</span>');
			}
		}, 'json');
	})

	.on('click', '.sa-device .add', function() {
		var t = $(this),
			html = '<table class="sa-device-add">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" />' +
				'<tr><td class="label r">����������� ����� (����?):<td><input id="name_rod" type="text" maxlength="100" />' +
				'<tr><td class="label r">������������� �����:<td><input id="name_mn" type="text" maxlength="100" />' +
				'<tr><td class="label r top">��������� ������������:<td class="equip">' + devEquip +
				'</table>',
			dialog = _dialog({
				top:60,
				width:460,
				head:'���������� ������ ������������ ����������',
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
					msg:'<SPAN class=red>���������� ��������� ��� ����</SPAN>',
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
						_msg('�������!');
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
				head:'��������� ������ ����������',
				load:1,
				butSubmit:'���������',
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
					'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" value="' + res.name + '" />' +
					'<tr><td class="label r">����������� ����� (����?):<td><input id="name_rod" type="text" maxlength="100" value="' + res.name_rod + '" />' +
					'<tr><td class="label r">������������� �����:<td><input id="name_mn" type="text" maxlength="100" value="' + res.name_mn + '" />' +
					'<tr><td class="label r top">��������� ������������:<td class="equip">' + res.equip +
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
					msg:'<SPAN class=red>���������� ��������� ��� ����</SPAN>',
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
						_msg('��������!');
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
				head:'�������� ����������',
				content:'<center><b>����������� �������� ����������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '.sa-vendor .add', function() {
		var t = $(this),
			html = '<table class="sa-vendor-add">' +
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" />' +
				'<tr><td class="label r">��������:<td><input id="bold" type="hidden" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'���������� ������ ������������ �������������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('�������!');
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
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" value="' + ven.find('.name a').html() + '" />' +
				'<tr><td class="label r">��������:<td><input id="bold" type="hidden" value="' + bold + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'��������� ������ �������������',
				content:html,
				butSubmit:'���������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('��������!');
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
				head:'�������� �������������',
				content:'<center><b>����������� �������� �������������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
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
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'���������� ������ ������������ ������',
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
				hint('�� ������� ������������');
			else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('._spisok').find('.tr').remove().end().append(res.html);
						dialog.close();
						_msg('�������!');
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
				'<tr><td class="label r">������������:<td><input id="name" type="text" maxlength="100" value="' + mod.find('.name .dn').html() + '" />' +
				'</table>',
			dialog = _dialog({
				top:60,
				width:390,
				head:'��������� ������ ������',
				content:html,
				butSubmit:'���������',
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
					msg:'<SPAN class=red>�� ������� ������������</SPAN>',
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
						_msg('��������!');
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
				head:'�������� ������',
				content:'<center><b>����������� �������� ������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '.sa-equip .rightLink a', function() {
		$('.sa-equip .rightLink a.sel').removeClass('sel');
		$(this).addClass('sel');
		ZPNAME.device_id = $('.sa-equip .rightLink .sel').attr('val');
		var send = {
			op:'equip_show',
			device_id:ZPNAME.device_id
		};
		$('#eq-spisok').addClass('dis');
		$('#zp-spisok').addClass('dis');
		$.post(AJAX_SA, send, function(res) {
			$('#eq-spisok').removeClass('dis').html(res.equip);
			$('#zp-spisok').removeClass('dis').html(res.zp);
			sortable();
		}, 'json');
	})
	.on('click', '.sa-equip #equip-add', function() {
		var t = $(this),
			html = '<table class="sa-equip-add">' +
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" />' +
			'</table>',
			dialog = _dialog({
				top:90,
				width:350,
				head:'���������� ����� ������������',
				content:html,
				submit:submit
			});
		$('#name').keyEnter(submit).focus();
		function submit() {
			var send = {
				op:'equip_add',
				name:$('#name').val(),
				device_id:$('.sa-equip .rightLink .sel').attr('val')
			};
			if(!send.name) {
				dialog.err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('#eq-spisok').html(res.html);
						dialog.close();
						_msg('�������');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.sa-equip .equip-edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			html =
				'<table class="sa-equip-add">' +
					'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				top:90,
				width:350,
				head:'�������������� ������������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#name').keyEnter(submit).focus();
		function submit() {
			var send = {
				op:'equip_edit',
				id:id,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						t.find('.name').html(send.name)
						dialog.close();
						_msg('���������!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.sa-equip .equip-del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'�������� ������������',
				content:'<center><b>����������� �������� ������������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
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

	.on('click', '#zpname-add', zpNameAdd)
	.on('click', '.zpname-edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var name = t.find('.name').html(),
			html = '<table class="sa-tab">' +
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'��������������',
				content:html,
				butSubmit:'���������',
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
				dialog.err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						t.find('.name').html(send.name);
						dialog.close();
						_msg('��������.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.zpname-del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'��������',
				content:'<center><b>����������� �������� ������������ ��������.</b></center>',
				butSubmit:'�������',
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
					$('#zp-spisok').html(res.html);
					dialog.close();
					_msg('�������!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '.sa-fault .add', function() {
		var html = '<table class="sa-tab">' +
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'���������� ����� �������������',
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
				err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('�������!');
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
				'<tr><td class="label">������������:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'�������������� �������������',
				content:html,
				butSubmit:'���������',
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
				err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('��������.');
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
				head:'�������� �������������',
				content:'<center><b>����������� �������� �������������.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '.sa-color .add', function() {
		var html = '<table class="sa-color-add">' +
				'<tr><td class="label">�������:<td><input id="predlog" type="text" maxlength="100" />' +
				'<tr><td class="label">����:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				top:90,
				width:310,
				head:'���������� ������ �����',
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
				err('�� ������ �������');
				$('#predlog').focus();
			} else if(!send.name) {
				err('�� ������ ����');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('�������!');
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
				'<tr><td class="label">�������:<td><input id="predlog" type="text" maxlength="100" value="' + predlog + '" />' +
				'<tr><td class="label">����:<td><input id="name" type="text" maxlength="100" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				top:90,
				width:310,
				head:'�������������� �����',
				content:html,
				butSubmit:'���������',
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
				err('�� ������ �������');
				$('#predlog').focus();
			} else if(!send.name) {
				err('�� ������ ����');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SA, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('��������.');
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
				head:'�������� �����',
				content:'<center><b>����������� �������� �����.</b></center>',
				butSubmit:'�������',
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
					_msg('�������!');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.ready(function() {
		if($('.sa-model').length) {
			$('#find')._search({
				width:300,
				txt:'����� �� �������� ������',
				enter:1,
				focus:1,
				func:function(val) {
					var path = $('.path');
					if(path.hasClass('busy'))
						return;
					var send = {
						op:'model_spisok',
						vendor_id:VENDOR_ID,
						find:escape(val)
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
