<?php
require_once('../include/conf.php');//todo для удаления
require_once('../config.php');
require_once(DOCUMENT_ROOT.'/view/main.php');

function jsonError($values=null)
{
    $send['error'] = 1;
    if(empty($values))
        $send['text'] = 'Произошла неизвестная ошибка.<br />Попробуйте позднее.';
    elseif(is_array($values))
        $send += $values;
    else
        $send['text'] = $values;
    die(json_encode($send));
}//end of jsonError()

function jsonSuccess($send=array())
{
    $send['success'] = 1;
    die(json_encode($send));
}//end of jsonSuccess()

switch(@$_POST['op']) {
    case 'report_prihod_load':
        if(!preg_match(REGEXP_DATE, @$_POST['day_begin']))
            $_POST['day_begin'] = currentMonday();
        if(!preg_match(REGEXP_DATE, @$_POST['day_end']))
            $_POST['day_end'] = currentSunday();
        $send['html'] = utf8(report_prihod_spisok($_POST['day_begin'], $_POST['day_end']));
        jsonSuccess($send);
        break;
    case 'report_prihod_next':
        if(!preg_match(REGEXP_NUMERIC, $_POST['page']))
            jsonError();
        if(!preg_match(REGEXP_DATE, $_POST['day_begin']))
            jsonError();
        if(!preg_match(REGEXP_DATE, $_POST['day_end']))
            jsonError();
        $send['html'] = utf8(report_prihod_spisok($_POST['day_begin'], $_POST['day_end'], intval($_POST['page'])));
        jsonSuccess($send);
        break;
    case 'report_prohod_add':
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
        jsonSuccess();
        break;
}

jsonError();