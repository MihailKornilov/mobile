<?php
define('TIME', microtime(true));
define('DOCUMENT_ROOT', dirname(__FILE__));
define('NAMES', 'cp1251');
define('DOMAIN', $_SERVER["SERVER_NAME"]);
define('VIEWER_ID', $_GET['viewer_id']);
define('VALUES', 'viewer_id='.VIEWER_ID.
                 '&api_id='.@$_GET['api_id'].
                 '&auth_key='.@$_GET['auth_key'].
                 '&sid='.@$_GET['sid']);
define('SITE', 'http://'.DOMAIN);
define('URL', SITE.'/index.php?'.VALUES);

define('REGEXP_NUMERIC', '/^[0-9]{1,20}$/i');
define('REGEXP_DATE', '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/');

$SA[982006] = 1;  // Корнилов Михаил
$SA[2170788] = 1; // Корнилов Виталий
define('ADMIN', isset($SA[$_GET['viewer_id']]));
if(ADMIN) {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
}

$dbConnect = mysql_connect($mysql['host'], $mysql['user'], $mysql['pass'], 1) or die("Can't connect to database");
mysql_select_db($mysql['database'], $dbConnect) or die("Can't select database");
$sqlQuery = 0;
query('SET NAMES `'.NAMES.'`', $dbConnect);

$sql = "SELECT * FROM `setup_global` LIMIT 1";
$G = mysql_fetch_assoc(query($sql));
define('VERSION', $G['script_style']);
define('G_VALUES', $G['g_values']);

function query($sql) {
    global $sqlQuery;
    $res = mysql_query($sql) or die($sql);
    $sqlQuery++;
    return $res;
}