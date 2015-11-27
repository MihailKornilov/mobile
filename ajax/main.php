<?php
require_once('config.php');

switch(@$_POST['op']) {
	case 'ws_create':
		$org_name = win1251(htmlspecialchars(trim($_POST['org_name'])));
		if(empty($org_name))
			jsonError();
		if(!$country_id = _num($_POST['country_id']))
			jsonError();
		if(!$city_id = _num($_POST['city_id']))
			jsonError();

		$country_name = _txt($_POST['country_name']);
		$city_name = _txt($_POST['city_name']);

		$ex = explode(',', $_POST['devs']);
		foreach($ex as $id)
			if(!preg_match(REGEXP_NUMERIC, $id))
				jsonError();

		$sql = "INSERT INTO `workshop` (
				`admin_id`,
				`org_name`,
				`country_id`,
				`country_name`,
				`city_id`,
				`city_name`,
				`devs`
			) VALUES (
				".VIEWER_ID.",
				'".$org_name."',
				".$country_id.",
				'".$country_name."',
				".$city_id.",
				'".$city_name."',
				'".$_POST['devs']."'
			)";
		query($sql);

		$ws_id = query_value("SELECT `id` FROM `workshop` WHERE `admin_id`=".VIEWER_ID." ORDER BY `id` DESC LIMIT 1");

		query("UPDATE `vk_user` SET `ws_id`=".$ws_id.",`admin`=1 WHERE `viewer_id`=".VIEWER_ID);
		_cacheClear($ws_id);
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