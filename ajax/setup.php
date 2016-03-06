<?php
switch(@$_POST['op']) {
	case 'info_save':
		if(!VIEWER_ADMIN)
			jsonError();
		if(!$type = _num($_POST['type']))
			jsonError();

		$ws_type_id = _app('ws_type_id');
		if($ws_type_id == $type)
			jsonError();

		$sql = "UPDATE `_app` SET `ws_type_id`=".$type." WHERE `id`=".APP_ID;
		query($sql, GLOBAL_MYSQL_CONNECT);

		_history(array(
			'type_id' => 1031,
			'v1' =>
				'<table>'.
					'<tr><td>'._wsType($ws_type_id).'<td>»<td>'._wsType($type).
				'</table>'
		));

		_cacheClear();
		jsonSuccess();
		break;
	case 'info_devs_set':
//		if(!RULES_INFO)
//			jsonError();

		if(!$ids = _ids($_POST['devs']))
			jsonError();

		$sql = "UPDATE `_app` SET `devs`='".$ids."' WHERE `id`=".APP_ID;
		query($sql, GLOBAL_MYSQL_CONNECT);


		jsonSuccess();
		break;
	case 'info_del':
		if(!VIEWER_ADMIN)
			jsonError();

		xcache_unset(CACHE_PREFIX.'viewer_'.VIEWER_ADMIN);

		$sql = "UPDATE `_app`
				SET `deleted`=1,
					`dtime_del`=CURRENT_TIMESTAMP
				WHERE `id`=".APP_ID;
		query($sql, GLOBAL_MYSQL_CONNECT);

		$sql = "UPDATE `_vkuser`
				SET `admin`=0,
					`worker`=0
				WHERE `app_id`=".APP_ID;
		query($sql, GLOBAL_MYSQL_CONNECT);

		_cacheClear();
		_globalCacheClear();

		_history(array(
			'type_id' => 1032
		));

		jsonSuccess();
		break;

	case 'cartridge_edit':
		if(!$id = _num($_POST['id']))
			jsonError();
		if(!$type_id = _num($_POST['type_id']))
			jsonError();
		$name = _txt($_POST['name']);
		$cost_filling = _num($_POST['cost_filling']);
		$cost_restore = _num($_POST['cost_restore']);
		$cost_chip = _num($_POST['cost_chip']);
		$join_id = _num($_POST['join_id']);

		if(empty($name))
			jsonError();
		if($join_id == $id)
			jsonError();

		$sql = "SELECT * FROM `setup_cartridge` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		if($join_id) {
			$sql = "SELECT * FROM `setup_cartridge` WHERE `id`=".$join_id;
			if(!$j = query_assoc($sql))
				jsonError();
			$sql = "UPDATE `zayav_cartridge`
					SET `cartridge_id`=".$id."
					WHERE `cartridge_id`=".$join_id;
			query($sql);
			$sql = "DELETE FROM `setup_cartridge` WHERE `id`=".$join_id;
			query($sql);

			_history(array(
				'type_id' => 1019,
				'v1' => $name,
				'v2' => $j['name']
			));
		}

		$sql = "UPDATE `setup_cartridge`
				SET `type_id`=".$type_id.",
					`name`='".addslashes($name)."',
					`cost_filling`=".$cost_filling.",
					`cost_restore`=".$cost_restore.",
					`cost_chip`=".$cost_chip."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'cartridge'.APP_ID);
		GvaluesCreate();

		$changes =
			_historyChange('Вид', _cartridgeType($r['type_id']), _cartridgeType($type_id)).
			_historyChange('Модель', $r['name'], $name).
			_historyChange('Заправка', $r['cost_filling'], $cost_filling).
			_historyChange('Восстановление', $r['cost_restore'], $cost_restore).
			_historyChange('Замена чипа', $r['cost_chip'], $cost_chip);

		if($changes)
			_history(array(
				'type_id' => 1034,
				'v1' => $name,
				'v2' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_service_cartridge_spisok($id));
		$send['cart'] = query_selArray("SELECT `id`,`name` FROM `setup_cartridge` WHERE `app_id`=".APP_ID." ORDER BY `name`");
		jsonSuccess($send);
		break;
}
