<?php
include('incUpload.php');

$im = @imagecreatefromjpeg($_POST['link']);
if (!$im) { $im = @imagecreatefrompng($_POST['link']); }
if (!$im) { $im = @imagecreatefromgif($_POST['link']); }

$send = imSave($im, $path, $file_name);

echo json_encode($send);
?>



