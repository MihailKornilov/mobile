<?php
if(!SA)
	jsonError();

switch(@$_POST['op']) {
	case 'tovar_category_add':
		$name = _txt($_POST['name']);
		if(empty($name))
			jsonError();
		$sql = "INSERT INTO `tovar_category` (
					`name`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					"._maxSql('tovar_category')."
				)";
		query($sql);

//		GvaluesCreate();

		$send['html'] = utf8(sa_tovar_category_spisok());
		jsonSuccess($send);
		break;
	case 'tovar_category_edit':
		if(!$id = _num($_POST['id']))
			jsonError();
		$name = _txt($_POST['name']);
		if(empty($name))
			jsonError();

		$sql = "UPDATE `tovar_category`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		//GvaluesCreate();

		$send['html'] = utf8(sa_tovar_category_spisok());
		jsonSuccess($send);
		break;
	case 'tovar_category_device_load':
		$sql = "SELECT `id`,`name` FROM `base_device` WHERE !`category_id` ORDER BY `sort`";
		$send['dev'] = query_selArray($sql);
		jsonSuccess($send);
		break;
	case 'tovar_category_device_add':
		if(!$id = _num($_POST['id']))
			jsonError();
		if(!$device_id = _num($_POST['device_id']))
			jsonError();

		$sql = "UPDATE `base_device`
				SET `category_id`=".$id."
				WHERE `id`=".$device_id;
		query($sql);

		//GvaluesCreate();

		$send['html'] = utf8(sa_tovar_category_spisok());
		jsonSuccess($send);
		break;
	case 'tovar_category_del':
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

	case 'model_spisok':
		$send['html'] = utf8(sa_model_spisok($_POST));
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
		if(query_value("SELECT COUNT(`id`) FROM `zp_catalog` WHERE `base_model_id`=".$model_id))
			jsonError();

		$sql = "DELETE FROM `base_model` WHERE `id`=".$model_id;
		query($sql);
		xcache_unset(CACHE_PREFIX.'model_name');
		GvaluesCreate();
		jsonSuccess();
		break;

	case 'equip_add':
		if(!$device_id = _num($_POST['device_id']))
			jsonError();
		$name = _txt($_POST['name']);
		if(empty($name))
			jsonError();
		$sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `setup_device_equip`");
		$sql = "INSERT INTO `setup_device_equip` (
					`name`,
					`sort`
				) VALUES (
					'".addslashes($name)."',
					".$sort."
				)";
		query($sql);
		xcache_unset(CACHE_PREFIX.'device_equip');
		$send['html'] = utf8(sa_equip_spisok($device_id));
		jsonSuccess($send);
		break;
	case 'equip_set'://Установка ids комплектаций для конктерного вида устройтсва
		if(!$device_id = _num($_POST['device_id']))
			jsonError();

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
		if(!$device_id = _num($_POST['device_id']))
			jsonError();
		$send['equip'] = utf8(sa_equip_spisok($device_id));
		$send['zp'] = utf8(sa_zpname_spisok($device_id));
		jsonSuccess($send);
		break;
	case 'equip_edit':
		if(!$id = _num($_POST['id']))
			jsonError();

		$name = _txt($_POST['name']);

		if(empty($name))
			jsonError();

		$sql = "UPDATE `setup_device_equip`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		xcache_unset(CACHE_PREFIX.'device_equip');

		jsonSuccess();
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

	case 'zpname_edit':
		if(!$id = _num($_POST['id']))
			jsonError();

		$name = _txt($_POST['name']);
		if(empty($name))
			jsonError();

		$sql = "SELECT * FROM `setup_zp_name` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "UPDATE `setup_zp_name`
				SET `name`='".addslashes($name)."'
				WHERE `id`=".$id;
		query($sql);

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'zp_name');

		jsonSuccess();
		break;
	case 'zpname_del':
		if(!$id = _num($_POST['id']))
			jsonError();

		$sql = "SELECT * FROM `setup_zp_name` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "SELECT COUNT(`id`) FROM `zp_catalog` WHERE `name_id`=".$id;
		if(query_value($sql))
			jsonError();

		$sql = "DELETE FROM `setup_zp_name` WHERE `id`=".$id;
		query($sql);

		GvaluesCreate();
		xcache_unset(CACHE_PREFIX.'zp_name');

		$send['html'] = utf8(sa_zpname_spisok($r['device_id']));
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
}