var AJAX_WS = APP_HTML + '/ajax/ws.php?' + VALUES,
	scannerWord = '',
	scannerTime = 0,
	scannerTimer,
	scannerDialog,
	scannerDialogShow = false,
	charSpisok = {
		48:'0',
		49:1,
		50:2,
		51:3,
		52:4,
		53:5,
		54:6,
		55:7,
		56:8,
		57:9,
		65:'A',
		66:'B',
		67:'C',
		68:'D',
		69:'E',
		70:'F',
		71:'G',
		72:'H',
		73:'I',
		74:'J',
		75:'K',
		76:'L',
		77:'M',
		78:'N',
		79:'O',
		80:'P',
		81:'Q',
		82:'R',
		83:'S',
		84:'T',
		85:'U',
		86:'V',
		87:'W',
		88:'X',
		89:'Y',
		90:'Z',
		189:'-'
	},

	modelImageGet = function() {
		var send = {
				op:'model_img_get',
				model_id:$('#dev_model').val()
			},
			dev = $('#device_image');
		dev.html('');
		if(send.model_id > 0) {
			dev.addClass('busy');
			$.post(AJAX_WS, send, function(res) {
				if(res.success)
					 dev.html(res.img)
						.find('img').on('load', function() {
							$(this).show().parent().removeClass('busy');
						});
			}, 'json');
		}
	},

	colorSel = function(spisok) {
		$('#color_id')._select({
			width:120,
			title0:'���� �� ������',
			spisok:spisok,
			func:colorSelDop
		});
		$('#color_dop')._select({
			width:120,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK,
			func:function(id) {
				colorSel(id ? COLORPRE_SPISOK : COLOR_SPISOK);
			}
		});
	},
	colorSelDop = function(id) {
		if(id) {
			if($('#color_id_select').length == 0)
				colorSel($('#color_dop').val() > 0 ? COLORPRE_SPISOK : COLOR_SPISOK);
			$('.color_dop').removeClass('dn');
		} else {
			colorSel(COLOR_SPISOK);
			$('#color_dop')._select(0);
			$('.color_dop').addClass('dn');
		}
	},

	clientAdd = function(callback) {
		var html = '<table style="border-spacing:10px">' +
				'<tr><td class="label">���:<td><input type="text" id="fio" style="width:220px;">' +
				'<tr><td class="label">�������:<td><input type="text" id="telefon" style=width:220px;>' +
			'</table>',
			dialog = _dialog({
				width:340,
				head:'���������� �o���� �������',
				content:html,
				submit:submit
			});
		$('#fio').focus();
		function submit() {
			var send = {
				op:'client_add',
				fio:$('#fio').val(),
				telefon:$('#telefon').val()
			};
			if(!send.fio) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">�� ������� ��� �������.</SPAN>',
					top:-47,
					left:81,
					indent:40,
					show:1,
					remove:1
				});
				$('#fio').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������ �����.');
						if(typeof callback == 'function')
							callback(res);
						else
							document.location.href = URL + '&p=client&d=info&id=' + res.uid;
					} else dialog.abort();
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
	},

	zayavFilter = function () {
		var v = {
				op:'zayav_spisok',
				find:$('#find')._search('val'),
				sort:$('#sort').val(),
				desc:$('#desc').val(),
				status:$('#status').val(),
				finish:$('#day_finish').val(),
				diff:$('#diff').val(),
				zpzakaz:$('#zpzakaz').val(),
				device:$('#dev_device').val(),
				vendor:$('#dev_vendor').val(),
				model:$('#dev_model').val(),
				place:$('#device_place').val()
			},
			loc = '';
		if(v.status != '1')
			v.finish = '0000-00-00';
		if(v.sort != '1') loc += '.sort=' + v.sort;
		if(v.desc != '0') loc += '.desc=' + v.desc;
		if(v.find) loc += '.find=' + escape(v.find);
		else {
			if(v.status > 0) loc += '.status=' + v.status;
			if(v.finish != '0000-00-00') loc += '.finish=' + v.finish;
			if(v.diff > 0) loc += '.diff=' + v.diff;
			if(v.zpzakaz > 0) loc += '.zpzakaz=' + v.zpzakaz;
			if(v.device > 0) loc += '.device=' + v.device;
			if(v.vendor > 0) loc += '.vendor=' + v.vendor;
			if(v.model > 0) loc += '.model=' + v.model;
			if(v.place != 0) loc += '.place=' + v.place;
		}
		VK.callMethod('setLocation', hashLoc + loc);

		_cookie('zayav_find', escape(v.find));
		_cookie('zayav_sort', v.sort);
		_cookie('zayav_desc', v.desc);
		_cookie('zayav_status', v.status);
		_cookie('zayav_finish', v.finish);
		_cookie('zayav_diff', v.diff);
		_cookie('zayav_zpzakaz', v.zpzakaz);
		_cookie('zayav_device', v.device);
		_cookie('zayav_vendor', v.vendor);
		_cookie('zayav_model', v.model);
		_cookie('zayav_place', escape(v.place));

		return v;
	},
	zayavSpisok = function() {
		var send = zayavFilter();
		$('.condLost')[(send.find ? 'add' : 'remove') + 'Class']('hide');

		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('.result').html(res.all);
			$('#spisok').html(res.html);
			$('#mainLinks').removeClass('busy');
		}, 'json');
	},
	zayavMoneyUpdate = function() {//���������� ���������� � ��������
		var send = {
			op:'zayav_money_update',
			zayav_id:ZAYAV.id
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				$('b.acc').html(res.acc);
				$('.acc_tr')[(!res.acc ? 'add' : 'remove') + 'Class']('dn');
				$('b.op').html(res.opl);
				$('.op_tr')[(!res.opl ? 'add' : 'remove') + 'Class']('dn');
				$('.dopl')
					[(!res.diff ? 'add' : 'remove') + 'Class']('dn')
					.html((res.diff > 0 ? '+' : '') + res.diff);
				//���������� �������� �� ������
				$('.ze-spisok').remove();
				$('#ze_acc').html(res.acc_sum).after(res.html);
				ZAYAV.expense = res.array;
				ZAYAV.worker_zp = res.worker_zp;
			}
		}, 'json');
	},
	zayavDevSelect = function(dev) {
		modelImageGet(dev);
		if(dev.device_id == 0) {
			$('.equip_spisok').html('');
			$('.tr_equip').addClass('dn');
		} else if(dev.vendor_id == 0 && dev.model_id == 0) {
			var send = {
				op:'equip_check_get',
				device_id:dev.device_id
			};
			$.post(AJAX_WS, send, function(res) {
				if(res.spisok) {
					$('.equip_spisok').html(res.spisok);
					$('.tr_equip').removeClass('dn');
				} else {
					$('.equip_spisok').html('');
					$('.tr_equip').addClass('dn');
				}
			}, 'json');
		}
	},
	zayavPlace = function(other) {
		if(other == undefined)
			other = '';
		if(!window.PLACE_OTHER) {
			DEVPLACE_SPISOK.push({
				uid:0,
				title:'������: ' +
					  '<input type="text" ' +
							 'id="place_other" ' +
				   (!other ? 'class="dn" ' : '') +
							 'maxlength="20" ' +
							 'value="' + other + '" />'
			});
			window.PLACE_OTHER = 1;
		}
		$('#place')._radio({
			spisok:DEVPLACE_SPISOK,
			light:1,
			func:function(val) {
				$('#place_other')[(val != 0 ? 'add' : 'remove') + 'Class']('dn');
				if(val == 0)
					$('#place_other').val('').focus();
			}
		});
	},
	kvitHtml = function(id) {
		var params =
			'scrollbars=yes,' +
			'resizable=yes,' +
			'status=no,' +
			'location=no,' +
			'toolbar=no,' +
			'menubar=no,' +
			'width=680,' +
			'height=500,' +
			'left=20,' +
			'top=20';
		window.open(APP_HTML + '/view/kvit_html.php?' + VALUES + '&id=' + id, 'kvit', params);
	},

	cartridgeNew = function(id, callback) {
		var t = $(this),
			html = '<table id="cartridge-new-tab">' +
				'<tr><td class="label"><b>������ ���������:</b><td><input type="text" id="name" />' +
				'<tr><td class="label r">��������:<td><input type="text" id="cost_filling" class="money" maxlength="11" /> ���.' +
				'<tr><td class="label r">��������������:<td><input type="text" id="cost_restore" class="money" maxlength="11" /> ���.' +
				'<tr><td class="label r">������ ����:<td><input type="text" id="cost_chip" class="money" maxlength="11" /> ���.' +
				'</table>',
			dialog = _dialog({
				top:20,
				head:'���������� ������ ���������',
				content:html,
				submit:submit
			});
		$('#name').focus();
		$('#name,#cost_filling,#cost_restore,#cost_chip').keyEnter(submit);
		function submit() {
			var send = {
				op:'cartridge_new',
				name:$('#name').val(),
				cost_filling:_num($('#cost_filling').val()),
				cost_restore:_num($('#cost_restore').val()),
				cost_chip:_num($('#cost_chip').val()),
				from:$('#setup-service-cartridge').length ? 'setup' : ''
			};
			if(!send.name) {
				dialog.err('�� ������� ������������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						if(send.from == 'setup')
							$('#spisok').html(res.spisok);
						else {
							CARTRIDGE_SPISOK = res.spisok;
							$('#' + id)._select(res.spisok);
							$('#' + id)._select(res.insert_id);
							callback(res.insert_id);
						}
						dialog.close();
						_msg('�������!');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},
	zayavCartridgeAdd = function() {
		if(!window.CLIENT)
			CLIENT = {
				id:0,
				fio:''
			};
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label">������:' +
						'<td><input type="hidden" id="client_id" value="' + CLIENT.id + '" />' +
							'<b>' + CLIENT.fio + '</b>' +
					'<tr><td class="label"><b>���������� ����������:</b><td><input type="text" id="count" /> ��.' +
					'<tr><td class="label topi">������ ����������:<td id="crt">' +
					'<tr><td class="label top">�������:<td><textarea id="comm"></textarea>' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'����� ������ �� �������� ����������',
				content:html,
				submit:submit
			});
		if(!CLIENT.id)
			$('#client_id').clientSel({add:1});
		$('#count').focus();
		$('#crt').cartridge();
		$('#comm').autosize();
		function submit() {
			var send = {
				op:'zayav_cartridge_add',
				client_id:_num($('#client_id').val()),
				count:_num($('#count').val()),
				ids:$('#crt').cartridge('get'),
				comm:$('#comm').val()
			};
			if(!send.client_id) dialog.err('�� ������ ������');
			else if(!send.count) {
				dialog.err('�� ������� ���������� ����������');
				$('#count').focus();
//			else if(!send.ids) dialog.err('�� ������� �� ������ ���������');
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������');
						location.href = URL + '&p=zayav&d=info&id=' + res.id + '&from=cartridge';
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},
	cartridgeFilter = function () {
		var v = {
				op:'zayav_cartridge_spisok',
				sort:$('#sort').val(),
				desc:$('#desc').val(),
				status:$('#status').val()
			};
		return v;
	},
	cartridgeSpisok = function() {
		var send = cartridgeFilter();
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('.result').html(res.all);
				$('#spisok').html(res.html);
			}
		}, 'json');
	},
	zayavInfoCartridgeAdd = function() {
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label topi">������ ����������:<td id="crt">' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'���������� ���������� � ������',
				content:html,
				submit:submit
			});
		$('#crt').cartridge();
		function submit() {
			var send = {
				op:'zayav_info_cartridge_add',
				zayav_id:ZAYAV.id,
				ids:$('#crt').cartridge('get')
			};
			if(!send.ids) dialog.err('�� ������� �� ������ ���������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#cart-tab').html(res.html);
						_msg('�������.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},

	zpFilter = function() {
		var v = {
				op:'zp_spisok',
				find:$.trim($('#find')._search('val')),
				menu:$('#zp_menu').val(),
				name:$('#zp_name').val(),
				device:$('#dev_device').val(),
				vendor:$('#dev_vendor').val(),
				model:$('#dev_model').val(),
				bu:$('#bu').val(),
				sort:$('#zp_sort').val()
			},
			loc = '';
		if(v.find) loc += '.find=' + escape(v.find);
		if(v.menu > 0) loc += '.menu=' + v.menu;
		if(v.name > 0) loc += '.name=' + v.name;
		if(v.device > 0) loc += '.device=' + v.device;
		if(v.vendor > 0) loc += '.vendor=' + v.vendor;
		if(v.model > 0) loc += '.model=' + v.model;
		if(v.bu > 0) loc += '.bu=' + v.bu;
		if(v.sort > 0) loc += '.sort=' + v.sort;
		VK.callMethod('setLocation', hashLoc + loc);

		_cookie('zp_find', escape(v.find));
		_cookie('zp_menu', v.menu);
		_cookie('zp_name', v.name);
		_cookie('zp_device', v.device);
		_cookie('zp_vendor', v.vendor);
		_cookie('zp_model', v.model);
		_cookie('zp_bu', v.bu);
		_cookie('zp_sort', v.sort);

		$('#zp-filter')[(v.menu == 4 ? 'add' : 'remove') + 'Class']('dn');
		$('#sort')[(v.menu == 4 ? 'add' : 'remove') + 'Class']('dn');

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
	},

	incomeSpisok = function() {
		var send = {
			op:'income_spisok',
			day:$('.selected').val(),
			del:$('#del').val()
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				$('.inc-path').html(res.path);
				$('.spisok').html(res.html);
			}
		}, 'json');
	},
	expenseFilter = function() {
		var arr = [],
			inp = $('#monthList input');
		for(var n = 1; n <= 12; n++)
			if(inp.eq(n - 1).val() == 1)
				arr.push(n);
		return {
			op:'expense_spisok',
			category:$('#category').val(),
			worker:$('#worker').val(),
			year:$('#year').val(),
			month:arr.join()
		};
	},
	expenseSpisok = function() {
		$('#mainLinks').addClass('busy');
		$.post(AJAX_WS, expenseFilter(), function(res) {
			$('#mainLinks').removeClass('busy');
			if(res.success) {
				$('#spisok').html(res.html);
				$('#monthList').html(res.mon);
			}
		}, 'json');
	},
	salarySpisok = function() {
		if($('.headName').hasClass('_busy'))
			return;
		var send = {
			op:'salary_spisok',
			worker_id:WORKER_ID,
			year:$('#year').val(),
			mon:$('#salmon').val()
		};
		$('.headName').addClass('_busy');
		$.post(AJAX_WS, send, function (res) {
			$('.headName').removeClass('_busy');
			if(res.success) {
				MON = send.mon * 1;
				YEAR = send.year;
				$('.headName em').html(MONTH_DEF[MON] + ' ' + YEAR);
				$('#spisok').html(res.html);
				$('#monthList').html(res.month);
			}
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
		title0:'������� ������� ������ �������...',
		spisok:[],
		write:1,
		nofind:'�������� �� �������',
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
$.fn.device = function(o) {
	o = $.extend({
		width:150,
		func:function() {},
		type_no:0,
		device_id:0,
		vendor_id:0,
		model_id:0,
		device_ids:null, // ������ id, ������� ����� �������� � ������ ��� ���������
		vendor_ids:null, // ��� ��������������
		model_ids:null,  // ��� �������
		add:0,
		device_funcAdd:null, // ������� �����, ���� ������ ��������� ����� ��������
		vendor_funcAdd:null,
		model_funcAdd:null
	},o);

	var t = $(this),
		id = t.attr('id'),
		html = '<input type="hidden" id="' + id + '_device" value="' + o.device_id + '">' +
			'<input type="hidden" id="' + id + '_vendor" value="' + o.vendor_id + '">' +
			'<input type="hidden" id="' + id + '_model" value="' + o.model_id + '">',
		device_no = ['���������� �� �������','����� ����������'],
		vendor_no = ['������������� �� ������','����� �������������'],
		model_no = ['������ �� �������','����� ������'],
		dialog;
	t.html(html);
	var devSel = $('#' + id + '_device'),
		venSel = $('#' + id + '_vendor'),
		modSel = $('#' + id + '_model');

	// �������� ������ ������ ���������, ������� ����� �������� � ������
	if(o.device_ids) {
		DEV_SPISOK = [];
		for(n = 0; n < o.device_ids.length; n++) {
			var uid = o.device_ids[n];
			DEV_SPISOK.push({uid:uid, title:DEV_ASS[uid]});
		}
	}

	// �������� ������ ������ ��������������, ������� ����� �������� � ������
	if(o.vendor_ids) {
		var vendors = {};
		for(k in VENDOR_SPISOK) {
			for(n = 0; n < VENDOR_SPISOK[k].length; n++) {
				var sp = VENDOR_SPISOK[k][n];
				if(o.vendor_ids.indexOf(sp.uid) >= 0) {
					if(vendors[k] == undefined)
						vendors[k] = [];
					vendors[k].push(sp);
				}
			}
		}
		VENDOR_SPISOK = vendors;
	}

	// �������� ������ ������ �������, ������� ����� �������� � ������
	if(o.model_ids) {
		var models = {};
		for(k in MODEL_SPISOK)
			for(n = 0; n < MODEL_SPISOK[k].length; n++) {
				var sp = MODEL_SPISOK[k][n];
				if(o.model_ids.indexOf(sp.uid) >= 0) {
					if(!models[k])
						models[k] = [];
					models[k].push(sp);
				}
			}
		MODEL_SPISOK = models;
	}

	// ���������� ����� ���������
	if (o.add > 0) {
		o.device_funcAdd = function() {
			var html = '<table class="device-add-tab">' +
				'<tr><td class="label">��������:<td><input type="text" id="daname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'���������� �o���� ����������',
				content:html,
				submit:deviceAddSubmit
			});
			$('#daname')
				.focus()
				.keyEnter(deviceAddSubmit);
		};
		o.vendor_funcAdd = function () {
			var html ='<table class="device-add-tab">' +
				'<tr><td class="label">��������:<td><input type="text" id="vaname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'���������� �o���� �������������',
				content:html,
				submit:vendorAddSubmit
			});
			$('#vaname')
				.focus()
				.keyEnter(vendorAddSubmit);
		};
		o.model_funcAdd = function(){
			var html = '<table class="device-add-tab">' +
				'<tr><td class="label">��������:<td><input type="text" id="maname">' +
				'</table>';
			dialog = _dialog({
				width:300,
				head:'���������� �o��� ������',
				content:html,
				submit:modelAddSubmit,
				focus:'#model_name'
			});
			$('#maname')
				.focus()
				.keyEnter(modelAddSubmit);
		};
	}

	function deviceAddSubmit() {
		var send = {
			op:'base_device_add',
			name:$('#daname').focus().val()
		};
		if(!send.name)
			addHint('�� ������� �������� ����������.');
		else if(name_test(DEV_SPISOK, send.name))
			addHint();
		else {
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				dialog.abort();
				if(res.success) {
					DEV_SPISOK.push({uid:res.id, title:send.name});
					DEV_ASS[res.id] = name;
					devSel
						._select(DEV_SPISOK)
						._select(res.id);
					getVendor(0);
					modSel.val(0)._select('remove'); //��������� ������ ������ � ��������������� � 0
					o.func(getIds());
					dialog.close();
				}
			} ,'json');
		}
	}
	function vendorAddSubmit() {
		var dv = devSel.val(),
			send = {
			op:'base_vendor_add',
			device_id:dv,
			name:$('#vaname').focus().val()
		};
		if(!send.name)
			addHint('�� ������� �������� �������������.');
		else if(name_test(VENDOR_SPISOK[dv], send.name))
			addHint();
		else {
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				dialog.abort();
				if(res.success) {
					// ���� � ���������� ��� ��������������, ������� �������� ������ ������
					if(!VENDOR_SPISOK[dv])
						VENDOR_SPISOK[dv] = [];
					VENDOR_SPISOK[dv].push({uid:res.id, title:send.name});
					VENDOR_ASS[res.id] = send.name;
					venSel
						._select(VENDOR_SPISOK[dv])
						._select(res.id);
					getModel(0);
					dialog.close();
				}
			}, 'json');
		}
	}
	function modelAddSubmit() {
		var vv = venSel.val(),
			send = {
			op:'base_model_add',
			device_id:devSel.val(),
			vendor_id:vv,
			name:$('#maname').focus().val()
		};
		if(!send.name)
			addHint('�� ������� �������� ������.');
		else if(name_test(MODEL_SPISOK[vv], send.name)) {
			addHint();
		} else {
			dialog.process();
			$.post(AJAX_WS, send, function (res) {
				dialog.abort();
				if(res.success) {
					// ���� � ������������� ��� �������, ������� �������� ������ ������
					if(!MODEL_SPISOK[vv])
						MODEL_SPISOK[vv] = [];
					MODEL_SPISOK[vv].push({uid:res.id, title:send.name});
					MODEL_ASS[res.id] = send.name;
					modSel
						._select(MODEL_SPISOK[vv])
						._select(res.id);
					dialog.close();
				}
			}, 'json');
		}
	}
	function addHint(msg) {
		msg = msg || '����� �������� ��� ���� � ������.';
		dialog.bottom.vkHint({
			msg:'<SPAN class="red">' + msg + '</SPAN>',
			top:-47,
			left:53,
			indent:50,
			show:1,
			remove:1
		});
	}

	// ����� ������ ���������
	devSel._select({
		width:o.width,
		block:1,
		title0:device_no[o.type_no],
		spisok:DEV_SPISOK,
		func:function(id) {
			venSel.val(0);
			modSel.val(0)._select('remove'); //��������� ������ ������ � ��������������� � 0, ���� ��� �����
			if(!id)
				venSel._select('remove');
			else
				getVendor(0);
			o.func(getIds());
		},
		funcAdd:o.device_funcAdd,
		bottom:3
	});
	if(o.device_id)
		getVendor();

	// ����� ������ ��������������
	function getVendor(vendor_id) {
		if(vendor_id != undefined)
			o.vendor_id = vendor_id; // ���������� �������� �������������, ���� �����
		venSel.val(o.vendor_id);
		venSel._select({
			width:o.width,
			block:1,
			title0:vendor_no[o.type_no],
			spisok:VENDOR_SPISOK[devSel.val()], // �������� ���������� �������� �� ��� �������
			func:function(id) {
				modSel.val(0);
				if(!id)
					modSel._select('remove'); //��������� ������ ������ � ��������������� � 0
				else
					getModel(0);
				o.func(getIds());
			},
			funcAdd:o.vendor_funcAdd,
			bottom:3
		});
		venSel._select(o.vendor_id);
		if(o.vendor_id)
			getModel();
	}

	// ����� ������ �������
	function getModel(model_id) {
		if(model_id != undefined)
			o.model_id = model_id; //���������� �������� ������, ���� �����
		modSel.val(o.model_id);
		modSel._select({
			width:o.width,
			block:1,
			write:1,
			title0:model_no[o.type_no],
			spisok:MODEL_SPISOK[venSel.val()],
			limit:50,
			funcAdd:o.model_funcAdd,
			bottom:10,
			func:function() { o.func(getIds()); }
		});
		modSel._select(o.model_id);
	}

	// �������� �� ���������� ����� ��� �������� ������ ��������
	function name_test(spisok, name) {
		if(spisok) {
		name = name.toLowerCase();
		for(var n = 0; n < spisok.length; n++)
			if(spisok[n].title.toLowerCase() == name)
				return true;
		}
		return false;
	}

	function getIds() {
		return {
			device_id:devSel.val(),
			vendor_id:venSel.val(),
			model_id:modSel.val()
		};
	}
};
$.fn.zayavExpense = function(o) {
	var t = $(this),
		id = t.attr('id'),
		num = 1,
		n;

	if(typeof o == 'string') {
		if(o == 'get') {
			var units = t.find('.ptab'),
				send = [];
			for(n = 0; n < units.length; n++) {
				var u = units.eq(n),
					attr = id + u.attr('val'),
					cat_id = $('#' + attr + 'cat').val(),
					sum = u.find('.zesum').val(),
					dop = '';
				if(cat_id == 0)
					continue;
				if(!_cena(sum) && sum != '0')
					return 'sum_error';
				if(ZE_TXT[cat_id])
					dop = u.find('.zetxt').val();
				else if(ZE_WORKER[cat_id]) {
					dop = $('#' + attr + 'worker').val();
				} else if(ZE_ZP[cat_id])
					dop = $('#' + attr + 'zp').val();

				send.push(cat_id + ':' +
						  dop + ':' +
						  sum);
			}
			return send.join();
		}
	}

	t.html('<div id="_ze-edit"></div>');
	var ze = t.find('#_ze-edit');

	if(typeof o == 'object')
		for(n = 0; n < o.length; n++)
			itemAdd(o[n])

	itemAdd();

	function itemAdd(v) {
		if(!v)
			v = [
				0, //0 - ���������
				'',//1 - ��������, id ���������� ��� id ��������
				'' //2 - �����
			];
		var attr = id + num,
			attr_cat = attr + 'cat',
			attr_worker = attr + 'worker',
			attr_zp = attr + 'zp',
			html =
				'<table id="ptab'+ num + '" class="ptab" val="' + num + '"><tr>' +
					'<td><input type="hidden" id="' + attr_cat + '" value="' + v[0] + '" />' +
					'<td class="tddop">' +
						(v[0] && ZE_TXT[v[0]] ? '<input type="text" class="zetxt" placeholder="�������� �� �������" tabindex="' + (num * 10 - 1) + '" value="' + v[1] + '" />' : '') +
						(v[0] && ZE_WORKER[v[0]] ? '<input type="hidden" id="' + attr_worker + '" value="' + v[1] + '" />' : '') +
						(v[0] && ZE_ZP[v[0]] ? '<input type="hidden" id="' + attr_zp + '" value="' + v[1] + '" />' : '') +
					'<td class="tdsum' + (v[0] ? '' : ' dn') + '">' +
						'<input type="text" class="zesum" maxlength="6" tabindex="' + (num * 10) + '" value="' + v[2] + '" />���.' +
				'</table>';
		ze.append(html);
		var ptab = $('#ptab' + num),
			tddop = ptab.find('.tddop'),
			zesum = ptab.find('.zesum');
		$('#' + attr_cat)._select({
			width:130,
			disabled:0,
			title0:'���������',
			spisok:ZE_SPISOK,
			func:function(id) {
				ptab.find('.tdsum')[(id ? 'remove' : 'add') + 'Class']('dn');
				if(ZE_TXT[id]) {
					tddop.html('<input type="text" class="zetxt" placeholder="�������� �� �������" tabindex="' + (num * 10 - 11) + '" />');
					tddop.find('.zetxt').focus();
				} else if(ZE_WORKER[id]) {
					tddop.html('<input type="hidden" id="' + attr_worker + '" />');
					$('#' + attr_worker)._select({
						width:240,
						title0:'��������� �� ������',
						spisok:WORKER_SPISOK,
						func:function(v) {
							zesum.focus();
						}
					});
					zesum.focus();
				} else if(ZE_ZP[id]) {
					tddop.html('<input type="hidden" id="' + attr_zp + '" />');
					$('#' + attr_zp)._select({
						width:240,
						title0:'�������� �� �������',
						spisok:ZAYAV.zp_avai,
						func:function(v) {
							zesum.focus();
						}
					});
					zesum.focus();
				} else {
					tddop.html('');
					zesum.focus();
				}
				zesum.val(id == 1 ? ZAYAV.worker_zp : '');
				if(id && !ptab.next().hasClass('ptab'))
					itemAdd();
			}
		});
		if(v[0] && ZE_WORKER[v[0]])
			$('#' + attr_worker)._select({
				width:240,
				disabled:0,
				title0:'���������',
				spisok:WORKER_SPISOK,
				func:function(v) {
					zesum.focus();
				}
			});
		if(v[0] && ZE_ZP[v[0]])
			$('#' + attr_zp)._select({
				width:240,
				title0:'�������� �� �������',
				spisok:ZAYAV.zp_avai,
				func:function(v) {
					zesum.focus();
				}
			});
		num++;
	}
	return t;
};
$.fn.cartridge = function(o) {
	var t = $(this),
		id = t.attr('id'),
		num = 1,
		n;

	if(typeof o == 'string') {
		if(o == 'get') {
			var units = t.find('.icar'),
				send = [],
				v;
			for(n = 0; n < units.length; n++) {
				v = units.eq(n).val();
				if(v == 0)
					continue;
				send.push(v);
			}
			return send.join();
		}
	}
	if(typeof o == 'object')
		for(var i = 0; i < o.length; i++) {
			add(o[i]);
			num++;
		}

	add();
	function add(v) {
		t.append('<input type="hidden" class="icar" id="car' + num + '" ' + (v ? 'value="' + v + '" ' : '') + '/>');
		$('#car' + num)._select({
			width:170,
			bottom:4,
			title0:'�������� �� ������',
			write:1,
			spisok:CARTRIDGE_SPISOK,
			func:add_test,
			funcAdd:function(id) {
				cartridgeNew(id, add_test);
			}
		});
	}
	function add_test(v) {//��������, ��� �� ��������� �������, ����� ��������� ����� ����
		if(!v)
			return;
		var units = t.find('.icar');
		for(n = 0; n < units.length; n++)
			if(units.eq(n).val() == 0)
				return;
		num++;
		add();
	}
};

