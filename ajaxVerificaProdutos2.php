<?php

ini_set('max_execution_time', 100000);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("ajaxIncludes.php");

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
        'rede_usuario'  => HND_USER,
        'rede_senha'    => HND_PASS,
        'txtValor'      => $captchaDecode,
        'entrar'        => 'Entrar'
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

        $url = 'https://vo.hinode.com.br/vo-2/vo3-gera-pedido.asp';

        $result = CurlHelper::curl($url, false, false, $cookieFile);

        $html = new Simple_html_dom($result['exec']);

        $find_ss_pg = $html->find('input[id=ss_pg]');

        if (is_null($find_ss_pg)) {
            echo 'Houve um erro ao acessar o sistema da hinode. Erro 3';
            exit;
        }

        $ss_pg = $find_ss_pg[0]->attr['value'];

        $idconsultor = HND_USER;

        $searchProdutos = explode(',', $_GET['cod_produtos']);

        $searchCDH = explode(',', $_GET['cod_franquias']);

        // *************** Verifica/Adiciona Produto ****************** //

        $success = false;

        $a_cdh = array();
        $data_cdh = array();

        $db = new MySqlPDO();

        foreach ($searchCDH as $val_cdh) {

            if (!empty($val_cdh)) {

                if (!in_array($val_cdh, $a_cdh)) {

                    $a_cdh[] = $val_cdh;

                    foreach ($searchProdutos as $val_prod) {

                        $post = array(
                            'acao'             => 'car_add_item',
                            'idconsultor'      => $idconsultor,
                            'id_cdhret'        => $val_cdh,
                            'loc_prod'         => $val_prod,
                            'qtd_prod'         => '1',
                            'ss_pg'            => $ss_pg,
                            'regra_estoque'    => '0',
                            'atv_cons'         => '0',
                            'atv_cad_cons'     => '1',
                            'vl_sub_ped'       => '0.00',
                            'valor_minimo_kit' => '0.00',
                            'ponto_minimo_kit' => '0.00',
                            'atv_cons_bkp'     => '0',
                            'atv_cad_cons_bkp' => '1',
                        );

                        $result = executaAcaoProdutos($post, $cookieFile, true);

                        if ($result['exec'] == '') {

                            $data_cdh[$val_cdh][] = $val_prod;

                            //var_dump($result['exec']);

                            /**
                             *
                             * type: 'POST',
                             * data: { acao: 'car_del_item', idconsultor: vid_cons, loc_prod: vloc_prod, ss_pg: vss_pg, atv_cad_cons: vatv_cad_cons_bkp, qtd_prod: vqtd },
                             */

                            $post = array(
                                'acao'             => 'car_del_item',
                                'idconsultor'      => $idconsultor,
                                'loc_prod'         => $val_prod,
                                'qtd_prod'         => '1',
                                'ss_pg'            => $ss_pg,
                                'atv_cad_cons'     => '1',
                                'atv_cad_cons_bkp' => '1',
                            );

                            $result = executaAcaoProdutos($post, $cookieFile, true);

                            //var_dump($result['exec']);
                        }
                    }

                }
            }
        }

        if (count($data_cdh) > 0) {

            $str = '';

            $a_cdh = array();

            foreach ($data_cdh as $ind => $val) {

                if (!in_array($ind, $a_cdh)) {

                    $a_cdh[] = $ind;
                    $str .= '<div class="panel panel-primary"><div class="panel-heading"><strong>' . getCdh($ind, $db) . '</strong></div>';
                }

                $str .= '<table class="table table-hover">
                        <tr>
                            <th>Imagem</th>
                            <th>Codigo</th>
                            <th>Nome</th>
                            <th style="width: 45%;">Descricao</th>
                        </tr>
                        <tbody>';

                foreach ($val as $prod) {

                    $sql_prod = getProd($prod, $db);

                    $str .= '<tr>
                                <th scope="row"><img src="https://online.hinode.com.br/produtos/' . $sql_prod['code'] . '_p.jpg" alt="' . $sql_prod['name'] . '"></th>
                                <td>' . $sql_prod['code'] . '</td>
                                <td>' . $sql_prod['name'] . '</td>
                                <td>' . $sql_prod['description'] . '</td>
                            </tr>';
                }

                $str .= '</tbody></table>';
                $str .= '</div>';
            }

            echo $str;

            unlink($cookieFile);

        } else {
            echo 'Nenhum resultado encontrado';
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
    /*$contentCDH = array(
        '10360072' => 'Vila Velha - Praia da Costa',
        '10650784' => 'Vitoria - Praia do Canto',
        '10153011' => 'Vitoria - Santa Lucia',
        '10615393' => 'CARIACICA - JARDIM AMERICA',
        '10438342' => 'SERRA - PQ RESIDENCIAL LARANJEIRAS',
        '10930066' => 'GUARAPARI',

        '10488217' => 'Cachoeiro de Itapemirim - CENTRO',
        '10774033' => 'COLATINA - ESPLANADA',
        '10843260' => 'LINHARES - CENTRO',
        '10790939' => 'SAO MATEUS - SERNAMBY',
    );

    return isset($contentCDH[$cod]) ? $contentCDH[$cod] : '';*/

    $select = new SelectSqlHelper();
    $select->fields = "fra.code,fra.description,fra.state,fra.district";
    $select->where = "fra.code = '{$cod}'";

    $sql = $db->read($select, 'hnd_franquia', 'fra', array(), null);

    if (count($sql) > 0) {
        return $sql[0]['description'] . ' - ' . $sql[0]['district'] . ' - ' . $sql[0]['state'];
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
        //return $sql[0]['code'] . ' - ' . strip_tags($sql[0]['name']);
        return $sql[0];
    }

    return '';
}