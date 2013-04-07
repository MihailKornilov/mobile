<?php
include('incUpload.php');

$orig = $path.$file_name."_orig.jpg";
$input = file_get_contents('php://input');  // получаем изображение
file_put_contents($orig, $input);               // сохраняем его как оригинальное
$im = imagecreatefromjpeg($orig);
unlink($orig);

$res = imSave($im, $path, $file_name);

if ($res->error > 0) {
  $cookie = "error_".$res->error;
} else {
  $cookie = "uploaded_";
  setcookie("fotoParam", $res->link."_".$res->x."_".$res->y , time() + 3600, "/");
}
setcookie("fotoUpload", $cookie, time() + 3600, "/");
?>