$(document)
	.keydown(function(e) {
		if(scannerDialogShow)
			return;
//		if($('#scanner').length < 1)$('body').prepend('<div id="scanner"></div>');window.sc = $('#scanner');
		if(e.keyCode == 13) {
			var d = (new Date()).getTime(),
				time = d - scannerTime;
			if(scannerWord.length > 5 && time < 300) {
				scannerDialogShow = true;
				scannerTimer = setTimeout(timeStop, 500);
				scannerDialog = _dialog({
					head:'������ �����-����',
					width:250,
					content:'������� ���: <b>' + scannerWord + '</b>',
					butSubmit:'�����'
				});
				var send = {
					op:'scanner_word',
					word:scannerWord
				};
				scannerDialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						if(e.target.localName == 'input') {
							scannerDialog.close();
							return;
						}
						if(_cookie('p') == 'zayav' && _cookie('d') == 'add') {
							$('#' + (res.imei ? 'imei' : 'serial')).val(send.word);
							scannerDialog.close();
							return;
						}
						if(res.zayav_id)
							document.location.href = URL + '&p=zayav&d=info&id=' + res.zayav_id;
						else {
							var client_id = _cookie('p') == 'client' && _cookie('d') == 'info' ? _cookie('id') : 0;
							document.location.href =
								URL +
								'&p=zayav&d=add&' + (res.imei ? 'imei' : 'serial') + '=' + send.word +
								(client_id ? '&back=client&id=' + client_id : '');
						}
					} else
						scannerDialog.abort();
				}, 'json');
			}
