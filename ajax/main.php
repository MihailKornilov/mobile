<?php
require_once('config.php');

switch(@$_POST['op']) {
	case 'ws_create':
		if(!$org_name = _txt($_POST['org_name']))
			jsonError();
		if(!$country_id = _num($_POST['country_id']))
			jsonError();
		if(!$city_id = _num($_POST['city_id']))
			jsonError();
		if(!$devs = _ids($_POST['devs']))
			jsonError();

		$country_name = _txt($_POST['country_name']);
		$city_name = _txt($_POST['city_name']);

		$sql = "INSERT INTO `_ws` (
				`app_id`,
				`admin_id`,
				`name`,
				`country_id`,
				`country_name`,
				`city_id`,
				`city_name`
			) VALUES (
				".APP_ID.",
				".VIEWER_ID.",
				'".$org_name."',
				".$country_id.",
				'".addslashes($country_name)."',
				".$city_id.",
				'".addslashes($city_name)."'
			)";
		query($sql, GLOBAL_MYSQL_CONNECT);

		$sql = "SELECT `id`
				FROM `_ws`
				WHERE `app_id`=".APP_ID."
				  AND `admin_id`=".VIEWER_ID."
				ORDER BY `id` DESC
				LIMIT 1";
		$ws_id = query_value($sql, GLOBAL_MYSQL_CONNECT);

		$sql = "UPDATE `_vkuser`
				SET `ws_id`=".$ws_id.",
					`admin`=1,
					`worker`=1
				WHERE `app_id`=".APP_ID."
				  AND `viewer_id`=".VIEWER_ID;
		query($sql, GLOBAL_MYSQL_CONNECT);

		$sql = "INSERT INTO `setup` (
				`ws_id`,
				`devs`
			) VALUES (
				".$ws_id.",
				'".$devs."'
			)";
		query($sql);

		_cacheClear($ws_id);
		_globalCacheClear($ws_id);
		_globalValuesJS();

		jsonSuccess();
		break;

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

}

jsonError();