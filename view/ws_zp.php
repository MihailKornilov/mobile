<?php
// ---===! zp !===--- Секция запчастей

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
					'<a val="'.$r['id'].'" class="go-zp-info">'.
						'<b>'._zpName($r['name_id']).'</b> для '.
						_deviceName($r['base_device_id'], 1).
						_vendorName($r['base_vendor_id']).
						_modelName($r['base_model_id']).
					'</a>';
				$arr[$id]['zp_short']  =
					'<a val="'.$r['id'].'" class="go-zp-info">'.
						'<b>'._zpName($r['name_id']).'</b> '.
						_vendorName($r['base_vendor_id']).
						_modelName($r['base_model_id']).
					'</a>';
			}
	}
	return $arr;
}//_zpLink()

function zpAddQuery($zp) {//Внесение новой запчасти из заявки и из списка запчастей
	if(!isset($zp['compat_id']))
		$zp['compat_id'] = 0;
	$find = ($zp['base_model_id'] ? _modelName($zp['base_model_id']).' ' : '').$zp['version'];
	$sql = "INSERT INTO `zp_catalog` (
				`name_id`,
				`base_device_id`,
				`base_vendor_id`,
				`base_model_id`,
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
				'".addslashes($zp['version'])."',
				".$zp['color_id'].",
				".$zp['compat_id'].",
				".VIEWER_ID.",
				'".addslashes($find)."'
			)";
	query($sql);
	return mysql_insert_id();
}//zpAddQuery()

