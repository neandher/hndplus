<?php

ini_set('max_execution_time', 100000);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require("config.php");
require("Helpers/CurlHelper.php");
require("Helpers/EmailHelper.php");
require("Helpers/phpmailer/PHPMailer.php");
require('Helpers/Simple_html_dom.php');
require('Helpers/LoggedExceptionHelper.php');
require('Database/MySqlPDO.php');
require('Helpers/SelectSqlHelper.php');

$_GET['filter'] = utf8_decode($_GET['filter']);

$innerJoin = " inner join uf as uf on uf.sigla = fra.state ";

$where = " fra.description like '%" . $_GET['filter'] . "%' or fra.district like '%" . $_GET['filter'] . "%' ";
$where .= " or fra.city like '%" . $_GET['filter'] . "%' or uf.description like '%" . $_GET['filter'] . "%' ";

$select = new SelectSqlHelper();
$select->fields = "fra.description,fra.district,fra.city,fra.code,fra.state,uf.description";
$select->innerjoin = $innerJoin;
$select->where = $where;
$select->orderby = "fra.description asc";
$select->limit = 10;

$db = new MySqlPDO();
$sql = $db->read($select, 'hnd_franquia', 'fra', array(), null);

if (count($sql) > 0) {

    ?>

    <div class="panel panel-primary">
        <!-- Default panel contents -->
        <div class="panel-heading">Clique sobre a franquia para adicion&aacute;-la a lista de pesquisa</div>

        <div class="list-group">

            <?php
            foreach ($sql as $ind => $val) {
                ?>
                <a href="#" class="list-group-item" onclick="updateCodFran('<?php echo $val['code'] ?>')">
                    <h4 class="list-group-item-heading"><?php echo utf8_encode($val['description']) ?></h4>
                    <p class="list-group-item-text">
                        <?php echo utf8_encode($val['district']) . ' / ' . utf8_encode(
                                $val['city']
                            ) . ' / ' . utf8_encode($val['state']) ?>
                    </p>
                </a>
                <?php
            }
            ?>
        </div>
    </div>

    <?php
}
?>