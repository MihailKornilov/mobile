<?php

// ---===! setup !===--- ������ ��������

function setup() {
	return array(
		'info' => '�������� ����������',
		'service' => '���� �����'
	);
/*
	if(!RULES_INFO)
		unset($page['info']);
	if(!RULES_WORKER)
		unset($page['worker']);
	if(!VIEWER_ADMIN)
		unset($page['service']);
	if(!RULES_INVOICE)
		unset($page['invoice']);
*/
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
		'<div class="headName">�������� ����������</div>'.
		'<table class="tab">'.
			'<tr><td class="label">�������� �����������:<td><b>'.$ws['org_name'].'</b>'.
			'<tr><td class="label">�����:<td>'.$ws['city_name'].', '.$ws['country_name'].
			'<tr><td class="label">������� �������������:<td>'._viewer($ws['admin_id'], 'name').
			'<tr><td class="label">��� �����������:<td><input type="hidden" id="type" value="'.$ws['type'].'" />'.
			'<tr><td><td>'._button('info_save', '���������').
		'</table>'.

		'<div class="headName">��������� ������������� ���������</div>'.
		'<div id="devs">'.$checkDevs.'</div>'.

		'<div class="headName">�������� '._wsType($ws['type'], 2).'</div>'.
		'<div class="del_inf">'._wsType($ws['type']).', � ����� ��� ������ ��������� ��� ����������� ��������������.</div>'.
		_button('info_del', '������� '._wsType($ws['type'], 4)).
	'</div>';
}//setup_info()


function setup_service() {
	$r = query_assoc("SELECT * FROM `workshop` WHERE `id`=".WS_ID);
	return
	'<div id="setup-service">'.
		'<div class="headName">���� ����������� �����</div>'.

		'<div class="unit'.($r['service_device'] ? ' on' : '').'">'.
			'<h1>������ ������������ ������������</h1>'.
			'<h2>���� � ������ � �� ���������������� ������������ ������������ � ����������� ���������: '.
				'<ul><li>�����������;'.
					'<li>���������;'.
					'<li>���������;'.
					'<li>��������� ���������;'.
					'<li>���������;'.
					'<li>�������������;'.
					'<li>��.'.
				'</ul>'.
			'</h2>'.
//			'<h4><a>���������</a></h4>'.
		'</div>'.

		'<div class="unit'.($r['service_cartridge'] ? ' on' : '').'">'.
			'<h1>�������� ����������</h1>'.
			'<h2>��������, �������������� ���������� �� �������� ���������, ������� � ���. ������ �����, ���������.</h2>'.
			'<h3><a class="s-cartridge-toggle">��������</a></h3>'.
			'<h4>'.
				'<a href="'.URL.'&p=setup&d=service&d1=cartridge">���������</a> :: '.
				'<a class="s-cartridge-toggle off">���������</a>'.
			'</h4>'.
		'</div>'.

	'</div>';
}//setup_service()
function setup_service_cartridge() {
	return
		'<div id="setup-service-cartridge">'.
			'<a href="'.URL.'&p=setup&d=service" id="back"><< ����� � <b>����� �����</b></a>'.
			'<div class="headName">���������� ��������� ����������<a class="add">������ ����� ��������</a></div>'.
			'<div id="spisok">'.setup_service_cartridge_spisok().'</div>'.
		'</div>';
}//setup_service_cartridge()
function setup_service_cartridge_spisok($edit_id=0) {
	$send = '';
	foreach(_cartridgeType() as $type_id => $name) {
		$sql = "SELECT `s`.*,
				   COUNT(`z`.`id`) `count`
			FROM `setup_cartridge` `s`
			  LEFT JOIN `zayav_cartridge` AS `z`
			  ON `s`.`id`=`z`.`cartridge_id`
			WHERE `ws_id`=".WS_ID."
			  AND `type_id`=".$type_id."
			GROUP BY `s`.`id`
			ORDER BY `name`";
		$q = query($sql);
		if(!mysql_num_rows($q))
			continue;

		$spisok = array();
		while ($r = mysql_fetch_assoc($q))
			$spisok[$r['id']] = $r;

		$send .=
			'<div class="type">'.$name.':</div>'.
			'<table class="_spisok">' .
				'<tr><th class="n">�' .
					'<th class="name">������' .
					'<th class="cost">��� ������:<br />����./�����./���' .
					'<th class="count">���-��' .
					'<th class="set">';
		$n = 1;
		foreach ($spisok as $id => $r) {
			$cost = array();
			if($r['cost_filling'])
				$cost[] = '<span class="'._tooltip('��������', -30).$r['cost_filling'].'</span>';
			if($r['cost_restore'])
				$cost[] = '<span class="'._tooltip('��������������', -48).$r['cost_restore'].'</span>';
			if($r['cost_chip'])
				$cost[] = '<span class="'._tooltip('������ ����', -40).$r['cost_chip'].'</span>';
			$send .=
				'<tr'.($edit_id == $r['id'] ? ' class="edited"' : '').'>' .
					'<td class="n">'.($n++) .
					'<td class="name">'.$r['name'] .
					'<td class="cost">'.implode(' / ', $cost) .
						'<input type="hidden" class="type_id" value="'.$r['type_id'].'" />' .
						'<input type="hidden" class="filling" value="'.$r['cost_filling'].'" />' .
						'<input type="hidden" class="restore" value="'.$r['cost_restore'].'" />' .
						'<input type="hidden" class="chip" value="'.$r['cost_chip'].'" />' .
					'<td class="count">'.($r['count'] ? $r['count'] : '') .
					'<td class="set">' .
						'<div val="'.$id.'" class="img_edit'._tooltip('��������', -33).'</div>';
		}
		$send .= '</table>';
	}
	return $send ? $send : '������ ����.';
}//setup_service_cartridge_spisok()

/*
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
				'<td>'.($r['viewer_id'] == VIEWER_ADMIN ? '' : '<div class="img_del'._tooltip('������� ����������', -66).'</div>').
					'<a href="'.URL.'&p=setup&d=worker&id='.$r['viewer_id'].'" class="name">'.$r['name'].'</a>'.
					($r['enter_last'] != '0000-00-00 00:00:00' ? '<div class="activity">�������'.($r['sex'] == 1 ? 'a' : '').' � ���������� '.FullDataTime($r['enter_last']).'</div>' : '').
		'</table>';
	}
	return $send;
}//setup_worker_spisok()

function _setupRules($rls, $admin=0) {
	$rules = array(
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
				'RULES_INVOICE' => array(	// �����
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
*/

