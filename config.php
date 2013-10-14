<?php
define('TIME', microtime(true));
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'cp1251');
define('DOMAIN', $_SERVER["SERVER_NAME"]);
define('LOCAL', DOMAIN == 'vkmobile');
define('VIEWER_ID', $_GET['viewer_id']);
define('VALUES', 'viewer_id='.VIEWER_ID.
    '&api_id='.@$_GET['api_id'].
    '&auth_key='.@$_GET['auth_key'].
    '&sid='.@$_GET['sid']);
define('SITE', 'http://'.DOMAIN);
define('URL', SITE.'/index.php?'.VALUES);

require_once(DOCUMENT_ROOT.'/syncro.php');
require_once(DOCUMENT_ROOT.'/view/main.php');

define('REGEXP_NUMERIC', '/^[0-9]{1,20}$/i');
define('REGEXP_CENA', '/^[0-9]{1,6}(.[0-9]{1,2})?$/i');
define('REGEXP_BOOL', '/^[0-1]$/');
define('REGEXP_DATE', '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/');
define('REGEXP_YEAR', '/^[0-9]{4}$/');
define('REGEXP_WORD', '/^[a-z0-9]{1,10}$/i');
define('REGEXP_WORDFIND', '/^[a-zа-я0-9,.;]{1,}$/i');

$SA[982006] = 1; // Корнилов Михаил
$SA[2170788] = 1; // Корнилов Виталий
define('ADMIN', isset($SA[VIEWER_ID]));
if(ADMIN) {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
}

//Включает работу куков в IE через фрейм
header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

_dbConnect();
_getSetupGlobal();
_getVkUser();
_getWorkshop();




function _dbConnect() {
    global $mysql, $sqlQuery;
    $dbConnect = mysql_connect($mysql['host'], $mysql['user'], $mysql['pass'], 1) or die("Can't connect to database");
    mysql_select_db($mysql['database'], $dbConnect) or die("Can't select database");
    $sqlQuery = 0;
    query('SET NAMES `'.NAMES.'`', $dbConnect);
}//end of _dbConnect()
function query($sql) {
    global $sqlQuery, $sqls;
    $sqls .= $sql.'<br /><br />';
    $res = mysql_query($sql) or die($sql);
    $sqlQuery++;
    return $res;
}
function query_value($sql) {
    if(!$r = mysql_fetch_row(query($sql)))
        return false;
    return $r[0];
}
function query_selJson($sql) {
    $send = array();
    $q = query($sql);
    while($sp = mysql_fetch_row($q))
        $send[] = '{uid:'.$sp[0].',title:"'.$sp[1].'"}';
    return '['.implode(',',$send).']';
}
function query_ptpJson($sql) {//Ассоциативный массив
    $send = array();
    $q = query($sql);
    while($sp = mysql_fetch_row($q))
        $send = $sp[0].':'.(preg_match(REGEXP_NUMERIC, $sp[1]) ? $sp[1] : '"'.$sp[1].'"');
    return '{'.implode(',', $send).'}';
}

function _getSetupGlobal() {//Получение глобальных данных
    $g = xcache_get('vkmobile_setup_global');
    if(empty($g)) {
        $sql = "SELECT * FROM `setup_global` LIMIT 1";
        $g = mysql_fetch_assoc(query($sql));
        xcache_set('vkmobile_setup_global', $g, 86400);
    }
    define('VERSION', $g['script_style']);
    define('G_VALUES', $g['g_values']);
}//end of _getSetupGlobal()
function _getVkUser() {//Получение данных о пользователе
    $key = 'vkmobile_viewer_'.VIEWER_ID;
    $u = xcache_get($key);
    if(empty($u)) {
        $sql = "SELECT * FROM `vk_user` WHERE `viewer_id`='".VIEWER_ID."' LIMIT 1";
        if(!$u = mysql_fetch_assoc(query($sql)))
            $u = _vkUserUpdate();
        xcache_set($key, $u, 86400);
    }
    define('WS_ID', $u['ws_id']);
    define('VIEWER_NAME', $u['first_name'].' '.$u['last_name']);
    define('VIEWER_COUNTRY_ID', $u['country_id']);
    define('VIEWER_CITY_ID', $u['city_id']);
    define('VIEWER_ADMIN', ($u['admin'] == 1));
}//end of _getVkUser()
function _getWorkshop() {//Получение данных о мастерской
    $ws = xcache_get('vkmobile_workshop_'.WS_ID);
    if(empty($ws)) {
        $sql = "SELECT * FROM `workshop` WHERE `id`=".WS_ID." AND `status`=1 LIMIT 1";
        $ws = mysql_fetch_assoc(query($sql));
        xcache_set('vkmobile_workshop_'.WS_ID, $ws, 86400);
    }
    define('WS_DEVS', $ws['devs']);
    define('KASSA_START', $ws['kassa_start']);
}//end of _getWorkshop()