var modelImageGet = function() {
		var send = {
				op:'model_img_get',
				model_id:_num($('#dev_model').val())
			},
			dev = $('#device_image');
		dev.html('');
		if(send.model_id) {
			dev.addClass('busy');
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success)
					 dev.html(res.img)
						.find('img').on('load', function() {
							$(this).show().parent().removeClass('busy');
						});
			}, 'json');
		}
	},

	zayavDevSelect = function(dev) {
		modelImageGet(dev);
		if(!dev.device_id) {
			$('#equip-spisok').html('');
			$('#equip-tr').addClass('dn');
		} else if(!dev.vendor_id && !dev.model_id) {
			var send = {
				op:'equip_check_get',
				device_id:dev.device_id
			};
			$.post(AJAX_MAIN, send, function(res) {
				if(res.spisok) {
					$('#equip-spisok').html(res.spisok);
					$('#equip-tr').removeClass('dn');
				} else {
					$('#equip-spisok').html('');
					$('#equip-tr').addClass('dn');
				}
			}, 'json');
		}
	},
	zayavPlace = function(func) {
		if(!window.PLACE_OTHER) {
			DEVPLACE_SPISOK.push({
				uid:0,
				title:'<div id="place-other-div">������:<input type="text" id="place_other" class="dn" /></div>'
			});
			window.PLACE_OTHER = 1;
		}
		$('#device-place')._radio({
			spisok:DEVPLACE_SPISOK,
			light:1,
			func:function(val) {
				$('#place_other')[(val ? 'add' : 'remove') + 'Class']('dn');
				if(!val)
					$('#place_other').val('').focus();
				if(typeof func == 'function')
					func();
			}
		});
	},
	zayavKvit = function() {
		var html = '<table class="zayav-print">' +
				'<tr><td class="label">���� �����:<td>' + KVIT.dtime +
				'<tr><td class="label top">����������:<td>' + KVIT.device +
				'<tr><td class="label">����:<td>' + (KVIT.color ? KVIT.color : '<i>�� ������</i>') +
				'<tr><td class="label">IMEI:<td>' + (ZI.imei ? ZI.imei : '<i>�� ������</i>') +
				'<tr><td class="label">�������� �����:<td>' + (ZI.serial ? ZI.serial : '<i>�� ������</i>') +
				'<tr><td class="label">������������:<td>' + (KVIT.equip ? KVIT.equip : '<i>�� �������</i>') +
				'<tr><td class="label">��������:<td><b>' + ZI.client_link + '</b>' +
				'<tr><td class="label">�������:<td>' + (KVIT.phone ? KVIT.phone : '<i>�� ������</i>') +
				'<tr><td class="label top">�������������:<td><textarea id="defect">' + KVIT.defect + '</textarea>' +
				'<tr><td colspan="2"><a id="preview"><span>��������������� �������� ���������</span></a>' +
				'</table>',
			dialog = _dialog({
				width: 380,
				top: 30,
				head: '������ �' + ZI.nomer + ' - ������������ ���������',
				content: html,
				butSubmit: '��������� ���������',
				submit: submit
			});
		$('#defect').focus().autosize();
		$('#preview').click(function () {
			var t = $(this),
				send = {
					op: 'zayav_kvit',
					zayav_id: ZI.id,
					defect: $.trim($('#defect').val())
				};
			if(t.hasClass('_busy'))
				return;
			if(!send.defect)
				dialog.err('�� ������� �������������');
			else {
				t.addClass('_busy');
				$.post(AJAX_MAIN, send, function(res) {
					t.removeClass('_busy');
					if(res.success)
						kvitHtml(res.id);
				}, 'json');
			}
		});
		function submit() {
			var send = {
				op: 'zayav_kvit',
				zayav_id: ZI.id,
				defect: $.trim($('#defect').val()),
				active: 1
			};
			if(!send.defect)
				dialog.err('�� ������� �������������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('��������� ���������');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
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
		window.open(URL + '&p=print&d=kvit_html&id=' + id, 'kvit', params);
	},

	cartridgeNew = function(id, callback) {
		var t = $(this),
			html = '<table id="cartridge-new-tab">' +
				'<tr><td class="label r">���:<td><input type="hidden" id="type_id" value="1" />' +
				'<tr><td class="label r"><b>������ ���������:</b><td><input type="text" id="name" />' +
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
		$('#type_id')._select({
			spisok:CARTRIDGE_TYPE
		});
		$('#name').focus();
		$('#name,#cost_filling,#cost_restore,#cost_chip').keyEnter(submit);
		function submit() {
			var send = {
				op:'cartridge_new',
				type_id:$('#type_id').val(),
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
				$.post(AJAX_MAIN, send, function(res) {
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
	cartridgeSchetSet = function(schet_id) {
		var send = {
			op:'zayav_cartridge_schet_set',
			schet_id:schet_id,
			ids:_checkAll()
		};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				location.reload();
		}, 'json');
	},
	zayavCartridgeAdd = function() {
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
				zayav_id:ZI.id,
				ids:$('#crt').cartridge('get')
			};
			if(!send.ids) dialog.err('�� ������� �� ������ ���������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#zc-spisok').html(res.html);
						_msg();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	},
	zayavCartridgeSchet = function() {
		if(!_checkAll())
			return false;

		var	dialog = _dialog({
				head:'��������� ���������� � ����������',
				load:1,
				butSubmit:''
			}),
			send = {
				op:'zayav_cartridge_ids',
				ids:_checkAll()
			};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				dialog.close();
				_schetEdit({
					edit:1,
					client_id:ZI.client_id,
					client:ZI.client_link,
					zayav_id:ZI.id,
					arr:res.arr,
					func:cartridgeSchetSet
				});
			} else
				dialog.loadError();
		}, 'json');
		return true;
	},

	zpNameSelect = function(id, name_id) {
		if(!window.ZPNAME)
			window.ZPNAME = {};
		ZPNAME.device_id = id;
		$('#name_id')._select({
			width:230,
			funcAdd:id ? zpNameAdd : null,
			title0:id ? '������������ �������� �� �������' : '������� �������� ����������',
			spisok:ZPNAME_SPISOK[id]
		})._select(name_id || 0);
	},
	zpNameAdd = function() {//�������� ����� ��������
		var html =
				'<table id="zpname-add-tab">' +
					'<tr><td class="label">����������:<td><b>' + DEV_ASS[ZPNAME.device_id] + '</b>' +
					'<tr><td class="label">��������:<td><input id="name" type="text" maxlength="100" />' +
				'</table>',
			dialog = _dialog({
				width:390,
				head:'���������� ������ ������������ ��������',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'zpname_add',
				device_id:ZPNAME.device_id,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.err('�� ������� ��������');
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						if($('.sa-equip').length)
							$('#zp-spisok').html(res.zp);
						else {
							ZPNAME_SPISOK[ZPNAME.device_id].push({
								uid:res.id,
								title:send.name
							});
							$('#name_id')
								._select(ZPNAME_SPISOK[ZPNAME.device_id])
								._select(res.id);
						}
						dialog.close();
						_msg();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	};

$.fn.device = function(o) {
	o = $.extend({
		width:150,
		func:function() {},
		func_device:function() {},
		type_no:0,
		device_id:0,
		vendor_id:0,
		model_id:0,
		device_multiselect:0,
		device_ids:null, // ������ id, ������� ����� �������� � ������ ��� ���������
		vendor_ids:null, // ��� ��������������
		model_ids:null,  // ��� �������
		add:0,
		device_funcAdd:null, // ������� �����, ���� ������ ��������� ����� ��������
		vendor_funcAdd:null,
		model_funcAdd:null,
		no_model:0 //�� �������� ������ ������� (��� �������)
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
	if(o.add > 0) {
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
			$.post(AJAX_MAIN, send, function(res) {
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
			$.post(AJAX_MAIN, send, function(res) {
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
			$.post(AJAX_MAIN, send, function (res) {
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
		multiselect:o.device_multiselect,
		title0:device_no[o.type_no],
		spisok:DEV_SPISOK,
		func:function(device_id) {
			venSel.val(0);
			modSel.val(0)._select('remove'); //��������� ������ ������ � ��������������� � 0, ���� ��� �����
			var id = devSel.val();
			if(id == '0' || id.split(',').length > 1)
				venSel._select('remove');
			else
				getVendor(0);
			o.func(getIds());
			o.func_device(device_id);
		},
		funcAdd:o.device_funcAdd,
		bottom:3
	});

	if(typeof o.device_id == 'number' && o.device_id || o.device_id != '0' && o.device_id.split(',').length == 1)
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
		if(o.no_model)
			return;
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
			for(var n = 0; n < spisok.length; n++) {
				var sp = spisok[n].title;
				if(sp == undefined)
					continue;
				if(sp.toLowerCase() == name)
					return true;
			}
		}
		return false;
	}

	function getIds() {
		return {
			device_id:o.device_multiselect ? devSel.val() : _num(devSel.val()),
			vendor_id:_num(venSel.val()),
			model_id:_num(modSel.val())
		};
	}
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
	.on('click', '#zayav-dev-place-change', function() {
		var html =
				'<table class="device-add-tab">' +
					'<tr><td class="label r topi">��������������� ����������:<td><input type="hidden" id="device-place" value="-1" />' +
				'</table>',

			dialog = _dialog({
				width:420,
				head:'��������� ��������������� ����������',
				content:html,
				butSubmit:'���������',
				submit:submit
			});
		zayavPlace(ZI.place_other);

		function submit() {
			var send = {
				op:'zayav_device_place',
				zayav_id:ZI.id,
				place:_num($('#device-place').val()),
				place_other:$('#place_other').val()
			};
			if(send.place)
				send.place_other = '';
			if(send.place == -1 || send.place == 0 && send.place_other == '') {
				dialog.err('������� ��������������� ����������');
				$('#place_other').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg();
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#_zayav-info #zp-add', function() {
		var html = '<div class="zayav_zp_add">' +
				'<center>���������� �������� � ����������<br />' +
					'<b>' +
						DEV_ASS[ZI.device_id] + ' ' +
						VENDOR_ASS[ZI.vendor_id] + ' ' +
						MODEL_ASS[ZI.model_id] +
					'</b>.'+
				'</center>' +
				'<table style="border-spacing:6px">' +
					'<tr><td class="label r">������������ ��������:<td><input type="hidden" id="name_id" />' +
					'<tr><td class="label r">������:<td><input type="text" id="version" />' +
					'<tr><td class="label r">����:<td><input type="hidden" id="color_id" />' +
				'</table>' +
			'</div>',
			dialog = _dialog({
				top:40,
				width:400,
				head:'�������� ����� ��������',
				content:html,
				submit:submit
			});

		zpNameSelect(ZI.device_id);
		$('#color_id')._select({
			width:130,
			title0:'���� �� ������',
			spisok:COLOR_SPISOK
		});
		function submit() {
			var send = {
				op:'zayav_zp_add',
				zayav_id: ZI.id,
				name_id:_num($('#name_id').val()),
				version:$('#version').val(),
				color_id:$('#color_id').val()
			};
			if(!send.name_id)
				dialog.err('�� ������� ������������ ��������');
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						_msg();
						dialog.close();
						$('#zayav-zp-spisok').html(res.html);
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#_zayav-info .zakaz', function() {//����� �������� �� ������
		var t = $(this),
			send = {
				op:'zayav_zp_zakaz',
				zayav_id:ZI.id,
				zp_id:t.parent().parent().attr('val')
			};
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success) {
				t.html('��������!').attr('class', 'zakaz_ok');
				_msg(res.msg);
			}
		}, 'json');
	})
	.on('click', '#_zayav-info .zakaz_ok', function() {
		location.href = URL + '&p=zp&menu=3';
	})
	.on('click', '#_zayav-info .set', function() {
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
				zayav_id:ZI.id
			};
			dialog.process();
			$.post(AJAX_MAIN, send, function(res) {
				if(res.success) {
					dialog.close();
					_msg();
					location.reload();
				} else
					dialog.abort();
			},'json');
		}
	})
	.on('click', '#diagnost-ready', function () {
		var html =
				'<div class="_info">' +
					'����� �������� ����������� ����������� � ������ ����� �������� �����������. ' +
					'��� ������������� ����� �������� �����������.' +
				'</div>' +
				'<table class="_dialog-tab" id="zayav-diagnost-tab">' +
				'<tr><td class="label">������:<td><b>�' + ZI.nomer + '</b>' +
				'<tr><td class="label topi">����������:<td><textarea id="comm"></textarea>' +
				'<tr><td class="label">�������� �����������:<td><input type="hidden" id="diagnost-remind" />' +
				'<tr class="remind-tr"><td class="label">����������:<td><input type="text" id="remind-txt" value="�������� ���������� �����������">' +
				'<tr class="remind-tr"><td class="label">����:<td><input type="hidden" id="remind-day">' +
				'</table>',
			dialog = _dialog({
				width: 450,
				head: '�������� ����������� �����������',
				content: html,
				submit: submit
			});
		$('#comm').autosize().focus();
		$('#diagnost-remind')._check();
		$('#diagnost-remind_check').click(function() {
			$('.remind-tr').toggle();
		});
		$('#remind-day')._calendar();

		function submit() {
			var send = {
				op: 'zayav_diagnost',
				zayav_id: ZI.id,
				comm: $('#comm').val(),
				remind: _num($('#diagnost-remind').val()),
				remind_txt: $('#remind-txt').val(),
				remind_day: $('#remind-day').val()
			};
			if(!send.comm) {
				dialog.err('�� ������ ����� ����������');
				$('#comm').focus();
			}
			else if(send.remind && !send.remind_txt) {
				dialog.err('�� ������� ���������� �����������');
				$('#remind-txt').focus();
			}
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('���������� ����������� �������');
						document.location.reload();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '.zayav_kvit', function() {
		kvitHtml($(this).attr('val'));
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
		$.post(AJAX_MAIN, send, function(res) {
			t.removeClass('_busy');
			if(res.success)
				t.after(res.html);
		}, 'json');
	})

	.on('click', '#zayav-cartridge #cart-add', zayavCartridgeAdd)
	.on('click', '#zayav-cartridge .cart-edit', function() {//���������� �������� ���� ����������
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
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						$('#zc-spisok').html(res.html);
						_msg();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#zayav-cartridge .cart-del', function() {
		var t = _parent($(this));
		_dialogDel({
			id:t.attr('val'),
			head:'���������',
			op:'zayav_info_cartridge_del',
			func:function() {
				t.remove();
			}
		});
	});
