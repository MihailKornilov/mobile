<?php
if (!isset($WS->id)) header("Location:".$URL."&my_page=wsIndex");

$html = '';
_mainLinks();
echo $html;
unset($html);


