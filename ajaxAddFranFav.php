<?php

ini_set('max_execution_time', 100000);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("ajaxIncludes.php");

$db = new MySqlPDO();

$dados['fra_code'] = $_GET['franquia'];
$dados['con_id'] = HND_USER;

insert($dados, $db, 'hnd_fra_fav');