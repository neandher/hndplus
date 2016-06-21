<?php

ini_set('max_execution_time', 100000);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("ajaxIncludes.php");

//var_dump($_GET);exit;

$cookieFile = TEMP_PATH . session_id() . '.txt';

$url = 'https://vo.hinode.com.br/vo-2/rede_login.asp';

$result = CurlHelper::curl($url, false, false, $cookieFile);

$html = new Simple_html_dom($result['exec']);

$captcha = $html->find('div[id=divQuadro]');

if (count($captcha) > 0) {

    $letra1 = $captcha[0]->nodes[0]->parent->children[0]->nodes[0]->_[4];
    $letra2 = $captcha[0]->nodes[0]->parent->children[1]->nodes[0]->_[4];
    $letra3 = $captcha[0]->nodes[0]->parent->children[2]->nodes[0]->_[4];
    $letra4 = $captcha[0]->nodes[0]->parent->children[3]->nodes[0]->_[4];

    $captchaDecode = $letra1 . $letra2 . $letra3 . $letra4;

    $url = 'https://vo.hinode.com.br/vo-2/rede_login1.asp';

    $post = array(
        'login_tipo_id' => 'idconsultor',
        'rede_usuario' => HND_USER,
        'rede_senha' => HND_PASS,
        'txtValor' => $captchaDecode,
        'entrar' => 'Entrar'
    );

    $post_fields = http_build_query($post, null, '&');

    $result = CurlHelper::curlPost($url, $post_fields, $cookieFile);

    $html = new Simple_html_dom($result['exec']);

    $find_login = $html->find('a');

    if (!is_array($find_login)) {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 4';
        exit;
    }

    if ($find_login[0]->attr['href'] == 'rede_login.asp') {
        echo 'Login ou senha invalidos!';
        exit;
    }

    if ($find_login[0]->attr['href'] == 'index.asp') {

        //

        $idconsultor = HND_USER;

        $searchProdutos = explode(',', $_GET['cod_produtos']);

        $searchCDH = explode(',', $_GET['cod_franquias']);

        // *************** Verifica/Adiciona Produto ****************** //

        $success = false;

        $a_cdh = array();
        $data_cdh = array();
        $check_prod = array();

        $db = new MySqlPDO();

        foreach ($searchCDH as $val_cdh) {

            if (!empty($val_cdh)) {

                if (!in_array($val_cdh, $a_cdh)) {

                    $initDados = getInitDados($cookieFile);

                    $a_cdh[] = $val_cdh;

                    foreach ($searchProdutos as $val_prod) {

                        $post = array(
                            'acao' => 'car_add_item',
                            'idconsultor' => $idconsultor,
                            'id_cdhret' => $val_cdh,
                            'loc_prod' => $val_prod,
                            'qtd_prod' => '1',
                            'ss_pg' => $initDados['ss_pg'],
                            'regra_estoque' => '0',
                            'atv_cons' => '0',
                            'atv_cad_cons' => '1',
                            'vl_sub_ped' => '0.00',
                            'valor_minimo_kit' => '0.00',
                            'ponto_minimo_kit' => '0.00',
                            'atv_cons_bkp' => '0',
                            'atv_cad_cons_bkp' => '1',
                        );

                        $result = executaAcaoProdutos($post, $cookieFile, true);

                        if ($result['exec'] == '') {

                            $dadosProduto = array(
                                'val_prod' => $val_prod,
                                'ss_pg' => $initDados['ss_pg'],
                                'vl_credito' => $initDados['vl_credito'],
                            );

                            $data_cdh[$val_cdh][] = $dadosProduto;

                            $post = array(
                                'acao' => 'car_del_item',
                                'idconsultor' => $idconsultor,
                                'loc_prod' => $val_prod,
                                'qtd_prod' => '1',
                                'ss_pg' => $initDados['ss_pg'],
                                'atv_cad_cons' => '1',
                                'atv_cad_cons_bkp' => '1',
                            );

                            $result = executaAcaoProdutos($post, $cookieFile, true);
                        }
                    }
                }
            }
        }

        if (count($data_cdh) > 0) {

            $str = '<div class="page-header">
                        <h1>Resultado da Pesquisa</h1>
                        <p>Adicione a quantidade de produtos que deseja pedir na franquia que melhor lhe atenda</p>
                    </div>';

            $a_cdh = array();

            foreach ($data_cdh as $ind => $val) {

                if (!in_array($ind, $a_cdh)) {

                    $cdh_info = getCdh($ind, $db);

                    $a_cdh[] = $ind;
                    $str .= '<div class="panel panel-primary"><div class="panel-heading">';
                    $str .= '<strong><span id="txt_fra_' . $ind . '"> ' . $cdh_info['description'] . ' - ' . $cdh_info['district'] . '</span> - ' . $cdh_info['state'] . ' - Total de ' . count(
                            $data_cdh[$ind]
                        ) . ' iten(s)';

                    $str .= '</strong></div>';
                }

                $str .= '<div class="table-responsive">
                        <table class="table table-hover">
                        <tr>
                            <th style="max-width: 20%">Imagem</th>
                            <th>Nome</th>
                            <th>Codigo</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tbody>';

                foreach ($val as $prod) {

                    $sql_prod = getProd($prod['val_prod'], $db);

                    $str .= '<tr>
                                <th scope="row">
                                <img src="https://online.hinode.com.br/produtos/' . $sql_prod['code'] . '_p.jpg" alt="' . $sql_prod['name'] . '"
                                onerror="this.src=\'web-files/default.jpg\'">
                                </th>
                                <td><h4><span class="label label-success" id="txt_pro_' . $sql_prod['code'] . '">' . $sql_prod['name'] . '</span></h4></td>
                                <td><h4><span class="label label-success">' . $sql_prod['code'] . '</span></h4></td>
                                <td id="input_qnt_pedido_' . $sql_prod['code'] . '_' . $ind . '">    
                                    <input type="number" class="form-control" placeholder="quantidade" id="quantidade_' . $sql_prod['code'] . '_' . $ind . '">
                                </td>
                                <td id="btn_add_pedido_' . $sql_prod['code'] . '_' . $ind . '"><a href="javascript:void(0)" class="btn btn-info"';
                    $str .= "onclick=addProdPedido($('#quantidade_" . $sql_prod['code'] . "_" . $ind . "').val(),'" . $sql_prod['code'] . "','" . $ind . "')>";
                    $str .= 'Adicionar pedido</a></td>
                              </tr>';
                }

                $str .= '</tbody></table>';
                $str .= '</div></div>';
            }

            echo $str;

            unlink($cookieFile);

        } else {
            echo '<div class="alert alert-danger" role="alert">Nenhum produto encontrado!</div>';
        }
    } else {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 2';
    }
} else {
    echo 'Houve um erro ao acessar o sistema da hinode. Erro 1';
}

