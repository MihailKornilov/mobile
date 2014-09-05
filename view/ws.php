<?php
function _remindActiveSet() { //��������� ���������� �������� �����������
	$key = CACHE_PREFIX.'remind_active'.WS_ID;
	$count = xcache_get($key);
	if(!strlen($count)) {
		$sql = "SELECT COUNT(`id`) AS `count`
				FROM `reminder`
				WHERE `ws_id`=".WS_ID."
				  AND `day`<=DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d')
				  AND `status`=1
				  AND (`private`=0 OR `private`=1 AND `viewer_id_add`=".VIEWER_ID.")";
		$r = mysql_fetch_assoc(query($sql));
		$count = $r['count'];
		xcache_set($key, $count, 7200);
	}
	define('REMIND_ACTIVE', $count > 0 ? ' (<b>'.$count.'</b>)' : '');
}//_remindActiveSet()
function _mainLinks() {
	global $html;
	_remindActiveSet();
	$links = array(
		array(
			'name' => '�������',
			'page' => 'client',
			'show' => 1
		),
		array(
			'name' => '������',
			'page' => 'zayav',
			'show' => 1
		),
		array(
			'name' => '��������',
			'page' => 'zp',
			'show' => 1
		),
		array(
			'name' => '������'.REMIND_ACTIVE,
			'page' => 'report',
			'show' => 1
		),
		array(
			'name' => '���������',
			'page' => 'setup',
			'show' => 1
		)
	);

	$send = '<div id="mainLinks">';
	foreach($links as $l)
		if($l['show'])
			$send .= '<a href="'.URL.'&p='.$l['page'].'"'.($l['page'] == $_GET['p'] ? 'class="sel"' : '').'>'.$l['name'].'</a>';
	$send .= pageHelpIcon().'</div>';
	$html .= $send;
}//_mainLinks()

