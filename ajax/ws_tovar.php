<?php
switch(@$_POST['op']) {
	case 'tovar_spisok':
		$filter = tovarFilter($_POST);
		$data = tovar_spisok($filter);
		if($filter['page'] == 1) {
			$send['result'] = utf8($data['result']);
		}
		$send['html'] = utf8($data['spisok']);
		jsonSuccess($send);
		break;
	case 'tovar_avai_add':
		if(!$id = _num($_POST['id']))
			jsonError();
		if(!$count = _num($_POST['count']))
			jsonError();
		$cena = _cena($_POST['cena']);
		$color_id = _num($_POST['color_id']);
		$color_dop = _num($_POST['color_dop']);
		$bu = _bool($_POST['bu']);

		$sql = "SELECT * FROM `base_model` WHERE `id`=".$id;
		if(!$r = query_assoc($sql))
			jsonError();

		$sql = "INSERT INTO `tovar_move` (
					`ws_id`,
					`model_id`,
					`count`,
					`cena`,
					`summa`,
					`color_id`,
					`color_dop`,
					`bu`,
					`viewer_id_add`
				) VALUES (
					".WS_ID.",
					".$id.",
					".$count.",
					".$cena.",
					".round($count * $cena, 2).",
					".$color_id.",
					".$color_dop.",
					".$bu.",
					".VIEWER_ID."
				)";
		query($sql);


		tovar_avai_update($id);


		jsonSuccess();
		break;
	case 'tovar_avai_show':
		if(!$tovar_id = _num($_POST['tovar_id']))
			jsonError();

		$sql = "SELECT * FROM `base_model` WHERE `id`=".$tovar_id;
		if(!$r = query_assoc($sql))
			jsonError();

		$html = '<div id="tovar-avai-show">';
		$sql = "SELECT *
				FROM `tovar_avai`
				WHERE `ws_id`=".WS_ID."
				  AND `model_id`=".$tovar_id;
		$q = query($sql);

		$html .=
			'<div>'.
				_deviceName($r['device_id']).
				'<b>'._vendorName($r['vendor_id']).$r['name'].'</b>'.
			'</div>';

		if(!mysql_num_rows($q))
			$html .= 'Наличия нет.';
		else {
			$dev = _deviceName($r['device_id'])._vendorName($r['vendor_id']).$r['name'];
			$html .=
				'<table class="_spisok">'.
					'<tr><th>'.
						'<th>Наименование'.
						'<th>Цена'.
						'<th>Кол-во';
			$n = 1;
			while($r = mysql_fetch_assoc($q))
				$html .=
					'<tr><td>'.($n++).
						'<td class="name">'.
							$dev.
							($r['color_id'] ? '<span class="color">'._color($r['color_id'], $r['color_dop']).'</span>' : '').
							($r['bu'] ? '<b class="bu">б/у</b>' : '').
						'<td>'.($r['sell'] > 0 ? $r['sell'] : '<a>указать</a>').
						'<td class="count">'.$r['count'];
			$html .= '</table>';
		}
		$html .= '</div>';

		$send['html'] = utf8($html);
		jsonSuccess($send);
		break;

	case 'tovar_add':
		if(!$vendor_id = _num($_POST['vendor_id']))
			jsonError();

		$name = _txt($_POST['name']);
		if(empty($name))
			jsonError();

		$device_id = query_value("SELECT `device_id` FROM `base_vendor` WHERE `id`=".$vendor_id);
		if(!$device_id)
			jsonError();

		$sql = "SELECT COUNT(*)
				FROM `base_model`
				WHERE `vendor_id`=".$vendor_id."
				  AND `name`='".$name."'";
		if(query_value($sql))
			jsonError();

		$sql = "INSERT INTO `base_model` (
					`device_id`,
					`vendor_id`,
					`name`,
					`viewer_id_add`
				) VALUES (
					".$device_id.",
					".$vendor_id.",
					'".addslashes($name)."',
					".VIEWER_ID."
				)";
		query($sql);
		xcache_unset(CACHE_PREFIX.'model_name');
		GvaluesCreate();
		jsonSuccess();
		break;

}