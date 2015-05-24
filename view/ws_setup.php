<?php

// ---===! setup !===--- Секция настроек

function setup() {
	$pages = array(
		'my' => 'Мои настройки',
		'info' => 'Основная информация',
		'rekvisit' => 'Реквизиты организации',
		'service' => 'Виды услуг',
		'worker' => 'Сотрудники',
		'invoice' => 'Счета',
		'expense' => 'Категории расходов',
		'zayavexpense' => 'Расходы по заявке'
	);
	if(!RULES_INFO)
		unset($pages['info']);
	if(!RULES_WORKER)
		unset($pages['worker']);
	if(!WS_ADMIN)
		unset($pages['service']);
	if(!RULES_INVOICE)
		unset($pages['invoice']);

	$d = empty($_GET['d']) ? 'my' : $_GET['d'];

	switch($d) {
		default: $d = 'my';
		case 'my': $left = 'Мои настройки'; break;
		case 'info': $left = setup_info(); break;
		case 'rekvisit': $left = setup_rekvisit(); break;
		case 'worker':
			if($id = _isnum(@$_GET['id'])) {
				$left = setup_worker_rules($id);
				break;
			}
			$left = setup_worker();
			break;
		case 'service':
			if(@$_GET['d1'] == 'cartridge') {
				$left = setup_service_cartridge();
				break;
			}
			$left = setup_service();
			break;
		case 'invoice': $left = setup_invoice(); break;
		case 'expense': $left = setup_expense(); break;
		case 'zayavexpense': $left = setup_zayav_expense(); break;
	}
	$links = '';
	foreach($pages as $p => $name)
		$links .= '<a href="'.URL.'&p=setup&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';
	return
	'<div id="setup">'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$left.
				'<td class="right"><div class="rightLink">'.$links.'</div>'.
		'</table>'.
	'</div>';
}//setup()

function setup_info() {
	$sql = "SELECT * FROM `workshop` WHERE `status` AND `id`=".WS_ID;
	if(!$ws = mysql_fetch_assoc(query($sql))) {
		_cacheClear();
		header('Location:'.URL);
	}

	$devs = array();
	foreach(explode(',', $ws['devs']) as $d)
		$devs[$d] = $d;

	$sql = "SELECT `id`,`name_mn` FROM `base_device` ORDER BY `sort`";
	$q = query($sql);
	$checkDevs = '';
	while($r = mysql_fetch_assoc($q))
		$checkDevs .= _check($r['id'], $r['name_mn'], isset($devs[$r['id']]) ? 1 : 0);
	return
	'<script type="text/javascript">'.
		'var WS_TYPE='._selJson(_wsType()).';'.
	'</script>'.
	'<div id="setup_info">'.
		'<div class="headName">Основная информация</div>'.
		'<table class="tab">'.
			'<tr><td class="label">Название организации:<td><b>'.$ws['org_name'].'</b>'.
			'<tr><td class="label">Город:<td>'.$ws['city_name'].', '.$ws['country_name'].
			'<tr><td class="label">Главный администратор:<td>'._viewer($ws['admin_id'], 'name').
			'<tr><td class="label">Вид организации:<td><input type="hidden" id="type" value="'.$ws['type'].'" />'.
			'<tr><td><td>'._button('info_save', 'Сохранить').
		'</table>'.

		'<div class="headName">Категории ремонтируемых устройств</div>'.
		'<div id="devs">'.$checkDevs.'</div>'.

		'<div class="headName">Удаление '._wsType($ws['type'], 2).'</div>'.
		'<div class="del_inf">'._wsType($ws['type']).', а также все данные удаляются без возможности восстановления.</div>'.
		_button('info_del', 'Удалить '._wsType($ws['type'], 4)).
	'</div>';
}//setup_info()

