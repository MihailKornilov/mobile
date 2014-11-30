<?php
require_once('config.php');
require_once(API_PATH.'/vk_ajax.php');

switch(@$_POST['op']) {
	case 'cache_clear':
		if(!SA)
			jsonError();
		_cacheClear();
		jsonSuccess();
		break;

	case 'ws_create':
		$org_name = win1251(htmlspecialchars(trim($_POST['org_name'])));
		if(empty($org_name))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['country_id']))
			jsonError();
		if(!preg_match(REGEXP_NUMERIC, $_POST['city_id']))
			jsonError();

		$country_id = intval($_POST['country_id']);
		$city_id = intval($_POST['city_id']);
		$country_name = win1251(htmlspecialchars(trim($_POST['country_name'])));
		$city_name = win1251(htmlspecialchars(trim($_POST['city_name'])));

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
		query("UPDATE `vk_user` SET `ws_id`=".mysql_insert_id().",`admin`=1 WHERE `viewer_id`=".VIEWER_ID);
		_cacheClear();
		jsonSuccess();
		break;
}

jsonError();