<?php
require_once('config.php');
if(!SA) jsonError();
require_once(DOCUMENT_ROOT.'/view/sa.php');

switch(@$_POST['op']) {
    case 'ws_status_change':
        if(!preg_match(REGEXP_NUMERIC, $_POST['ws_id']))
            jsonError('�������� id');
        $ws_id = intval($_POST['ws_id']);
        $sql = "SELECT * FROM `workshop` WHERE `id`=".$ws_id;
        if(!$ws = mysql_fetch_assoc(query($sql)))
            jsonError('���������� �� ����������');
        if($ws['status']) {
            query("UPDATE `workshop` SET `status`=0,`dtime_del`=CURRENT_TIMESTAMP WHERE `id`=".$ws_id);
            query("UPDATE `vk_user` SET `ws_id`=0,`admin`=0 WHERE `ws_id`=".$ws_id);
        } else {
            if(query_value("SELECT `ws_id` FROM `vk_user` WHERE `viewer_id`=".$ws['admin_id']))
                jsonError('�� ��������������� ���������� ������ ����������');
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
}

jsonError();