function setup_rekvisit() {
	$sql = "SELECT * FROM `workshop` WHERE `id`=".WS_ID;
	$g = query_assoc($sql);
	return
		'<div id="setup_rekvisit">'.
			'<div class="headName">Реквизиты организации</div>'.
			'<table class="t">'.
				'<tr><td class="label top">Название организации:<td><textarea id="org_name">'.$g['org_name'].'</textarea>'.
				'<tr><td class="label">ОГРН:<td><input type="text" id="ogrn" value="'.$g['ogrn'].'" />'.
				'<tr><td class="label">ИНН:<td><input type="text" id="inn" value="'.$g['inn'].'" />'.
				'<tr><td class="label">КПП:<td><input type="text" id="kpp" value="'.$g['kpp'].'" />'.
				'<tr><td class="label top">Юридический адрес:<td><textarea id="adres_yur">'.$g['adres_yur'].'</textarea>'.
				'<tr><td class="label">Телефоны:<td><input type="text" id="telefon" value="'.$g['telefon'].'" />'.
				'<tr><td class="label top">Адрес офиса:<td><textarea id="adres_ofice">'.$g['adres_ofice'].'</textarea>'.
				'<tr><td class="label">Режим работы:<td><input type="text" id="time_work" value="'.$g['time_work'].'" />'.
			'</table>'.
			'<div class="headName">Банк получателя</div>'.
			'<table class="t">'.
				'<tr><td class="label top">Наименование банка:<td><textarea id="bank_name">'.$g['bank_name'].'</textarea>'.
				'<tr><td class="label">БИК:<td><input type="text" id="bik" value="'.$g['bik'].'" />'.
				'<tr><td class="label">Расчётный счёт:<td><input type="text" id="schet" value="'.$g['schet'].'" />'.
				'<tr><td class="label">Корреспондентский счёт:<td><input type="text" id="kor_schet" value="'.$g['kor_schet'].'" />'.
				'<tr><td><td><div class="vkButton"><button>Сохранить</button></div>'.
			'</table>'.
		'</div>';
}//setup_rekvisit()

function setup_service() {
	$r = query_assoc("SELECT * FROM `workshop` WHERE `id`=".WS_ID);
	return
	'<div id="setup-service">'.
		'<div class="headName">Виды оказываемых услуг</div>'.

		'<div class="unit'.($r['service_device'] ? ' on' : '').'">'.
			'<h1>Ремонт электронного оборудования</h1>'.
			'<h2>Приём в ремонт и на профилактическое обслуживание оборудования и электронных устройств: '.
				'<ul><li>компьютеров;'.
					'<li>ноутбуков;'.
					'<li>планшетов;'.
					'<li>мобильных телефонов;'.
					'<li>принтеров;'.
					'<li>фотоаппаратов;'.
					'<li>др.'.
				'</ul>'.
			'</h2>'.
//			'<h4><a>Настроить</a></h4>'.
		'</div>'.

		'<div class="unit'.($r['service_cartridge'] ? ' on' : '').'">'.
			'<h1>Заправка картриджей</h1>'.
			'<h2>Заправка, восстановление картриджей от лазерных принтеров, копиров и МФУ. Замена чипов, фотовалов.</h2>'.
			'<h3><a class="s-cartridge-toggle">Включить</a></h3>'.
			'<h4>'.
				'<a href="'.URL.'&p=setup&d=service&d1=cartridge">Настроить</a> :: '.
				'<a class="s-cartridge-toggle off">Отключить</a>'.
			'</h4>'.
		'</div>'.

	'</div>';
}//setup_service()
function setup_service_cartridge() {
	return
		'<div id="setup-service-cartridge">'.
			'<a href="'.URL.'&p=setup&d=service" id="back"><< назад к <b>Видам услуг</b></a>'.
			'<div class="headName">Управление заправкой картриджей<a class="add">Внести новый картридж</a></div>'.
			'<div id="spisok">'.setup_service_cartridge_spisok().'</div>'.
		'</div>';
}//setup_service_cartridge()
function setup_service_cartridge_spisok($edit_id=0) {
	$sql = "SELECT `s`.*,
				   COUNT(`z`.`id`) `count`
			FROM `setup_cartridge` `s`
			  LEFT JOIN `zayav_cartridge` AS `z`
			  ON `s`.`id`=`z`.`cartridge_id`
			WHERE `ws_id`=".WS_ID."
			GROUP BY `s`.`id`
			ORDER BY `name`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Список пуст.';

	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;

	$send =
		'<table class="_spisok">'.
			'<tr><th class="n">№'.
				'<th class="name">Модель'.
				'<th class="cost">Вид услуги:<br />Запр./восст./чип'.
				'<th class="count">Кол-во'.
				'<th class="set">';
	$n = 1;
	foreach($spisok as $id => $r) {
		$cost = array();
		if($r['cost_filling'])
			$cost[] = '<span class="'._tooltip('Заправка', -30).$r['cost_filling'].'</span>';
		if($r['cost_restore'])
			$cost[] = '<span class="'._tooltip('Восстановление', -48).$r['cost_restore'].'</span>';
		if($r['cost_chip'])
			$cost[] = '<span class="'._tooltip('Замена чипа', -40).$r['cost_chip'].'</span>';
		$send .=
			'<tr'.($edit_id == $r['id'] ? ' class="edited"' : '').'>'.
				'<td class="n">'.($n++).
				'<td class="name">'.$r['name'].
				'<td class="cost">'.implode(' / ', $cost).
					'<input type="hidden" class="filling" value="'.$r['cost_filling'].'" />'.
					'<input type="hidden" class="restore" value="'.$r['cost_restore'].'" />'.
					'<input type="hidden" class="chip" value="'.$r['cost_chip'].'" />'.
				'<td class="count">'.($r['count'] ? $r['count'] : '').
				'<td class="set">'.
					'<div val="'.$id.'" class="img_edit'._tooltip('Изменить', -33).'</div>';
	}
	$send .= '</table>';
	return $send;
}//setup_service_cartridge_spisok()


