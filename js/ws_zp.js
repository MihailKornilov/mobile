var zpFilter = function() {
		var v = {
				op:'zp_spisok',
				find:$.trim($('#find')._search('val')),
				menu:$('#zp_menu').val(),
				name:$('#zp_name').val(),
				device:_num($('#dev_device').val()),
				vendor:$('#dev_vendor').val(),
				model:$('#dev_model').val(),
				sort:$('#zp_sort').val()
			},
			loc = '';
		if(v.find) loc += '.find=' + escape(v.find);
		if(v.menu > 0) loc += '.menu=' + v.menu;
		if(v.name > 0) loc += '.name=' + v.name;
		if(v.device) loc += '.device=' + v.device;
		if(v.vendor > 0) loc += '.vendor=' + v.vendor;
		if(v.model > 0) loc += '.model=' + v.model;
		if(v.sort > 0) loc += '.sort=' + v.sort;
		VK.callMethod('setLocation', hashLoc + loc);

		_cookie(VIEWER_ID + '_zp_find', escape(v.find));
		_cookie(VIEWER_ID + '_zp_menu', v.menu);
		_cookie(VIEWER_ID + '_zp_name', v.name);
		_cookie(VIEWER_ID + '_zp_device', v.device);
		_cookie(VIEWER_ID + '_zp_vendor', v.vendor);
		_cookie(VIEWER_ID + '_zp_model', v.model);
		_cookie(VIEWER_ID + '_zp_sort', v.sort);

		$('#zp-filter')[(v.menu == 4 ? 'add' : 'remove') + 'Class']('dn');
		$('#sort')[(v.menu == 4 ? 'add' : 'remove') + 'Class']('dn');

		$('#zp_name')._select(!v.device ? 'remove' : {
			width:170,
			title0:'������������ ��������',
			spisok:ZPNAME_SPISOK[v.device],
			func:zpSpisok
		});

		return v;
	},
	zpSpisok = function() {
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, zpFilter(), function (res) {
			$('#mainLinks').removeClass('busy');
			$('.result').html(res.all);
			$('#zp-spisok').html(res.html);
		}, 'json');
	},
	zpAvaiAdd = function(obj) {
		var html = '<table class="avaiAddTab">' +
						'<tr><td class="left">' +
							'<div class="name">' + obj.name + '</div>' +
							'<div>' + obj.for + '</div>' +
							'<div class="avai">������� �������: <b>' + obj.count + '</b> ��.</div>' +
							'<table class="inp">' +
								'<tr><td class="label">����������:<td><input type="text" id="count" maxlength="5">' +
								'<tr><td class="label">���� �� ��.:<td><input type="text" id="cena" maxlength="10"><span>�� �����������</span>' +
							'</table>' +
							'<td valign="top">' + obj.img +
					'</table>',
			dialog = _dialog({
				head:'�������� ������� ��������',
				content:html,
				submit:submit
			});
		$('#count').focus();
		function submit() {
			var msg,
				send = {
					op:'zp_avai_add',
					zp_id:obj.zp_id,
					count:$('#count').val(),
					cena:$('#cena').val()
				};
			if(!send.cena)
				send.cena = 0;
			if (!REGEXP_NUMERIC.test(send.count) || send.count == 0) {
				msg = '����������� ������� ����������.';
				$('#count').focus();
			} else if(send.cena != 0 && !REGEXP_CENA.test(send.cena)) {
				msg = '����������� ������� ����.';
				$('#cena').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						obj.callback(res);
						dialog.close();
						_msg('�������� ������� �������� �����������.');
					}
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:92
				});
		}
	},
	zpAvaiNo = function(c) {
		if(c == 0) {
			_dialog({
				top:100,
				width:300,
				head:'��� �������',
				content:'<center>�������� ��� � �������.</center>',
				butSubmit:'',
				butCancel:'�������'
			});
			return true;
		}
		return false;
	},
	zpAvaiUpdate = function() {
		var send = {
			op:'zp_avai_update',
			zp_id: ZP.id
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				ZP.count = res.count;
				$('.move').html(res.move);
				$('.avai')
					[(res.count == 0 ? 'add' : 'remove') + 'Class']('no')
					.html(res.count == 0 ? '��� � �������.' : '� ������� ' + res.count + ' ��.');
			}
		}, 'json');
	};

