<?php
require_once('config.php');

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
					'<tr><td>'._wsType($r['type']).'<td>�<td>'._wsType($type).
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
		xcache_unset(CACHE_PREFIX.'viewer_'.WS_ADMIN);
		query("UPDATE `workshop` SET `status`=0,`dtime_del`=CURRENT_TIMESTAMP WHERE `id`=".WS_ID);
		query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".WS_ID);
		_cacheClear();

		history_insert(array(
			'type' => 1004
		));

		jsonSuccess();
		break;

	case 'rekvisit':
		$org_name = _txt($_POST['org_name']);
		$ogrn = _txt($_POST['ogrn']);
		$inn = _txt($_POST['inn']);
		$kpp = _txt($_POST['kpp']);
		$adres_yur = _txt($_POST['adres_yur']);
		$telefon = _txt($_POST['telefon']);
		$adres_ofice = _txt($_POST['adres_ofice']);
		$time_work = _txt($_POST['time_work']);
		$schet = _txt($_POST['schet']);
		$bank_name = _txt($_POST['bank_name']);
		$bik = _txt($_POST['bik']);
		$kor_schet = _txt($_POST['kor_schet']);

		$sql = "SELECT * FROM `workshop` WHERE `id`=".WS_ID;
		$g = query_assoc($sql);

		$sql = "UPDATE `workshop`
				SET `org_name`='".addslashes($org_name)."',
					`ogrn`='".addslashes($ogrn)."',
					`inn`='".addslashes($inn)."',
					`kpp`='".addslashes($kpp)."',
					`adres_yur`='".addslashes($adres_yur)."',
					`telefon`='".addslashes($telefon)."',
					`adres_ofice`='".addslashes($adres_ofice)."',
					`time_work`='".addslashes($time_work)."',
					`schet`='".addslashes($schet)."',
					`bank_name`='".addslashes($bank_name)."',
					`bik`='".addslashes($bik)."',
					`kor_schet`='".addslashes($kor_schet)."'
				WHERE `id`=".WS_ID;
		query($sql);

		$changes = '';
		if($g['org_name'] != $org_name)
			$changes .= '<tr><th>�������� �����������:<td>'.$g['org_name'].'<td>�<td>'.$org_name;
		if($g['ogrn'] != $ogrn)
			$changes .= '<tr><th>����:<td>'.$g['ogrn'].'<td>�<td>'.$ogrn;
		if($g['inn'] != $inn)
			$changes .= '<tr><th>���:<td>'.$g['inn'].'<td>�<td>'.$inn;
		if($g['kpp'] != $kpp)
			$changes .= '<tr><th>���:<td>'.$g['kpp'].'<td>�<td>'.$kpp;
		if($g['adres_yur'] != $adres_yur)
			$changes .= '<tr><th>����������� �����:<td>'.$g['adres_yur'].'<td>�<td>'.$adres_yur;
		if($g['telefon'] != $telefon)
			$changes .= '<tr><th>��������:<td>'.$g['telefon'].'<td>�<td>'.$telefon;
		if($g['adres_ofice'] != $adres_ofice)
			$changes .= '<tr><th>����� �����:<td>'.$g['adres_ofice'].'<td>�<td>'.$adres_ofice;
		if($g['time_work'] != $time_work)
			$changes .= '<tr><th>����� ������:<td>'.$g['time_work'].'<td>�<td>'.$time_work;
		if($g['schet'] != $schet)
			$changes .= '<tr><th>��������� ����:<td>'.$g['schet'].'<td>�<td>'.$schet;
		if($g['bank_name'] != $bank_name)
			$changes .= '<tr><th>������������ �����:<td>'.$g['bank_name'].'<td>�<td>'.$bank_name;
		if($g['bik'] != $bik)
			$changes .= '<tr><th>���:<td>'.$g['bik'].'<td>�<td>'.$bik;
		if($g['kor_schet'] != $kor_schet)
			$changes .= '<tr><th>����������������� ����:<td>'.$g['kor_schet'].'<td>�<td>'.$kor_schet;
		if($changes)
			history_insert(array(
				'type' => 1020,
				'value' => '<table>'.$changes.'</table>'
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
					jsonError('���� ������������ ��� ��������</br >����������� ���� �����������.');
				if($r['ws_id'])
					jsonError('���� ������������ ��� ��������</br >����������� ������ �����������.');
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
		if(!$viewer_id = _num($_POST['viewer_id']))
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
		if(!$viewer_id = _num($_POST['viewer_id']))
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

	case 'cartridge_toggle'://�����������-���������� ������ �������� ����������
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
			$changes .= '<tr><th>���:<td>'._cartridgeType($r['type_id']).'<td>�<td>'._cartridgeType($type_id);
		if($r['name'] != $name)
			$changes .= '<tr><th>������:<td>'.$r['name'].'<td>�<td>'.$name;
		if($r['cost_filling'] != $cost_filling)
			$changes .= '<tr><th>��������:<td>'.$r['cost_filling'].'<td>�<td>'.$cost_filling;
		if($r['cost_restore'] != $cost_restore)
			$changes .= '<tr><th>��������������:<td>'.$r['cost_restore'].'<td>�<td>'.$cost_restore;
		if($r['cost_chip'] != $cost_chip)
			$changes .= '<tr><th>������ ����:<td>'.$r['cost_chip'].'<td>�<td>'.$cost_chip;
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
			$changes .= '<tr><th>������������:<td>'.$r['name'].'<td>�<td>'.$name;
		if($r['about'] != $about)
			$changes .= '<tr><th>��������:<td>'.str_replace("\n", '<br />', $r['about']).'<td>�<td>'.str_replace("\n", '<br />', $about);
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
			$changes .= '<tr><th>������������:<td>'.$r['name'].'<td>�<td>'.$name;
		if($r['show_worker'] != $show_worker)
			$changes .= '<tr><th>������ �����������:<td>'.($r['show_worker'] ? '��' : '���').'<td>�<td>'.($show_worker ? '��' : '���');
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
		$dop = _num($_POST['dop']);

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
		if(!$id = _num($_POST['id']))
			jsonError();

		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$dop = _num($_POST['dop']);

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
			$changes .= '<tr><th>������������:<td>'.$r['name'].'<td>�<td>'.$name;
		if($r['dop'] != $dop)
			$changes .= '<tr><th>�������������� ����:'.
							'<td>'.($r['dop'] ? _zayavExpenseDop($r['dop']) : '').
							'<td>�'.
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
		if(!$id = _num($_POST['id']))
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