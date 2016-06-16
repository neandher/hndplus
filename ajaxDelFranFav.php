<?php

ini_set('max_execution_time', 100000);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("ajaxIncludes.php");

$db = new MySqlPDO();

delete("fra_code = '{$_GET['franquia']}' and con_id = '" . HND_USER . "'", $db, 'hnd_fra_fav');