$(document)
	.on('click', '#zp .clear', function() {
		$('#find')._search('clear');
		$('#zp_menu')._dropdown(0);
		$('#zp_name')._select(0);
		$('#dev').device({
			width:220,
			type_no:1,
			device_ids:WS_DEVS,
			func:zpSpisok
		});
		zpSpisok();
	})
	.on('click', '#zp ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = zpFilter();
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#zp .avai_add', function() {
		var unit = $(this);
		while(!unit.hasClass('unit'))
			unit = unit.parent();
		var obj = {
			zp_id:unit.attr('val'),
			name:unit.find('.name').html(),
			for:unit.find('.for').html(),
			count:$(this).hasClass('avai') ? $(this).find('b').html() : 0,
			img:unit.find('.img').html(),
			callback:function(res) {
				unit.find('.avai_add')
					.removeClass('hid')
					.addClass('avai')
					.html('� �������: <b>' + res.count + '</b>');
			}
		}
		zpAvaiAdd(obj);
		if($('.avaiAddTab img').attr('val'))
			$('.avaiAddTab img').addClass('fotoView');
	})
	.on('mouseenter', '.zp-zakaz:not(.busy)', function() {
		window.ZAKAZ_COUNT = $(this).find('.zcol').html();
	})
	.on('mouseleave', '.zp-zakaz:not(.busy)', function() {
		var t = $(this),
			count = t.find('.zcol').html(),
			tr = t;
		if(count != window.ZAKAZ_COUNT) {
			t.addClass('_busy');
			while(tr[0].tagName != 'TR')
				tr = tr.parent();
			var send = {
				op:'zp_zakaz_edit',
				zp_id:tr.attr('val'),
				count:count
			};
			$.post(AJAX_WS, send, function(res) {
				t.removeClass('_busy');
				if(res.success)
					t.find('.zcol')[(count > 0 ? 'remove' : 'add') + 'Class']('no');
			}, 'json');
		}
	})
	.on('mouseenter', '.zpzakaz:not(.busy)', function() {
		window.zakaz_count = $(this).find('tt:first').next('b').html();
	})
	.on('mouseleave', '.zpzakaz:not(.busy)', function() {
		var t = $(this),
			count = t.find('tt:first').next('b').html(),
			unit = t;
		if(count != window.zakaz_count) {
			t.removeClass('hid')
			 .addClass('busy')
			 .find('.cnt').html('���: <b></b>');
			while(!unit.hasClass('unit'))
				unit = unit.parent();
			var send = {
				op:'zp_zakaz_edit',
				zp_id:unit.attr('val'),
				count:count
			};
			$.post(AJAX_WS, send, function(res) {
				t.removeClass('busy');
				if(res.success) {
					t.find('.cnt').html(count > 0 ? '���: <b>' + count + '</b>' : '���');
					if(count == 0)
						t.addClass('hid');
				}
			}, 'json');
		}
	})
	.on('click', '.zpzakaz tt,.zp-zakaz tt', function() {
		var t = $(this),
			znak = t.html(),
			c = t[znak == '+' ? 'prev' : 'next'](),
			count = c.html();
		if(znak == '+')
			count++;
		else {
			count--;
			if(count < 0)
				count = 0;
		}
		c.html(count);
		c[(count ? 'remove' : 'add') + 'Class']('no');
	})
	.on('click', '.zp-id,.go-zp-info', function() {
		location.href = URL + '&p=zp&d=info&id=' + $(this).attr('val');
	})
	.on('click', '.price-info', function() {
		var dialog = _dialog({
				top:20,
				width:550,
				head:'���������� � ��������',
				load:1,
				butSubmit:'',
				butCancel:'�������'
			}),
			send = {
				op:'zp_price_info',
				id:$(this).attr('val')
			};
		$.post(AJAX_WS, send, function(res) {
			var html =
				'<div id="price-info-tab">' +
					'<table id="head">' +
						'<tr><td class="label">�������:<td>' + res.articul +
						'<tr><td class="label">������������:<td>' + res.name +
						'<tr><td class="label">����:<td><b>' + res.cena + '</b>' +
					'</table>' +
					'<div class="headName">���������:</div>' +
					res.upd +
				'</div>';
			dialog.content.html(html);
		}, 'json');
	})

	.on('click', '#zpInfo .avai_add', function() {
		var obj = ZP;
		obj.zp_id = obj.id;
		obj.callback = zpAvaiUpdate;
		zpAvaiAdd(obj);
		$('.avaiAddTab img')
			.removeAttr('height')
			.width(80);
	})
	.on('click', '#zpInfo .edit', function() {
		var html =
			'<table class="zp-add-tab">' +
				'<tr><td class="label">������������:<td><input type="hidden" id="name_id" value="' + ZP.name_id + '">' +
				'<tr><td class="label topi">����������:<td id="add_dev">' +
				'<tr><td><td>' + ZP.images +
				'<tr><td class="label">������:<td><input type="text" id="version" value="' + ZP.version + '">' +
				'<tr><td class="label">����:<td><input type="hidden" id="color_id" value="' + ZP.color_id + '">' +
				'<tr><td class="label">�����������:<td><input type="hidden" id="price_id" value="' + ZP.price_id + '">' +
			'</table>',
			dialog = _dialog({
				top:30,
				width:500,
				head:'�������������� ��������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		zpNameSelect(ZP.device, ZP.name_id);
		$('#add_dev').device({
			width:250,
			add:1,
			device_id:ZP.device,
			vendor_id:ZP.vendor,
			model_id:ZP.model,
			func_device:zpNameSelect
		});
		imageSortable();
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK
		});
		$('#price_id')._select({
			width:300,
			title0:'������� ������� ������...',
			spisok:[],
			write:1,
			nofind:'��������� �� �������',
			funcKeyup:priceGet
		});
		priceGet();
		function priceGet(val) {
			var send = {
				op:'zp_price_get',
				val:val || ''
			};
			$('#price_id')._select('process');
			$.post(AJAX_WS, send, function(res) {
				$('#price_id')._select('cancel');
				if(res.success)
					$('#price_id')._select(res.spisok);
			}, 'json');
		}
		function submit() {
			var msg,
				send = {
					op:'zp_edit',
					zp_id:ZP.id,
					name_id:_num($('#name_id').val()),
					device_id:_num($('#add_dev_device').val()),
					vendor_id:$('#add_dev_vendor').val(),
					model_id:$('#add_dev_model').val(),
					version:$('#version').val(),
					color_id:$('#color_id').val(),
					price_id:$('#price_id').val()
				};
			if(!send.name_id) dialog.err('�� ������� ������������ ��������.');
			else if(!send.device_id) dialog.err('�� ������� ����������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('�������������� ������ �����������');
						window.location.reload();
					} else
						dialog.abort();
				},'json');
			}
		}
	})
	.on('click', '#zpInfo .set', function() {
		if(zpAvaiNo(ZP.count))
			return;
		var html = '<table class="zp_dec_dialog">' +
				'<tr><td class="label r">����������:<td><input type="text" id="count" value="1"><span>(max: <b>' + ZP.count + '</b>)</span>' +
				'<tr><td class="label r topi">����� ������:<td><input type="text" id="zayavNomer">' +
				'<tr><td class="label r topi">����������:<td><textarea id="prim"></textarea>' +
			'</table>',
			dialog = _dialog({
				width:400,
				head:'��������� ��������',
				content:html,
				submit:submit
			});
		$('#count').focus().select();

		function submit() {
			var msg,
				send = {
					op:'zayav_zp_set',
					zp_id:ZP.id,
					count:$('#count').val(),
					zayav_id:$('#zayavNomerId').length > 0 ? $('#zayavNomerId').val() : 0,
					prim:$('#prim').val()
				};
			if(!REGEXP_NUMERIC.test(send.count) || send.count > ZP.count || send.count == 0) {
				msg = '����������� ������� ����������.';
				$('#count').focus();
			} else if(send.zayav_id == 0) {
				msg = '�� ������ ����� ������.';
				$('#zayavNomer').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg('��������� �������� �����������.');
					}
				},'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					left:74,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '#zpInfo .sale', function() {
		if(zpAvaiNo(ZP.count))
			return;
		var html = '<table class="zp_dec_dialog">' +
				'<tr><td class="label">����:<td><input type="hidden" id="invoice_id" value="' + (INVOICE_SPISOK.length == 1 ? INVOICE_SPISOK[0].uid : 0) + '" />' +
				'<tr><td class="label">���� �� ��.:<td><input type="text" id="cena" class="money" maxlength="11" /> ���.' +
				'<tr><td class="label">����������:' +
					'<td><input type="text" id="count" value="1" maxlength="11" />' +
						'<span>(max: <b>' + ZP.count + '</b>)</span>' +
				'<tr><td class="label">������:<td><input type="hidden" id="client_id">' +
				'<tr><td class="label top">����������:<td><textarea id="prim"></textarea>' +
				'</table>',
			dialog = _dialog({
				top:40,
				width:440,
				head:'������� ��������',
				content:html,
				submit:submit
			});

		$('#invoice_id')._select({
			width:240,
			title0:'�� ������',
			spisok:INVOICE_SPISOK,
			func:function() {
				$('#cena').focus();
			}
		});
		$('#cena').focus();
		$('#client_id').clientSel({add:1,width:240});
		$('#prim').autosize();

		function submit() {
			var send = {
				op:'zp_sale',
				zp_id:ZP.id,
				invoice_id:$('#invoice_id').val(),
				count:$('#count').val(),
				cena:$('#cena').val(),
				client_id:$('#client_id').val(),
				prim:$('#prim').val()
			};
			if(send.invoice_id == 0) err('�� ������ ����');
			else if(!REGEXP_NUMERIC.test(send.count) || send.count > ZP.count || send.count == 0) {
				err('����������� ������� ����������');
				$('#count').focus();
			} else if(!REGEXP_CENA.test(send.cena) || send.cena == 0) {
				err('����������� ������� ����');
				$('#cena').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg('������� �������� �����������.');
					} else
						dialog.abort();
				},'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				left:123,
				top:-47,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#zpInfo .defect,#zpInfo .return,#zpInfo .writeoff', function() {
		if(zpAvaiNo(ZP.count))
			return;
		var rus = {defect:'����������', return:'�������', 'writeoff':'��������'},
			end = {defect:'���', return:'��', 'writeoff':'���'},
			type = $(this).attr('class'),
			html = '<table class="zp_dec_dialog">' +
				'<tr><td class="label r">����������:<td><input type="text" id="count" value="1"><span>(max: <b>' + ZP.count + '</b>)</span>' +
				'<tr><td class="label r top">����������:<td><textarea id="prim"></textarea>' +
				'</table>',
			dialog = _dialog({
				width:400,
				head:rus[type] + ' ��������',
				content:html,
				submit:submit
			});

		$('#count').focus().select();

		function submit() {
			var send = {
				op:'zp_other',
				zp_id:ZP.id,
				type:type,
				count:$('#count').val(),
				prim:$('#prim').val()
			};
			if(!REGEXP_NUMERIC.test(send.count) || send.count > ZP.count || send.count == 0) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">����������� ������� ����������.</SPAN>',
					left:73,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
				$('#count').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg(rus[type] + ' �������� ��������' + end[type] + '.');
					}
				},'json');
			}
		}
	})
	.on('click', '#zpInfo .move .img_del', function() {
		var id = $(this).attr('val');
		var dialog = _dialog({
			top:110,
			width:250,
			head:'�������� ������',
			content:'<center>����������� �������� ������.</center>',
			butSubmit:'�������',
			submit:function() {
				var send = {
					op:'zp_move_del',
					id:id
				};
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						zpAvaiUpdate();
						dialog.close();
						_msg('������ �������.');
					}
				}, 'json');
			}
		});
	})
	.on('click', '#zpInfo .move ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = {
				op:'zp_move_next',
				zp_id:ZP.id,
				page:$(this).attr('val')
			};
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.spisok).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '#zpInfo .compat_add', function() {
		var sp = ZP,
			html = '<div class="compatAddTab">' +
				'<div class="name">' +
					sp.name + '<br />' +
					sp.for +
				'</div>' +
				'<table class="prop">' +
					(sp.version ? '<tr><td class="label">������:<td>' + sp.version : '') +
					(sp.color_id > 0 ? '<tr><td class="label">����:<td>' + sp.color_name : '') +
				'</table>' +
				'<div class="headName">�������� � ����������:</div>' +
				'<div id="dev"></div>' +
				'<div id="cres"></div>' +
			'</div>',
			dialog = _dialog({
				top:90,
				width:400,
				head:'���������� ������������� � ������� ������������',
				content:html,
				butSubmit:'��������',
				submit:submit
			}),
			cres = $('#cres'),
			dev = {},
			go = 0;
		$('#dev').device({
			width:220,
			device_id:sp.device,
			vendor_id:sp.vendor,
			add:1,
			func:devSelect
		});

		function devSelect(obj) {
			dialog.abort();
			go = 0;
			dev = obj;
			cres.html('&nbsp;');
			if(obj.device_id > 0 && obj.vendor_id > 0 && obj.model_id > 0) {
				if(obj.device_id == sp.device && obj.vendor_id == sp.vendor && obj.model_id == sp.model) {
					cres.html('<em class="red">���������� ������� ������������� �� ��� �� ����������.</em>');
					return;
				}
				var send = {
					op:'zp_compat_find',
					zp_id:sp.id,
					name_id:sp.name_id,
					device_id:obj.device_id,
					vendor_id:obj.vendor_id,
					model_id:obj.model_id,
					color_id:sp.color_id
				};
				cres.addClass('_busy');
				$.post(AJAX_WS, send, function(res) {
					cres.removeClass('_busy');
					if(res.success)
						finded(res);
				}, 'json');
			}
		}

		function finded(res) {
			if(res.id) {
				if(res.compat_id == sp.compat_id) {
					cres.html('<em class="red">��������� �������� ��� �������� �������������� ���� ��������.</em>');
					return;
				}
				cres.html('�������� <B>' + res.name + '</B><br />' +
						  '����� ��������� � �������������.<br /><br />' +
						  '���������� � ���������, ��������<br />' +
						  '� ������� ����� ������� � ������<br />' +
						  '����� ��� ����� ���������.');
			} else
				cres.html('�������� <b>' + res.name + '</b><br />' +
						  '��� � �������� ���������.<br /><br />' +
						  '��� ���������� ������������� ���<br />' +
						  '����� ������������� ������� � �������.');
			go = 1;
		}

		function submit() {
			if(go == 0) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">�������� ���������� ��� ���������� �������������.</SPAN>',
					left:103,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
				return;
			}
			var send = {
				op:'zp_compat_add',
				zp_id:sp.id,
				device_id:dev.device_id,
				vendor_id:dev.vendor_id,
				model_id:dev.model_id
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				dialog.abort();
				if(res.success) {
					dialog.close();
					_msg('������������� �������.');
					window.location.reload();
				}
			}, 'json');
		}
	})
	.on('click', '#zpInfo .compatSpisok .img_del', function() {
		var id = $(this).attr('val');
		var dialog = _dialog({
			top:110,
			width:250,
			head:'�������� �������������',
			content:'<center>����������� �������� �������������.</center>',
			butSubmit:'�������',
			submit:function() {
				var send = {
					op:'zp_compat_del',
					id:id,
					zp_id:ZP.id
				};
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('������������� �������.');
						$('.compatCount').html(res.count);
						$('.compatSpisok').html(res.spisok);
					}
				}, 'json');
			}
		});
		return false;
	})

	.ready(function() {
		if($('#zp').length) {
			$('.vkButton').click(function() {
				var html =
						'<table class="zp-add-tab">' +
							'<tr><td class="label">������������:<td><input type="hidden" id="name_id" />' +
							'<tr><td class="label topi">����������:<td id="add_dev">' +
							'<tr><td class="label">������:<td><input type="text" id="version" />' +
							'<tr><td class="label">����:<td><input type="hidden" id="color_id" />' +
						'</table>',
					dialog = _dialog({
						top:50,
						width:460,
						head:'�������� ����� �������� � �������',
						content:html,
						submit:submit
					});
				zpNameSelect(_num($('#dev_device').val()), $('#zp_name').val());
				$('#add_dev').device({
					width:200,
					add:1,
					device_id:$('#dev_device').val(),
					vendor_id:$('#dev_vendor').val(),
					model_id:$('#dev_model').val(),
					func_device:zpNameSelect
				});
				$('#color_id')._select({
					width:130,
					title0:'���� �� ������',
					spisok:COLOR_SPISOK
				});

				function submit() {
					var send = {
						op:'zp_add',
						name_id:_num($('#name_id').val()),
						device_id:_num($('#add_dev_device').val()),
						vendor_id:$('#add_dev_vendor').val(),
						model_id:$('#add_dev_model').val(),
						version:$('#version').val(),
						color_id:$('#color_id').val()
					};
					if(!send.name_id) dialog.err('�� ������� ������������ ��������');
					else if(!send.device_id) dialog.err('�� ������� ����������');
					else {
						dialog.process();
						$.post(AJAX_WS, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								location.href = URL + '&p=zp&d=info&id=' + res.id;
							}
						},'json');
					}
				}
			});
			$('#find')
				._search({
					width:250,
					focus:1,
					txt:'������� �����...',
					enter:1,
					func:zpSpisok
				})
				.inp(ZP.find);
			$('#zp_menu')._dropdown({
				head:'����� �������',
				spisok:[
					{uid:0,title:'����� �������'},
					{uid:1,title:'�������'},
					{uid:2,title:'��� � �������'},
					{uid:3,title:'�����'},
					{uid:4,title:'������'}
				],
				func:zpSpisok
			});
			$('#dev').device({
				width:220,
				type_no:1,
				device_ids:WS_DEVS,
				device_id:ZP.device,
				vendor_id:ZP.vendor,
				model_id:ZP.model,
				func_device:function() {
					$('#zp_name')._select(0);
				},
				func:zpSpisok
			});
			$('#zp_sort')._dropdown({
				head:'�� ��������',
				spisok:[
					{uid:0,title:'�� ��������'},
					{uid:1,title:'�� ���� ����������'}
				],
				func:zpSpisok
			});
			zpFilter();
		}
	});
