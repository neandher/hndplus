<?php

ini_set('max_execution_time', 100000);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("ajaxIncludes.php");

$db = new MySqlPDO();

$fields = "fra.code,fra.description,fra.district";

$innerJoin = " inner join hnd_fra_fav as frf on frf.fra_code = fra.code and frf.con_id = '".HND_USER."' ";

$select = new SelectSqlHelper();
$select->fields = $fields;
$select->innerjoin = $innerJoin;

$sql = $db->read($select, 'hnd_franquia', 'fra', array(), null);

echo json_encode($sql, JSON_UNESCAPED_UNICODE);