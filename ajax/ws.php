<?php
switch(@$_POST['op']) {
	case 'scanner_word':
		$word = win1251(htmlspecialchars(trim($_POST['word'])));
		if(empty($word))
			jsonError();
		if(!preg_match(REGEXP_WORD, $word))
			jsonError();
		$sql = "SELECT `id`
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND (`imei`='".$word."'
				   OR `serial`='".$word."'
				   OR `barcode`='".substr($word, 0, 12)."')";
		$id = query_value($sql);
		$send = array();
		if($id)
			$send['zayav_id'] = $id;
		elseif(preg_match(REGEXP_NUMERIC, $word) && strlen($word) == 15)
			$send['imei'] = 1;
		jsonSuccess($send);
		break;

	case 'base_device_add':
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "SELECT `name` FROM `base_device` WHERE `name`='".addslashes($name)."'";
		if(mysql_num_rows(query($sql)))
			jsonError();

		$sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `base_device`");
		$sql = "INSERT INTO `base_device` (
				`name`,
				`name_rod`,
				`name_mn`,
				`sort`,
				`viewer_id_add`
			) values (
				'".addslashes($name)."',
				'".addslashes($name)."',
				'".addslashes($name)."',
				".$sort.",
				".VIEWER_ID."
			)";
		query($sql);
		$send['id'] = mysql_insert_id();

		$sql = "UPDATE `workshop` SET `devs`=CONCAT(`devs`,',".$send['id']."') WHERE `id`=".WS_ID;
		query($sql);

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'device_name');
		xcache_unset(CACHE_PREFIX.'setup_global');
		xcache_unset(CACHE_PREFIX.'workshop_'.WS_ID);
		jsonSuccess($send);
		break;
	case 'base_vendor_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$device_id = intval($_POST['device_id']);
		$sql = "SELECT `name` FROM `base_vendor` WHERE `device_id`=".$device_id." AND `name`='".addslashes($name)."'";
		if(mysql_num_rows(query($sql)))
			jsonError();

		$sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `base_vendor` WHERE `device_id`=".$device_id);
		$sql = "INSERT INTO `base_vendor` (
				`device_id`,
				`name`,
				`sort`,
				`viewer_id_add`
			) values (
				".$device_id.",
				'".addslashes($name)."',
				".$sort.",
				".VIEWER_ID."
			)";
		query($sql);
		$send['id'] = mysql_insert_id();

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'vendor_name');
		xcache_unset(CACHE_PREFIX.'setup_global');
		xcache_unset(CACHE_PREFIX.'workshop_'.WS_ID);
		jsonSuccess($send);
		break;
	case 'base_model_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$device_id = intval($_POST['device_id']);
		$vendor_id = intval($_POST['vendor_id']);
		$sql = "SELECT `name`
				FROM `base_model`
				WHERE `device_id`=".$device_id."
				  AND `vendor_id`=".$vendor_id."
				  AND `name`='".addslashes($name)."'";
		if(mysql_num_rows(query($sql)))
			jsonError();

		$sql = "INSERT INTO `base_model` (
				`device_id`,
				`vendor_id`,
				`name`,
				`viewer_id_add`
			) values (
				".$device_id.",
				".$vendor_id.",
				'".addslashes($name)."',
				".VIEWER_ID."
			)";
		query($sql);
		$send['id'] = mysql_insert_id();

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'model_name_count');
		xcache_unset(CACHE_PREFIX.'setup_global');
		xcache_unset(CACHE_PREFIX.'workshop_'.WS_ID);
		jsonSuccess($send);
		break;

	case 'zayav_add':
		if(!$client_id = _num($_POST['client_id']))
			jsonError();
		if(!$device = _num($_POST['device']))
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['day_finish']))
			jsonError();

		$vendor = _num($_POST['vendor']);
		$model = _num($_POST['model']);
		if(!empty($_POST['equip'])) {
			$ids = explode(',', $_POST['equip']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}
		$place_id = _num($_POST['place']);
		$place_other = !$place_id ? _txt($_POST['place_other']) : '';
		$imei = win1251(htmlspecialchars(trim($_POST['imei'])));
		$serial = win1251(htmlspecialchars(trim($_POST['serial'])));
		$color = intval($_POST['color']);
		$color_dop = $color ? intval($_POST['color_dop']) : 0;
		$diagnost = _bool($_POST['diagnost']);
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));
		$pre_cost = _num($_POST['pre_cost']);
		$day_finish = $_POST['day_finish'];

		$modelName = '';
		if($model > 0) {
			$sql = "select `name` FROM `base_model` WHERE `id`=".$model;
			$r = mysql_fetch_assoc(query($sql));
			$modelName = $r['name'];
		}

		$sql = "SELECT IFNULL(MAX(`nomer`),0)+1 FROM `zayav` WHERE `ws_id`=".WS_ID." LIMIT 1";
		$nomer = query_value($sql);

		$sql = "INSERT INTO `zayav` (
					`ws_id`,
					`nomer`,
					`client_id`,

					`base_device_id`,
					`base_vendor_id`,
					`base_model_id`,

					`equip`,
					`imei`,
					`serial`,
					`color_id`,
					`color_dop`,

					`zayav_status`,
					`zayav_status_dtime`,

					`device_place`,

					`diagnost`,
					`barcode`,
					`pre_cost`,
					`day_finish`,
					`viewer_id_add`,
					`find`
				) VALUES (
					".WS_ID.",
					".$nomer.",
					".$client_id.",

					".$device.",
					".$vendor.",
					".$model.",

					'".$_POST['equip']."',
					'".addslashes($imei)."',
					'".addslashes($serial)."',
					".$color.",
					".$color_dop.",

					1,
					current_timestamp,

					".$place_id.",

					".$diagnost.",
					'".rand(10, 99).(time() + rand(10000, 99999))."',
					".$pre_cost.",
					'".$day_finish."',
					".VIEWER_ID.",
					'".addslashes($modelName.' '.$imei.' '.$serial)."'
				)";
		query($sql);
		$send['id'] = query_insert_id('zayav');

		zayavPlaceCheck($send['id'], $place_id, $place_other);

		if($comm) {
			$sql = "INSERT INTO `vk_comment` (
						`table_name`,
						`table_id`,
						`txt`,
						`viewer_id_add`
					) VALUES (
						'zayav',
						".$send['id'].",
						'".$comm."',
						".VIEWER_ID."
					)";
			query($sql);
		}

		_history(array(
			'type_id' => 73,
			'client_id' => $client_id,
			'zayav_id' => $send['id']
		));
		jsonSuccess($send);
		break;
	case 'model_img_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']))
			jsonError();
		$send['img'] = _imageGet(array(
			'owner' => 'dev'.intval($_POST['model_id']),
			'view' => 1
		));
		jsonSuccess($send);
		break;
	case 'equip_check_get':
		if(!$device_id = _num($_POST['device_id']))
			jsonError();
		$send['spisok'] = utf8(devEquipCheck($device_id));
		jsonSuccess($send);
		break;
	case 'zayav_day_finish':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();

		$day = $_POST['day'];
		$zayav_spisok = _bool($_POST['zayav_spisok']);

		$send['html'] = utf8(_zayavFinishCalendar($day, '', $zayav_spisok));
		jsonSuccess($send);
		break;
	case 'zayav_day_finish_next':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();

		$day = $_POST['day'];
		$zayav_spisok = _bool($_POST['zayav_spisok']);

		$send['html'] = utf8(_zayavFinishCalendar($day, $_POST['mon'], $zayav_spisok));
		jsonSuccess($send);
		break;
	case 'zayav_day_finish_save':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();

		$day = $_POST['day'];
		$zayav_id = _num(@$_POST['zayav_id']);
		$save = _bool($_POST['save']);

		if($zayav_id && $save) {
			$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
			if(!$z = query_assoc($sql))
				jsonError();
			zayav_day_finish_change($zayav_id, $day);
		}

		$send['data'] = utf8($day == '0000-00-00' ? '�� ������' : FullData($day, 1, 0, 1));
		jsonSuccess($send);
		break;
	case 'zayav_spisok':
		$_POST['find'] = win1251($_POST['find']);
		$data = zayav_spisok($_POST);
		if($data['filter']['page'] == 1) {
			setcookie('zback_spisok_page', 1, time() + 3600, '/');
			$send['all'] = utf8($data['result']);
		}
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zayav_edit':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		if(!$client_id = _num($_POST['client_id']))
			jsonError();
		if(!$device = _num($_POST['device']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['model']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_dop']))
			jsonError();
		$vendor = intval($_POST['vendor']);
		$model = intval($_POST['model']);
		$imei = win1251(htmlspecialchars(trim($_POST['imei'])));
		$serial = win1251(htmlspecialchars(trim($_POST['serial'])));
		$color_id = intval($_POST['color_id']);
		$color_dop = $color_id ? intval($_POST['color_dop']) : 0;
		if(!empty($_POST['equip'])) {
			$ids = explode(',', $_POST['equip']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}
		$equip = $_POST['equip'];
		$diagnost = _bool($_POST['diagnost']);
		$pre_cost = _num($_POST['pre_cost']);

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav` SET
					`client_id`=".$client_id.",
					`base_device_id`=".$device.",
					`base_vendor_id`=".$vendor.",
					`base_model_id`=".$model.",
					`imei`='".addslashes($imei)."',
					`serial`='".addslashes($serial)."',
					`color_id`=".$color_id.",
					`color_dop`=".$color_dop.",
					`equip`='".$equip."',
					`pre_cost`=".$pre_cost.",
					`diagnost`=".$diagnost.",
					`find`='".addslashes(_modelName($model).' '.$imei.' '.$serial)."'
				WHERE `id`=".$zayav_id;
		query($sql);

		if($z['client_id'] != $client_id) {
			$sql = "UPDATE `_money_accrual`
					SET `client_id`=".$client_id."
					WHERE `app_id`=".APP_ID."
					  AND `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql, GLOBAL_MYSQL_CONNECT);
			$sql = "UPDATE `_money_income`
					SET `client_id`=".$client_id."
					WHERE `app_id`=".APP_ID."
					  AND `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql, GLOBAL_MYSQL_CONNECT);
			clientBalansUpdate($z['client_id']);
			clientBalansUpdate($client_id);
		}

		$old = _deviceName($z['base_device_id'])._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']);
		$new = _deviceName($device)._vendorName($vendor)._modelName($model);

		$changes =
			_historyChange('������', $z['client_id'], $client_id, _clientVal($z['client_id'], 'go'), _clientVal($client_id, 'go')).
			_historyChange('����������', $old, $new).
			_historyChange('imei', $z['imei'], $imei).
			_historyChange('Serial', $z['serial'], $serial).
			_historyChange('����', _color($z['color_id'], $z['color_dop']), _color($color_id, $color_dop)).
			_historyChange('��������', zayavEquipSpisok($z['equip']), zayavEquipSpisok($equip)).
			_historyChange('��������� �������', $z['pre_cost'] ? $z['pre_cost'] : '', $pre_cost ? $pre_cost : '').
			_historyChange('�����������', _daNet($z['diagnost']), _daNet($diagnost));
		if($changes)
			_history(array(
				'type_id' => 72,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'v1' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;
	case 'zayav_status_place':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		if(!$zayav_status = _num($_POST['status']))
			jsonError();

		if(!preg_match(REGEXP_NUMERIC, $_POST['place']))
			jsonError();
		$place_id = _num($_POST['place']);
		$place_other = !$place_id ? _txt($_POST['place_other']) : '';
		if(!$place_id && !$place_other)
			jsonError();

		if(!preg_match(REGEXP_DATE, $_POST['day_finish']))
			jsonError();
		$day_finish = $_POST['day_finish'];

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($z['zayav_status'] == $zayav_status)
			jsonError();

		$sql = "UPDATE `zayav`
				SET `zayav_status`=".$zayav_status.",
					`zayav_status_dtime`=CURRENT_TIMESTAMP
				WHERE `id`=".$zayav_id;
		query($sql);

		_history(array(
			'type_id' => 71,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => $z['zayav_status'],
			'v2' => $zayav_status
		));

		zayavPlaceCheck($zayav_id, $place_id, $place_other);
		zayav_day_finish_change($zayav_id, $day_finish);

		jsonSuccess();
		break;
	case 'zayav_cartridge_status':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		if(!$zayav_status = _num($_POST['status']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `cartridge` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($z['zayav_status'] == $zayav_status)
			jsonError();

		$sql = "UPDATE `zayav`
				SET `zayav_status`=".$zayav_status.",
					`zayav_status_dtime`=CURRENT_TIMESTAMP
				WHERE `id`=".$zayav_id;
		query($sql);

		_history(array(
			'type_id' => 71,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => $z['zayav_status'],
			'v2' => $zayav_status
		));

		jsonSuccess();
		break;
	case 'zayav_device_place':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		$place_id = _num($_POST['place']);
		$place_other = !$place_id ? _txt($_POST['place_other']) : '';

		zayavPlaceCheck($zayav_id, $place_id, $place_other);

		jsonSuccess();
		break;
	case 'zayav_zp_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['name_id']) || $_POST['name_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
			jsonError();
		$sql = "SELECT
					`id`,
					`base_device_id`,
					`base_vendor_id`,
					`base_model_id`
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND `id`=".intval($_POST['zayav_id']);
		if(!$zp = mysql_fetch_assoc(query($sql)))
			jsonError();
		define('MODEL', _vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']));
		$zp['name_id'] = intval($_POST['name_id']);
		$zp['version'] = win1251(htmlspecialchars(trim($_POST['version'])));
		$zp['color_id'] = intval($_POST['color_id']);
		zpAddQuery($zp);
		$send['html'] = utf8(zayav_zp($zp));
		jsonSuccess($send);
		break;
	case 'zayav_zp_zakaz':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();

		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".intval($_POST['zp_id']);
		$zp = mysql_fetch_assoc(query($sql));
		$compat_id = $zp['compat_id'] ? $zp['compat_id'] : $zp['id'];

		$sql = "INSERT INTO `zp_zakaz` (
					`ws_id`,
					`zp_id`,
					`zayav_id`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$compat_id.",
					".intval($_POST['zayav_id']).",
					".VIEWER_ID."
				)";
		query($sql);
		$send['msg'] = utf8('�������� <b>'._zpName($zp['name_id']).'</b> ��� '._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).' ��������� � ������.');
		jsonSuccess($send);
		break;
	case 'zayav_zp_set':// ��������� �������� �� ������
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		if(!isset($_POST['count']))
			$_POST['count'] = 1;
		if(empty($_POST['count']) || !preg_match(REGEXP_NUMERIC, $_POST['count']))
			jsonError();

		$compat_id = _zpCompatId($zp_id);
		$count = intval($_POST['count']) * -1;
		$prim = isset($_POST['prim']) ? win1251(htmlspecialchars(trim($_POST['prim']))) : '';

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT * FROM `zp_catalog` WHERE id=".$zp_id." LIMIT 1";
		if(!$zp = query_assoc($sql))
			jsonError();

		$sql = "INSERT INTO `zp_move` (
					`ws_id`,
					`zp_id`,
					`count`,
					`type`,
					`zayav_id`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$compat_id.",
					".$count.",
					'set',
					".$zayav_id.",
					'".$prim."',
					".VIEWER_ID."
				)";
		query($sql);

		$count = _zpAvaiSet($compat_id);

		//�������� �� ������ ��������, ����������� � ������
		query("DELETE FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zayav_id`=".$zayav_id." AND `zp_id`=".$zp_id);

		$parent_id = 0;
		$sql = "SELECT `id`,`parent_id`
				FROM `vk_comment`
				WHERE `table_name`='zayav'
				  AND `table_id`=".$zayav_id."
				  AND `status`
				ORDER BY `id` DESC
				LIMIT 1";
		if($r = mysql_fetch_assoc(query($sql)))
			$parent_id = $r['parent_id'] ? $r['parent_id'] : $r['id'];

		define('MODEL', _vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']));
		$sql = "INSERT INTO `vk_comment` (
					`table_name`,
					`table_id`,
					`txt`,
					`parent_id`,
					`viewer_id_add`
				) VALUES (
					'zayav',
					".$zayav_id.",
					'".addslashes('��������� ��������: <a class="zp-id" val="'.$zp_id.'">'._zpName($zp['name_id']).' '.MODEL.'</a>')."',
					".$parent_id.",
					".VIEWER_ID."
				)";
		query($sql);

		_history(array(
			'type_id' => 13,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => $count,
			'zp_id' => $zp_id
		));

		//���������� �������� � ������� �� ������
		$cena = query_value("SELECT `cena` FROM `zp_move` WHERE `zp_id`=".$compat_id." AND `type`='' ORDER BY `id` DESC LIMIT 1");
		$sql = "INSERT INTO `_zayav_expense` (
							`app_id`,
							`ws_id`,
							`zayav_id`,
							`category_id`,
							`zp_id`,
							`sum`
						) VALUES (
							".APP_ID.",
							".WS_ID.",
							".$zayav_id.",
							2,
							".$compat_id.",
							".$cena."
						)";
		query($sql, GLOBAL_MYSQL_CONNECT);

		jsonSuccess();
		break;
	case 'zayav_tooltip':
		if(!$id = _num($_POST['id']))
			jsonError();

		$z = query_assoc("SELECT * FROM `zayav` WHERE `id`=".$id);
		$c = query_assoc("SELECT * FROM `client` WHERE !`deleted` AND `id`=".$z['client_id']);

		$telefon = _clientTelefon($c);


		$send['html'] = utf8($html);
		jsonSuccess($send);
		break;
	case 'zayav_nomer_info'://��������� ������ � ������ �� ������
		if(empty($_POST['nomer']) || !preg_match(REGEXP_NUMERIC, $_POST['nomer']))
			jsonError();
		$nomer = intval($_POST['nomer']);
		$sql = "SELECT *
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND `nomer`=".$nomer."
				  AND `zayav_status`
				LIMIT 1";
		if(!$z = mysql_fetch_assoc(query($sql)))
			$send['html'] = '<span class="zayavNomerTab">������ �� �������</span>';
		else
			$send['html'] = '<table class="zayavNomerTab">'.
				'<tr><td>'._zayavImg($z).
					'<td><a href="'.URL.'&p=zayav&d=info&id='.$z['id'].'">'._deviceName($z['base_device_id']).'<br />'.
						   _vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).
						'</a>'.
			'</table>'.
			'<input type="hidden" id="zayavNomerId" value="'.$z['id'].'" />';
		$send['html'] = utf8($send['html']);
		jsonSuccess($send);
		break;
	case 'zayav_kvit':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		$active = _bool(@$_POST['active']);
		$defect = _txt($_POST['defect']);
		if(empty($defect))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		if(!_clientQuery($z['client_id']))
			jsonError();

		query("DELETE FROM `zayav_kvit` WHERE `ws_id`=".WS_ID." AND !`active` AND `zayav_id`=".$zayav_id);

		$zayav = 'zayav'.$z['id'];
		$dev = 'dev'.$z['base_model_id'];
		$v = array(
			'owner' => array($zayav, $dev),
			'size' => 'b',
			'x' => 180,
			'y' => 220
		);
		$img = _imageGet($v);
		$image = '';
		if($img[$zayav]['id'])
			$image = $img[$zayav]['img'];
		elseif($img[$dev]['id'])
			$image = $img[$dev]['img'];

		$sql = "INSERT INTO `zayav_kvit` (
					`ws_id`,
					`zayav_id`,
					`nomer`,
					`dtime`,

					`device_id`,
					`vendor_id`,
					`model_id`,

					`color_id`,
					`color_dop`,

					`imei`,
					`serial`,
					`equip`,

					`client_fio`,
					`client_telefon`,

					`image`,
					`defect`,
					`active`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$zayav_id.",
					".$z['nomer'].",
					'".$z['dtime_add']."',

					".$z['base_device_id'].",
					".$z['base_vendor_id'].",
					".$z['base_model_id'].",

					".$z['color_id'].",
					".$z['color_dop'].",

					'".addslashes($z['imei'])."',
					'".addslashes($z['serial'])."',
					'".addslashes($z['equip'])."',

					'".addslashes(_clientVal($z['client_id'], 'name'))."',
					'".addslashes(_clientVal($z['client_id'], 'phone'))."',

					'".addslashes($image)."',
					'".addslashes($defect)."',
					".$active.",
					".VIEWER_ID."
				)";
		$send['id'] = query($sql);

		if($active)
			$send['html'] = utf8(zayav_kvit($zayav_id));

		jsonSuccess($send);
		break;
	case 'zayav_money_update':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$send = zayavBalansUpdate($zayav_id);

		$expense = zayav_expense_spisok($z, 'all');
		$send['html'] = utf8($expense['html']);
		foreach($expense['array'] as $n => $r)
			$expense['array'][$n][1] = utf8($expense['array'][$n][1]);
		$send['array'] = $expense['array'];
		$send['acc_sum'] = utf8(zayav_acc_sum($z));

		//������� ���������� �� ��� ����������
		$acc_sum = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE !`deleted` AND `zayav_id`=".$zayav_id);
		$expense_sum = query_value("SELECT SUM(`sum`) FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id." AND `category_id`!=1");
		$send['worker_zp'] = round(($acc_sum - $expense_sum) * 0.3);

		jsonSuccess($send);
		break;
	case 'zayav_executer_change'://��������� ����������� ������
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$executer_id = _num($_POST['executer_id']);
		if($executer_id) {//���� id ������ ���������� ��� � ���������� - ������
			$sql = "SELECT COUNT(*)
					FROM `_vkuser`
					WHERE `app_id`=".APP_ID."
					  AND `ws_id`=".WS_ID."
					  AND `worker`
					  AND `viewer_id`=".$executer_id;
			if(!query_value($sql, GLOBAL_MYSQL_CONNECT))
				jsonError();
		}

		if($z['executer_id'] == $executer_id)
			jsonError();

		$sql = "UPDATE `zayav` SET `executer_id`=".$executer_id." WHERE `id`=".$zayav_id;
		query($sql);


		_history(array(
			'type_id' => 58,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' =>
				'<table>'.
					'<tr><td>'.($z['executer_id'] ? _viewer($z['executer_id'], 'viewer_name') : '').
						'<td>�'.
						'<td>'.($executer_id ? _viewer($executer_id, 'viewer_name') : '').
					'</table>'
		));

		jsonSuccess();
		break;
	case 'zayav_diagnost':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		$comm = _txt($_POST['comm']);
		$remind = _bool($_POST['remind']);
		$remind_txt = _txt($_POST['remind_txt']);
		$remind_day = _txt($_POST['remind_day']);
		if($remind) {
			if(!$remind_txt)
				jsonError();
			if(!preg_match(REGEXP_DATE, $remind_day))
				jsonError();
		}

		$sql = "SELECT *
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND !`deleted`
				  AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `zayav` SET `diagnost`=0 WHERE `id`=".$zayav_id;
		query($sql);

		_vkCommentAdd('zayav', $zayav_id, $comm);

		//�������� �����������, ���� ����
		if($remind)
			_remind_add(array(
				'zayav_id' => $zayav_id,
				'txt' => $remind_txt,
				'day' => $remind_day
			));


		_history(array(
			'type_id' => 62,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id
		));

		jsonSuccess();
		break;

	case 'cartridge_new'://�������� ����� ������ ���������
		if(!$type_id = _num($_POST['type_id']))
			jsonError();

		$name = _txt($_POST['name']);
		$cost_filling = _num($_POST['cost_filling']);
		$cost_restore = _num($_POST['cost_restore']);
		$cost_chip = _num($_POST['cost_chip']);

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `setup_cartridge` (
					`ws_id`,
					`type_id`,
					`name`,
					`cost_filling`,
					`cost_restore`,
					`cost_chip`
				) VALUES (
					".WS_ID.",
					".$type_id.",
					'".addslashes($name)."',
					".$cost_filling.",
					".$cost_restore.",
					".$cost_chip."
				)";
		query($sql);
		$send['insert_id'] = mysql_insert_id();

		xcache_unset(CACHE_PREFIX.'cartridge'.WS_ID);
		GvaluesCreate();

		_history(array(
			'type_id' => 1030,
			'v1' => $name
		));

		if($_POST['from'] == 'setup')
			$send['spisok'] = utf8(setup_service_cartridge_spisok());
		else {
			$send['spisok'] = query_selArray("SELECT `id`,`name` FROM `setup_cartridge` WHERE `ws_id`=".WS_ID." ORDER BY `name`");
		}

		jsonSuccess($send);
		break;
	case 'zayav_cartridge_add':
		if(!$client_id = _num($_POST['client_id']))
			jsonError();

		if(!$count = _num($_POST['count']))
			jsonError();

		if(!$pay_type = _num($_POST['pay_type']))
			jsonError();

		// ���� �� ������ �� ���� �������� (�������� ��������, ������ ����������� ������ ����������)
//		if(empty($_POST['ids']))
//			jsonError();

		$ids = $_POST['ids'];
		if(!empty($ids)) {
			$ids = explode(',', $_POST['ids']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}

		$comm = _txt($_POST['comm']);

		$sql = "SELECT IFNULL(MAX(`nomer`),0)+1 FROM `zayav` WHERE `ws_id`=".WS_ID." LIMIT 1";
		$nomer = query_value($sql);

		$sql = "INSERT INTO `zayav` (
					`ws_id`,
					`nomer`,
					`cartridge`,
					`client_id`,
					`cartridge_count`,
					`pay_type`,

					`zayav_status`,
					`zayav_status_dtime`,

					`barcode`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$nomer.",
					1,
					".$client_id.",
					".$count.",
					".$pay_type.",

					1,
					current_timestamp,

					'".rand(10, 99).(time() + rand(10000, 99999))."',
					".VIEWER_ID."
				)";
		query($sql);
		$send['id'] = query_insert_id('zayav');

		if(!empty($ids))
			foreach($ids as $id) {
				$sql = "INSERT INTO `zayav_cartridge` (
							`zayav_id`,
							`cartridge_id`
						) VALUES (
							".$send['id'].",
							".$id."
						)";
				query($sql);
			}

		_vkCommentAdd('zayav', $send['id'], $comm);

		_history(array(
			'type_id' => 54,
			'client_id' => $client_id,
			'zayav_id' => $send['id']
		));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_add'://���������� ���������� � ������
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		// ���� �� ������ �� ���� ��������
		if(empty($_POST['ids']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `cartridge` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$ids = explode(',', $_POST['ids']);
		for($n = 0; $n < count($ids); $n++) {
			if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
				jsonError();
		}

		$cartgidge = array();
		foreach($ids as $id) {
			$sql = "INSERT INTO `zayav_cartridge` (
						`zayav_id`,
						`cartridge_id`
					) VALUES (
						".$zayav_id.",
						".$id."
					)";
			query($sql);
			$cartgidge[] = '<u>'._cartridgeName($id).'</u>';
		}


		_history(array(
			'type_id' => 55,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => implode(', ', $cartgidge)
		));

		$send['html'] = utf8(zayav_cartridge_info_tab($zayav_id));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_edit'://���������� �������� �� ���������
		if(!$id = _num($_POST['id']))
			jsonError();
		if(!$cartridge_id = _num($_POST['cart_id']))
			jsonError();

		$filling = _bool($_POST['filling']);
		$restore = _bool($_POST['restore']);
		$chip = _bool($_POST['chip']);
		$cost = _num($_POST['cost']);
		$prim = _txt($_POST['prim']);

		$sql = "SELECT * FROM `zayav_cartridge` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `cartridge` AND `id`=".$r['zayav_id'];
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav_cartridge`
				SET `cartridge_id`=".$cartridge_id.",
					`filling`=".$filling.",
					`restore`=".$restore.",
					`chip`=".$chip.",
					`cost`=".$cost.",
					`dtime_ready`=".($filling || $restore || $chip ? "CURRENT_TIMESTAMP" : "'0000-00-00 00:00:00'").",
					`prim`='".addslashes($prim)."'
				WHERE `id`=".$id;
		query($sql);

		$changes =
			_historyChange('������', _cartridgeName($r['cartridge_id']), _cartridgeName($cartridge_id)).
			_historyChange('���������', $r['cost'], $cost).
			_historyChange('����������', $r['prim'], $prim);
		if($r['filling'] != $filling || $r['restore'] != $restore || $r['chip'] != $chip) {
			$old = array();
			if($r['filling'])
				$old[] = '���������';
			if($r['restore'])
				$old[] = '������������';
			if($r['chip'])
				$old[] = '������ ���';
			$new = array();
			if($filling)
				$new[] = '���������';
			if($restore)
				$new[] = '������������';
			if($chip)
				$new[] = '������ ���';
			$changes .= _historyChange('��������', implode(', ', $old), implode(', ', $new));
		}
		if($changes)
			_history(array(
				'type_id' => 57,
				'client_id' => $z['client_id'],
				'zayav_id' => $r['zayav_id'],
				'v1' => _cartridgeName($cartridge_id),
				'v2' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(zayav_cartridge_info_tab($r['zayav_id']));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_del'://�������� ��������� �� ������
		if(!$id = _num($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_cartridge` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `cartridge` AND `id`=".$r['zayav_id'];
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "DELETE FROM `zayav_cartridge` WHERE `id`=".$id;
		query($sql);

		_history(array(
			'type_id' => 56,
			'client_id' => $z['client_id'],
			'zayav_id' => $r['zayav_id'],
			'v1' => _cartridgeName($r['cartridge_id'])
		));


		$send['html'] = utf8(zayav_cartridge_info_tab($r['zayav_id']));
		jsonSuccess($send);
		break;
	case 'cartridge_spisok':
		$data = zayav_cartridge_spisok($_POST);
		if($data['filter']['page'] == 1)
			$send['all'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zayav_cartridge_edit':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		if(!$client_id = _num($_POST['client_id']))
			jsonError();
		if(!$count = _num($_POST['count']))
			jsonError();
		if(!$pay_type = _num($_POST['pay_type']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
				SET `client_id`=".$client_id.",
					`cartridge_count`=".$count.",
					`pay_type`=".$pay_type."
				WHERE `id`=".$zayav_id;
		query($sql);

		if($z['client_id'] != $client_id) {
			$sql = "UPDATE `_money_accrual`
					SET `client_id`=".$client_id."
					WHERE `app_id`=".APP_ID."
					  AND `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql, GLOBAL_MYSQL_CONNECT);
			$sql = "UPDATE `_money_income`
					SET `client_id`=".$client_id."
					WHERE `app_id`=".APP_ID."
					  AND `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql, GLOBAL_MYSQL_CONNECT);
			clientBalansUpdate($z['client_id']);
			clientBalansUpdate($client_id);
		}

		$changes =
			_historyChange('������', _clientVal($z['client_id'], 'go'), _clientVal($client_id, 'go')).
			_historyChange('���������� ����������', $z['cartridge_count'], $count).
			_historyChange('������', _payType($z['pay_type']), _payType($pay_type));

		if($changes)
			_history(array(
				'type_id' => 72,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'v1' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;

	case 'zp_add':
		$zp = array();
		if(!$zp['name_id'] = _num($_POST['name_id']))
			jsonError();
		if(!$zp['base_device_id'] = _num($_POST['device_id']))
			jsonError();

		$zp += array(
			'base_vendor_id' => _num($_POST['vendor_id']),
			'base_model_id' => _num($_POST['model_id']),
			'version' => _txt($_POST['version']),
			'color_id' => _num($_POST['color_id']),
		);
		$send['id'] = zpAddQuery($zp);

		jsonSuccess($send);
		break;
	case 'zp_spisok':
		$_POST['find'] = win1251($_POST['find']);
		$data = zp_spisok($_POST);
		if($data['filter']['page'] == 1)
			$send['all'] = utf8($data['result']);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zp_avai_add':
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		if(!$count = _num($_POST['count']))
			jsonError();

		$zp_id = _zpCompatId($zp_id);
		$cena = _cena($_POST['cena']);
		$summa = round($count * $cena, 2);

		$sql = "INSERT INTO `zp_move` (
					`ws_id`,
					`zp_id`,
					`count`,
					`cena`,
					`summa`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$zp_id.",
					".$count.",
					'".$cena."',
					'".$summa."',
					".VIEWER_ID."
				)";
		query($sql);
		_history(array(
			'type_id' => 18,
			'zp_id' => $zp_id,
			'v1' => $count
		));
		$send['count'] = _zpAvaiSet($zp_id);
		jsonSuccess($send);
		break;
	case 'zp_zakaz_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['count']))
			jsonError();
		$zp_id = _zpCompatId($_POST['zp_id']);
		$count = intval($_POST['count']);
		$zakazId = query_value("SELECT `id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id." AND `zayav_id`=0 LIMIT 1");
		if($count > 0) {
			$sql = "SELECT IFNULL(SUM(`count`),0)
					FROM `zp_zakaz`
					WHERE `ws_id`=".WS_ID."
					  AND `zp_id`=".$zp_id."
					  AND `zayav_id`>0
					LIMIT 1";
			$zakazZayavCount = query_value($sql);
			if($zakazZayavCount)
				$count -= $zakazZayavCount;
		}
		if($count > 0) {
			if($zakazId)
				query("UPDATE `zp_zakaz` SET `count`=".$count." WHERE `id`=".$zakazId);
			else {
				$sql = "INSERT INTO `zp_zakaz` (
							`ws_id`,
							`zp_id`,
							`count`,
							`viewer_id_add`
						) VALUES (
							".WS_ID.",
							".$zp_id.",
							".$count.",
							".VIEWER_ID."
						)";
				query($sql);
			}
		} else
			query("DELETE FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
		jsonSuccess();
		break;
	case 'zp_edit':
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		if(!$name_id = _num($_POST['name_id']))
			jsonError();
		if(!$device_id = _num($_POST['device_id']))
			jsonError();
		$vendor_id = _num($_POST['vendor_id']);
		$model_id = _num($_POST['model_id']);
		$version = _txt($_POST['version']);
		$color_id = _num($_POST['color_id']);
		$price_id = _num($_POST['price_id']);

		$find = ($model_id ? _modelName($model_id).' ' : '').$version;

		$sql = "UPDATE `zp_catalog`
				SET `name_id`=".$name_id.",
					`base_device_id`=".$device_id.",
					`base_vendor_id`=".$vendor_id.",
					`base_model_id`=".$model_id.",
					`version`='".$version."',
					`color_id`=".$color_id.",
					`price_id`=".$price_id.",
					`find`='".addslashes($find)."'
				WHERE `id`=".$zp_id;
		query($sql);

		$compat_id = _zpCompatId($zp_id);
		if($compat_id != $zp_id) {
			$sql = "UPDATE `zp_catalog`
					SET `name_id`=".$name_id.",
						`version`='".$version."',
						`price_id`=".$price_id."
					WHERE `id`=".$compat_id;
			query($sql);
		}

		jsonSuccess();
		break;
	case 'zp_sale':// ������� ��������
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || !$_POST['zp_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['invoice_id']) || !$_POST['invoice_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['count']) || !$_POST['count'])
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['cena']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			jsonError();

		$cena = round($_POST['cena'], 2);
		$count = intval($_POST['count']);

		$v = array(
			'invoice_id' => $_POST['invoice_id'],
			'zp_id' => _zpCompatId($_POST['zp_id']),
			'client_id' => intval($_POST['client_id']),
			'sum' => round($count * $cena, 2),
			'prim' => $_POST['prim']
		);

		$sql = "INSERT INTO `zp_move` (
					`ws_id`,
					`zp_id`,
					`count`,
					`cena`,
					`summa`,
					`type`,
					`client_id`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$v['zp_id'].",
					-".$count.",
					".$cena.",
					".$v['sum'].",
					'sale',
					".$v['client_id'].",
					'".win1251(htmlspecialchars(trim($v['prim'])))."',
					".VIEWER_ID."
				)";
		query($sql);

		_zpAvaiSet($v['zp_id']);

		if(!$v = income_insert($v))
			jsonError();

		jsonSuccess();
		break;
	case 'zp_other':// ������� ��������
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		if(!$count = _num($_POST['count']))
			jsonError();

		switch($_POST['type']) {
			case 'defect': $type = 17; break;
			case 'return': $type = 16; break;
			case 'writeoff': $type = 15; break;
			default: jsonError();
		}

		$zp_id = _zpCompatId($zp_id);
		$count *= -1;
		$prim = _txt($_POST['prim']);

		$sql = "INSERT INTO `zp_move` (
					`ws_id`,
					`zp_id`,
					`count`,
					`type`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$zp_id.",
					".$count.",
					'".$_POST['type']."',
					'".addslashes($prim)."',
					".VIEWER_ID."
				)";
		query($sql);

		_zpAvaiSet($zp_id);

		_history(array(
			'type_id' => $type,
			'zp_id' => $zp_id,
			'v1' => $prim
		));

		jsonSuccess();
		break;
	case 'zp_avai_update':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		$zp_id = _zpCompatId($_POST['zp_id']);
		$send['count'] = _zpAvaiSet($zp_id);
		$send['move'] = utf8(zp_move($zp_id));
		jsonSuccess($send);
		break;
	case 'zp_move_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || $_POST['id'] == 0)
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT * FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `id`=".$id;
		if(!$move = mysql_fetch_assoc(query($sql)))
			jsonError();
		$lastMoveId = query_value("SELECT `id`
								   FROM `zp_move`
								   WHERE `ws_id`=".WS_ID." AND `zp_id`="._zpCompatId($move['zp_id'])."
								   ORDER BY `id` DESC
								   LIMIT 1");
		if($id != $lastMoveId)
			jsonError();
		$sql = "DELETE FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `id`=".$id;
		query($sql);
		jsonSuccess();
		break;
	case 'zp_move_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$zp_id = _zpCompatId($_POST['zp_id']);
		$send['spisok'] = utf8(zp_move($zp_id, intval($_POST['page'])));
		jsonSuccess($send);
		break;
	case 'zp_compat_find':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['name_id']) || $_POST['name_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']) || $_POST['device_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) || $_POST['vendor_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']) || $_POST['model_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
			jsonError();

		$zp_id = intval($_POST['zp_id']);
		$name_id = intval($_POST['name_id']);
		$device_id = intval($_POST['device_id']);
		$vendor_id = intval($_POST['vendor_id']);
		$model_id = intval($_POST['model_id']);
		$color_id = intval($_POST['color_id']);

		$sql = "SELECT `id`,`compat_id`
				FROM `zp_catalog`
				WHERE `id`!=".$zp_id."
				  AND `name_id`=".$name_id."
				  AND `base_device_id`=".$device_id."
				  AND `base_vendor_id`=".$vendor_id."
				  AND `base_model_id`=".$model_id."
				  AND `color_id`=".$color_id."
				LIMIT 1";
		$send = mysql_fetch_assoc(query($sql));
		$send['name'] = utf8(_zpName($name_id).' ��� '._deviceName($device_id, 1)._vendorName($vendor_id)._modelName($model_id));
		jsonSuccess($send);
		break;
	case 'zp_compat_add':
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		if(!$device_id = _num($_POST['device_id']))
			jsonError();
		if(!$vendor_id = _num($_POST['vendor_id']))
			jsonError();
		if(!$model_id = _num($_POST['model_id']))
			jsonError();

		$compat_id = _zpCompatId($zp_id);
		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$compat_id;
		if(!$zp = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(!$zp['compat_id'])
			query("UPDATE `zp_catalog` SET `compat_id`=".$compat_id." WHERE `id`=".$zp_id);

		$sql = "SELECT `id`,`compat_id`
				FROM `zp_catalog`
				WHERE `id`!=".$zp_id."
				  AND `name_id`=".$zp['name_id']."
				  AND `base_device_id`=".$device_id."
				  AND `base_vendor_id`=".$vendor_id."
				  AND `base_model_id`=".$model_id."
				  AND `color_id`=".$zp['color_id']."
				LIMIT 1";
		if($r = mysql_fetch_assoc(query($sql))) {
			if($r['compat_id'] == $compat_id)
				jsonError();
			if(!$r['compat_id']) {
				query("UPDATE `zp_catalog` SET `compat_id`=".$compat_id." WHERE `id`=".$r['id']);
				$r['compat_id'] = $r['id'];
			}
			query("UPDATE `zp_catalog` SET `compat_id`=".$compat_id." WHERE `compat_id`=".$r['compat_id']);
			query("UPDATE `zp_avai` SET `zp_id`=".$compat_id." WHERE `zp_id`=".$r['compat_id']);
			query("UPDATE `zp_zakaz` SET `zp_id`=".$compat_id." WHERE `zp_id`=".$r['compat_id']);
			query("UPDATE `zp_move` SET `zp_id`=".$compat_id." WHERE `zp_id`=".$r['compat_id']);
			_zpAvaiSet($zp_id);
		} else {
			$zp['base_device_id'] = $device_id;
			$zp['base_vendor_id'] = $vendor_id;
			$zp['base_model_id'] = $model_id;
			$zp['compat_id'] = $compat_id;
			zpAddQuery($zp);
		}
		jsonSuccess();
		break;
	case 'zp_compat_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || $_POST['id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		$id = intval($_POST['id']);
		$zp_id = intval($_POST['zp_id']);
		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$id;
		if(!$zp = mysql_fetch_assoc(query($sql)))
			jsonError();
		query("UPDATE `zp_catalog` SET `compat_id`=0 WHERE `id`=".$id);
		if($id == $zp['compat_id']) {
			$sql = "SELECT * FROM `zp_catalog` WHERE `compat_id`=".$id;
			$q = query($sql);
			$r = mysql_fetch_assoc($q);
			if(mysql_num_rows($q) == 1)
				query("UPDATE `zp_catalog` SET `compat_id`=0 WHERE `id`=".$r['id']);
			else
				query("UPDATE `zp_catalog` SET `compat_id`=".$r['id']." WHERE `compat_id`=".$id);
			query("UPDATE `zp_avai` SET `zp_id`=".$r['id']." WHERE `zp_id`=".$id);
			query("UPDATE `zp_zakaz` SET `zp_id`=".$r['id']." WHERE `zp_id`=".$id);
			query("UPDATE `zp_move` SET `zp_id`=".$r['id']." WHERE `zp_id`=".$id);
		}
		$spisok = zp_compat_spisok($zp_id);
		$send['count'] = utf8(zp_compat_count(count($spisok)));
		$send['spisok'] = utf8(implode($spisok));
		jsonSuccess($send);
		break;
	case 'zp_price_get':
		$send['spisok'] = array();
		$val = win1251(htmlspecialchars(trim($_POST['val'])));
		$sql = "SELECT *
				FROM `zp_price`
				WHERE `id`".(!empty($val) ? " AND `name` LIKE '%".$val."%'" : '')."
				ORDER BY `name`
				LIMIT 50";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$send['spisok'][] = array(
				'uid' => $r['id'],
				'title' => utf8(htmlspecialchars_decode($r['name'])),
				'content' => utf8(htmlspecialchars_decode($r['name'])).': <b>'.round($r['cena']).'</b>'
			);
		jsonSuccess($send);
		break;
	case 'zp_price_info':
		if(!$id = _num($_POST['id']))
			jsonError();
		if(!$zp = query_assoc("SELECT * FROM `zp_price` WHERE `id`=".$id))
			jsonError();

		$ass = array(
			'name' => '������������',
			'cena' => '����'
		);
		$sql = "SELECT * FROM `zp_price_upd` WHERE `price_id`=".$zp['id']." ORDER BY `dtime_add` DESC";
		$q = query($sql);
		$upd = '';
		while($r = mysql_fetch_assoc($q)) {
			$diff = '';
			if($r['row'] == 'cena') {
				$res = $r['new'] - $r['old'];
				$diff = '<span>'.($res > 0 ? '+' : '').$res.'</span>';
			}
			$upd .=
				'<tr><td class="row">'.$ass[$r['row']].
					'<td>'.($r['row'] == 'cena' ? '<b>'.$r['old'].'</b>' : $r['old']).
					'<td>'.($r['row'] == 'cena' ? '<b>'.$r['new'].'</b>' : $r['new']).$diff.
					'<td class="dtime">'.FullData($r['dtime_add']);
		}

		if($upd)
			$upd = '<table class="_spisok _money">'.$upd.'</table>';

		$send = array(
			'articul' => $zp['articul'],
			'name' => utf8($zp['name']),
			'cena' => round($zp['cena'], 2),
			'upd' => utf8($upd)
		);

		jsonSuccess($send);
		break;


	case 'salary_bonus_spisok':
		if(!$worker_id = _num($_POST['worker_id']))
			jsonError();
		if(!$year = _num($_POST['year']))
			jsonError();
		if(!$week = _num($_POST['week']))
			jsonError();
		$send['spisok'] = utf8(salary_worker_bonus($worker_id, $year, $week));
		jsonSuccess($send);
		break;
	case 'salary_bonus':
		if(!$worker_id = _num($_POST['worker_id']))
			jsonError();
		if(!$year = _num($_POST['year']))
			jsonError();
		if(!$week = _num($_POST['week']))
			jsonError();

		$bonus = array();
		$bonusSum = 0;
		foreach(explode(',', $_POST['bonus']) as $ex) {
			$r = explode(':', $ex);
			if(!$id = _num($r[0]))
				jsonError();
			$expense = _num($r[1]);
			$sum = intval($r[2]);
			$bonus[$id] = array(
				'expense' => $expense,
				'sum' => $sum
			);
			$bonusSum += $sum;
		}

		$sql = "INSERT INTO `zayav_expense` (
					`ws_id`,
					`worker_id`,
					`sum`,
					`mon`,
					`year`
				) VALUES (
					".WS_ID.",
					".$worker_id.",
					".$bonusSum.",
					".intval(strftime('%m')).",
					".strftime('%Y')."
				)";
		query($sql);
		$insert_id = mysql_insert_id();

		$first_day = date('Y-m-d', ($week - 1) * 7 * 86400 + strtotime('1/1/'.$year) - date('w', strtotime('1/1/'.$year)) * 86400 + 86400);
		$last_day = date('Y-m-d', $week * 7 * 86400 + strtotime('1/1/'.$year) - date('w', strtotime('1/1/'.$year)) * 86400);
		$about = '����� �� ��������, '.__viewerRules($worker_id, 'RULES_MONEY_PROCENT').'%:'.
				 '<br />'.
				 '<a class="bonus-show" val="'.$insert_id.'">'.
					$week.'-� ������ ('.FullData($first_day).' - '.FullData($last_day).')'.
				 '</a>.';
		query("UPDATE `zayav_expense` SET `txt`='".addslashes($about)."' WHERE `id`=".$insert_id);

		// �������� ������ �������
		$arr = array();
		foreach($bonus as $id => $r)
			$arr[] = '('.
				WS_ID.','.
				$insert_id.','.
				$id.','.
				$r['expense'].','.
				$r['sum'].
			')';
		$sql = "INSERT INTO `zayav_expense_bonus` (
					`ws_id`,
					`expense_id`,
					`money_id`,
					`expense`,
					`bonus`
				) VALUES ".implode(',', $arr);
		query($sql);

		jsonSuccess();
		break;
	case 'salary_bonus_show':// �������� ������ �� ��������
		if(!$expense_id = _num($_POST['expense_id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_expense` WHERE `id`=".$expense_id;
		if(!$r = query_assoc($sql))
			jsonError();

		$send['html'] = utf8(salary_worker_bonus_show($r));

		jsonSuccess($send);
		break;
}

function zayav_day_finish_change($zayav_id, $day) {//��������� ����� ����������
	$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
	$z = query_assoc($sql);
	if($day != $z['day_finish'] && $day != '0000-00-00') {
		query("UPDATE `zayav` SET `day_finish`='".$day."' WHERE `id`=".$zayav_id);
		_history(array(
			'type_id' => 52,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => '<table><tr>'.
				'<th>����:'.
					'<td>'.($z['day_finish'] == '0000-00-00' ? '�� ������' : FullData($z['day_finish'], 0, 1, 1)).
					'<td>�'.
					'<td>'.FullData($day, 0, 1, 1).
				'</table>'
		));
	}
}//zayav_day_finish_change()
mb_internal_encoding('UTF-8');
function mb_ucfirst($text) {
	return mb_strtoupper(mb_substr($text, 0, 1)).mb_substr($text, 1);
}



