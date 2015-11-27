<?php
switch(@$_POST['op']) {
	case 'info_save':
		if(!VIEWER_ADMIN)
			jsonError();
		if(!$type = _num($_POST['type']))
			jsonError();

		$sql = "SELECT * FROM `workshop` WHERE `status` AND `id`=".WS_ID;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($r['type'] == $type)
			jsonError();

		query("UPDATE `workshop` SET `type`=".$type." WHERE `id`=".WS_ID);

		history_insert(array(
			'type' => 1021,
			'value' =>
				'<table>'.
					'<tr><td>'._wsType($r['type']).'<td>»<td>'._wsType($type).
				'</table>'
		));

		_cacheClear();
		jsonSuccess();
		break;
	case 'info_devs_set':
		if(!RULES_INFO)
			jsonError();
		foreach(explode(',', $_POST['devs']) as $id)
			if(!preg_match(REGEXP_NUMERIC, $id))
				jsonError();
		query("UPDATE `workshop` SET `devs`='".$_POST['devs']."' WHERE `id`=".WS_ID);
		xcache_unset(CACHE_PREFIX.'workshop_'.WS_ID);
		jsonSuccess();
		break;
	case 'info_del':
		if(!VIEWER_ADMIN)
			jsonError();
		xcache_unset(CACHE_PREFIX.'viewer_'.VIEWER_ADMIN);
		query("UPDATE `workshop` SET `status`=0,`dtime_del`=CURRENT_TIMESTAMP WHERE `id`=".WS_ID);
		query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".WS_ID);
		_cacheClear();

		history_insert(array(
			'type' => 1004
		));

		jsonSuccess();
		break;


	case 'cartridge_toggle'://подключение-отключение услуги заправки картриджей
		if(!VIEWER_ADMIN)
			jsonError();

		$v = _bool($_POST['v']);

		$old = query_value("SELECT `service_cartridge` FROM `workshop` WHERE `id`=".WS_ID);
		if($old == $v)
			jsonError();

		query("UPDATE `workshop` SET `service_cartridge`=".$v." WHERE `id`=".WS_ID);
		xcache_unset(CACHE_PREFIX.'workshop_'.WS_ID);

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
			history_insert(array(
				'type' => 1019,
				'value' => $name,
				'value1' => $j['name']
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

		xcache_unset(CACHE_PREFIX.'cartridge'.WS_ID);
		GvaluesCreate();

		$changes = '';
		if($r['type_id'] != $type_id)
			$changes .= '<tr><th>Вид:<td>'._cartridgeType($r['type_id']).'<td>»<td>'._cartridgeType($type_id);
		if($r['name'] != $name)
			$changes .= '<tr><th>Модель:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['cost_filling'] != $cost_filling)
			$changes .= '<tr><th>Заправка:<td>'.$r['cost_filling'].'<td>»<td>'.$cost_filling;
		if($r['cost_restore'] != $cost_restore)
			$changes .= '<tr><th>Восстановление:<td>'.$r['cost_restore'].'<td>»<td>'.$cost_restore;
		if($r['cost_chip'] != $cost_chip)
			$changes .= '<tr><th>Замена чипа:<td>'.$r['cost_chip'].'<td>»<td>'.$cost_chip;
		if($changes)
			history_insert(array(
				'type' => 1018,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_service_cartridge_spisok($id));
		$send['cart'] = query_selArray("SELECT `id`,`name` FROM `setup_cartridge` WHERE `ws_id`=".WS_ID." ORDER BY `name`");
		jsonSuccess($send);
		break;
}
