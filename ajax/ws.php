<?php
require_once('config.php');

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
				   OR `serial`='".$word."')";
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
		xcache_unset(CACHE_PREFIX.'setup_global');
		xcache_unset(CACHE_PREFIX.'workshop_'.WS_ID);
		jsonSuccess($send);
		break;

	case 'client_sel':
		if(!preg_match(REGEXP_WORDFIND, win1251($_POST['val'])))
			$_POST['val'] = '';
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			$_POST['client_id'] = 0;
		$val = win1251($_POST['val']);
		$client_id = intval($_POST['client_id']);
		$sql = "SELECT *
				FROM `client`
				WHERE `ws_id`=".WS_ID."
				  AND `deleted`=0".
					(!empty($val) ? " AND (`fio` LIKE '%".$val."%' OR `telefon` LIKE '%".$val."%')" : '').
					($client_id > 0 ? " AND `id`<=".$client_id : '')."
				ORDER BY `id` DESC
				LIMIT 50";
		$q = query($sql);
		$send['spisok'] = array();
		while($r = mysql_fetch_assoc($q)) {
			$unit = array(
				'uid' => $r['id'],
				'title' => utf8($r['fio'])
			);
			if($r['telefon'])
				$unit['content'] = utf8($r['fio'].'<div class="pole2">'.$r['telefon'].'</div>');
			$send['spisok'][] = $unit;
		}
		jsonSuccess($send);
		break;
	case 'client_add':
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		if(empty($fio))
			jsonError();
		$sql = "INSERT INTO `client` (
					`ws_id`,
					`fio`,
					`telefon`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					'".addslashes($fio)."',
					'".addslashes($telefon)."',
					".VIEWER_ID."
				)";
		query($sql);
		$send = array(
			'uid' => mysql_insert_id(),
			'title' => utf8($fio)
		);
		history_insert(array(
			'type' => 3,
			'client_id' => $send['uid']
		));
		jsonSuccess($send);
		break;
	case 'client_spisok':
		$filter = clientFilter($_POST);
		$send = client_data(1, $filter);
		$send['all'] = utf8(client_count($send['all'], $filter['dolg']));
		$send['spisok'] = utf8($send['spisok']);
		jsonSuccess($send);
		break;
	case 'client_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send = client_data(intval($_POST['page']), clientFilter($_POST));
		$send['spisok'] = utf8($send['spisok']);
		jsonSuccess($send);
		break;
	case 'client_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['join']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['client2']))
			jsonError();
		$client_id = intval($_POST['client_id']);
		$fio = win1251(htmlspecialchars(trim($_POST['fio'])));
		$telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
		$join = intval($_POST['join']);
		$client2 = intval($_POST['client2']);
		if(empty($fio))
			jsonError();
		$sql = "SELECT * FROM `client` WHERE `deleted`=0 AND `id`=".$client_id;
		if(!$client = mysql_fetch_assoc(query($sql)))
			jsonError();
		if($join && $client2 == 0)
			jsonError();
		if($join && $client_id == $client2)
			jsonError();
		query("UPDATE `client`
			   SET `fio`='".addslashes($fio)."',`telefon`='".addslashes($telefon)."'
			   WHERE `id`=".$client_id);
		if($join) {
			query("UPDATE `accrual`	SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
			query("UPDATE `money`	SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
			query("UPDATE `vk_comment` SET `table_id`=".$client_id."  WHERE `table_name`='client' AND `table_id`=".$client2);
			query("UPDATE `zayav`	SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
			query("UPDATE `zp_move`	SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
			query("UPDATE `client`  SET `deleted`=1,`join_id`=".$client_id." WHERE `id`=".$client2);
			clientBalansUpdate($client_id);
			history_insert(array(
				'type' => 11,
				'client_id' => $client_id,
				'value' => _clientLink($client2, 1)
			));
		}

		$changes = '';
		if($client['fio'] != $fio)
			$changes .= '<tr><th>Фио:<td>'.$client['fio'].'<td>»<td>'.$fio;
		if($client['telefon'] != $telefon)
			$changes .= '<tr><th>Тел.:<td>'.$client['telefon'].'<td>»<td>'.$telefon;
		if($changes)
			history_insert(array(
				'type' => 10,
				'client_id' => $client_id,
				'value' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;
	case 'client_zayav_spisok':
		$data = zayav_data(1, zayavfilter($_POST), 10);
		$send['all'] = utf8(zayav_count($data['all'], 0));
		$send['html'] = utf8(zayav_spisok($data));
		jsonSuccess($send);
		break;
	case 'client_zayav_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send['html'] = utf8(zayav_spisok(zayav_data(intval($_POST['page']), zayavfilter($_POST), 10)));
		jsonSuccess($send);
		break;
	case 'client_history_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			jsonError();
		$page = intval($_POST['page']);
		$client_id = intval($_POST['client_id']);
		$send['html'] = utf8(history_spisok($page, array('client_id'=>$client_id), 15));
		jsonSuccess($send);
		break;

	case 'zayav_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client']) || !$_POST['client'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['device']) || !$_POST['device'])
			jsonError();
		$client = intval($_POST['client']);
		$device = intval($_POST['device']);
		$vendor = intval($_POST['vendor']);
		$model = intval($_POST['model']);
		if(!empty($_POST['equip'])) {
			$ids = explode(',', $_POST['equip']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}
		$place = intval($_POST['place']);
		$place_other = $place == 0 ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';
		$imei = win1251(htmlspecialchars(trim($_POST['imei'])));
		$serial = win1251(htmlspecialchars(trim($_POST['serial'])));
		$color = intval($_POST['color']);
		$color_dop = $color ? intval($_POST['color_dop']) : 0;
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));
		$reminder = intval($_POST['reminder']);
		$reminder_txt = win1251(htmlspecialchars(trim($_POST['reminder_txt'])));
		$reminder_day = htmlspecialchars(trim($_POST['reminder_day']));
		if($reminder) {
			if(!$reminder_txt)
				jsonError();
			if(!preg_match(REGEXP_DATE, $reminder_day))
				jsonError();
		}
		$modelName = '';
		if($model > 0) {
			$sql = "select `name` FROM `base_model` WHERE `id`=".$model;
			$r = mysql_fetch_assoc(query($sql));
			$modelName = $r['name'];
		}
		$sql = "SELECT IFNULL(MAX(`nomer`),0)+1 AS `nomer` FROM `zayav` WHERE `ws_id`=".WS_ID." LIMIT 1";
		$r = mysql_fetch_assoc(query($sql));
		$nomer = $r['nomer'];

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

					`device_status`,
					`device_place`,
					`device_place_other`,

					`viewer_id_add`,
					`find`
				) VALUES (
					".WS_ID.",
					".$nomer.",
					".$client.",

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

					1,
					".addslashes($place).",
					'".$place_other."',

					".VIEWER_ID.",
					'".addslashes($modelName.' '.$imei.' '.$serial)."'
				)";
		query($sql);
		$send['id'] = mysql_insert_id();
		xcache_unset(CACHE_PREFIX.'zayav_base_device'.WS_ID);
		xcache_unset(CACHE_PREFIX.'zayav_base_vendor'.WS_ID);
		xcache_unset(CACHE_PREFIX.'zayav_base_model'.WS_ID);

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

		if($reminder) {
			$sql = "INSERT INTO `reminder` (
				`ws_id`,
				`zayav_id`,
				`txt`,
				`day`,
				`history`,
				`viewer_id_add`
			 ) VALUES (
				".WS_ID.",
				".$send['id'].",
				'".$reminder_txt."',
				'".$reminder_day."',
				'".FullDataTime(curTime())." "._viewer(VIEWER_ID, 'name')." добавил напоминание для заявки.',
				".VIEWER_ID."
			)";
			query($sql);
		}
		history_insert(array(
			'type' => 1,
			'client_id' => $client,
			'zayav_id' => $send['id']
		));
		jsonSuccess($send);
		break;
	case 'model_img_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']))
			jsonError();
		$send['img'] = _modelImg(intval($_POST['model_id']), 'small', 80, 80, 'fotoView');
		jsonSuccess($send);
		break;
	case 'equip_check_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']) && $_POST['device_id'] == 0)
			jsonError();
		$device_id = intval($_POST['device_id']);
		$send['spisok'] = utf8(devEquipCheck($device_id));
		jsonSuccess($send);
		break;
	case 'zayav_spisok_load':
		$_POST['find'] = win1251($_POST['find']);
		$data = zayav_data(1, zayavfilter($_POST));
		$send['all'] = utf8(zayav_count($data['all']));
		$send['html'] = utf8(zayav_spisok($data));
		jsonSuccess($send);
		break;
	case 'zayav_next':
		$_POST['find'] = win1251($_POST['find']);
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send['html'] = utf8(zayav_spisok(zayav_data(intval($_POST['page']), zayavfilter($_POST))));
		jsonSuccess($send);
		break;
	case 'zayav_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && !$_POST['zayav_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) && !$_POST['client_id'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['device']) && !$_POST['device'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['model']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_dop']))
			jsonError();
		$zayav_id = intval($_POST['zayav_id']);
		$client_id = intval($_POST['client_id']);
		$device = intval($_POST['device']);
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

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
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
					`find`='".addslashes(_modelName($model).' '.$imei.' '.$serial)."'
				WHERE `id`=".$zayav_id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayav_base_device'.WS_ID);
		xcache_unset(CACHE_PREFIX.'zayav_base_vendor'.WS_ID);
		xcache_unset(CACHE_PREFIX.'zayav_base_model'.WS_ID);

		if($zayav['client_id'] != $client_id) {
			$sql = "UPDATE `accrual`
					SET `client_id`=".$client_id."
					WHERE `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$zayav['client_id'];
			query($sql);
			$sql = "UPDATE `money`
					SET `client_id`=".$client_id."
					WHERE `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$zayav['client_id'];
			query($sql);
			clientBalansUpdate($zayav['client_id']);
			clientBalansUpdate($client_id);
		}

		$changes = '';
		if($zayav['client_id'] != $client_id)
			$changes .= '<tr><th>Клиент:<td>'._clientLink($zayav['client_id']).'<td>»<td>'._clientLink($client_id);
		if(   $zayav['base_device_id'] != $device
		   || $zayav['base_vendor_id'] != $vendor
		   || $zayav['base_model_id'] != $model) {
			$old = _deviceName($zayav['base_device_id'])._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']);
			$new = _deviceName($device)._vendorName($vendor)._modelName($model);
			$changes .= '<tr><th>Устройство:<td>'.$old.'<td>»<td>'.$new;
		}
		if($zayav['imei'] != $imei)
			$changes .= '<tr><th>imei:<td>'.$zayav['imei'].'<td>»<td>'.$imei;
		if($zayav['serial'] != $serial)
			$changes .= '<tr><th>Serial:<td>'.$zayav['serial'].'<td>»<td>'.$serial;
		if($zayav['color_id'] != $color_id || $zayav['color_dop'] != $color_dop)
			$changes .= '<tr><th>Цвет:<td>'._color($zayav['color_id'], $zayav['color_dop']).'<td>»<td>'._color($color_id, $color_dop);
		if($zayav['equip'] != $equip)
			$changes .= '<tr><th>Комплект:<td>'.zayavEquipSpisok($zayav['equip']).'<td>»<td>'.zayavEquipSpisok($equip);
		if($changes)
			history_insert(array(
				'type' => 7,
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;
	case 'zayav_delete':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();
		$zayav_id = intval($_POST['zayav_id']);
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT IFNULL(SUM(`sum`),0) AS `acc`
				FROM `accrual`
				WHERE `ws_id`=".WS_ID."
				  AND `status`=1
				  AND `zayav_id`=".$zayav_id."
				LIMIT 1";
		if(query_value($sql) != 0)
			jsonError();

		$sql = "SELECT IFNULL(SUM(`sum`),0) AS `opl`
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND `deleted`=0
				  AND `sum`>0
				  AND `zayav_id`=".$zayav_id."
				LIMIT 1";
		if(query_value($sql) != 0)
			jsonError();

		$sql = "DELETE FROM `zayav` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id;
		query($sql);

		$sql = "DELETE FROM `reminder` WHERE `ws_id`=".WS_ID." AND `zayav_id`=".$zayav_id;
		query($sql);

		$sql = "DELETE FROM `vk_comment` WHERE `table_name`='zayav' AND `table_id`=".$zayav_id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayav_base_device'.WS_ID);
		xcache_unset(CACHE_PREFIX.'zayav_base_vendor'.WS_ID);
		xcache_unset(CACHE_PREFIX.'zayav_base_model'.WS_ID);

		history_insert(array(
			'type' => 2,
			'value' => $zayav['nomer']
		));

		$send['client_id'] = $zayav['client_id'];
		jsonSuccess($send);
		break;
	case 'zayav_status_place':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_status']) && $_POST['zayav_status'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['dev_status']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['dev_place']))
			jsonError();
		$zayav_id = intval($_POST['zayav_id']);
		$zayav_status = intval($_POST['zayav_status']);
		$dev_status = intval($_POST['dev_status']);
		$dev_place = intval($_POST['dev_place']);
		$place_other = $dev_place == 0 ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';
		if($dev_place == 0 && !$place_other)
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id." LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
				SET `device_status`=".$dev_status.",
					`device_place`=".$dev_place.",
					`device_place_other`='".$place_other."'
					".($zayav['zayav_status'] != $zayav_status ? ",`zayav_status`=".$zayav_status.",`zayav_status_dtime`=CURRENT_TIMESTAMP" : '')."
				WHERE `id`=".$zayav_id;
		query($sql);

		$send['z_status'] = _zayavStatus($zayav_status);
		$send['z_status']['name'] = utf8($send['z_status']['name']);
		$send['z_status']['dtime'] = utf8(FullDataTime($zayav['zayav_status_dtime'], 1));
		$send['dev_place'] = utf8($dev_place > 0 ? _devPlace($dev_place) : $place_other);
		$send['dev_status'] = utf8(_devStatus($dev_status));

		if($zayav['zayav_status'] != $zayav_status) {
			history_insert(array(
				'type' => 4,
				'zayav_id' => $zayav_id,
				'value' => $zayav_status
			));
			$send['z_status']['dtime'] = utf8(FullDataTime(curTime(), 1));
		}
		jsonSuccess($send);
		break;
	case 'zayav_img_update'://Обновление картинки заявки после загрузки новой
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
		jsonError();
		$send['html'] = _zayavImg(intval($_POST['zayav_id']), 'big', 200, 320, 'fotoView');
		jsonSuccess($send);
		break;
	case 'zayav_accrual_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || $_POST['status'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['dev_status']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['remind']))
			jsonError();
		$remind = intval($_POST['remind']);
		$remind_txt = win1251(htmlspecialchars(trim($_POST['remind_txt'])));
		$remind_day = htmlspecialchars(trim($_POST['remind_day']));
		if($remind) {
			if(!$remind_txt)
				jsonError();
			if(!preg_match(REGEXP_DATE, $remind_day))
				jsonError();
		}

		$zayav_id = intval($_POST['zayav_id']);
		$sum = intval($_POST['sum']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));
		$status = intval($_POST['status']);
		$dev_status = intval($_POST['dev_status']);

		$sql = "SELECT *
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND `zayav_status`>0
				  AND `id`=".$zayav_id;
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "INSERT INTO `accrual` (
					`ws_id`,
					`zayav_id`,
					`client_id`,
					`sum`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$zayav_id.",
					".$zayav['client_id'].",
					".$sum.",
					'".addslashes($prim)."',
					".VIEWER_ID."
				)";
		query($sql);

		clientBalansUpdate($zayav['client_id']);
		$send = zayavBalansUpdate($zayav_id);

		$send['html'] = utf8(zayav_accrual_unit(array(
			'id' => mysql_insert_id(),
			'sum' => $sum,
			'prim' => $prim,
		)));

		history_insert(array(
			'type' => 5,
			'zayav_id' => $zayav_id,
			'value' => $sum
		));

		//Обновление статуса заявки, если изменялся
		$sql = "UPDATE `zayav`
				SET `device_status`=".$dev_status."
					".($zayav['zayav_status'] != $status ? ",`zayav_status`=".$status.",`zayav_status_dtime`=CURRENT_TIMESTAMP" : "")."
				WHERE `ws_id`=".WS_ID."
				  AND `id`=".$zayav_id;
		query($sql);
		if($zayav['zayav_status'] != $status) {
			history_insert(array(
				'type' => 4,
				'zayav_id' => $zayav_id,
				'value' => $status
			));
			$send['status'] = _zayavStatus($status);
			$send['status']['name'] = utf8($send['status']['name']);
			$send['status']['dtime'] = utf8(FullDataTime(curTime()));
		}

		//Внесение напоминания, если есть
		if($remind) {
			$sql = "INSERT INTO `reminder` (
				`ws_id`,
				`zayav_id`,
				`txt`,
				`day`,
				`history`,
				`viewer_id_add`
			 ) VALUES (
				".WS_ID.",
				".$zayav_id.",
				'".$remind_txt."',
				'".$remind_day."',
				'".FullDataTime(curTime())." "._viewer(VIEWER_ID, 'name')." добавил напоминание при внесении начисления.',
				".VIEWER_ID."
			)";
			query($sql);
			$send['remind'] = utf8(remind_spisok(remind_data(1, array('zayav'=>$zayav_id))));
		}
		jsonSuccess($send);
		break;
	case 'zayav_accrual_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT * FROM `accrual` WHERE `status`=1 AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$sql = "UPDATE `accrual` SET
					`status`=0,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);
		$send = zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 8,
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'zayav_id' => $r['zayav_id']
		));
		jsonSuccess($send);
		break;
	case 'zayav_accrual_rest':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT *
				FROM `accrual`
				WHERE `ws_id`=".WS_ID."
				  AND `status`=0
				  AND `id`=".$id;
		if(!$acc = mysql_fetch_assoc(query($sql)))
			jsonError();
		$sql = "UPDATE `accrual` SET
					`status`=1,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($acc['client_id']);
		$send = zayavBalansUpdate($acc['zayav_id']);

		history_insert(array(
			'type' => 27,
			'value' => $acc['sum'],
			'value1' => $acc['prim'],
			'zayav_id' => $acc['zayav_id']
		));
		$send['html'] = utf8(zayav_accrual_unit($acc));
		jsonSuccess($send);
		break;
	case 'zayav_oplata_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['dev_place']))
			jsonError();
		$zayav_id = intval($_POST['zayav_id']);
		$sum = intval($_POST['sum']);
		$kassa = intval($_POST['kassa']);
		$dev_place = intval($_POST['dev_place']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));

		$sql = "SELECT *
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND `zayav_status`>0
				  AND `id`=".$zayav_id;
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "INSERT INTO `money` (
					`ws_id`,
					`zayav_id`,
					`client_id`,
					`sum`,
					`kassa`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$zayav_id.",
					".$zayav['client_id'].",
					".$sum.",
					".$kassa.",
					'".addslashes($prim)."',
					".VIEWER_ID."
				)";
		query($sql);

		clientBalansUpdate($zayav['client_id']);
		$send = zayavBalansUpdate($zayav_id);

		$send['html'] = utf8(zayav_oplata_unit(array(
			'id' => mysql_insert_id(),
			'sum' => $sum,
			'prim' => $prim
		)));

		history_insert(array(
			'type' => 6,
			'zayav_id' => $zayav_id,
			'value' => $sum
		));

		//Обновление местонахождения устройства
		$sql = "UPDATE `zayav`
				SET `device_place`=".$dev_place.",
					`device_place_other`=''
				WHERE `ws_id`=".WS_ID."
				  AND `id`=".$zayav_id;
		query($sql);
		jsonSuccess($send);
		break;
	case 'income_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND `deleted`=0
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);
		zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 9,
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'client_id' => $r['client_id'],
			'zayav_id' => $r['zayav_id'],
			'zp_id' => $r['zp_id'],
		));
		jsonSuccess();
		break;
	case 'income_rest':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT *
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND `deleted`=1
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$sql = "UPDATE `money` SET
					`deleted`=0,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);
		$send = zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 19,
			'value' => $r['sum'],
			'value1' => $r['prim'],
			'client_id' => $r['client_id'],
			'zayav_id' => $r['zayav_id'],
			'zp_id' => $r['zp_id']
		));
		jsonSuccess();
		break;
	case 'zayav_zp_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['name_id']) || $_POST['name_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['bu']))
			jsonError();
		$sql = "SELECT
					`base_device_id` AS `device_id`,
					`base_vendor_id` AS `vendor_id`,
					`base_model_id` AS `model_id`
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND `id`=".intval($_POST['zayav_id']);
		if(!$zp = mysql_fetch_assoc(query($sql)))
			jsonError();
		$zp['name_id'] = intval($_POST['name_id']);
		$zp['version'] = win1251(htmlspecialchars(trim($_POST['version'])));
		$zp['bu'] = intval($_POST['bu']);
		$zp['color_id'] = intval($_POST['color_id']);
		$zp['id'] = zpAddQuery($zp);
		$send['html'] = utf8(zayav_zp_unit($zp, _vendorName($zp['vendor_id'])._modelName($zp['model_id'])));
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
		$send['msg'] = utf8('Запчасть <b>'._zpName($zp['name_id']).'</b> для '._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).' добавлена к заказу.');
		jsonSuccess($send);
		break;
	case 'zayav_zp_set':// Установка запчасти из заявки
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!isset($_POST['count']))
			$_POST['count'] = 1;
		if(!preg_match(REGEXP_NUMERIC, $_POST['count']) || $_POST['count'] == 0)
			jsonError();


		$zayav_id = intval($_POST['zayav_id']);
		$zp_id = _zpCompatId($_POST['zp_id']);
		$count = intval($_POST['count']) * -1;
		$prim = isset($_POST['prim']) ? win1251(htmlspecialchars(trim($_POST['prim']))) : '';

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
					".$zp_id.",
					".$count.",
					'set',
					".$zayav_id.",
					'".$prim."',
					".VIEWER_ID."
				)";
		query($sql);

		$count = _zpAvaiSet($zp_id);

		//Удаление из заказа запчасти, привязанной к заявке
		query("DELETE FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zayav_id`=".$zayav_id." AND `zp_id`=".$zp_id);

		$parent_id = 0;
		$sql = "SELECT `id`,`parent_id`
				FROM `vk_comment`
				WHERE `table_name`='zayav'
				  AND `table_id`=".$zayav_id."
				  AND `status`=1
				ORDER BY `id` DESC
				LIMIT 1";
		if($r = mysql_fetch_assoc(query($sql)))
			$parent_id = $r['parent_id'] ? $r['parent_id'] : $r['id'];
		$sql = "SELECT * FROM `zp_catalog` WHERE id=".$zp_id." LIMIT 1";
		$zp = mysql_fetch_assoc(query($sql));
		$model = _vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']);
		$sql = "INSERT INTO `vk_comment` (
					`table_name`,
					`table_id`,
					`txt`,
					`parent_id`,
					`viewer_id_add`
				) VALUES (
					'zayav',
					".$zayav_id.",
					'".addslashes('Установка запчасти: <a href="'.URL.'&p=zp&d=info&id='.$zp_id.'">'._zpName($zp['name_id']).' '.$model.'</a>')."',
					".$parent_id.",
					".VIEWER_ID."
				)";
		query($sql);

		history_insert(array(
			'type' => 13,
			'value' => $count,
			'zp_id' => $zp_id,
			'zayav_id' => $zayav_id
		));

		$zp['avai'] = $count;
		$send['zp_unit'] = utf8(zayav_zp_unit($zp, $model));
		$send['comment'] = utf8(_vkComment('zayav', $zayav_id));
		jsonSuccess($send);
		break;

	case 'zayav_nomer_info'://Получение данных о заявке по номеру
		if(!preg_match(REGEXP_NUMERIC, $_POST['nomer']) || $_POST['nomer'] == 0)
			jsonError();
		$nomer = intval($_POST['nomer']);
		$sql = "SELECT *
				FROM `zayav`
				WHERE `ws_id`=".WS_ID."
				  AND `nomer`=".$nomer."
				  AND `zayav_status`>0
				LIMIT 1";
		if(!$zayav = mysql_fetch_assoc(query($sql)))
			$send['html'] = '<span class="zayavNomerTab">Заявка не найдена</span>';
		else
			$send['html'] = '<table class="zayavNomerTab">'.
				'<tr><td>'._zayavImg($zayav['id'], 'small', 60, 40, 'fotoView').
					'<td><a href="'.URL.'&p=zayav&d=info&id='.$zayav['id'].'">'._deviceName($zayav['base_device_id']).'<br />'.
						   _vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).
						'</a>'.
			'</table>'.
			'<input type="hidden" id="zayavNomerId" value="'.$zayav['id'].'" />';
		$send['html'] = utf8($send['html']);
		jsonSuccess($send);
		break;

	case 'zp_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['name_id']) || $_POST['name_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']) || $_POST['device_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) || $_POST['vendor_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']) || $_POST['model_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['bu']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
			jsonError();

		$zp = array(
			'name_id' => intval($_POST['name_id']),
			'device_id' => intval($_POST['device_id']),
			'vendor_id' => intval($_POST['vendor_id']),
			'model_id' => intval($_POST['model_id']),
			'version' => win1251(htmlspecialchars(trim($_POST['version']))),
			'bu' => intval($_POST['bu']),
			'color_id' => intval($_POST['color_id']),
		);
		zpAddQuery($zp);

		jsonSuccess();
		break;
	case 'zp_spisok_load':
		$_POST['find'] = win1251($_POST['find']);
		$data = zp_data(1, zpfilter($_POST));
		$send['all'] = utf8(zp_count($data));
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zp_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send = zp_data(intval($_POST['page']), zpfilter($_POST));
		$send['spisok'] = utf8($send['spisok']);
		jsonSuccess($send);
		break;
	case 'zp_avai_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['count']) || $_POST['count'] == 0)
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['cena']))
			jsonError();
		$zp_id = _zpCompatId($_POST['zp_id']);
		$count = intval($_POST['count']);
		$cena = round($_POST['cena'], 2);
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
		history_insert(array(
			'type' => 18,
			'zp_id' => $zp_id,
			'value' => $count
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
	case 'zp_img_update'://Обновление картинки заявки после загрузки новой
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']))
			jsonError();
		$send['html'] = _zpImg(intval($_POST['zp_id']), 'big', 160, 280, 'fotoView');
		jsonSuccess($send);
		break;
	case 'zp_edit':
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
		if(!preg_match(REGEXP_BOOL, $_POST['bu']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
			jsonError();

		$zp_id = intval($_POST['zp_id']);
		$name_id = intval($_POST['name_id']);
		$device_id = intval($_POST['device_id']);
		$vendor_id = intval($_POST['vendor_id']);
		$model_id = intval($_POST['model_id']);
		$version = win1251(htmlspecialchars(trim($_POST['version'])));
		$bu = intval($_POST['bu']);
		$color_id = intval($_POST['color_id']);

		$sql = "UPDATE `zp_catalog`
				SET `name_id`=".$name_id.",
					`base_device_id`=".$device_id.",
					`base_vendor_id`=".$vendor_id.",
					`base_model_id`=".$model_id.",
					`version`='".$version."',
					`bu`=".$bu.",
					`color_id`=".$color_id."
				WHERE `id`=".$zp_id;
		query($sql);

		$compat_id = _zpCompatId($zp_id);
		if($compat_id != $zp_id) {
			$sql = "UPDATE `zp_catalog`
					SET `name_id`=".$name_id.",
						`version`='".$version."',
						`bu`=".$bu.",
						`color_id`=".$color_id."
					WHERE `id`=".$compat_id;
			query($sql);
		}


		jsonSuccess();
		break;
	case 'zp_sale':// Продажа запчасти
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['count']) || $_POST['count'] == 0)
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['cena']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			jsonError();

		$zp_id = _zpCompatId($_POST['zp_id']);
		$count = intval($_POST['count']) * -1;
		$cena = round($_POST['cena'], 2);
		$summa = $count * $cena * -1;
		$kassa = intval($_POST['kassa']);
		$client_id = intval($_POST['client_id']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));

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
					".$zp_id.",
					".$count.",
					".$cena.",
					".$summa.",
					'sale',
					".$client_id.",
					'".$prim."',
					".VIEWER_ID."
				)";
		query($sql);

		_zpAvaiSet($zp_id);

		$sql = "INSERT INTO `money` (
					`ws_id`,
					`zp_id`,
					`sum`,
					`kassa`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$zp_id.",
					".$summa.",
					".$kassa.",
					".VIEWER_ID."
				)";
		query($sql);

		history_insert(array(
			'type' => 14,
			'value' => $summa,
			'zp_id' => $zp_id,
			'client_id' => $client_id
		));

		jsonSuccess();
		break;
	case 'zp_other':// Продажа запчасти
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['count']) || $_POST['count'] == 0)
			jsonError();
		switch($_POST['type']) {
			case 'defect': $type = 17; break;
			case 'return': $type = 16; break;
			case 'writeoff': $type = 15; break;
			default: jsonError();
		}

		$zp_id = _zpCompatId($_POST['zp_id']);
		$count = intval($_POST['count']) * -1;
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));

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
					'".$prim."',
					".VIEWER_ID."
				)";
		query($sql);

		_zpAvaiSet($zp_id);

		history_insert(array(
			'type' => $type,
			'zp_id' => $zp_id
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
		if(!preg_match(REGEXP_BOOL, $_POST['bu']))
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
		$bu = intval($_POST['bu']);
		$name_id = intval($_POST['name_id']);
		$device_id = intval($_POST['device_id']);
		$vendor_id = intval($_POST['vendor_id']);
		$model_id = intval($_POST['model_id']);
		$color_id = intval($_POST['color_id']);

		$sql = "SELECT `id`,`compat_id`
				FROM `zp_catalog`
				WHERE `id`!=".$zp_id."
				  AND `bu`=".$bu."
				  AND `name_id`=".$name_id."
				  AND `base_device_id`=".$device_id."
				  AND `base_vendor_id`=".$vendor_id."
				  AND `base_model_id`=".$model_id."
				  AND `color_id`=".$color_id."
				LIMIT 1";
		$send = mysql_fetch_assoc(query($sql));
		$send['name'] = utf8(_zpName($name_id).' для '._deviceName($device_id, 1)._vendorName($vendor_id)._modelName($model_id));
		jsonSuccess($send);
		break;
	case 'zp_compat_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']) || $_POST['device_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) || $_POST['vendor_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']) || $_POST['model_id'] == 0)
			jsonError();

		$zp_id = intval($_POST['zp_id']);
		$compat_id = _zpCompatId($zp_id);
		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$compat_id;
		if(!$zp = mysql_fetch_assoc(query($sql)))
			jsonError();

		$device_id = intval($_POST['device_id']);
		$vendor_id = intval($_POST['vendor_id']);
		$model_id = intval($_POST['model_id']);

		$sql = "SELECT `id`,`compat_id`
				FROM `zp_catalog`
				WHERE `id`!=".$zp_id."
				  AND `bu`=".$zp['bu']."
				  AND `name_id`=".$zp['name_id']."
				  AND `base_device_id`=".$device_id."
				  AND `base_vendor_id`=".$vendor_id."
				  AND `base_model_id`=".$model_id."
				  AND `color_id`=".$zp['color_id']."
				LIMIT 1";

		if(!$zp['compat_id'])
			query("UPDATE `zp_catalog` SET `compat_id`=".$compat_id." WHERE `id`=".$zp_id);

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
			$zp['device_id'] = $device_id;
			$zp['vendor_id'] = $vendor_id;
			$zp['model_id'] = $model_id;
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

	case 'history_spisok':
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			$_POST['worker'] = 0;
		if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
			$_POST['action'] = 0;
		$filter = array();
		if($_POST['worker'])
			$filter['worker'] = intval($_POST['worker']);
		if($_POST['action'])
			$filter['action'] = intval($_POST['action']);
		$send['html'] = utf8(history_spisok(1, $filter));
		jsonSuccess($send);
		break;
	case 'history_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			$_POST['worker'] = 0;
		if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
			$_POST['action'] = 0;
		$page = intval($_POST['page']);
		$filter = array();
		if($_POST['worker'])
			$filter['worker'] = intval($_POST['worker']);
		if($_POST['action'])
			$filter['action'] = intval($_POST['action']);
		$send['html'] = utf8(history_spisok($page, $filter));
		jsonSuccess($send);
		break;

	case 'remind_spisok':
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['private']))
			jsonError();
		$filter = array(
			'status' => intval($_POST['status']),
			'private' => intval($_POST['private'])
		);
		$data = remind_data(1, $filter);
		$send['html'] = utf8(!empty($data) ? remind_spisok($data) : '<div class="_empty">Заданий не найдено.</div>');
		jsonSuccess($send);
		break;
	case 'report_remind_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['private']))
			jsonError();
		if(empty($_POST['txt']))
			jsonError();
		$client_id = intval($_POST['client_id']);
		$zayav_id = intval($_POST['zayav_id']);
		$txt = win1251(htmlspecialchars(trim($_POST['txt'])));
		$private = intval($_POST['private']);
		$sql = "INSERT INTO `reminder` (
					`ws_id`,
					`client_id`,
					`zayav_id`,
					`txt`,
					`day`,
					`private`,
					`history`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$client_id.",
					".$zayav_id.",
					'".$txt."',
					'".$_POST['day']."',
					".$private.",
					'".FullDataTime(curTime())." "._viewer(VIEWER_ID, 'name')." создал задание.',
					".VIEWER_ID."
				)";
		query($sql);
		history_insert(array(
			'type' => 20,
			'client_id' => $client_id,
			'zayav_id' => $zayav_id
		));
		$filter = array();
		if(isset($_POST['from_zayav']) && $zayav_id)
			$filter['zayav'] = $zayav_id;
		if(isset($_POST['from_client']) && $client_id)
			$filter['client'] = $client_id;
		$send['html'] = utf8(remind_spisok(remind_data(1, $filter)));
		xcache_unset(CACHE_PREFIX.'remind_active'.WS_ID);
		jsonSuccess($send);
		break;
	case 'remind_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['private']))
			jsonError();
		$filter = array(
			'status' => intval($_POST['status']),
			'private' => intval($_POST['private'])
		);
		$send['html'] = utf8(remind_spisok(remind_data(intval($_POST['page']), $filter)));
		jsonSuccess($send);
		break;
	case 'report_remind_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$sql = "SELECT
					`client_id`,
					`zayav_id`,
					`txt`,
					`day`,
					`dtime_add` AS `dtime`,
					`viewer_id_add`
				FROM `reminder` WHERE `id`=".intval($_POST['id'])." AND `status`=1";
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$r['viewer'] = utf8(_viewer($r['viewer_id_add'], 'link'));
		if($r['client_id'] > 0)
			$r['client'] = utf8(_clientLink($r['client_id']));
		if($r['zayav_id'] > 0)
			$r['zayav'] = utf8(_zayavNomerLink($r['zayav_id'], 1));
		$r['txt'] = utf8($r['txt']);
		$r['dtime'] = utf8(FullDataTime($r['dtime']));
		unset($r['client_id']);
		unset($r['zayav_id']);
		unset($r['viewer_id_add']);
		jsonSuccess($r);
		break;
	case 'report_remind_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['from_zayav']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['from_client']))
			jsonError();
		$history = win1251(htmlspecialchars(trim($_POST['history'])));
		$action = '';
		switch($_POST['action']) {
			case 1: $action = " указал новую дату: ".FullData($_POST['day']).". Причина: ".$history; break;
			case 2: $action = " выполнил задание.".($history ? " (".$history.")" : ''); break;
			case 3: $action = " отменил задание. Причина: ".$history; break;
		}
		$sql = "UPDATE `reminder`
				SET `day`='".$_POST['day']."',
					`status`=".$_POST['status'].",
					`history`=CONCAT(`history`,'<BR>".FullDataTime(curTime())." "._viewer(VIEWER_ID, 'name').$action."')
				WHERE `id`=".intval($_POST['id']);
		query($sql);
		$filter = array();
		if($_POST['from_zayav'])
			$filter['zayav'] = $_POST['from_zayav'];
		if($_POST['from_client'])
			$filter['client'] = $_POST['from_client'];
		$data = remind_data(1, $filter);
		$html = remind_spisok($data);
		if(empty($data) && !isset($filter['zayav']))
			$html = '<div class="_empty">Заданий нет.</div>';
		$send['html'] = utf8($html);
		xcache_unset(CACHE_PREFIX.'remind_active'.WS_ID);
		jsonSuccess($send);
		break;

	case 'income_spisok':
		$data = income_spisok($_POST);
		$send['html'] = utf8($data['spisok']);
		$send['path'] = utf8(income_path($data['filter']['period']));
		jsonSuccess($send);
		break;
	case 'income_next':
		$data = income_spisok($_POST);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'income_add':
		if(empty($_POST['about']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
			jsonError();
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$sum = intval($_POST['sum']);
		$kassa = intval($_POST['kassa']);
		$sql = "INSERT INTO `money` (
					`ws_id`,
					`sum`,
					`prim`,
					`kassa`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$sum.",
					'".addslashes($about)."',
					".$kassa.",
					".VIEWER_ID."
				)";
		query($sql);
		history_insert(array(
			'type' => 6,
			'value' => $sum,
			'value1' => $about
		));
		jsonSuccess();
		break;

	case 'expense_spisok':
		if(!preg_match(REGEXP_YEAR, $_POST['year']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['month']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['category']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		$year = intval($_POST['year']);
		$mon = ($_POST['month'] < 10 ? 0 : '').$_POST['month'];
		$cat = intval($_POST['category']);
		$worker = intval($_POST['worker']);
		$send['mon'] = utf8(expenseMonthSum($year, intval($mon), $cat, $worker));
		$send['html'] = utf8(expense_spisok(1, $year.'-'.$mon, $cat, $worker));
		jsonSuccess($send);
		break;
	case 'expense_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		if(!preg_match(REGEXP_YEAR, $_POST['year']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['month']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['category']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		$year = intval($_POST['year']);
		$mon = ($_POST['month'] < 10 ? 0 : '').$_POST['month'];
		$cat = intval($_POST['category']);
		$worker = intval($_POST['worker']);
		$send['html'] = utf8(expense_spisok(intval($_POST['page']), $year.'-'.$mon, $cat, $worker));
		jsonSuccess($send);
		break;
	case 'expense_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['category']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
			jsonError();
		$category = intval($_POST['category']);
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		if($category == 0 && empty($about))
			jsonError();
		$sum = intval($_POST['sum']) * -1;
		$kassa = intval($_POST['kassa']);
		$worker = intval($_POST['worker']);
		$sql = "INSERT INTO `money`
					(`ws_id`,`sum`,   `prim`,   `kassa`,  `rashod_category`,  `worker_id`,`viewer_id_add`)
				VALUES
					(".WS_ID.",".$sum.",'".$about."',".$kassa.",".$category.",".$worker.",".VIEWER_ID.")";
		query($sql);
		history_insert(array(
			'type' => 21,
			'value' => abs($sum),
			'value1' => $about
		));
		jsonSuccess();
		break;
	case 'expense_del':
		if(!VIEWER_ADMIN)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "UPDATE `money` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);
		$sql = "SELECT * FROM `money` WHERE `id`=".$id;
		$r = mysql_fetch_assoc(query($sql));
		history_insert(array(
			'type' => 22,
			'value' => abs($r['sum'])
		));
		jsonSuccess();
		break;
	case 'expense_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$sql = "SELECT
					`sum` * -1 AS `sum`,
					`prim` AS `about`,
					`kassa`,
					`worker_id`,
					`rashod_category` AS `category`
				FROM `money`
				WHERE `deleted`=0
				  AND `id`=".intval($_POST['id'])."
				LIMIT 1";
		if(!$send = mysql_fetch_assoc(query($sql)))
			jsonError();
		$send['about'] = utf8($send['about']);
		jsonSuccess($send);
		break;
	case 'expense_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
			jsonError();
		$id = intval($_POST['id']);
		$category = intval($_POST['category']);
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		if($category == 0 && empty($about))
			jsonError();
		$sum = intval($_POST['sum']) * -1;
		$kassa = intval($_POST['kassa']);
		$worker = intval($_POST['worker']);
		$sql = "UPDATE `money` SET
					`sum`=".$sum.",
					`prim`='".$about."',
					`kassa`=".$kassa.",
					`rashod_category`=".$category.",
					`worker_id`=".$worker."
				WHERE `id`=".$id;
		query($sql);
		history_insert(array(
			'type' => 23,
			'value' => abs($sum),
			'value1' => $about
		));
		jsonSuccess();
		break;
	case 'report_kassa_set':
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		$set_sum = intval($_POST['sum']);

		$sql = "SELECT SUM(`sum`) AS `sum` FROM `kassa` WHERE `ws_id`=".WS_ID." AND `status`=1 LIMIT 1";
		$r = mysql_fetch_assoc(query($sql));
		$kassa_sum = $r['sum'];

		$sql = "SELECT SUM(`sum`) AS `sum` FROM `money` WHERE `ws_id`=".WS_ID." AND `deleted`=0 AND `kassa`=1 LIMIT 1";
		$r = mysql_fetch_assoc(query($sql));
		$money_sum = $r['sum'];

		$kassa_start = $set_sum - $kassa_sum - $money_sum;
		$sql = "UPDATE `workshop` SET `kassa_start`=".$kassa_start." WHERE `id`=".WS_ID;
		query($sql);
		history_insert(array(
			'type' => 24,
			'value' => $set_sum
		));
		xcache_unset(CACHE_PREFIX.'workshop_'.WS_ID);
		jsonSuccess();
		break;
	case 'report_kassa_load':
		if(!preg_match(REGEXP_BOOL, $_POST['del_show']) || !VIEWER_ADMIN)
			$_POST['del_show'] = 0;
		$send['html'] = utf8(report_kassa_spisok(1, intval($_POST['del_show'])));
		jsonSuccess($send);
		break;
	case 'report_kassa_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		$send['html'] = utf8(report_kassa_spisok(intval($_POST['page'])));
		jsonSuccess($send);
		break;
	case 'report_kassa_action':
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['down']))
			jsonError();
		$sum = intval($_POST['sum']) * (intval($_POST['down']) == 1 ? -1 : 1);
		$txt = win1251(htmlspecialchars(trim($_POST['txt'])));
		$sql = "INSERT INTO `kassa` (
					`ws_id`,`sum`,`txt`,`viewer_id_add`
				) VALUES (
					".WS_ID.",".$sum.",'".$txt."',".VIEWER_ID."
				)";
		query($sql);
		jsonSuccess();
		break;
	case 'report_kassa_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "UPDATE `kassa` SET
					`status`=0,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);
		$sql = "SELECT * FROM `kassa` WHERE `id`=".$id;
		$r = mysql_fetch_assoc(query($sql));
		history_insert(array(
			'type' => 25,
			'value' => $r['sum'],
			'value1' => $r['txt']
		));
		$send['sum'] = kassa_sum();
		jsonSuccess($send);
		break;
	case 'report_kassa_rest':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "UPDATE `kassa` SET
					`status`=1,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);
		$sql = "SELECT * FROM `kassa` WHERE `id`=".$id;
		$r = mysql_fetch_assoc(query($sql));
		history_insert(array(
			'type' => 26,
			'value' => $r['sum'],
			'value1' => $r['txt']
		));
		$send['sum'] = kassa_sum();
		jsonSuccess($send);
		break;

	case 'setup_org_name_save':
		if(!VIEWER_ADMIN)
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		query("UPDATE `workshop` SET `org_name`='".$name."' WHERE `id`=".WS_ID);
		jsonSuccess();
		break;
	case 'setup_devs_set':
		if(!VIEWER_ADMIN)
			jsonError();
		$ex = explode(',', $_POST['devs']);
		foreach($ex as $id)
			if(!preg_match(REGEXP_NUMERIC, $id))
				jsonError();
		query("UPDATE `workshop` SET `devs`='".$_POST['devs']."' WHERE `id`=".WS_ID);
		jsonSuccess();
		break;
	case 'setup_ws_del':
		if(!VIEWER_ADMIN)
			jsonError();
		$sql = "SELECT `viewer_id` FROM `vk_user` WHERE `ws_id`=".WS_ID;
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			xcache_unset(CACHE_PREFIX.'viewer_'.$r['viewer_id']);
		query("UPDATE `workshop` SET `status`=0,`dtime_del`=CURRENT_TIMESTAMP WHERE `id`=".WS_ID);
		query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".WS_ID);
		_cacheClear();
		jsonSuccess();
		break;
	case 'setup_worker_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT * FROM `vk_user` WHERE `viewer_id`=".$id;
		if($r = mysql_fetch_assoc(query($sql))) {
			if($r['ws_id'] == WS_ID)
				jsonError('Этот пользователь уже является</br >сотрудником этой мастерской.');
			if($r['ws_id'] > 0)
				jsonError('Этот пользователь уже является</br >сотрудником другой мастерской.');
		}
		_vkUserUpdate($id);
		query("UPDATE `vk_user` SET `ws_id`=".WS_ID." WHERE `viewer_id`=".$id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$id);
		$send['html'] = utf8(setup_workers_spisok());
		jsonSuccess($send);
		break;
	case 'setup_worker_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		$viewer_id = intval($_POST['viewer_id']);
		query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		$send['html'] = utf8(setup_workers_spisok());
		jsonSuccess($send);
		break;
	case 'setup_worker_admin_set':
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		$viewer_id = intval($_POST['viewer_id']);
		query("UPDATE `vk_user` SET `admin`=1 WHERE `ws_id`=".WS_ID." AND`viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		jsonSuccess();
		break;
	case 'setup_worker_admin_cancel':
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		$viewer_id = intval($_POST['viewer_id']);
		if(WS_ADMIN == $viewer_id)
			jsonError();
		query("UPDATE `vk_user` SET `admin`=0 WHERE `ws_id`=".WS_ID." AND`viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		jsonSuccess();
		break;

	case 'setup_expense_category_add':
		if(empty($_POST['name']))
			jsonError();
		$sql = "INSERT INTO `setup_rashod_category` (
					`name`,
					`viewer_id_add`
				) VALUES (
					'".win1251(htmlspecialchars(trim($_POST['name'])))."',
					".VIEWER_ID."
				)";
		query($sql);
		$send['id'] = mysql_insert_id();
		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'rashod');
		jsonSuccess($send);
		break;
	case 'tooltip_zayav_info_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT * FROM `zayav` WHERE `id`=".$id;
		$zayav = mysql_fetch_assoc(query($sql));

		$sql = "SELECT `fio` FROM `client` WHERE `deleted`=0 AND `id`=".$zayav['client_id'];
		$r = mysql_fetch_assoc(query($sql));
		$client = $r['fio'];

		$html = '<table><tr>'.
					'<td><div class="image">'._zayavImg($id).'</div></td>'.
					'<td class="inf">'.
						_deviceName($zayav['base_device_id']).'<br />'.
						'<b>'._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).'</b><br /><br />'.
						'<span style="color:#000">Клиент:</span> '.$client.
					'</td>'.
				'</tr></table>';

		$send['html'] = utf8($html);
		jsonSuccess($send);
		break;
}

jsonError();