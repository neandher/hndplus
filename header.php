<?php

$gmtDate = gmdate("D, d M Y H:i:s");
header("Expires: {$gmtDate} GMT");
header("Last-Modified: {$gmtDate} GMT");
header("Cache-Control: no-cache,must-revalidate");
header("Pragma: no-cache");
//header('Content-Type: text/html; charset=utf-8');
header('Content-Type: text/html; charset=iso-8859-1');