//			sc.append('<br /> - Enter<br />len = ' + scannerWord.length + '<br />time = ' + time + '<br />');
		} else {
			if(scannerDialog) {
				scannerDialog.close();
				scannerDialog = undefined;
			}
			if(scannerTimer)
				clearTimeout(scannerTimer);
			scannerTimer = setTimeout(timeStop, 500);
			if(!scannerWord)
				scannerTime = (new Date()).getTime();
			scannerWord += charSpisok[e.keyCode] ||  '';
//			sc.append((charSpisok[e.keyCode] ||  '') + ' = ' + e.keyCode + ' - ' + ((new Date()).getTime() - scannerTime) + '<br />');
		}
		function timeStop() {
			scannerWord = '';
			scannerTime = 0;
			if(scannerTimer)
				clearTimeout(scannerTimer);
			scannerTimer = undefined;
			scannerDialogShow = false;
//			sc.append('<br /> - Clear<br />');
		}
	})

	.on('mouseenter', '.zayav_link', function(e) {
		var t = $(this),
			tooltip = t.find('.tooltip');
		if(!tooltip.hasClass('empty'))
			return;
		var send = {
			op:'zayav_tooltip',
			id:t.attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			if(e.clientY < 90)
				tooltip.css('top', '12px');
			tooltip
				.html(res.html)
				.removeClass('empty');
		}, 'json');
	})
	.on('keyup', '#zayavNomer', function() {
		var t = $(this);
		if(t.hasClass('_busy'))
			return;
		t.next('.zayavNomerTab').remove().end()
		 .addClass('_busy');
		var send = {
			op:'zayav_nomer_info',
			nomer:t.val()
		};
		$.post(AJAX_WS, send, function(res) {
			t.removeClass('_busy');
			if(res.success)
				t.after(res.html);
		}, 'json');
	})

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

	.on('click', '#clientInfo .cedit', function() {
		var html = '<table class="client_edit">' +
			'<tr><td class="label">���:<td><input type="text" id="fio" value="' + $('.fio').html() + '">' +
			'<tr><td class="label">�������:<td><input type="text" id="telefon" value="' + $('.telefon').html() + '">' +
			'<tr><td class="label">����������:<td><input type="hidden" id="join">' +
			'<TR class=tr_join><td class="label">� ��������:<td><input type="hidden" id="client2">' +
			'</table>';
		var dialog = _dialog({
			head:'�������������� ������ �������',
			top:60,
			width:400,
			content:html,
			butSubmit:'���������',
			submit:submit
		});
		$('#fio,#telefon').keyEnter(submit);
		$('#join')._check();
		$('#join_check')
			.click(function() {
				$('.tr_join').toggle();
			})
			.vkHint({
				msg:'<B>����������� ��������.</B><br />' +
					'����������, ���� ���� ������ ��� ����� � ���� ������.<br /><br />' +
					'������� ������ ����� �����������.<br />�������� ������� �������.<br />' +
					'��� ������, ���������� � ������� ������ ������ �����<br />�����������.<br /><br />' +
					'��������, �������� ����������!',
				width:330,
				delayShow:1500,
				top:-162,
				left:-79,
				indent:80
			});
		$('#client2').clientSel({width:240});
		function submit() {
			var msg,
				send = {
					op:'client_edit',
					client_id:CLIENT.id,
					fio:$.trim($('#fio').val()),
					telefon:$.trim($('#telefon').val()),
					join:$('#join').val(),
					client2:$('#client2').val()
				};
			if(send.join == 0)
				send.client2 = 0;
			if(!send.fio) {
				msg = '�� ������� ��� �������.';
				$("#fio").focus();
			} else if(send.join == 1 && send.client2 == 0)
				msg = '������� ������� �������.';
			else if(send.join == 1 && send.client2 == CLIENT.id)
				msg = '�������� ������� �������.';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						$('.fio').html(send.fio);
						$('.telefon').html(send.telefon);
						CLIENT.fio = send.fio;
						if(send.client2 > 0)
							document.location.reload();
						dialog.close();
						_msg('������ ������� ��������.');
					}
				}, 'json');
			}
			if(msg)
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
	.on('click', '#clientInfo .remind_add', function() {
		var html = '<table class="remind_add_tab">' +
			'<tr><td class="label">������:<td><b>' + CLIENT.fio + '</b>' +
			'<tr><td class="label top">�������� �������:<td><TEXTAREA id="txt"></TEXTAREA>' +
			'<tr><td class="label">������� ���� ����������:<td><input type="hidden" id="data">' +
			'<tr><td class="label">������:<td><input type="hidden" id="private">' +
			'</table>';
		var dialog = _dialog({
				top:60,
				width:480,
				head:'���������� ������ �������',
				content:html,
				submit:submit
			}),
			txt = $('.remind_add_tab #txt'),
			day = $('.remind_add_tab #data'),
			priv = $('.remind_add_tab #private');
		txt.autosize().focus();
		day._calendar();
		priv._check();
		$('.remind_add_tab #private_check').vkHint({
			msg:'������� �������<br />������ ������ ��.',
			top:-71,
			left:-11,
			indent:'left',
			delayShow:1000
		});

		function submit() {
			var send = {
				op:'report_remind_add',
				from_client:1,
				client_id:CLIENT.id,
				zayav_id:0,
				txt:txt.val(),
				day:day.val(),
				private:priv.val()
			};
			if(!send.txt) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>�� ������� ���������� �����������.</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:150
				});
				txt.focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('����� ������� ������� ���������.');
						$('#remind_spisok').html(res.html);
					}
				}, 'json');
			}
		}//submit()
	})
	.on('click', '.go-client-info', function(e) {
		e.stopPropagation();
		location.href = URL + '&p=client&d=info&id=' + $(this).attr('val');
	})

	.on('click', '.day-finish-link', function(e) {//�������� ��������� ��������
		e.stopPropagation();
		var t = $(this),
			save = t.hasClass('no-save') ? 0 : 1;
		if(t.hasClass('_busy'))
			return;
		var dialog = _dialog({
				top:40,
				width:480,
				head:'��������� ��������',
				load:1,
				butSubmit:''
			}),
			send = {
				op:'zayav_day_finish',
				day:$('#day_finish').val(),
				zayav_spisok:$('#zayav').length
			};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
		$(document)
			.off('click', '#zayav-finish-calendar td.d:not(.old),#fc-cancel,.fc-old-sel')
			.on('click', '#zayav-finish-calendar td.d:not(.old),#fc-cancel,.fc-old-sel', function() {
				if(t.hasClass('_busy'))
					return;
				dialog.close();
				t.addClass('_busy');
				send = {
					op:'zayav_day_finish_save',
					day:$(this).attr('val'),
					zayav_id:window.ZAYAV ? ZAYAV.id : 0,
					save:save
				};
				$.post(AJAX_WS, send, function(res) {
					t.removeClass('_busy');
					if(res.success) {
						t.prev('input').val(send.day);
						t.find('span').html(res.data);
						if($('#zayav').length)
							zayavSpisok();
					}
				}, 'json');
			});
	})
	.on('click', '#zayav-finish-calendar .ch', function() {//��������� ��������� ��������
		if($('#fc-head').hasClass('_busy'))
			return;
		$('#fc-head').addClass('_busy');
		var send = {
			op:'zayav_day_finish_next',
			mon:$(this).attr('val'),
			day:$('#day_finish').val(),
			zayav_spisok:$('#zayav').length
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				$('#zayav-finish-calendar').after(res.html).remove();
			else
				$('#fc-head').removeClass('_busy');
		}, 'json');
	})

	.on('click', '#zayav-add', function() {
		var t = $(this),
			back = t.attr('val');
		if(t.hasClass('cartridge')) {
			var html =
				'<div id="zayav-add-tab">' +
					'<div class="unit" id="go-device">' +
						'���� � ������<br />������������' +
					'</div>' +
					'<div class="unit" id="cartridge-add">' +
						'��������, ��������������<br />����������' +
					'</div>' +
				'</div>',
				dialog = _dialog({
					top:30,
					width:300,
					head:'����� ������',
					content:html,
					butSubmit:''
				});
			$('#go-device').click(goDevice);
			$('#cartridge-add').click(function() {
				dialog.close();
				zayavCartridgeAdd();
			});
		} else
			goDevice();
		function goDevice() {
			location.href = URL + '&p=zayav&d=add&back=' + back;
		}
	})

	.on('click', '#zayav ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = zayavFilter();
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.zayav_unit', function() {
		_cookie('zback_scroll', VK_SCROLL);
		var t = $(this),
			from = t.hasClass('cart') ? '&from=cartridge' : '';
		location.href = URL + '&p=zayav&d=info&id=' + t.attr('val') + from;
	})
	.on('mouseenter', '.zayav_unit', function() {
		var t = $(this),
			msg = t.find('.note').html();
		if(msg)
			t.vkHint({
				width:150,
				msg:msg,
				ugol:'left',
				top:10,
				left:t.width() + 43,
				show:1,
				indent:5,
				delayShow:500
			});
	})
	.on('click', '#zayav #sort_radio div', zayavSpisok)
	.on('click', '#zayav #zpzakaz_radio div', zayavSpisok)
	.on('click', '#zayav .clear', function() {
		$('#find')._search('clear');
		$('#sort')._radio(1);
		$('#desc')._check(0);
		$('#status').rightLink(0);
		$('#day_finish').val('0000-00-00');
		$('.day-finish-link span').html('�� ������');
		$('#diff')._check(0);
		$('#zpzakaz')._radio(0);
		$('#dev').device({
			width:155,
			type_no:1,
			device_ids:Z.device_ids,
			vendor_ids:Z.vendor_ids,
			model_ids:Z.model_ids,
			func:zayavSpisok
		});
		$('#device_place')._select(0);
		zayavSpisok();
	})

	.on('click', '#zayav-cartridge ._next', function() {
		if($(this).hasClass('busy'))
			return;
		var next = $(this),
			send = cartridgeFilter();
		send.page = $(this).attr('val');
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '#zayav-info .zedit', function() {
		var html = '<table class="zayav-info-edit">' +
			'<tr><td class="label r">������:		<td><input type="hidden" id="client_id" value="' + ZAYAV.client_id + '">' +
			'<tr><td class="label r top">����������:<td><table><td id="dev"><td id="device_image"></table>' +
			'<tr><td><td>' + ZAYAV.images +
			'<tr><td class="label r">IMEI:		  <td><input type="text" id="imei" maxlength="20" value="' + ZAYAV.imei + '">' +
			'<tr><td class="label r">�������� �����:<td><input type="text" id="serial" maxlength="30" value="' + ZAYAV.serial + '">' +
			'<tr><td class="label r">����:' +
				'<td><input type="hidden" id="color_id" value="' + ZAYAV.color_id + '" />' +
					'<span class="color_dop dn"><tt>-</tt><input TYPE="hidden" id="color_dop" value="' + ZAYAV.color_dop + '" /></span>' +
			'<tr class="tr_equip' + (ZAYAV.equip ? '' : ' dn') + '">' +
				'<td class="label r top">������������:<td class="equip_spisok">' + ZAYAV.equip +
			'<tr><td class="label">��������� �������:<td><input type="text" class="money" id="pre_cost" maxlength="11" value="' + (ZAYAV.pre_cost ? ZAYAV.pre_cost : '') + '" /> ���.' +
		'</table>',
			dialog = _dialog({
				width:420,
				top:30,
				head:'������ �' + ZAYAV.nomer + ' - ��������������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#client_id').clientSel({width:267});
		$('#client_id_select').vkHint({
			msg:'���� ���������� ������, �� ���������� � ������� ������ ����������� �� ������ �������.',
			width:200,
			top:-83,
			left:-2,
			delayShow:1500
		});
		$('#dev').device({
			width:190,
			device_id:ZAYAV.device,
			vendor_id:ZAYAV.vendor,
			model_id:ZAYAV.model,
			add:1,
			func:zayavDevSelect
		});
		modelImageGet();
		imageSortable();
		colorSelDop(ZAYAV.color_id);

		function submit() {
			var msg,
				send = {
					op:'zayav_edit',
					zayav_id:ZAYAV.id,
					client_id:$('#client_id').val(),
					device:$('#dev_device').val(),
					vendor:$('#dev_vendor').val(),
					model:$('#dev_model').val(),
					imei: $.trim($('#imei').val()),
					serial:$.trim($('#serial').val()),
					color_id:$('#color_id').val(),
					color_dop:$('#color_dop').val(),
					equip:'',
					pre_cost:$('#pre_cost').val()
				};
			if(!$('.tr_equip').hasClass('dn')) {
				var inp = $('.equip_spisok input'),
					arr = [];
				for(var n = 0; n < inp.length; n++) {
					var eq = inp.eq(n);
					if(eq.val() == 1)
						arr.push(eq.attr('id').split('_')[1]);
				}
				send.equip = arr.join();
			}
			if(send.deivce == 0) msg = '�� ������� ����������';
			else if(send.client_id == 0) msg = '�� ������ ������';
			else if(send.pre_cost && !REGEXP_NUMERIC.test(send.pre_cost)) {
				msg = '����������� ������� ��������������� ���������';
				$('#pre_cost').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ ��������!');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					top:-47,
					left:107,
					show:1,
					remove:1
				});
		}
	})
	.on('click', '#zayav-info .remind_add', function() {
		var html = '<table class="remind_add_tab">' +
			'<tr><td class="label">������:<td>�<b>' + ZAYAV.nomer + '</b>' +
			'<tr><td class="label top">�������� �������:<td><TEXTAREA id="txt"></TEXTAREA>' +
			'<tr><td class="label">������� ���� ����������:<td><input type="hidden" id="data">' +
			'<tr><td class="label">������:<td><input type="hidden" id="private">' +
			'</table>';
		var dialog = _dialog({
				top:60,
				width:480,
				head:'���������� ������ �������',
				content:html,
				submit:submit
			}),
			txt = $('.remind_add_tab #txt'),
			day = $('.remind_add_tab #data'),
			priv = $('.remind_add_tab #private');
		txt.autosize().focus();
		day._calendar();
		priv._check();
		$('.remind_add_tab #private_check').vkHint({
			msg:'������� �������<br />������ ������ ��.',
			top:-71,
			left:-11,
			indent:'left',
			delayShow:1000
		});

		function submit() {
			var send = {
				op:'report_remind_add',
				from_zayav:1,
				client_id:0,
				zayav_id:ZAYAV.id,
				txt:txt.val(),
				day:day.val(),
				private:priv.val()
			};
			if(!send.txt) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>�� ������� ���������� �����������.</SPAN>',
					remove:1,
					indent:40,
					show:1,
					top:-48,
					left:150
				});
				txt.focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ������� ������� ���������.');
						$('#remind_spisok').html(res.html);
					} else {
						dialog.abort();
					}
				}, 'json');
			}
		}//submit()
	})
	.on('click', '#zayav-info .acc_add', function() {
		var html = '<table class="zayav_accrual_add">' +
				'<tr><td class="label">�����: <td><input type="text" id="sum" class="money" maxlength="5" /> ���.' +
				'<tr><td class="label">����������:<em>(�� �����������)</em><td><input type="text" id="prim" maxlength="100" />' +
				'<tr><td class="label">������ ������: <td><input type="hidden" id="acc_status" value="2" />' +
				'<tr><td class="label">�������� �����������:<td><input type="hidden" id="acc_remind" />' +
			'</table>' +

			'<table class="zayav_accrual_add remind">' +
				'<tr><td class="label">����������:<td><input type="text" id="reminder_txt" value="��������� � �������� � ����������.">' +
				'<tr><td class="label">����:<td><input type="hidden" id="reminder_day">' +
			'</table>';
		var dialog = _dialog({
			top:60,
			width:420,
			head:'������ �' + ZAYAV.nomer + ' - ���������� �� ����������� ������',
			content:html,
			submit:submit
		});
		$('#sum').focus();
		$('#sum,#prim,#reminder_txt').keyEnter(submit);
		$('#acc_status')._dropdown({spisok:STATUS});
		$('#acc_remind')._check();
		$('#acc_remind_check').click(function(id) {
			$('.zayav_accrual_add.remind').toggle();
		});
		$('#reminder_day')._calendar();

		function submit() {
			var msg,
				send = {
					op:'zayav_accrual_add',
					zayav_id:ZAYAV.id,
					sum:$('#sum').val(),
					prim:$('#prim').val(),
					status:$('#acc_status').val(),
					remind:$('#acc_remind').val(),
					remind_txt:$('#reminder_txt').val(),
					remind_day:$('#reminder_day').val()
				};
			if(!REGEXP_NUMERIC.test(send.sum)) { msg = '����������� ������� �����.'; $('#sum').focus(); }
			else if(send.remind == 1 && !send.remind_txt) { msg = '�� ������ ����� �����������'; $('#reminder_txt').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('���������� ������� �����������!');
						$('#money_spisok').html(res.html);
						zayavMoneyUpdate();
						if(res.status) {
							$('#status')
								.html(res.status.name)
								.css('background-color', '#' + res.status.color);
							$('#status_dtime').html(res.status.dtime);
						}
						if(res.remind)
							$('#remind-spisok').html(res.remind);
					}
				}, 'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					top:-48,
					left:123,
					indent:40,
					remove:1,
					show:1
				});
		}
	})
	.on('click', '#zayav-info .acc_del', function() {
		var send = {
			op:'zayav_accrual_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">��������...');
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				tr.find('.deleting').html('���������� �������. <a class="acc_rest" val="' + send.id + '">������������</a>');
				zayavMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '#zayav-info .acc_rest', function() {
		var send = {
				op:'zayav_accrual_rest',
				id:$(this).attr('val')
			},
			t = $(this),
			tr = t.parent().parent();
		t.remove();
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				tr.after(res.html).remove();
				zayavMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '#zayav-info .status_place', function() {
		var html =
			'<div id="zayav-status">' +
		(ZAYAV.status != 1 ?
				'<div class="st c1" val="1">' +
					'������� ����������' +
					'<div class="about">������������� ������ �� ������.</div>' +
				'</div>'
		: '') +
		(ZAYAV.status != 2 ?
				'<div class="st c2" val="2">' +
					'���������' +
					'<div class="about">' +
						'������ ��������� �������.<br />' +
						'�� �������� ��������� ������� �� ������, ��������� ����������.<br />' +
						'�������� �����������, ���� ����������.' +
					'</div>' +
				'</div>'
		: '') +
		(ZAYAV.status != 3 ?
				'<div class="st c3" val="3">' +
					'������ ��������' +
					'<div class="about">������ ������ �� �����-���� �������.</div>' +
				'</div>'
		: '') +
				'<input type="hidden" id="zs-status" />' +
				'<table id="zs-tab">' +
					'<tr><td class="label r topi">��������������� ����������:<td><input type="hidden" id="place" value="-1" />' +
					'<tr id="zs-srok" class="dn">' +
						'<td class="label r">���� ����������:' +
						'<td><input type="hidden" id="zs-day_finish" value="0000-00-00" />' +
							'<div class="day-finish-link no-save"><span>�� ������</span></div>' +
				'</table>' +

			'</div>',

			dialog = _dialog({
				top:30,
				width:420,
				head:'��������� ������� ������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		zayavPlace(ZAYAV.place_other);
		$('.st').click(function() {
			var t = $(this),
				v = t.attr('val');
			t.parent().find('.st').hide();
			t.show();
			$('#zs-status').val(v);
			$('#zs-tab').show();
			if(v == 1)
				$('#zs-srok').removeClass('dn');
		});


		function submit() {
			var send = {
				op:'zayav_status_place',
				zayav_id:ZAYAV.id,
				status:$('#zs-status').val() * 1,
				place:$('#place').val() * 1,
				place_other:$('#place_other').val(),
				day_finish:$('#zs-day_finish').val()
			};
			if(send.dev_place > 0)
				send.place_other = '';
			if(!send.status)
				err('�������� ������ ������');
			else if(send.place == -1 || send.place == 0 && send.place_other == '') {
				err('�� ������� ��������������� ����������');
				$('#place_other').focus();
			} else if(send.status == 1 && send.day_finish == '0000-00-00')
				err('�� ������ ���� ����������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ���������.');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:103,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#zayav-info .dev_place', function() {
		var html =
				'<table class="device-add-tab">' +
					'<tr><td class="label r topi">��������������� ����������:<td><input type="hidden" id="place" value="-1" />' +
				'</table>',

			dialog = _dialog({
				head:'��������� ��������������� ����������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		zayavPlace(ZAYAV.place_other);

		function submit() {
			var send = {
				op:'zayav_device_place',
				zayav_id:ZAYAV.id,
				place:$('#place').val() * 1,
				place_other:$('#place_other').val()
			};
			if(send.dev_place > 0)
				send.place_other = '';
			if(send.place == -1 || send.place == 0 && send.place_other == '') {
				err('�� ������� ��������������� ����������');
				$('#place_other').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ���������.');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class=red>' + msg + '</SPAN>',
				top:-47,
				left:80,
				indent:50,
				show:1,
				remove:1
			});
		}
	})
	.on('click', '#zayav-info .zakaz', function() {
		var t = $(this),
			send = {
				op:'zayav_zp_zakaz',
				zayav_id:ZAYAV.id,
				zp_id:t.parent().parent().attr('val')
			};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				t.html('��������!').attr('class', 'zakaz_ok');
				_msg(res.msg);
			}
		}, 'json');
	})
	.on('click', '#zayav-info .zakaz_ok', function() {
		location.href = URL + '&p=zp&menu=3';
	})
	.on('click', '#zayav-info .zpAdd', function() {
		var html = '<div class="zayav_zp_add">' +
				'<center>���������� �������� � ����������<br />' +
					'<b>' +
						DEV_ASS[ZAYAV.device] + ' ' +
						VENDOR_ASS[ZAYAV.vendor] + ' ' +
						MODEL_ASS[ZAYAV.model] +
					'</b>.'+
				'</center>' +
				'<table style="border-spacing:6px">' +
					'<tr><td class="label r">������������ ��������:<td><input type="hidden" id="name_id" />' +
					'<tr><td class="label r">������:<td><input type="text" id="version" maxlength="30" />' +
					'<tr><td class="label r">����:<td><input type="hidden" id="color_id" />' +
					'<tr><td class="label r">�/�:<td><input type="hidden" id="bu" />' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				top:40,
				width:380,
				head:'�������� ����� ��������',
				content:html,
				submit:submit
			});

		$('#name_id')._select({
			width:200,
			title0:'������������ �� �������',
			spisok:ZPNAME_SPISOK
		});
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK
		});
		$('#bu')._check();
		function submit() {
			var send = {
				op:'zayav_zp_add',
				zayav_id: ZAYAV.id,
				name_id:$('#name_id').val(),
				version:$('#version').val(),
				color_id:$('#color_id').val(),
				bu:$('#bu').val()
			};
			if(send.name_id == 0)
				dialog.bottom.vkHint({msg:'<SPAN class="red">�� ������� ������������ ��������.</SPAN>',
					top:-47,
					left:56,
					show:1,
					remove:1
				});
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						_msg('�������� �������� �����������.');
						dialog.close();
						$('#zpSpisok').html(res.html);
					}
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info .set', function() {
		var unit = $(this).parent().parent();
		var html = '<center class="zayav_zp_set">' +
			'��������� ��������<br />' + unit.find('a:first').html() + '.<br />' +
			(unit.find('.color').length > 0 ? unit.find('.color').html() + '.<br />' : '') +
			'<br />���������� �� ��������� �����' +
			'<br />����� ��������� � �������' +
			'<br />� � ������� �� ������.' +
		'</center>',
		dialog = _dialog({
			top:150,
			width:400,
			head:'��������� ��������',
			content:html,
			butSubmit:'����������',
			submit:submit
		});
		function submit() {
			var send = {
				op:'zayav_zp_set',
				zp_id:unit.attr('val'),
				zayav_id:ZAYAV.id
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					zayavMoneyUpdate();
					dialog.close();
					_msg('��������� �������� �����������.');
					unit.parent().html(res.zp_unit);
					$('.vkComment').after(res.comment).remove();
				} else
					dialog.abort();
			},'json');
		}
	})
	.on('click', '.zayav_kvit', function() {
		kvitHtml($(this).attr('val'));
	})

	.on('click', '#zayav-info .zc-edit', function() {//�������������� ������ � �����������
		var html =
				'<table id="cartridge-add-tab">' +
					'<tr><td class="label">������:' +
						'<td><input type="hidden" id="client_id" value="' + ZAYAV.client_id + '" />' +
					'<tr><td class="label"><b>���������� ����������:</b><td><input type="text" id="count" value="' + ZAYAV.cartridge_count + '" /> ��.' +
				'</table>',
			dialog = _dialog({
				width:470,
				head:'�������������� ������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#client_id').clientSel({add:1});
		$('#crt').cartridge(ZAYAV.cart_ids);
		function submit() {
			var send = {
				op:'zayav_cartridge_edit',
				zayav_id:ZAYAV.id,
				client_id:_num($('#client_id').val()),
				count:_num($('#count').val())
			};
			if(!send.client_id) dialog.err('�� ������ ������');
			else if(!send.count) dialog.err('�� ������� ���������� ����������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ ��������');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info #cart-add', zayavInfoCartridgeAdd)
	.on('click', '#zayav-info .cart-edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			cart_id = t.find('.cart_id').val(),
			filling = t.find('.filling').val(),
			restore = t.find('.restore').val(),
			chip = t.find('.chip').val(),
			cost = t.find('.cost').html(),
			prim = t.find('u').html(),
			html =
				'<table id="cart-edit-tab">' +
					'<tr><td class="label">��������:<td><input type="hidden" id="cart_id" value="' + cart_id + '" />' +
					'<tr><td class="label topi">��������:' +
						'<td><input type="hidden" id="filling" value="' + filling + '" />' +
							'<input type="hidden" id="restore" value="' + restore + '" />' +
							'<input type="hidden" id="chip" value="' + chip + '" />' +
					'<tr><td class="label">��������� �����:<td><input type="text" class="money" id="cost" value="' + cost + '" /> ���.' +
					'<tr><td class="label">����������:<td><input type="text" id="prim" value="' + prim + '" />' +
				'</table>',
			dialog = _dialog({
				width:470,
				top:30,
				head:'�������� �� ���������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		$('#cart_id')._select({
			write:1,
			spisok:CARTRIDGE_SPISOK,
			func:costSet
		});
		$('#filling')._check({name:'��������',func:costSet});
		$('#restore')._check({name:'��������������',func:costSet});
		$('#chip')._check({name:'������ ����',func:costSet});
		function costSet() {
			var c = 0,
				cart_id = _num($('#cart_id').val());
			if($('#filling').val() == 1)
				c = CARTRIDGE_FILLING[cart_id];
			if($('#restore').val() == 1)
				c += CARTRIDGE_RESTORE[cart_id];
			if($('#chip').val() == 1)
				c += CARTRIDGE_CHIP[cart_id];
			$('#cost').val(c);
		}
		function submit() {
			var send = {
				op:'zayav_info_cartridge_edit',
				id:id,
				cart_id:_num($('#cart_id').val()),
				filling:$('#filling').val(),
				restore:$('#restore').val(),
				chip:$('#chip').val(),
				cost:$('#cost').val(),
				prim:$('#prim').val()
			};
			if(!send.cart_id) dialog.err('�� ������ ��������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#cart-tab').html(res.html);
						_msg('��������.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-info .cart-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			dialog = _dialog({
				head:'�������� ���������',
				content:'<center>����������� �������� ���������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		function submit() {
			var send = {
				op:'zayav_info_cartridge_del',
				id:id
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					$('#cart-tab').html(res.html);
					_msg('�������.');
				} else
					dialog.abort();
				}, 'json');
		}
	})

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
		$('#bu')._check(0);
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
			'<table class="zp_add_dialog">' +
				'<tr><td class="label">������������ ��������:<td><input type="hidden" id="name_id" value="' + ZP.name_id + '">' +
				'<tr><td class="label topi">����������:<td id="add_dev">' +
				'<tr><td><td>' + ZP.images +
				'<tr><td class="label">������:<td><input type="text" id="version" value="' + ZP.version + '">' +
				'<tr><td class="label">�/�:<td><input type="hidden" id="add_bu" value="' + ZP.bu + '">' +
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
		$('#name_id')._select({
			width:200,
			title0:'������������ �� �������',
			spisok:ZPNAME_SPISOK
		});
		$('#add_dev').device({
			width:200,
			add:1,
			device_id:ZP.device,
			vendor_id:ZP.vendor,
			model_id:ZP.model
		});
		imageSortable();
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK
		});
		$('#add_bu')._check();
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
					name_id:$('#name_id').val(),
					device_id:$('#add_dev_device').val(),
					vendor_id:$('#add_dev_vendor').val(),
					model_id:$('#add_dev_model').val(),
					version:$('#version').val(),
					bu:$('#add_bu').val(),
					color_id:$('#color_id').val(),
					price_id:$('#price_id').val()
				};
			if(send.name_id == 0) msg = '�� ������� ������������ ��������.';
			else if(send.device_id == 0) msg = '�� ������� ����������';
			else if(send.vendor_id == 0) msg = '�� ������ �������������';
			else if(send.model_id == 0) msg = '�� ������� ������';
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					dialog.abort();
					if(res.success) {
						dialog.close();
						_msg('�������������� ������ �����������.');
						window.location.reload();
					}
				},'json');
			}

			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					left:110,
					top:-47,
					indent:50,
					show:1,
					remove:1
				});
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
					(sp.bu == 1 ? '<span class="bu">�/y</span>' : '') +
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
					bu:sp.bu,
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

	.on('click', '#income_next', function() {
		var next = $(this),
			send = {
				op:'income_next',
				page:next.attr('val'),
				day:$('.selected').val(),
				del:$('#del').val()
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.income-add', function() {
		var html = '<table id="income-add-tab">' +
			'<input type="hidden" id="zayav_id" value="' + (window.ZAYAV ? ZAYAV.id : 0) + '" />' +
			(window.ZAYAV ? '<tr><td class="label">������:<td><b>�' + ZAYAV.nomer + '</b>' : '') +
			'<tr><td class="label">����:<td><input type="hidden" id="invoice_id" value="' + (INVOICE_SPISOK.length ? INVOICE_SPISOK[0].uid : 0) + '" />' +
			'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="11" /> ���.' +
			'<tr><td class="label">��������:<td><input type="text" id="prim" maxlength="100" />' +
			(window.ZAYAV ? '<tr><td class="label topi">���������������<br />����������:<td><input type="hidden" id="place" value="-1" />' : '') +
			'</table>';
		var dialog = _dialog({
				width:380,
				head:'�������� �������',
				content:html,
				submit:submit
			});
		$('#invoice_id')._select({
			width:218,
			title0:'�� ������',
			spisok:INVOICE_SPISOK,
			func:function() {
				$('#sum').focus();
			}
		});
		$('#sum').focus();
		$('#sum,#prim').keyEnter(submit);
		if(window.ZAYAV)
			zayavPlace();

		function submit() {
			var send = {
				op:'income_add',
				zayav_id:$('#zayav_id').val(),
				invoice_id:$('#invoice_id').val(),
				sum:$('#sum').val(),
				prim:$('#prim').val(),
				place:window.ZAYAV ? $('#place').val() : 0,
				place_other:window.ZAYAV ? $('#place_other').val() : ''
			};
			if(send.invoice_id == 0) err('�� ������ ����');
			else if(!REGEXP_CENA.test(send.sum)) { err('����������� ������� �����'); $('#sum').focus(); }
			else if(!window.ZAYAV && !send.prim) { err('�� ������� ��������'); $('#prim').focus(); }
			else if(window.ZAYAV && send.place == -1) err('�� ������� ��������������� ����������');
			else {
				dialog.process();
				$.post(AJAX_WS, send, function (res) {
					if(res.success) {
						dialog.close();
						_msg('����� ����� �����.');
						if(window.ZAYAV) {
							$('#money_spisok').html(res.html);
							if(res.comment)
								$('.vkComment').after(res.comment).remove();
							zayavMoneyUpdate();
						} else
							incomeSpisok();
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:103
			});
		}
	})
	.on('click', '.income-del', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		if(t.hasClass('deleting'))
			return;
		t.addClass('deleting');
		var send = {
			op:'income_del',
			id:t.attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			t.removeClass('deleting');
			if(res.success) {
				t.addClass('deleted');
				if(window.ZAYAV)
					zayavMoneyUpdate();
			}
		}, 'json');
	})
	.on('click', '.income-rest', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var send = {
			op:'income_rest',
			id:t.attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success) {
				t.removeClass('deleted');
				if(window.ZAYAV)
					zayavMoneyUpdate();
			}
		}, 'json');
	})

	.on('click', '.expense #monthList div', expenseSpisok)
	.on('click', '.expense ._next', function() {
		var next = $(this),
			send = expenseFilter();
		send.page = next.attr('val');
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})
	.on('click', '.expense .img_del', function() {
		var send = {
			op:'expense_del',
			id:$(this).attr('val')
		};
		var tr = $(this).parent().parent();
		tr.html('<td colspan="4" class="deleting">��������...</td>');
		$.post(AJAX_WS, send, function (res) {
			if(res.success) {
				_msg('�������� �����������.');
				tr.remove();
			}
		}, 'json');
	})

	.on('click', '.invoice_set', function() {
		var t = $(this),
			html =
				'<table class="_dialog-tab">' +
					'<tr><td class="label">�����:<td><input type="text" class="money" id="sum" maxlength="11" /> ���.' +
				'</table>';
		var dialog = _dialog({
				width:320,
				head:'��������� ������� ����� �����',
				content:html,
				butSubmit:'����������',
				submit:submit
			});

		$('#sum').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'invoice_set',
				invoice_id:t.attr('val'),
				sum:$('#sum').val()
			};
			if(!REGEXP_CENA.test(send.sum)) {
				err('����������� ������� �����');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						$('#invoice-spisok').html(res.html);
						dialog.close();
						_msg('��������� ����� �����������');
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-48,
				left:72
			});
		}
	})
	.on('click', '.invoice_reset', function() {
		var t = $(this),
			html = '����� �� ����� ����� ��������.';
		var dialog = _dialog({
				width:320,
				head:'����� ����� �����',
				content:html,
				butSubmit:'���������',
				submit:submit
			});

		function submit() {
			var send = {
				op:'invoice_reset',
				invoice_id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					$('#invoice-spisok').html(res.html);
					dialog.close();
					_msg('����� ��������');
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '#report.invoice .img_note', function() {
		var dialog = _dialog({
			top:20,
			width:570,
			head:'������� �������� �� ������',
			load:1,
			butSubmit:'',
			butCancel:'�������'
		});
		var send = {
			op:'invoice_history',
			invoice_id:$(this).attr('val')
		};
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	})
	.on('click', '.invoice-history ._next', function() {
		var next = $(this),
			send = {
				op:'invoice_history',
				page:next.attr('val'),
				invoice_id:$('#invoice_history_id').val()
			};
		if(next.hasClass('busy'))
			return;
		next.addClass('busy');
		$.post(AJAX_WS, send, function(res) {
			if(res.success)
				next.after(res.html).remove();
			else
				next.removeClass('busy');
		}, 'json');
	})

	.on('click', '.salary .rate-set', function() {
		var html =
				'<div class="_info">' +
					'����� ��������� ������ ���������� ��������� ����� ����� ������������� ����������� ' +
					'�� ��� ������ � ����������� ���� ��������� ��������������. ' +
				'</div>' +
				'<table class="salary-tab set">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="11" value="' + (RATE.sum ? RATE.sum : '') + '" /> ���.' +
					'<tr><td class="label">������:<td><input type="hidden" id="period" value="' + RATE.period + '" />' +
					'<tr class="tr-day' + (RATE.period == 3 ? ' dn' : '') + '">' +
						'<td class="label">���� ����������:' +
						'<td>' +
							'<div class="div-day' + (RATE.period != 1 ? ' dn' : '') + '"><input type="text" id="day" maxlength="2" value="' + RATE.day + '" /></div>' +
							'<div class="div-week' + (RATE.period != 2 ? ' dn' : '') + '"><input type="hidden" id="day_week" value="' + RATE.day + '" /></div>' +
				'</table>',
			dialog = _dialog({
				top:30,
				width:320,
				head:'��������� ������ �/� ��� ����������',
				content:html,
				butSubmit:'����������',
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#day').keyEnter(submit);
		$('#period')._select({
			width:70,
			spisok:SALARY_PERIOD,
			func:function(id) {
				$('#day_week')._select(1);
				$('.tr-day')[(id == 3 ? 'add' : 'remove') + 'Class']('dn');
				$('.div-day')[(id != 1 ? 'add' : 'remove') + 'Class']('dn');
				$('.div-week')[(id != 2 ? 'add' : 'remove') + 'Class']('dn');
			}
		});
		$('#day_week')._select({
			spisok:[
				{uid:1,title:'�����������'},
				{uid:2,title:'�������'},
				{uid:3,title:'�����'},
				{uid:4,title:'�������'},
				{uid:5,title:'�������'},
				{uid:6,title:'�������'},
				{uid:7,title:'�����������'}
			]
		});

		function submit() {
			var send = {
				op:'salary_rate_set',
				worker_id:WORKER_ID,
				sum:_cena($('#sum').val()),
				period:$('#period').val() * 1,
				day:$('#day').val() * 1
			};
			if(send.period == 2)
				send.day = $('#day_week').val() * 1;
			if(!send.sum) { err('����������� ������� �����.'); $('#sum').focus(); }
			else if(send.period == 1 && (!REGEXP_NUMERIC.test(send.day) || !send.day || send.day > 28)) { err('����������� ������ ����.'); $('#day').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						RATE.sum = send.sum;
						RATE.period = send.period;
						RATE.day = send.day;
						dialog.close();
						_msg('��������� ������ �����������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:74
			});
		}
	})
	.on('click', '.salary .bonus', function() {// �������� ������ �� ��������
		var n,
			year = [],
			week = [],
			html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�������:<td>' + PROCENT +
					'<tr><td class="label">���:<td><input type="hidden" id="b-year" value="2015" />' +
					'<tr><td class="label">������:<td><input type="hidden" id="week" />' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" disabled /> ���.' +
				'</table>' +
				'<div id="bonus-spisok"></div>',
			dialog = _dialog({
				top:20,
				width:450,
				head:'������������ ������ �� ��������',
				content:html,
				submit:submit
			});

		for(n = 2014; n <= 2015; n++)
			year.push({uid:n,title:n});
		$('#b-year')._select({
			width:100,
			spisok:year,
			func:bonus_spisok
		});

		for(n = 1; n <= 53; n++)
			week.push({uid:n,title:n});
		$('#week')._select({
			title0:'������',
			width:100,
			spisok:week,
			func:bonus_spisok
		});

		function bonus_spisok() {
			var send = {
				op:'salary_bonus_spisok',
				worker_id:WORKER_ID,
				year:$('#b-year').val(),
				week:$('#week').val() * 1
			};
			if(!send.week) {
				$('#bonus-spisok').html('');
				$('#sum').val('');
				return;
			}
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					$('#bonus-spisok').html(res.spisok);
					bonus_sum();
					$('.i-expense').keyup(function() {
						var t = $(this),
							expense = t.val(),
							money = t.parent().parent().find('b').html(),
							bns = t.parent().parent().find('.bns');
						if(!REGEXP_NUMERIC.test(expense))
							return;
						bns.html(Math.round((money - expense) * PROCENT / 100));
						bonus_sum();
					});
				}
			}, 'json');
		}
		function bonus_sum() {
			var bns = $('.bns'),
				sum = 0,
				send = [];
			for(n = 0; n < bns.length; n++) {
				var eq = bns.eq(n);
				sum += eq.html() * 1;
				send.push(eq.attr('val') + ':' + eq.parent().find('.i-expense').val() + ':' + eq.html());
			}
			$('#sum').val(sum);
			return send.join();
		}
		function submit() {
			var send = {
				op:'salary_bonus',
				worker_id:WORKER_ID,
				year:$('#b-year').val(),
				week:$('#week').val(),
				bonus:bonus_sum()
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('����� ��������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .bonus-show', function() {
		var dialog = _dialog({
			top:20,
			width:500,
			head:'�������� ������ �� ��������',
			load:1,
			butSubmit:'',
			butCancel:'�������'
		});

		var send = {
			op:'salary_bonus_show',
			expense_id:$(this).attr('val')
		};
		$.post(AJAX_WS, send, function (res) {
			if(res.success)
				dialog.content.html(res.html);
			else
				dialog.loadError();
		}, 'json');
	})
	.on('click', '.salary .up', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="50">' +
					'<tr><td class="label">�����:' +
						'<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
							'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
					'</table>',
			dialog = _dialog({
				head:'�������� ���������� ��� ����������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});
		function submit() {
			var send = {
				op:'salary_up',
				worker_id:WORKER_ID,
				sum:_cena($('#sum').val()),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!send.sum) {
				err('����������� ������� �����.');
				$('#sum').focus();
			} else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('���������� �����������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .deduct', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">�����:' +
						'<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
							'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
					'</table>',
			dialog = _dialog({
				head:'�������� ������ �� ��������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});
		function submit() {
			var send = {
				op:'salary_deduct',
				worker:WORKER_ID,
				sum:$('#sum').val(),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!REGEXP_NUMERIC.test(send.sum)) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('����� ���������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .ze_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'��������',
				content:'<center>����������� �������� ������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		while(t[0].tagName != 'TR')
			t = t.parent();
		function submit() {
			var send = {
				op:'salary_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .zp_add', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�� �����:' +
						'<td><input type="hidden" id="invoice_id">' +
							'<a href="' + URL + '&p=setup&d=invoice" class="img_edit' + _tooltip('��������� ������', -56) + '</a>' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
					'<tr><td class="label">��������:<td><input type="text" id="about" maxlength="100">' +
					'<tr><td class="label">�����:' +
						'<td><input type="hidden" id="tabmon" value="' + MON + '" /> ' +
							'<input type="hidden" id="tabyear" value="' + YEAR + '" />' +
				'</table>',
			dialog = _dialog({
				head:'������ �������� ����������',
				content:html,
				submit:submit
			});

		$('#sum').focus();
		$('#invoice_id')._select({
			title0:'�� ������',
			spisok:INVOICE_SPISOK,
			func:function() {
				$('#sum').focus();
			}
		});
		$('#sum,#about').keyEnter(submit);
		$('#tabmon')._select({
			width:80,
			spisok:MON_SPISOK
		});
		$('#tabyear')._select({
			width:60,
			spisok:YEAR_SPISOK
		});

		function submit() {
			var send = {
				op:'salary_zp_add',
				worker_id:WORKER_ID,
				invoice_id:$('#invoice_id').val() * 1,
				sum:_cena($('#sum').val()),
				about:$('#about').val(),
				mon:$('#tabmon').val(),
				year:$('#tabyear').val()
			};
			if(!send.invoice_id) err('������� � ������ ����� ������������ ������.');
			else if(!send.sum) { err('����������� ������� �����.'); $('#sum').focus(); }
			else {
				dialog.process();
				$.post(AJAX_WS, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('������ �������� �����������.');
						salarySpisok();
					} else
						dialog.abort();
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<SPAN class="red">' + msg + '</SPAN>',
				remove:1,
				indent:40,
				show:1,
				top:-47,
				left:93
			});
		}
	})
	.on('click', '.salary .zp_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'�������� �/�',
				content:'<center>����������� �������� ������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		function submit() {
			var send = {
				op:'expense_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .deduct_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:110,
				width:250,
				head:'��������',
				content:'<center>����������� �������� ������.</center>',
				butSubmit:'�������',
				submit:submit
			});
		while(t[0].tagName != 'TR')
			t = t.parent();
		function submit() {
			var send = {
				op:'salary_deduct_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_WS, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg('�������.');
					salarySpisok();
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('click', '.salary .start-set', function() {
		var html =
				'<table class="salary-tab">' +
					'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="8"> ���.' +
				'</table>',
			dialog = _dialog({
				head:'��������� ������� �� �������� ����������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});

		$('#sum').focus().keyEnter(submit);

		function submit() {
			var send = {
				op:'salary_start_set',
				worker_id:WORKER_ID,
				sum:_cena($('#sum').val())
			};
			dialog.process();
			$.post(AJAX_WS, send, function (res) {
				if(res.success) {
					dialog.close();
					_msg('��������� �����������.');
					$('#spisok').html(res.html);
				} else
					dialog.abort();
			}, 'json');
		}
	})
	.on('mouseenter', '.salary .show', function() {
		$(this).removeClass('show');
	})
	.on('click', '.go-report-salary', function() {
		var v = $(this).attr('val').split(':');
		location.href = URL + '&p=report&d=salary&id=' + v[0] + '&year=' + v[1] + '&mon=' + v[2] + '&acc_id=' + v[3];
	})

	.ready(function() {
		if($('#client').length) {
			$('#find')._search({
				width:602,
				focus:1,
				enter:1,
				txt:'������� ������� ������ �������',
				func:clientSpisok
			}).inp(C.find);
			$('#buttonCreate').vkHint({
				msg:'<B>�������� ������ ������� � ����.</B><br /><br />' +
					'����� �������� �� ��������� �� �������� � ����������� � ������� ��� ���������� ��������.<br /><br />' +
					'�������� ����� ����� ��������� ��� <A href="' + URL + '&p=zayav&d=add&back=client">�������� ����� ������</A>.',
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
				msg:'<b>������ ���������.</b><br /><br />' +
					'��������� �������, � ������� ������ ����� 0. ����� � ���������� ������������ ����� ����� �����.',
				ugol:'right',
				width:150,
				top:-6,
				left:-185,
				indent:20,
				delayShow:1000
			});
		}
		if($('#clientInfo').length) {
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

		if($('#zayav').length) {
			$('#find')
				.vkHint({
					msg:'����� ������������ ��<br />���������� � ��������<br />������, imei � ��������<br />������.',
					ugol:'right',
					top:-9,
					left:-178,
					delayShow:800
				})
				._search({
					width:153,
					focus:1,
					txt:'������� �����...',
					enter:1,
					func:zayavSpisok
				})
				.inp(Z.find);
			$('#desc')._check(zayavSpisok);
			$('#status').rightLink(zayavSpisok);
			$('#diff')._check(zayavSpisok);
			$('#dev').device({
				width:155,
				type_no:1,
				device_id:Z.device_id,
				vendor_id:Z.vendor_id,
				model_id:Z.model_id,
				device_ids:Z.device_ids,
				vendor_ids:Z.vendor_ids,
				model_ids:Z.model_ids,
				func:zayavSpisok
			});
			// ���������� ����������
			for(n = 0; n < Z.place_other.length; n++) {
				var sp = Z.place_other[n];
				DEVPLACE_SPISOK.push({uid:encodeURI(sp), title:sp});
			}
			DEVPLACE_SPISOK.push({uid:-1, title:'�� ��������', content:'<B>�� ��������</B>'});
			$('#device_place')._select({
				width:155,
				title0:'����� ���������������',
				spisok:DEVPLACE_SPISOK,
				func:zayavSpisok
			});
			//������������� ������������� ������
			if(Z.cookie_id) {
				VK.callMethod('scrollWindow', _cookie('zback_scroll'));
				$('#u' + Z.cookie_id).css('opacity', 0.1).delay(400).animate({opacity:1}, 700);
			}
			zayavFilter();
		}
		if($('#zayavAdd').length) {
			$('#client_id').clientSel({add:1});
			$('#dev').device({
				width:190,
				add:1,
				device_ids:WS_DEVS,
				func:zayavDevSelect
			});
			zayavPlace();
			colorSelDop(0);
			$(document).on('click', '#fault', function() {
				var i = $(this).find('input'),
					arr = [];
				for(var n = 0; n < i.length; n++)
					if(i.eq(n).val() == 1) {
						var uid = i.eq(n).attr('id').split('_')[1];
						arr.push(FAULT_ASS[uid]);
					}
				$('#comm').val(arr.join(', '));
			});
			$('#comm').autosize();
			$('.vkCancel').click(function() {
				location.href = URL + '&p=' + $(this).attr('val');
			});
			$('.vkButton').click(function () {
				if($(this).hasClass('busy'))
					return;
				var send = {
					op:'zayav_add',
					client_id:$('#client_id').val(),
					device:$('#dev_device').val(),
					vendor:$('#dev_vendor').val(),
					model:$('#dev_model').val(),
					equip:'',
					place:$('#place').val(),
					place_other:$('#place_other').val(),
					imei:$('#imei').val(),
					serial:$('#serial').val(),
					color:$('#color_id').val(),
					color_dop:$('#color_dop').val(),
					comm:$('#comm').val(),
					pre_cost:$('#pre_cost').val(),
					day_finish:$('#day_finish').val()
				};
				if(!$('.tr_equip').hasClass('dn')) {
					var inp = $('.equip_spisok input'),
						arr = [];
					for(var n = 0; n < inp.length; n++) {
						var eq = inp.eq(n);
						if(eq.val() == 1)
							arr.push(eq.attr('id').split('_')[1]);
					}
					send.equip = arr.join();
				}
				var msg = '';
				if(send.client_id == 0) msg = '�� ������ ������';
				else if(send.device == 0) msg = '�� ������� ����������';
				else if(send.place == '-1' || send.place == 0 && !send.place_other) msg = '�� ������� ��������������� ����������';
				else if(send.pre_cost && (!REGEXP_NUMERIC.test(send.pre_cost) || send.pre_cost == 0)) {
					msg = '����������� ������� ��������������� ���������';
					$('#pre_cost').focus();
				} else if(send.day_finish == '0000-00-00') msg = '�� ������ ���� ���������� �������';
				else {
					if(send.place > 0) send.place_other = '';
					$(this).addClass('busy');
					$.post(AJAX_WS, send, function(res) {
						location.href = URL + '&p=zayav&d=info&id=' + res.id;
					}, 'json');
				}

				if(msg)
					$(this).vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-48,
						left:201,
						indent:30,
						remove:1,
						show:1
					});
			});
		}
		if($('#zayav-info').length) {
			$('.hist').click(function() {
				$('#dopLinks .sel').removeClass('sel');
				$(this).addClass('sel');
				$('.itab').addClass('h');
			});
			$('.info').click(function() {
				$('#dopLinks .sel').removeClass('sel');
				$(this).addClass('sel');
				$('.itab').removeClass('h');
			});
			$('.delete')
				.click(function() {
					var dialog = _dialog({
						top:110,
						width:250,
						head:'�������� ������',
						content:'<center>����������� �������� ������.</center>',
						butSubmit:'�������',
						submit:function() {
							var send = {
								op:'zayav_delete',
								zayav_id:ZAYAV.id
							};
							dialog.process();
							$.post(AJAX_WS, send, function(res) {
								if(res.success)
									location.href = URL + '&p=client&d=info&id=' + res.client_id;
								else
									dialog.abort();
							}, 'json');
						}
					});
				})
				.vkHint({
					msg:'������ ����� ������� ��� ���������� �������� � ����������. ����� ��������� ��� ������ � ���� ������.',
					width:150,
					ugol:'top',
					top:39,
					left:457,
					indent:'right'
				});
			$('.img_print').click(function() {
				var html = '<table class="zayav-print">' +
						'<tr><td class="label">���� �����:<td>' + PRINT.dtime +
						'<tr><td class="label top">����������:<td>' + PRINT.device +
		                '<tr><td class="label">����:<td>' + (PRINT.color ? PRINT.color : '<i>�� ������</i>') +
						'<tr><td class="label">IMEI:<td>' + (PRINT.imei ? PRINT.imei : '<i>�� ������</i>') +
						'<tr><td class="label">�������� �����:<td>' + (PRINT.serial ? PRINT.serial : '<i>�� ������</i>') +
						'<tr><td class="label">������������:<td>' + (PRINT.equip ? PRINT.equip : '<i>�� �������</i>') +
						'<tr><td class="label">��������:<td><b>' + PRINT.client + '</b>' +
						'<tr><td class="label">�������:<td>' + (PRINT.telefon ? PRINT.telefon : '<i>�� ������</i>') +
						'<tr><td class="label top">�������������:<td><textarea id="defect">' + PRINT.defect + '</textarea>' +
						'<tr><td colspan="2"><a id="preview"><span>��������������� �������� ���������</span></a>' +
					'</table>',
					dialog = _dialog({
						width:380,
						top:30,
						head:'������ �' + ZAYAV.nomer + ' - ������������ ���������',
						content:html,
						butSubmit:'��������� ���������',
						submit:submit
					});
				$('#defect').focus().autosize();
				$('#preview').click(function() {
					var t = $(this),
						send = {
							op:'zayav_kvit',
							zayav_id:ZAYAV.id,
							defect:$.trim($('#defect').val())
						};
					if(t.hasClass('_busy'))
						return;
					if(!send.defect) err(1);
					else {
						t.addClass('_busy');
						$.post(AJAX_WS, send, function(res) {
							t.removeClass('_busy');
							if(res.success)
								kvitHtml(res.id);
						}, 'json');
					}
				});
				function submit() {
					var send = {
						op:'zayav_kvit',
						zayav_id:ZAYAV.id,
						defect:$.trim($('#defect').val()),
						active:1
					};
					if(!send.defect) err();
					else {
						dialog.process();
						$.post(AJAX_WS, send, function(res) {
							if(res.success) {
								dialog.close();
								_msg('��������� ���������');
								$('#kvit_spisok').html(res.html);
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(prev) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">�� ������� �������������</SPAN>',
						top:prev ? -112 : -47,
						left:prev ? 127 : 97,
						indent:50,
						show:1,
						remove:1
					});
				}
			});
			$('#ze-edit').click(function() {
				var html =
						'<table class="ze-edit-tab">' +
							'<tr><td class="label">������: <td><b>�' + ZAYAV.nomer + '</b>' +
							'<tr><td class="label">�������:<td>' +
							'<tr><td colspan="2" id="zes">' +
						'</table>',
					dialog = _dialog({
						top:30,
						width:510,
						head:'��������� �������� ������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#zes').zayavExpense(ZAYAV.expense);
				function submit() {
					var send = {
						op:'zayav_expense_edit',
						zayav_id:ZAYAV.id,
						expense:$('#zes').zayavExpense('get')
					};
					if(send.expense == 'sum_error') err('����������� ������� �����');
					else {
						dialog.process();
						$.post(AJAX_WS, send, function(res) {
							if(res.success) {
								zayavMoneyUpdate();
								dialog.close();
								_msg('���������.');
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						top:-47,
						left:167,
						indent:40,
						show:1,
						remove:1
					});
				}
			});
		}

		if($('#zayav-cartridge').length) {
			$('#desc')._check(cartridgeSpisok);
			$('#status').rightLink(cartridgeSpisok);
		}

		if($('#zp').length) {
			$('.vkButton').click(function() {
				var html =
						'<table class="zp_add_dialog">' +
							'<tr><td class="label">������������ ��������:<td><input type="hidden" id="name_id">' +
							'<tr><td class="label top">����������:<td id="add_dev">' +
							'<tr><td class="label">������:<td><input type="text" id="version">' +
							'<tr><td class="label">�/�:<td><input type="hidden" id="add_bu">' +
							'<tr><td class="label">����:<td><input type="hidden" id="color_id">' +
						'</table>',
					dialog = _dialog({
						top:70,
						width:380,
						head:'�������� ����� �������� � �������',
						content:html,
						submit:submit
					});
				$('#name_id')._select({
					width:200,
					title0:'������������ �� �������',
					spisok:ZPNAME_SPISOK
				});
				$('#color_id')._select({
					width:130,
					title0:'���� �� ������',
					spisok:COLOR_SPISOK
				});
				$('#add_bu')._check();
				$('#add_dev').device({width:200,add:1});

				function submit() {
					var send = {
						op:'zp_add',
						name_id:$('#name_id').val(),
						device_id:$('#add_dev_device').val(),
						vendor_id:$('#add_dev_vendor').val(),
						model_id:$('#add_dev_model').val(),
						version:$('#version').val(),
						bu:$('#add_bu').val(),
						color_id:$('#color_id').val()
					};
					if(send.name_id == 0) err('�� ������� ������������ ��������');
					else if(send.device_id == 0) err('�� ������� ����������');
					else if(send.vendor_id == 0) err('�� ������ �������������');
					else if(send.model_id == 0) err('�� ������� ������');
					else {
						dialog.process();
						$.post(AJAX_WS, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								$('#zp_name')._select(send.name_id);
								$('#dev').device({
									width:220,
									type_no:1,
									device_ids:WS_DEVS,
									device_id:send.device_id,
									vendor_id:send.vendor_id,
									model_id:send.model_id,
									func:zpSpisok
								});
								zpSpisok();
							}
						},'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						left:110,
						top:-47,
						indent:50,
						show:1,
						remove:1
					});
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
			$('#zp_name')._select({
				width:170,
				title0:'������������ ��������',
				spisok:ZPNAME_SPISOK,
				func:zpSpisok
			});
			$('#dev').device({
				width:220,
				type_no:1,
				device_ids:WS_DEVS,
				device_id:ZP.device,
				vendor_id:ZP.vendor,
				model_id:ZP.model,
				func:zpSpisok
			});
			$('#bu')._check(zpSpisok);
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

		if($('#report.history').length) {
			$('#viewer_id_add')._select({
				width:140,
				title0:'�� ������',
				spisok:WORKERS,
				func:_history
			});
			$('#action')._select({
				width:140,
				title0:'�� �������',
				spisok:[
					{uid:1, title:'�������'},
					{uid:2, title:'������'},
					{uid:3, title:'��������'},
					{uid:4, title:'�������'}
				],
				func:_history
			});
		}
		if($('#report.income').length) {
			window._calendarFilter = incomeSpisok;
			$('#del')._check(incomeSpisok);
		}
		if($('#report.expense').length) {
			$('.add').click(function() {
				var html =
					'<table id="expense-add-tab">' +
						'<tr><td class="label">���������:<td><input type="hidden" id="expense_id" />' +
							'<a href="' + URL + '&p=setup&d=expense" class="img_edit' + _tooltip('��������� ��������� ��������', -95) + '</a>' +
						'<tr class="tr-work dn"><td class="label">���������:<td><input type="hidden" id="worker_id" />' +
						'<tr class="tr-work dn"><td class="label">�����:' +
							'<td><input type="hidden" id="tabmon" value="' + ((new Date).getMonth() + 1) + '" /> ' +
								'<input type="hidden" id="tabyear" value="' + (new Date).getFullYear() + '" />' +
						'<tr><td class="label">��������:<td><input type="text" id="prim" maxlength="100">' +
						'<tr><td class="label">�� �����:<td><input type="hidden" id="invoice_id" value="' + (INVOICE_SPISOK.length ? INVOICE_SPISOK[0].uid : 0) + '" />' +
						'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" maxlength="11" /> ���.' +
					'</table>',
					dialog = _dialog({
						width:380,
						head:'�������� �������',
						content:html,
						submit:submit
					});

				$('#expense_id')._select({
					width:200,
					title0:'�� �������',
					spisok:EXPENSE_SPISOK,
					func:function(id) {
						$('#worker_id')._select(0);
						$('.tr-work')[(EXPENSE_WORKER[id] ? 'remove' : 'add') + 'Class']('dn');
					}
				});
				$('#worker_id')._select({
					width:200,
					title0:'�� ������',
					spisok:WORKERS
				});
				$('#prim').focus();
				$('#invoice_id')._select({
					width:200,
					title0:'�� ������',
					spisok:INVOICE_SPISOK,
					func:function() {
						$('#sum').focus();
					}
				});
				$('#tabmon')._select({
					width:80,
					spisok:MON_SPISOK
				});
				$('#tabyear')._select({
					width:60,
					spisok:YEAR_SPISOK
				});

				function submit() {
					var send = {
						op:'expense_add',
						expense_id:$('#expense_id').val(),
						worker_id:$('#worker_id').val(),
						prim:$('#prim').val(),
						invoice_id:$('#invoice_id').val(),
						sum:_cena($('#sum').val()),
						mon:$('#tabmon').val(),
						year:$('#tabyear').val()
					};
					if(!send.prim && send.expense_id == 0) { err('�������� ��������� ��� ������� ��������.'); $('#prim').focus(); }
					else if(send.invoice_id == 0) err('������� � ������ ����� ������������ ������.');
					else if(!send.sum) { err('����������� ������� �����.'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_WS, send, function (res) {
							if(res.success) {
								dialog.close();
								_msg('����� ������ �����.');
								expenseSpisok();
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						remove:1,
						indent:40,
						show:1,
						top:-47,
						left:103
					});
				}
			});
			$('#category')._select({
				width:140,
				title0:'����� ���������',
				spisok:EXPENSE_SPISOK,
				func:expenseSpisok
			});
			$('#worker')._select({
				width:140,
				title0:'��� ����������',
				spisok:WORKERS,
				func:expenseSpisok
			});
			$('#year').years({
				func:expenseSpisok,
				center:function() {
					var inp = $('#monthList input'),
						all = 0;
					for(var n = 1; n <= 12; n++)
						if(inp.eq(n - 1).val() == 0) {
							all = 1;
							break;
						}
					for(n = 1; n <= 12; n++)
						$('#c' + n)._check(all);
					expenseSpisok();
				}
			});		}
		if($('#report.invoice').length) {
			$('.transfer').click(function() {
				var t = $(this),
					from = INVOICE_SPISOK[0] ? INVOICE_SPISOK[0].uid : 0,
					to = INVOICE_SPISOK[1] ? INVOICE_SPISOK[1].uid : 0,
					html = '<table class="_dialog-tab">' +
							'<tr><td class="label">�� �����:<td><input type="hidden" id="from" value="' + from + '" />' +
							'<tr><td class="label">�� ����:<td><input type="hidden" id="to" value="' + to + '" />' +
							'<tr><td class="label">�����:<td><input type="text" id="sum" class="money" /> ���. ' +
							'<tr><td class="label">�����������:<td><input type="text" id="about" />' +
						'</table>',
					dialog = _dialog({
						width:350,
						head:'������� ����� �������',
						content:html,
						butSubmit:'���������',
						submit:submit
					});
				$('#from')._select({
					width:218,
					title0:'�� ������',
					spisok:INVOICE_SPISOK
				});
				$('#to')._select({
					width:218,
					title0:'�� ������',
					spisok:INVOICE_SPISOK
				});
				$('#sum,#about').keyEnter(submit);
				function submit() {
					var send = {
						op:'invoice_transfer',
						from:$('#from').val() * 1,
						to:$('#to').val() * 1,
						sum:$('#sum').val(),
						about:$('#about').val()
					};
					if(!send.from) err('�������� ����-�����������');
					else if(!send.to) err('�������� ����-����������');
					else if(send.from == send.to) err('�������� ������ ����');
					else if(!REGEXP_CENA.test(send.sum) || send.sum == 0) { err('����������� ������� �����'); $('#sum').focus(); }
					else {
						dialog.process();
						$.post(AJAX_WS, send, function(res) {
							if(res.success) {
								$('#invoice-spisok').html(res.i);
								$('.transfer-spisok').html(res.t);
								dialog.close();
								_msg('������� ���������.');
							} else
								dialog.abort();
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<span class="red">' + msg + '</span>',
						top:-47,
						left:92,
						indent:50,
						show:1,
						remove:1
					});
				}
			});
		}
		if($('#report.salary').length) {
			if($('#monthList').length) {
				$('#year').years({func:salarySpisok});
				$('#salmon')._radio({func:salarySpisok});
			}
		}
	});
