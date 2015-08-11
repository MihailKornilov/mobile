<?php
// ---===! tovar !===--- Секция товаров

/*
	type
		0: внесение наличия
		1: продажа
		2:
*/

function tovarFilter($v) {
	$default = array(
		'page' => 1,
		'limit' => 50,
		'device_id' => 0,
		'vendor_id' => 0,
		'avai' => 0

	);
	$filter = array(
		'page' => _num(@$v['page']) ? $v['page'] : $default['page'],
		'limit' => _num(@$v['limit']) ? $v['limit'] : $default['limit'],
		'device_id' => !empty($v['device_id']) ? $v['device_id'] : $default['device_id'],
		'vendor_id' => _num(@$v['vendor_id']) ? $v['vendor_id'] : $default['vendor_id'],
		'avai' => _bool(@$v['avai']) ? 1 : $default['avai'],
		'clear' => ''
	);
	foreach($default as $k => $r)
		if($r != $filter[$k]) {
			$filter['clear'] = '<a id="filter-clear">Очистить фильтр</a>';
			break;
		}
	return $filter;
}//tovarFilter()
function tovar($v) {
	$filter = tovarFilter($v);

	if(!$filter['device_id'] && !$filter['vendor_id'] && !$filter['avai'])
		return tovar_catalog($filter);

	$data = tovar_spisok($filter);

	return
	'<div id="tovar">'.
		'<div class="result">'.$data['result'].'</div>'.
		'<table class="tabLR">'.
			'<tr><td class="left">'.$data['spisok'].
				'<td class="right">'.
					'<div id="buttonCreate">'.
						'<a id="tovar-add">Внести новый товар<br />в каталог</a>'.
					'</div>'.
					'<div id="find"></div>'.
					'<div class="findHead">Устройство</div><div id="dev"></div>'.
					_check('avai', 'Наличие', $filter['avai']).
		'</table>'.
	'</div>'.
	'<script type="text/javascript">'.
		'var T={'.
			'device_id:"'.$filter['device_id'].'",'.
			'vendor_id:'.$filter['vendor_id'].
		'};'.
	'</script>';
}//tovar()
function tovar_spisok($filter) {
	$cond = "`id`";

	if($filter['device_id'])
		$cond .= " AND `device_id` IN (".$filter['device_id'].")";
	if($filter['vendor_id'])
		$cond .= " AND `vendor_id`=".$filter['vendor_id'];
	if($filter['avai']) {
		$sql = "SELECT `model_id` FROM `tovar_avai` WHERE `ws_id`=".WS_ID;
		$ids = query_ids($sql);
		$cond .= " AND `id` IN (".$ids.")";
	}

	$sql = "SELECT COUNT(*) FROM `base_model` WHERE ".$cond;
	if(!$all = query_value($sql))
		return array(
			'all' => 0,
			'result' => 'Товаров не найдено'.$filter['clear'],
			'spisok' => '<div class="_empty">Товаров не найдено</div>',
			'filter' => $filter
		);

	$filter['all'] = $all;

	$send = array(
		'all' => $all,
		'result' => 'Показан'._end($all, '', 'о').' '.$all.' товар'._end($all, '', 'а', 'ов').$filter['clear'],
		'spisok' => '',
		'filter' => $filter
	);

	$sql = "SELECT
				*,
				0 `avai`
			FROM `base_model`
			WHERE ".$cond."
			ORDER BY `id` DESC
			LIMIT "._start($filter).",".$filter['limit'];
	$q = query($sql);
	$tovar = array();
	$images = array();
	while($r = mysql_fetch_assoc($q)) {
		$tovar[$r['id']] = $r;
		$images[] = 'dev'.$r['id'];
	}

	$images = _imageGet(array(
		'owner' => $images,
		'view' => 1
	));


	//наличие товара
	$sql = "SELECT
				`model_id`,
				SUM(`count`) `count`
			FROM `tovar_avai`
			WHERE `model_id` IN (".implode(',', array_keys($tovar)).")
			GROUP BY `model_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$tovar[$r['model_id']]['avai'] = $r['count'];

	foreach($tovar as $r) {
		$send['spisok'] .=
			'<div class="tovar-unit">'.
				'<table class="tb">'.
					'<tr><td class="img">'.
							$images['dev'.$r['id']]['img'].
						'<td class="cont">'.

							($r['avai'] ? '<div val="'.$r['id'].'" class="avai'._tooltip('Наличие', -24).$r['avai'].'</div>' : '').
							'<a class="avai_add" val="'.$r['id'].'">Внести наличие</a>'.

							'<div>'._deviceName($r['device_id']).'</div>'.
							'<b>'._vendorName($r['vendor_id']).$r['name'].'</b>'.
							'<input type="hidden" id="u'.$r['id'].'" value="'._deviceName($r['device_id']).'<b>'._vendorName($r['vendor_id']).$r['name'].'</b>" />'.

				'</table>'.
			'</div>';
	}

	$send['spisok'] .=
		_next(array(
				//	'tr' => 1,
				//	'type' => 4,
				'id' => 'tovar_next'
			) + $filter);

	return $send;
}//tovar_spisok()

function tovar_catalog($filter) {
	$data = tovar_catalog_spisok($filter);
	return
	'<div id="tovar-catalog">'.
		'<div id="filter">'.
			'<table id="filter-tab">'.
				'<tr><td><div id="find"></div>'.
					'<td id="td-button-add">'._button('tovar-add', 'Внести новый товар в каталог').
			'</table>'.
		'</div>'.
		'<div class="result">4002 товара в каталоге</div>'.
		'<div id="spisok">'.$data['spisok'].'</div>'.
	'</div>';
}//tovar_catalog()
function tovar_catalog_spisok($filter) {
	$spisok = array();
	$sql = "SELECT
				*,
				'' `dev`
			FROM `tovar_category`
			ORDER BY `sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$spisok[$r['id']] = $r;
	}

	//наличие устройств с учётом характеристик
	$avai = array();
	$sql ="SELECT
				`m`.`device_id`,
				COUNT(`m`.`device_id`) `count`
			FROM
				`tovar_avai` `ta`,
				`base_model` `m`
			WHERE `m`.`id`=`ta`.`model_id`
			  AND `ta`.`ws_id`=".WS_ID."
			GROUP BY `m`.`device_id`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$avai[$r['device_id']] = $r['count'];

	//список устройств для каждой категории товаров
	$sql = "SELECT *
			FROM `base_device`
			WHERE `category_id`
			ORDER BY `sort`";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q))
		$spisok[$r['category_id']]['dev'] .=
			'<li><a class="dev" val="'.$r['id'].'">'.
					$r['name_mn'].
					(isset($avai[$r['id']]) ? ' (<b>'.$avai[$r['id']].'</b>)' : '').
				'</a>';


	$send['spisok'] = '<table id="tab">';
	$td = 1;
	foreach($spisok as $r) {
		if($td == 4) {
			$td = 1;
			$send['spisok'] .= '<tr>';
		}
		$send['spisok'] .=
			'<td class="td">'.
				'<div class="head">'.$r['name'].'</div>'.
				'<ul>'.$r['dev'].'</ul>';
		$td++;
	}

	$send['spisok'] .= '</table>';

	return $send;
}//tovar_catalog_spisok()

