<?php
define('DOCUMENT_ROOT', dirname(__FILE__));
require_once(DOCUMENT_ROOT.'/syncro.php');
require_once(DOCUMENT_ROOT.'/view/main.php');
require_once(API_PATH.'/vk.php');

require_once(DOCUMENT_ROOT.'/view/ws.php');
require_once(DOCUMENT_ROOT.'/view/ws_tovar.php');
require_once(DOCUMENT_ROOT.'/view/ws_zp.php');
require_once(DOCUMENT_ROOT.'/view/ws_report.php');
require_once(DOCUMENT_ROOT.'/view/ws_setup.php');
require_once(DOCUMENT_ROOT.'/view/sa.php');



//глобальные константы для конкретной организации
if(WS_ID) {
	$sql = "SELECT * FROM `setup` WHERE `ws_id`=".WS_ID." LIMIT 1";
	$setup = query_assoc($sql);
	define('WS_DEVS', $setup['devs']);
	define('WS_TYPE', $setup['ws_type_id']);
}

require_once API_PATH.'/nofunc.php';
