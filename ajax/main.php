<?php
require_once('../include/conf.php');//todo для удаления
require_once('../config.php');
require_once(DOCUMENT_ROOT.'/view/main.php');

function jsonError($values=null) {
    $send['error'] = 1;
    if(empty($values))
        $send['text'] = 'Произошла неизвестная ошибка.<br />Попробуйте позднее.';
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
    case 'report_history_load':
        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            $_POST['worker'] = 0;
        if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
            $_POST['action'] = 0;
        $send['html'] = utf8(report_history(intval($_POST['worker']), intval($_POST['action'])));
        jsonSuccess($send);
        break;
    case 'report_history_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['worker']))
            $_POST['worker'] = 0;
        if(!preg_match(REGEXP_NUMERIC, $_POST['action']))
            $_POST['action'] = 0;
        $send['html'] = utf8(report_history(intval($_POST['worker']), intval($_POST['action']), intval($_POST['page'])));
        jsonSuccess($send);
        break;
    case 'report_remind_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['client_id']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['zayav_id']))
            jsonError();
        if(!preg_match(REGEXP_DATE, $_POST['day']))
            jsonError();
        if(!preg_match(REGEXP_BOOL, $_POST['private']))
            jsonError();
        if(empty($_POST['txt']))
            jsonError();
        $client_id = intval($_POST['client_id']);
        $zayav_id = intval($_POST['zayav_id']);
        $txt = win1251(htmlspecialchars(trim($_POST['txt'])));
        $private = intval($_POST['private']);
        $sql = "SELECT CONCAT(`first_name`,' ',`last_name`) AS `name` FROM `vk_user` WHERE `viewer_id`=".VIEWER_ID." LIMIT 1";
        $r = mysql_fetch_assoc(query($sql));
        $vk_name = $r['name'];
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
                    '".FullDataTime(strftime("%Y-%m-%d %H:%M:%S", time()))." ".$vk_name." создал задание.',
                    ".VIEWER_ID."
                )";
        query($sql);
        history_insert(array(
            'type' => 20,
            'client_id' => $client_id,
            'zayav_id' => $zayav_id
        ));
        $send['html'] = utf8(report_remind_spisok());
        jsonSuccess($send);
        break;
    case 'report_remind_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        $send['html'] = utf8(report_remind_spisok(intval($_POST['page'])));
        jsonSuccess($send);
        break;
    case 'report_prihod_load':
        if(!preg_match(REGEXP_DATE, $_POST['day_begin']))
            $_POST['day_begin'] = currentMonday();
        if(!preg_match(REGEXP_DATE, $_POST['day_end']))
            $_POST['day_end'] = currentSunday();
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

        if ($kassa == 1) {
            $sql = "INSERT INTO `kassa`
                        (`ws_id`, `sum`, `txt`, `money_id`, `viewer_id_add`)
                    VALUES
                        (".WS_ID.", ".$sum.", '".$about."', ".mysql_insert_id().", ".VIEWER_ID.")";
            query($sql);
        }
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
            'zp_id' => $r['zp_id'],
        ));
        jsonSuccess();
        break;
}

jsonError();