function _expense($type_id=false, $i='name') {//������ ������� ��� ������
	if(!defined('EXPENSE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'expense';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_expense` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'worker' => $r['show_worker']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('EXPENSE_LOADED')) {
			foreach($arr as $id => $r) {
				define('EXPENSE_'.$id, $r['name']);
				define('EXPENSE_WORKER_'.$id, $r['worker']);
			}
			define('EXPENSE_0', '');
			define('EXPENSE_WORKER_0', 0);
			define('EXPENSE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'worker')
		return constant('EXPENSE_WORKER_'.$type_id);
	return constant('EXPENSE_'.$type_id);
}//_expense()
function _invoice($type_id=false, $i='name') {//������ ������� ��� ������
	if(!defined('INVOICE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'invoice';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$arr = array();
			$sql = "SELECT * FROM `invoice` ORDER BY `id`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q)) {
				$r['start'] = round($r['start'], 2);
				$arr[$r['id']] = $r;
			}
			xcache_set($key, $arr, 86400);
		}
		if(!defined('INVOICE_LOADED')) {
			foreach($arr as $id => $r) {
				define('INVOICE_'.$id, $r['name']);
				define('INVOICE_START_'.$id, $r['start']);
			}
			define('INVOICE_0', '');
			define('INVOICE_START_0', 0);
			define('INVOICE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'start')
		return constant('INVOICE_START_'.$type_id);
	return constant('INVOICE_'.$type_id);
}//_invoice()
function _income($type_id=false, $i='name') {//������ ������� ��� ������
	if(!defined('INCOME_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'income';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_income` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'invoice_id' => $r['invoice_id']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('INCOME_LOADED')) {
			foreach($arr as $id => $r) {
				define('INCOME_'.$id, $r['name']);
				define('INCOME_INVOICE_'.$id, $r['invoice_id']);
			}
			define('INCOME_0', '');
			define('INCOME_INVOICE_0', 0);
			define('INCOME_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'invoice')
		return constant('INCOME_INVOICE_'.$type_id);
	return constant('INCOME_'.$type_id);
}//_income()

function viewerAdded($viewer_id) {//����� ����������, ������� ������ ������ � ������ ����
	return '��'.(_viewer($viewer_id, 'sex') == 1 ? '����' : '��').' '._viewer($viewer_id, 'name');
}


// ---===! client !===--- ������ ��������

function _clientLink($arr, $fio=0) {//���������� ����� � ������ ������� � ������ ��� ������� �� id
	$clientArr = array(is_array($arr) ? 0 : $arr);
	if(is_array($arr)) {
		$ass = array();
		foreach($arr as $r) {
			$clientArr[$r['client_id']] = $r['client_id'];
			if($r['client_id'])
				$ass[$r['client_id']][] = $r['id'];
		}
		unset($clientArr[0]);
	}
	if(!empty($clientArr)) {
		$sql = "SELECT
					`id`,
					`fio`,
					`deleted`
		        FROM `client`
				WHERE `ws_id`=".WS_ID."
				  AND `id` IN (".implode(',', $clientArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return $fio ? $r['fio'] : '<a'.($r['deleted'] ? ' class="deleted"' : '').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['client_link'] = '<a'.($r['deleted'] ? ' class="deleted"' : '').' href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>';
				$arr[$id]['client_fio'] = $r['fio'];
			}
	}
	return $arr;
}//_clientLink()
function clientFilter($v) {
	return array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'limit' => _isnum(@$v['limit']) ? $v['limit'] : 20,
		'fast' => win1251(htmlspecialchars(trim(@$v['fast']))),
		'dolg' => _isbool(@$v['dolg']),
		'active' => _isbool(@$v['active']),
		'comm' => _isbool(@$v['comm'])
	);
}//clientFilter()
function client_data($v=array()) {
	$filter = clientFilter($v);
	$cond = "`ws_id`=".WS_ID." AND !`deleted`";
	$reg = '';
	$regEngRus = '';
	$dolg = 0;
	if($filter['fast']) {
		$engRus = _engRusChar($filter['fast']);
		$cond .= " AND (`fio` LIKE '%".$filter['fast']."%'
					 OR `telefon` LIKE '%".$filter['fast']."%'
					 ".($engRus ?
						   "OR `fio` LIKE '%".$engRus."%'
							OR `telefon` LIKE '%".$engRus."%'"
						: '')."
					 )";
		$reg = '/('.$filter['fast'].')/i';
		if($engRus)
			$regEngRus = '/('.$engRus.')/i';
	} else {
		if($filter['dolg']) {
			$cond .= " AND `balans`<0";
			$dolg = abs(query_value("SELECT SUM(`balans`) FROM `client` WHERE !`deleted` AND `balans`<0"));
		}
		if($filter['active']) {
			$ids = query_ids("SELECT DISTINCT `client_id`
							FROM `zayav`
							WHERE `ws_id`=".WS_ID."
							  AND `zayav_status`=1");
			$cond .= " AND `id` IN (".$ids.")";
		}
		if($filter['comm']) {
			$ids = query_ids("SELECT DISTINCT `table_id`
							FROM `vk_comment`
							WHERE `status` AND `table_name`='client'");
			$cond .= " AND `id` IN (".$ids.")";
		}
	}

	$all = query_value("SELECT COUNT(`id`) AS `all` FROM `client` WHERE ".$cond." LIMIT 1");
	if(!$all)
		return array(
			'all' => 0,
			'result' => '�������� �� �������.',
			'spisok' => '<div class="_empty">�������� �� �������.</div>',
			'filter' => $filter
		);

	$send['all'] = $all;
	$send['result'] = '������'._end($all, ' ', '� ').$all.' ������'._end($all, '', '�', '��').
					  ($dolg ? '<em>(����� ����� ����� = '.$dolg.' ���.)</em>' : '');
	$send['filter'] = $filter;

	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT *
			FROM `client`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(!empty($filter['fast'])) {
			if(preg_match($reg, $r['fio']))
				$r['fio'] = preg_replace($reg, '<em>\\1</em>', $r['fio'], 1);
			if(preg_match($reg, $r['telefon']))
				$r['telefon'] = preg_replace($reg, '<em>\\1</em>', $r['telefon'], 1);
			if($regEngRus && preg_match($regEngRus, $r['fio']))
				$r['fio'] = preg_replace($regEngRus, '<em>\\1</em>', $r['fio'], 1);
			if($regEngRus && preg_match($regEngRus, $r['telefon']))
				$r['telefon'] = preg_replace($regEngRus, '<em>\\1</em>', $r['telefon'], 1);
		}
		$spisok[$r['id']] = $r;
	}

	$sql = "SELECT
				`client_id` AS `id`,
				COUNT(`id`) AS `count`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `zayav_status`
			  AND `client_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `client_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['zayav_count'] = $r['count'];

	$sql = "SELECT
				`table_id` AS `id`
			FROM `vk_comment`
			WHERE `status`
			  AND `table_name`='client'
			  AND `table_id` IN (".implode(',', array_keys($spisok)).")
			GROUP BY `table_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']]['comm'] = 1;

	$send['spisok'] = '';
	foreach($spisok as $r)
		$send['spisok'] .= '<div class="unit'.(isset($r['comm']) ? ' i' : '').'">'.
			($r['balans'] ? '<div class="balans">������: <b style=color:#'.($r['balans'] < 0 ? 'A00' : '090').'>'.$r['balans'].'</b></div>' : '').
			'<table>'.
			   '<tr><td class="label">���:<td><a href="'.URL.'&p=client&d=info&id='.$r['id'].'">'.$r['fio'].'</a>'.
				($r['telefon'] ? '<tr><td class="label">�������:<td>'.$r['telefon'] : '').
				(isset($r['zayav_count']) ? '<tr><td class="label">������:<td>'.$r['zayav_count'] : '').
			'</table>'.
		 '</div>';
	if($start + $limit < $send['all']) {
		$c = $send['all'] - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="_next" val="'.($page + 1).'"><span>�������� ��� '.$c.' ������'._end($c, '�', '�', '��').'</span></div>';
	}
	return $send;
}//client_data()
function client_list() {
	$data = client_data();
	return '<div id="client">'.
		'<div id="find"></div>'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a>����� ������</a></div>'.
					'<div class="filter">'.
					   _check('dolg', '��������').
					   _check('active', '� ��������� ��������').
					   _check('comm', '���� �������').
					'</div>'.
		  '</table>'.
		'</div>';
}//client_list()

function client_info($client_id) {
	$sql = "SELECT * FROM `client` WHERE `ws_id`=".WS_ID." AND `id`=".$client_id;
	if(!$client = mysql_fetch_assoc(query($sql)))
		return _noauth('������� �� ����������');
	if($client['deleted'])
		if($client['join_id'])
			return _noauth('������ <b>'.$client['fio'].'</b> ��� �������� � �������� '._clientLink($client['join_id']).'.');
		else
			return _noauth('������ ��� �����.');

	$zayavData = zayav_spisok(array(
		'client_id' => $client_id,
		'limit' => 10
	));
	$commCount = query_value("SELECT COUNT(`id`)
							  FROM `vk_comment`
							  WHERE `status`=1
								AND `parent_id`=0
								AND `table_name`='client'
								AND `table_id`=".$client_id);

	$moneyCount = query_value("SELECT COUNT(`id`)
							   FROM `money`
							   WHERE `ws_id`=".WS_ID."
								 AND `deleted`=0
								 AND `client_id`=".$client_id);
	$money = '<div class="_empty">�������� ���.</div>';
	if($moneyCount) {
		$money = '<table class="_spisok _money">'.
			'<tr><th class="sum">�����'.
			'<th>��������'.
			'<th class="data">����';
		$sql = "SELECT *
				FROM `money`
				WHERE `ws_id`=".WS_ID."
				  AND `deleted`=0
				  AND `client_id`=".$client_id;
		$q = query($sql);
		$moneyArr = array();
		while($r = mysql_fetch_assoc($q))
			$moneyArr[$r['id']] = $r;
		$moneyArr = _zayavNomerLink($moneyArr);
		foreach($moneyArr as $r) {
			$about = '';
			if($r['zayav_id'])
				$about .= '������ '.$r['zayav_link'].'. ';
			if($r['zp_id'])
				$about = '������� �������� '.$r['zp_id'].'. ';
			$about .= $r['prim'];
			$money .= '<tr><td class="sum"><b>'.round($r['sum'], 2).'</b>'.
						  '<td>'.$about.
						  '<td class="dtime" title="���: '._viewer($r['viewer_id_add'], 'name').'">'.FullDataTime($r['dtime_add']);
		}
		$money .= '</table>';
	}

	$remindData = remind_data(1, array('client'=>$client_id));

	$history = history(array('client_id'=>$client_id,'limit'=>15));

	return
		'<script type="text/javascript">'.
			'var CLIENT={'.
					'id:'.$client_id.','.
					'fio:"'.addslashes($client['fio']).'"'.
				'},'.
				'DEVICE_IDS=['._zayavBaseDeviceIds($client_id).'],'.
				'VENDOR_IDS=['._zayavBaseVendorIds($client_id).'],'.
				'MODEL_IDS=['._zayavBaseModelIds($client_id).'];'.
		'</script>'.
		'<div id="clientInfo">'.
			'<table class="tabLR">'.
				'<tr><td class="left">'.
					'<div class="fio">'.$client['fio'].'</div>'.
					'<div class="cinf">'.
						'<table style="border-spacing:2px">'.
							'<tr><td class="label">�������:  <td class="telefon">'.$client['telefon'].'</TD>'.
							'<tr><td class="label">������:   <td><b style=color:#'.($client['balans'] < 0 ? 'A00' : '090').'>'.$client['balans'].'</b>'.
						'</table>'.
						'<div class="dtime">������� ��� '._viewer($client['viewer_id_add'], 'name').' '.FullData($client['dtime_add'], 1).'</div>'.
					'</div>'.
					'<div id="dopLinks">'.
						'<a class="link sel" val="zayav">������'.($zayavData['all'] ? ' (<b>'.$zayavData['all'].'</b>)' : '').'</a>'.
						'<a class="link" val="money">�������'.($moneyCount ? ' (<b>'.$moneyCount.'</b>)' : '').'</a>'.
						'<a class="link" val="remind">�������'.(!empty($remindData) ? ' (<b>'.$remindData['all'].'</b>)' : '').'</a>'.
						'<a class="link" val="comm">�������'.($commCount ? ' (<b>'.$commCount.'</b>)' : '').'</a>'.
						'<a class="link" val="hist">�������'.($history['all'] ? ' (<b>'.$history['all'].'</b>)' : '').'</a>'.
					'</div>'.
					'<div id="zayav_spisok">'.$zayavData['spisok'].'</div>'.
					'<div id="money_spisok">'.$money.'</div>'.
					'<div id="remind_spisok">'.(!empty($remindData) ? remind_spisok($remindData) : '<div class="_empty">������� ���.</div>').'</div>'.
					'<div id="comments">'._vkComment('client', $client_id).'</div>'.
					'<div id="histories">'.$history['spisok'].'</div>'.
				'<td class="right">'.
					'<div class="rightLink">'.
						'<a href="'.URL.'&p=zayav&d=add&back=client&id='.$client_id.'"><b>����� ������</b></a>'.
						'<a class="remind_add">����� �������</a>'.
						'<a class="cedit">�������������</a>'.
					'</div>'.
					'<div id="zayav_filter">'.
						'<div id="zayav_result">'.$zayavData['result'].'</div>'.
						'<div class="findHead">������ ������</div>'.
						_rightLink('status', _zayavStatusName()).
						_check('diff', '������������ ������').
						'<div class="findHead">����������</div><div id="dev"></div>'.
					'</div>'.
			'</table>'.
		'</div>';
}//client_info()
function clientBalansUpdate($client_id, $ws_id=WS_ID) {//���������� ������� �������
	if(!$client_id)
		return 0;
	$prihod = query_value("SELECT IFNULL(SUM(`sum`),0)
						   FROM `money`
						   WHERE `ws_id`=".$ws_id."
							 AND !`deleted`
							 AND `client_id`=".$client_id."
							 AND `sum`>0");
	$acc = query_value("SELECT IFNULL(SUM(`sum`),0)
						FROM `accrual`
						WHERE `ws_id`=".$ws_id."
						  AND !`deleted`
						  AND `client_id`=".$client_id);
	$balans = $prihod - $acc;
	query("UPDATE `client` SET `balans`=".$balans." WHERE `id`=".$client_id);
	return $balans;
}//clientBalansUpdate()





// ---===! zayav !===--- ������ ������

function _zayavStatus($id=false) {
	$arr = array(
		'0' => array(
			'name' => '����� ������',
			'color' => 'ffffff'
		),
		'1' => array(
			'name' => '������� ����������',
			'color' => 'E8E8FF'
		),
		'2' => array(
			'name' => '���������!',
			'color' => 'CCFFCC'
		),
		'3' => array(
			'name' => '��������� �� �������',
			'color' => 'FFDDDD'
		)
	);
	return $id ? $arr[$id] : $arr;
}//_zayavStatus()
function _zayavStatusName($id=false) {
	$status = _zayavStatus();
	if($id)
		return $status[$id]['name'];
	$send = array();
	foreach($status as $id => $r)
		$send[$id] = $r['name'];
	return $send;
}//_zayavStatusName()
function _zayavStatusColor($id=false) {
	$status = _zayavStatus();
	if($id)
		return $status[$id]['color'];
	$send = array();
	foreach($status as $id => $r)
		$send[$id] = $r['color'];
	return $send;
}//_zayavStatusColor()
function _zayavNomerLinkForming($v) {
	$class = (!$v['nohint'] ? 'zayav_link' : '').
			 ($v['deleted'] ? ' deleted' : '');
	return
		'<a href="'.URL.'&p=zayav&d=info&id='.$v['id'].'"'.
			($class ? ' class="'.$class.'"' : '').
			(!$v['nohint'] ? ' val="'.$v['id'].'"' : '').
		'>'.
			'�'.$v['nomer'].
			(!$v['nohint'] ? '<div class="tooltip empty"></div>' : '').
		'</a>';
}//_zayavNomerLinkForming()
function _zayavNomerLink($arr, $noHint=0) { //����� ������� ������ � ������������ ����������� �������������� ���������� ��� ���������
	$zayavArr = array(is_array($arr) ? 0 : $arr);
	if(is_array($arr)) {
		$ass = array();
		foreach($arr as $r) {
			$zayavArr[$r['zayav_id']] = $r['zayav_id'];
			if($r['zayav_id'])
				$ass[$r['zayav_id']][] = $r['id'];
		}
		unset($zayavArr[0]);
	}
	if(!empty($zayavArr)) {
		$sql = "SELECT
	            `id`,
	            `nomer`,
	            `deleted`,
	            ".$noHint." `nohint`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `id` IN (".implode(',', $zayavArr).")";
		$q = query($sql);
		if(!is_array($arr)) {
			if($r = mysql_fetch_assoc($q))
				return _zayavNomerLinkForming($r);
			return '';
		}
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id)
				$arr[$id]['zayav_link'] = _zayavNomerLinkForming($r);
	}
	return $arr;
}//_zayavNomerLink()
function _zayavLink($arr, $noHint=0) {
	$ids = array();
	$arrIds = array();
	foreach($arr as $key => $r)
		if(!empty($r['zayav_id'])) {
			$ids[$r['zayav_id']] = 1;
			$arrIds[$r['zayav_id']][] = $key;
		}
	if(empty($ids))
		return $arr;
	$sql = "SELECT
	            *,
	            ".$noHint." `nohint`
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND `id` IN (".implode(',', array_keys($ids)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($arrIds[$r['id']] as $id) {
			$arr[$id] += array(
				'zayav_link' => _zayavNomerLinkForming($r),
				'zayav_status_color' => _zayavStatusColor($r['zayav_status']),
				'zayav_add' => FullData($r['dtime_add'], 1)
			);
		}
	return $arr;
}//_zayavLink()
function _zayavBaseDeviceIds($client_id=0) { //������ id ���������, ������� ������������ � �������
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z` USE INDEX(`i_zayav_status`),
				 `base_device` `b`
			WHERE `b`.`id`=`z`.`base_device_id`
			  AND `z`.`zayav_status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`
			ORDER BY `b`.`sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}//_zayavBaseDeviceIds()
function _zayavBaseVendorIds($client_id=0) { //������ id ��������������, ������� ������������ � �������
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z`,
				 `base_vendor` `b`
			WHERE `b`.`id`=`z`.`base_vendor_id`
			  AND `z`.`zayav_status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`
			ORDER BY `b`.`sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}//_zayavBaseVendorIds()
function _zayavBaseModelIds($client_id=0) { //������ id ��������������, ������� ������������ � �������
	$ids = array();
	$sql = "SELECT `b`.`id`
			FROM `zayav` `z`,
				 `base_model` `b`
			WHERE `b`.`id`=`z`.`base_model_id`
			  AND `z`.`zayav_status`
			  AND `z`.`ws_id`=".WS_ID."
			  ".($client_id ? "AND `z`.`client_id`=".$client_id : '')."
			GROUP BY `b`.`id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$ids[] = $r['id'];
	return implode(',', $ids);
}//_zayavBaseModelIds()
function _zayavImg($z, $size='s') {
	$v = array(
		'owner' => array('zayav'.$z['id'], 'dev'.$z['base_model_id'])
	);
	if($size == 'b')
		$v += array(
			'size' => 'b',
			'x' => 200,
			'y' => 320,
			'view' => 1
		);
	$img = _imageGet($v);
	return $img['zayav'.$z['id']]['id'] ? $img['zayav'.$z['id']]['img'] : $img['dev'.$z['base_model_id']]['img'];
}//_zayavImg()
function _zayavExpense($type_id=false, $i='name') {//������ �������� ������
	if(!defined('ZE_LOADED') || $type_id === false) {
		$key = CACHE_PREFIX.'zayav_expense';
		$arr = xcache_get($key);
		if(empty($arr)) {
			$sql = "SELECT * FROM `setup_zayav_expense` ORDER BY `sort`";
			$q = query($sql);
			while($r = mysql_fetch_assoc($q))
				$arr[$r['id']] = array(
					'name' => $r['name'],
					'dop' => $r['dop']
				);
			xcache_set($key, $arr, 86400);
		}
		if(!defined('ZE_LOADED')) {
			foreach($arr as $id => $r) {
				define('ZE_'.$id, $r['name']);
				define('ZE_TXT_'.$id, $r['dop'] == 1);
				define('ZE_WORKER_'.$id, $r['dop'] == 2);
				define('ZE_ZP_'.$id, $r['dop'] == 3);
			}
			define('ZE_0', '');
			define('ZE_TXT_0', '');
			define('ZE_WORKER_0', 0);
			define('ZE_ZP_0', 0);
			define('ZE_LOADED', true);
		}
	}
	if($type_id === false)
		return $arr;
	if($i == 'txt')
		return constant('ZE_TXT_'.$type_id);
	if($i == 'worker')
		return constant('ZE_WORKER_'.$type_id);
	if($i == 'zp')
		return constant('ZE_ZP_'.$type_id);
	return constant('ZE_'.$type_id);
}//_zayavExpense()
function _zayavExpenseDop($id=false) {
	$arr =  array(
		0 => '���',
		1 => '��������� ����',
		2 => '������ �����������',
		3 => '������ ���������'
	);
	return $id !== false ? $arr[$id] : $arr;
}//_zayavExpenseDop()

function zayav_add($v=array()) {
	$sql = "SELECT `id`,`name` FROM `setup_fault` ORDER BY SORT";
	$q = query($sql);
	$fault = '<table>';
	$k = 0;
	while($r = mysql_fetch_assoc($q))
		$fault .= (++$k%2 ? '<tr>' : '').'<td>'._check('f_'.$r['id'], $r['name']);
	$fault .= '</table>';

	$client_id = empty($_GET['id']) ? 0 : intval($_GET['id']);

	switch(@$_GET['back']) {
		case 'client': $back = 'client'.($client_id > 0 ? '&d=info&id='.$client_id : ''); break;
		default: $back = 'zayav';
	}
	return '<div id="zayavAdd">'.
		'<div class="headName">�������� ����� ������</div>'.
		'<table style="border-spacing:8px">'.
			'<tr><td class="label">������:		<td><INPUT TYPE="hidden" id="client_id" value="'.$client_id.'" />'.
			'<tr><td class="label topi">����������:<td><table><td id="dev"><td id="device_image"></table>'.
			'<tr><td class="label">IMEI:		  <td><INPUT type="text" id="imei" maxlength="20"'.(isset($v['imei']) ? ' value="'.$v['imei'].'"' : '').' />'.
			'<tr><td class="label">�������� �����:<td><INPUT type="text" id="serial" maxlength="30"'.(isset($v['serial']) ? ' value="'.$v['serial'].'"' : '').' />'.
			'<tr><td class="label">����:'.
				'<td><INPUT TYPE="hidden" id="color_id" />'.
					'<span class="color_dop dn"><tt>-</tt><INPUT TYPE="hidden" id="color_dop" /></span>'.
			'<tr class="tr_equip dn"><td class="label">������������:<td class="equip_spisok">'.
			'<tr><td class="label topi">��������������� ����������<br />����� �������� ������:<td><input type="hidden" id="place" value="-1" />'.
			'<tr><td class="label top">�������������: <td id="fault">'.$fault.
			'<tr><td class="label topi">�������:	   <td><textarea id="comm"></textarea>'.
			'<tr><td class="label">��������������� ���������:<td><input type="text" class="money" id="pre_cost" maxlength="11" /> ���.'.
			'<tr><td class="label">�������� �����������:<td>'._check('reminder').
		'</table>'.

		'<table id="reminder_tab">'.
			'<tr><td class="label">����������: <td><INPUT TYPE="text" id="reminder_txt" />'.
			'<tr><td class="label">����:	   <td><INPUT TYPE="hidden" id="reminder_day" />'.
		'</table>'.

		'<div class="vkButton"><button>������</button></div>'.
		'<div class="vkCancel" val="'.$back.'"><button>������</button></div>'.
	'</div>';
}//zayav_add()

function zayavFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? intval($v['limit']) : 20,
		'client_id' => !empty($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']) ? intval($v['client_id']) : 0,
		'find' => !empty($v['find']) ? htmlspecialchars(trim($v['find'])) : '',
		'status' => !empty($v['status']) && preg_match(REGEXP_NUMERIC, $v['status']) ? intval($v['status']) : 0,
		'zpzakaz' => !empty($v['zpzakaz']) && preg_match(REGEXP_NUMERIC, $v['zpzakaz']) ? intval($v['zpzakaz']) : 0,
		'device' => !empty($v['device']) && preg_match(REGEXP_NUMERIC, $v['device']) ? intval($v['device']) : 0,
		'vendor' => !empty($v['vendor']) && preg_match(REGEXP_NUMERIC, $v['vendor']) ? intval($v['vendor']) : 0,
		'model' => !empty($v['model']) && preg_match(REGEXP_NUMERIC, $v['model']) ? intval($v['model']) : 0,
		'devstatus' => empty($v['devstatus']) || preg_match(REGEXP_NUMERIC, $v['devstatus']) && $v['devstatus'] != -1 ? 0 : $v['devstatus'],
		'sort' => !empty($v['sort']) && preg_match(REGEXP_NUMERIC, $v['sort']) ? intval($v['sort']) : 1,
		'desc' => !empty($v['desc']) && preg_match(REGEXP_BOOL, $v['desc']) ? intval($v['desc']) : 0,
		'diff' => !empty($v['diff']) ? 1 : 0,
		'place' => !empty($v['place']) ? win1251(urldecode(htmlspecialchars(trim($v['place'])))) : ''
	);
}//zayavFilter()
function zayav_spisok($v) {
	$filter = zayavFilter($v);

	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`ws_id`=".WS_ID." AND !`deleted` AND `zayav_status`";

	if($filter['find']) {
		$cond .= " AND `find` LIKE '%".$filter['find']."%'";
		if($page ==1 && preg_match(REGEXP_NUMERIC, $filter['find']))
			$nomer = intval($filter['find']);
		$reg = '/('.$filter['find'].')/i';
	} else {
		if($filter['client_id'])
			$cond .= " AND `client_id`=".$filter['client_id'];
		if($filter['status'])
			$cond .= " AND `zayav_status`=".$filter['status'];
		if($filter['diff'])
			$cond .= " AND `accrual_sum`!=`oplata_sum`";
		if($filter['zpzakaz']) {
			$ids = query_ids("SELECT `zayav_id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID);
			$cond .= " AND `id` ".($filter['zpzakaz'] == 2 ? 'NOT' : '')." IN (".$ids.")";
		}
		if($filter['device'])
			$cond .= " AND `base_device_id`=".$filter['device'];
		if($filter['vendor'])
			$cond .= " AND `base_vendor_id`=".$filter['vendor'];
		if($filter['model'])
			$cond .= " AND `base_model_id`=".$filter['model'];
		if($filter['place']) {
			if(preg_match(REGEXP_NUMERIC, $filter['place']))
				$cond .= " AND `device_place`=".$filter['place'];
			elseif($filter['place'] == -1)
				$cond .= " AND !`device_place` AND !LENGTH(`device_place_other`)";
			else
				$cond .= " AND !`device_place` AND `device_place_other`='".$filter['place']."'";
		}
		if($filter['devstatus'])
			$cond .= " AND `device_status`=".$filter['devstatus'];
	}

	$all = query_value("SELECT COUNT(*) FROM `zayav` USE INDEX (`i_zayav_status`) WHERE ".$cond." LIMIT 1");

	$zayav = array();
	$images = array();
	if(isset($nomer)) {
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status` AND `nomer`=".$nomer." LIMIT 1";
		if($r = mysql_fetch_assoc(query($sql))) {
			$all++;
			$limit--;
			$r['nomer_find'] = 1;
			$zayav[$r['id']] = $r;
			$images[] = 'zayav'.$r['id'];
			$images[] = 'dev'.$r['base_model_id'];
		}
	}

	$filter_clear = !$filter['client_id'] ? '<a class="clear">�������� ������� ������</a>' : '';

	if(!$all)
		return array(
			'all' => 0,
			'result' => '������ �� �������'.$filter_clear,
			'spisok' => '<div class="_empty">������ �� �������</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => '�������'._end($all, '�', '�').' '.$all.' ����'._end($all, '��', '��', '��').$filter_clear,
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$limit_save = $limit;
	if($page > 1)
		setcookie('zback_spisok_page', $page, time() + 3600, '/');
	if(!empty($_COOKIE['zback_info']) && $page == 1 && !empty($_COOKIE['zback_spisok_page']) && $_COOKIE['zback_spisok_page'] > 1) {
		setcookie('zback_info', '', time() - 3600, '/');
		$page = $_COOKIE['zback_spisok_page'];
		$limit *= $_COOKIE['zback_spisok_page'];
	}
	$sql = "SELECT
	            *,
				'' AS `note`
			FROM `zayav`
			WHERE ".$cond."
			ORDER BY `".($filter['sort'] == 2 ? 'zayav_status_dtime' : 'dtime_add')."` ".($filter['desc'] ? 'ASC' : 'DESC')."
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(isset($nomer) && $nomer == $r['nomer'])
			continue;
		$zayav[$r['id']] = $r;
		$images[] = 'zayav'.$r['id'];
		$images[] = 'dev'.$r['base_model_id'];
	}

	$zayavIds = implode(',', array_keys($zayav));

	if(!$filter['client_id'])
		$zayav = _clientLink($zayav);

	$images = _imageGet(array(
		'owner' => $images,
		'view' => 1
	));

	//��������
	$sql = "SELECT `zayav_id`,`zp_id` FROM `zp_zakaz` WHERE `zayav_id` IN (".$zayavIds.")";
	$q = query($sql);
	$zp = array();
	$zpZakaz = array();
	while($r = mysql_fetch_assoc($q)) {
		$zp[$r['zp_id']] = $r['zp_id'];
		$zpZakaz[$r['zayav_id']][] = $r['zp_id'];
	}
	if(!empty($zp)) {
		$sql = "SELECT `id`,`name_id` FROM `zp_catalog` WHERE `id` IN (".implode(',', $zp).")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			$zp[$r['id']] = $r['name_id'];
		foreach($zpZakaz as $id => $zz)
			foreach($zz as $i => $zpId)
				$zpZakaz[$id][$i] = _zpName($zp[$zpId]);
	}

	//�������
	$sql = "SELECT
				`table_id`,
				`txt`
			FROM `vk_comment`
			WHERE `table_name`='zayav'
			  AND `table_id` IN (".$zayavIds.")
			  AND `status`
			ORDER BY `id` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zayav[$r['table_id']]['note'] = $r['txt'];

	foreach($zayav as $id => $r) {
		$r['model'] = _modelName($r['base_model_id']);
		$img = $images['zayav'.$id]['id'] ? $images['zayav'.$id]['img'] : $images['dev'.$r['base_model_id']]['img'];
		if($filter['find']) {
			if(preg_match($reg, $r['model']))
				$r['model'] = preg_replace($reg, "<em>\\1</em>", $r['model'], 1);
			$r['imei'] = preg_match($reg, $r['imei']) ? preg_replace($reg, "<em>\\1</em>", $r['imei'], 1) : '';
			$r['serial'] = preg_match($reg, $r['serial']) ? preg_replace($reg, "<em>\\1</em>", $r['serial'], 1) : '';
		} else {
			$r['imei'] = '';
			$r['serial'] = '';
		}
		$diff = $r['accrual_sum'] - $r['oplata_sum'];
		$send['spisok'] .=
			'<div class="zayav_unit" id="u'.$id.'" style="background-color:#'._zayavStatusColor($r['zayav_status']).'" val="'.$id.'">'.
				'<table width="100%">'.
					'<tr><td valign=top>'.
						'<h2'.(isset($r['nomer_find']) ? ' class="finded"' : '').'>#'.$r['nomer'].'</h2>'.
						'<a class="name">'.
							_deviceName($r['base_device_id']).
							'<b>'._vendorName($r['base_vendor_id']).$r['model'].'</b>'.
						'</a>'.
						'<table class="utab">'.
   (!$filter['client_id'] ? '<tr><td class="label">������:<td>'.$r['client_link'] : '').
							'<tr><td class="label">���� ������:'.
								'<td>'.FullData($r['dtime_add'], 1).
									($r['accrual_sum'] || $r['oplata_sum'] ?
										'<div class="balans'.($diff ? ' diff' : '').'">'.
											'<span class="acc'._tooltip('���������', -39).$r['accrual_sum'].'</span>/'.
											'<span class="opl'._tooltip($diff ? ($diff > 0 ? '����' : '����').'����� '.abs($diff).' ���.' : '��������', -17, 'l').$r['oplata_sum'].'</span>'.
										'</div>'
									: '').
			  ($r['imei'] ? '<tr><td class="label">IMEI:<td>'.$r['imei'] : '').
		    ($r['serial'] ? '<tr><td class="label">�������� �����:<td>'.$r['serial'] : '').
	(isset($zpZakaz[$id]) ? '<tr><td class="label">�������� �/�:<td class="zz">'.implode(', ', $zpZakaz[$id]) : '').
						'</table>'.
					'<td class="image">'.$img.
				'</table>'.
				'<div class="note">'.$r['note'].'</div>'.
			'</div>';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit_save : $c;
		$send['spisok'] .=
			'<div class="_next" val="'.($page + 1).'">'.
				'<span>�������� ��� '.$c.' ����'._end($c, '��', '��', '��').'</span>'.
			'</div>';
	}
	return $send;
}//zayav_spisok()
function zayav_list($v) {
	$data = zayav_spisok($v);
	$v = $data['filter'];
	$place_other = array();
	$sql = "SELECT DISTINCT `device_place_other` AS `other`
			FROM `zayav`
			WHERE LENGTH(`device_place_other`)
			  AND `zayav_status`
			  AND `ws_id`=".WS_ID;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$place_other[] = '"'.$r['other'].'"';

	return '<div id="zayav">'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td id="spisok">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate"><a HREF="'.URL.'&p=zayav&d=add&back=zayav">����� ������</a></div>'.
					'<div id="find"></div>'.
					'<div class="findHead">�������</div>'.
					_radio('sort', array(1=>'�� ���� ����������',2=>'�� ���������� �������'), $v['sort']).
					_check('desc', '�������� �������', $v['desc']).
					'<div class="condLost'.(!empty($v['find']) ? ' hide' : '').'">'.
						'<div class="findHead">������ ������</div>'.
						_rightLink('status', _zayavStatusName(), $v['status']).
						_check('diff', '������������ ������', $v['diff']).
						'<div class="findHead">�������� ��������</div>'.
						_radio('zpzakaz', array(0=>'��� ������',1=>'��',2=>'���'), $v['zpzakaz'], 1).
						'<div class="findHead">����������</div><div id="dev"></div>'.
						'<div class="findHead">���������� ����������</div><INPUT TYPE="hidden" id="device_place" value="'.$v['place'].'">'.
						'<div class="findHead">��������� ����������</div><INPUT TYPE="hidden" id="devstatus" value="'.$v['devstatus'].'">'.
					'</div>'.
		'</table>'.
		'<script type="text/javascript">'.
			'var Z={'.
				'device_ids:['._zayavBaseDeviceIds().'],'.
				'vendor_ids:['._zayavBaseVendorIds().'],'.
				'model_ids:['._zayavBaseModelIds().'],'.
				'place_other:['.implode(',', $place_other).'],'.
				'find:"'.unescape($v['find']).'",'.
				'device_id:'.$v['device'].','.
				'vendor_id:'.$v['vendor'].','.
				'model_id:'.$v['model'].','.
				'cookie_id:'.(!empty($_COOKIE['zback_info']) ? $_COOKIE['zback_info'] : 0).
			'};'.
		'</script>'.
	'</div>';
}//zayav_list()


function zayavBalansUpdate($zayav_id, $ws_id=WS_ID) {//���������� ������� �������
	if(!$zayav_id)
		return false;
	$opl = query_value("SELECT IFNULL(SUM(`sum`),0)
						   FROM `money`
						   WHERE `ws_id`=".$ws_id."
							 AND !`deleted`
							 AND `zayav_id`=".$zayav_id."
							 AND `sum`>0");
	$acc = query_value("SELECT IFNULL(SUM(`sum`),0)
						FROM `accrual`
						WHERE `ws_id`=".$ws_id."
						  AND !`deleted`
						  AND `zayav_id`=".$zayav_id);
	query("UPDATE `zayav` SET `accrual_sum`=".$acc.",`oplata_sum`=".$opl." WHERE `id`=".$zayav_id);
	return array(
		'acc' => round($acc, 2),
		'opl' => round($opl, 2),
		'diff' => $acc - $opl
	);
}//zayavBalansUpdate()
function zayavEquipSpisok($ids) {//������ ������������ ����� �������
	if(empty($ids))
		return '';
	$arr = explode(',', $ids);
	$equip = array();
	foreach($arr as $id)
		$equip[$id] = 1;
	$send = array();
	foreach(equipCache() as $id => $r)
		if(isset($equip[$id]))
			$send[] = $r['name'];
	return implode(', ', $send);
}//zayavEquipSpisok()
function zayav_info($zayav_id) {
	//��������� id ������, ���� ������� �� ������ ������
	if(!empty($_COOKIE['zback_spisok']))
		setcookie('zback_info', $zayav_id, time() + 3600, '/');

	$sql = "SELECT *
			FROM `zayav`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `zayav_status`
			  AND `id`=".$zayav_id;
	if(!$z = mysql_fetch_assoc(query($sql)))
		return '������ �� ����������.';

	define('MODEL', _vendorName($z['base_vendor_id'])._modelName($z['base_model_id']));
	define('DOPL', $z['accrual_sum'] - $z['oplata_sum']);

	$status = _zayavStatusName();
	unset($status[0]);
	$expense = zayav_expense_spisok($z, 'all');
	$history = history(array('zayav_id'=>$zayav_id));

	return '<script type="text/javascript">'.
		'var STATUS='._selJson($status).','.
		'ZAYAV={'.
			'id:'.$zayav_id.','.
			'nomer:'.$z['nomer'].','.
			'client_id:'.$z['client_id'].','.
			'device:'.$z['base_device_id'].','.
			'vendor:'.$z['base_vendor_id'].','.
			'model:'.$z['base_model_id'].','.
			'z_status:'.$z['zayav_status'].','.
			'dev_status:'.$z['device_status'].','.
			'dev_place:'.$z['device_place'].','.
			'place_other:"'.$z['device_place_other'].'",'.
			'imei:"'.$z['imei'].'",'.
			'serial:"'.$z['serial'].'",'.
			'color_id:'.$z['color_id'].','.
			'color_dop:'.$z['color_dop'].','.
			'equip:"'.addslashes(devEquipCheck($z['base_device_id'], $z['equip'])).'",'.
			'pre_cost:'.$z['pre_cost'].','.
			'images:"'.addslashes(_imageAdd(array('owner'=>'zayav'.$zayav_id))).'",'.
			'expense:['.$expense['json'].'],'.
			'zp_avai:'.zayav_zp_avai($z).
		'},'.
		'WORKER_SPISOK='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user`
										 WHERE `ws_id`=".WS_ID."
										 ORDER BY `dtime_add`").','.
//		'WORKER_ASS=_toAss(WORKER_SPISOK),'.
		'PRINT={'.
			'dtime:"'.FullDataTime($z['dtime_add']).'",'.
			'device:"'._deviceName($z['base_device_id']).'<b>'._vendorName($z['base_vendor_id'])._modelName($z['base_model_id']).'</b>",'.
			'color:"'._color($z['color_id'], $z['color_dop']).'",'.
			($z['imei'] ? 'imei:"'.$z['imei'].'",' : '').
			($z['serial'] ? 'serial:"'.$z['serial'].'",' : '').
			($z['equip'] ? 'equip:"'.zayavEquipSpisok($z['equip']).'",' : '').
			'client:"'._clientLink($z['client_id'], 1).'",'.
			'telefon:"'.query_value("SELECT `telefon` FROM `client` WHERE id=".$z['client_id']).'",'.
			'defect:"'.addslashes(str_replace("\n", ' ', query_value("SELECT `txt` FROM `vk_comment` WHERE `status` AND `table_name`='zayav' AND `table_id`=".$zayav_id." AND !`parent_id` ORDER BY `id` DESC"))).'"'.
		'};'.
	'</script>'.
	'<div id="zayav-info">'.
		'<div id="dopLinks">'.
			'<a class="img_del delete'.(!empty($money) ?  ' dn': '').'"></a>'.
			'<a class="link info sel">����������</a>'.
			'<a class="link zedit">��������������</a>'.
			'<a class="link acc_add">���������</a>'.
			'<a class="link income-add">������� �����</a>'.
			'<a class="link hist">�������</a>'.
		'</div>'.
		'<table class="itab">'.
			'<tr class="z-info"><td id="left">'.
				'<div class="headName">'.
					'������ �'.$z['nomer'].
					'<a class="img_print'._tooltip('����������� ���������', -75).'</a>'.
					//'<a href="'.SITE.'/view/_kvit.php?'.VALUES.'&id='.$zayav_id.'" class="img_word" title="����������� ��������� � Microsoft Word"></a>'.
				'</div>'.
				'<table class="tabInfo">'.
					'<tr><td class="label">����������: <td>'._deviceName($z['base_device_id']).'<a><b>'.MODEL.'</b></a>'.
					'<tr><td class="label">������:	 <td>'._clientLink($z['client_id']).
					'<tr><td class="label">���� �����:'.
						'<td class="dtime_add'._tooltip('������ ��� '._viewer($z['viewer_id_add'], 'name'), -70).FullDataTime($z['dtime_add']).
  ($z['pre_cost'] ? '<tr><td class="label">���������:<td><b class="'._tooltip('��������������� ��������� �������', -10, 'l').$z['pre_cost'].'</b> ���.' : '').
					'<tr><td class="label">������:'.
						'<td><div id="status" style="background-color:#'._zayavStatusColor($z['zayav_status']).'" class="status_place">'.
								_zayavStatusName($z['zayav_status']).
							'</div>'.
							'<div id="status_dtime">�� '.FullDataTime($z['zayav_status_dtime'], 1).'</div>'.
					'<tr class="acc_tr'.($z['accrual_sum'] ? '' : ' dn').'"><td class="label">���������: <td><b class="acc">'.$z['accrual_sum'].'</b> ���.'.
					'<tr class="op_tr'.($z['oplata_sum'] ? '' : ' dn').'"><td class="label">��������:	<td><b class="op">'.$z['oplata_sum'].'</b> ���.'.
						'<span class="dopl'.(DOPL ? '' : ' dn')._tooltip('����������� �������', -60).(DOPL > 0 ? '+' : '').DOPL.'</span>'.
				'</table>'.
				'<div id="kvit_spisok">'.zayav_kvit($zayav_id).'</div>'.
				'<div class="headBlue">�������</div>'.
				'<div id="ze_acc">'.
					'<a id="ze-edit">��������</a>'.
					'<h1>'.zayav_acc_sum($z).'</h1>'.
				'</div>'.
				$expense['html'].
				'<div class="headBlue rm">�������<a class="add remind_add">�������� �������</a></div>'.
				'<div id="remind_spisok">'.remind_spisok(remind_data(1, array('zayav'=>$z['id']))).'</div>'.
				_vkComment('zayav', $z['id']).
				'<div class="headBlue mon">���������� � �������'.
					'<a class="add income-add">������� �����</a>'.
					'<em>::</em>'.
					'<a class="add acc_add">���������</a>'.
				'</div>'.
				'<div id="money_spisok">'.zayav_info_money($zayav_id).'</div>'.

			'<td id="right">'.
				'<div id="foto">'._zayavImg($z, 'b').'</div>'.
				'<div class="headBlue">���������� �� ����������</div>'.
				'<div class="devContent">'.
					'<div class="devName">'._deviceName($z['base_device_id']).'<br />'.'<a>'.MODEL.'</a></div>'.
					'<table class="devInfo">'.
						($z['imei'] ? '<tr><th>imei:		 <td>'.$z['imei'] : '').
						($z['serial'] ? '<tr><th>serial:	 <td>'.$z['serial'] : '').
						($z['equip'] ? '<tr><th valign="top">��������:<td>'.zayavEquipSpisok($z['equip']) : '').
						($z['color_id'] ? '<tr><th>����:  <td>'._color($z['color_id'], $z['color_dop']) : '').
						'<tr><th>����������:<td><a class="dev_place status_place">'.($z['device_place'] ? @_devPlace($z['device_place']) : $z['device_place_other']).'</a>'.
						'<tr><th>���������: <td><a class="dev_status status_place">'._devStatus($z['device_status']).'</a>'.
					'</table>'.
				'</dev>'.

				'<div class="headBlue">'.
					'<a class="goZp" href="'.URL.'&p=zp&device='.$z['base_device_id'].'&vendor='.$z['base_vendor_id'].'&model='.$z['base_model_id'].'">������ ���������</a>'.
					'<a class="zpAdd add">��������</a>'.
				'</div>'.
				'<div id="zpSpisok">'.zayav_zp($z).'</div>'.

			'<tr class="z-hist"><td>'.
				'<div class="headName">������ �'.$z['nomer'].' - ������� ��������</div>'.
				$history['spisok'].
		'</table>'.
	'</div>';
}//zayav_info()
function zayav_kvit($zayav_id) {
	$sql = "SELECT * FROM `zayav_kvit` WHERE `ws_id`=".WS_ID." AND `active` AND `zayav_id`=".$zayav_id." ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$send = '<div class="headBlue">���������</div>'.
			'<table class="_spisok _money">';
	$n = 1;
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr><td><a class="zayav_kvit" val="'.$r['id'].'">��������� '.($n++).'</a>. '.
					'<span class="kvit_defect">'.$r['defect'].'</span>'.
				'<td class="dtime">'.FullDataTime($r['dtime_add']);
	$send .= '</table>';
	return $send;
}//zayav_kvit()
function zayav_acc_sum($z) {
	return $z['accrual_sum'] ? '����� ����� ����������: <b>'.$z['accrual_sum'].'</b> ���.' : '���������� ���.';
}//zayav_acc_sum()
function zayav_expense_test($v) {// �������� ������������ ������ �������� ������ ��� �������� � ����
	if(empty($v))
		return array();
	$send = array();
	$ex = explode(',', $v);
	foreach($ex as $r) {
		$ids = explode(':', $r);
		if(!_isnum($ids[0]))
			return false;
		if((_zayavExpense($ids[0], 'worker') || _zayavExpense($ids[0], 'zp')) && !_isnum($ids[1]))
			return false;
		if(_zayavExpense($ids[0], 'txt'))
			$ids[1] = win1251(htmlspecialchars(trim($ids[1])));
		if(!_zayavExpense($ids[0], 'txt') && !_zayavExpense($ids[0], 'worker') && !_zayavExpense($ids[0], 'zp'))
			$ids[1] = '';
		if(!_cena($ids[2]))
			return false;
		if(_zayavExpense($ids[0], 'worker') && !_isnum($ids[3]))
			return false;
		if(_zayavExpense($ids[0], 'worker') && !_isnum($ids[4]))
			return false;
		if(!$ids[1]) {
			$ids[3] = 0;
			$ids[4] = 0;
		}
		$send[] = $ids;
	}
	return $send;
}//zayav_expense_test()
function zayav_expense_spisok($z, $type='html') {//��������� ������ �������� ������
	$sql = "SELECT * FROM `zayav_expense` WHERE `zayav_id`=".$z['id']." ORDER BY `id`";
	$q = query($sql);
	$arr = array();
	while($r = mysql_fetch_assoc($q))
		$arr[$r['id']] = $r;

	$arr = _zpLink($arr);

	$html = '<table class="ze-spisok">';
	$json = array();
	$array = array();
	$expense_sum = 0;
	foreach($arr as $r) {
		$sum = round($r['sum'], 2);
		$expense_sum += $sum;
		$html .= '<tr>'.
			'<td class="name">'._zayavExpense($r['category_id']).
			'<td>'.(_zayavExpense($r['category_id'], 'txt') ? $r['txt'] : '').
				(_zayavExpense($r['category_id'], 'worker') ?
					'<a href="'.URL.'&p=report&d=salary&id='.$r['worker_id'].'&year='.$r['year'].'&mon='.$r['mon'].'&acc_id='.$r['id'].'">'.
						_viewer($r['worker_id'], 'name').
					'</a>'
				: '').
				(_zayavExpense($r['category_id'], 'zp') ? $r['zp_short'] : '').
			'<td class="sum">'.$sum.' �.';
		$json[] = '['.
			$r['category_id'].','.
			(_zayavExpense($r['category_id'], 'txt') ? '"'.$r['txt'].'"' : '').
			(_zayavExpense($r['category_id'], 'worker') ? intval($r['worker_id']) : '').
			(_zayavExpense($r['category_id'], 'zp') ? intval($r['zp_id']) : '').','.
			$sum.','.
			intval($r['mon']).','.
			intval($r['year']).
		']';
		$array[] = array(
			intval($r['category_id']),
				(_zayavExpense($r['category_id'], 'txt') ? $r['txt'] : '').
				(_zayavExpense($r['category_id'], 'worker') ? intval($r['worker_id']) : '').
				(_zayavExpense($r['category_id'], 'zp') ? intval($r['zp_id']) : ''),
			$sum,
			intval($r['mon']),
			intval($r['year'])
		);
	}
	if(!empty($arr))
		$html .= '<tr><td colspan="2" class="itog">����:<td class="sum"><b>'.$expense_sum.'</b> �.'.
				 '<tr><td colspan="2" class="itog">�������:<td class="sum">'.($z['accrual_sum'] - $expense_sum).' �.';
	$html .= '</table>';
	switch($type) {
		default:
		case 'html': return $html;
		case 'json': return implode(',', $json);
		case 'array': return $array;
		case 'all': return array(
			'html' => $html,
			'json' => implode(',', $json),
			'array' => $array
		);
	}
}//zayav_expense_spisok()
function zayav_info_money($zayav_id) {
	$sql = "(
		SELECT
			'acc' AS `type`,
			`id`,
			`sum`,
			`prim`,
			`dtime_add`,
			`viewer_id_add`
		FROM `accrual`
		WHERE `ws_id`=".WS_ID."
		  AND !`deleted`
		  AND `zayav_id`=".$zayav_id."
	) UNION (
		SELECT
			'op' AS `type`,
			`id`,
			`sum`,
			`prim`,
			`dtime_add`,
			`viewer_id_add`
		FROM `money`
		WHERE `ws_id`=".WS_ID."
		  AND !`deleted`
		  AND `sum`>0
		  AND `zayav_id`=".$zayav_id."
	)
	ORDER BY `dtime_add`";
	$q = query($sql);
	$send = '';
	if(mysql_num_rows($q)) {
		$send = '<table class="_spisok _money">';
		while($r = mysql_fetch_assoc($q))
			$send .= $r['type'] == 'acc' ? zayav_accrual_unit($r) : zayav_oplata_unit($r);
		$send .= '</table>';
	}

	return $send;
}//zayav_money()
function zayav_accrual_unit($r) {
	return '<tr><td class="sum '.$r['type']._tooltip('����������', -3).round($r['sum'], 2).
		'<td>'.$r['prim'].
		'<td class="dtime'._tooltip('�������� '._viewer($r['viewer_id_add'], 'name'), -40).FullDataTime($r['dtime_add']).
		'<td class="ed">'.
			'<div val="'.$r['id'].'" class="img_del acc_del'._tooltip('������� ����������', -64).'</div>';
}//zayav_accrual_unit()
function zayav_oplata_unit($r) {
	return '<tr val="'.$r['id'].'">'.
		'<td class="sum '.$r['type']._tooltip('�����', 8).round($r['sum'], 2).
		'<td>'.$r['prim'].
		'<td class="dtime'._tooltip('����� ��� '._viewer($r['viewer_id_add'], 'name'), -60).FullDataTime($r['dtime_add']).
		'<td class="ed">'.
			'<div class="img_del income-del'._tooltip('������� �����', -54).'</div>'.
			'<div class="img_rest income-rest'._tooltip('������������ �����', -69).'</div>';
}//zayav_oplata_unit()
function zayav_zp($z) {
	$sql = "SELECT *
			FROM `zp_catalog`
			WHERE `base_device_id`=".$z['base_device_id']."
			  AND `base_vendor_id`=".$z['base_vendor_id']."
			  AND `base_model_id`=".$z['base_model_id'];
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '<div class="_empty">��� '.MODEL.' ��������� ���.</div>';

	$zp = array();
	$ids = array();
	while($r = mysql_fetch_assoc($q)) {
		$id = $r['compat_id'] ? $r['compat_id'] : $r['id'];
		$zp[$id] = $r;
		$ids[$r['id']] = $r['id'];
		$ids[$r['compat_id']] = $r['compat_id'];
	}
	unset($ids[0]);

	$img = array();
	foreach($ids as $id)
		$img[] = 'zp'.$id;
	$img = _imageGet(array('owner' => $img));

	$ids = implode(',', $ids);
	$sql = "SELECT `zp_id` AS `id`,`count` FROM `zp_avai` WHERE `zp_id` IN (".$ids.")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[$r['id']]['avai'] = $r['count'];
	$sql = "SELECT `zp_id` AS `id`,`count`
				FROM `zp_zakaz`
				WHERE `zp_id` IN (".$ids.")
				  AND `zayav_id`=".$z['id'];
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[$r['id']]['zakaz'] = $r['count'];

	$send = '';
	foreach($zp as $id => $r)
		$send .=
			'<div class="unit" val="'.$r['id'].'">'.
				'<div class="image"><div>'.$img['zp'.$id]['img'].'</div></div>'.
				($r['bu'] ? '<span class="bu">�/�</span>' : '').
				'<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'"><b>'._zpName($r['name_id']).'</b> '.MODEL.'</a>'.
				($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
				($r['color_id'] ? '<div class="color">����: '._color($r['color_id']).'</div>' : '').
				'<div>'.
					(isset($r['zakaz']) ? '<a class="zakaz_ok">��������!</a>' : '<a class="zakaz">��������</a>').
					(isset($r['avai']) && $r['avai'] ? '<b class="avai">�������: '.$r['avai'].'</b> <a class="set">����������</a>' : '').
				'</div>'.
			'</div>';

	return $send;
}//zayav_zp()
function zayav_zp_avai($z) {
	$sql = "SELECT
				`id`,
				`compat_id`,
				`name_id`,
				`version`
			FROM `zp_catalog` `c`
			WHERE `c`.`base_device_id`=".$z['base_device_id']."
			  AND `c`.`base_vendor_id`=".$z['base_vendor_id']."
			  AND `c`.`base_model_id`=".$z['base_model_id'];
	$q = query($sql);
	$send = array();
	while($r = mysql_fetch_assoc($q))
		$send[] = '{'.
			'uid:'.($r['compat_id'] ? $r['compat_id'] : $r['id']).','.
			'title:"'.addslashes(_zpName($r['name_id'])).'"'.
			($r['version'] ? ',content:"'.addslashes(_zpName($r['name_id']).'<span>'.$r['version'].'</span>').'"' : '').
		'}';
	return '['.implode(',',$send).']';
}//zayav_zp_avai()
function zayav_msg_to_client($zayav_id) {
	$parent_id = 0;
	$sql = "SELECT `id`,`parent_id`
			FROM `vk_comment`
			WHERE `table_name`='zayav'
			  AND `table_id`=".$zayav_id."
			  AND `status`
			ORDER BY `id` DESC
			LIMIT 1";
	if($r = query_assoc($sql))
		$parent_id = $r['parent_id'] ? $r['parent_id'] : $r['id'];
	$sql = "INSERT INTO `vk_comment` (
				`table_name`,
				`table_id`,
				`txt`,
				`parent_id`,
				`viewer_id_add`
			) VALUES (
				'zayav',
				".$zayav_id.",
				'�������� �������.',
				".$parent_id.",
				".VIEWER_ID."
			)";
	query($sql);
	return utf8(_vkComment('zayav', $zayav_id));
}//zayav_msg_to_client()





// ---===! zp !===--- ������ ���������

function _zpLink($arr) {
	$ids = array();
	$ass = array();
	foreach($arr as $r) {
		$ids[$r['zp_id']] = $r['zp_id'];
		if($r['zp_id'])
			$ass[$r['zp_id']][] = $r['id'];
	}
	unset($ids[0]);
	if(!empty($ids)) {
		$sql = "SELECT *
	        FROM `zp_catalog`
	        WHERE `id` IN (".implode(',', $ids).")";
		$q = query($sql);
		while($r = mysql_fetch_assoc($q))
			foreach($ass[$r['id']] as $id) {
				$arr[$id]['zp_link'] =
					'<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'">'.
						'<b>'._zpName($r['name_id']).'</b> ��� '.
						_deviceName($r['base_device_id'], 1).
						_vendorName($r['base_vendor_id']).
						_modelName($r['base_model_id']).
					'</a>';
				$arr[$id]['zp_short']  =
					'<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'">'.
						'<b>'._zpName($r['name_id']).'</b> '.
						_vendorName($r['base_vendor_id']).
						_modelName($r['base_model_id']).
					'</a>';
			}
	}
	return $arr;
}//_zpLink()

function zpAddQuery($zp) {//�������� ����� �������� �� ������ � �� ������ ���������
	if(!isset($zp['compat_id']))
		$zp['compat_id'] = 0;
	$sql = "INSERT INTO `zp_catalog` (
				`name_id`,
				`base_device_id`,
				`base_vendor_id`,
				`base_model_id`,
				`bu`,
				`version`,
				`color_id`,
				`compat_id`,
				`viewer_id_add`,
				`find`
			) VALUES (
				".$zp['name_id'].",
				".$zp['base_device_id'].",
				".$zp['base_vendor_id'].",
				".$zp['base_model_id'].",
				".$zp['bu'].",
				'".addslashes($zp['version'])."',
				".$zp['color_id'].",
				".$zp['compat_id'].",
				".VIEWER_ID.",
				'".addslashes(_modelName($zp['base_model_id']).' '.$zp['version'])."'
			)";
	query($sql);
	return mysql_insert_id();
}//zpAddQuery()

function zpFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? intval($v['limit']) : 20,
		'find' => !empty($v['find']) ? htmlspecialchars(trim($v['find'])) : '',
		'menu' => !empty($v['menu']) && preg_match(REGEXP_NUMERIC, $v['menu']) ? intval($v['menu']) : 0,
		'name' => !empty($v['name']) && preg_match(REGEXP_NUMERIC, $v['name']) ? intval($v['name']) : 0,
		'device' => !empty($v['device']) && preg_match(REGEXP_NUMERIC, $v['device']) ? intval($v['device']) : 0,
		'vendor' => !empty($v['vendor']) && preg_match(REGEXP_NUMERIC, $v['vendor']) ? intval($v['vendor']) : 0,
		'model' => !empty($v['model']) && preg_match(REGEXP_NUMERIC, $v['model']) ? intval($v['model']) : 0,
		'bu' => !empty($v['bu']) && preg_match(REGEXP_BOOL, $v['bu']) ? intval($v['bu']) : 0,
	);
}//zpFilter()
function zp_spisok1($v) {
	$filter = zpFilter($v);
	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`id`";

	if(empty($filter['find']) && !$filter['model'])
		$cond .= " AND (!`compat_id` OR `compat_id`=`id`)";
	if(!empty($filter['find'])) {
		$cond .= " AND `find` LIKE '%".$filter['find']."%'";
		$reg = '/('.$filter['find'].')/i';
	}
	switch($filter['menu']) {
		case '1':
			$sql = "SELECT `zp_id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
			$ids = query_ids($sql);
			$cond .= " AND `id` IN (".$ids.")";
			break;
		case '2':
			$sql = "SELECT `zp_id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
			$ids = query_ids($sql);
			$cond .= " AND `id` NOT IN (".$ids.")";
			break;
		case '3':
			$sql = "SELECT `zp_id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." GROUP BY `zp_id`";
			$ids = query_ids($sql);
			$cond .= " AND `id` IN (".$ids.")";
			break;
	}
	if($filter['name'])
		$cond .= " AND `name_id`=".$filter['name'];
	if($filter['device'])
		$cond .= " AND `base_device_id`=".$filter['device'];
	if($filter['vendor'])
		$cond .= " AND `base_vendor_id`=".$filter['vendor'];
	if($filter['model'])
		$cond .= " AND `base_model_id`=".$filter['model'];
	if($filter['bu'])
		$cond .= " AND `bu`=1";

	$filter_clear = '<a class="clear">�������� ������</a>';
	$all = query_value("SELECT COUNT(`id`) FROM `zp_catalog` WHERE ".$cond." LIMIT 1");
	if(!$all)
		return array(
			'all' => 0,
			'result' => '��������� �� �������'.$filter_clear,
			'spisok' => '<div class="_empty">��������� �� �������</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => '�������'._end($all, '� ', '� ').$all.' �������'._end($all, '�', '�', '��').$filter_clear,
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
	            *,
	            0 AS `avai`,
	            0 AS `zakaz`,
	            '' AS `zz`
			FROM `zp_catalog`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$ids = array();
	$compat = array();
	$img = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['model'] = _modelName($r['base_model_id']);
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['model']))
				$r['model'] = preg_replace($reg, "<em>\\1</em>", $r['model'], 1);
			if(preg_match($reg, $r['version']))
				$r['version'] = preg_replace($reg, "<em>\\1</em>", $r['version'], 1);
		}
		$r['zp_id'] = $r['compat_id'] ? $r['compat_id'] : $r['id'];
		$compat[$r['zp_id']][] = $r['id'];
		$ids[$r['zp_id']] = $r['zp_id'];
		$img[] = 'zp'.$r['id'];
		$img[] = 'zp'.$r['compat_id'];
		$spisok[$r['id']] = $r;
	}

	$img = _imageGet(array(
		'owner' => $img,
		'view' => 1
	));

	// ��������� ���������� �� �������
	$sql = "SELECT
				`zp_id`,
				`count`
			FROM `zp_avai`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id` IN (".implode(',', $ids).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($compat[$r['zp_id']] as $id)
			$spisok[$id]['avai'] = $r['count'];

	// ��������� ���������� �� ������
	$sql = "SELECT
				`zp_id`,
				SUM(`count`) AS `count`
			FROM `zp_zakaz`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id` IN (".implode(',', $ids).")
			GROUP BY `zp_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		foreach($compat[$r['zp_id']] as $id)
			$spisok[$id]['zakaz'] = $r['count'];

	// ����������� ������ �� ������, ��� ������� ������ �����
	$sql = "SELECT
				`id`,
				`zp_id`,
				`zayav_id`
			FROM `zp_zakaz`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id` IN (".implode(',', $ids).")
			  AND `zayav_id`>0";
	$q = query($sql);
	$zakaz = array();
	while($r = mysql_fetch_assoc($q))
		$zakaz[$r['id']] = $r;
	$zakaz = _zayavNomerLink($zakaz);
	foreach($zakaz as $r)
		foreach($compat[$r['zp_id']] as $id)
			$spisok[$id]['zz'][] = $r['zayav_link'];

	$send['spisok'] = '';
	foreach($spisok as $id => $r) {
		$zakazEdit = '<span class="zzedit">���: <tt>�</tt><b>'.$r['zakaz'].'</b><tt>+</tt></span>';
		$send['spisok'] .= '<div class="unit" val="'.$id.'">'.
			'<table>'.
				'<tr><td class="img">'.$img['zp'.$id]['img'].
					'<td class="cont">'.
						($r['bu'] ? '<span class="bu">�/�</span>' : '').
						'<a href="'.URL.'&p=zp&d=info&id='.$id.'" class="name">'.
							_zpName($r['name_id']).
							' <b>'._vendorName($r['base_vendor_id']).$r['model'].'</b>'.
						'</a>'.
						($r['version'] ? '<div class="version">'.$r['version'].'</div>' : '').
						'<div class="for">��� '._deviceName($r['base_device_id'], 1).'</div>'.
						($r['color_id'] ? '<div class="color"><span>����:</span> '._color($r['color_id']).'</div>' : '').
						//($r['compat_id'] == $id ? '<b>�������</b>' : '').
						//($r['compat_id'] > 0 && $r['compat_id'] != $id ? '<b>�������������</b>' : '').
						($r['zz'] ? '<div class="zz">�������� ��� ����'.(count($r['zz']) > 1 ? '��' : '��').' '.implode(', ', $r['zz']).'</div>' : '').
					'<td class="action">'.
						($r['avai'] ? '<a class="avai avai_add">� �������: <b>'.$r['avai'].'</b></a>' : '<a class="hid avai_add">������ �������</a>').
						'<a class="zpzakaz'.($r['zakaz'] ? '' : ' hid').'">�����<span class="cnt">'.($r['zakaz'] ? '���: <b>'.$r['zakaz'].'</b>' : '���').'</span>'.$zakazEdit.'</a>'.
			'</table>'.
		'</div>';
	}
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="_next" val="'.($page + 1).'"><span>�������� ��� '.$c.' �������'._end($c, '�', '�', '��').'</span></div>';
	}
	return $send;
}//zp_spisok()
function zp_list1($v) {
	$data = zp_spisok($v);
	$filter = $data['filter'];
	$menu = array(
		0 => '����� �������',
		1 => '�������',
		2 => '��� � �������',
		3 => '�����'
	);
	return '<div id="zp">'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate" class="add"><a>������ ����� ��������</a></div>'.
					'<div id="find"></div>'.
					_rightLink('menu', $menu, $filter['menu']).
					'<div class="findHead">������������</div><INPUT type="hidden" id="zp_name" value="'.$filter['name'].'" />'.
					'<div class="findHead">����������</div><div id="dev"></div>'.
					_check('bu', '�/�', $filter['bu']).
		'</table>'.
		'<script type="text/javascript">'.
			'var ZP={'.
				'find:"'.addslashes($filter['find']).'",'.
				'device:'.$filter['device'].','.
				'vendor:'.$filter['vendor'].','.
				'model:'.$filter['model'].
			'};'.
		'</script>'.
	'</div>';
}//zp_list()