function zpFilter($v) {
	return array(
		'page' => !empty($v['page']) && preg_match(REGEXP_NUMERIC, $v['page']) ? intval($v['page']) : 1,
		'limit' => !empty($v['limit']) && preg_match(REGEXP_NUMERIC, $v['limit']) ? intval($v['limit']) : 100,
		'sort' => !empty($v['sort']) && preg_match(REGEXP_NUMERIC, $v['sort']) ? intval($v['sort']) : 0,
		'find' => !empty($v['find']) ? htmlspecialchars(trim($v['find'])) : '',
		'menu' => !empty($v['menu']) && preg_match(REGEXP_NUMERIC, $v['menu']) ? intval($v['menu']) : 0,
		'name' => !empty($v['name']) && preg_match(REGEXP_NUMERIC, $v['name']) ? intval($v['name']) : 0,
		'device' => !empty($v['device']) && preg_match(REGEXP_NUMERIC, $v['device']) ? intval($v['device']) : 0,
		'vendor' => !empty($v['vendor']) && preg_match(REGEXP_NUMERIC, $v['vendor']) ? intval($v['vendor']) : 0,
		'model' => !empty($v['model']) && preg_match(REGEXP_NUMERIC, $v['model']) ? intval($v['model']) : 0
	);
}//zpFilter()
function zp_spisok($v) {
	$filter = zpFilter($v);
	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`c`.`id`";

	if(empty($filter['find']) && !$filter['model'])
		$cond .= " AND (!`compat_id` OR `compat_id`=`c`.`id`)";
	if(!empty($filter['find'])) {
		$cond .= " AND `find` LIKE '%".$filter['find']."%'";
		$reg = '/('.$filter['find'].')/i';
	}
	switch($filter['menu']) {
		case '1':
			$sql = "SELECT `zp_id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
			$ids = query_ids($sql);
			$cond .= " AND `c`.`id` IN (".$ids.")";
			break;
		case '2':
			$sql = "SELECT `zp_id` FROM `zp_avai` WHERE `ws_id`=".WS_ID;
			$ids = query_ids($sql);
			$cond .= " AND `c`.`id` NOT IN (".$ids.")";
			break;
		case '3':
			$sql = "SELECT `zp_id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." GROUP BY `zp_id`";
			$ids = query_ids($sql);
			$cond .= " AND `c`.`id` IN (".$ids.")";
			break;
		case '4':
			return zp_price($v);
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
	$sort = "`s`.`name`,`d`.`sort`,`v`.`sort`,`c`.`find`";
	if($filter['sort'])
		$sort = "`c`.`id` DESC";

	$all = query_value("SELECT COUNT(`c`.`id`) FROM `zp_catalog` `c` WHERE ".$cond);
	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Запчастей не найдено',
			'spisok' => '<div class="_empty">Запчастей не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, 'а ', 'о ').$all.' запчаст'._end($all, 'ь', 'и', 'ей').
					($filter['menu'] == 3 ? '<a id="xls-zakaz" href="'.APP_HTML.'/view/xls_zakaz.php?'.VALUES.'">Экспорт в xsl</a>' : ''),
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
	            `c`.*,
	            0 AS `avai`,
	            0 AS `zakaz`,
	            '' AS `zz`,
				`p`.`cena`
			FROM `zp_catalog` `c`

				LEFT JOIN `setup_zp_name` `s`
				ON `c`.`name_id`=`s`.`id`

				LEFT JOIN `base_device` `d`
				ON `c`.`base_device_id`=`d`.`id`

				LEFT JOIN `base_vendor` `v`
				ON `c`.`base_vendor_id`=`v`.`id`

				LEFT JOIN `zp_price` `p`
				ON `c`.`price_id`=`p`.`id`

			WHERE ".$cond."
			ORDER BY ".$sort."
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

	// количество по наличию
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

	// количество по заказу
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

	$send['spisok'] =
		$page == 1 ?
		'<table class="_spisok">'.
			'<tr><th>Наименование'.
				'<th>Нал.'.
				'<th>Заказ'
		: '';
	foreach($spisok as $id => $r) {
		$send['spisok'] .= '<tr val="'.$id.'">'.
			'<td><a href="'.URL.'&p=zp&d=info&id='.$id.'" class="name">'.
					_zpName($r['name_id']).' '.
					_vendorName($r['base_vendor_id']).$r['model'].
				'</a>'.
				($r['version'] ? '<span class="version">'.$r['version'].'</span>' : '').
				($r['color_id'] ? '<u class="color">'._color($r['color_id']).'</u>' : '').
				($r['cena'] ? '<b class="cena">'.round($r['cena']).'</b>' : '').
			'<td class="zp-avai">'.($r['avai'] ? $r['avai'] : '').
			'<td class="zp-zakaz">'.
				'<tt>—</tt>'.
				'<span class="zcol'.($r['zakaz'] ? '' : ' no').'">'.$r['zakaz'].'</span>'.
				'<tt>+</tt>';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'">'.
				'<td colspan="4">'.
					'<span>Показать ещё '.$c.' запчаст'._end($c, 'ь', 'и', 'ей').'</span>';
	}

	$send['spisok'] .= $page == 1 ?  '</table>' : '';

	return $send;
}//zp_spisok()
function zp_price($v) {
	$filter = zpFilter($v);
	$page = $filter['page'];
	$limit = $filter['limit'];
	$cond = "`id`";

	if(!empty($filter['find'])) {
		$cond .= " AND `name` LIKE '%".$filter['find']."%'";
		$reg = '/('.$filter['find'].')/i';
	}

	$all = query_value("SELECT COUNT(`id`) FROM `zp_price` WHERE ".$cond);
	if(!$all)
		return array(
			'all' => 0,
			'result' => 'Запчастей не найдено',
			'spisok' => '<div class="_empty">Запчастей не найдено</div>',
			'filter' => $filter
		);

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, 'а ', 'о ').$all.' запчаст'._end($all, 'ь', 'и', 'ей'),
		'spisok' => '',
		'filter' => $filter
	);

	$start = ($page - 1) * $limit;
	$spisok = array();
	$sql = "SELECT
				*,
				0 AS `changed`
			FROM `zp_price`
			WHERE ".$cond."
			ORDER BY `name`
			LIMIT ".$start.",".$limit;
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		if(!empty($filter['find'])) {
			if(preg_match($reg, $r['name']))
				$r['name'] = preg_replace($reg, "<em>\\1</em>", $r['name'], 1);
		}
		$spisok[$r['id']] = $r;
	}

	$sql = "SELECT *
			FROM `zp_price_upd`
			WHERE `price_id` IN (".implode(',', array_keys($spisok)).")";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['price_id']]['changed'] = 1;

	$send['spisok'] =
		$page == 1 ?
		'<table class="_spisok">'.
			'<tr><th>Код'.
				'<th>Наименование.'.
				'<th>Цена'.
				'<th>Заказ'
		: '';
	foreach($spisok as $id => $r) {
		$send['spisok'] .= '<tr val="'.$id.'">'.
			'<td class="articul">'.($r['avai'] ? $r['articul'] : '<s>'.$r['articul'].'</s>').
			'<td class="name">'.
				($r['changed'] ? '<a class="price-info" val="'.$id.'">'.$r['name'].'</a>' : '<div class="nam">'.$r['name'].'</div>').
			'<td class="price-cena">'.round($r['cena']).
			'<td class="zp-zakaz">';
			//	'<tt>—</tt>'.
			//	'<span class="zcol'.($r['zakaz'] ? '' : ' no').'">'.$r['zakaz'].'</span>'.
			//	'<tt>+</tt>';
	}

	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$send['spisok'] .=
			'<tr class="_next" val="'.($page + 1).'">'.
				'<td colspan="4">'.
					'<span>Показать ещё '.$c.' запчаст'._end($c, 'ь', 'и', 'ей').'</span>';
	}

	$send['spisok'] .= $page == 1 ?  '</table>' : '';

	return $send;
}//zp_price()