function setup_worker() {
	return
	'<div id="setup_worker">'.
		'<div class="headName">Управление сотрудниками<a class="add">Новый сотрудник</a></div>'.
		'<div id="spisok">'.setup_worker_spisok().'</div>'.
	'</div>';
}//setup_worker()
function setup_worker_spisok() {
	$sql = "SELECT *,
				   CONCAT(`first_name`,' ',`last_name`) AS `name`
			FROM `vk_user`
			WHERE `ws_id`=".WS_ID."
			ORDER BY `dtime_add`";
	$q = query($sql);
	$send = '';
	while($r = mysql_fetch_assoc($q)) {
		$send .=
		'<table class="unit" val="'.$r['viewer_id'].'">'.
			'<tr><td class="photo"><a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'"><img src="'.$r['photo'].'"></a>'.
				'<td>'.($r['viewer_id'] == WS_ADMIN ? '' : '<div class="img_del'._tooltip('Удалить сотрудника', -66).'</div>').
					'<a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'" class="name">'.$r['name'].'</a>'.
					($r['enter_last'] != '0000-00-00 00:00:00' ? '<div class="activity">Заходил'.($r['sex'] == 1 ? 'a' : '').' в приложение '.FullDataTime($r['enter_last']).'</div>' : '').
		'</table>';
	}
	return $send;
}//setup_worker_spisok()
function _setupRules($rls, $admin=0) {
	$rules = array(
		'RULES_MONEY_PROCENT' => array(	// процент от платежей
			'def' => 0
		),
		'RULES_APPENTER' => array(	// Разрешать вход в приложение
			'def' => 0,
			'admin' => 1,
			'childs' => array(
				'RULES_INFO' => array(	    // Информация о мастерской
					'def' => 0,
					'admin' => 1
				),
				'RULES_WORKER' => array(	// Сотрудники
					'def' => 0,
					'admin' => 1
				),
				'RULES_RULES' => array(	    // Настройка прав сотрудников
					'def' => 0,
					'admin' => 1
				),
				'RULES_INVOICE' => array(	// Счета
					'def' => 0,
					'admin' => 1
				),
				'RULES_HISTORYSHOW' => array(// Видит историю действий
					'def' => 0,
					'admin' => 1
				),
				'RULES_HISTORYTRANSFER' => array(// Видит историю переводов
					'def' => 0,
					'admin' => 1
				),
				'RULES_MONEY' => array(	    // Может видеть платежи: только свои, все платежи
					'def' => 0,
					'admin' => 1
				)
			)
		)
	);
	$ass = array();
	foreach($rules as $i => $r) {
		$ass[$i] = $admin && isset($r['admin']) ? $r['admin'] : (isset($rls[$i]) ? $rls[$i] : $r['def']);
		//$parent = $ass[$i];
		if(isset($r['childs']))
			foreach($r['childs'] as $ci => $cr)
				$ass[$ci] = $admin && isset($cr['admin']) ? $cr['admin'] : (isset($rls[$ci]) ? $rls[$ci] : $cr['def']);
	}
	return $ass;
}//_setupRules()
function setup_worker_rules($viewer_id) {
	$u = _viewer($viewer_id);
	if($u['ws_id'] != WS_ID)
		return 'Сотрудника не существует.';
	$rule = _viewerRules($viewer_id);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.

		'<table class="utab">'.
			'<tr><td>'.$u['photo'].
			'<td><div class="name">'.$u['name'].'</div>'.
			($viewer_id < VIEWER_MAX ? '<a href="http://vk.com/id'.$viewer_id.'" class="vklink" target="_blank">Перейти на страницу VK</a>' : '').
		'</table>'.

		'<div class="headName">Общее</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">Имя:<td><input type="text" id="first_name" value="'.$u['first_name'].'" />'.
			'<tr><td class="lab">Фамилия:<td><input type="text" id="last_name" value="'.$u['last_name'].'" />'.
			'<tr><td><td><div class="vkButton g-save"><button>Сохранить</button></div>'.
		'</table>'.

		'<div class="headName">Дополнительно</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">Процент от платежей:<td><input type="text" id="rules_money_procent" value="'.$rule['RULES_MONEY_PROCENT'].'" maxlength="2" />'.
			'<tr><td><td><div class="vkButton dop-save"><button>Сохранить</button></div>'.
		'</table>'.

	(!$u['admin'] && $viewer_id < VIEWER_MAX && RULES_RULES ?
		'<div class="headName">Права</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">Разрешать вход<br />в приложение:<td>'._check('rules_appenter', '', $rule['RULES_APPENTER']).
		'</table>'.
		'<div class="app-div'.($rule['RULES_APPENTER'] ? '' : ' dn').'">'.
			'<table class="rtab">'.
				'<tr><td class="lab top">Управление установками:'.
					'<td class="setup-div">'.
						_check('rules_rekvisit', 'Информация об организации', $rule['RULES_INFO']).
						_check('rules_worker', 'Сотрудники', $rule['RULES_WORKER']).
						_check('rules_rules', 'Настройка прав сотрудников', $rule['RULES_RULES']).
						_check('rules_invoice', 'Счета', $rule['RULES_INVOICE']).
				'<tr><td class="lab">Видит историю действий:<td>'._check('rules_historyshow', '', $rule['RULES_HISTORYSHOW']).
				'<tr><td class="lab">Видит историю переводов:<td>'._check('rules_historytransfer', '', $rule['RULES_HISTORYTRANSFER']).
				'<tr><td class="lab">Может видеть платежи:<td><input type="hidden" id="rules_money" value="'.$rule['RULES_MONEY'].'" />'.
			'</table>'.
			'</div>'.
			'<table class="rtab">'.
				'<tr><td class="lab"><td><div class="vkButton rules-save"><button>Сохранить</button></div>'.
			'</table>'
	: '').
	'</div>';
}//setup_worker_rules()
function setup_worker_rules_save($post, $viewer_id) {
	$rules = array();
	foreach($post as $i => $v)
		if(preg_match('/^rules_/', $i))
			if(!preg_match(REGEXP_NUMERIC, $v))
				jsonError();
			else
				$rules[strtoupper($i)] = $v;

	$cur = query_ass("SELECT `key`,`value` FROM `vk_user_rules` WHERE `viewer_id`=".$viewer_id);
	$rules += $cur;
	foreach($rules as $i => $v)
		if(isset($cur[$i]))
			query("UPDATE `vk_user_rules` SET `value`=".$v." WHERE `key`='".$i."' AND `viewer_id`=".$viewer_id);
		else
			query("INSERT INTO `vk_user_rules` (
						`viewer_id`,
						`key`,
						`value`
					  ) VALUES (
					    ".$viewer_id.",
					    '".$i."',
					    ".$v."
					  )");
	xcache_unset(CACHE_PREFIX.'viewer_'.$viewer_id);
	xcache_unset(CACHE_PREFIX.'viewer_rules_'.$viewer_id);
}//setup_worker_rules_save()

