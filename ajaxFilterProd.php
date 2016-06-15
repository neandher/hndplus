<?php

ini_set('max_execution_time', 100000);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("ajaxIncludes.php");

$_GET['filter'] = utf8_decode($_GET['filter_prod']);

/*$innerJoin = " left join hnd_subcategoria as sca on sca.code = pro.sca_id  ";
$innerJoin .= " left join hnd_categoria as cat on cat.code = sca.cat_id  ";*/
$innerJoin = "";

$where = " pro.name like '%" . $_GET['filter'] . "%' or pro.code like '%" . $_GET['filter'] . "%' ";
$where .= " or pro.description like '%" . $_GET['filter'] . "%' ";
//$where .= " or sca.name like '%" . $_GET['filter'] . "%' or cat.name like '%" . $_GET['filter'] . "%' ";
//$where .= " or sca.name like '%" . $_GET['filter'] . "%' ";

$select = new SelectSqlHelper();
$select->fields = "pro.name,pro.code,pro.description";
$select->innerjoin = $innerJoin;
$select->where = $where;
$select->orderby = "pro.name asc";
$select->limit = 20;

$db = new MySqlPDO();
$sql = $db->read($select, 'hnd_produto', 'pro', array(), null);

if (count($sql) > 0) {

    ?>

    <div class="panel panel-primary">
        <!-- Default panel contents -->
        <div class="panel-heading">Clique sobre o produto para adicion&aacute;-lo a lista de pesquisa</div>

        <table class="table table-hover">
            <tbody>

            <?php
            foreach ($sql as $ind => $val) {
                ?>

                <tr style="cursor: pointer" onclick="updateCodProd('<?php echo $val['code'] ?>|<?php echo $val['name'].',' ?>')">
                    <th scope="row">
                        <img src="https://online.hinode.com.br/produtos/<?php echo $val['code'] ?>_p.jpg"
                             onerror="this.src='web-files/default.jpg'"/>
                    </th>
                    <td><?php echo '<strong>' . $val['code'] . ' - ' . $val['name'] . '</strong> <br>' . $val['description'] ?></td>
                </tr>

                <?php
            }
            ?>
            </tbody>
        </table>
    </div>

    <?php
}
?>