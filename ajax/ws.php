<?php
switch(@$_POST['op']) {
	case 'zpname_add':
		if(!$device_id = _num($_POST['device_id']))
			jsonError();

		$name = _txt($_POST['name']);

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `setup_zp_name` (
					`device_id`,
					`name`
				) VALUES (
					".$device_id.",
					'".addslashes($name)."'
				)";
		query($sql);

		$send['id'] = mysql_insert_id();

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'zp_name');

		$send['zp'] = SA ? utf8(sa_zpname_spisok($device_id)) : '';

		jsonSuccess($send);
		break;

	case 'base_device_add':
		$name = _txt($_POST['name']);
		if(empty($name))
			jsonError();

		$sql = "SELECT COUNT(*) FROM `base_device` WHERE `name`='".addslashes($name)."'";
		if(query_value($sql))
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
		$send['id'] = query_insert_id('base_device');

		$sql = "UPDATE `setup` SET `devs`=CONCAT(`devs`,',".$send['id']."') WHERE `ws_id`=".WS_ID;
		query($sql);

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'device_name');

		jsonSuccess($send);
		break;
	case 'base_vendor_add':
		if(!$device_id = _num($_POST['device_id']))
			jsonError();

		$name = _txt($_POST['name']);
		if(empty($name))
			jsonError();

		$sql = "SELECT COUNT(*) FROM `base_vendor` WHERE `device_id`=".$device_id." AND `name`='".addslashes($name)."'";
		if(query_value($sql))
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
		$send['id'] = query_insert_id('base_vendor');

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'vendor_name');

		jsonSuccess($send);
		break;
	case 'base_model_add':
		if(!$device_id = _num($_POST['device_id']))
			jsonError();
		if(!$vendor_id = _num($_POST['vendor_id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "SELECT COUNT(*)
				FROM `base_model`
				WHERE `device_id`=".$device_id."
				  AND `vendor_id`=".$vendor_id."
				  AND `name`='".addslashes($name)."'";
		if(query_value($sql))
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
		$send['id'] = query_insert_id('base_model');

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'model_name_count');

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
	case 'zayav_device_place':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		$place_id = _num($_POST['place']);
		$place_other = !$place_id ? _txt($_POST['place_other']) : '';

		zayavPlaceCheck($zayav_id, $place_id, $place_other);

		jsonSuccess();
		break;
	case 'zayav_zp_add':
		if(!$zayav_id = _num($_POST['zayav_id']))
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
				FROM `_zayav`
				WHERE `app_id`=".APP_ID."
				  AND `ws_id`=".WS_ID."
				  AND !`deleted`
				  AND `id`=".$zayav_id;
		if(!$zp = query_assoc($sql, GLOBAL_MYSQL_CONNECT))
			jsonError();

		$zp['name_id'] = intval($_POST['name_id']);
		$zp['version'] = _txt($_POST['version']);
		$zp['color_id'] = intval($_POST['color_id']);
		zpAddQuery($zp);

		$send['html'] = utf8(zayav_zp($zp));
		jsonSuccess($send);
		break;
	case 'zayav_zp_zakaz':
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();

		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$zp_id;
		$zp = query_assoc($sql);
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
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		if(!$count = _num(@$_POST['count']))
			$count = 1;

		$compat_id = _zpCompatId($zp_id);
		$prim = _txt(@$_POST['prim']);

		if(!$z = _zayavQuery($zayav_id))
			jsonError();

		$sql = "SELECT * FROM `zp_catalog` WHERE id=".$zp_id." LIMIT 1";
		if(!$zp = query_assoc($sql))
			jsonError();

		$sql = "INSERT INTO `zp_move` (
					`ws_id`,
					`zp_id`,
					`count`,
					`type`,
					`client_id`,
					`zayav_id`,
					`prim`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$compat_id.",
					-".$count.",
					'set',
					".$z['client_id'].",
					".$zayav_id.",
					'".$prim."',
					".VIEWER_ID."
				)";
		query($sql);

		$count = _zpAvaiSet($compat_id);

		//Удаление из заказа запчасти, привязанной к заявке
		$sql = "DELETE FROM `zp_zakaz`
				WHERE `ws_id`=".WS_ID."
				  AND `zayav_id`=".$zayav_id."
				  AND `zp_id`=".$zp_id;
		query($sql);

		_note(array(
			'add' => 1,
			'comment' => 1,
			'p' => 'zayav',
			'id' => $zayav_id,
			'txt' => 'Установка запчасти: '.
					 '<a class="zp-id" val="'.$zp_id.'">'.
						_zpName($zp['name_id']).' '.
						_vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).
					 '</a>'
		));

		//добавление запчасти в расходы по заявке
		$sql = "SELECT `cena`
				FROM `zp_move`
				WHERE `zp_id`=".$compat_id."
				  AND `type`=''
				ORDER BY `id` DESC
				LIMIT 1";
		$cena = query_value($sql);
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

		_history(array(
			'type_id' => 13,
			'client_id' => $z['client_id'],
			'zayav_id' => $zayav_id,
			'v1' => $count,
			'zp_id' => $zp_id
		));

		jsonSuccess();
		break;
	case 'zayav_nomer_info'://Получение данных о заявке по номеру
		if(!$nomer = _num($_POST['nomer']))
			jsonError();

		$sql = "SELECT *
				FROM `_zayav`
				WHERE `app_id`=".APP_ID."
				  AND `ws_id`=".WS_ID."
				  AND `nomer`=".$nomer."
				  AND `status`
				LIMIT 1";
		if(!$z = query_assoc($sql, GLOBAL_MYSQL_CONNECT))
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
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();
		$active = _bool(@$_POST['active']);
		$defect = _txt($_POST['defect']);
		if(empty($defect))
			jsonError();

		if(!$z = _zayavQuery($zayav_id))
			jsonError();

		$sql = "DELETE FROM `zayav_kvit`
				WHERE `ws_id`=".WS_ID."
				  AND !`active`
				  AND `zayav_id`=".$zayav_id;
		query($sql);

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

		jsonSuccess($send);
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

		if(!$z = _zayavQuery($zayav_id))
			jsonError();

		$sql = "UPDATE `_zayav` SET `diagnost`=0 WHERE `id`=".$zayav_id;
		query($sql, GLOBAL_MYSQL_CONNECT);

		_note(array(
			'add' => 1,
			'comment' => 1,
			'p' => 'zayav',
			'id' => $zayav_id,
			'txt' => 'Результаты диагностики: '.$comm
		));

		//Внесение напоминания, если есть
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

	case 'cartridge_new'://внесение новой модели картриджа
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
	case 'zayav_info_cartridge_add'://добавление картриджей к заявке
		if(!$zayav_id = _num($_POST['zayav_id']))
			jsonError();

		// Если не указан ни один картридж
		if(!$ids = _ids($_POST['ids'], 1))
			jsonError();

		if(!$z = _zayavQuery($zayav_id))
			jsonError();

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

		$send['html'] = utf8(zayavInfoCartridge_spisok($zayav_id));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_edit'://применение действия по картриджу
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
		if(!$r = query_assoc($sql))
			jsonError();

		if(!$z = _zayavQuery($r['zayav_id']))
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
			_historyChange('Модель', _cartridgeName($r['cartridge_id']), _cartridgeName($cartridge_id)).
			_historyChange('Стоимость', $r['cost'], $cost).
			_historyChange('Примечание', $r['prim'], $prim);
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
			$changes .= _historyChange('Действие', implode(', ', $old), implode(', ', $new));
		}
		if($changes)
			_history(array(
				'type_id' => 57,
				'client_id' => $z['client_id'],
				'zayav_id' => $r['zayav_id'],
				'v1' => _cartridgeName($cartridge_id),
				'v2' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(zayavInfoCartridge_spisok($r['zayav_id']));
		jsonSuccess($send);
		break;
	case 'zayav_info_cartridge_del'://удаление картриджа из заявки
		if(!$id = _num($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_cartridge` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		if(!$z = _zayavQuery($r['zayav_id']))
			jsonError();

		$sql = "DELETE FROM `zayav_cartridge` WHERE `id`=".$id;
		query($sql);

		_history(array(
			'type_id' => 56,
			'client_id' => $z['client_id'],
			'zayav_id' => $r['zayav_id'],
			'v1' => _cartridgeName($r['cartridge_id'])
		));

		jsonSuccess();
		break;
	case 'zayav_cartridge_ids':
		if(!$ids = _ids($_POST['ids']))
			jsonError();

		$send['arr'] = zayav_cartridge_for_schet($ids);
		jsonSuccess($send);
		break;
	case 'zayav_cartridge_schet_set':
		if(!$schet_id = _num($_POST['schet_id']))
			jsonError();
		if(!$ids = _ids($_POST['ids']))
			jsonError();

		$sql = "UPDATE `zayav_cartridge`
				SET `schet_id`=".$schet_id."
				WHERE `id` IN (".$ids.")";
		query($sql);

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
	case 'zp_sale':// Продажа запчасти
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		if(!$invoice_id = _num($_POST['invoice_id']))
			jsonError();
		if(!$count = _num($_POST['count']))
			jsonError();
		if(!$cena = _cena($_POST['cena']))
			jsonError();

		$client_id = _num($_POST['client_id']);
		$sum = _cena($count * $cena);
		$prim = _txt($_POST['client_id']);

		//внесение платежа
		$sql = "INSERT INTO `_money_income` (
					`app_id`,
					`ws_id`,
					`invoice_id`,
					`zp_id`,
					`sum`,
					`client_id`,
					`viewer_id_add`
				) VALUES (
					".APP_ID.",
					".WS_ID.",
					".$invoice_id.",
					".$zp_id.",
					".$sum.",
					".$client_id.",
					".VIEWER_ID."
				)";
		query($sql, GLOBAL_MYSQL_CONNECT);

		$income_id = query_insert_id('_money_income', GLOBAL_MYSQL_CONNECT);


		//внесение движения запчасти
		$sql = "INSERT INTO `zp_move` (
				`ws_id`,
				`zp_id`,
				`count`,
				`cena`,
				`summa`,
				`type`,
				`client_id`,
				`income_id`,
				`prim`,
				`viewer_id_add`
			) VALUES (
				".WS_ID.",
				".$zp_id.",
				-".$count.",
				".$cena.",
				".$sum.",
				'sale',
				".$client_id.",
				".$income_id.",
				'".addslashes($prim)."',
				".VIEWER_ID."
			)";
		query($sql);

		_zpAvaiSet($zp_id);

		//баланс для расчётного счёта
		_balans(array(
			'action_id' => 1,
			'invoice_id' => $invoice_id,
			'sum' => $sum,
			'income_id' => $income_id
		));

		jsonSuccess();
		break;
	case 'zp_other':// Продажа запчасти
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
		if(!$zp_id = _num($_POST['zp_id']))
			jsonError();
		$send['count'] = _zpAvaiSet($zp_id);
		$send['move'] = utf8(zp_move($zp_id));
		jsonSuccess($send);
		break;
	case 'zp_move_del':
		if(!$id = _num($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `id`=".$id;
		if(!$move = query_assoc($sql))
			jsonError();

		$sql = "SELECT `id`
				FROM `zp_move`
				WHERE `ws_id`=".WS_ID."
				  AND `zp_id`="._zpCompatId($move['zp_id'])."
				ORDER BY `id` DESC
				LIMIT 1";
		$lastMoveId = query_value($sql);
		if($id != $lastMoveId)
			jsonError();

		$sql = "DELETE FROM `zp_move` WHERE `id`=".$id;
		query($sql);

		if($move['type'] == 'sale') {
			$sql = "SELECT *
					FROM `_money_income`
					WHERE `app_id`=".APP_ID."
					  AND `ws_id`=".WS_ID."
					  AND !`deleted`
					  AND `id`=".$move['income_id'];
			if($r = query_assoc($sql, GLOBAL_MYSQL_CONNECT)) {
				$sql = "UPDATE `_money_income`
						SET `deleted`=1,
							`viewer_id_del`=".VIEWER_ID.",
							`dtime_del`=CURRENT_TIMESTAMP
						WHERE `id`=".$r['id'];
				query($sql, GLOBAL_MYSQL_CONNECT);

				//баланс для расчётного счёта
				_balans(array(
					'action_id' => 2,
					'invoice_id' => $r['invoice_id'],
					'sum' => $r['sum'],
					'income_id' => $r['id']
				));
			}


		}

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
		$send['name'] = utf8(_zpName($name_id).' для '._deviceName($device_id, 1)._vendorName($vendor_id)._modelName($model_id));
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
		$about = 'Бонус по платежам, '.__viewerRules($worker_id, 'RULES_MONEY_PROCENT').'%:'.
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
		if(!$expense_id = _num($_POST['expense_id']))
			jsonError();

		$sql = "SELECT * FROM `zayav_expense` WHERE `id`=".$expense_id;
		if(!$r = query_assoc($sql))
			jsonError();

		$send['html'] = utf8(salary_worker_bonus_show($r));

		jsonSuccess($send);
		break;
}