function setup_invoice() {
	return
	'<div id="setup_invoice">'.
		'<div class="headName">Управление счетами<a class="add">Новый счёт</a></div>'.
		'<div class="spisok">'.setup_invoice_spisok().'</div>'.
	'</div>';
}//setup_invoice()
function setup_invoice_spisok() {
	$sql = "SELECT * FROM `invoice` WHERE `ws_id`=".WS_ID." ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Список пуст.';

	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">Наименование'.
				'<th class="set">';
	foreach($spisok as $id => $r)
		$send .=
			'<tr val="'.$id.'">'.
				'<td class="name">'.
					'<div>'.$r['name'].'</div>'.
					'<pre>'.$r['about'].'</pre>'.
				'<td class="set">'.
					'<div class="img_edit'._tooltip('Изменить', -33).'</div>';
					//'<div class="img_del"></div>'
	$send .= '</table>';
	return $send;
}//setup_invoice_spisok()

function setup_expense() {
	return
	'<div id="setup_expense">'.
		'<div class="headName">Категории расходов организации<a class="add">Новая категория</a></div>'.
		'<div id="spisok">'.setup_expense_spisok().'</div>'.
	'</div>';
}//setup_expense()
function setup_expense_spisok() {
	$sql = "SELECT `s`.*,
				   COUNT(`m`.`id`) AS `use`
			FROM `setup_expense` AS `s`
			  LEFT JOIN `money` AS `m`
			  ON `s`.`id`=`m`.`expense_id` AND !`m`.`deleted`
			WHERE `s`.`ws_id`=".WS_ID."
			GROUP BY `s`.`id`
			ORDER BY `s`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Список пуст.';

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">Наименование'.
				'<th class="worker">Показывать<br />список<br />сотрудников'.
				'<th class="use">Кол-во<br />записей'.
				'<th class="set">'.
		'</table>'.
		'<dl class="_sort" val="setup_expense">';

	while($r = mysql_fetch_assoc($q))
		$send .='<dd val="'.$r['id'].'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="worker">'.($r['show_worker'] ? 'да' : '').
					'<td class="use">'.($r['use'] ? $r['use'] : '').
					'<td class="set">'.
						'<div class="img_edit'._tooltip('Изменить', -33).'</div>'.
						(!$r['use'] ? '<div class="img_del"></div>' : '').
			'</table>';
	$send .= '</dl>';
	return $send;
}//setup_expense_spisok()

