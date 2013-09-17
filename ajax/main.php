<?php
require_once('../include/conf.php');//todo для удаления
require_once('../config.php');
require_once(DOCUMENT_ROOT.'/view/main.php');

function jsonError($values=null) {
    $send['error'] = 1;
    if(empty($values))
        $send['text'] = utf8('Произошла неизвестная ошибка.<br />Попробуйте позднее.');
    elseif(is_array($values))
        $send += $values;
    else
        $send['text'] = $values;
    die(json_encode($send));
}//end of jsonError()
function jsonSuccess($send=array()) {
    $send['success'] = 1;
    die(json_encode($send));
}//end of jsonSuccess()

switch(@$_POST['op']) {
    case 'script_style':
        if(!ADMIN)
            jsonError();
        query("UPDATE `setup_global` SET `script_style`=`script_style`+1");
        xcache_unset('vkmobile_setup_global');
        jsonSuccess();
        break;
    case 'cache_clear':
        xcache_unset('vkmobile_setup_global');
        xcache_unset('vkmobile_viewer_'.VIEWER_ID);
        xcache_unset('vkmobile_workshop_'.WS_ID);
        xcache_unset('vkmobile_remind_active');
        xcache_unset('vkmobile_device_name');
        xcache_unset('vkmobile_vendor_name');
        xcache_unset('vkmobile_model_name_count');
        xcache_unset('vkmobile_zp_name');
        xcache_unset('vkmobile_color_name');
        xcache_unset('vkmobile_device_place');
        xcache_unset('vkmobile_device_status');
        xcache_unset('vkmobile_zayav_base_device'.WS_ID);
        xcache_unset('vkmobile_zayav_base_vendor'.WS_ID);
        xcache_unset('vkmobile_zayav_base_model'.WS_ID);
        jsonSuccess();
        break;

    case 'vkcomment_add':
        $table = htmlspecialchars(trim($_POST['table']));
        if(strlen($table) > 20)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        if(empty($_POST['txt']))
            jsonError();
        $txt = win1251(htmlspecialchars(trim($_POST['txt'])));
        $sql = "INSERT INTO `vk_comment` (
                    `table_name`,
                    `table_id`,
                    `txt`,
                    `viewer_id_add`
                ) VALUES (
                    '".$table."',
                    ".intval($_POST['id']).",
                    '".addslashes($txt)."',
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(_vkCommentUnit(mysql_insert_id(), _viewersInfo(), $txt, curTime()));
        jsonSuccess($send);
        break;
    case 'vkcomment_add_child':
        $table = htmlspecialchars(trim($_POST['table']));
        if(strlen($table) > 20)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['parent']))
            jsonError();
        if(empty($_POST['txt']))
            jsonError();
        $txt = win1251(htmlspecialchars(trim($_POST['txt'])));
        $sql = "INSERT INTO `vk_comment` (
                    `table_name`,
                    `table_id`,
                    `txt`,
                    `parent_id`,
                    `viewer_id_add`
                ) VALUES (
                    '".$table."',
                    ".intval($_POST['id']).",
                    '".addslashes($txt)."',
                    ".intval($_POST['parent']).",
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(_vkCommentChild(mysql_insert_id(), _viewersInfo(), $txt, curTime()));
        jsonSuccess($send);
        break;
    case 'vkcomment_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        if(!ADMIN) {
            $sql = "SELECT `viewer_id_add` FROM `vk_comment` WHERE `status`=1 AND `id`=".$id;
            if(!$r = mysql_fetch_assoc(query($sql)))
                jsonError();
            if($r['viewer_id_add'] != VIEWER_ID)
                jsonError();
        }

        $childs = array();

        $sql = "SELECT `id` FROM `vk_comment` WHERE `status`=1 AND `parent_id`=".$id;
        $q = query($sql);
        if(mysql_num_rows($q)) {
            while($r = mysql_fetch_assoc($q))
                $childs[] = $r['id'];
            $sql = "UPDATE `vk_comment` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP
               WHERE `parent_id`=".$id;
            query($sql);
        }

        $sql = "UPDATE `vk_comment` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP,
                    `child_del`=".(!empty($childs) ? "'".implode(',', $childs)."'" : 'NULL')."
               WHERE `id`=".$id;
        query($sql);
        jsonSuccess();
        break;
    case 'vkcomment_rest':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);

        $sql = "SELECT `child_del` FROM `vk_comment` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        if($r['child_del']) {
            $sql = "UPDATE `vk_comment` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00'
               WHERE `id` IN (".$r['child_del'].")";
            query($sql);
        }

        $sql = "UPDATE `vk_comment` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00',
                    `child_del`=NULL
               WHERE `id`=".$id;
        query($sql);
        jsonSuccess();
        break;

    case 'foto_load':
        if(!preg_match(REGEXP_WORD, $_POST['owner']))
            jsonError();
        $sql = "SELECT *
                FROM `images`
                WHERE `owner`='".$_POST['owner']."'
                  AND `status`=1
                ORDER BY `sort`";
        $q = query($sql);
        $send = array();
        while($r = mysql_fetch_assoc($q))
            $send['img'][] = array(
                'link' => $r['link'].'-big.jpg',
                'x' => $r['big_x'],
                'y' => $r['big_y'],
                'dtime' => utf8(FullData($r['dtime_add'], 1))
            );
        jsonSuccess($send);
        break;

    case 'client_sel':
        if(!preg_match(REGEXP_WORDFIND, win1251($_POST['val'])))
            $_POST['val'] = '';
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
            $_POST['client_id'] = 0;
        $val = win1251($_POST['val']);
        $client_id = intval($_POST['client_id']);
        $sql = "SELECT *
                FROM `client`
                WHERE `ws_id`=".WS_ID.
                    (!empty($val) ? " AND (`fio` LIKE '%".$val."%' OR `telefon` LIKE '%".$val."%')" : '').
                    ($client_id > 0 ? " AND `id`<=".$client_id : '')."
                ORDER BY `id` DESC
                LIMIT 50";
        $q = query($sql);
        $send['spisok'] = array();
        while($r = mysql_fetch_assoc($q)) {
            $unit = array(
                'uid' => $r['id'],
                'title' => utf8($r['fio'])
            );
            if($r['telefon'])
                $unit['content'] = utf8($r['fio'].'<div class="pole2">'.$r['telefon'].'</div>');
            $send['spisok'][] = $unit;
        }
        jsonSuccess($send);
        break;
    case 'client_add':
        $fio = win1251(htmlspecialchars(trim($_POST['fio'])));
        $telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
        if(empty($fio))
            jsonError();
        $sql = "INSERT INTO `client` (
                    `ws_id`,
                    `fio`,
                    `telefon`,
                    `viewer_id_add`
                ) VALUES (
                    ".WS_ID.",
                    '".addslashes($fio)."',
                    '".addslashes($telefon)."',
                    ".VIEWER_ID."
                )";
        query($sql);
        $send = array(
            'uid' => mysql_insert_id(),
            'title' => utf8($fio)
        );
        history_insert(array(
            'type' => 3,
            'client_id' => $send['uid']
        ));
        jsonSuccess($send);
        break;
    case 'client_spisok_load':
        $filter = clientFilter($_POST);
        $send = client_data(1, $filter);
        $send['all'] = utf8(client_count($send['all'], $filter['dolg']));
        $send['spisok'] = utf8($send['spisok']);
        jsonSuccess($send);
        break;
    case 'client_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send = client_data(intval($_POST['page']), clientFilter($_POST));
        $send['spisok'] = utf8($send['spisok']);
        jsonSuccess($send);
        break;
    case 'client_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) || $_POST['client_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['join']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['client2']))
            jsonError();
        $client_id = intval($_POST['client_id']);
        $fio = win1251(htmlspecialchars(trim($_POST['fio'])));
        $telefon = win1251(htmlspecialchars(trim($_POST['telefon'])));
        $join = intval($_POST['join']);
        $client2 = intval($_POST['client2']);
        if(empty($fio))
            jsonError();
        if($join && $client2 == 0)
            jsonError();
        if($join && $client_id == $client2)
            jsonError();
        query("UPDATE `client` SET `fio`='".$fio."',`telefon`='".$telefon."' WHERE `id`=".$client_id);
        if($join) {
            query("UPDATE `accrual`    SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
            query("UPDATE `money`      SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
            query("UPDATE `vk_comment` SET `table_id`=".$client_id."  WHERE `table_name`='client' AND `table_id`=".$client2);
            query("UPDATE `zayavki`    SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
            query("UPDATE `zp_move`    SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
            query("UPDATE `zp_zakaz`   SET `client_id`=".$client_id." WHERE `client_id`=".$client2);
            query("DELETE FROM `client` WHERE `id`=".$client2);
            setClientBalans($client_id);
        }
        history_insert(array(
            'type' => $join ? 11 : 10,
            'client_id' => $client_id
        ));
        jsonSuccess();
        break;
    case 'client_zayav_load':
        $data = zayav_data(1, zayavfilter($_POST), 10);
        $send['all'] = utf8(zayav_count($data['all'], 0));
        $send['html'] = utf8(zayav_spisok($data));
        jsonSuccess($send);
        break;
    case 'client_zayav_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send['html'] = utf8(zayav_spisok(zayav_data(intval($_POST['page']), zayavfilter($_POST), 10)));
        jsonSuccess($send);
        break;

    case 'zayav_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['client']) || $_POST['client'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['device']) || $_POST['device'] == 0)
            jsonError();
        $client = intval($_POST['client']);
        $device = intval($_POST['device']);
        $vendor = intval($_POST['vendor']);
        $model = intval($_POST['model']);
        $place = intval($_POST['place']);
        $place_other = $place == 0 ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';
        $imei = win1251(htmlspecialchars(trim($_POST['imei'])));
        $serial = win1251(htmlspecialchars(trim($_POST['serial'])));
        $color = intval($_POST['color']);
        $comm = win1251(htmlspecialchars(trim($_POST['comm'])));
        $reminder = intval($_POST['reminder']);
        $reminder_txt = win1251(htmlspecialchars(trim($_POST['reminder_txt'])));
        $reminder_day = htmlspecialchars(trim($_POST['reminder_day']));
        if($reminder) {
            if(!$reminder_txt)
                jsonError();
            if(!preg_match(REGEXP_DATE, $reminder_day))
                jsonError();
        }
        $modelName = '';
        if($model > 0) {
            $sql = "select `name` FROM `base_model` WHERE `id`=".$model;
            $r = mysql_fetch_assoc(query($sql));
            $modelName = $r['name'];
        }
        $sql = "SELECT IFNULL(MAX(`nomer`),0)+1 AS `nomer` FROM `zayavki` WHERE `ws_id`=".WS_ID." LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $nomer = $r['nomer'];

        $sql = "INSERT INTO `zayavki` (
                    `ws_id`,
                    `nomer`,
                    `client_id`,

                    `base_device_id`,
                    `base_vendor_id`,
                    `base_model_id`,

                    `imei`,
                    `serial`,
                    `color_id`,

                    `zayav_status`,
                    `zayav_status_dtime`,

                    `device_status`,
                    `device_place`,
                    `device_place_other`,

                    `viewer_id_add`,
                    `find`
                ) VALUES (
                    ".WS_ID.",
                    ".$nomer.",
                    ".$client.",

                    ".$device.",
                    ".$vendor.",
                    ".$model.",

                    '".$imei."',
                    '".$serial."',
                    ".$color.",

                    1,
                    current_timestamp,

                    1,
                    ".$place.",
                    '".$place_other."',

                    ".VIEWER_ID.",
                    '".$modelName." ".$imei." ".$serial."'
                )";
        query($sql);
        $send['id'] = mysql_insert_id();
        xcache_unset('vkmobile_zayav_base_device'.WS_ID);
        xcache_unset('vkmobile_zayav_base_vendor'.WS_ID);
        xcache_unset('vkmobile_zayav_base_model'.WS_ID);

        if($comm) {
            $sql = "INSERT INTO `vk_comment` (
                        `table_name`,
                        `table_id`,
                        `txt`,
                        `viewer_id_add`
                    ) VALUES (
                        'zayav',
                        ".$send['id'].",
                        '".$comm."',
                        ".VIEWER_ID."
                    )";
            query($sql);
        }

        if($reminder) {
            $sql = "INSERT INTO `reminder` (
                `ws_id`,
                `zayav_id`,
                `txt`,
                `day`,
                `history`,
                `viewer_id_add`
             ) VALUES (
                ".WS_ID.",
                ".$send['id'].",
                '".$reminder_txt."',
                '".$reminder_day."',
                '".FullDataTime(curTime())." "._viewerName()." добавил напоминание для заявки.',
                ".VIEWER_ID."
            )";
            query($sql);
        }
        history_insert(array(
            'type' => 1,
            'client_id' => $client,
            'zayav_id' => $send['id']
        ));
        jsonSuccess($send);
        break;
    case 'model_img_get':
        if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']))
            jsonError();
        $send['img'] = _modelImg(intval($_POST['model_id']), 'small', 100, 100, 'fotoView');
        jsonSuccess($send);
        break;
    case 'zayav_spisok_load':
        $_POST['find'] = win1251($_POST['find']);
        $data = zayav_data(1, zayavfilter($_POST));
        $send['all'] = utf8(zayav_count($data['all']));
        $send['html'] = utf8(zayav_spisok($data));
        jsonSuccess($send);
        break;
    case 'zayav_next':
        $_POST['find'] = win1251($_POST['find']);
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send['html'] = utf8(zayav_spisok(zayav_data(intval($_POST['page']), zayavfilter($_POST))));
        jsonSuccess($send);
        break;
    case 'zayav_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']) && $_POST['client_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['device']) && $_POST['device'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['vendor']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['model']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
            jsonError();
        $zayav_id = intval($_POST['zayav_id']);
        $client_id = intval($_POST['client_id']);
        $device = intval($_POST['device']);
        $vendor = intval($_POST['vendor']);
        $model = intval($_POST['model']);
        $imei = win1251(htmlspecialchars(trim($_POST['imei'])));
        $serial = win1251(htmlspecialchars(trim($_POST['serial'])));
        $color_id = intval($_POST['color_id']);

        $sql = "SELECT * FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id." LIMIT 1";
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "UPDATE `zayavki` SET
                    `client_id`=".$client_id.",
                    `base_device_id`=".$device.",
                    `base_vendor_id`=".$vendor.",
                    `base_model_id`=".$model.",
                    `imei`='".$imei."',
                    `serial`='".$serial."',
                    `color_id`=".$color_id.",
                    `find`='"._modelName($model)." ".$imei." ".$serial."'
                WHERE `id`=".$zayav_id;
        query($sql);

        xcache_unset('vkmobile_zayav_base_device'.WS_ID);
        xcache_unset('vkmobile_zayav_base_vendor'.WS_ID);
        xcache_unset('vkmobile_zayav_base_model'.WS_ID);

        if($zayav['client_id'] != $client_id) {
            $sql = "UPDATE `accrual`
                    SET `client_id`=".$client_id."
                    WHERE `ws_id`=".WS_ID."
                      AND `zayav_id`=".$zayav_id."
                      AND `client_id`=".$zayav['client_id'];
            query($sql);
            $sql = "UPDATE `money`
                    SET `client_id`=".$client_id."
                    WHERE `ws_id`=".WS_ID."
                      AND `zayav_id`=".$zayav_id."
                      AND `client_id`=".$zayav['client_id'];
            query($sql);
            setClientBalans($zayav['client_id']);
            setClientBalans($client_id);
        }

        history_insert(array(
            'type' => 7,
            'zayav_id' => $zayav_id
        ));

        jsonSuccess();
        break;
    case 'zayav_delete':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
            jsonError();
        $zayav_id = intval($_POST['zayav_id']);
        $sql = "SELECT * FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id." LIMIT 1";
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "SELECT IFNULL(SUM(`summa`),0) AS `acc`
                FROM `accrual`
                WHERE `ws_id`=".WS_ID."
                  AND `status`=1
                  AND `zayav_id`=".$zayav_id."
                LIMIT 1";
        if(query_value($sql) != 0)
            jsonError();

        $sql = "SELECT IFNULL(SUM(`summa`),0) AS `opl`
                FROM `money`
                WHERE `ws_id`=".WS_ID."
                  AND `status`=1
                  AND `summa`>0
                  AND `zayav_id`=".$zayav_id."
                LIMIT 1";
        if(query_value($sql) != 0)
            jsonError();

        $sql = "DELETE FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id;
        query($sql);

        $sql = "DELETE FROM `reminder` WHERE `ws_id`=".WS_ID." AND `zayav_id`=".$zayav_id;
        query($sql);

        $sql = "DELETE FROM `vk_comment` WHERE `table_name`='zayav' AND `table_id`=".$zayav_id;
        query($sql);

        xcache_unset('vkmobile_zayav_base_device'.WS_ID);
        xcache_unset('vkmobile_zayav_base_vendor'.WS_ID);
        xcache_unset('vkmobile_zayav_base_model'.WS_ID);

        history_insert(array(
            'type' => 2,
            'value' => $zayav['nomer']
        ));

        $send['client_id'] = $zayav['client_id'];
        jsonSuccess($send);
        break;
    case 'zayav_status_place':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) && $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_status']) && $_POST['zayav_status'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['dev_status']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['dev_place']))
            jsonError();
        $zayav_id = intval($_POST['zayav_id']);
        $zayav_status = intval($_POST['zayav_status']);
        $dev_status = intval($_POST['dev_status']);
        $dev_place = intval($_POST['dev_place']);
        $place_other = $dev_place == 0 ? win1251(htmlspecialchars(trim($_POST['place_other']))) : '';
        if($dev_place == 0 && !$place_other)
            jsonError();

        $sql = "SELECT * FROM `zayavki` WHERE `ws_id`=".WS_ID." AND `id`=".$zayav_id." LIMIT 1";
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "UPDATE `zayavki`
                SET `device_status`=".$dev_status.",
                    `device_place`=".$dev_place.",
                    `device_place_other`='".$place_other."'
                    ".($zayav['zayav_status'] != $zayav_status ? ",`zayav_status`=".$zayav_status.",`zayav_status_dtime`=CURRENT_TIMESTAMP" : '')."
                WHERE `id`=".$zayav_id;
        query($sql);

        $send['z_status'] = _zayavStatus($zayav_status);
        $send['z_status']['name'] = utf8($send['z_status']['name']);
        $send['z_status']['dtime'] = utf8(FullDataTime($zayav['zayav_status_dtime'], 1));
        $send['dev_place'] = utf8($dev_place > 0 ? _devPlace($dev_place) : $place_other);
        $send['dev_status'] = utf8(_devStatus($dev_status));

        if($zayav['zayav_status'] != $zayav_status) {
            history_insert(array(
                'type' => 4,
                'zayav_id' => $zayav_id,
                'value' => $zayav_status
            ));
            $send['z_status']['dtime'] = utf8(FullDataTime(curTime(), 1));
        }
        jsonSuccess($send);
        break;
    case 'zayav_img_update'://Обновление картинки заявки после загрузки новой
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
        jsonError();
        $send['html'] = _zayavImg(intval($_POST['zayav_id']), 'big', 200, 320, 'fotoView');
        jsonSuccess($send);
        break;
    case 'zayav_money_update':
        //Получение разницы между начислениями и платежами и их обновление
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "SELECT IFNULL(SUM(`summa`),0) AS `acc`
                FROM `accrual`
                WHERE `ws_id`=".WS_ID."
                  AND `status`=1
                  AND `zayav_id`=".$id;
        $send = mysql_fetch_assoc(query($sql));
        $sql = "SELECT IFNULL(SUM(`summa`),0) AS `opl`
                FROM `money`
                WHERE `ws_id`=".WS_ID."
                  AND `status`=1
                  AND `summa`>0
                  AND `zayav_id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        $send['opl'] = $r['opl'];
        $send['dopl'] = $send['acc'] - $r['opl'];
        jsonSuccess($send);
        break;
    case 'zayav_accrual_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['status']) || $_POST['status'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['dev_status']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['remind']))
            jsonError();
        $remind = intval($_POST['remind']);
        $remind_txt = win1251(htmlspecialchars(trim($_POST['remind_txt'])));
        $remind_day = htmlspecialchars(trim($_POST['remind_day']));
        if($remind) {
            if(!$remind_txt)
                jsonError();
            if(!preg_match(REGEXP_DATE, $remind_day))
                jsonError();
        }

        $zayav_id = intval($_POST['zayav_id']);
        $sum = intval($_POST['sum']);
        $prim = win1251(htmlspecialchars(trim($_POST['prim'])));
        $status = intval($_POST['status']);
        $dev_status = intval($_POST['dev_status']);

        $sql = "SELECT *
                FROM `zayavki`
                WHERE `ws_id`=".WS_ID."
                  AND `zayav_status`>0
                  AND `id`=".$zayav_id;
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "INSERT INTO `accrual` (
                    `ws_id`,
                    `zayav_id`,
                    `client_id`,
                    `summa`,
                    `prim`,
                    `viewer_id_add`
                ) VALUES (
                    ".WS_ID.",
                    ".$zayav_id.",
                    ".$zayav['client_id'].",
                    ".$sum.",
                    '".addslashes($prim)."',
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(zayav_accrual_unit(array(
            'id' => mysql_insert_id(),
            'summa' => $sum,
            'prim' => $prim,
        )));
        setClientBalans($zayav['client_id']);
        history_insert(array(
            'type' => 5,
            'zayav_id' => $zayav_id,
            'value' => $sum
        ));

        //Обновление статуса заявки, если изменялся
        $sql = "UPDATE `zayavki`
                SET `device_status`=".$dev_status."
                    ".($zayav['zayav_status'] != $status ? ",`zayav_status`=".$status.",`zayav_status_dtime`=CURRENT_TIMESTAMP" : "")."
                WHERE `ws_id`=".WS_ID."
                  AND `id`=".$zayav_id;
        query($sql);
        if($zayav['zayav_status'] != $status) {
            history_insert(array(
                'type' => 4,
                'zayav_id' => $zayav_id,
                'value' => $status
            ));
            $send['status'] = _zayavStatus($status);
            $send['status']['name'] = utf8($send['status']['name']);
            $send['status']['dtime'] = utf8(FullDataTime(curTime()));
        }

        //Внесение напоминания, если есть
        if($remind) {
            $sql = "INSERT INTO `reminder` (
                `ws_id`,
                `zayav_id`,
                `txt`,
                `day`,
                `history`,
                `viewer_id_add`
             ) VALUES (
                ".WS_ID.",
                ".$zayav_id.",
                '".$remind_txt."',
                '".$remind_day."',
                '".FullDataTime(curTime())." "._viewerName()." добавил напоминание при внесении начисления.',
                ".VIEWER_ID."
            )";
            query($sql);
            $send['remind'] = utf8(report_remind_spisok(remind_data(1, array('zayav'=>$zayav_id))));
        }
        jsonSuccess($send);
        break;
    case 'zayav_oplata_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']) || $_POST['sum'] == 0)
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['dev_place']))
            jsonError();
        $zayav_id = intval($_POST['zayav_id']);
        $sum = intval($_POST['sum']);
        $kassa = intval($_POST['kassa']);
        $dev_place = intval($_POST['dev_place']);
        $prim = win1251(htmlspecialchars(trim($_POST['prim'])));

        $sql = "SELECT *
                FROM `zayavki`
                WHERE `ws_id`=".WS_ID."
                  AND `zayav_status`>0
                  AND `id`=".$zayav_id;
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            jsonError();

        $sql = "INSERT INTO `money` (
                    `ws_id`,
                    `zayav_id`,
                    `client_id`,
                    `summa`,
                    `kassa`,
                    `prim`,
                    `viewer_id_add`
                ) VALUES (
                    ".WS_ID.",
                    ".$zayav_id.",
                    ".$zayav['client_id'].",
                    ".$sum.",
                    ".$kassa.",
                    '".addslashes($prim)."',
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(zayav_oplata_unit(array(
            'id' => mysql_insert_id(),
            'summa' => $sum,
            'prim' => $prim
        )));
        setClientBalans($zayav['client_id']);
        history_insert(array(
            'type' => 6,
            'zayav_id' => $zayav_id,
            'value' => $sum
        ));

        //Обновление местонахождения устройства
        $sql = "UPDATE `zayavki`
                SET `device_place`=".$dev_place.",
                    `device_place_other`=''
                WHERE `ws_id`=".WS_ID."
                  AND `id`=".$zayav_id;
        query($sql);
        jsonSuccess($send);
        break;
    case 'zayav_accrual_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "UPDATE `accrual` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP
                WHERE `id`=".$id;
        query($sql);
        $sql = "SELECT * FROM `accrual` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        history_insert(array(
            'type' => 8,
            'value' => $r['summa'],
            'value1' => $r['prim'],
            'zayav_id' => $r['zayav_id']
        ));
        jsonSuccess();
        break;
    case 'zayav_oplata_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "UPDATE `money` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP
                WHERE `id`=".$id;
        query($sql);
        $sql = "SELECT * FROM `money` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        history_insert(array(
            'type' => 9,
            'value' => $r['summa'],
            'value1' => $r['prim'],
            'zayav_id' => $r['zayav_id']
        ));
        jsonSuccess();
        break;
    case 'zayav_accrual_rest':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "SELECT *
                FROM `accrual`
                WHERE `ws_id`=".WS_ID."
                  AND `status`=0
                  AND `id`=".$id;
        if(!$acc = mysql_fetch_assoc(query($sql)))
            jsonError();
        $sql = "UPDATE `accrual` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00'
                WHERE `id`=".$id;
        query($sql);
        history_insert(array(
            'type' => 27,
            'value' => $acc['summa'],
            'value1' => $acc['prim'],
            'zayav_id' => $acc['zayav_id']
        ));
        $send['html'] = utf8(zayav_accrual_unit($acc));
        jsonSuccess($send);
        break;
    case 'zayav_oplata_rest':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "SELECT *
                FROM `money`
                WHERE `ws_id`=".WS_ID."
                  AND `status`=0
                  AND `id`=".$id;
        if(!$acc = mysql_fetch_assoc(query($sql)))
            jsonError();
        $sql = "UPDATE `money` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00'
                WHERE `id`=".$id;
        query($sql);
        history_insert(array(
            'type' => 19,
            'value' => $acc['summa'],
            'value1' => $acc['prim'],
            'zayav_id' => $acc['zayav_id']
        ));
        $send['html'] = utf8(zayav_oplata_unit($acc));
        jsonSuccess($send);
        break;
    case 'zayav_zp_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['name_id']) || $_POST['name_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['bu']))
            jsonError();
        $sql = "SELECT
                    `base_device_id` AS `device_id`,
                    `base_vendor_id` AS `vendor_id`,
                    `base_model_id` AS `model_id`
                FROM `zayavki`
                WHERE `ws_id`=".WS_ID."
                  AND `id`=".intval($_POST['zayav_id']);
        if(!$zp = mysql_fetch_assoc(query($sql)))
            jsonError();
        $zp['name_id'] = intval($_POST['name_id']);
        $zp['version'] = win1251(htmlspecialchars(trim($_POST['version'])));
        $zp['bu'] = intval($_POST['bu']);
        $zp['color_id'] = intval($_POST['color_id']);
        $zp['id'] = zpAddQuery($zp);
        $send['html'] = utf8(zayav_zp_unit($zp, _vendorName($zp['vendor_id'])._modelName($zp['model_id'])));
        jsonSuccess($send);
        break;
    case 'zayav_zp_zakaz':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
            jsonError();

        $sql = "SELECT * FROM `zp_catalog` WHERE `id`=".intval($_POST['zp_id']);
        $zp = mysql_fetch_assoc(query($sql));
        $compat_id = $zp['compat_id'] ? $zp['compat_id'] : $zp['id'];

        $sql = "INSERT INTO `zp_zakaz` (
                    `ws_id`,
                    `zp_id`,
                    `zayav_id`,
                    `viewer_id_add`
                ) VALUES (
                    ".WS_ID.",
                    ".$compat_id.",
                    ".intval($_POST['zayav_id']).",
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['msg'] = utf8('Запчасть <b>'._zpName($zp['name_id']).'</b> для '._vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']).' добавлена к заказу.');
        jsonSuccess($send);
        break;
    case 'zayav_zp_set':// Установка запчасти из заявки
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']) || $_POST['zayav_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
            jsonError();
        if(!isset($_POST['count']))
            $_POST['count'] = 1;
        if(!preg_match(REGEXP_NUMERIC, $_POST['count']) || $_POST['count'] == 0)
            jsonError();


        $zayav_id = intval($_POST['zayav_id']);
        $zp_id = _zpCompatId($_POST['zp_id']);
        $count = intval($_POST['count']) * -1;
        $prim = isset($_POST['prim']) ? win1251(htmlspecialchars(trim($_POST['prim']))) : '';

        $sql = "INSERT INTO `zp_move` (
                    `ws_id`,
                    `zp_id`,
                    `count`,
                    `type`,
                    `zayav_id`,
                    `prim`,
                    `viewer_id_add`
                ) VALUES (
                    ".WS_ID.",
                    ".$zp_id.",
                    ".$count.",
                    'set',
                    ".$zayav_id.",
                    '".$prim."',
                    ".VIEWER_ID."
                )";
        query($sql);

        $count = _zpAvaiSet($zp_id);

        //Удаление из заказа запчасти, привязанной к заявке
        query("DELETE FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zayav_id`=".$zayav_id." AND `zp_id`=".$zp_id);

        $parent_id = 0;
        $sql = "SELECT `id`,`parent_id`
                FROM `vk_comment`
                WHERE `table_name`='zayav'
                  AND `table_id`=".$zayav_id."
                  AND `status`=1
                ORDER BY `id` DESC
                LIMIT 1";
        if($r = mysql_fetch_assoc(query($sql)))
            $parent_id = $r['parent_id'] ? $r['parent_id'] : $r['id'];
        $sql = "SELECT * FROM `zp_catalog` WHERE id=".$zp_id." LIMIT 1";
        $zp = mysql_fetch_assoc(query($sql));
        $model = _vendorName($zp['base_vendor_id'])._modelName($zp['base_model_id']);
        $sql = "INSERT INTO `vk_comment` (
                    `table_name`,
                    `table_id`,
                    `txt`,
                    `parent_id`,
                    `viewer_id_add`
                ) VALUES (
                    'zayav',
                    ".$zayav_id.",
                    '".addslashes('Установка запчасти: <a href="'.URL.'&p=zp&d=info&id='.$zp_id.'">'._zpName($zp['name_id']).' '.$model.'</a>')."',
                    ".$parent_id.",
                    ".VIEWER_ID."
                )";
        query($sql);
        $zp['avai'] = $count;
        $send['zp_unit'] = utf8(zayav_zp_unit($zp, $model));
        $send['comment'] = utf8(_vkComment('zayav', $zayav_id));
        jsonSuccess($send);
        break;

    case 'zayav_nomer_info'://Получение данных о заявке по номеру
        if(!preg_match(REGEXP_NUMERIC, $_POST['nomer']) || $_POST['nomer'] == 0)
            jsonError();
        $nomer = intval($_POST['nomer']);
        $sql = "SELECT *
                FROM `zayavki`
                WHERE `ws_id`=".WS_ID."
                  AND `nomer`=".$nomer."
                  AND `zayav_status`>0
                LIMIT 1";
        if(!$zayav = mysql_fetch_assoc(query($sql)))
            $send['html'] = '<span class="zayavNomerTab">Заявка не найдена</span>';
        else
            $send['html'] = '<table class="zayavNomerTab">'.
                '<tr><td>'._zayavImg($zayav['id'], 'small', 60, 40, 'fotoView').
                    '<td><a href="'.URL.'&p=zayav&d=info&id='.$zayav['id'].'">'._deviceName($zayav['base_device_id']).'<br />'.
                           _vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).
                        '</a>'.
            '</table>'.
            '<input type="hidden" id="zayavNomerId" value="'.$zayav['id'].'" />';
        $send['html'] = utf8($send['html']);
        jsonSuccess($send);
        break;

    case 'zp_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['name_id']) || $_POST['name_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']) || $_POST['device_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) || $_POST['vendor_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']) || $_POST['model_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['bu']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
            jsonError();

        $zp = array(
            'name_id' => intval($_POST['name_id']),
            'device_id' => intval($_POST['device_id']),
            'vendor_id' => intval($_POST['vendor_id']),
            'model_id' => intval($_POST['model_id']),
            'version' => win1251(htmlspecialchars(trim($_POST['version']))),
            'bu' => intval($_POST['bu']),
            'color_id' => intval($_POST['color_id']),
        );
        zpAddQuery($zp);

        jsonSuccess();
        break;
    case 'zp_spisok_load':
        $_POST['find'] = win1251($_POST['find']);
        $data = zp_data(1, zpfilter($_POST));
        $send['all'] = utf8(zp_count($data));
        $send['html'] = utf8($data['spisok']);
        jsonSuccess($send);
        break;
    case 'zp_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send = zp_data(intval($_POST['page']), zpfilter($_POST));
        $send['spisok'] = utf8($send['spisok']);
        jsonSuccess($send);
        break;
    case 'zp_avai_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['count']) || $_POST['count'] == 0)
            jsonError();
        if(!preg_match(REGEXP_CENA, $_POST['cena']))
            jsonError();
        $zp_id = _zpCompatId($_POST['zp_id']);
        $count = intval($_POST['count']);
        $cena = round($_POST['cena'], 2);
        $summa = round($count * $cena, 2);
        $sql = "INSERT INTO `zp_move` (
                    `ws_id`,
                    `zp_id`,
                    `count`,
                    `cena`,
                    `summa`,
                    `viewer_id_add`
                ) VALUES (
                    ".WS_ID.",
                    ".$zp_id.",
                    ".$count.",
                    '".$cena."',
                    '".$summa."',
                    ".VIEWER_ID."
                )";
        query($sql);
        history_insert(array(
            'type' => 18,
            'zp_id' => $zp_id,
            'value' => $count
        ));
        $send['count'] = _zpAvaiSet($zp_id);
        jsonSuccess($send);
        break;
    case 'zp_zakaz_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['count']))
            jsonError();
        $zp_id = _zpCompatId($_POST['zp_id']);
        $count = intval($_POST['count']);
        $zakazId = query_value("SELECT `id` FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id." AND `zayav_id`=0 LIMIT 1");
        if($count > 0) {
            $sql = "SELECT IFNULL(SUM(`count`),0)
                    FROM `zp_zakaz`
                    WHERE `ws_id`=".WS_ID."
                      AND `zp_id`=".$zp_id."
                      AND `zayav_id`>0
                    LIMIT 1";
            $zakazZayavCount = query_value($sql);
            if($zakazZayavCount)
                $count -= $zakazZayavCount;
        }
        if($count > 0) {
            if($zakazId)
                query("UPDATE `zp_zakaz` SET `count`=".$count." WHERE `id`=".$zakazId);
            else {
                $sql = "INSERT INTO `zp_zakaz` (
                            `ws_id`,
                            `zp_id`,
                            `count`,
                            `viewer_id_add`
                        ) VALUES (
                            ".WS_ID.",
                            ".$zp_id.",
                            ".$count.",
                            ".VIEWER_ID."
                        )";
                query($sql);
            }
        } else
            query("DELETE FROM `zp_zakaz` WHERE `ws_id`=".WS_ID." AND `zp_id`=".$zp_id);
        jsonSuccess();
        break;
    case 'zp_img_update'://Обновление картинки заявки после загрузки новой
        if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']))
            jsonError();
        $send['html'] = _zpImg(intval($_POST['zp_id']), 'big', 160, 280, 'fotoView');
        jsonSuccess($send);
        break;
    case 'zp_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['zp_id']) || $_POST['zp_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['name_id']) || $_POST['name_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']) || $_POST['device_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['vendor_id']) || $_POST['vendor_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['model_id']) || $_POST['model_id'] == 0)
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['bu']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['color_id']))
            jsonError();

        $zp_id = intval($_POST['zp_id']);
        $name_id = intval($_POST['name_id']);
        $device_id = intval($_POST['device_id']);
        $vendor_id = intval($_POST['vendor_id']);
        $model_id = intval($_POST['model_id']);
        $version = win1251(htmlspecialchars(trim($_POST['version'])));
        $bu = intval($_POST['bu']);
        $color_id = intval($_POST['color_id']);

        $sql = "UPDATE `zp_catalog`
                SET `name_id`=".$name_id.",
                    `base_device_id`=".$device_id.",
                    `base_vendor_id`=".$vendor_id.",
                    `base_model_id`=".$model_id.",
                    `version`='".$version."',
                    `bu`=".$bu.",
                    `color_id`=".$color_id."
                WHERE `id`=".$zp_id;
        query($sql);
        jsonSuccess();
        break;

    case 'report_history_load':
        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            $_POST['worker'] = 0;
        if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
            $_POST['action'] = 0;
        $send['html'] = utf8(report_history_spisok(intval($_POST['worker']), intval($_POST['action'])));
        jsonSuccess($send);
        break;
    case 'report_history_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            $_POST['worker'] = 0;
        if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
            $_POST['action'] = 0;
        $send['html'] = utf8(report_history_spisok(intval($_POST['worker']), intval($_POST['action']), intval($_POST['page'])));
        jsonSuccess($send);
        break;
    case 'report_remind_load':
        if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['private']))
            jsonError();
        $filter = array(
            'status' => intval($_POST['status']),
            'private' => intval($_POST['private'])
        );
        $data = remind_data(1, $filter);
        $send['html'] = utf8(!empty($data) ? report_remind_spisok($data) : '<div class="_empty">Заданий не найдено.</div>');
        jsonSuccess($send);
        break;
    case 'report_remind_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
            jsonError('client');
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
            jsonError('zayav');
        if(!preg_match(REGEXP_DATE, $_POST['day']))
            jsonError('day');
        if(!preg_match(REGEXP_BOOL, $_POST['private']))
            jsonError('private');
        if(empty($_POST['txt']))
            jsonError('txt');
        $client_id = intval($_POST['client_id']);
        $zayav_id = intval($_POST['zayav_id']);
        $txt = win1251(htmlspecialchars(trim($_POST['txt'])));
        $private = intval($_POST['private']);
        $sql = "INSERT INTO `reminder` (
                    `ws_id`,
                    `client_id`,
                    `zayav_id`,
                    `txt`,
                    `day`,
                    `private`,
                    `history`,
                    `viewer_id_add`
                ) VALUES (
                    ".WS_ID.",
                    ".$client_id.",
                    ".$zayav_id.",
                    '".$txt."',
                    '".$_POST['day']."',
                    ".$private.",
                    '".FullDataTime(curTime())." "._viewerName()." создал задание.',
                    ".VIEWER_ID."
                )";
        query($sql);
        history_insert(array(
            'type' => 20,
            'client_id' => $client_id,
            'zayav_id' => $zayav_id
        ));
        $filter = array();
        if(isset($_POST['from_zayav']) && $zayav_id)
            $filter['zayav'] = $zayav_id;
        if(isset($_POST['from_client']) && $client_id)
            $filter['client'] = $client_id;
        $send['html'] = utf8(report_remind_spisok(remind_data(1, $filter)));
        xcache_unset('vkmobile_remind_active');
        jsonSuccess($send);
        break;
    case 'report_remind_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send['html'] = utf8(report_remind_spisok(remind_data(intval($_POST['page']))));
        jsonSuccess($send);
        break;
    case 'report_remind_get':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $sql = "SELECT
                    `client_id`,
                    `zayav_id`,
                    `txt`,
                    `day`,
                    `dtime_add` AS `dtime`,
                    `viewer_id_add`
                FROM `reminder` WHERE `id`=".intval($_POST['id'])." AND `status`=1";
        if(!$r = mysql_fetch_assoc(query($sql)))
            jsonError();
        $r['viewer'] = utf8(_viewerName($r['viewer_id_add'], true));
        if($r['client_id'] > 0) {
            $c = _clientsLink(array($r['client_id']));
            $r['client'] = utf8($c[$r['client_id']]);
        }
        if($r['zayav_id'] > 0)
            $r['zayav'] = utf8(_zayavNomerLink($r['zayav_id'], 1));
        $r['txt'] = utf8($r['txt']);
        $r['dtime'] = utf8(FullDataTime($r['dtime']));
        unset($r['client_id']);
        unset($r['zayav_id']);
        unset($r['viewer_id_add']);
        jsonSuccess($r);
        break;
    case 'report_remind_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['status']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
            jsonError();
        if(!preg_match(REGEXP_DATE, $_POST['day']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['from_zayav']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['from_client']))
            jsonError();
        $history = win1251(htmlspecialchars(trim($_POST['history'])));
        $action = '';
        switch($_POST['action']) {
            case 1: $action = " указал новую дату: ".FullData($_POST['day']).". Причина: ".$history; break;
            case 2: $action = " выполнил задание.".($history ? " (".$history.")" : ''); break;
            case 3: $action = " отменил задание. Причина: ".$history; break;
        }
        $sql = "UPDATE `reminder`
                SET `day`='".$_POST['day']."',
                    `status`=".$_POST['status'].",
                    `history`=CONCAT(`history`,'<BR>".FullDataTime(curTime())." "._viewerName().$action."')
                WHERE `id`=".intval($_POST['id']);
        query($sql);
        $filter = array();
        if($_POST['from_zayav'])
            $filter['zayav'] = $_POST['from_zayav'];
        if($_POST['from_client'])
            $filter['client'] = $_POST['from_client'];
        $data = remind_data(1, $filter);
        $html = report_remind_spisok($data);
        if(empty($data) && !isset($filter['zayav']))
            $html = '<div class="_empty">Заданий нет.</div>';
        $send['html'] = utf8($html);
        xcache_unset('vkmobile_remind_active');
        jsonSuccess($send);
        break;
    case 'report_prihod_load':
        if(!preg_match(REGEXP_DATE, $_POST['day_begin']))
            $_POST['day_begin'] = _curMonday();
        if(!preg_match(REGEXP_DATE, $_POST['day_end']))
            $_POST['day_end'] = _curSunday();
        if(!preg_match(REGEXP_BOOL, $_POST['del_show']) || !ADMIN)
            $_POST['del_show'] = 0;
        $send['html'] = utf8(report_prihod_spisok($_POST['day_begin'], $_POST['day_end'], intval($_POST['del_show'])));
        jsonSuccess($send);
        break;
    case 'report_prihod_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        if(!preg_match(REGEXP_DATE, $_POST['day_begin']))
            jsonError();
        if(!preg_match(REGEXP_DATE, $_POST['day_end']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['del_show']) || !ADMIN)
            $_POST['del_show'] = 0;
        $send['html'] = utf8(report_prihod_spisok($_POST['day_begin'], $_POST['day_end'], intval($_POST['del_show']), intval($_POST['page'])));
        jsonSuccess($send);
        break;
    case 'report_prihod_add':
        if(empty($_POST['about']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
            jsonError();
        $about = win1251(htmlspecialchars(trim($_POST['about'])));
        $sum = intval($_POST['sum']);
        $kassa = intval($_POST['kassa']);
        $sql = "INSERT INTO `money`
                    (`ws_id`,`summa`,`prim`,`kassa`,`viewer_id_add`)
                VALUES
                    (".WS_ID.",".$sum.",'".$about."',".$kassa.",".VIEWER_ID.")";
        query($sql);
        history_insert(array(
            'type' => 6,
            'value' => $sum,
            'value1' => $about
        ));
        jsonSuccess();
        break;
    case 'report_prihod_del':
        if(!ADMIN)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "UPDATE `money` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP
                WHERE `id`=".$id;
        query($sql);
        $sql = "SELECT * FROM `money` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        history_insert(array(
            'type' => 9,
            'value' => $r['summa'],
            'value1' => $r['prim'],
            'client_id' => $r['client_id'],
            'zayav_id' => $r['zayav_id'],
            'zp_id' => $r['zp_id'],
        ));
        jsonSuccess();
        break;
    case 'report_prihod_rest':
        if(!ADMIN)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "UPDATE `money` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00'
                WHERE `id`=".$id;
        query($sql);
        $sql = "SELECT * FROM `money` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        history_insert(array(
            'type' => 19,
            'value' => $r['summa'],
            'value1' => $r['prim'],
            'client_id' => $r['client_id'],
            'zayav_id' => $r['zayav_id'],
            'zp_id' => $r['zp_id']
        ));
        jsonSuccess();
        break;
    case 'report_rashod_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send['html'] = utf8(report_rashod_spisok(intval($_POST['page'])));
        jsonSuccess($send);
        break;
    case 'report_rashod_load':
        if(!preg_match(REGEXP_YEAR, $_POST['year']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['month']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['category']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            jsonError();
        $year = intval($_POST['year']);
        $send['summ'] = report_rashod_monthSum($year, intval($_POST['category']), intval($_POST['worker']));
        $send['html'] = utf8(report_rashod_spisok(1, $year.'-'.$_POST['month'], intval($_POST['category']), intval($_POST['worker'])));
        jsonSuccess($send);
        break;
    case 'report_rashod_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['category']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
            jsonError();
        $category = intval($_POST['category']);
        $about = win1251(htmlspecialchars(trim($_POST['about'])));
        if($category == 0 && empty($about))
            jsonError();
        $sum = intval($_POST['sum']) * -1;
        $kassa = intval($_POST['kassa']);
        $worker = intval($_POST['worker']);
        $sql = "INSERT INTO `money`
                    (`ws_id`,`summa`,   `prim`,   `kassa`,  `rashod_category`,  `worker_id`,`viewer_id_add`)
                VALUES
                    (".WS_ID.",".$sum.",'".$about."',".$kassa.",".$category.",".$worker.",".VIEWER_ID.")";
        query($sql);
        history_insert(array(
            'type' => 21,
            'value' => abs($sum),
            'value1' => $about
        ));
        jsonSuccess();
        break;
    case 'report_rashod_del':
        if(!ADMIN)
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "UPDATE `money` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP
                WHERE `id`=".$id;
        query($sql);
        $sql = "SELECT * FROM `money` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        history_insert(array(
            'type' => 22,
            'value' => abs($r['summa'])
        ));
        jsonSuccess();
        break;
    case 'report_rashod_get':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $sql = "SELECT
                    `summa` * -1 AS `sum`,
                    `prim` AS `about`,
                    `kassa`,
                    `worker_id`,
                    `rashod_category` AS `category`
                FROM `money`
                WHERE `status`=1
                  AND `id`=".intval($_POST['id'])."
                LIMIT 1";
        if(!$send = mysql_fetch_assoc(query($sql)))
            jsonError();
        $send['about'] = utf8($send['about']);
        jsonSuccess($send);
        break;
    case 'report_rashod_edit':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['kassa']))
            jsonError();
        $id = intval($_POST['id']);
        $category = intval($_POST['category']);
        $about = win1251(htmlspecialchars(trim($_POST['about'])));
        if($category == 0 && empty($about))
            jsonError();
        $sum = intval($_POST['sum']) * -1;
        $kassa = intval($_POST['kassa']);
        $worker = intval($_POST['worker']);
        $sql = "UPDATE `money` SET
                    `summa`=".$sum.",
                    `prim`='".$about."',
                    `kassa`=".$kassa.",
                    `rashod_category`=".$category.",
                    `worker_id`=".$worker."
                WHERE `id`=".$id;
        query($sql);
        history_insert(array(
            'type' => 23,
            'value' => abs($sum),
            'value1' => $about
        ));
        jsonSuccess();
        break;
    case 'report_kassa_set':
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
            jsonError();
        $set_sum = intval($_POST['sum']);

        $sql = "SELECT SUM(`sum`) AS `sum` FROM `kassa` WHERE `ws_id`=".WS_ID." AND `status`=1 LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $kassa_sum = $r['sum'];

        $sql = "SELECT SUM(`summa`) AS `sum` FROM `money` WHERE `ws_id`=".WS_ID." AND `status`=1 AND `kassa`=1 LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $money_sum = $r['sum'];

        $kassa_start = $set_sum - $kassa_sum - $money_sum;
        $sql = "UPDATE `workshop` SET `kassa_start`=".$kassa_start." WHERE `id`=".WS_ID;
        query($sql);
        history_insert(array(
            'type' => 24,
            'value' => $set_sum
        ));
        xcache_unset('vkmobile_workshop_'.WS_ID);
        jsonSuccess();
        break;
    case 'report_kassa_load':
        if(!preg_match(REGEXP_BOOL, $_POST['del_show']) || !ADMIN)
            $_POST['del_show'] = 0;
        $send['html'] = utf8(report_kassa_spisok(1, intval($_POST['del_show'])));
        jsonSuccess($send);
        break;
    case 'report_kassa_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send['html'] = utf8(report_kassa_spisok(intval($_POST['page'])));
        jsonSuccess($send);
        break;
    case 'report_kassa_action':
        if(!preg_match(REGEXP_NUMERIC, $_POST['sum']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['down']))
            jsonError();
        $sum = intval($_POST['sum']) * (intval($_POST['down']) == 1 ? -1 : 1);
        $txt = win1251(htmlspecialchars(trim($_POST['txt'])));
        $sql = "INSERT INTO `kassa` (
                    `ws_id`,`sum`,`txt`,`viewer_id_add`
                ) VALUES (
                    ".WS_ID.",".$sum.",'".$txt."',".VIEWER_ID."
                )";
        query($sql);
        jsonSuccess();
        break;
    case 'report_kassa_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "UPDATE `kassa` SET
                    `status`=0,
                    `viewer_id_del`=".VIEWER_ID.",
                    `dtime_del`=CURRENT_TIMESTAMP
                WHERE `id`=".$id;
        query($sql);
        $sql = "SELECT * FROM `kassa` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        history_insert(array(
            'type' => 25,
            'value' => $r['sum'],
            'value1' => $r['txt']
        ));
        $send['sum'] = kassa_sum();
        jsonSuccess($send);
        break;
    case 'report_kassa_rest':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "UPDATE `kassa` SET
                    `status`=1,
                    `viewer_id_del`=0,
                    `dtime_del`='0000-00-00 00:00:00'
                WHERE `id`=".$id;
        query($sql);
        $sql = "SELECT * FROM `kassa` WHERE `id`=".$id;
        $r = mysql_fetch_assoc(query($sql));
        history_insert(array(
            'type' => 26,
            'value' => $r['sum'],
            'value1' => $r['txt']
        ));
        $send['sum'] = kassa_sum();
        jsonSuccess($send);
        break;

    case 'setup_rashod_category_add':
        if(empty($_POST['name']))
            jsonError();
        $sql = "INSERT INTO `setup_rashod_category` (
                    `name`,`viewer_id_add`
                ) VALUES (
                    '".win1251(htmlspecialchars(trim($_POST['name'])))."',".VIEWER_ID."
                )";
        query($sql);
        $send['id'] = mysql_insert_id();
        jsonSuccess($send);
        break;
    case 'tooltip_zayav_info_get':
        if(!preg_match(REGEXP_NUMERIC, $_POST['id']))
            jsonError();
        $id = intval($_POST['id']);
        $sql = "SELECT * FROM `zayavki` WHERE `id`=".$id;
        $zayav = mysql_fetch_assoc(query($sql));

        $sql = "SELECT `fio` FROM `client` WHERE `id`=".$zayav['client_id'];
        $r = mysql_fetch_assoc(query($sql));
        $client = $r['fio'];

        $html = '<table><tr>'.
                    '<td><div class="image">'._zayavImg($id).'</div></td>'.
                    '<td class="inf">'.
                        _deviceName($zayav['base_device_id']).'<br />'.
                        '<b>'._vendorName($zayav['base_vendor_id'])._modelName($zayav['base_model_id']).'</b><br /><br />'.
                        '<span style="color:#000">Клиент:</span> '.$client.
                    '</td>'.
                '</tr></table>';

        $send['html'] = utf8($html);
        jsonSuccess($send);
        break;
}

jsonError();