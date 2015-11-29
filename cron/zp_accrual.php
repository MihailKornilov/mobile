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

$sql = "SELECT *
		FROM `_vkuser`
		WHERE `app_id`=".APP_ID."
		  AND `ws_id`
		  AND `salary_rate_sum`>0";
$q = query($sql, GLOBAL_MYSQL_CONNECT);
while($r = mysql_fetch_assoc($q)) {
	$insert = 0;
	switch($r['salary_rate_period']) {
		case 1:
			if($r['salary_rate_day'] == DAY)
				$insert = 1;
			break;
		case 2:
			if($r['salary_rate_day'] == WEEK)
				$insert = 1;
			break;
		case 3: $insert = 1; break;
	}
	if(!$insert)
		continue;
	$sql = "INSERT INTO `_salary_accrual` (
				`app_id`,
				`ws_id`,
				`worker_id`,
				`sum`,
				`about`,
				`year`,
				`mon`
			) VALUES (
				".APP_ID.",
				".$r['ws_id'].",
				".$r['viewer_id'].",
				".$r['salary_rate_sum'].",
				'".ABOUT."',
				".YEAR.",
				".MON."
			)";
	query($sql, GLOBAL_MYSQL_CONNECT);

	_balans(array(
		'action_id' => 19,
		'worker_id' => $r['viewer_id'],
		'sum' => $r['salary_rate_sum']
	));

	_history(array(
		'ws_id' => $r['ws_id'],
		'type_id' => 46,
		'worker_id' => $r['viewer_id'],
		'v1' => _cena($r['salary_rate_sum']),
		'v2' => ABOUT
	));
	echo _viewer($r['viewer_id'], 'viewer_name').': '._cena($r['salary_rate_sum'])."\n";
}

exit;