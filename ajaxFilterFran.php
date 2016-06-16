<?php

ini_set('max_execution_time', 100000);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("ajaxIncludes.php");

$_GET['filter'] = utf8_decode($_GET['filter_fran']);

$innerJoin = " inner join uf as uf on uf.sigla = fra.state ";
$innerJoin .= " left join hnd_fra_fav as frf on frf.fra_code = fra.code and frf.con_id = '".HND_USER."' ";

$where = " fra.description like '%" . $_GET['filter'] . "%' or fra.district like '%" . $_GET['filter'] . "%' ";
$where .= " or fra.city like '%" . $_GET['filter'] . "%' or uf.description like '%" . $_GET['filter'] . "%' ";

$select = new SelectSqlHelper();
$select->fields = "fra.description,fra.district,fra.city,fra.code,fra.state,frf.con_id";
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
        <div class="panel-heading">Selecione as franquias que deseja adicionar a pesquisa</div>

        <table class="table table-hover">
            <tr>
                <th>Descri&ccedil;&atilde;o</th>
                <th>Bairro</th>
                <th>Cidade</th>
                <th>Estado</th>
                <th></th>
                <th></th>
            </tr>
            <tbody>

            <?php
            foreach ($sql as $ind => $val) {
                ?>
                <tr>
                    <td scope="row"><?php echo $val['description'] ?></td>
                    <td><?php echo $val['district'] ?></td>
                    <td><?php echo $val['city'] ?></td>
                    <td><?php echo $val['state'] ?></td>
                    <td>
                        <?php if (!empty($val['con_id'])) {
                            ?>
                            <a href="#" onclick="delFranFav('<?php echo $val['code'] ?>')"
                               id="fran_fav_<?php echo $val['code'] ?>"
                               class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Remover dos Favoritos
                            </a>
                            <?php
                        } else {
                            ?>
                            <a href="#" onclick="addFranFav('<?php echo $val['code'] ?>')"
                               id="fran_fav_<?php echo $val['code'] ?>"
                               class="btn btn-warning btn-xs"><span class="glyphicon glyphicon-star"></span> Adicionar aos Favoritos
                            </a>
                            <?php
                        }
                        ?>
                    </td>
                    <td>
                        <a href="#"
                           onclick="updateCodFran('<?php echo $val['code'] ?>|<?php echo strip_tags(preg_replace('/\s/', ' ', $val['description'] . ' - ' . $val['district'])) ?>,')"
                           class="btn btn-info btn-xs">
                            <span class="glyphicon glyphicon-plus"></span> Adicionar a Pesquisa
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <!--<div class="list-group">

            <?php
        /*            foreach ($sql as $ind => $val) {
                        */ ?>
                <a href="#" class="list-group-item"
                   onclick="updateCodFran('<?php /*echo $val['code'] */ ?>|<?php /*echo strip_tags(preg_replace('/\s/', ' ', $val['description'] . ' - ' . $val['district'])) */ ?>,')">
                    <h4 class="list-group-item-heading"><?php /*echo $val['description'] */ ?></h4>
                    <p class="list-group-item-text">
                        <?php /*echo $val['district'] . ' / ' . $val['city'] . ' / ' . $val['state'] */ ?>
                    </p>
                </a>
                <?php
        /*            }
                    */ ?>
        </div>-->
    </div>

    <?php
}
?>