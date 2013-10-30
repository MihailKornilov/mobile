<?php
require_once('config.php');
if(!SA) jsonError();
require_once(DOCUMENT_ROOT.'/view/sa.php');

switch(@$_POST['op']) {
    case 'ws_status_change':
        if(!preg_match(REGEXP_NUMERIC, $_POST['ws_id']))
            jsonError('Неверный id');
        $ws_id = intval($_POST['ws_id']);
        $sql = "SELECT * FROM `workshop` WHERE `id`=".$ws_id;
        if(!$ws = mysql_fetch_assoc(query($sql)))
            jsonError('Мастерской не существует');
        if($ws['status']) {
            query("UPDATE `workshop` SET `status`=0,`dtime_del`=CURRENT_TIMESTAMP WHERE `id`=".$ws_id);
            query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".$ws_id);
        } else {
            if(query_value("SELECT `ws_id` FROM `vk_user` WHERE `viewer_id`=".$ws['admin_id']))
                jsonError('За администратором закреплена другая мастерская');
            query("UPDATE `workshop` SET `status`=1,`dtime_del`='0000-00-00 00:00:00' WHERE `id`=".$ws_id);
            query("UPDATE `vk_user` SET `ws_id`=".$ws_id.",`admin`=1 WHERE `viewer_id`=".$ws['admin_id']);
            xcache_unset(CACHE_PREFIX.'viewer_'.$ws['admin_id']);
        }
        _cacheClear($ws_id);
        jsonSuccess();
        break;
    case 'ws_del':
        if(!preg_match(REGEXP_NUMERIC, $_POST['ws_id']))
            jsonError();
        $ws_id = intval($_POST['ws_id']);
        foreach(sa_ws_tables() as $tab => $about)
            query("DELETE FROM `".$tab."` WHERE `ws_id`=".$ws_id);
        query("DELETE FROM `workshop` WHERE `id`=".$ws_id);
        query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".$ws_id);
        _cacheClear($ws_id);
        jsonSuccess();
        break;
    case 'ws_equip_add':
        if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
            jsonError();
        $device_id = intval($_POST['device_id']);
        $name = win1251(htmlspecialchars(trim($_POST['name'])));
        $title = win1251(htmlspecialchars(trim($_POST['title'])));
        if(empty($name))
            jsonError();
        $sort = query_value("SELECT IFNULL(MAX(`sort`)+1,0) FROM `setup_device_equip`");
        $sql = "INSERT INTO `setup_device_equip` (
                    `name`,
                    `title`,
                    `sort`,
                    `viewer_id_add`
                ) VALUES (
                    '".addslashes($name)."',
                    '".addslashes($title)."',
                    ".$sort.",
                    ".VIEWER_ID."
                )";
        query($sql);
        $send['html'] = utf8(sa_equip_spisok($device_id));
        jsonSuccess($send);
        break;
    case 'ws_equip_set':
        if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
            jsonError();
        $device_id = intval($_POST['device_id']);

        if(!empty($_POST['ids'])) {
            $ids = explode(',', $_POST['ids']);
            for($n = 0; $n < count($ids); $n++)
                if(!preg_match(REGEXP_NUMERIC, $ids[$n]))
                    jsonError();
        }

        $sql = "UPDATE `base_device` SET `equip`='".$_POST['ids']."' WHERE `id`=".$device_id;
        query($sql);
        jsonSuccess();
        break;
    case 'ws_equip_show':
        if(!preg_match(REGEXP_NUMERIC, $_POST['device_id']))
            jsonError();
        $device_id = intval($_POST['device_id']);
        $send['html'] = utf8(sa_equip_spisok($device_id));
        jsonSuccess($send);
        break;
}

jsonError();