function zp_spisok($v) {
	$filter = zpFilter($v);
	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`id`";

	$all = query_value("SELECT COUNT(`id`) FROM `zp_catalog` WHERE ".$cond." LIMIT 1");
	if(!$all)
		return array(
			'all' => 0,
			'result' => '��������� �� �������',
			'spisok' => '<div class="_empty">��������� �� �������</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => '�������'._end($all, '� ', '� ').$all.' �������'._end($all, '�', '�', '��'),
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
	            *,
	            0 AS `avai`,
	            0 AS `zakaz`,
	            '' AS `zz`
			FROM `zp_catalog`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$ids = array();
	$compat = array();
	$img = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['model'] = _modelName($r['base_model_id']);
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['model']))
				$r['model'] = preg_replace($reg, "<em>\\1</em>", $r['model'], 1);
			if(preg_match($reg, $r['version']))
				$r['version'] = preg_replace($reg, "<em>\\1</em>", $r['version'], 1);
		}
		$r['zp_id'] = $r['compat_id'] ? $r['compat_id'] : $r['id'];
		$compat[$r['zp_id']][] = $r['id'];
		$ids[$r['zp_id']] = $r['zp_id'];
		$img[] = 'zp'.$r['id'];
		$img[] = 'zp'.$r['compat_id'];
		$spisok[$r['id']] = $r;
	}

	$send['spisok'] = '<table class="_spisok">';
	foreach($spisok as $id => $r) {
		$send['spisok'] .=
			'<tr><td>'.
				'<a href="'.URL.'&p=zp&d=info&id='.$id.'" class="name">'.
					_zpName($r['name_id']).
					' '._vendorName($r['base_vendor_id']).$r['model'].
				'</a>';
	}
	$send['spisok'] .= '</table>';

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .= '<div class="_next" val="'.($page + 1).'"><span>�������� ��� '.$c.' �������'._end($c, '�', '�', '��').'</span></div>';
	}
	return $send;
}//zp_spisok()
function zp_list($v) {
	$data = zp_spisok($v);
	$filter = $data['filter'];
	$menu = array(
		0 => '����� �������',
		1 => '�������',
		2 => '��� � �������',
		3 => '�����'
	);
	return
		'<div id="zp">'.
			'<div id="zp-head">'.
				'<table id="head-t"><tr>'.
					'<td><div id="find"></div>'.
					'<td><div class="vkButton"><button>������ ����� ��������</button></div>'.
				'</table>'.
				'<div id="zp-filter">'.
					'<input type="hidden" id="zp_name" value="'.$filter['name'].'" />'.
					'<input type="hidden" id="zp_dev" value="" />'.
					_check('bu', '�/�', $filter['bu']).
					'<a class="clear">�������� ������</a>'.
				'</div>'.
			'</div>'.
			'<div class="result">'.$data['result'].'</div>'.
			'<div id="zpsp">'.$data['spisok'].'</div>'.
			'<script type="text/javascript">'.
				'var ZP={'.
					'find:"'.addslashes($filter['find']).'",'.
					'device:'.$filter['device'].','.
					'vendor:'.$filter['vendor'].','.
					'model:'.$filter['model'].
				'};'.
			'</script>'.
		'</div>';
}//zp_list()


