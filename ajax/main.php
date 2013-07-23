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
}

jsonError();