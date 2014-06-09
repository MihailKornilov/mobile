<?php
function toMailSend() {
	echo "\n\n----\nExecution time: ".round(microtime(true) - TIME, 3);
	mail(CRON_MAIL, 'Cron mobile: zp_accrual.php', ob_get_contents());
}

define('CRON', true);
require_once dirname(dirname(__FILE__)).'/config.php';

set_time_limit(1800);
ob_start();
register_shutdown_function('toMailSend');

define('YEAR', strftime('%Y'));
define('MON', strftime('%m'));
define('DAY', intval(strftime('%d')));
$w = date('w', time());
define('WEEK', !$w ? 7 : $w);
define('ABOUT', 'Ставка за '._monthDef(MON).' '.YEAR);

echo 'week='.WEEK;
echo 'day='.DAY;

$sql = "SELECT * FROM `vk_user` WHERE `ws_id` AND `rate_sum`>0";
$q = query($sql);
while($r = mysql_fetch_assoc($q)) {
	$insert = 0;
	switch($r['rate_period']) {
		case 1:
			if($r['rate_day'] == DAY)
				$insert = 1;
			break;
		case 2:
			if($r['rate_day'] == WEEK)
				$insert = 1;
			break;
		case 3: $insert = 1; break;
	}
	if(!$insert)
		continue;
	$sql = "INSERT INTO `zayav_expense` (
				`ws_id`,
				`worker_id`,
				`sum`,
				`txt`,
				`year`,
				`mon`
			) VALUES (
				".$r['ws_id'].",
				".$r['viewer_id'].",
				".$r['rate_sum'].",
				'".ABOUT."',
				".YEAR.",
				".MON."
			)";
	query($sql);
	history_insert(array(
		'ws_id' => $r['ws_id'],
		'type' => 46,
		'value' => _cena($r['rate_sum']),
		'value1' => $r['viewer_id'],
		'value2' => ABOUT
	));
	echo _viewer($r['viewer_id'], 'name').': '._cena($r['rate_sum'])."\n";
}

mysql_close();
exit;