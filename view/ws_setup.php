<?php

// ---===! setup !===--- ������ ��������

function setup() {
	return array(
		'info' => '�������� ����������'
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
	$sql = "SELECT *
			FROM `_ws`
			WHERE `app_id`=".APP_ID."
			  AND `id`=".WS_ID;
	if(!$ws = query_assoc($sql, GLOBAL_MYSQL_CONNECT)) {
		_cacheClear();
		header('Location:'.URL);
	}


	$sql = "SELECT *
			FROM `setup`
			WHERE `ws_id`=".WS_ID;
	$setup = query_assoc($sql);

	$devs = array();
	foreach(explode(',', $setup['devs']) as $d)
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
			'<tr><td class="label">�������� �����������:<td><b>'.$ws['name'].'</b>'.
			'<tr><td class="label">�����:<td>'.$ws['city_name'].', '.$ws['country_name'].
			'<tr><td class="label">������� �������������:<td>'._viewer($ws['admin_id'], 'viewer_name').
			'<tr><td class="label">��� �����������:<td><input type="hidden" id="type" value="'.$setup['ws_type_id'].'" />'.
			'<tr><td><td>'._button('info_save', '���������').
		'</table>'.

		'<div class="headName">��������� ������������� ���������</div>'.
		'<div id="devs">'.$checkDevs.'</div>'.

	(VIEWER_ADMIN ?
		'<div class="headName">�������� '._wsType($setup['ws_type_id'], 2).'</div>'.
		'<div class="del_inf">'._wsType($setup['ws_type_id']).', � ����� ��� ������ ��������� ��� ����������� ��������������.</div>'.
		_button('info_del', '������� '._wsType($setup['ws_type_id'], 4))
	: '').

	'</div>';
}//setup_info()


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
		if(!$spisok = query_arr($sql))
			continue;

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

