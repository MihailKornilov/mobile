<?php
require_once('config.php');

switch(@$_POST['op']) {
	case 'info_save':
		if(!RULES_INFO)
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['org_name'])));

		$sql = "SELECT * FROM `workshop` WHERE `id`=".WS_ID;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if($name == $r['org_name'])
			jsonError();

		query("UPDATE `workshop` SET `org_name`='".$name."' WHERE `id`=".WS_ID);

		history_insert(array(
			'type' => 1003,
			'value' => '<table><td>'.$r['org_name'].'<td>»<td>'.$name.'</table>'
		));

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
		xcache_unset(CACHE_PREFIX.'viewer_'.WS_ADMIN);
		query("UPDATE `workshop` SET `status`=0,`dtime_del`=CURRENT_TIMESTAMP WHERE `id`=".WS_ID);
		query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".WS_ID);
		_cacheClear();

		history_insert(array(
			'type' => 1004
		));

		jsonSuccess();
		break;

	case 'worker_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		$viewer_id = intval($_POST['viewer_id']);
		if($viewer_id) {
			$sql = "SELECT * FROM `vk_user` WHERE `viewer_id`=".$viewer_id." LIMIT 1";
			if($r = mysql_fetch_assoc(query($sql))) {
				if($r['ws_id'] == WS_ID)
					jsonError('Этот пользователь уже является</br >сотрудником этой мастерской.');
				if($r['ws_id'])
					jsonError('Этот пользователь уже является</br >сотрудником другой мастерской.');
			}
			_viewer($viewer_id);
			query("UPDATE `vk_user` SET `ws_id`=".WS_ID." WHERE `viewer_id`=".$viewer_id);
			xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		} else {
			if(!preg_match(REGEXP_NUMERIC, $_POST['sex']) || !$_POST['sex'])
				jsonError();
			$first_name = win1251(htmlspecialchars(trim($_POST['first_name'])));
			$last_name = win1251(htmlspecialchars(trim($_POST['last_name'])));
			$sex = intval($_POST['sex']);
			if(!$first_name || !$last_name)
				jsonError();
			$viewer_id = _maxSql('vk_user', 'viewer_id');
			if($viewer_id < VIEWER_MAX)
				$viewer_id = VIEWER_MAX;
			$sql = "INSERT INTO `vk_user` (
				`ws_id`,
				`viewer_id`,
				`first_name`,
				`last_name`,
				`sex`,
				`photo`
			) VALUES (
				".WS_ID.",
				".$viewer_id.",
				'".addslashes($first_name)."',
				'".addslashes($last_name)."',
				".$sex.",
				'http://vk.com/images/camera_c.gif'
			)";
			query($sql);
		}

		history_insert(array(
			'type' => 1001,
			'value' => $viewer_id
		));

		GvaluesCreate();

		$send['html'] = utf8(setup_worker_spisok());
		jsonSuccess($send);
		break;
	case 'worker_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();
		$viewer_id = intval($_POST['viewer_id']);
		$sql = "SELECT * FROM `vk_user` WHERE `ws_id`=".WS_ID." AND `viewer_id`=".$viewer_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		if($r['viewer_id'] == WS_ADMIN)
			jsonError();

		query("UPDATE `vk_user` SET `ws_id`=0 WHERE `viewer_id`=".$viewer_id);
		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		GvaluesCreate();

		history_insert(array(
			'type' => 1002,
			'value' => $viewer_id
		));

		$send['html'] = utf8(setup_worker_spisok());
		jsonSuccess($send);
		break;
	case 'worker_name_save':
		if(!RULES_WORKER)
			jsonError();
		if(!$viewer_id = _isnum($_POST['viewer_id']))
			jsonError();

		$u = _viewer($viewer_id);
		if($u['ws_id'] != WS_ID)
			jsonError();

		$first_name = win1251(htmlspecialchars(trim($_POST['first_name'])));
		$last_name = win1251(htmlspecialchars(trim($_POST['last_name'])));

		$sql = "UPDATE `vk_user`
				SET `first_name`='".addslashes($first_name)."',
					`last_name`='".addslashes($last_name)."'
				WHERE `viewer_id`=".$viewer_id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
		GvaluesCreate();

		jsonSuccess();
		break;
	case 'worker_dop_save':
		if(!RULES_WORKER)
			jsonError();
		if(!$viewer_id = _isnum($_POST['viewer_id']))
			jsonError();

		$u = _viewer($viewer_id);
		if($u['ws_id'] != WS_ID)
			jsonError();

		setup_worker_rules_save($_POST, $viewer_id);
		jsonSuccess();
		break;
	case 'worker_rules_save':
		if(!RULES_RULES)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['viewer_id']))
			jsonError();

		$viewer_id = intval($_POST['viewer_id']);

		$u = _viewer($viewer_id);
		if($u['admin'])
			jsonError();
		if($u['ws_id'] != WS_ID)
			jsonError();

		setup_worker_rules_save($_POST, $viewer_id);
		jsonSuccess();
		break;

	case 'cartridge_toggle'://подключение-отключение услуги заправки картриджей
		if(!WS_ADMIN)
			jsonError();

		$v = _isbool($_POST['v']);

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
		$name = _txt($_POST['name']);
		$cost_filling = _isnum($_POST['cost_filling']);
		$cost_restore = _isnum($_POST['cost_restore']);
		$cost_chip = _isnum($_POST['cost_chip']);

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_cartridge` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `setup_cartridge`
				SET `name`='".addslashes($name)."',
					`cost_filling`=".$cost_filling.",
					`cost_restore`=".$cost_restore.",
					`cost_chip`=".$cost_chip."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'cartridge'.WS_ID);
		GvaluesCreate();

		$changes = '';
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

		$send['html'] = utf8(setup_service_cartridge_spisok());
		jsonSuccess($send);
		break;
	case 'cartridge_del':
		if(!$id = _num($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `setup_cartridge` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

jsonError();//todo временно удаления нет

		query("DELETE FROM `setup_cartridge` WHERE `id`=".$id);

		xcache_unset(CACHE_PREFIX.'cartridge'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1019,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_service_cartridge_spisok());
		jsonSuccess($send);
		break;

	case 'invoice_add':
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `invoice` (
					`ws_id`,
					`name`,
					`about`
				) VALUES (
					".WS_ID.",
					'".addslashes($name)."',
					'".addslashes($about)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'invoice'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1008,
			'value' => $name
		));

		$send['html'] = utf8(setup_invoice_spisok());
		jsonSuccess($send);
		break;
	case 'invoice_edit':
		if(!$invoice_id = _num($_POST['id']))
			jsonError();
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$about = win1251(htmlspecialchars(trim($_POST['about'])));
		if(empty($name))
			jsonError();


		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `invoice`
				SET `name`='".addslashes($name)."',
					`about`='".addslashes($about)."'
				WHERE `id`=".$invoice_id;
		query($sql);


		xcache_unset(CACHE_PREFIX.'invoice'.WS_ID);
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['about'] != $about)
			$changes .= '<tr><th>Описание:<td>'.str_replace("\n", '<br />', $r['about']).'<td>»<td>'.str_replace("\n", '<br />', $about);
		if($changes)
			history_insert(array(
				'type' => 1009,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_invoice_spisok());
		jsonSuccess($send);
		break;
	case 'invoice_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$invoice_id = intval($_POST['id']);

		$sql = "SELECT * FROM `invoice` WHERE `id`=".$invoice_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		query("DELETE FROM `invoice` WHERE `id`=".$invoice_id);
		query("UPDATE `setup_income` SET `invoice_id`=0 WHERE `invoice_id`=".$invoice_id);

		xcache_unset(CACHE_PREFIX.'invoice'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1010,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_invoice_spisok());
		jsonSuccess($send);
		break;

	case 'expense_add':
		if(!preg_match(REGEXP_BOOL, $_POST['show_worker']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$show_worker = intval($_POST['show_worker']);

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `setup_expense` (
					`ws_id`,
					`name`,
					`show_worker`,
					`sort`
				) VALUES (
					".WS_ID.",
					'".addslashes($name)."',
					".$show_worker.",
					"._maxSql('setup_expense')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'expense'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1005,
			'value' => $name
		));

		$send['html'] = utf8(setup_expense_spisok());
		jsonSuccess($send);
		break;
	case 'expense_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['show_worker']))
			jsonError();

		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$show_worker = intval($_POST['show_worker']);

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_expense` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `setup_expense`
				SET `name`='".addslashes($name)."',
					`show_worker`=".$show_worker."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'expense'.WS_ID);
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['show_worker'] != $show_worker)
			$changes .= '<tr><th>Список сотрудников:<td>'.($r['show_worker'] ? 'да' : 'нет').'<td>»<td>'.($show_worker ? 'да' : 'нет');
		if($changes)
			history_insert(array(
				'type' => 1006,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_expense_spisok());
		jsonSuccess($send);
		break;
	case 'expense_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_expense` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `money` WHERE `expense_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_expense` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'expense'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1007,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_expense_spisok());
		jsonSuccess($send);
		break;

	case 'zayav_expense_add':
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$dop = _isnum($_POST['dop']);

		if(empty($name))
			jsonError();

		$sql = "INSERT INTO `setup_zayav_expense` (
					`ws_id`,
					`name`,
					`dop`,
					`sort`
				) VALUES (
					".WS_ID.",
					'".addslashes($name)."',
					".$dop.",
					"._maxSql('setup_zayav_expense', 'sort')."
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayav_expense'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1014,
			'value' => $name
		));

		$send['html'] = utf8(setup_zayav_expense_spisok());
		jsonSuccess($send);
		break;
	case 'zayav_expense_edit':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$dop = _isnum($_POST['dop']);

		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_zayav_expense` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `setup_zayav_expense`
				SET `name`='".addslashes($name)."',
					`dop`=".$dop."
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayav_expense'.WS_ID);
		GvaluesCreate();

		$changes = '';
		if($r['name'] != $name)
			$changes .= '<tr><th>Наименование:<td>'.$r['name'].'<td>»<td>'.$name;
		if($r['dop'] != $dop)
			$changes .= '<tr><th>Дополнительное поле:'.
							'<td>'.($r['dop'] ? _zayavExpenseDop($r['dop']) : '').
							'<td>»'.
							'<td>'.($dop ? _zayavExpenseDop($dop) : '');
		if($changes)
			history_insert(array(
				'type' => 1015,
				'value' => $name,
				'value1' => '<table>'.$changes.'</table>'
			));

		$send['html'] = utf8(setup_zayav_expense_spisok());
		jsonSuccess($send);
		break;
	case 'zayav_expense_del':
		if(!$id = _isnum($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `setup_zayav_expense` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zayav_expense` WHERE `category_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `setup_zayav_expense` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'zayav_expense'.WS_ID);
		GvaluesCreate();

		history_insert(array(
			'type' => 1016,
			'value' => $r['name']
		));

		$send['html'] = utf8(setup_zayav_expense_spisok());
		jsonSuccess($send);
		break;
}

jsonError();