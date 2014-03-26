<?php
require_once('config.php');
if(!SA) jsonError();
require_once(DOCUMENT_ROOT.'/view/ws.php');
require_once(DOCUMENT_ROOT.'/view/sa.php');

switch(@$_POST['op']) {
	case 'ws_status_change':
		if(!preg_match(REGEXP_NUMERIC, $_POST['ws_id']))
			jsonError('Неверный id');
		$ws_id = intval($_POST['ws_id']);
		$sql = "SELECT * FROM `workshop` WHERE `id`=".$ws_id;
		if(!$ws = mysql_fetch_assoc(query($sql)))
			jsonError('Мастерской не существует');
		if($ws['status']) {
			query("UPDATE `workshop` SET `status`=0,`dtime_del`=CURRENT_TIMESTAMP WHERE `id`=".$ws_id);
			query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".$ws_id);
		} else {
			if(query_value("SELECT `ws_id` FROM `vk_user` WHERE `viewer_id`=".$ws['admin_id']))
				jsonError('За администратором закреплена другая мастерская');
			query("UPDATE `workshop` SET `status`=1,`dtime_del`='0000-00-00 00:00:00' WHERE `id`=".$ws_id);
			query("UPDATE `vk_user` SET `ws_id`=".$ws_id.",`admin`=1 WHERE `viewer_id`=".$ws['admin_id']);
			xcache_unset(CACHE_PREFIX.'viewer_'.$ws['admin_id']);
		}
		_cacheClear($ws_id);
		jsonSuccess();
		break;
	case 'ws_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['ws_id']))
			jsonError();
		$ws_id = intval($_POST['ws_id']);
		foreach(sa_ws_tables() as $tab => $about)
			query("DELETE FROM `".$tab."` WHERE `ws_id`=".$ws_id);
		query("DELETE FROM `workshop` WHERE `id`=".$ws_id);
		query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".$ws_id);
		_cacheClear($ws_id);
		jsonSuccess();
		break;
	case 'ws_client_balans':
		if(!preg_match(REGEXP_NUMERIC, $_POST['ws_id']))
			jsonError();
		$ws_id = intval($_POST['ws_id']);
		$sql = "SELECT
				  `c`.`id`,
				  `c`.`balans`,
				  IFNULL(SUM(`m`.`sum`),0) AS `money`
				FROM `client` AS `c`
				  LEFT JOIN `money` AS `m`
				  ON `m`.`deleted`=0
					AND `c`.`id`=`m`.`client_id`
					AND `m`.`sum`>0
				WHERE `c`.`ws_id`=".$ws_id."
				  AND `c`.`deleted`=0
				GROUP BY `c`.`id`
				ORDER BY `c`.`id`";
		$q = query($sql);
		$client = array();
		while($r = mysql_fetch_assoc($q))
			$client[$r['id']] = $r;
		$sql = "SELECT
				  `c`.`id`,
				  IFNULL(SUM(`a`.`sum`),0) AS `acc`
				FROM `client` AS `c`
				  LEFT JOIN `accrual` AS `a`
				  ON `a`.`status`=1
					AND `c`.`id`=`a`.`client_id`
				WHERE `c`.`ws_id`=".$ws_id."
				  AND `c`.`deleted`=0
				GROUP BY `c`.`id`
				ORDER BY `c`.`id`";
		$q = query($sql);
		$send['count'] = 0;
		$upd = array();
		while($r = mysql_fetch_assoc($q)) {
			$balans = $client[$r['id']]['money'] - $r['acc'];
			if($client[$r['id']]['balans'] != $balans) {
				$upd[] = '('.$r['id'].','.$balans.')';
				$send['count']++;
			}
		}
		if(!empty($upd)) {
			$sql = "INSERT INTO `client`
						(`id`,`balans`)
					VALUES ".implode(',', $upd)."
					ON DUPLICATE KEY UPDATE `balans`=VALUES(`balans`)";
			query($sql);
		}
		$send['time'] = round(microtime(true) - TIME, 3);
		jsonSuccess($send);
		break;
	case 'ws_zayav_balans':
		if(!preg_match(REGEXP_NUMERIC, $_POST['ws_id']))
			jsonError();
		$ws_id = intval($_POST['ws_id']);
		$sql = "SELECT
				  `z`.`id`,
				  `z`.`accrual_sum`,
				  IFNULL(SUM(`a`.`sum`),0) AS `acc`
				FROM `zayav` AS `z`
				  LEFT JOIN `accrual` AS `a`
				  ON `z`.`id`=`a`.`zayav_id`
					AND `a`.`status`=1
				WHERE `z`.`ws_id`=".$ws_id."
				GROUP BY `z`.`id`
				ORDER BY `z`.`id`";
		$q = query($sql);
		$zayav = array();
		while($r = mysql_fetch_assoc($q))
			$zayav[$r['id']] = $r;
		$sql = "SELECT
				  `z`.`id`,
				  `z`.`oplata_sum`,
				  IFNULL(SUM(`m`.`sum`),0) AS `opl`
				FROM `zayav` AS `z`
				  LEFT JOIN `money` AS `m`
				  ON `z`.`id`=`m`.`zayav_id`
					AND `m`.`deleted`=0
					AND `m`.`sum`>0
				WHERE `z`.`ws_id`=".$ws_id."
				GROUP BY `z`.`id`
				ORDER BY `z`.`id`";
		$q = query($sql);
		$send['count'] = 0;
		$upd = array();
		while($r = mysql_fetch_assoc($q)) {
			$z = $zayav[$r['id']];
			if($z['accrual_sum'] != $z['acc'] || $r['oplata_sum'] != $r['opl']) {
				$upd[] = '('.$r['id'].','.$z['acc'].','.$r['opl'].')';
				$send['count']++;
			}
		}
		if(!empty($upd)) {
			$sql = "INSERT INTO `zayav`
						(`id`,`accrual_sum`, `oplata_sum`)
					VALUES ".implode(',', $upd)."
					ON DUPLICATE KEY UPDATE
						`accrual_sum`=VALUES(`accrual_sum`),
						`oplata_sum`=VALUES(`oplata_sum`)";
			query($sql);
		}
		$send['time'] = round(microtime(true) - TIME, 3);
		jsonSuccess($send);
		break;

	case 'device_add':
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$name_rod = win1251(htmlspecialchars(trim($_POST['rod'])));
		$name_mn = win1251(htmlspecialchars(trim($_POST['mn'])));
		if(empty($name) || empty($name_rod) || empty($name_mn))
			jsonError();
		if(!empty($_POST['equip'])) {
			$ids = explode(',', $_POST['equip']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}
		$sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `base_device`");
		$sql = "INSERT INTO `base_device` (
					`name`,
					`name_rod`,
					`name_mn`,
					`equip`,
					`sort`,
					`viewer_id_add`
				) VALUES (
					'".addslashes($name)."',
					'".addslashes($name_rod)."',
					'".addslashes($name_mn)."',
					'".$_POST['equip']."',
					".$sort.",
					".VIEWER_ID."
				)";
		query($sql);
		xcache_unset(CACHE_PREFIX.'device_name');
		GvaluesCreate();
		$send['html'] = utf8(sa_device_spisok());
		jsonSuccess($send);
		break;
	case 'device_get':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT * FROM `base_device` WHERE `id`=".$id." LIMIT 1";
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$send['name'] = utf8($r['name']);
		$send['name_rod'] = utf8($r['name_rod']);
		$send['name_mn'] = utf8($r['name_mn']);
		$send['equip'] = utf8(devEquipCheck(0, $r['equip']));
		jsonSuccess($send);
		break;
	case 'device_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$name_rod = win1251(htmlspecialchars(trim($_POST['rod'])));
		$name_mn = win1251(htmlspecialchars(trim($_POST['mn'])));
		if(empty($name) || empty($name_rod) || empty($name_mn))
			jsonError();
		if(!empty($_POST['equip'])) {
			$ids = explode(',', $_POST['equip']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}
		$sql = "UPDATE `base_device` SET
					`name`='".addslashes($name)."',
					`name_rod`='".addslashes($name_rod)."',
					`name_mn`='".addslashes($name_mn)."',
					`equip`='".$_POST['equip']."'
				WHERE `id`=".$id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'device_name');
		GvaluesCreate();
		$send['html'] = utf8(sa_device_spisok());
		jsonSuccess($send);
		break;
	case 'device_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		if(query_value("SELECT COUNT(`id`) FROM `base_vendor` WHERE `device_id`=".$id))
			jsonError();
		if(query_value("SELECT COUNT(`id`) FROM `base_model` WHERE `device_id`=".$id))
			jsonError();
		if(query_value("SELECT COUNT(`id`) FROM `zayav` WHERE `base_device_id`=".$id))
			jsonError();
		$sql = "DELETE FROM `base_device` WHERE `id`=".$id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'device_name');
		GvaluesCreate();
		$send['html'] = utf8(sa_device_spisok());
		jsonSuccess($send);
		break;

	case 'vendor_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']) && $_POST['device_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['bold']))
			jsonError();

		$device_id = intval($_POST['device_id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$bold = intval($_POST['bold']);
		if(empty($name))
			jsonError();

		$sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `base_vendor` WHERE `device_id`=".$device_id);
		$sql = "INSERT INTO `base_vendor` (
					`device_id`,
					`name`,
					`bold`,
					`sort`,
					`viewer_id_add`
				) VALUES (
					".$device_id.",
					'".addslashes($name)."',
					".$bold.",
					".$sort.",
					".VIEWER_ID."
				)";
		query($sql);
		xcache_unset(CACHE_PREFIX.'vendor_name');
		GvaluesCreate();
		$send['html'] = utf8(sa_vendor_spisok($device_id));
		jsonSuccess($send);
		break;
	case 'vendor_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) && $_POST['vendor_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_BOOL, $_POST['bold']))
			jsonError();

		$vendor_id = intval($_POST['vendor_id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$bold = intval($_POST['bold']);
		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `base_vendor` WHERE `id`=".$vendor_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `base_vendor` SET
					`name`='".addslashes($name)."',
					`bold`='".$bold."'
				WHERE `id`=".$vendor_id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'vendor_name');
		GvaluesCreate();
		$send['html'] = utf8(sa_vendor_spisok($r['device_id']));
		jsonSuccess($send);
		break;
	case 'vendor_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) && $_POST['vendor_id'] == 0)
			jsonError();
		$vendor_id = intval($_POST['vendor_id']);

		$sql = "SELECT * FROM `base_vendor` WHERE `id`=".$vendor_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `base_model` WHERE `vendor_id`=".$vendor_id))
			jsonError();
		if(query_value("SELECT COUNT(`id`) FROM `zayav` WHERE `base_vendor_id`=".$vendor_id))
			jsonError();
		$sql = "DELETE FROM `base_vendor` WHERE `id`=".$vendor_id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'vendor_name');
		GvaluesCreate();
		$send['html'] = utf8(sa_vendor_spisok($r['device_id']));
		jsonSuccess($send);
		break;

	case 'model_next':
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) && $_POST['vendor_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
			jsonError();
		if(!preg_match(REGEXP_WORDFIND, win1251($_POST['find'])))
			$_POST['find'] = '';
		$vendor_id = intval($_POST['vendor_id']);
		$find = win1251(htmlspecialchars(trim($_POST['find'])));
		$page = intval($_POST['page']);
		$send['html'] = utf8(sa_model_spisok($vendor_id, $page, $find));
		jsonSuccess($send);
		break;
	case 'model_load':
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) && $_POST['vendor_id'] == 0)
			jsonError();
		if(!preg_match(REGEXP_WORDFIND, win1251($_POST['find'])))
			$_POST['find'] = '';
		$vendor_id = intval($_POST['vendor_id']);
		$find = win1251(htmlspecialchars(trim($_POST['find'])));
		$send['html'] = utf8(sa_model_spisok($vendor_id, 1, $find));
		jsonSuccess($send);
		break;
	case 'model_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) && $_POST['vendor_id'] == 0)
			jsonError('Некорректный vendor_id');

		$vendor_id = intval($_POST['vendor_id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError('Пустое наименование');

		$device_id = query_value("SELECT `device_id` FROM `base_vendor` WHERE `id`=".$vendor_id);
		if(!$device_id)
			jsonError('vendor_id нет в базе');

		$sql = "SELECT *
				FROM `base_model`
				WHERE `vendor_id`=".$vendor_id."
				  AND `name`='".$name."'";
		if(mysql_num_rows(query($sql)))
			jsonError('Такое название уже существует в списке');

		$sql = "INSERT INTO `base_model` (
					`device_id`,
					`vendor_id`,
					`name`,
					`viewer_id_add`
				) VALUES (
					".$device_id.",
					".$vendor_id.",
					'".addslashes($name)."',
					".VIEWER_ID."
				)";
		query($sql);
		xcache_unset(CACHE_PREFIX.'model_name');
		GvaluesCreate();
		$send['html'] = utf8(sa_model_spisok($vendor_id));
		jsonSuccess($send);
		break;
	case 'model_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']) && $_POST['model_id'] == 0)
			jsonError();

		$model_id = intval($_POST['model_id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `base_model` WHERE `id`=".$model_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "UPDATE `base_model` SET `name`='".addslashes($name)."' WHERE `id`=".$model_id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'model_name');
		GvaluesCreate();
		jsonSuccess();
		break;
	case 'model_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']) && $_POST['model_id'] == 0)
			jsonError();
		$model_id = intval($_POST['model_id']);

		$sql = "SELECT * FROM `base_model` WHERE `id`=".$model_id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zayav` WHERE `base_model_id`=".$model_id))
			jsonError();
		$sql = "DELETE FROM `base_model` WHERE `id`=".$model_id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'model_name');
		GvaluesCreate();
		jsonSuccess();
		break;

	case 'equip_add':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
			jsonError();
		$device_id = intval($_POST['device_id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$title = win1251(htmlspecialchars(trim($_POST['title'])));
		if(empty($name))
			jsonError();
		$sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `setup_device_equip`");
		$sql = "INSERT INTO `setup_device_equip` (
					`name`,
					`title`,
					`sort`,
					`viewer_id_add`
				) VALUES (
					'".addslashes($name)."',
					'".addslashes($title)."',
					".$sort.",
					".VIEWER_ID."
				)";
		query($sql);
		xcache_unset(CACHE_PREFIX.'device_equip');
		$send['html'] = utf8(sa_equip_spisok($device_id));
		jsonSuccess($send);
		break;
	case 'equip_set'://Установка ids комплектаций для конктерного вида устройтсва
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
			jsonError();
		$device_id = intval($_POST['device_id']);

		if(!empty($_POST['ids'])) {
			$ids = explode(',', $_POST['ids']);
			for($n = 0; $n < count($ids); $n++)
				if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
					jsonError();
		}

		$sql = "UPDATE `base_device` SET `equip`='".$_POST['ids']."' WHERE `id`=".$device_id;
		query($sql);
		jsonSuccess();
		break;
	case 'equip_show':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
			jsonError();
		$device_id = intval($_POST['device_id']);
		$send['html'] = utf8(sa_equip_spisok($device_id));
		jsonSuccess($send);
		break;
	case 'equip_get'://Получение данных для редактирования комплектации
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$id = intval($_POST['id']);
		$sql = "SELECT * FROM `setup_device_equip` WHERE `id`=".$id." LIMIT 1";
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();
		$send['name'] = utf8($r['name']);
		$send['title'] = utf8($r['title']);
		jsonSuccess($send);
		break;
	case 'equip_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$device_id = intval($_POST['device_id']);
		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		$title = win1251(htmlspecialchars(trim($_POST['title'])));
		if(empty($name))
			jsonError();
		$sql = "UPDATE `setup_device_equip`
				SET `name`='".addslashes($name)."',
					`title`='".addslashes($title)."'
				WHERE `id`=".$id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'device_equip');
		$send['html'] = utf8(sa_equip_spisok($device_id));
		jsonSuccess($send);
		break;
	case 'equip_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
			jsonError();
		$device_id = intval($_POST['device_id']);
		$id = intval($_POST['id']);
		$sql = "DELETE FROM `setup_device_equip` WHERE `id`=".$id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'device_equip');
		$send['html'] = utf8(sa_equip_spisok($device_id));
		jsonSuccess($send);
		break;

	case 'fault_add':
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `setup_fault` (
					`name`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					"._maxSql('setup_fault')."
				)";
		query($sql);

		GvaluesCreate();

		$send['html'] = utf8(sa_fault_spisok());
		jsonSuccess($send);
		break;
	case 'fault_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || !$_POST['id'])
			jsonError();
		$id = intval($_POST['id']);
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "UPDATE `setup_fault`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		GvaluesCreate();

		$send['html'] = utf8(sa_fault_spisok());
		jsonSuccess($send);
		break;
	case 'fault_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || !$_POST['id'])
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_fault` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		$sql = "DELETE FROM `setup_fault` WHERE `id`=".$id;
		query($sql);

		GvaluesCreate();

		$send['html'] = utf8(sa_fault_spisok());
		jsonSuccess($send);
		break;

	case 'color_add':
		$predlog = win1251(htmlspecialchars(trim($_POST['predlog'])));
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `setup_color_name` (
					`predlog`,
					`name`
				) VALUES (
					'".addslashes($predlog)."',
					'".addslashes($name)."'
				)";
		query($sql);

		xcache_unset(CACHE_PREFIX.'color_name');
		GvaluesCreate();

		$send['html'] = utf8(sa_color_spisok());
		jsonSuccess($send);
		break;
	case 'color_edit':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || !$_POST['id'])
			jsonError();
		$id = intval($_POST['id']);
		$predlog = win1251(htmlspecialchars(trim($_POST['predlog'])));
		$name = win1251(htmlspecialchars(trim($_POST['name'])));
		if(empty($name))
			jsonError();

		$sql = "UPDATE `setup_color_name`
				SET `predlog`='".addslashes($predlog)."',
					`name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'color_name');
		GvaluesCreate();

		$send['html'] = utf8(sa_color_spisok());
		jsonSuccess($send);
		break;
	case 'color_del':
		if(!preg_match(REGEXP_NUMERIC, $_POST['id']) || !$_POST['id'])
			jsonError();
		$id = intval($_POST['id']);

		$sql = "SELECT * FROM `setup_color_name` WHERE `id`=".$id;
		if(!$r = mysql_fetch_assoc(query($sql)))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zayav` WHERE `color_id`=".$id))
			jsonError();

		if(query_value("SELECT COUNT(`id`) FROM `zp_catalog` WHERE `color_id`=".$id))
			jsonError();

		$sql = "DELETE FROM `setup_color_name` WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'color_name');
		GvaluesCreate();

		$send['html'] = utf8(sa_color_spisok());
		jsonSuccess($send);
		break;
}

jsonError();