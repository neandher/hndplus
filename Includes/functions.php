<?php

function insert(Array $dados, $db, $tabela)
{

    $campos = implode(", ", array_keys($dados));

    foreach ($dados as $ind => $val) {
        $expVal[] = "?";
    }

    $expVal = implode(", ", $expVal);

    $con = " INSERT INTO {$tabela} ({$campos}) VALUES ({$expVal}) ";

    $valores = array_values($dados);

    $db->prepareExecute($con, $valores);
}

function delete( $where, $db, $tabela ){

    $con = " DELETE FROM {$tabela} WHERE {$where} ";

    return $db->prepareExecute( $con,array());
}