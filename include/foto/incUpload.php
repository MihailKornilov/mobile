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
  for($i = 0; $i < 10; $i++) { $name .= $arr[rand(0,35)]; }
  return $name;
}


// изменение размера изображения
function imResize(
$im,           // картинка
$x_cur,      // исходный X
$y_cur,      // исходный Y
$x_new,    // новый X
$y_new,    // новый Y
$name) {   // имя файла для сохранения

  // если ширина больше или равна высоте
  if ($x_cur >= $y_cur) {
    $x = $x_new;
    if ($x > $x_cur) { $x = $x_cur; } // если новая ширина больше, чем исходная, то X остаётся исходным
    $y = round($y_cur / $x_cur * $x);
    if ($y > $y_new) { // если новая высота в итоге осталась меньше исходной, то подравнивание по Y
      $y = $y_new;
      $x = round($x_cur / $y_cur * $y);
    }
  }

  // если выстоа больше ширины
  if ($y_cur > $x_cur) {
    $y = $y_new;
    if ($y > $y_cur) { $y = $y_cur; } // если новая высота больше, чем исходная, то Y остаётся исходным
    $x = round($x_cur / $y_cur * $y);
    if ($x > $x_new) { // если новая ширина в итоге осталась меньше исходной, то подравнивание по X
      $x = $x_new;
      $y = round($y_cur / $x_cur * $x);
    }
  }

  $im_new=imagecreatetruecolor($x,$y);
  imagecopyresampled($im_new, $im, 0, 0, 0, 0, $x, $y, $x_cur, $y_cur);
  imagejpeg($im_new, $name, 80);
  imagedestroy($im_new);

  $send->x = $x;
  $send->y = $y;
  return $send;
}






// сохранение изображения и запись в базу
function imSave($im, $path, $file_name) {
  global $VK;
  $send->error = 0;

  if (!$im) {
    $send->error = 1; // если файл - не картинка
  } else {
    $x = imagesx($im);
    $y = imagesy($im);
    if ($x < 100 or $y < 100) {
      $send->error = 2; // если картинка имеет неправильные размеры
    } else {
      $small = imResize($im, $x, $y, 80, 80, $path.$file_name."-small.jpg");
      $big = imResize($im, $x, $y, 610, 610, $path.$file_name."-big.jpg");

      $sort = $VK->QRow("select count(id) from images where owner='".$_GET['owner']."'");
      $send->link = "http://".$_SERVER["SERVER_NAME"]."/files/images/".$file_name;
      $VK->Query("insert into images (
  link,
  small_x,
  small_y,
  big_x,
  big_y,
  owner,
  sort,
  viewer_id_add
  ) values (
  '".$send->link."',
  ".$small->x.",
  ".$small->y.",
  ".$big->x.",
  ".$big->y.",
  '".$_GET['owner']."',
  ".$sort.",
  ".$_GET['viewer_id']."
  )");
      $send->x = $big->x;
      $send->y = $big->y;
    }
  }
  return $send;
}








require_once('../AjaxHeader.php');
ini_set('memory_limit','120M');

$file_name = $_GET['owner']."-".fileNameCreate();
$path = $PATH_FILES."images/";        // путь для хранения изображений
$im = null;
?>