function zp_info($zp_id) {
	$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$zp_id;
	if(!$zp = mysql_fetch_assoc(query($sql)))
		return '�������� �� ����������';

	$compat_id = $zp['compat_id'] ? $zp['compat_id'] : $zp_id;
	if($zp_id != $compat_id) {
		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$compat_id;
		$compat = mysql_fetch_assoc(query($sql));
		$zp['color_id'] = $compat['color_id'];
		$zp['bu'] = $compat['bu'];
	}

	$avai = query_value("SELECT `count` FROM `zp_avai` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);

	$zakazCount = query_value("SELECT IFNULL(SUM(`count`),0) FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);
	$zakazEdit = '<span class="zzedit">���: <tt>�</tt><b>'.$zakazCount.'</b><tt>+</tt></span>';

	$compatSpisok = zp_compat_spisok($zp_id, $compat_id);
	$compatCount = count($compatSpisok);

	return
	'<script type="text/javascript">'.
		'var ZP={'.
			'id:'.$zp_id.','.
			'compat_id:'.$compat_id.','.
			'name_id:'.$zp['name_id'].','.
			'device:'.$zp['base_device_id'].','.
			'vendor:'.$zp['base_vendor_id'].','.
			'model:'.$zp['base_model_id'].','.
			'version:"'.$zp['version'].'",'.
			'color_id:'.$zp['color_id'].','.
			($zp['color_id'] ? 'color_name:"'._color($zp['color_id']).'",' : '').
			'bu:'.$zp['bu'].','.
			'name:"'._zpName($zp['name_id']).' <b>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</b>",'.
			'for:"��� '._deviceName($zp['base_device_id'], 1).'",'.
			'count:'.($avai ? $avai : 0).','.
			'images:"'.addslashes(_imageAdd(array('owner'=>'zp'.$compat_id))).'"'.
		'};'.
	'</script>'.
	'<div id="zpInfo">'.
		'<table class="ztab">'.
			'<tr><td class="left">'.
					'<div class="name">'.
						($zp['bu'] ? '<span>�/�</span>' : '').
						_zpName($zp['name_id']).
						'<em>'.$zp['version'].'</em>'.
					'</div>'.
					'<div class="for">'.
						'��� '._deviceName($zp['base_device_id'], 1).
						' <a>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</a>'.
					'</div>'.
					'<table class="prop">'.
						($zp['color_id'] ? '<tr><td class="label">����:<td>'._color($zp['color_id']) : '').
						//'<tr><td class="label">id:<td>'.$zp['id'].
						//'<tr><td class="label">compat_id:<td>'.$zp['compat_id'].
					'</table>'.
					'<div class="avai'.($avai ? '' : ' no').'">'.($avai ? '� ������� '.$avai.' ��.' : '��� � �������.').'</div>'.
					'<div class="added">��������� � ������� '.FullData($zp['dtime_add'], 1).'</div>'.
					'<div class="headBlue">��������</div>'.
					'<div class="move">'.zp_move($compat_id).'</div>'.
				'<td class="right">'.
					'<div id="foto">'.
						_imageGet(array(
							'owner' => 'zp'.$compat_id,
							'size' => 'b',
							'x' => 200,
							'y' => 320,
							'view' => 1
						)).
					'</div>'.
					'<div class="rightLink">'.
						'<a class="edit">�������������</a>'.
						'<a class="avai_add">������ �������</a>'.
						'<a class="zpzakaz unit'.($zakazCount ? '' : ' hid').'" val="'.$zp_id.'">'.
							'�����<span class="cnt">'.($zakazCount ? '���: <b>'.$zakazCount.'</b>' : '���').'</span>'.
							$zakazEdit.
						'</a>'.
						'<a class="set"> - ���������</a>'.
						'<a class="sale"> - �������</a>'.
						'<a class="defect"> - ����</a>'.
						'<a class="return"> - �������</a>'.
						'<a class="writeoff"> - ��������</a>'.
					'</div>'.
					'<div class="headBlue">�������������<a class="add compat_add">��������</a></div>'.
					'<div class="compatCount">'.zp_compat_count($compatCount).'</div>'.
					'<div class="compatSpisok">'.($compatCount ? implode($compatSpisok) : '').'</div>'.
		'</table>'.
	'</div>';
}//zp_info()
function zp_move($zp_id, $page=1) {
	$all = query_value("SELECT COUNT(`id`) FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
	if(!$all)
		return '<div class="unit">�������� �������� ���.</div>';

	$limit = 10;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `zp_move`
			WHERE `ws_id`=".WS_ID."
			  AND `zp_id`=".$zp_id."
			ORDER BY `id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['id']] = $r;
	$spisok = _zayavNomerLink($spisok);
	$spisok = _clientLink($spisok);
	$move = '';
	$type = array(
		'' => '������',
		'set' => '���������',
		'sale' => '�������',
		'defect' => '����',
		'return' => '�������',
		'writeoff' => '��������'
	);
	$n = 0;
	foreach($spisok as $r) {
		$cena = round($r['cena'], 2);
		$summa = round($r['summa'], 2);
		$count = abs($r['count']);
		$move .= '<div class="unit">'.
				(!$n++ && $page == 1 ? '<div val="'.$r['id'].'" class="img_del'._tooltip('������� ������', -50).'</div>' : '').
				$type[$r['type']].' <b>'.$count.'</b> ��. '.
				($summa ? '�� ����� '.$summa.' ���.'.($count > 1 ? ' <span class="cenaed">('.$cena.' ���./��.)</span> ' : '') : '').
				($r['zayav_id'] ? '�� ������ '.$r['zayav_link'].'.' : '').
				($r['client_id'] ? '������� '.$r['client_link'].'.' : '').
			($r['prim'] ? '<div class="prim">'.$r['prim'].'</div>' : '').
			'<div class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -1, 'l').FullDataTime($r['dtime_add']).'</div>'.
		'</div>';
	}
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$move .= '<div class="_next" val="'.($page + 1).'"><span>�������� ��� '.$c.' �����'._end($c, '�', '�', '��').'</span></div>';
	}
	return $move;
}//zp_move()
function zp_compat_spisok($zp_id, $compat_id=false) {
	if(!$compat_id)
		$compat_id = _zpCompatId($zp_id);
	$sql = "SELECT * FROM `zp_catalog` WHERE `id`!=".$zp_id." AND `compat_id`=".$compat_id;
	$q = query($sql);
	$send = array();
	while($r = mysql_fetch_assoc($q)) {
		$key = explode(' ', _modelName($r['base_model_id']));
		$send[$key[0]] = '<a href="'.URL.'&p=zp&d=info&id='.$r['id'].'">'.
			'<div class="img_del" val="'.$r['id'].'" title="��������� �������������"></div>'.
			_vendorName($r['base_vendor_id'])._modelName($r['base_model_id']).
		'</a>';
	}
	ksort($send);
	return $send;
}//zp_compat_spisok()
function zp_compat_count($c) {
	return $c ? $c.' ���������'._end($c, '�', '�', '') : '�������������� ���';
}






// ---===! report !===--- ������ �������

function report() {
	$d = empty($_GET['d']) ? 'history' : $_GET['d'];
	$d1 = '';
	$pages = array(
		'history' => '������� ��������',
		'remind' => '�������'.REMIND_ACTIVE.'<div class="img_add report_remind_add"></div>',
		'money' => '������',
		'salary' => '�/� �����������',
		'stat' => '����������'
	);

	$rightLink = '<div class="rightLink">';
	if($pages)
		foreach($pages as $p => $name)
			$rightLink .= '<a href="'.URL.'&p=report&d='.$p.'"'.($d == $p ? ' class="sel"' : '').'>'.$name.'</a>';
	$rightLink .= '</div>';

	$right = '';
	switch($d) {
		default: $d = 'history';
		case 'histoty':
			$data = history();
			$left = $data['spisok'];
			$right .= history_right();
			break;
		case 'remind':
			$data = remind_data();
			$left = !empty($data) ? remind_spisok($data) : '<div class="_empty">������� ���.</div>';
			$right .= remind_right();
			break;
		case 'money':
			$d1 = empty($_GET['d1']) ? 'income' : $_GET['d1'];
			switch($d1) {
				default:
					$d1 = 'income';
					switch(@$_GET['d2']) {
						case 'all': $left = income_all(); break;
						case 'year':
							if(empty($_GET['year']) || !preg_match(REGEXP_YEAR, $_GET['year'])) {
								$left = '������ ������������ ���.';
								break;
							}
							$left = income_year(intval($_GET['year']));
							break;
						case 'month':
							if(empty($_GET['mon']) || !preg_match(REGEXP_YEARMONTH, $_GET['mon'])) {
								$left = '������ ������������ �����.';
								break;
							}
							$left = income_month($_GET['mon']);
							break;
						default:
							if(!_calendarDataCheck(@$_GET['day']))
								$_GET['day'] = _calendarWeek();
							$left = income_day($_GET['day']);
							$right = income_right($_GET['day']);
					}
					break;
				case 'expense':
					$left = expense();
					$right .= expense_right();
					break;
				case 'invoice': $left = invoice(); break;
			}
			$left =
				'<div id="dopLinks">'.
					'<a class="link'.($d1 == 'income' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=income">�����������</a>'.
					'<a class="link'.($d1 == 'expense' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=expense">�������</a>'.
					'<a class="link'.($d1 == 'invoice' ? ' sel' : '').'" href="'.URL.'&p=report&d=money&d1=invoice">�����</a>'.
				'</div>'.
				$left;
			break;
		case 'salary':
			if($worker_id = _isnum(@$_GET['id'])) {
				$v = salaryFilter(array(
					'worker_id' => $worker_id,
					'mon' => intval(@$_GET['mon']),
					'year' => intval(@$_GET['year']),
					'acc_id' => intval(@$_GET['acc_id'])
				));
				$left = salary_worker($v);
				if(defined('WORKER_OK'))
					$right = '<input type="hidden" id="year" value="'.$v['year'].'" />'.
							 '<div id="monthList">'.salary_monthList($v).'</div>';
			} else
				$left = salary();
			break;
		case 'stat': $left = statistic(); break;
	}

	return
	'<table class="tabLR '.($d1 ? $d1 : $d).'" id="report">'.
		'<tr><td class="left">'.$left.
			'<td class="right">'.
				$rightLink.
				$right.
	'</table>';
}//report()

function history_insert($v) {
	if(empty($v['ws_id']))
		$v['ws_id'] = WS_ID;
	$type = $v['type'];
	unset($v['type']);
	_historyInsert($type, $v);
}//history_insert()
function history_types($v, $filter) {
	switch($v['type']) {
		case 1: return
					($filter['zayav_id'] ?
						'������ �������' :
						'������� ����� ������ '.$v['zayav_link'].
						($filter['client_id'] ? '' : ' ��� ������� '.$v['client_link'])
					).
					'.';
		case 2: return $filter['zayav_id'] ? '������ �������.' : '������� ������ '.$v['zayav_link'].'.';
		case 3: return ($filter['client_id'] ? '������ �����' : '����� ����� ������ '.$v['client_link']).'.';
		case 4:
			$statusPrev = $v['value1'] ? _zayavStatus($v['value1']) : '';
			$status = _zayavStatus($v['value']);
			return '������ ������ ������'.
					($filter['zayav_id'] ? '' : ' '.(!isset($v['zayav_link']) ? 'id=<b>'.$v['id'].'</b>' : $v['zayav_link'])).
					($v['value1'] ? ':<br />' : ' �� ').
					($v['value1'] ? '<span style="background-color:#'.$statusPrev['color'].'" class="zstatus">'.$statusPrev['name'].'</span> � ' : '').
					'<span style="background-color:#'.$status['color'].'" class="zstatus">'.$status['name'].'</span>';
		case 5: return '����������� ���������� �� ����� <b>'.$v['value'].'</b> ���.'.
						($filter['zayav_id'] ? '' : ' ��� ������ '.$v['zayav_link'].'.');
		case 6: return
			'����� ����� '.
			($v['value2'] ? '<span class="oplata">'._income($v['value2']).'</span> ' : '').
			'�� ����� <b>'.$v['value'].'</b> ���. '.
			($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
			($v['zayav_id'] && !$filter['zayav_id'] ? '�� ������ '.$v['zayav_link'].'. ' : '').
			($v['zp_id'] ? '<br />������� �������� '.$v['zp_link'].'. ' : '');
		case 7: return '��������������� ������ ������'.
						($filter['zayav_id'] ? '' : ' '.$v['zayav_link']).
						($v['value'] ? ':<div class="changes">'.$v['value'].'</div>' : '').
						'.';
		case 8:
			return '������� ���������� �� ����� <b>'.$v['value'].'</b> ���. '.
				($v['value1'] ? '('.$v['value1'].')' : '').
				($filter['zayav_id'] ? '' : ' � ������ '.$v['zayav_link']).
				'.';
		case 9:
			return '����� ����� '.
				($v['value2'] ? '<span class="oplata">'._income($v['value2']).'</span> ' : '').
				'�� ����� <b>'.$v['value'].'</b> ���. '.
				($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
				($v['zayav_id'] && !$filter['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (������� �������� '.$v['zp_link'].')' : '').
				'.';
		case 10: return '��������������� ������ �������'.
						($filter['client_id'] ? '' : ' '.$v['client_link']).
						($v['value'] ? ':<div class="changes">'.$v['value'].'</div>' : '.');
		case 11: return '����������� ����������� �������� <i>'.$v['value'].'</i> � '.$v['client_link'].'.';
		case 13: return '����������� ��������� �������� '.$v['zp_link'].
						($filter['zayav_id'] ? '' : ' �� ������ '.$v['zayav_link']).
						'.';
		case 15: return '����������� �������� �������� '.$v['zp_link'].'';
		case 16: return '��������� ������� �������� '.$v['zp_link'].'';
		case 17: return '����������� �������� '.$v['zp_link'].'';
		case 18: return '������� ������� �������� '.$v['zp_link'].' � ���������� '.$v['value'].' ��.';
		case 19:
			return '������������ ����� '.
				($v['value2'] ? '<span class="oplata">'._income($v['value2']).'</span> ' : '').
				'�� ����� <b>'.$v['value'].'</b> ���. '.
				($v['value1'] ? '<span class="prim">('.$v['value1'].')</span> ' : '').
				($v['zayav_id'] && !$filter['zayav_id'] ? ' � ������ '.$v['zayav_link'] : '').
				($v['zp_id'] ? ' (������� �������� '.$v['zp_link'].')' : '').
				'.';
		case 20:
			return '������� ����� �������'.
				($v['zayav_id'] && !$filter['zayav_id'] ? ' ��� ������ '.$v['zayav_link'] : '').
				($v['client_id']  && !$filter['client_id'] ? ' ��� ������� '.$v['client_link'] : '').
				'.';
		case 21: return '����� ������ �� ����� <b>'.$v['value'].'</b> ���.';
		case 22: return '����� ������ �� ����� <b>'.$v['value'].'</b> ���.';
//		case 23: return '�������� ������ ������� �� ����� <b>'.$v['value'].'</b> ���.';
		case 27: return '������������� ���������� �� ����� <b>'.$v['value'].'</b> ���. '.
						($v['value1'] ? '('.$v['value1'].')' : '').
						($filter['zayav_id'] ? '' : ' � ������ '.$v['zayav_link']).
						'.';
		case 28: return '��������� ������� ����� ��� ����� <span class="oplata">'._invoice($v['value1']).'</span>: <b>'.$v['value'].'</b> ���.';
		case 29: return '��������� ��������������� ����������'.
						($filter['zayav_id'] ? '' : ' �� ������ '.$v['zayav_link']).
						' ��� �������� �������:<div class="changes">'.$v['value'].'</div>';
		case 30: return '��������� ��������'.($filter['zayav_id'] ? '' : ' �� ������ '.$v['zayav_link']).':<div class="changes z">'.$v['value'].'</div>';

		case 35: return '�������� ������ � ���������� <u>'._viewer($v['value'], 'name').'</u>:<div class="changes">'.$v['value1'].'</div>.';
		case 36: return
			'�������� ���������� �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'��� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';
		case 37: return
			'������ �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'��� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';


		case 39:
			return '������� '.
					($v['value1'] > 100 ?
						'�� ���������� <u>'._viewer($v['value1'], 'name').'</u> ' :
						'�� ����� <span class="oplata">'._invoice($v['value1']).'</span> '
					).
					($v['value2'] > 100 ?
						'���������� <u>'._viewer($v['value2'], 'name').'</u> ' :
						'�� ���� <span class="oplata">'._invoice($v['value2']).'</span> '
					).
					'� ����� <b>'.$v['value'].'</b> ���. '.
					($v['value3'] ? '<span class="prim">('.$v['value3'].')</span>' : '');
		case 45: return '��������� ������� �/� � ����� <b>'.$v['value1'].'</b> ���. '.
						'��� ���������� <u>'._viewer($v['value'], 'name').'</u>. ';
		case 44: return
			'�������� ������ �� �/� �� ����� <b>'.$v['value'].'</b> '.
			($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
			'� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';

		case 46: return '�������������� ���������� �/� ���������� <u>'._viewer($v['value1'], 'name').'</u> '.
						'� ������� <b>'.$v['value'].'</b> ���. <em>('.$v['value2'].')</em>.';

		case 50: return '�������� ���������� �/� � ����� <b>'.$v['value'].'</b> ���. � ���������� <u>'._viewer($v['value1'], 'name').'</u>.';
		case 51: return '�������� ������ � ����� <b>'.$v['value'].'</b> ���. '.
						($v['value1'] ? '<em>('.$v['value1'].')</em> ' : '').
						'� ���������� <u>'._viewer($v['value2'], 'name').'</u>.';

		case 1001: return '� ����������: ���������� ������ ���������� <u>'._viewer($v['value'], 'name').'</u>.';
		case 1002: return '� ����������: �������� ���������� <u>'._viewer($v['value'], 'name').'</u>.';

		case 1003: return '� ����������: ��������� �������� ����������:<div class="changes">'.$v['value'].'</div>';
		case 1004: return '� ����������: ���������� �������.';

		case 1005: return '� ����������: �������� ����� ��������� �������� ���������� <u>'.$v['value'].'</u>.';
		case 1006: return '� ����������: ��������� ������ ��������� �������� ���������� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1007: return '� ����������: �������� ��������� �������� ���������� <u>'.$v['value'].'</u>.';

		case 1008: return '� ����������: �������� ������ ����� <u>'.$v['value'].'</u>.';
		case 1009: return '� ����������: ��������� ������ ����� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1010: return '� ����������: �������� ����� <u>'.$v['value'].'</u>.';

		case 1011: return '� ����������: �������� ������ ���� ������� <u>'.$v['value'].'</u>.';
		case 1012: return '� ����������: ��������� ���� ������� <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1013: return '� ����������: �������� ���� ������� <u>'.$v['value'].'</u>.';

		case 1014: return '<a href="'.URL.'&p=setup&d=zayavexpense">� ����������:</a> �������� ����� ��������� �������� ������ <u>'.$v['value'].'</u>.';
		case 1015: return '<a href="'.URL.'&p=setup&d=zayavexpense">� ����������:</a> ��������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>:<div class="changes">'.$v['value1'].'</div>';
		case 1016: return '<a href="'.URL.'&p=setup&d=zayavexpense">� ����������:</a> �������� ������ ��������� �������� ������ <u>'.$v['value'].'</u>.';

		default: return $v['type'];
	}
}//history_types()
function history_group($action) {
	switch($action) {
		case 1: return '3,10,11';
		case 2: return '1,2,4,5,6,7,8,9,13';
		case 3: return '13,14,15,16,17,18';
		case 4: return '6,9,12,19';
	}
	return 0;
}//history_types_group()
function history_right() {
	$sql = "SELECT DISTINCT `viewer_id_add`
			FROM `history`
			WHERE `ws_id`=".WS_ID."
			  AND `viewer_id_add`";
	$q = query($sql);
	$viewer = array();
	while($r = mysql_fetch_assoc($q))
		$viewer[] = $r['viewer_id_add'];
	$workers = array();
	foreach($viewer as $id)
		$workers[] = '{uid:'.$id.',title:"'._viewer($id, 'name').'"}';
	return
	'<script type="text/javascript">var WORKERS=['.implode(',', $workers).'];</script>'.
	'<div class="findHead">���������</div><input type="hidden" id="viewer_id_add">'.
	'<div class="findHead">��������</div><input type="hidden" id="action">';
}//history_right()
function history($v=array()) {
	return _history(
		'history_types',
		array('_clientLink', '_zayavNomerLink', '_zpLink'),
		$v,
		array(
			'ws_id' => WS_ID,
			'client_id' => !empty($v['client_id']) && _isnum($v['client_id']) ? intval($v['client_id']) : 0,
			'zayav_id' => !empty($v['zayav_id']) && _isnum($v['zayav_id']) ? intval($v['zayav_id']) : 0,
			'zp_id' => !empty($v['zp_id']) && _isnum($v['zp_id']) ? intval($v['zp_id']) : 0
		)
	);
}//history()

function remind_right() {
	return
		'<div class="findHead">��������� �������</div>'.
		_radio('status', array(1=>'��������',2=>'���������',0=>'��������'), 1, 1).
		_check('private', '������');
}//remind_right()
function remind_data($page=1, $filter=array()) {
	$cond = "`ws_id`=".WS_ID." AND `status`=".(isset($filter['status']) ? intval($filter['status']) : 1);
	if(!empty($filter['private']))
		$cond .= " AND `private`=1";
	if(!empty($filter['zayav']))
		$cond .= " AND `zayav_id`=".intval($filter['zayav']);
	if(!empty($filter['client'])) {
		$client_id = intval($filter['client']);
		$cond .= " AND `client_id`=".$client_id;
		$sql = "SELECT `id` FROM `zayav` WHERE `ws_id`=".WS_ID." AND `zayav_status`>0 AND `client_id`=".$client_id;
		$q = query($sql);
		$zayav_ids = array();
		while($r = mysql_fetch_assoc($q))
			$zayav_ids[] = $r['id'];
		if(!empty($zayav_ids))
			$cond .= " OR `ws_id`=".WS_ID." AND `status`=1 AND `zayav_id` IN (".implode(',', $zayav_ids).")";
	}
	$send['all'] = query_value("SELECT COUNT(`id`) FROM `reminder` WHERE ".$cond);
	if(!$send['all'])
		return array();

	$limit = 20;
	$start = ($page - 1) * $limit;
	$sql = "SELECT *
			FROM `reminder`
			WHERE ".$cond."
			ORDER BY `day` ASC,`id` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$send['spisok'] = array();
	while($r = mysql_fetch_assoc($q))
		$send['spisok'][$r['id']] = $r;
	$send['spisok'] = _clientLink($send['spisok']);
	$send['spisok'] = _zayavNomerLink($send['spisok']);
	if($start + $limit < $send['all']) {
		$send['page'] = ++$page;
		$c = $send['all'] - $start - $limit;
		$send['c'] = $c > $limit ? $limit : $c;
	}
	$send['filter'] = $filter;
	return $send;
}//remind_data()
function remind_spisok($data) {
	if(empty($data['spisok']))
		return '';
	$send = '';
	$today = strtotime(strftime("%Y-%m-%d", time()));
	foreach($data['spisok'] as $r) {
		$day_leave = (strtotime($r['day']) - $today) / 3600 / 24;
		$leave = '';
		if($day_leave < 0)
			$leave = '���������'._end($day_leave * -1, ' ', '� ').round($day_leave * -1)._end($day_leave * -1, ' ����', ' ���', ' ����');
		elseif($day_leave > 2)
			$leave = '�����'._end($day_leave, '�� ', '��� ').$day_leave._end($day_leave, ' ����', ' ���', ' ����');
		else
			switch($day_leave) {
				case 0: $leave = '�������'; break;
				case 1: $leave = '������'; break;
				case 2: $leave = '�����������'; break;
			}

		if($r['status'] == 0) $color = 'grey';
		elseif($r['status'] == 2) $color = 'green';
		elseif($day_leave > 0) $color = 'blue';
		elseif($day_leave < 0) $color = 'redd';
		else $color = 'yellow';
		// ��������� ������
		switch($r['status']) {
			case 2: $rem_cond = "<EM>���������.</EM>"; break;
			case 0: $rem_cond = "<EM>��������.</EM>"; break;
			default:
				$rem_cond = '<EM>��������� '.($day_leave == 0 ? '' : '�� ').'</EM>'.
					($day_leave >= 0 && $day_leave < 3 ? $leave : FullData($r['day'], 1)).
					($day_leave > 2 || $day_leave < 0 ? '<SPAN>, '.$leave.'</SPAN>' : '');
		}
		$send .= '<div class="remind_unit '.$color.'">'.
			'<div class="txt">'.
				($r['private'] ? '<u>������.</u> ' : '').
				($r['client_id'] && empty($data['filter']['client']) ? '������ '.$r['client_link'].': ' : '').
				($r['zayav_id'] && empty($data['filter']['zayav']) ? '������ '.@$r['zayav_link'].': ' : '').
				'<b>'.$r['txt'].'</b>'.
			'</div>'.
			'<div class="day">'.
				'<div class="action">'.
					($r['status'] == 1 ? '<a class="edit" val="'.$r['id'].'">��������</a> :: ' : '').
					'<a class="hist_a">�������</a>'.
				'</div>'.
				$rem_cond.
				'<div class="hist">'.$r['history'].'</div>'.
			'</div>'.
		'</div>';
	}
	if(isset($data['page']))
		$send .= '<div class="_next" id="remind_next" val="'.$data['page'].'">'.
					'<span>�������� ��� '.$data['c'].' ������'._end($data['c'], '�', '�', '�').'</span>'.
				 '</div>';
	return $send;
}//remind_spisok()

function income_path($data) {
	$ex = explode(':', $data);
	$d = explode('-', $ex[0]);
	define('YEAR', $d[0]);
	define('MON', @$d[1]);
	define('DAY', @$d[2]);
	$to = '';
	if(!empty($ex[1])) {
		$d = explode('-', $ex[1]);
		$to = ' - '.intval($d[2]).
			($d[1] != MON ? ' '._monthFull($d[1]) : '').
			($d[0] != YEAR ? ' '.$d[0] : '');
	}
	return
		'<a href="'.URL.'&p=report&d=money&d1=income&d2=all">���</a> � '.(YEAR ? '' : '<b>�� �� �����</b>').
		(MON ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.YEAR.'">'.YEAR.'</a> � ' : '<b>'.YEAR.'</b>').
		(DAY ? '<a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.YEAR.'-'.MON.'">'._monthDef(MON, 1).'</a> � ' : (MON ? '<b>'._monthDef(MON, 1).'</b>' : '')).
		(DAY ? '<b>'.intval(DAY).$to.'</b>' : '');
}//income_path()
function income_all() {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y') AS `year`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			GROUP BY DATE_FORMAT(`dtime_add`,'%Y')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$spisok = array();
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['year']] = '<tr>'.
			'<td><a href="'.URL.'&p=report&d=money&d1=income&d2=year&year='.$r['year'].'">'.$r['year'].'</a>'.
			'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	return
	'<div class="headName">����� �������� �� �����</div>'.
	'<table class="_spisok">'.
		'<tr><th>���'.
			'<th>�����'.
			implode('', $spisok).
	'</table>';
}//income_all()
function income_year($year) {
	$spisok = array();
	for($n = 1; $n <= (strftime('%Y', time()) == $year ? intval(strftime('%m', time())) : 12); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'._monthDef($n, 1).
				'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%m') AS `mon`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$year."%'
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[intval($r['mon'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&d2=month&mon='.$year.'-'.$r['mon'].'">'._monthDef($r['mon'], 1).'</a>'.
				'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	return
	'<div class="headName">����� �������� �� ������� �� '.$year.' ���</div>'.
	'<div class="inc-path">'.income_path($year).'</div>'.
	'<table class="_spisok">'.
		'<tr><th>�����'.
			'<th>�����'.
			implode('', $spisok).
	'</table>';
}//income_year()
function income_month($mon) {
	$path = income_path($mon);
	$spisok = array();
	for($n = 1; $n <= (strftime('%Y', time()) == YEAR ? intval(strftime('%d', time())) : date('t', strtotime($mon.'-01'))); $n++)
		$spisok[$n] =
			'<tr><td class="r grey">'.$n.'.'.MON.'.'.YEAR.
			'<td class="r">';
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%d') AS `day`,
				   SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE '".$mon."%'
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[intval($r['day'])] =
			'<tr><td class="r"><a href="'.URL.'&p=report&d=money&d1=income&day='.$mon.'-'.$r['day'].'">'.intval($r['day']).'.'.MON.'.'.YEAR.'</a>'.
			'<td class="r"><b>'._sumSpace($r['sum']).'</b>';

	return
	'<div class="headName">����� �������� �� ���� �� '._monthDef(MON, 1).' '.YEAR.'</div>'.
	'<div class="inc-path">'.$path.'</div>'.
	'<table class="_spisok sums">'.
		'<tr><th>�����'.
			'<th>�����'.
			implode('', $spisok).
	'</table>';
}//income_month()
function income_days($month=0) {
	$sql = "SELECT DATE_FORMAT(`dtime_add`,'%Y-%m-%d') AS `day`
			FROM `money`
			WHERE `deleted`=0
			  AND `sum`>0
			  AND `dtime_add` LIKE ('".($month ? $month : strftime('%Y-%m'))."%')
			GROUP BY DATE_FORMAT(`dtime_add`,'%d')";
	$q = query($sql);
	$days = array();
	while($r = mysql_fetch_assoc($q))
		$days[$r['day']] = 1;
	return $days;
}//income_days()
function income_right($sel) { //������� ������ ������ ��� ������� ��������
	return
		_calendarFilter(array(
			'days' => income_days(),
			'func' => 'income_days',
			'sel' => $sel
		)).
		(VIEWER_ADMIN ? _check('del', '�������� �������') : '');
}//income_right()
function income_day($day) {
	$data = income_spisok(array('day'=>$day));
	return
		'<div class="headName">������ �����������<a class="add income-add">������ �����</a></div>'.
		'<div class="inc-path">'.income_path($day).'</div>'.
		'<div class="spisok">'.$data['spisok'].'</div>';
}//income_day()
function incomeFilter($v) {
	$send = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 30,
		'client_id' => !empty($v['client_id']) && preg_match(REGEXP_NUMERIC, $v['client_id']) ? $v['client_id'] : 0,
		'zayav_id' => !empty($v['zayav_id']) && preg_match(REGEXP_NUMERIC, $v['zayav_id']) ? $v['zayav_id'] : 0,
		'del' => isset($v['del']) && preg_match(REGEXP_BOOL, $v['del']) ? $v['del'] : 0,
		'day' => '',
		'from' => '',
		'to' => ''
	);
	$send = _calendarPeriod(@$v['day']) + $send;
	return $send;
}//incomeFilter()
function income_spisok($filter=array()) {
	$filter = incomeFilter($filter);

	$cond = "`ws_id`=".WS_ID." AND `sum`>0";

	if($filter['client_id'])
		$cond .= " AND `client_id`=".$filter['client_id'];
	if($filter['zayav_id'])
		$cond .= " AND `zayav_id`=".$filter['zayav_id'];
	if(!$filter['del'] || !VIEWER_ADMIN)
		$cond .= " AND !`deleted`";
	if($filter['day'])
		$cond .= " AND `dtime_add` LIKE '".$filter['day']."%'";
	if($filter['from'])
		$cond .= " AND `dtime_add`>='".$filter['from']." 00:00:00' AND `dtime_add`<='".$filter['to']." 23:59:59'";

	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$send = mysql_fetch_assoc(query($sql));
	$send['filter'] = $filter;
	if(!$send['all'])
		return $send + array('spisok' => '<div class="_empty">�������� ���.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($filter['page'] - 1) * $filter['limit'];


	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$money = array();
	while($r = mysql_fetch_assoc($q))
		$money[$r['id']] = $r;
	//$money = _viewer($money);
	$money = _zayavNomerLink($money);
	$money = _zpLink($money);

	$send['spisok'] = $page > 1 ? '' :
		'<input type="hidden" id="money_limit" value="'.$filter['limit'].'" />'.
		'<input type="hidden" id="money_client_id" value="'.$filter['client_id'].'" />'.
		'<input type="hidden" id="money_zayav_id" value="'.$filter['zayav_id'].'" />'.
		'<div class="_moneysum">'.
			'�������'._end($all, '', '�').' <b>'.$all.'</b> ������'._end($all, '', '�', '��').
			' �� ����� <b>'._sumSpace($send['sum']).'</b> ���.'.
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>�����'.
				'<th>��������'.
				'<th>����'.
				'<th>';

	foreach($money as $r)
		$send['spisok'] .= income_unit($r, $filter);
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'" id="income_next"><td colspan="4">'.
				'<span>�������� ��� '.$c.' ������'._end($c, '', '�', '��').'</span>';
	}
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//income_spisok()
function income_unit($r) {
	$about = '';
	if($r['zayav_id'])
		$about = '������ '.$r['zayav_link'];
	if($r['zp_id'])
		$about = '������� �������� '.$r['zp_link'];
	$about .= ($about ? '. ' : '').$r['prim'];
	return
		'<tr'.($r['deleted'] ? ' class="deleted"' : '').' val="'.$r['id'].'">'.
			'<td class="sum">'._sumSpace($r['sum']).
			'<td>'.$about.
			'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -20).FullDataTime($r['dtime_add']).
			'<td class="ed">'.
				'<div class="img_del income-del'._tooltip('������� �����', -54).'</div>'.
				'<div class="img_rest income-rest'._tooltip('������������ �����', -69).'</div>';
}//income_unit()
function income_insert($v) {
	$v = array(
		'client_id' => empty($v['client_id']) ? 0 : intval($v['client_id']),
		'zayav_id' => empty($v['zayav_id']) ? 0 : intval($v['zayav_id']),
		'zp_id' => empty($v['zp_id']) ? 0 : intval($v['zp_id']),
		'income_id' => intval($v['income_id']),
		'sum' => round(str_replace(',', '.', $v['sum']), 2),
		'prim' => win1251(htmlspecialchars(trim($v['prim'])))
	);

	if($v['zayav_id']) {
		$sql = "SELECT * FROM `zayav` WHERE `ws_id`=".WS_ID." AND !`deleted` AND `id`=".$v['zayav_id'];
		if(!$r = mysql_fetch_assoc(query($sql)))
			return false;
		$v['client_id'] = $r['client_id'];
	}

	$sql = "INSERT INTO `money` (
				`ws_id`,
				`client_id`,
				`zayav_id`,
				`zp_id`,
				`invoice_id`,
				`income_id`,
				`sum`,
				`prim`,
				`viewer_id_add`
			) VALUES (
				".WS_ID.",
				".$v['client_id'].",
				".$v['zayav_id'].",
				".$v['zp_id'].",
				"._income($v['income_id'], 'invoice').",
				".$v['income_id'].",
				".$v['sum'].",
				'".addslashes($v['prim'])."',
				".VIEWER_ID."
			)";
	query($sql);

	invoice_history_insert(array(
		'action' => 1,
		'table' => 'money',
		'id' => mysql_insert_id()
	));
	clientBalansUpdate($v['client_id']);
	zayavBalansUpdate($v['zayav_id']);

	history_insert(array(
		'type' => 6,
		'client_id' => $v['client_id'],
		'zayav_id' => $v['zayav_id'],
		'zp_id' => $v['zp_id'],
		'value' => $v['sum'],
		'value1' => $v['prim'],
		'value2' => $v['income_id']
	));

	return $v;
}//income_insert()

function expenseFilter($v) {
	$send = array(
		'page' => _isnum(@$v['page']) ? $v['page'] : 1,
		'limit' => _isnum(@$v['limit']) ? $v['limit'] : 30,
		'category' => _isnum(@$v['category']),
		'worker' => _isnum(@$v['worker']),
		//'invoice_id' => !empty($v['invoice_id']) && preg_match(REGEXP_NUMERIC, $v['invoice_id']) ? $v['invoice_id'] : 0,
		'year' => !empty($v['year']) && preg_match(REGEXP_NUMERIC, $v['year']) ? $v['year'] : strftime('%Y'),
		'month' => isset($v['month']) ? $v['month'] : intval(strftime('%m'))
		//'del' => isset($v['del']) && preg_match(REGEXP_BOOL, $v['del']) ? $v['del'] : 0
	);
	$mon = array();
	if(!empty($send['month']))
		foreach(explode(',', $send['month']) as $r)
			$mon[$r] = 1;
	$send['month'] = $mon;
	return $send;
}//expenseFilter()
function expense_right() {
	$sql = "SELECT DISTINCT `worker_id` AS `viewer_id_add`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND `sum`<0
			  AND `worker_id`";
	$q = query($sql);
	$viewer = array();
	while($r = mysql_fetch_assoc($q)) {
		$r['id'] = $r['viewer_id_add'];
		$viewer[$r['viewer_id_add']] = $r;
	}
	$viewer = _viewer($viewer);
	$workers = array();
	foreach($viewer as $id => $w)
		$workers[] = '{uid:'.$id.',title:"'.$w['viewer_name'].'"}';
	return '<script type="text/javascript">var WORKERS=['.implode(',', $workers).'];</script>'.
		'<div class="findHead">���������</div>'.
		'<input type="hidden" id="category">'.
		'<div class="findHead">���������</div>'.
		'<input type="hidden" id="worker">'.
		'<input type="hidden" id="year">'.
		'<div id="monthList">'.expenseMonthSum().'</div>';
}//expense_right()
function expenseMonthSum($v=array()) {
	$filter = expenseFilter($v);
	$sql = "SELECT
				DISTINCT(DATE_FORMAT(`dtime_add`,'%m')) AS `month`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `sum`<0
			  AND `dtime_add` LIKE '".$filter['year']."-%'".
			  ($filter['category'] ? " AND `expense_id`=".$filter['category'] : '').
			  ($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '')."
			GROUP BY DATE_FORMAT(`dtime_add`,'%m')
			ORDER BY `dtime_add` ASC";
	$q = query($sql);
	$res = array();
	while($r = mysql_fetch_assoc($q))
		$res[intval($r['month'])] = abs($r['sum']);
	$send = '';
	for($n = 1; $n <= 12; $n++)
		$send .= _check(
			'c'.$n,
			_monthDef($n).(isset($res[$n]) ? '<span class="sum">'.$res[$n].'</span>' : ''),
			isset($filter['month'][$n]),
			1
		);
	return $send;
}//expenseMonthSum()
function expense() {
	$data = expense_spisok();
	$year = array();
	for($n = 2014; $n <= strftime('%Y'); $n++)
		$year[$n] = $n;
	return
	'<script type="text/javascript">'.
		'var WORKERS='.query_selJson("SELECT `viewer_id`,CONCAT(`first_name`,' ',`last_name`) FROM `vk_user` WHERE `ws_id`=".WS_ID).','.
			'MON_SPISOK='._selJson(_monthDef(0, 1)).','.
			'YEAR_SPISOK='._selJson($year).';'.
	'</script>'.
	'<div class="headName">������ �������� ����������<a class="add">������ ����� ������</a></div>'.
	'<div id="spisok">'.$data['spisok'].'</div>';
}//expense()
function expense_spisok($v=array()) {
	$filter = expenseFilter($v);
	$dtime = array();
	foreach($filter['month'] as $mon => $k)
		$dtime[] = "`dtime_add` LIKE '".$filter['year']."-".($mon < 10 ? 0 : '').$mon."%'";
	$cond = "`ws_id`=".WS_ID."
			 AND !`deleted`
			 AND `sum`<0".
			 (!empty($dtime) ? " AND (".implode(' OR ', $dtime).")" : '').
			 ($filter['category'] ? ' AND `expense_id`='.$filter['category'] : '').
			 ($filter['worker'] ? " AND `worker_id`=".$filter['worker'] : '');

	$sql = "SELECT
				COUNT(`id`) AS `all`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE ".$cond;
	$send = mysql_fetch_assoc(query($sql));
	$send['filter'] = $filter;
	if(!$send['all'])
		return $send + array('spisok' => '<div class="_empty">�������� ���.</div>');

	$all = $send['all'];
	$page = $filter['page'];
	$limit = $filter['limit'];
	$start = ($page - 1) * $limit;

	$send['spisok'] = $page == 1 ?
		'<div class="summa">'.
			'�������'._end($all, '�', '�').' <b>'.$all.'</b> �����'._end($all, '�', '�', '��').
			' �� ����� <b>'.abs($send['sum']).'</b> ���.'.
			(empty($dtime) ? ' �� �� �����.' : '').
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>�����'.
				'<th>��������'.
				'<th>����'.
				'<th>'
	: '';
	$sql = "SELECT *
			FROM `money`
			WHERE ".$cond."
			ORDER BY `dtime_add` DESC
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[$r['id']] = $r;
	$expense = _viewer($expense);
	foreach($expense as $r)
		$send['spisok'] .= '<tr'.($r['deleted'] ? ' class="deleted"' : '').'>'.
			'<td class="sum"><b>'.abs($r['sum']).'</b>'.
			'<td>'.($r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).($r['prim'] || $r['worker_id'] ? ':' : '').'</span> ' : '').
				   ($r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u>'.
				   ($r['prim'] ? ', ' : '') : '').$r['prim'].
			'<td class="dtime'._tooltip(viewerAdded($r['viewer_id_add']), -20).FullDataTime($r['dtime_add']).
			'<td class="ed"><div val="'.$r['id'].'" class="img_del'._tooltip('�������', -29).'</div>';
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'"><td colspan="4">'.
				'<span>�������� ��� '.$c.' �����'._end($c, '�', '�', '��').'</span>';
	}
	if($page == 1)
		$send['spisok'] .= '</table>';
	return $send;
}//expense_spisok()

function _invoiceBalans($invoice_id, $start=false) {// ��������� �������� ������� �����
	if($start === false)
		$start = _invoice($invoice_id, 'start');
	$income = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `money` WHERE !`deleted` AND `invoice_id`=".$invoice_id);
	$from = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE `invoice_from`=".$invoice_id);
	$to = query_value("SELECT IFNULL(SUM(`sum`),0) FROM `invoice_transfer` WHERE `invoice_to`=".$invoice_id);
	return round($income - $start - $from + $to, 2);
}//_invoiceBalans()
function invoice() {
	$sql = "SELECT `viewer_id` FROM `vk_user_rules` WHERE `key`='RULES_GETMONEY' AND `value`";
	$q = query($sql);
	$worker_getmoney = array();
	while($r = mysql_fetch_assoc($q))
		$worker_getmoney[] = '{'.
				'uid:'.$r['viewer_id'].','.
				'title:"'.addslashes(_viewer($r['viewer_id'], 'name')).'"'.
			'}';
	return
		'<script type="text/javascript">'.
			'var W_GETMONEY=['.implode(',', $worker_getmoney).'];'.
		'</script>'.
		'<div class="headName">'.
			'�����'.
			'<a class="add transfer">������� ����� �������</a>'.
			'<span>::</span>'.
			'<a href="'.URL.'&p=setup&d=invoice" class="add">���������� �������</a>'.
		'</div>'.
		'<div id="invoice-spisok">'.invoice_spisok().'</div>'.
		'<div class="headName">������� ���������</div>'.
		'<div class="transfer-spisok">'.transfer_spisok().'</div>';
}//invoice()
function invoice_spisok() {
	$invoice = _invoice();
	if(empty($invoice))
		return '����� �� ����������.';

	$send = '<table class="_spisok">';
	foreach($invoice as $r)
		$send .= '<tr>'.
			'<td class="name">'.
				'<b>'.$r['name'].'</b>'.
				'<div class="about">'.$r['about'].'</div>'.
		($r['start'] != -1 ?
			'<td class="balans"><b>'._sumSpace(_invoiceBalans($r['id'])).'</b> ���.'.
			'<td><div val="'.$r['id'].'" class="img_note'._tooltip('���������� ������� ��������', -95).'</div>'
		: '').
		(VIEWER_ADMIN || $r['start'] != -1 ? '<td><a class="invoice_set" val="'.$r['id'].'">����������<br />������� �����</a>' : '');
	$send .= '</table>';
	return $send;
}//invoice_spisok()
function transfer_spisok($v=array()) {
	$v = array(
		//	'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		//	'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 15,
	);
	$sql = "SELECT *
	        FROM `invoice_transfer`
	        WHERE `id`
	        ORDER BY `id` DESC";
	$q = query($sql);
	$send = '<table class="_spisok _money">'.
		'<tr><th>C����'.
			'<th>�� �����'.
			'<th>�� ����'.
			'<th>��������'.
			'<th>����';
	while($r = mysql_fetch_assoc($q))
		$send .=
			'<tr><td class="sum">'._sumSpace($r['sum']).
				'<td>'.($r['invoice_from'] ? '<span class="type">'._invoice($r['invoice_from']).'</span>' : '').
					($r['worker_from'] && $r['invoice_from'] ? '<br />' : '').
					($r['worker_from'] ? _viewer($r['worker_from'], 'name') : '').
				'<td>'.($r['invoice_to'] ? '<span class="type">'._invoice($r['invoice_to']).'</span>' : '').
					($r['worker_to'] && $r['invoice_to'] ? '<br />' : '').
					($r['worker_to'] ? _viewer($r['worker_to'], 'name') : '').
				'<td class="about">'.$r['about'].
				'<td class="dtime">'.FullDataTime($r['dtime_add'], 1);
	$send .= '</table>';
	return $send;
}//transfer_spisok()
function invoice_history($v) {
	$v = array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? $v['page'] : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? $v['limit'] : 15,
		'invoice_id' => intval($v['invoice_id'])
	);
	$send = $v['page'] == 1 ?
				'<div>���� <u>'._invoice($v['invoice_id']).'</u>:</div>'.
				'<input type="hidden" id="invoice_history_id" value="'.$v['invoice_id'].'" />'
			: '';

	$all = query_value("SELECT COUNT(*) FROM `invoice_history` WHERE `invoice_id`=".$v['invoice_id']);
	if(!$all)
		return $send.'<br />������� ���.';

	$start = ($v['page'] - 1) * $v['limit'];
	$sql = "SELECT `h`.*,
				   IFNULL(`m`.`zayav_id`,0) AS `zayav_id`,
				   IFNULL(`m`.`zp_id`,0) AS `zp_id`,
				   IFNULL(`m`.`income_id`,0) AS `income_id`,
				   IFNULL(`m`.`expense_id`,0) AS `expense_id`,
				   IFNULL(`m`.`worker_id`,0) AS `worker_id`,
				   IFNULL(`m`.`prim`,'') AS `prim`,
				   IFNULL(`i`.`invoice_from`,0) AS `invoice_from`,
				   IFNULL(`i`.`invoice_to`,0) AS `invoice_to`,
				   IFNULL(`i`.`worker_from`,0) AS `worker_from`,
				   IFNULL(`i`.`worker_to`,0) AS `worker_to`
			FROM `invoice_history` `h`
				LEFT JOIN `money` `m`
				ON `h`.`table`='money' AND `h`.`table_id`=`m`.`id`
				LEFT JOIN `invoice_transfer` `i`
				ON `h`.`table`='invoice_transfer' AND `h`.`table_id`=`i`.`id`
			WHERE `h`.`invoice_id`=".$v['invoice_id']."
			ORDER BY `h`.`id` DESC
			LIMIT ".$start.",".$v['limit'];
	$q = query($sql);
	$history = array();
	while($r = mysql_fetch_assoc($q))
		$history[$r['id']] = $r;

	$history = _zayavNomerLink($history);
	$history = _zpLink($history);

	if($v['page'] == 1)
		$send .= '<table class="_spisok _money invoice-history">'.
			'<tr><th>��������'.
				'<th>�����'.
				'<th>������'.
				'<th>��������'.
				'<th>����';
	foreach($history as $r) {
		$about = '';
		if($r['zayav_id'])
			$about = '������ '.$r['zayav_link'].'. ';
		if($r['zp_id'])
			$about = '������� �������� '.$r['zp_link'].'. ';
		$about .= $r['prim'].' ';
		$worker = $r['worker_id'] ? '<u>'._viewer($r['worker_id'], 'name').'</u> ' : '';
		$expense = $r['expense_id'] ? '<span class="type">'._expense($r['expense_id']).(!trim($about) && !$worker ? '' : ': ').'</span> ' : '';
		if($r['invoice_from'] != $r['invoice_to']) {//����� �� �����, ������� �������
			if(!$r['invoice_to'])//������ ���� �������� ������������
				$about .= '�������� ���������� '._viewer($r['worker_to'], 'name');
			elseif(!$r['invoice_from'])//������ ���� �������� �� ������������
				$about .= '��������� �� ���������� '._viewer($r['worker_from'], 'name');
			elseif($r['invoice_id'] == $r['invoice_from'])//��������������� ���� ����� - ����������
				$about .= '����������� �� ���� <span class="type">'._invoice($r['invoice_to']).'</span>';
			elseif($r['invoice_id'] == $r['invoice_to'])//��������������� ���� ����� - ����������
				$about .= '����������� �� ����� <span class="type">'._invoice($r['invoice_from']).'</span>';;
		} else {//����� �����, ������� ����������
			if($r['invoice_id'] == $r['worker_from'])//��������������� ���� ���������� - ����������
				$about .= '����������� �� ���� <span class="type">'._invoice($r['invoice_to']).'</span> '._viewer($r['worker_to'], 'name');
			if($r['invoice_id'] == $r['worker_to'])//��������������� ���� ���������� - ����������
				$about .= '����������� �� ����� <span class="type">'._invoice($r['invoice_from']).'</span> '._viewer($r['worker_from'], 'name');
		}
		$send .=
			'<tr><td class="action">'.invoiceHistoryAction($r['action']).
				'<td class="sum">'.($r['sum'] != 0 ? _sumSpace($r['sum']) : '').
				'<td class="balans">'._sumSpace($r['balans']).
				'<td>'.$expense.$worker.$about.
				'<td class="dtime">'.FullDataTime($r['dtime_add']);
	}

	if($start + $v['limit'] < $all) {
		$c = $all - $start - $v['limit'];
		$c = $c > $v['limit'] ? $v['limit'] : $c;
		$send .=
			'<tr class="_next" val="'.($v['page'] + 1).'"><td colspan="5">'.
			'<span>�������� ��� '.$c.' �����'._end($c, '�', '�', '��').'</span>';
	}
	if($v['page'] == 1)
		$send .= '</table>';
	return $send;
}//invoice_history()
function invoiceHistoryAction($id, $i='name') {//�������� �������� � ������� ������
	$action = array(
		1 => array(
			'name' => '�������� �������',
			'znak' => ''
		),
		2 => array(
			'name' => '�������� �������',
			'znak' => '-'
		),
		3 => array(
			'name' => '�������������� �������',
			'znak' => ''
		),
		4 => array(
			'name' => '������� ����� �������',
			'znak' => ''
		),
		5 => array(
			'name' => '��������� ������� �����',
			'znak' => ''
		),
		6 => array(
			'name' => '�������� �������',
			'znak' => '-'
		),
		7 => array(
			'name' => '�������� �������',
			'znak' => ''
		),
		8 => array(
			'name' => '�������������� �������',
			'znak' => '-'
		),
		9 => array(
			'name' => '�������������� �������',
			'znak' => ''
		)
	);
	return $action[$id][$i];
}//invoiceHistoryAction()
function invoice_history_insert($v) {
	$v = array(
		'action' => $v['action'],
		'table' => empty($v['table']) ? '' : $v['table'],
		'id' => empty($v['id']) ? 0 : $v['id'],
		'sum' => empty($v['sum']) ? 0 : $v['sum'],
		'worker_id' => empty($v['worker_id']) ? 0 : $v['worker_id'],
		'invoice_id' => empty($v['invoice_id']) ? 0 : $v['invoice_id']
	);
	if($v['table']) {
		$r = query_assoc("SELECT * FROM `".$v['table']."` WHERE `id`=".$v['id']);
		$v['sum'] = abs($r['sum']);
		switch($v['table']) {
			case 'money':
				$v['invoice_id'] = $r['invoice_id'];
				$v['sum'] = invoiceHistoryAction($v['action'], 'znak').$v['sum'];
				break;
			case 'invoice_transfer':
				if(!$r['invoice_from'] && !$r['invoice_to'])
					return;
				if(!$r['invoice_from']) {//������ ������� � ������������
					$v['invoice_id'] = $r['invoice_to'];
					if($r['worker_to'])
						invoice_history_insert_sql($r['worker_to'], $v);
					break;
				}
				if(!$r['invoice_to']) {//�������� ������� ������������
					$v['invoice_id'] = $r['invoice_from'];
					$v['sum'] *= -1;
					if($r['worker_from'])
						invoice_history_insert_sql($r['worker_from'], $v);
					break;
				}
				//�������� �� ����� � �������� � �� ����� �����������
				$v['invoice_id'] = $r['invoice_from'];
				invoice_history_insert_sql($r['invoice_to'], $v);
				break;
		}
	}
	invoice_history_insert_sql($v['invoice_id'], $v);
}//invoice_history_insert()
function invoice_history_insert_sql($invoice_id, $v) {
	if(_invoice($invoice_id, 'start') == -1)
		return;
	$sql = "INSERT INTO `invoice_history` (
				`action`,
				`table`,
				`table_id`,
				`invoice_id`,
				`sum`,
				`balans`,
				`viewer_id_add`
			) VALUES (
				".$v['action'].",
				'".$v['table']."',
				".$v['id'].",
				".$invoice_id.",
				".$v['sum'].",
				"._invoiceBalans($invoice_id).",
				".VIEWER_ID."
			)";
	query($sql);
}

function statistic() {
	$sql = "SELECT
				SUM(`sum`) AS `sum`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `dtime`
			FROM `money`
			WHERE !`deleted`
			  AND `sum`>0
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$prihod = array();
	while($r = mysql_fetch_assoc($q))
		$prihod[] = array(strtotime($r['dtime']) * 1000, intval($r['sum']));

	$sql = "SELECT
				SUM(`sum`)*-1 AS `sum`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `dtime`
			FROM `money`
			WHERE !`deleted`
			  AND `sum`<0
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$expense = array();
	while($r = mysql_fetch_assoc($q))
		$expense[] = array(strtotime($r['dtime']) * 1000, intval($r['sum']));

	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `mon`
			FROM `client`
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$client = array();
	while($r = mysql_fetch_assoc($q))
		$client[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//����� ������
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE !`deleted`
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m-%d')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$zayav = array();
	while($r = mysql_fetch_assoc($q))
		$zayav[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));

	//����������� ������
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE !`deleted`
			  AND `zayav_status`=2
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayav_ok = array();
	while($r = mysql_fetch_assoc($q))
		$zayav_ok[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));

	//��������� ������
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d') AS `day`
			FROM `zayav`
			WHERE !`deleted`
			  AND `zayav_status`=3
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-%d')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayav_fail = array();
	while($r = mysql_fetch_assoc($q))
		$zayav_fail[] = array((strtotime($r['day']) + 40000) * 1000, intval($r['count']));



	//����� ������ - �����
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`dtime_add`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE !`deleted`
			GROUP BY DATE_FORMAT(`dtime_add`, '%Y-%m')
			ORDER BY `dtime_add`";
	$q = query($sql);
	$zayavmon = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//����������� ������ - �����
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE !`deleted`
			  AND `zayav_status`=2
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayavmon_ok = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon_ok[] = array(strtotime($r['mon']) * 1000, intval($r['count']));

	//��������� ������ - �����
	$sql = "SELECT
				COUNT(`id`) AS `count`,
				DATE_FORMAT(`zayav_status_dtime`, '%Y-%m-15') AS `mon`
			FROM `zayav`
			WHERE !`deleted`
			  AND `zayav_status`=3
			GROUP BY DATE_FORMAT(`zayav_status_dtime`, '%Y-%m')
			ORDER BY `zayav_status_dtime`";
	$q = query($sql);
	$zayavmon_fail = array();
	while($r = mysql_fetch_assoc($q))
		$zayavmon_fail[] = array(strtotime($r['mon']) * 1000, intval($r['count']));


	return
	'<script type="text/javascript" src="http://nyandoma'.(LOCAL ? '' : '.ru').'/js/highstock.js"></script>'.
	'<div id="statistic"></div>'.
	'<br /><br />'.
	'<div id="client-count"></div>'.
	'<br /><br />'.
	'<div id="zayav-count"></div>'.
	'<br /><br />'.
	'<div id="zayav-count-mon"></div>'.
	'<script type="text/javascript">'.
		'var statPrihod='.json_encode($prihod).','.
			'statRashod='.json_encode($expense).','.
			'CLIENT_COUNT='.json_encode($client).','.
			'ZAYAV_COUNT='.json_encode($zayav).','.
			'ZAYAV_OK='.json_encode($zayav_ok).','.
			'ZAYAV_FAIL='.json_encode($zayav_fail).';'.
			'ZAYAVMON_COUNT='.json_encode($zayavmon).','.
			'ZAYAVMON_OK='.json_encode($zayavmon_ok).','.
			'ZAYAVMON_FAIL='.json_encode($zayavmon_fail).';'.
	'</script>'.
	'<script type="text/javascript" src="'.SITE.'/js/statistic.js"></script>';
}//statistic()

function salary() {
	return
		'<div class="headName">���������� �������� �����������</div>'.
		'<div id="spisok">'.salary_spisok().'</div>';
}//salary()
function salary_spisok() {
	$sql = "SELECT
				`u`.`viewer_id`,
				`u`.`rate_sum`,
				`u`.`rate_period`,
				CONCAT(`u`.`first_name`,' ',`u`.`last_name`) AS `name`,
				IFNULL(SUM(`m`.`sum`),0) AS `zp`
			FROM `vk_user` AS `u`
				LEFT JOIN `money` AS `m`
				ON `u`.`viewer_id`=`m`.`worker_id`
					AND !`m`.`deleted`
					AND `m`.`worker_id`
					AND `m`.`sum`<0
			WHERE `u`.`ws_id`=".WS_ID."
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);
	$worker = array();
	while($r = mysql_fetch_assoc($q))
		$worker[$r['viewer_id']] = $r;

	//���������� � ��������
	$sql = "SELECT
 				`e`.`worker_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `sum`
			FROM `zayav_expense` AS `e`,
			 	 `zayav` AS `z`
			WHERE `z`.`ws_id`=".WS_ID."
			  AND `e`.`worker_id`
			  AND `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			GROUP BY `e`.`worker_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$worker[$r['worker_id']]['zp'] += $r['sum'];

	//���������� ��� ������
	$sql = "SELECT
 				`u`.`viewer_id`,
				IFNULL(SUM(`e`.`sum`),0) AS `ze`
			FROM `vk_user` AS `u`
				LEFT JOIN `zayav_expense` AS `e`
				ON `u`.`viewer_id`=`e`.`worker_id`
					AND `e`.`worker_id`
					AND !`e`.`zayav_id`
			WHERE `u`.`ws_id`=".WS_ID."
			GROUP BY `u`.`viewer_id`
			ORDER BY `u`.`dtime_add`";
	$q = query($sql);

	$send = '<table class="_spisok">'.
		'<tr><th>���'.
			'<th>������'.
			'<th>������';
	while($r = mysql_fetch_assoc($q)) {
		$w = $worker[$r['viewer_id']];
		$start = _viewer($r['viewer_id'], 'salary_balans_start');
		$balans = $start == -1 ? '' : round($w['zp'] + $r['ze'] + $start, 2);
		$send .=
			'<tr><td class="fio"><a href="'.URL.'&p=report&d=salary&id='.$r['viewer_id'].'" class="name">'.$w['name'].'</a>'.
				'<td class="rate">'.($w['rate_sum'] == 0 ? '' : '<b>'.round($w['rate_sum'], 2).'</b>/'.salaryPeriod($w['rate_period'])).
				'<td class="balans" style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans;
	}
	$send .= '</table>';
	return $send;
}//salary_spisok()
function salary_monthList($v) {
	$filter = salaryFilter($v);

	$acc = array();
	$zp = array();
	for($n = 1; $n <= 12; $n++) {
		$acc[$n] = 0;
		$zp[$n] = 0;
	}

	//��������� ���� ��������������, ������ ���������� � �� �������
	$sql = "SELECT
	            `mon`,
				SUM(`sum`) AS `sum`
			FROM `zayav_expense`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `year`=".$filter['year']."
			GROUP BY `mon`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$acc[intval($r['mon'])] = round($r['sum']);

	//��������� ���� ��
	$sql = "SELECT
	            `mon`,
				SUM(`sum`) AS `sum`
			FROM `money`
			WHERE !`deleted`
			  AND `worker_id`=".$filter['worker_id']."
			  AND `year`=".$filter['year']."
			GROUP BY `mon`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$zp[intval($r['mon'])] = abs(round($r['sum'], 2));

	$mon = array();
	foreach(_monthDef(0, 1) as $i => $r)
		$mon[$i] = $r.($acc[$i] || $zp[$i]? '<em>'.$acc[$i].'/'.$zp[$i].'</em>' : '');
	return _radio('salmon', $mon, $filter['mon'], 1);
}//salary_monthList()
function salaryFilter($v) {
	$v = array(
		'worker_id' => _isnum(@$v['worker_id']),
		'mon' => _isnum(@$v['mon']) ? intval($v['mon']) : intval(strftime('%m')),
		'year' => _isnum(@$v['year']) ? intval($v['year']) : intval(strftime('%Y')),
		'acc_id' => _isnum(@$v['acc_id'])
	);
	$v['month'] = _monthDef($v['mon'], 1).' '.$v['year'];
	$v['year-mon'] = $v['year'].'-'.($v['mon'] < 10 ? 0 : '').$v['mon'];
	return $v;
}//salaryFilter()
function salaryPeriod($v=false) {
	$arr = array(
		1 => '�����',
		2 => '������',
		3 => '����'
	);
	if($v == false)
		return $arr;
	return $arr[$v];
}//salaryPeriod()
function salary_worker($v) {
	$filter = salaryFilter($v);
	if(!query_value("SELECT COUNT(*) FROM `vk_user` WHERE `viewer_id`=".$filter['worker_id']))
		return '<h2>���������� �� ����������.</h2>';
	define('WORKER_OK', true);
	$year = array();
	for($n = 2014; $n <= $filter['year']; $n++)
		$year[$n] = $n;
	return
	'<script type="text/javascript">'.
		'var WORKER_ID='.$filter['worker_id'].','.
			'MON='.$filter['mon'].','.
			'MON_SPISOK='._selJson(_monthDef(0, 1)).','.
			'YEAR='.$filter['year'].','.
			'YEAR_SPISOK='._selJson($year).','.
			'RATE={'.
				'sum:'.round(_viewer($filter['worker_id'], 'rate_sum'), 2).','.
				'period:'._viewer($filter['worker_id'], 'rate_period').','.
				'day:'._viewer($filter['worker_id'], 'rate_day').
			'};'.
	'</script>'.
	'<div class="headName">'._viewer($filter['worker_id'], 'name').': ������� �/� �� <em>'.$filter['month'].'</em>.</div>'.
	'<div id="spisok">'.salary_worker_spisok($filter).'</div>';
}//salary_worker()
function salary_worker_spisok($v) {
	$filter = salaryFilter($v);

	if(!$filter['worker_id'])
		return '������������ id ����������';

	$start = _viewer($filter['worker_id'], 'salary_balans_start');
	if($start != -1) {
		$sMoney = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `money`
			WHERE `worker_id`=".$filter['worker_id']."
			  AND `sum`<0
			  AND !`deleted`");
		$sExpense = query_value("
			SELECT IFNULL(SUM(`sum`),0)
			FROM `zayav_expense`
			WHERE `mon`
			  AND `worker_id`=".$filter['worker_id']);
		$balans = round($sMoney + $sExpense + $start, 2);
		$balans = '<b style="color:#'.($balans < 0 ? 'A00' : '090').'">'.$balans.'</b> ���.';
	} else
		$balans = '<a class="start-set">����������</a>';

	$rate_sum = _cena(_viewer($filter['worker_id'], 'rate_sum'));
	$rate_period = _viewer($filter['worker_id'], 'rate_period');
	$rate_day = _viewer($filter['worker_id'], 'rate_day');
	$send =
	'<div class="uhead">'.
		'<h1>'.
			'������: '.
				($rate_sum
					? '<b>'.$rate_sum.'</b> ���.'.
					  '<span>('.
						($rate_period == 1 ? $rate_day.'-� ����� ������' : '').
						($rate_period == 2 ? '�����������, '.$rate_day.'-� ���� ������' : '').
						($rate_period == 3 ? '���������' : '').
					  ')</span>'
					: '���'
				).
			'<a class="rate-set">�������� ������</a>'.
		'</h1>'.
		'������: '.$balans.
		'<div class="a">'.
			'<a class="up">���������</a> :: '.
			'<a class="zp_add">������ �/�</a> :: '.
			'<a class="deduct">������ �����</a>'.
		'</div>'.
	'</div>'.
	'<div id="salary-sel">&nbsp;</div>';

	$send .= salary_worker_acc($filter);
	$send .= salary_worker_zp($filter);
	return $send;
}//salary_worker_spisok()
function salary_worker_acc($v) {
	$sql = "(SELECT
				'����������' AS `type`,
				`e`.`id`,
			    `e`.`sum`,
				'' AS `about`,
				`e`.`zayav_id`,
				`e`.`dtime_add`
			FROM `zayav_expense` `e`,
				 `zayav` `z`
			WHERE `z`.`id`=`e`.`zayav_id`
			  AND !`z`.`deleted`
			  AND `e`.`year`=".$v['year']."
			  AND `e`.`mon`=".$v['mon']."
			  AND `e`.`worker_id`=".$v['worker_id']."
			  AND `e`.`sum`>0
			GROUP BY `e`.`id`
		) UNION (
			SELECT
				'����������' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`dtime_add`
			FROM `zayav_expense`
			WHERE !`zayav_id`
			  AND `worker_id`=".$v['worker_id']."
			  AND `sum`>0
			  AND `year`=".$v['year']."
			  AND `mon`=".$v['mon']."
		) UNION (
			SELECT
				'�����' AS `type`,
				`id`,
			    `sum`,
				`txt` AS `about`,
				0 AS `zayav_id`,
				`dtime_add`
			FROM `zayav_expense`
			WHERE `worker_id`=".$v['worker_id']."
			  AND `sum`<0
			  AND `year`=".$v['year']."
			  AND `mon`=".$v['mon']."
		)
		ORDER BY `id` DESC";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$spisok = array();
	while($r = mysql_fetch_assoc($q)) {
		$key = strtotime($r['dtime_add']);
		while(isset($spisok[$key]))
			$key++;
		$spisok[$key] = $r;
	}

	$spisok = _zayavLink($spisok);
	krsort($spisok);

	$send = '<table class="_spisok _money">'.
		'<tr>'.
			'<th>���'.
			'<th>�����'.
			'<th>��������'.
			'<th>';

	foreach($spisok as $r) {
		$about = $r['zayav_id'] ?
			'<span class="s-zayav" style="background-color:#'.$r['zayav_status_color'].'">'.$r['zayav_link'].'</span>'.
			'<tt>�� '.$r['zayav_add'].'</tt>'
			:
			$r['about'];
		$send .=
			'<tr val="'.$r['id'].'" class="'.($v['acc_id'] == $r['id'] ? ' show' : '').'">'.
				'<td class="type">'.$r['type'].
				'<td class="sum">'.round($r['sum'], 2).
				'<td class="about">'.$about.
				'<td class="ed">'.
					($r['type'] == '����������' && !$r['zayav_id'] ? '<div class="img_del ze_del'._tooltip('�������', -29).'</div>' : '').
					($r['type'] == '�����' ? '<div class="img_del deduct_del'._tooltip('�������', -29).'</div>' : '');
	}
	$send .= '</table>';
	return $send;
}//salary_worker_acc()
function salary_worker_zp($v) {
	$sql = "SELECT *
			FROM `money`
			WHERE `ws_id`=".WS_ID."
			  AND !`deleted`
			  AND `worker_id`=".$v['worker_id']."
			  AND `sum`<0
			  AND `year`=".$v['year']."
			  AND `mon`=".$v['mon']."
			ORDER BY `id`";
	$q = query($sql);
	if(!mysql_num_rows($q))
		return '';
	$zp = '';
	$summa = 0;
	while($r = mysql_fetch_assoc($q)) {
		$sum = abs(round($r['sum'], 2));
		$summa += $sum;
		$zp .= '<tr>'.
			'<td class="sum">'.$sum.
			'<td class="about"><span class="type">'._invoice($r['invoice_id']).(empty($r['prim']) ? '' : ':').'</span> '.$r['prim'].
			'<td class="dtime">'.FullDataTime($r['dtime_add']).
			'<td class="ed"><div val="'.$r['id'].'" class="img_del zp_del'._tooltip('�������', -29).'</div>';
	}
	$send =
		'<div class="zp-head">'.
			'<b>�/� �� '.$v['month'].'</b>:'.
			'<span><a class="zp_add">������ �/�</a> :: �����: <b>'.$summa.'</b> ���.</span>'.
		'</div>'.
		'<table class="_spisok _money">'.
			'<tr><th>�����'.
				'<th>��������'.
				'<th>����'.
				'<th>'.
				$zp.
		'</table>';

	return $send;
}//salary_worker_zp()



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
function _viewerRules($viewer_id=VIEWER_ID, $rule='') {
	$key = CACHE_PREFIX.'viewer_rules_'.$viewer_id;
	$wr = xcache_get($key);
	if(empty($wr)) {
		$rules = query_ass("SELECT `key`,`value` FROM `vk_user_rules` WHERE `viewer_id`=".$viewer_id);
		$admin = _viewer($viewer_id, 'admin');
		$wr = _setupRules($rules, $admin);
		xcache_set($key, $wr, 86400);
	}
	return $rule ? $wr[$rule] : $wr;
}//_viewerRules()
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
			if(!preg_match(REGEXP_BOOL, $v))
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