function tovar_avai_update($id) {//обновление наличия товара после внесения движения
	$avai = array();
	$sql = "SELECT * FROM `tovar_move` WHERE `model_id`=".$id." ORDER BY `id` DESC";
	$q = query($sql);
	while($r = mysql_fetch_assoc($q)) {
		$continue = 0;
		foreach($avai as $k => $i) {
			if( $i['color_id'] == $r['color_id']
			 && $i['color_dop'] == $r['color_dop']
			 && $i['bu'] == $r['bu']) {
				$avai[$k]['count'] += $r['count'];
				$continue = 1;
				break;
			}
		}
		if($continue)
			continue;
		$avai[] = array(
			'color_id' => $r['color_id'],
			'color_dop' => $r['color_dop'],
			'bu' => $r['bu'],
			'cena' => $r['cena'],
			'count' => $r['type'] ? $r['count'] * -1 : $r['count']
		);
	}

	query("DELETE FROM `tovar_avai` WHERE `model_id`=".$id);

	$values = array();
	foreach($avai as $k => $i)
		$values[] = "(
			".WS_ID.",
			".$id.",
			".$i['color_id'].",
			".$i['color_dop'].",
			".$i['bu'].",
			".$i['cena'].",
			".$i['count']."
		)";

	$sql = "INSERT INTO `tovar_avai` (
				`ws_id`,
				`model_id`,
				`color_id`,
				`color_dop`,
				`bu`,
				`cena`,
				`count`
			) VALUES ".implode(',', $values);
	query($sql);
}//tovar_avai_update()




