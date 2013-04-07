<?php
include('incUpload.php');

$post_name = $_FILES["file_name"]["tmp_name"];
switch ($_FILES["file_name"]["type"]) {
  case 'image/jpeg': $im = @imagecreatefromjpeg($post_name); break;
  case 'image/png': $im = @imagecreatefrompng($post_name); break;
  case 'image/gif': $im = @imagecreatefromgif($post_name); break;
  case 'image/tiff':
    $tmp = $path.$file_name."_upload";
    if (move_uploaded_file($post_name, $tmp.".tif")) {
      exec("convert ".$tmp.".tif ".$tmp.".jpg");
      unlink($tmp.".tif");
      $im = @imagecreatefromjpeg($tmp.".jpg");
      unlink($tmp.".jpg");
    }
  break;
}

$res = imSave($im, $path, $file_name);
if ($res->error > 0) {
  $cookie = "error_".$res->error;
} else {
  $cookie = "uploaded_";
  setcookie("fotoParam", $res->link."_".$res->x."_".$res->y , time() + 3600, "/");
}
setcookie("fotoUpload", $cookie, time() + 3600, "/");
?>
