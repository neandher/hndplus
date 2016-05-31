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

$innerJoin = "";

$where = " sca.name like '%" . $_GET['filter'] . "%' ";

$select = new SelectSqlHelper();
$select->fields = "sca.sca_id,sca.name,sca.code";
$select->innerjoin = $innerJoin;
$select->where = $where;
$select->orderby = "sca.name asc";
$select->limit = 10;

$db = new MySqlPDO();
$sql = $db->read($select, 'hnd_subcategoria', 'sca', array(), null);

if (count($sql) > 0) {

    ?>

    <div class="panel panel-primary">
        <!-- Default panel contents -->
        <div class="panel-heading">Clique a linha de produtos para adicion&aacute;-los a lista de pesquisa</div>

        <div class="list-group">

            <?php
            foreach ($sql as $ind => $val) {
                ?>
                <a href="#" class="list-group-item" onclick="updateCodProd('<?php echo listaProdPorSca($val['sca_id'], $db) ?>')">
                    <h4 class="list-group-item-heading"><?php echo utf8_encode($val['name']) ?>
                        (<?php echo $val['code'] ?>)</h4>
                </a>
                <?php
            }
            ?>
        </div>
    </div>

    <?php
}
?>

<?php

function listaProdPorSca($sca_id, $db)
{

    $where = " pro.sca_id = '{$sca_id}' ";

    $innerJoin = "";

    $select = new SelectSqlHelper();

    $select->fields = "pro.code";
    $select->innerjoin = $innerJoin;
    $select->where = $where;

    $result = $db->read($select, 'hnd_produto', 'pro', array(), null);

    $str = '';

    foreach ($result as $ind => $value) {
        $str .= $value['code'] . ',';
    }

    return substr($str, 0, (strlen($str)-1));
}
