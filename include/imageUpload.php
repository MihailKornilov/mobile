<?php
/*
 * Функции для сохранения изображений для трёх видов загрузок:
 * - при выборе файла
 * - по прямой ссылке
 * - с вебкамеры
*/

// создание имени файла
function fileNameCreate() {
  $arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','1','2','3','4','5','6','7','8','9','0');
  $name = '';
  for($i = 0; $i < 10; $i++)
      $name .= $arr[rand(0,35)];
  return $name;
}

// изменение размера изображения
function imResize($im, $x_cur, $y_cur, $x_new, $y_new, $name) {
    $send = _imageResize($x_cur, $y_cur, $x_new, $y_new);

    $im_new = imagecreatetruecolor($send['x'], $send['y']);
    imagecopyresampled($im_new, $im, 0, 0, 0, 0, $send['x'], $send['y'], $x_cur, $y_cur);
    imagejpeg($im_new, $name, 80);
    imagedestroy($im_new);

    return $send;
}

// сохранение изображения и запись в базу
function imSave($im) {
    $send['error'] = 0;

    if(!$im) {
        $send['error'] = 1; // если файл - не картинка
    } else {
        $x = imagesx($im);
        $y = imagesy($im);
        if($x < 100 or $y < 100) {
            $send['error'] = 2; // если картинка имеет неправильные размеры
        } else {
            $small = imResize($im, $x, $y, 80, 80, SERVER_PATH."-small.jpg");
            $big = imResize($im, $x, $y, 610, 610, SERVER_PATH."-big.jpg");

            $sort = query_value("SELECT COUNT(`id`) FROM `images` WHERE `owner`='".OWNER."' LIMIT 1");
            $send['link'] = 'http://'.DOMAIN.'/files/images/'.FILE_NAME;
            $sql = "INSERT INTO `images` (
                  `link`,
                  `small_x`,
                  `small_y`,
                  `big_x`,
                  `big_y`,
                  `owner`,
                  `sort`,
                  `viewer_id_add`
              ) VALUES (
                  '".$send['link']."',
                  ".$small['x'].",
                  ".$small['y'].",
                  ".$big['x'].",
                  ".$big['y'].",
                  '".OWNER."',
                  ".$sort.",
                  ".VIEWER_ID."
              )";
            query($sql);
            $send['x'] = $big['x'];
            $send['y'] = $big['y'];
        }
    }
    return $send;
}

function imgUploadCookie($res) {
    if($res['error']) {
        $cookie = "error_".$res['error'];
    } else {
        $cookie = "uploaded_";
        setcookie('fotoParam', $res['link'].'_'.$res['x'].'_'.$res['y'] , time() + 3600, "/");
    }
    setcookie('fotoUpload', $cookie, time() + 3600, '/');
    exit;
}

ini_set('memory_limit', '120M');

require_once('../config.php');
require_once(DOCUMENT_ROOT.'/view/main.php');

if(!preg_match(REGEXP_WORD, $_GET['owner']))
    imgUploadCookie(array('error'=>0));

define('OWNER', $_GET['owner']);
define('FILE_NAME', OWNER.'-'.fileNameCreate());
define('SERVER_PATH', PATH_FILES.'images/'.FILE_NAME);
$im = null;

switch($_POST['op']) {
    case 'file':
        $post_name = $_FILES["file_name"]["tmp_name"];
        $im = null;
        switch ($_FILES["file_name"]["type"]) {
            case 'image/jpeg': $im = @imagecreatefromjpeg($post_name); break;
            case 'image/png': $im = @imagecreatefrompng($post_name); break;
            case 'image/gif': $im = @imagecreatefromgif($post_name); break;
            case 'image/tiff':
                $tmp = SERVER_PATH."_upload";
                if(move_uploaded_file($post_name, $tmp.".tif")) {
                    exec("convert ".$tmp.".tif ".$tmp.".jpg");
                    unlink($tmp.".tif");
                    $im = @imagecreatefromjpeg($tmp.".jpg");
                    unlink($tmp.".jpg");
                }
                break;
        }

        imgUploadCookie(imSave($im));
        break;
    case 'link':
        $im = @imagecreatefromjpeg($_POST['link']);
        if(!$im) $im = @imagecreatefrompng($_POST['link']);
        if(!$im) $im = @imagecreatefromgif($_POST['link']);

        die(json_encode(imSave($im)));
        break;
    case 'webcam':
    default:
        $orig = SERVER_PATH."_orig.jpg";
        $input = file_get_contents('php://input');  // получаем изображение
        file_put_contents($orig, $input);           // сохраняем его как оригинальное
        $im = imagecreatefromjpeg($orig);
        unlink($orig);
        imgUploadCookie(imSave($im));
}