<?php
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'cp1251');
define('DOMAIN', defined('CRON') && CRON ? 'mobile.nyandoma.ru' : $_SERVER['SERVER_NAME']);
define('LOCAL', DOMAIN == 'mobile');

require_once(DOCUMENT_ROOT.'/syncro.php');
define('DATABASE', $mysql['database']);

$SA[2170788] = 1;// Корнилов Виталий
require_once(VKPATH.'/vk.php');
define('SA_VIEWER_ID', SA && @$_COOKIE['sa_viewer_id'] ? intval($_COOKIE['sa_viewer_id']) : 0);
//define('VIEWER_ID', SA_VIEWER_ID ? SA_VIEWER_ID : $_GET['viewer_id']);
_appAuth();

require_once(DOCUMENT_ROOT.'/view/main.php');
require_once(DOCUMENT_ROOT.'/view/ws.php');


_dbConnect();
_getSetupGlobal();
_getVkUser();


function _getSetupGlobal() {//Получение глобальных данных
	if(CRON)
		return;
	$key = CACHE_PREFIX.'setup_global';
	$g = xcache_get($key);
	if(empty($g)) {
		$sql = "SELECT * FROM `setup_global` LIMIT 1";
		$g = mysql_fetch_assoc(query($sql));
		xcache_set($key, $g, 86400);
	}
	define('VERSION', $g['script_style']);
	define('G_VALUES', $g['g_values']);
}//_getSetupGlobal()
function _getVkUser() {//Получение данных о пользователе
	if(CRON)
		return;
	$u = _viewer();
	define('WS_ID', $u['ws_id'] && _getWorkshop($u['ws_id']) ? $u['ws_id'] : 0);
	define('VIEWER_NAME', $u['name']);
	define('VIEWER_COUNTRY_ID', $u['country_id']);
	define('VIEWER_CITY_ID', $u['city_id']);
	define('VIEWER_ADMIN', $u['admin']);
	if(WS_ID)
		foreach(_viewerRules() as $key => $value)
			define($key, $value);
}//_getVkUser()
function _getWorkshop($ws_id) {//Получение данных о мастерской
	$ws = xcache_get(CACHE_PREFIX.'workshop_'.$ws_id);
	if(empty($ws)) {
		$sql = "SELECT * FROM `workshop` WHERE `id`=".$ws_id." AND `status`=1 LIMIT 1";
		$ws = mysql_fetch_assoc(query($sql));
		if(empty($ws))
			return false;
		xcache_set(CACHE_PREFIX.'workshop_'.$ws_id, $ws, 86400);
	}
	define('WS_DEVS', $ws['devs']);
	define('WS_ADMIN', $ws['admin_id']);
	return true;
}//_getWorkshop()