<?php
require_once('config.php');

switch(@$_POST['op']) {
    case 'cache_clear':
        if(!SA)
            jsonError();
        query("UPDATE `setup_global` SET `script_style`=`script_style`+1");
        _cacheClear();
        jsonSuccess();
        break;

    case 'ws_create':
        $org_name = win1251(htmlspecialchars(trim($_POST['org_name'])));
        if(empty($org_name))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['country_id']))
            jsonError();
        if(!preg_match(REGEXP_NUMERIC, $_POST['city_id']))
            jsonError();

        $country_id = intval($_POST['country_id']);
        $city_id = intval($_POST['city_id']);
        $country_name = win1251(htmlspecialchars(trim($_POST['country_name'])));
        $city_name = win1251(htmlspecialchars(trim($_POST['city_name'])));

        $ex = explode(',', $_POST['devs']);
        foreach($ex as $id)
            if(!preg_match(REGEXP_NUMERIC, $id))
                jsonError();

        $sql = "INSERT INTO `workshop` (
                `admin_id`,
                `org_name`,
                `country_id`,
                `country_name`,
                `city_id`,
                `city_name`,
                `devs`
            ) VALUES (
                ".VIEWER_ID.",
                '".$org_name."',
                ".$country_id.",
                '".$country_name."',
                ".$city_id.",
                '".$city_name."',
                '".$_POST['devs']."'
            )";
        query($sql);
        query("UPDATE `vk_user` SET `ws_id`=".mysql_insert_id().",`admin`=1 WHERE `viewer_id`=".VIEWER_ID);
        _cacheClear();
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
        if(!VIEWER_ADMIN) {
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

    case 'sort':
        if(!preg_match(REGEXP_MYSQLTABLE, $_POST['table']))
            jsonError();
        $table = htmlspecialchars(trim($_POST['table']));
        $sql = "SHOW TABLES LIKE '".$table."'";
        if(!mysql_num_rows(query($sql)))
            jsonError();

        $sort = explode(',', $_POST['ids']);
        if(empty($sort))
            jsonError();
        for($n = 0; $n < count($sort); $n++)
            if(!preg_match(REGEXP_NUMERIC, $sort[$n]))
                jsonError();

        for($n = 0; $n < count($sort); $n++)
            query("UPDATE `".$table."` SET `sort`=".$n." WHERE `id`=".intval($sort[$n]));
        _cacheClear();
        jsonSuccess();
        break;
}

jsonError();