function setup_zayav_expense() {
	return
	'<div id="setup_zayav_expense">'.
		'<div class="headName">Настройки категорий расходов по заявке<a class="add">Добавить</a></div>'.
		'<div class="spisok">'.setup_zayav_expense_spisok().'</div>'.
	'</div>';
}//setup_zayav_expense()
function setup_zayav_expense_spisok() {
	$sql = "SELECT `s`.*,
				   COUNT(`z`.`id`) AS `use`
			FROM `setup_zayav_expense` AS `s`
			  LEFT JOIN `zayav_expense` AS `z`
			  ON `z`.`ws_id`=".WS_ID." AND `s`.`id`=`z`.`category_id`
			WHERE `s`.`ws_id`=".WS_ID."
			GROUP BY `s`.`id`
			ORDER BY `s`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return 'Список пуст.';

	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[$r['id']] = $r;

	$send =
	'<table class="_spisok">'.
		'<tr><th class="name">Наименование'.
			'<th class="dop">Дополнительное поле'.
			'<th class="use">Кол-во<br />записей'.
			'<th class="set">'.
	'</table>'.
	'<dl class="_sort" val="setup_zayav_expense">';
	foreach($expense as $id => $r)
		$send .=
		'<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="dop">'.
						($r['dop'] ? _zayavExpenseDop($r['dop']) : '').
						'<input class="hdop" type="hidden" value="'.$r['dop'].'" />'.
					'<td class="use">'.($r['use'] ? $r['use'] : '').
					'<td class="set">'.
						'<div class="img_edit"></div>'.
						(!$r['use'] ? '<div class="img_del"></div>' : '').
			'</table>';
	$send .= '</dl>';
	return $send;
}//setup_zayav_expense_spisok()
