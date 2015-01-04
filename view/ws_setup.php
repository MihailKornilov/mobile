<?php

// ---===! setup !===--- ������ ��������

function setup() {
	$pages = array(
		'my' => '��� ���������',
		'info' => '���������� � ����������',
		'worker' => '����������',
		'invoice' => '�����',
		'income' => '���� ��������',
		'expense' => '��������� ��������',
		'zayavexpense' => '������� �� ������'
	);
	if(!RULES_INFO)
		unset($pages['info']);
	if(!RULES_WORKER)
		unset($pages['worker']);
	if(!RULES_INCOME) {
		unset($pages['invoice']);
		unset($pages['income']);
	}

	$d = empty($_GET['d']) ? 'my' : $_GET['d'];

	switch($d) {
		default: $d = 'my';
		case 'my': $left = '��� ���������'; break;
		case 'info': $left = setup_info(); break;
		case 'worker':
			if(!empty($_GET['id']) && preg_match(REGEXP_NUMERIC, $_GET['id'])) {
				$left = setup_worker_rules(intval($_GET['id']));
				break;
			}
			$left = setup_worker();
			break;
		case 'invoice': $left = setup_invoice(); break;
		case 'income': $left = setup_income(); break;
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
	$sql = "SELECT * FROM `workshop` WHERE `id`=".WS_ID." LIMIT 1";
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
	'<div id="setup_info">'.
		'<div class="headName">�������� ����������</div>'.
		'<TABLE class="tab">'.
			'<TR><TD class="label">�������� �����������:<TD><INPUT type="text" id="org_name" maxlength="100" value="'.$ws['org_name'].'">'.
			'<TR><TD class="label">�����:<TD>'.$ws['city_name'].', '.$ws['country_name'].
			'<TR><TD class="label">������� �������������:<TD><B>'._viewer($ws['admin_id'], 'name').'</B>'.
			'<TR><TD><TD><div class="vkButton" id="info_save"><button>���������</button></div>'.
		'</TABLE>'.

		'<div class="headName">��������� ������������� ���������</div>'.
		'<div id="devs">'.$checkDevs.'</div>'.

		'<div class="headName">�������� ����������</div>'.
		'<div class="del_inf">����������, � ����� ��� ������ ��������� ��� ����������� ��������������.</div>'.
		'<div class="vkButton" id="info_del"><button>������� ����������</button></div>'.
	'</div>';
}//setup_info()

function setup_worker() {
	return
	'<div id="setup_worker">'.
		'<div class="headName">���������� ������������<a class="add">����� ���������</a></div>'.
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
				'<td>'.($r['viewer_id'] == WS_ADMIN ? '' : '<div class="img_del'._tooltip('������� ����������', -66).'</div>').
					'<a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'" class="name">'.$r['name'].'</a>'.
					($r['enter_last'] != '0000-00-00 00:00:00' ? '<div class="activity">�������'.($r['sex'] == 1 ? 'a' : '').' � ���������� '.FullDataTime($r['enter_last']).'</div>' : '').
		'</table>';
	}
	return $send;
}//setup_worker_spisok()
function _setupRules($rls, $admin=0) {
	$rules = array(
		'RULES_GETMONEY' => array(	// ����� ��������� � ���������� ������:
			'def' => 0
		),
		'RULES_MONEY_PROCENT' => array(	// ������� �� ��������
			'def' => 0
		),
		'RULES_APPENTER' => array(	// ��������� ���� � ����������
			'def' => 0,
			'admin' => 1,
			'childs' => array(
				'RULES_INFO' => array(	    // ���������� � ����������
					'def' => 0,
					'admin' => 1
				),
				'RULES_WORKER' => array(	// ����������
					'def' => 0,
					'admin' => 1
				),
				'RULES_RULES' => array(	    // ��������� ���� �����������
					'def' => 0,
					'admin' => 1
				),
				'RULES_INCOME' => array(	// ����� � ���� ��������
					'def' => 0,
					'admin' => 1
				),
				'RULES_HISTORYSHOW' => array(// ����� ������� ��������
					'def' => 0,
					'admin' => 1
				),
				'RULES_HISTORYTRANSFER' => array(// ����� ������� ���������
					'def' => 0,
					'admin' => 1
				),
				'RULES_MONEY' => array(	    // ����� ������ �������: ������ ����, ��� �������
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
		return '���������� �� ����������.';
	$rule = _viewerRules($viewer_id);
	return
	'<script type="text/javascript">var RULES_VIEWER_ID='.$viewer_id.';</script>'.
	'<div id="setup_rules">'.

		'<table class="utab">'.
			'<tr><td>'.$u['photo'].
			'<td><div class="name">'.$u['name'].'</div>'.
			($viewer_id < VIEWER_MAX ? '<a href="http://vk.com/id'.$viewer_id.'" class="vklink" target="_blank">������� �� �������� VK</a>' : '').
		'</table>'.

		'<div class="headName">�����</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">���:<td><input type="text" id="first_name" value="'.$u['first_name'].'" />'.
			'<tr><td class="lab">�������:<td><input type="text" id="last_name" value="'.$u['last_name'].'" />'.
			'<tr><td><td><div class="vkButton g-save"><button>���������</button></div>'.
		'</table>'.

		'<div class="headName">�������������</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">����� ���������<br />� ���������� ������:<td>'._check('rules_getmoney', '', $rule['RULES_GETMONEY']).
			'<tr><td class="lab">������� �� ��������:<td><input type="text" id="rules_money_procent" value="'.$rule['RULES_MONEY_PROCENT'].'" maxlength="2" />'.
			'<tr><td><td><div class="vkButton dop-save"><button>���������</button></div>'.
		'</table>'.

	(!$u['admin'] && $viewer_id < VIEWER_MAX && RULES_RULES ?
		'<div class="headName">�����</div>'.
		'<table class="rtab">'.
			'<tr><td class="lab">��������� ����<br />� ����������:<td>'._check('rules_appenter', '', $rule['RULES_APPENTER']).
		'</table>'.
		'<div class="app-div'.($rule['RULES_APPENTER'] ? '' : ' dn').'">'.
			'<table class="rtab">'.
				'<tr><td class="lab top">���������� �����������:'.
					'<td class="setup-div">'.
						_check('rules_rekvisit', '���������� � ����������', $rule['RULES_INFO']).
						_check('rules_worker', '����������', $rule['RULES_WORKER']).
						_check('rules_rules', '��������� ���� �����������', $rule['RULES_RULES']).
						_check('rules_income', '����� � ���� ��������', $rule['RULES_INCOME']).
				'<tr><td class="lab">����� ������� ��������:<td>'._check('rules_historyshow', '', $rule['RULES_HISTORYSHOW']).
				'<tr><td class="lab">����� ������� ���������:<td>'._check('rules_historytransfer', '', $rule['RULES_HISTORYTRANSFER']).
				'<tr><td class="lab">����� ������ �������:<td><input type="hidden" id="rules_money" value="'.$rule['RULES_MONEY'].'" />'.
			'</table>'.
			'</div>'.
			'<table class="rtab">'.
				'<tr><td class="lab"><td><div class="vkButton rules-save"><button>���������</button></div>'.
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
		'<div class="headName">���������� �������<a class="add">����� ����</a></div>'.
		'<div class="spisok">'.setup_invoice_spisok().'</div>'.
	'</div>';
}//setup_invoice()
function setup_invoice_spisok() {
	$sql = "SELECT * FROM `invoice` WHERE `ws_id`=".WS_ID." ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;

	$sql = "SELECT *
	        FROM `setup_income`
	        WHERE `ws_id`=".WS_ID."
	          AND `invoice_id`
	        ORDER BY `sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$spisok[$r['invoice_id']]['type_name'][] = $r['name'];
		$spisok[$r['invoice_id']]['type_id'][] = $r['id'];
	}

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">������������'.
				'<th class="type">���� ��������'.
				'<th class="set">';
	foreach($spisok as $id => $r)
		$send .=
			'<tr val="'.$id.'">'.
				'<td class="name">'.
					'<div>'.$r['name'].'</div>'.
					'<pre>'.$r['about'].'</pre>'.
				'<td class="type">'.
					(isset($r['type_name']) ? implode('<br />', $r['type_name']) : '').
					'<input type="hidden" class="type_id" value="'.(isset($r['type_id']) ? implode(',', $r['type_id']) : 0).'" />'.
				'<td class="set">'.
					'<div class="img_edit'._tooltip('��������', -33).'</div>';
					//'<div class="img_del"></div>'
	$send .= '</table>';
	return $send;
}//setup_invoice_spisok()

function setup_income() {
	return
	'<div id="setup_income">'.
		'<div class="headName">��������� ����� ��������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_income_spisok().'</div>'.
	'</div>';
}//setup_income()
function setup_income_spisok() {
	$sql = "SELECT `i`.*,
				   COUNT(`m`.`id`) AS `money`
			FROM `setup_income` AS `i`
			  LEFT JOIN `money` AS `m`
			  ON `i`.`id`=`m`.`income_id`
			WHERE `i`.`ws_id`=".WS_ID."
			GROUP BY `i`.`id`
			ORDER BY `i`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$income = array();
	while($r = mysql_fetch_assoc($q))
		$income[$r['id']] = $r;

	$sql = "SELECT `i`.`id`,
				   COUNT(`m`.`id`) AS `del`
			FROM `setup_income` AS `i`,
				 `money` AS `m`
			WHERE `i`.`ws_id`=".WS_ID."
			  AND `i`.`id`=`m`.`income_id`
			  AND `m`.`deleted`
			GROUP BY `i`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$income[$r['id']]['del'] = $r['del'];

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">������������'.
				'<th class="money">���-��<br />��������'.
				'<th class="set">'.
		'</table>'.
		'<dl class="_sort" val="setup_income">';
	foreach($income as $id => $r) {
		$money = $r['money'] ? '<b>'.$r['money'].'</b>' : '';
		$money .= isset($r['del']) ? ' <span class="del" title="� ��� ����� ��������">('.$r['del'].')</span>' : '';
		$send .='<dd val="'.$id.'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="money">'.$money.
					'<td class="set">'.
						'<div class="img_edit'._tooltip('��������', -33).'</div>'.
						(!$r['money'] && $id > 1 ? '<div class="img_del'._tooltip('�������', -29).'</div>' : '').
			'</table>';
	}
	$send .= '</dl>';
	return $send;
}//setup_income_spisok()

function setup_expense() {
	return
	'<div id="setup_expense">'.
		'<div class="headName">��������� �������� ����������<a class="add">����� ���������</a></div>'.
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
		return '������ ����.';

	$send =
		'<table class="_spisok">'.
			'<tr><th class="name">������������'.
				'<th class="worker">����������<br />������<br />�����������'.
				'<th class="use">���-��<br />�������'.
				'<th class="set">'.
		'</table>'.
		'<dl class="_sort" val="setup_expense">';

	while($r = mysql_fetch_assoc($q))
		$send .='<dd val="'.$r['id'].'">'.
			'<table class="_spisok">'.
				'<tr><td class="name">'.$r['name'].
					'<td class="worker">'.($r['show_worker'] ? '��' : '').
					'<td class="use">'.($r['use'] ? $r['use'] : '').
					'<td class="set">'.
						'<div class="img_edit'._tooltip('��������', -33).'</div>'.
						(!$r['use'] ? '<div class="img_del"></div>' : '').
			'</table>';
	$send .= '</dl>';
	return $send;
}//setup_expense_spisok()

function setup_zayav_expense() {
	return
	'<div id="setup_zayav_expense">'.
		'<div class="headName">��������� ��������� �������� �� ������<a class="add">��������</a></div>'.
		'<div class="spisok">'.setup_zayav_expense_spisok().'</div>'.
	'</div>';
}//setup_zayav_expense()
function setup_zayav_expense_spisok() {
	$sql = "SELECT `s`.*,
				   COUNT(`z`.`id`) AS `use`
			FROM `setup_zayav_expense` AS `s`
			  LEFT JOIN `zayav_expense` AS `z`
			  ON `s`.`id`=`z`.`category_id`
			GROUP BY `s`.`id`
			ORDER BY `s`.`sort`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '������ ����.';

	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[$r['id']] = $r;

	$send =
	'<table class="_spisok">'.
		'<tr><th class="name">������������'.
			'<th class="dop">�������������� ����'.
			'<th class="use">���-��<br />�������'.
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