function executaAcaoProdutos($post, $cookieFile, $isPost = false)
{
    $url = 'https://vo.hinode.com.br/ajax/vo3_ajax_consultor_gera_pedido.asp';

    $post_fields = http_build_query($post, null, '&');

    return $isPost ? CurlHelper::curlPost($url, $post_fields, $cookieFile) : CurlHelper::curl(
        $url,
        false,
        false,
        $cookieFile
    );
}

function getCdh($cod, MySqlPDO $db)
{
    $select = new SelectSqlHelper();
    $select->fields = "fra.code,fra.description,fra.state,fra.district,fra.email";
    $select->where = "fra.code = '{$cod}'";

    $sql = $db->read($select, 'hnd_franquia', 'fra', array(), null);

    if (count($sql) > 0) {
        return $sql[0];
    }

    return '';
}

function getProd($cod, MySqlPDO $db)
{
    $select = new SelectSqlHelper();
    $select->fields = "pro.code,pro.name,pro.description";
    $select->where = "pro.code = '{$cod}'";

    $sql = $db->read($select, 'hnd_produto', 'pro', array(), null);

    if (count($sql) > 0) {
        return $sql[0];
    }

    return '';
}

function getInitDados($cookieFile)
{
    $url = 'https://vo.hinode.com.br/vo-2/vo3-gera-pedido.asp';

    $result = CurlHelper::curl($url, false, false, $cookieFile);

    $html = new Simple_html_dom($result['exec']);

    $find_ss_pg = $html->find('input[id=ss_pg]');

    if (!count($find_ss_pg) > 0) {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 3';
        exit;
    }

    $ss_pg = $find_ss_pg[0]->attr['value'];

    $find_vl_credito = $html->find('input[id=vl_credito]');

    if (!count($find_vl_credito) > 0) {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 5';
        exit;
    }

    $vl_credito = $find_vl_credito[0]->attr['value'];

    return array(
        'ss_pg' => $ss_pg,
        'vl_credito' => $vl_credito
    );
}