<?php

/**
 * Description of MySqlPDO
 *
 * @author Neandher
 */
class MySqlPDO
{

    private $_db;
    static $_dbinfo;

    public function __construct()
    {

        self::$_dbinfo['host'] = DB_HOST_MYSQL;
        self::$_dbinfo['user'] = DB_USER_MYSQL;
        self::$_dbinfo['pass'] = DB_PASS_MYSQL;
        self::$_dbinfo['name'] = DB_NAME_MYSQL;

        try {
            $this->_db = new PDO(
                'mysql:host=' . self::$_dbinfo['host'] . ';dbname=' . self::$_dbinfo['name'],
                self::$_dbinfo['user'],
                self::$_dbinfo['pass'],
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function prepareExecute($con, $valores)
    {

        try {

            $q = $this->_db->prepare($con);

            return $q->execute($valores);

        } catch (PDOException $e) {
            //$log = new LoggedExceptionHelper($e);
            die($e->getMessage());
        }

    }

    public function read(SelectSqlHelper $select, $tabela, $nickname, Array $valores, $opt)
    {

        $fields = ($select->fields != null ? "{$select->fields}" : "*");
        $where = ($select->where != null ? "WHERE {$select->where}" : "");
        $limit = ($select->limit != null ? "LIMIT {$select->limit}" : "");
        $offset = ($select->offset != null ? "OFFSET {$select->offset}" : "");
        $orderby = ($select->orderby != null ? "ORDER BY {$select->orderby}" : "");
        $innerJoin = ($select->innerjoin != null ? "{$select->innerjoin}" : "");
        $distinct = ($select->distinct != null ? "{$select->distinct}" : "");
        $alternativeSql = ($select->alternativeSql != null ? "{$select->alternativeSql}" : "");
        $paginator = (count(
            $select->paginator
        ) > 0 ? "LIMIT {$select->paginator['pi']} , {$select->paginator['maxRegistriesPerPage']}" : "");

        if ($alternativeSql != null) {
            $con = $alternativeSql;
        } else {

            $con = " SELECT {$distinct} {$fields} FROM {$tabela} {$nickname} {$innerJoin} {$where} {$orderby} {$paginator} {$limit} {$offset}  ";

        }

        //echo $con.'<br><br>';
        //var_dump($select->orderby);
        //exit;

        try {

            $q = $this->_db->prepare($con);

            if (is_null($opt)) {

                $q->execute($valores);

            } else {
                if ($opt == 'bind') {

                    //ex: $dados = array(":bind"=>array($bindvalue=>"str"));

                    foreach ($valores as $key => $value) {

                        foreach ($value as $ind => $value2) {

                            if ($value2 == "int") {

                                $q->bindValue($key, $ind, PDO::PARAM_INT);
                            }

                            if ($value2 == "str") {

                                $q->bindValue($key, $ind, PDO::PARAM_STR);
                            }
                        }
                    }

                    $q->execute();

                }
            }

            return $q->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $log = new LoggedExceptionHelper($e);
        }
    }
}