function zp_list($v) {
	$data = zp_spisok($v);
	$filter = $data['filter'];
	return
		'<div id="zp">'.
			'<div id="zp-head">'.
				'<table id="head-t"><tr>'.
					'<td id="td-find"><div id="find"></div>'.
					'<td><input type="hidden" id="zp_menu" value="'.$filter['menu'].'" />'.
					'<td><div class="vkButton"><button>Внести новую запчасть</button></div>'.
				'</table>'.
				'<table  id="zp-filter"><tr>'.
					'<td id="td-dev"><div id="dev"></div>'.
					'<td id="td-name"><input type="hidden" id="zp_name" value="'.$filter['name'].'" />'.
						'<a class="clear">Очистить фильтр</a>'.
				'</table>'.
			'</div>'.
			'<div id="sort">Порядок: <input type="hidden" id="zp_sort" value="'.$filter['sort'].'" /></div>'.
			'<div class="result">'.$data['result'].'</div>'.
			'<div id="zp-spisok">'.$data['spisok'].'</div>'.
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
		return 'Запчасти не существует';

	$compat_id = $zp['compat_id'] ? $zp['compat_id'] : $zp_id;
	if($zp_id != $compat_id) {
		$sql = "SELECT * FROM `zp_catalog` WHERE `id`=".$compat_id;
		$compat = mysql_fetch_assoc(query($sql));
		$zp['color_id'] = $compat['color_id'];
	}

	$avai = query_value("SELECT `count` FROM `zp_avai` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);

	$zakazCount = query_value("SELECT IFNULL(SUM(`count`),0) FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$compat_id);
	$zakazEdit = '<span class="zzedit">ано: <tt>—</tt><b>'.$zakazCount.'</b><tt>+</tt></span>';

	$compatSpisok = zp_compat_spisok($zp_id, $compat_id);
	$compatCount = count($compatSpisok);

	$price = $zp['price_id'] ? query_assoc("SELECT * FROM `zp_price` WHERE `id`=".$zp['price_id']) : array();

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
			'name:"'._zpName($zp['name_id']).' <b>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</b>",'.
			'for:"для '._deviceName($zp['base_device_id'], 1).'",'.
			'count:'.($avai ? $avai : 0).','.
			'images:"'.addslashes(_imageAdd(array('owner'=>'zp'.$compat_id))).'",'.
			'price_id:'.$zp['price_id'].
		'};'.
	'</script>'.
	'<div id="zpInfo">'.
		'<table class="ztab">'.
			'<tr><td class="left">'.
					'<div class="name">'.
						_zpName($zp['name_id']).
						'<em>'.$zp['version'].'</em>'.
					'</div>'.
					'<div class="for">'.
						'для '._deviceName($zp['base_device_id'], 1).
						' <a>'._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).'</a>'.
					'</div>'.
					'<table class="prop">'.
						($zp['color_id'] ? '<tr><td class="label">Цвет:<td>'._color($zp['color_id']) : '').
						($zp['price_id'] ?
							'<tr><td class="label top">Прайс:'.
								'<td><div class="price-name">'.
										'<u>'.$price['articul'].'</u>: '.
										$price['name'].
										' - <b>'.round($price['cena']).'</b>'.
									'</div>'
						: '').
						//'<tr><td class="label">id:<td>'.$zp['id'].
						//'<tr><td class="label">compat_id:<td>'.$zp['compat_id'].
					'</table>'.
					'<div class="avai'.($avai ? '' : ' no').'">'.($avai ? 'В наличии '.$avai.' шт.' : 'Нет в наличии.').'</div>'.
					'<div class="added">Добавлено в каталог '.FullData($zp['dtime_add'], 1).'</div>'.
					'<div class="headBlue">Движение</div>'.
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
						'<a class="edit">Редактировать</a>'.
						'<a class="avai_add">Внести наличие</a>'.
						'<a class="zpzakaz unit'.($zakazCount ? '' : ' hid').'" val="'.$zp_id.'">'.
							'Заказ<span class="cnt">'.($zakazCount ? 'ано: <b>'.$zakazCount.'</b>' : 'ать').'</span>'.
							$zakazEdit.
						'</a>'.
						'<a class="set"> - установка</a>'.
						'<a class="sale"> - продажа</a>'.
						'<a class="defect"> - брак</a>'.
						'<a class="return"> - возврат</a>'.
						'<a class="writeoff"> - списание</a>'.
					'</div>'.
					'<div class="headBlue">Совместимость<a class="add compat_add">добавить</a></div>'.
					'<div class="compatCount">'.zp_compat_count($compatCount).'</div>'.
					'<div class="compatSpisok">'.($compatCount ? implode($compatSpisok) : '').'</div>'.
		'</table>'.
	'</div>';
}//zp_info()
function zp_move($zp_id, $page=1) {
	$all = query_value("SELECT COUNT(`id`) FROM `zp_move` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
	if(!$all)
		return '<div class="unit">Движения запчасти нет.</div>';

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
	$spisok = _zayavValToList($spisok);
	$spisok = _clientValToList($spisok);
	$move = '';
	$type = array(
		'' => 'Приход',
		'set' => 'Установка',
		'sale' => 'Продажа',
		'defect' => 'Брак',
		'return' => 'Возврат',
		'writeoff' => 'Списание'
	);
	$n = 0;
	foreach($spisok as $r) {
		$cena = round($r['cena'], 2);
		$summa = round($r['summa'], 2);
		$count = abs($r['count']);
		$move .= '<div class="unit">'.
				(!$n++ && $page == 1 ? '<div val="'.$r['id'].'" class="img_del'._tooltip('Удалить запись', -50).'</div>' : '').
				$type[$r['type']].' <b>'.$count.'</b> шт. '.
				($summa ? 'на сумму '.$summa.' руб.'.($count > 1 ? ' <span class="cenaed">('.$cena.' руб./шт.)</span> ' : '') : '').
				($r['zayav_id'] ? 'по заявке '.$r['zayav_link'].'.' : '').
				($r['client_id'] ? 'клиенту '.$r['client_link'].'.' : '').
			($r['prim'] ? '<div class="prim">'.$r['prim'].'</div>' : '').
			'<div class="dtime'._tooltip(_viewerAdded($r['viewer_id_add']), -1, 'l').FullDataTime($r['dtime_add']).'</div>'.
		'</div>';
	}
	if($start + $limit < $all) {
		$c = $all - $start - $limit;
		$c = $c > $limit ? $limit : $c;
		$move .= '<div class="_next" val="'.($page + 1).'"><span>Показать ещё '.$c.' запис'._end($c, 'ь', 'и', 'ей').'</span></div>';
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
			'<div class="img_del" val="'.$r['id'].'" title="Разорвать совместимость"></div>'.
			_vendorName($r['base_vendor_id'])._modelName($r['base_model_id']).
		'</a>';
	}
	ksort($send);
	return $send;
}//zp_compat_spisok()
function zp_compat_count($c) {
	return $c ? $c.' устройств'._end($c, 'о', 'а', '') : 'Совместимостей нет';
}
