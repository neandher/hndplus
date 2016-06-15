<?php

ini_set('max_execution_time', 100000);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("ajaxIncludes.php");

$_GET['filter'] = utf8_decode($_GET['filter_fran']);

$innerJoin = " inner join uf as uf on uf.sigla = fra.state ";

$where = " fra.description like '%" . $_GET['filter'] . "%' or fra.district like '%" . $_GET['filter'] . "%' ";
$where .= " or fra.city like '%" . $_GET['filter'] . "%' or uf.description like '%" . $_GET['filter'] . "%' ";

$select = new SelectSqlHelper();
$select->fields = "fra.description,fra.district,fra.city,fra.code,fra.state";
$select->innerjoin = $innerJoin;
$select->where = $where;
$select->orderby = "fra.description asc";
$select->limit = 20;

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
                <a href="#" class="list-group-item"
                   onclick="updateCodFran('<?php echo $val['code'] ?>|<?php echo strip_tags(preg_replace('/\s/', ' ', $val['description'] . ' - ' . $val['district'])) ?>,')">
                    <h4 class="list-group-item-heading"><?php echo $val['description'] ?></h4>
                    <p class="list-group-item-text">
                        <?php echo $val['district'] . ' / ' . $val['city'] . ' / ' . $val['state'] ?>
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