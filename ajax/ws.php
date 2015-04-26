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

	case 'client_sel':
		$send['spisok'] = array();
		if(!empty($_POST['val']) && !preg_match(REGEXP_WORDFIND, win1251($_POST['val'])))
			jsonSuccess($send);

		$val = win1251($_POST['val']);
		$client_id = _num($_POST['client_id']);

		$sql = "SELECT *
				FROM `client`
				WHERE `ws_id`=".WS_ID."
				  AND !`deleted`".
					(!empty($val) ?
						" AND (`org_name` LIKE '%".$val."%'
							OR `org_telefon` LIKE '%".$val."%'
							OR `org_adres` LIKE '%".$val."%'
							OR `org_inn` LIKE '%".$val."%'
							OR `org_kpp` LIKE '%".$val."%'
							OR `fio1` LIKE '%".$val."%'
							OR `fio2` LIKE '%".$val."%'
							OR `fio3` LIKE '%".$val."%'
							OR `telefon1` LIKE '%".$val."%'
							OR `telefon2` LIKE '%".$val."%'
							OR `telefon3` LIKE '%".$val."%'
							  )"
					: '').
					($client_id > 0 ? " AND `id`<=".$client_id : '')."
				ORDER BY `id` DESC
				LIMIT 50";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q)) {
			$name = _clientName($r);
			$telefon = _clientTelefon($r);
			$unit = array(
				'uid' => $r['id'],
				'title' => utf8(htmlspecialchars_decode($name))
			);
			if($telefon)
				$unit['content'] = utf8($name.'<span>'.$telefon.'</span>');
			$send['spisok'][] = $unit;
		}
		jsonSuccess($send);
		break;
	case 'client_add':
		if(!$category_id = _num($_POST['category_id']))
			jsonError();

		$fio1 = _txt($_POST['fio1']);
		$fio2 = _txt($_POST['fio2']);
		$fio3 = _txt($_POST['fio3']);
		$telefon1 = _txt($_POST['telefon1']);
		$telefon2 = _txt($_POST['telefon2']);
		$telefon3 = _txt($_POST['telefon3']);
		$post1 = _txt($_POST['post1']);
		$post2 = _txt($_POST['post2']);
		$post3 = _txt($_POST['post3']);
		$org_name = _txt($_POST['org_name']);
		$org_telefon = _txt($_POST['org_telefon']);
		$org_adres = _txt($_POST['org_adres']);
		$org_inn = _txt($_POST['org_inn']);
		$org_kpp = _txt($_POST['org_kpp']);
		$info_dop = _txt($_POST['info_dop']);

		if($category_id == 1 && empty($fio1))//Для частного лица обязательно указывается ФИО
			jsonError();
		if($category_id > 1 && empty($org_name))//Для ИП и ООО обязательно указывается Название организации
			jsonError();

		$sql = "INSERT INTO `client` (
					`ws_id`,
					`category_id`,
					`org_name`,
					`org_telefon`,
					`org_adres`,
					`org_inn`,
					`org_kpp`,
					`info_dop`,
					`fio1`,
					`fio2`,
					`fio3`,
					`telefon1`,
					`telefon2`,
					`telefon3`,
					`post1`,
					`post2`,
					`post3`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$category_id.",
					'".addslashes($org_name)."',
					'".addslashes($org_telefon)."',
					'".addslashes($org_adres)."',
					'".addslashes($org_inn)."',
					'".addslashes($org_kpp)."',
					'".addslashes($info_dop)."',
					'".addslashes($fio1)."',
					'".addslashes($fio2)."',
					'".addslashes($fio3)."',
					'".addslashes($telefon1)."',
					'".addslashes($telefon2)."',
					'".addslashes($telefon3)."',
					'".addslashes($post1)."',
					'".addslashes($post2)."',
					'".addslashes($post3)."',
					".VIEWER_ID."
				)";
		query($sql);

		$name = $category_id == 1 ? $fio1 : $org_name;
		$telefon = $category_id == 1 ? $telefon1 : $org_telefon;
		$send = array(
			'uid' => mysql_insert_id(),
			'title' => utf8($name),
			'content' => utf8($name.'<span>'.$telefon.'</span>')
		);
		history_insert(array(
			'type' => 3,
			'client_id' => $send['uid']
		));
		jsonSuccess($send);
		break;
	case 'client_spisok':
		$_POST['find'] = win1251($_POST['find']);
		$data = client_data($_POST);
		if($data['filter']['page'] == 1)
			$send['all'] = utf8($data['result']);
		$send['spisok'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'client_edit':
		if(!$client_id = _num($_POST['id']))
			jsonError();
		if(!$category_id = _num($_POST['category_id']))
			jsonError();

		define('ORG', $category_id > 1);

		$fio1 = _txt($_POST['fio1']);
		$fio2 = ORG ? _txt($_POST['fio2']) : '';
		$fio3 = ORG ? _txt($_POST['fio3']) : '';
		$telefon1 = _txt($_POST['telefon1']);
		$telefon2 = ORG ? _txt($_POST['telefon2']) : '';
		$telefon3 = ORG ? _txt($_POST['telefon3']) : '';
		$post1 = ORG ? _txt($_POST['post1']) : '';
		$post2 = ORG ? _txt($_POST['post2']) : '';
		$post3 = ORG ? _txt($_POST['post3']) : '';
		$org_name = ORG ? _txt($_POST['org_name']) : '';
		$org_telefon = ORG ? _txt($_POST['org_telefon']) : '';
		$org_adres = ORG ? _txt($_POST['org_adres']) : '';
		$org_inn = ORG ? _txt($_POST['org_inn']) : '';
		$org_kpp = ORG ? _txt($_POST['org_kpp']) : '';
		$info_dop = _txt($_POST['info_dop']);
		$join = _bool($_POST['join']);
		$client2 = _num($_POST['client2']);

		if(!ORG && empty($fio1))//Для частного лица обязательно указывается ФИО
			jsonError();
		if(ORG && empty($org_name))//Для ИП и ООО обязательно указывается Название организации
			jsonError();

		$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$client_id;
		if(!$client = query_assoc($sql))
			jsonError();

		if($join) {
			if(!$client2)
				jsonError();
			if(!query_value("SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$client2))
				jsonError();
			if($client_id == $client2)
				jsonError();
		}

		$sql = "UPDATE `client`
				SET `category_id`=".$category_id.",
					`org_name`='".addslashes($org_name)."',
					`org_telefon`='".addslashes($org_telefon)."',
					`org_adres`='".addslashes($org_adres)."',
					`org_inn`='".addslashes($org_inn)."',
					`org_kpp`='".addslashes($org_kpp)."',
					`info_dop`='".addslashes($info_dop)."',
					`fio1`='".addslashes($fio1)."',
					`fio2`='".addslashes($fio2)."',
					`fio3`='".addslashes($fio3)."',
					`telefon1`='".addslashes($telefon1)."',
					`telefon2`='".addslashes($telefon2)."',
					`telefon3`='".addslashes($telefon3)."',
					`post1`='".addslashes($post1)."',
					`post2`='".addslashes($post2)."',
					`post3`='".addslashes($post3)."'
			   WHERE `id`=".$client_id;
		query($sql);

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
		if($client['category_id'] != $category_id)
			$changes .= '<tr><th>Категория:<td>'._clientCategory($client['category_id']).'<td>»<td>'._clientCategory($category_id);
		if($client['org_name'] != $org_name)
			$changes .= '<tr><th>Название организации:<td>'.$client['org_name'].'<td>»<td>'.$org_name;
		if($changes)
			history_insert(array(
				'type' => 10,
				'client_id' => $client_id,
				'value' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;
	case 'client_zayav_spisok':
		$_POST['limit'] = 10;
		$data = zayav_spisok($_POST);
		if($data['filter']['page'] == 1)
			$send['all'] = utf8($data['result']);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;

	case 'zayav_add':
		if(!$client_id = _isnum($_POST['client_id']))
			jsonError();
		if(!$device = _isnum($_POST['device']))
			jsonError();
		if(!preg_match(REGEXP_DATE, $_POST['day_finish']))
			jsonError();

		$vendor = _isnum($_POST['vendor']);
		$model = _isnum($_POST['model']);
		if(!empty($_POST['equip'])) {
			$ids = explode(',', $_POST['equip']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}
		$place = intval($_POST['place']);
		$place_other = !$place ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';
		$imei = win1251(htmlspecialchars(trim($_POST['imei'])));
		$serial = win1251(htmlspecialchars(trim($_POST['serial'])));
		$color = intval($_POST['color']);
		$color_dop = $color ? intval($_POST['color_dop']) : 0;
		$comm = win1251(htmlspecialchars(trim($_POST['comm'])));
		$pre_cost = _isnum($_POST['pre_cost']);
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
					`device_place_other`,

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

					".addslashes($place).",
					'".$place_other."',

					'".rand(10, 99).(time() + rand(10000, 99999))."',
					".$pre_cost.",
					'".$day_finish."',
					".VIEWER_ID.",
					'".addslashes($modelName.' '.$imei.' '.$serial)."'
				)";
		query($sql);
		$send['id'] = mysql_insert_id();

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

		history_insert(array(
			'type' => 1,
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
		if(!$device_id = _isnum($_POST['device_id']))
			jsonError();
		$send['spisok'] = utf8(devEquipCheck($device_id));
		jsonSuccess($send);
		break;
	case 'zayav_day_finish':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();

		$day = $_POST['day'];
		$zayav_spisok = _isbool($_POST['zayav_spisok']);

		$send['html'] = utf8(_zayavFinishCalendar($day, '', $zayav_spisok));
		jsonSuccess($send);
		break;
	case 'zayav_day_finish_next':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();

		$day = $_POST['day'];
		$zayav_spisok = _isbool($_POST['zayav_spisok']);

		$send['html'] = utf8(_zayavFinishCalendar($day, $_POST['mon'], $zayav_spisok));
		jsonSuccess($send);
		break;
	case 'zayav_day_finish_save':
		if(!preg_match(REGEXP_DATE, $_POST['day']))
			jsonError();

		$day = $_POST['day'];
		$zayav_id = _isnum($_POST['zayav_id']);
		$save = _isbool($_POST['save']);

		if($zayav_id && $save) {
			$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
			if(!$z = query_assoc($sql))
				jsonError();
			zayav_day_finish_change($zayav_id, $day);
		}

		$send['data'] = utf8($day == '0000-00-00' ? 'не указан' : FullData($day, 1, 0, 1));
		jsonSuccess($send);
		break;
	case 'zayav_spisok':
		$_POST['find'] = win1251($_POST['find']);
		$data = zayav_spisok($_POST);
		if($data['filter']['page'] == 1) {
			setcookie('zback_spisok_page', 1, time() + 3600, '/');
			$send['all'] = utf8($data['result']);
		}
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zayav_edit':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		if(!$client_id = _isnum($_POST['client_id']))
			jsonError();
		if(!$device = _isnum($_POST['device']))
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
		$pre_cost = _isnum($_POST['pre_cost']);

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
					`find`='".addslashes(_modelName($model).' '.$imei.' '.$serial)."'
				WHERE `id`=".$zayav_id;
		query($sql);

		if($z['client_id'] != $client_id) {
			$sql = "UPDATE `accrual`
					SET `client_id`=".$client_id."
					WHERE `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql);
			$sql = "UPDATE `money`
					SET `client_id`=".$client_id."
					WHERE `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql);
			clientBalansUpdate($z['client_id']);
			clientBalansUpdate($client_id);
		}

		$changes = '';
		if($z['client_id'] != $client_id)
			$changes .= '<tr><th>Клиент:<td>'._clientLink($z['client_id']).'<td>»<td>'._clientLink($client_id);
		if(   $z['base_device_id'] != $device
		   || $z['base_vendor_id'] != $vendor
		   || $z['base_model_id'] != $model) {
			$old = _deviceName($z['base_device_id'])._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']);
			$new = _deviceName($device)._vendorName($vendor)._modelName($model);
			$changes .= '<tr><th>Устройство:<td>'.$old.'<td>»<td>'.$new;
		}
		if($z['imei'] != $imei)
			$changes .= '<tr><th>imei:<td>'.$z['imei'].'<td>»<td>'.$imei;
		if($z['serial'] != $serial)
			$changes .= '<tr><th>Serial:<td>'.$z['serial'].'<td>»<td>'.$serial;
		if($z['color_id'] != $color_id || $z['color_dop'] != $color_dop)
			$changes .= '<tr><th>Цвет:<td>'._color($z['color_id'], $z['color_dop']).'<td>»<td>'._color($color_id, $color_dop);
		if($z['equip'] != $equip)
			$changes .= '<tr><th>Комплект:<td>'.zayavEquipSpisok($z['equip']).'<td>»<td>'.zayavEquipSpisok($equip);
		if($z['pre_cost'] != $pre_cost)
			$changes .= '<tr><th>Стоимость ремонта:<td>'.($z['pre_cost'] ? $z['pre_cost'] : '').'<td>»<td>'.($pre_cost ? $pre_cost : '');
		if($changes)
			history_insert(array(
				'type' => 7,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
		break;
	case 'zayav_delete':
		if(empty($_POST['zayav_id']) || !preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			jsonError();

		$zayav_id = intval($_POST['zayav_id']);
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT COUNT(`sum`)
				FROM `accrual`
				WHERE `ws_id`=".WS_ID."
				  AND !`deleted`
				  AND `zayav_id`=".$zayav_id;
		if(query_value($sql))
			jsonError();

		$sql = "SELECT COUNT(`sum`)
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND !`deleted`
				  AND `sum`>0
				  AND `zayav_id`=".$zayav_id;
		if(query_value($sql))
			jsonError();

		query("UPDATE `zayav` SET `deleted`=1 WHERE `id`=".$zayav_id);
		query("UPDATE `remind` SET `status`=0 WHERE `status`=1 AND `zayav_id`=".$zayav_id, GLOBAL_MYSQL_CONNECT);

		history_insert(array(
			'type' => 2,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id
		));

		$send['client_id'] = $z['client_id'];
		jsonSuccess($send);
		break;
	case 'zayav_status_place':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		if(!$zayav_status = _isnum($_POST['status']))
			jsonError();

		if(!preg_match(REGEXP_NUMERIC, $_POST['place']))
			jsonError();
		$dev_place = _num($_POST['place']);
		$place_other = $dev_place == 0 ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';
		if($dev_place == 0 && !$place_other)
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

		history_insert(array(
			'type' => 4,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' => $zayav_status,
			'value1' => $z['zayav_status']
		));

		zayav_place_change($zayav_id, $dev_place, $place_other);
		zayav_day_finish_change($zayav_id, $day_finish);

		jsonSuccess();
		break;
	case 'zayav_cartridge_status':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		if(!$zayav_status = _isnum($_POST['status']))
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

		history_insert(array(
			'type' => 4,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' => $zayav_status,
			'value1' => $z['zayav_status']
		));

		jsonSuccess();
		break;
	case 'zayav_device_place':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['place']))
			jsonError();
		$dev_place = intval($_POST['place']);
		$place_other = $dev_place == 0 ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';
		if($dev_place == 0 && !$place_other)
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		zayav_place_change($zayav_id, $dev_place, $place_other);

		jsonSuccess();
		break;
	case 'zayav_accrual_add':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || !$_POST['sum'])
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || !$_POST['status'])
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

		$sum = intval($_POST['sum']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));
		$status = intval($_POST['status']);

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
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
					".$z['client_id'].",
					".$sum.",
					'".addslashes($prim)."',
					".VIEWER_ID."
				)";
		query($sql);

		clientBalansUpdate($z['client_id']);
		zayavBalansUpdate($zayav_id);

		history_insert(array(
			'type' => 5,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' => $sum
		));

		//Обновление статуса заявки, если изменялся
		if($z['zayav_status'] != $status) {
			$sql = "UPDATE `zayav`
					SET `zayav_status`=".$status.",`zayav_status_dtime`=CURRENT_TIMESTAMP
					WHERE `id`=".$zayav_id;
			query($sql);
			history_insert(array(
				'type' => 4,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'value' => $status,
				'value1' => $z['zayav_status']
			));
			$send['status'] = _zayavStatus($status);
			$send['status']['name'] = utf8($send['status']['name']);
			$send['status']['dtime'] = utf8(FullDataTime(curTime()));
		}

		//Внесение напоминания, если есть
		if($remind) {
			_remind_add(array(
				'zayav_id' => $zayav_id,
				'txt' => $remind_txt,
				'day' => $remind_day
			));
			$send['remind'] = utf8(_remind_spisok(array('zayav_id'=>$zayav_id), 'spisok'));
		}

		$send['html'] = utf8(zayav_info_money($zayav_id));
		jsonSuccess($send);
		break;
	case 'zayav_accrual_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `accrual` WHERE !`deleted` AND `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$r['zayav_id'];
		if(!$z = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `accrual` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);
		zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 8,
			'client_id' => $r['client_id'],
			'zayav_id' => $r['zayav_id'],
			'value' => $r['sum'],
			'value1' => $r['prim']
		));
		jsonSuccess();
		break;
	case 'zayav_accrual_rest':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT
		            *,
					'acc' AS `type`
				FROM `accrual`
				WHERE `ws_id`=".WS_ID."
				  AND `deleted`
				  AND `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$r['zayav_id'];
		if(!$z = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `accrual` SET
					`deleted`=0,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		clientBalansUpdate($r['client_id']);
		zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 27,
			'client_id' => $r['client_id'],
			'zayav_id' => $r['zayav_id'],
			'value' => $r['sum'],
			'value1' => $r['prim']
		));
		$send['html'] = utf8(zayav_accrual_unit($r));
		jsonSuccess($send);
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
		$zp['bu'] = intval($_POST['bu']);
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
		$send['msg'] = utf8('Запчасть <b>'._zpName($zp['name_id']).'</b> для '._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).' добавлена к заказу.');
		jsonSuccess($send);
		break;
	case 'zayav_zp_set':// Установка запчасти из заявки
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		if(!$zp_id = _isnum($_POST['zp_id']))
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

		//Удаление из заказа запчасти, привязанной к заявке
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
					'".addslashes('Установка запчасти: <a class="zp-id" val="'.$zp_id.'">'._zpName($zp['name_id']).' '.MODEL.'</a>')."',
					".$parent_id.",
					".VIEWER_ID."
				)";
		query($sql);

		history_insert(array(
			'type' => 13,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' => $count,
			'zp_id' => $zp_id
		));

		//добавление запчасти в расходы по заявке
		$cena = query_value("SELECT `cena` FROM `zp_move` WHERE `zp_id`=".$compat_id." AND `type`='' ORDER BY `id` DESC LIMIT 1");
		$sql = "INSERT INTO `zayav_expense` (
							`ws_id`,
							`zayav_id`,
							`category_id`,
							`zp_id`,
							`sum`
						) VALUES (
							".WS_ID.",
							".$zayav_id.",
							2,
							".$compat_id.",
							".$cena."
						)";
		query($sql);

		$zp['avai'] = $count;
		$send['zp_unit'] = utf8(zayav_zp($zp));
		$send['comment'] = utf8(_vkComment('zayav', $zayav_id));
		jsonSuccess($send);
		break;
	case 'zayav_tooltip':
		if(!$id = _num($_POST['id']))
			jsonError();

		$z = query_assoc("SELECT * FROM `zayav` WHERE `id`=".$id);
		$c = query_assoc("SELECT * FROM `client` WHERE !`deleted` AND `id`=".$z['client_id']);

		$telefon = _clientTelefon($c);
		$html =
			'<table>'.
				'<tr><td><div class="image">'._zayavImg($z).'</div>'.
					'<td class="inf">'.
						'<div style="background-color:#'._zayavStatusColor($z['zayav_status']).'" '.
							 'class="tstat'._tooltip('Статус заявки: '._zayavStatusName($z['zayav_status']), -7, 'l').
						'</div>'.
						_deviceName($z['base_device_id']).
						'<div class="tname">'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</div>'.
						'<table>'.
							'<tr><td class="label top">Клиент:'.
								'<td>'._clientName($c).
									   ($telefon ? '<br />'.$telefon : '').
							'<tr><td class="label">Баланс:'.
								'<td><span class="bl" style=color:#'.($c['balans'] < 0 ? 'A00' : '090').'>'.$c['balans'].'</span>'.
						'</table>'.
			'</table>';

		$send['html'] = utf8($html);
		jsonSuccess($send);
		break;
	case 'zayav_nomer_info'://Получение данных о заявке по номеру
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
			$send['html'] = '<span class="zayavNomerTab">Заявка не найдена</span>';
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
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		$active = _isbool(@$_POST['active']);
		$defect = win1251(htmlspecialchars(trim($_POST['defect'])));
		if(empty($defect))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$sql = "SELECT * FROM `client` WHERE !`deleted` AND `id`=".$z['client_id'];
		if(!$c = query_assoc($sql))
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

					'".addslashes($c['fio'])."',
					'".addslashes($c['telefon'])."',

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
	case 'zayav_expense_edit':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();

		$expenseNew = zayav_expense_test($_POST['expense']);
		if($expenseNew === false)
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$expenseOld = zayav_expense_spisok($z, 'array');
		if($expenseNew != $expenseOld) {
			$old = zayav_expense_spisok($z);
			query("DELETE FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id);
			foreach($expenseNew as $r) {
				$sql = "INSERT INTO `zayav_expense` (
							`ws_id`,
							`zayav_id`,
							`category_id`,
							`txt`,
							`worker_id`,
							`zp_id`,
							`sum`,
							`mon`,
							`year`
						) VALUES (
							".WS_ID.",
							".$zayav_id.",
							".$r[0].",
							'".(_zayavExpense($r[0], 'txt') ? addslashes($r[1]) : '')."',
							".(_zayavExpense($r[0], 'worker') ? intval($r[1]) : 0).",
							".(_zayavExpense($r[0], 'zp') ? intval($r[1]) : 0).",
							".$r[2].",
							".intval(strftime('%m')).",
							".strftime('%Y')."
						)";
				query($sql);
			}
		//	_zayavBalansUpdate($zayav_id);
			$changes = '<tr><td>'.$old.'<td>»<td>'.zayav_expense_spisok($z);
			history_insert(array(
				'type' => 30,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));
		}
		jsonSuccess();
		break;
	case 'zayav_money_update':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
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

		//подсчёт начисления зп для сотрудника
		$acc_sum = query_value("SELECT SUM(`sum`) FROM `accrual` WHERE !`deleted` AND `zayav_id`=".$zayav_id);
		$expense_sum = query_value("SELECT SUM(`sum`) FROM `zayav_expense` WHERE `zayav_id`=".$zayav_id." AND `category_id`!=1");
		$send['worker_zp'] = round(($acc_sum - $expense_sum) * 0.3);

		jsonSuccess($send);
		break;
	case 'zayav_executer_change'://изменение исполнителя заявки
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$executer_id = _num($_POST['executer_id']);
		if($executer_id) {//если id такого сотрудника нет в мастерской - ошибка
			$sql = "SELECT COUNT(*) FROM `vk_user` WHERE `ws_id`=".WS_ID." AND `viewer_id`=".$executer_id;
			if(!query_value($sql))
				jsonError();
		}

		if($z['executer_id'] == $executer_id)
			jsonError();

		$sql = "UPDATE `zayav` SET `executer_id`=".$executer_id." WHERE `id`=".$zayav_id;
		query($sql);


		history_insert(array(
			'type' => 58,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' =>
				'<table>'.
					'<tr><td>'.($z['executer_id'] ? _viewer($z['executer_id'], 'name') : '').
						'<td>»'.
						'<td>'.($executer_id ? _viewer($executer_id, 'name') : '').
					'</table>'
		));

		jsonSuccess();
		break;
	case 'zayav_cartridge_schet_load'://получение данных для счёта по катрриджам
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `cartridge` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$sql = "SELECT *
				FROM `zayav_cartridge`
				WHERE `zayav_id`=".$zayav_id."
				  AND (`filling` OR `restore` OR `chip`)
				  AND `cost`
				  AND !`schet_id`
				ORDER BY `id`";
		$q = query($sql);
		$schet = array();
		$n = 1;
		while($r = mysql_fetch_assoc($q)) {
			$same = 0;//тут будет номер, с которым будет найдено совпадение
			foreach($schet as $sn => $unit) {
				$diff = 0; // пока различий не обнаружено
				foreach($unit as $key => $val) {
					if($key == 'count')
						continue;
					if($r[$key] != $val) {
						$diff = 1;
						break;
					}
				}
				if(!$diff) { //если различий нет, то запоминание номера и выход
					$same = $sn;
					break;
				}
			}

			if($same)
				$schet[$same]['count']++;
			else {
				$schet[$n] = array(
					'cartridge_id' => $r['cartridge_id'],
					'filling' => $r['filling'],
					'restore' => $r['restore'],
					'chip' => $r['chip'],
					'cost' => $r['cost'],
					'prim' => $r['prim'],
					'count' => 1
				);
				$n++;
			}
		}

		$spisok = array();
		foreach($schet as $r) {
			$prim = array();
			if($r['filling'])
				$prim[] = 'заправка';
			if($r['restore'])
				$prim[] = 'восстановление';
			if($r['chip'])
				$prim[] = 'замена чипа у';

			$txt = implode(', ', $prim).' картриджа '._cartridgeName($r['cartridge_id']).($r['prim'] ? ', '.$r['prim'] : '');
			$txt = mb_ucfirst($txt);

			$spisok[] = array(
				'name' => utf8($txt),
				'count' => $r['count'],
				'cost' => $r['cost'],
				'cartridge' => 1
			);
		}

		$send['spisok'] = $spisok;
		jsonSuccess($send);
		break;
	case 'zayav_cartridge_schet_add'://формирование счёта
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		if(!preg_match(REGEXP_DATE, $_POST['date_create']))
			jsonError();

		$date_create = $_POST['date_create'];

		$spisok = @$_POST['spisok'];
		if(empty($spisok))
			jsonError();

		$sum = 0;
		foreach($spisok as $r) {
			$r['name'] = _txt($r['name']);
			$sum += $r['count'] * $r['cost'];
		}

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `cartridge` AND `id`=".$zayav_id;
		if(!$z = query_assoc($sql))
			jsonError();

		$nomer = _maxSql('zayav_schet', 'nomer');

		$sql = "INSERT INTO `zayav_schet` (
					`nomer`,
					`zayav_id`,
					`date_create`,
					`sum`,
					`viewer_id_add`
				) VALUES (
					".$nomer.",
					".$zayav_id.",
					'".$date_create."',
					".$sum.",
					".VIEWER_ID."
				)";
		query($sql);

		$insert_id = mysql_insert_id();

		//присвоение картриджам номера счёта
		$sql = "UPDATE `zayav_cartridge`
				SET `schet_id`=".$insert_id."
				WHERE `zayav_id`=".$zayav_id."
				  AND (`filling` OR `restore` OR `chip`)
				  AND `cost`
				  AND !`schet_id`";
		query($sql);

		//внесение списка наименований для счёта
		$values = array();
		foreach($spisok as $r)
			$values[] = "(".
				$insert_id.",".
				"'".addslashes(win1251($r['name']))."',".
				$r['count'].",".
				$r['cost'].",".
				(empty($r['cartridge']) ? 0 : 1).
			")";
		$sql = "INSERT INTO `zayav_schet_spisok` (
					`schet_id`,
					`name`,
					`count`,
					`cost`,
					`cartridge`
				) VALUES ".implode(',', $values);
		query($sql);

		history_insert(array(
			'type' => 59,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' => 'СЦ'.$nomer,
			'value1' => $sum,
			'value2' => $date_create
		));

		$send['cart'] = utf8(zayav_cartridge_info_tab($zayav_id));
		$send['schet'] = utf8(zayav_info_schet_spisok($zayav_id));

		jsonSuccess($send);
		break;

	case 'cartridge_new'://внесение новой модели картриджа
		$name = _txt($_POST['name']);
		$cost_filling = _isnum($_POST['cost_filling']);
		$cost_restore = _isnum($_POST['cost_restore']);
		$cost_chip = _isnum($_POST['cost_chip']);

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `setup_cartridge` (
					`ws_id`,
					`name`,
					`cost_filling`,
					`cost_restore`,
					`cost_chip`
				) VALUES (
					".WS_ID.",
					'".addslashes($name)."',
					".$cost_filling.",
					".$cost_restore.",
					".$cost_chip."
				)";
		query($sql);
		$send['insert_id'] = mysql_insert_id();

		xcache_unset(CACHE_PREFIX.'cartridge'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1017,
			'value' => $name
		));

		if($_POST['from'] == 'setup')
			$send['spisok'] = utf8(setup_service_cartridge_spisok());
		else {
			$send['spisok'] = query_selArray("SELECT `id`,`name` FROM `setup_cartridge` WHERE `ws_id`=" . WS_ID . " ORDER BY `name`");
		}

		jsonSuccess($send);
		break;
	case 'zayav_cartridge_add':
		if(!$client_id = _isnum($_POST['client_id']))
			jsonError();

		if(!$count = _isnum($_POST['count']))
			jsonError();

		// Если не указан ни один картридж (временно отменено, теперь указывается просто количество)
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

					1,
					current_timestamp,

					'".rand(10, 99).(time() + rand(10000, 99999))."',
					".VIEWER_ID."
				)";
		query($sql);
		$send['id'] = mysql_insert_id();


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

		history_insert(array(
			'type' => 54,
			'client_id' => $client_id,
			'zayav_id' => $send['id']
		));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_add'://добавление картриджей к заявке
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();

		// Если не указан ни один картридж
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


		history_insert(array(
			'type' => 55,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' => implode(', ', $cartgidge)
		));

		$send['html'] = utf8(zayav_cartridge_info_tab($zayav_id));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_edit'://применение действия по картриджу
		if(!$id = _isnum($_POST['id']))
			jsonError();
		if(!$cartridge_id = _isnum($_POST['cart_id']))
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

		$changes = '';
		if($r['cartridge_id'] != $cartridge_id)
			$changes .= '<tr><th>Модель:<td>'._cartridgeName($r['cartridge_id']).'<td>»<td>'._cartridgeName($cartridge_id);
		if($r['filling'] != $filling || $r['restore'] != $restore || $r['chip'] != $chip) {
			$old = array();
			if($r['filling'])
				$old[] = 'заправлен';
			if($r['restore'])
				$old[] = 'восстановлен';
			if($r['chip'])
				$old[] = 'заменён чип';
			$new = array();
			if($filling)
				$new[] = 'заправлен';
			if($restore)
				$new[] = 'восстановлен';
			if($chip)
				$new[] = 'заменён чип';
			$changes .= '<tr><th>Действие:<td>'.implode(', ', $old).'<td>»<td>'.implode(', ', $new);
		}
		if($r['cost'] != $cost)
			$changes .= '<tr><th>Стоимость:<td>'.$r['cost'].'<td>»<td>'.$cost;
		if($r['prim'] != $prim)
			$changes .= '<tr><th>Примечание:<td>'.$r['prim'].'<td>»<td>'.$prim;
		if($changes)
			history_insert(array(
				'type' => 57,
				'client_id' => $z['client_id'],
				'zayav_id' => $r['zayav_id'],
				'value' => _cartridgeName($cartridge_id),
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(zayav_cartridge_info_tab($r['zayav_id']));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_del'://удаление картриджа из заявки
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_cartridge` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `cartridge` AND `id`=".$r['zayav_id'];
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "DELETE FROM `zayav_cartridge` WHERE `id`=".$id;
		query($sql);

		history_insert(array(
			'type' => 56,
			'client_id' => $z['client_id'],
			'zayav_id' => $r['zayav_id'],
			'value' => _cartridgeName($r['cartridge_id'])
		));


		$send['html'] = utf8(zayav_cartridge_info_tab($r['zayav_id']));
		jsonSuccess($send);
		break;
	case 'zayav_cartridge_spisok':
		$data = zayav_cartridge_spisok($_POST);
		if($data['filter']['page'] == 1)
			$send['all'] = utf8($data['result']);
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'zayav_cartridge_edit':
		if(!$zayav_id = _isnum($_POST['zayav_id']))
			jsonError();
		if(!$client_id = _isnum($_POST['client_id']))
			jsonError();
		if(!$count = _isnum($_POST['count']))
			jsonError();

		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
		if(!$z = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `zayav`
				SET `client_id`=".$client_id.",
					`cartridge_count`=".$count."
				WHERE `id`=".$zayav_id;
		query($sql);

		if($z['client_id'] != $client_id) {
			$sql = "UPDATE `accrual`
					SET `client_id`=".$client_id."
					WHERE `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql);
			$sql = "UPDATE `money`
					SET `client_id`=".$client_id."
					WHERE `ws_id`=".WS_ID."
					  AND `zayav_id`=".$zayav_id."
					  AND `client_id`=".$z['client_id'];
			query($sql);
			clientBalansUpdate($z['client_id']);
			clientBalansUpdate($client_id);
		}

		$changes = '';
		if($z['client_id'] != $client_id)
			$changes .= '<tr><th>Клиент:<td>'._clientLink($z['client_id']).'<td>»<td>'._clientLink($client_id);
		if($z['cartridge_count'] != $count)
			$changes .= '<tr><th>Количество картриджей:<td>'.$z['cartridge_count'].'<td>»<td>'.$count;
		if($changes)
			history_insert(array(
				'type' => 7,
				'client_id' => $z['client_id'],
				'zayav_id' => $zayav_id,
				'value' => '<table>'.$changes.'</table>'
			));

		jsonSuccess();
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
			'base_device_id' => intval($_POST['device_id']),
			'base_vendor_id' => intval($_POST['vendor_id']),
			'base_model_id' => intval($_POST['model_id']),
			'version' => win1251(htmlspecialchars(trim($_POST['version']))),
			'bu' => intval($_POST['bu']),
			'color_id' => intval($_POST['color_id']),
		);
		zpAddQuery($zp);

		jsonSuccess();
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
	case 'zp_edit':
		if(!$zp_id = _isnum($_POST['zp_id']))
			jsonError();
		if(!$name_id = _isnum($_POST['name_id']))
			jsonError();
		if(!$device_id = _isnum($_POST['device_id']))
			jsonError();
		if(!$vendor_id = _isnum($_POST['vendor_id']))
			jsonError();
		if(!$model_id = _isnum($_POST['model_id']))
			jsonError();

		$version = win1251(htmlspecialchars(trim($_POST['version'])));
		$bu = _isbool($_POST['bu']);
		$color_id = _isnum($_POST['color_id']);
		$price_id = _isnum($_POST['price_id']);

		$sql = "UPDATE `zp_catalog`
				SET `name_id`=".$name_id.",
					`base_device_id`=".$device_id.",
					`base_vendor_id`=".$vendor_id.",
					`base_model_id`=".$model_id.",
					`version`='".$version."',
					`bu`=".$bu.",
					`color_id`=".$color_id.",
					`price_id`=".$price_id.",
					`find`='".addslashes(_modelName($model_id).' '.$version)."'
				WHERE `id`=".$zp_id;
		query($sql);

		$compat_id = _zpCompatId($zp_id);
		if($compat_id != $zp_id) {
			$sql = "UPDATE `zp_catalog`
					SET `name_id`=".$name_id.",
						`version`='".$version."',
						`bu`=".$bu.",
						`price_id`=".$price_id."
					WHERE `id`=".$compat_id;
			query($sql);
		}

		jsonSuccess();
		break;
	case 'zp_sale':// Продажа запчасти
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
		if(!$zp_id = _isnum($_POST['zp_id']))
			jsonError();
		if(!$device_id = _isnum($_POST['device_id']))
			jsonError();
		if(!$vendor_id = _isnum($_POST['vendor_id']))
			jsonError();
		if(!$model_id = _isnum($_POST['model_id']))
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
				  AND `bu`=".$zp['bu']."
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
		if(!$id = _isnum($_POST['id']))
			jsonError();
		if(!$zp = query_assoc("SELECT * FROM `zp_price` WHERE `id`=".$id))
			jsonError();

		$ass = array(
			'name' => 'Наименование',
			'cena' => 'Цена'
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
		if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['invoice_id']) || !$_POST['invoice_id'])
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']))
			jsonError();

		if(!preg_match(REGEXP_NUMERIC, $_POST['place']))
			jsonError();

		$place = intval($_POST['place']);
		$place_other = !$place ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';

		if(!$_POST['zayav_id'] && empty($_POST['prim']))
			jsonError();

		if(!$v = income_insert($_POST))
			jsonError();

		$send = array();
		if($v['zayav_id'])
			$send = zayav_place_change($v['zayav_id'], $place, $place_other);

		jsonSuccess($send);
		break;
	case 'income_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT *
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND !`deleted`
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		invoice_history_insert(array(
			'action' => 2,
			'table' => 'money',
			'id' => $id
		));
		clientBalansUpdate($r['client_id']);
		zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 9,
			'client_id' => $r['client_id'],
			'zayav_id' => $r['zayav_id'],
			'zp_id' => $r['zp_id'],
			'value' => round($r['sum'], 2),
			'value1' => $r['prim'],
			'value2' => $r['invoice_id']
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
				  AND `deleted`
				  AND `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$sql = "UPDATE `money` SET
					`deleted`=0,
					`viewer_id_del`=0,
					`dtime_del`='0000-00-00 00:00:00'
				WHERE `id`=".$id;
		query($sql);

		invoice_history_insert(array(
			'action' => 3,
			'table' => 'money',
			'id' => $id
		));
		clientBalansUpdate($r['client_id']);
		$send = zayavBalansUpdate($r['zayav_id']);

		history_insert(array(
			'type' => 19,
			'client_id' => $r['client_id'],
			'zayav_id' => $r['zayav_id'],
			'zp_id' => $r['zp_id'],
			'value' => round($r['sum'], 2),
			'value1' => $r['prim'],
			'value2' => $r['invoice_id']
		));

		jsonSuccess();
		break;

	case 'expense_spisok':
		$data = expense_spisok($_POST);
		$send['html'] = utf8($data['spisok']);
		$send['mon'] = utf8(expenseMonthSum($_POST));
		jsonSuccess($send);
		break;
	case 'expense_add':
		if(!$invoice_id = _isnum($_POST['invoice_id']))
			jsonError();
		if(!$sum = _cena($_POST['sum']))
			jsonError();

		$expense_id = _isnum($_POST['expense_id']);
		$prim = win1251(htmlspecialchars(trim($_POST['prim'])));
		if(!$expense_id && empty($prim))
			jsonError();

		$worker_id = _isnum($_POST['worker_id']);
		$mon = _isnum($_POST['mon']);
		$year = _isnum($_POST['year']);
		if($expense_id == 1 && (!$worker_id || !$year || !$mon))
			jsonError();

		$sql = "INSERT INTO `money` (
					`ws_id`,
					`sum`,
					`prim`,
					`invoice_id`,
					`expense_id`,
					`worker_id`,
					`year`,
					`mon`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					-".$sum.",
					'".addslashes($prim)."',
					".$invoice_id.",
					".$expense_id.",
					".$worker_id.",
					".$year.",
					".$mon.",
					".VIEWER_ID."
				)";
		query($sql);

		invoice_history_insert(array(
			'action' => 6,
			'table' => 'money',
			'id' => mysql_insert_id()
		));

		history_insert(array(
			'type' => 21,
			'value' => abs($sum),
			'value1' => $prim,
			'value2' => $expense_id ? $expense_id : '',
			'value3' => $worker_id ? $worker_id : ''
		));
		jsonSuccess();
		break;
	case 'expense_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `money` WHERE !`deleted` AND `sum`<0 AND `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `money` SET
					`deleted`=1,
					`viewer_id_del`=".VIEWER_ID.",
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".$id;
		query($sql);

		invoice_history_insert(array(
			'action' => 7,
			'table' => 'money',
			'id' => $id
		));

		history_insert(array(
			'type' => 22,
			'value' => round(abs($r['sum']), 2)
		));
		jsonSuccess();
		break;

	case 'invoice_set':
		if(!$invoice_id = _isnum($_POST['invoice_id']))
			jsonError();
		$sum = _cena($_POST['sum']);

		$sql = "SELECT * FROM `invoice` WHERE `ws_id`=".WS_ID." AND `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		//if($r['start'] != -1 && !VIEWER_ADMIN)
		if($r['start'] != -1)
			jsonError();

		query("UPDATE `invoice` SET `start`="._invoiceBalans($invoice_id, $sum)." WHERE `id`=".$invoice_id);
		xcache_unset(CACHE_PREFIX.'invoice'.WS_ID);
		invoice_history_insert(array(
			'action' => 5,
			'invoice_id' => $invoice_id
		));

		history_insert(array(
			'type' => 28,
			'value' => $sum,
			'value1' => $invoice_id
		));

		$send['html'] = utf8(invoice_spisok());
		jsonSuccess($send);
		break;
	case 'invoice_reset':
		if(!$invoice_id = _isnum($_POST['invoice_id']))
			jsonError();

		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($r['start'] == -1 || !VIEWER_ADMIN)
			jsonError();

		query("UPDATE `invoice` SET `start`=-1 WHERE `id`=".$invoice_id);
		xcache_unset(CACHE_PREFIX.'invoice'.WS_ID);

		history_insert(array(
			'type' => 53,
			'value' => $invoice_id
		));

		$send['html'] = utf8(invoice_spisok());
		jsonSuccess($send);
		break;
	case 'invoice_history':
		if(empty($_POST['invoice_id']) || !preg_match(REGEXP_NUMERIC, $_POST['invoice_id']))
			jsonError();
		$send['html'] = utf8(invoice_history($_POST));
		jsonSuccess($send);
		break;
	case 'invoice_transfer':
		if(empty($_POST['from']) || !preg_match(REGEXP_NUMERIC, $_POST['from']))
			jsonError();
		if(empty($_POST['to']) || !preg_match(REGEXP_NUMERIC, $_POST['to']))
			jsonError();
		if(!preg_match(REGEXP_CENA, $_POST['sum']) || $_POST['sum'] == 0)
			jsonError();

		$from = intval($_POST['from']);
		$to = intval($_POST['to']);
		$sum = str_replace(',', '.', $_POST['sum']);
		$about = win1251(htmlspecialchars(trim($_POST['about'])));

		if($from == $to)
			jsonError();

		$invoice_from = $from > 100 ? 0 : $from;
		$invoice_to = $to > 100 ? 0 : $to;
		$sql = "INSERT INTO `invoice_transfer` (
					`ws_id`,
					`invoice_from`,
					`invoice_to`,
					`worker_from`,
					`worker_to`,
					`sum`,
					`about`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$invoice_from.",
					".$invoice_to.",
					".($from > 100 ? $from : 0).",
					".($to > 100  ? $to : 0).",
					".$sum.",
					'".addslashes($about)."',
					".VIEWER_ID."
				)";
		query($sql);

		invoice_history_insert(array(
			'action' => 4,
			'table' => 'invoice_transfer',
			'id' => mysql_insert_id()
		));

		history_insert(array(
			'type' => 39,
			'value' => $sum,
			'value1' => $from,
			'value2' => $to,
			'value3' => $about
		));

		$send['i'] = utf8(invoice_spisok());
		$send['t'] = utf8(transfer_spisok());
		jsonSuccess($send);
		break;

	case 'salary_rate_set':
		if(!$worker_id = _isnum($_POST['worker_id']))
			jsonError();
		if(!$sum = _cena($_POST['sum']))
			jsonError();
		if(!$period = _isnum($_POST['period']))
			jsonError();

		$day = 0;
		switch($period) {
			case 1:
				if(!$day = _isnum($_POST['day']))
					jsonError();
				if($day > 28)
					jsonError();
				break;
			case 2:
				if(!$day = _isnum($_POST['day']))
					jsonError();
				if($day > 7)
					jsonError();
		}

		$sql = "SELECT * FROM `vk_user` WHERE `ws_id`=".WS_ID." AND `viewer_id`=".$worker_id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `vk_user`
		        SET `rate_sum`=".$sum.",
		            `rate_period`=".$period.",
		            `rate_day`=".$day."
				WHERE `viewer_id`=".$worker_id;
		query($sql);

		$changes = '';
		if($r['rate_sum'] != $sum)
			$changes .= '<tr><th>Сумма:<td>'.round($r['rate_sum'], 2).'<td>»<td>'.$sum;
		if($r['rate_period'] != $period)
			$changes .= '<tr><th>Период:<td>'.salaryPeriod($r['rate_period']).'<td>»<td>'.salaryPeriod($period);
		if($r['rate_day'] != $day)
			$changes .= '<tr><th>День:<td>'.($r['rate_day'] ? $r['rate_day'] : '').'<td>»<td>'.($day ? $day : '');
		if($changes)
			history_insert(array(
				'type' => 35,
				'value' => $worker_id,
				'value1' => '<table>'.$changes.'</table>'
			));

		xcache_unset(CACHE_PREFIX.'viewer_'.$worker_id);

		jsonSuccess();
		break;
	case 'salary_up':
		if(!$worker_id = _isnum($_POST['worker_id']))
			jsonError();
		if(!$sum = _cena($_POST['sum']))
			jsonError();
		if(!$mon = _isnum($_POST['mon']))
			jsonError();
		if(!$year = _isnum($_POST['year']))
			jsonError();

		$about = win1251(htmlspecialchars(trim($_POST['about'])));

		$sql = "INSERT INTO `zayav_expense` (
					`ws_id`,
					`worker_id`,
					`sum`,
					`txt`,
					`mon`,
					`year`
				) VALUES (
					".WS_ID.",
					".$worker_id.",
					".$sum.",
					'".addslashes($about)."',
					".$mon.",
					".$year."
				)";
		query($sql);

		history_insert(array(
			'type' => 36,
			'value' => $sum,
			'value1' => $about,
			'value2' => $worker_id
		));

		jsonSuccess();
		break;
	case 'salary_deduct':
		if(!$worker_id = _isnum($_POST['worker']))
			jsonError();
		if(!$sum = _isnum($_POST['sum']))
			jsonError();
		if(!$year = _isnum($_POST['year']))
			jsonError();
		if(!$mon = _isnum($_POST['mon']))
			jsonError();
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$sql = "INSERT INTO `zayav_expense` (
					`ws_id`,
					`worker_id`,
					`sum`,
					`txt`,
					`year`,
					`mon`
				) VALUES (
					".WS_ID.",
					".$worker_id.",
					-".$sum.",
					'".addslashes($about)."',
					".$year.",
					".$mon."
				)";
		query($sql);

		history_insert(array(
			'type' => 44,
			'value' => $sum,
			'value1' => $about,
			'value2' => $worker_id
		));

		jsonSuccess();
		break;
	case 'salary_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_expense` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		query("DELETE FROM `zayav_expense` WHERE `id`=".$id);

		history_insert(array(
			'type' => 50,
			'value' => _cena($r['sum']),
			'value1' => $r['worker_id']
		));

		jsonSuccess();
		break;
	case 'salary_deduct_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_expense` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		query("DELETE FROM `zayav_expense` WHERE `id`=".$id);

		history_insert(array(
			'type' => 51,
			'value' => round(abs($r['sum']), 2),
			'value1' => $r['txt'],
			'value2' => $r['worker_id']
		));

		jsonSuccess();
		break;
	case 'salary_zp_add':
		if(!$worker_id = _isnum($_POST['worker_id']))
			jsonError();
		if(!$invoice_id = _isnum($_POST['invoice_id']))
			jsonError();
		if(!$sum = _cena($_POST['sum']))
			jsonError();
		if(!$mon = _isnum($_POST['mon']))
			jsonError();
		if(!$year = _isnum($_POST['year']))
			jsonError();

		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		$about = _monthDef($mon).' '.$year.($about ? ', ' : '').$about;

		$sql = "INSERT INTO `money` (
					`ws_id`,
					`sum`,
					`prim`,
					`invoice_id`,
					`expense_id`,
					`worker_id`,
					`year`,
					`mon`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					-".$sum.",
					'".addslashes($about)."',
					".$invoice_id.",
					1,
					".$worker_id.",
					".$year.",
					".$mon.",
					".VIEWER_ID."
				)";
		query($sql);

		invoice_history_insert(array(
			'action' => 6,
			'table' => 'money',
			'id' => mysql_insert_id()
		));

		history_insert(array(
			'type' => 37,
			'value' => $sum,
			'value1' => $about,
			'value2' => $worker_id
		));

		jsonSuccess();
		break;
	case 'salary_start_set':
		if(!$worker_id = _isnum($_POST['worker_id']))
			jsonError();
		$sum = _cena($_POST['sum']);

		$sql = "SELECT * FROM `vk_user` WHERE `ws_id`=".WS_ID." AND `viewer_id`=".$worker_id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sMoney = query_value("
				SELECT IFNULL(SUM(`sum`),0)
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND `worker_id`=".$worker_id."
				  AND `sum`<0
				  AND !`deleted`");
		$sExpense = query_value("
				SELECT IFNULL(SUM(`sum`),0)
				FROM `zayav_expense`
				WHERE `ws_id`=".WS_ID."
				  AND `mon`
			      AND `worker_id`=".$worker_id);
		$start = round($sum - $sMoney - $sExpense, 2);

		query("UPDATE `vk_user` SET `salary_balans_start`=".$start." WHERE `viewer_id`=".$worker_id);

		xcache_unset(CACHE_PREFIX.'viewer_'.$worker_id);

		history_insert(array(
			'type' => 45,
			'value' => $worker_id,
			'value1' => $sum
		));

		$send['html'] = utf8(salary_worker_spisok(array('worker_id'=>$worker_id)));
		jsonSuccess($send);
		break;
	case 'salary_spisok':
		$send['html'] = utf8(salary_worker_spisok($_POST));
		$send['month'] = utf8(salary_monthList($_POST));
		jsonSuccess($send);
		break;
	case 'salary_bonus_spisok':
		if(!$worker_id = _isnum($_POST['worker_id']))
			jsonError();
		if(!$year = _isnum($_POST['year']))
			jsonError();
		if(!$week = _isnum($_POST['week']))
			jsonError();
		$send['spisok'] = utf8(salary_worker_bonus($worker_id, $year, $week));
		jsonSuccess($send);
		break;
	case 'salary_bonus':
		if(!$worker_id = _isnum($_POST['worker_id']))
			jsonError();
		if(!$year = _isnum($_POST['year']))
			jsonError();
		if(!$week = _isnum($_POST['week']))
			jsonError();

		$bonus = array();
		$bonusSum = 0;
		foreach(explode(',', $_POST['bonus']) as $ex) {
			$r = explode(':', $ex);
			if(!$id = _isnum($r[0]))
				jsonError();
			$expense = _isnum($r[1]);
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

		$first_day = date('Y-m-d', ($week - 1) * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400 + 86400);
		$last_day = date('Y-m-d', $week * 7 * 86400 + strtotime('1/1/' . $year) - date('w', strtotime('1/1/' . $year)) * 86400);
		$about = 'Бонус по платежам, '._viewerRules($worker_id, 'RULES_MONEY_PROCENT').'%:'.
				 '<br />'.
				 '<a class="bonus-show" val="'.$insert_id.'">'.
					$week.'-я неделя ('.FullData($first_day).' - '.FullData($last_day).')'.
				 '</a>.';
		query("UPDATE `zayav_expense` SET `txt`='".addslashes($about)."' WHERE `id`=".$insert_id);

		// Внесение списка бонусов
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
	case 'salary_bonus_show':// просмотр бонуса по платежам
		if(!$expense_id = _isnum($_POST['expense_id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_expense` WHERE `id`=".$expense_id;
		if(!$r = query_assoc($sql))
			jsonError();

		$send['html'] = utf8(salary_worker_bonus_show($r));

		jsonSuccess($send);
		break;
}

jsonError();


function zayav_place_change($zayav_id, $place, $place_other) {//изменение местонахожнения устройства и внесение истории
	$r = query_assoc("SELECT * FROM `zayav` WHERE `id`=".$zayav_id);
	if($place != $r['device_place'] || $place_other != $r['device_place_other']) {
		$sql = "UPDATE `zayav`
						SET `device_place`=".$place.",
							`device_place_other`='".$place_other."',
							`device_place_dtime`=CURRENT_TIMESTAMP
						WHERE `id`=".$zayav_id;
		query($sql);
		history_insert(array(
			'type' => 29,
			'client_id' => $r['client_id'],
			'zayav_id' => $zayav_id,
			'value' =>
				'<table><tr>'.
					'<td>'.($r['device_place'] ? @_devPlace($r['device_place']) : $r['device_place_other']).
					'<td>»'.
					'<td>'.($place ? @_devPlace($place) : $place_other).
				'</table>'
		));
	}
	$send['html'] = utf8(zayav_info_money($zayav_id));

	if($place != $r['device_place'] && $place == 2)
		$send['comment'] = zayav_msg_to_client($zayav_id);
	return $send;
}//zayav_place_change()
function zayav_day_finish_change($zayav_id, $day) {//изменение срока выполнения
	$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$zayav_id;
	$z = query_assoc($sql);
	if($day != $z['day_finish'] && $day != '0000-00-00') {
		query("UPDATE `zayav` SET `day_finish`='".$day."' WHERE `id`=".$zayav_id);
		history_insert(array(
			'type' => 52,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'value' => '<table><tr>'.
				'<th>Срок:'.
					'<td>'.($z['day_finish'] == '0000-00-00' ? 'не указан' : FullData($z['day_finish'], 0, 1, 1)).
					'<td>»'.
					'<td>'.FullData($day, 0, 1, 1).
				'</table>'
		));
	}
}//zayav_day_finish_change()
mb_internal_encoding('UTF-8');
function mb_ucfirst($text) {
	return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}



