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

$efetuar_pedido = $_GET['pesquisa_opcao'] == 'pesquisar_pedido' ? true : false;

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
        $qtd_cdh = array();

        $db = new MySqlPDO();

        foreach ($searchCDH as $val_cdh) {

            if (!empty($val_cdh)) {

                if (!in_array($val_cdh, $a_cdh)) {

                    $initDados = getInitDados($cookieFile);

                    $count = 0;

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

                            $count++;

                            $dadosProduto = array(
                                'val_prod' => $val_prod,
                                'ss_pg' => $initDados['ss_pg'],
                                'vl_credito' => $initDados['vl_credito'],
                            );

                            $data_cdh[$val_cdh][] = $dadosProduto;

                            if (!$efetuar_pedido || ($efetuar_pedido && in_array($val_prod, $check_prod))) {

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
                                $count--;
                            }

                            $check_prod[] = $val_prod;
                        }
                    }

                    if ($count > 0) {
                        $qtd_cdh[$val_cdh] = $count;
                    }
                }
            }
        }

        if (count($qtd_cdh) > 0 && $efetuar_pedido) {

            arsort($qtd_cdh);

            foreach ($qtd_cdh as $cdh => $qtd) {

                $ss_pg = $data_cdh[$cdh][0]['ss_pg'];
                $vl_credito = $data_cdh[$cdh][0]['vl_credito'];

                $post = array(
                    'acao' => 'validar-carrinho',
                    'ss_pg' => $ss_pg,
                );

                $result = executaAcaoProdutos($post, $cookieFile, true);

                $retorno = $result['exec'];

                if (strstr($retorno, '|')) {

                    $exp = explode('|', $retorno);

                    if ($exp[0] === '0') {

                        $pedido = efetuaPedido($cookieFile, $cdh, $ss_pg, $vl_credito, $db);
                        $data_cdh[$cdh][0]['numero_pedido'] = $pedido;
                    }
                }
            }
        }

        if (count($data_cdh) > 0) {

            $str = '';

            $a_cdh = array();

            foreach ($data_cdh as $ind => $val) {

                if (!in_array($ind, $a_cdh)) {

                    $cdh_info = getCdh($ind, $db);

                    $a_cdh[] = $ind;
                    $str .= '<div class="panel panel-primary"><div class="panel-heading">';
                    $str .= '<strong>' . $cdh_info['description'] . ' - ' . $cdh_info['district'] . ' - ' . $cdh_info['state'] . ' - Total de ' . count($data_cdh[$ind]) . ' iten(s)';

                    if ($efetuar_pedido && isset($data_cdh[$ind][0]['numero_pedido'])) {
                        $str .= ' - Numero do pedido: ' . $data_cdh[$ind][0]['numero_pedido'];
                    }

                    $str .= '</strong></div>';
                }

                $str .= '<table class="table table-hover">
                        <tr>
                            <th style="max-width: 20%">Imagem</th>
                            <th>Codigo</th>
                            <th>Nome</th>
                            <th style="width: 45%;">Descricao</th>';

                $str .= $efetuar_pedido ? '<th>Status</th>' : '';

                $str .= '</tr>
                        <tbody>';

                foreach ($val as $prod) {

                    $sql_prod = getProd($prod['val_prod'], $db);

                    $str .= '<tr>
                                <th scope="row">
                                <img src="https://online.hinode.com.br/produtos/' . $sql_prod['code'] . '_p.jpg" alt="' . $sql_prod['name'] . '"
                                onerror="this.src=\'web-files/default.jpg\'">
                                </th>
                                <td>' . $sql_prod['code'] . '</td>
                                <td>' . $sql_prod['name'] . '</td>
                                <td>' . $sql_prod['description'] . '</td>';

                    if($efetuar_pedido){

                        $pedido_efetuado = false;

                        foreach ($qtd_cdh as $c_ind => $p_val){
                            if($c_ind == $ind){
                                $pedido_efetuado = true;
                            }
                        }
                        if($pedido_efetuado){
                            $str .= '<td><div class="alert alert-success" role="alert"><i class="glyphicon glyphicon-ok"></i> Pedido do produto efetuado</div></td>';
                        }
                        else{
                            $str .= '<td><div class="alert alert-danger" role="alert"><i class="glyphicon glyphicon-remove"></i> Pedido do produto nao efetuado</div></td>';
                        }
                    }

                    $str .= '</tr>';
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

function efetuaPedido($cookieFile, $val_cdh, $ss_pg, $vl_credito, MySqlPDO $db)
{

    $post = array(
        'acao' => 'car_lista_item',
        'idconsultor' => HND_USER,
        'ss_pg' => $ss_pg,
        'id_cdhret' => $val_cdh,
        'atv_cad_cons' => '1',
        'atv_cons' => '1'
    );

    $result = executaAcaoProdutos($post, $cookieFile, true);

    $retorno = json_decode($result['exec'], true);

    $arrayProdutos = array();
    $valor_subtotal_pedido = 0.00;
    $valor_total_pedido = 0.00;
    $pontos_total_pedido = 0.00;
    $peso_total_item = 0;
    $peso_total_pedido = 0;
    $valorDesconto = 0.00;

    foreach ($retorno as $val) {

        $arrayProdutos[$val['Car_prod_codigo']] = 1;
        $idProduto = $val['Car_prod_codigo'];
        $qtdProduto = $arrayProdutos[$val['Car_prod_codigo']];
        $cdCarrinho = $val['CdCarrinho'];

        foreach ($retorno as $value) {
            if ($value['Car_prod_codigo'] == $idProduto && $value['CdCarrinho'] != $cdCarrinho) {
                $qtdProduto++;
            }
        }

        $vqtd_total_item = $qtdProduto;
        $valor_subtotal_pedido += $vqtd_total_item * $val['Car_prod_valor_unt'];
        $valor_total_item = $vqtd_total_item * ($val['Car_prod_valor_unt'] - $val['Car_prod_desconto']);
        $valor_total_pedido += $valor_total_item;
        $pontos_total_item = $vqtd_total_item * $val['Car_prod_pontuacao'];
        $pontos_total_pedido += $pontos_total_item;

        if ($val['Car_prod_peso'] != '') {
            $peso_total_item = $qtdProduto * $val['Car_prod_peso'];
            $peso_total_pedido += $peso_total_item;
        }

        $valorDesconto += $val['Car_prod_desconto'];
    }

    if ($vl_credito >= $valor_total_pedido) {
        $valor_total_pedido_cred = 0.00;
    } else {
        $valor_total_pedido_cred = $valor_total_pedido - number_format($vl_credito, 2, '.', '');
    }

    $valor_total_pedido = number_format($valor_total_pedido_cred, 2, '.', '');
    $valor_subtotal_pedido = number_format($valor_subtotal_pedido, 2, '.', '');
    $pontos_total_pedido = number_format($pontos_total_pedido, 2, '.', '');
    $peso_total_pedido = number_format($peso_total_pedido, 3);

    // ----------

    $valor_credito_usado = 0.00;
    $valor_total_pedido_calc = 0.00;

    if ($vl_credito >= $valor_subtotal_pedido) {
        $valor_credito_usado = number_format($valor_subtotal_pedido, 2, '.', '');
    } else {
        $valor_total_pedido_calc = $valor_subtotal_pedido - number_format($vl_credito, 2, '.', '');
        $valor_credito_usado = number_format($vl_credito, 2, '.', '');
    }

    $valor_total_pedido = number_format($valor_total_pedido_calc, 2, '.', '');

    $post = array(
        'acao' => 'calc_frete_list_transp',
        'ss_pg' => $ss_pg,
        'peso_ped' => $peso_total_pedido,
        'cep_entr' => '',
        'id_cdhret' => $val_cdh,
    );

    $result = executaAcaoProdutos($post, $cookieFile, true);

    $retorno = json_decode($result['exec'], true);

    foreach ($retorno as $val) {
        $vtrans = $val['transporte'];
        $vtrans_desc = $val['descricao'];
        $vtrans_prazo = $val['prazo'];
    }

    $cdh_info = getCdh($val_cdh, $db);

    $post = array(
        'sessao_tipo_compra' => '1',
        'ss_pg' => $ss_pg,
        HND_USER => 'sessao_id_cons',
        'atv_cons' => '1',
        'atv_cons_bkp' => '1',
        'atv_cad_cons' => '1',
        'atv_cad_cons_bkp' => '1',
        'sessao_modo_entrega' => '2',
        //'sessao_nome_cons'            => 'JULIANNA DALMA BORGES VIANNA', // fazer corretamente
        'id_cdh_retira' => $val_cdh,
        'id_cdh_retira_desc' => $cdh_info['description'],
        //'email_ped'                   => 'julianna_dalma@hotmail.com', // fazer corretamente
        'cdh_retira_email' => $cdh_info['email'],
        'vl_total_pedido_frete' => '0.00',
        'vl_sub_total_pedido' => $valor_subtotal_pedido,
        'vl_total_pedido' => $valor_total_pedido,
        'pontos_total_pedido' => $pontos_total_pedido,
        'peso_total_pedido' => $peso_total_pedido,
        'forma_envio_transp_ped' => $vtrans,
        'forma_envio_transp_desc_ped' => $vtrans_desc,
        'prazo_transp_ped' => $vtrans_prazo,
        'vl_credito' => $vl_credito,
        'vl_credito_usado' => $valor_credito_usado,
    );

    $post_fields = http_build_query($post, null, '&');

    $url = 'https://vo.hinode.com.br/vo-2/vo3-gera-pedido-forma-pagamento.asp';

    $result = CurlHelper::curlPost($url, $post_fields, $cookieFile);

    $html = new Simple_html_dom($result['exec']);

    $find_pagamento = $html->find('input[name=pagamento]');

    if (!count($find_pagamento) > 0) {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 7';
        exit;
    }

    $pagamento = $find_pagamento[0]->attr['value'];

    $find_desc_forma_pag = $html->find('input[id=desc_forma_pag]');

    if (!count($find_desc_forma_pag) > 0) {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 8';
        exit;
    }

    $desc_forma_pag = $find_desc_forma_pag[0]->attr['value'];

    $post = array(
        'acao' => 'gera_pedido',
        'idconsultor' => HND_USER,
        'ss_pg' => $ss_pg,
        'sessao_modo_entrega' => '2',
        //'nome_cons'                   => 'JULIANNA DALMA BORGES VIANNA', // fazer corretamente
        'id_cdhret' => $val_cdh,
        'id_cdhret_desc' => $cdh_info['description'],
        'id_forma_pag' => $pagamento,
        'desc_forma_pag' => $desc_forma_pag,
        'vl_frete' => '0.00',
        'vl_ped' => $valor_total_pedido,
        'vl_sub_ped' => $valor_subtotal_pedido,
        'pontos_ped' => $pontos_total_pedido,
        'peso_ped' => $peso_total_pedido,
        'prazo_transp_ped' => $vtrans_prazo,
        'forma_envio_transp_ped' => $vtrans,
        'qtd_parc' => '1',
        'status_ped' => '1',
        'forma_envio_transp_desc_ped' => $vtrans_desc,
        //'email_ped'                   => 'julianna_dalma@hotmail.com', // fazer corretamente
        'email_cdh' => $cdh_info['email'],
        'vl_credito_usado' => $valor_credito_usado,
        'ped_juros' => '0',
        'ped_tipo' => 'CONSULTOR>HINODE',
        'qtd_parc_auto' => '0',
        'valorDesconto' => '',
        'valorExcedente' => '',
        'tipoKit' => '0',
        'vtel' => '',
        'idlogcons' => '',
        'desc_forma_pag_verf' => '2',
    );

    $post_fields = http_build_query($post, null, '&');

    $url = 'https://vo.hinode.com.br/vo-2/vo3_ajax_consultor_gera_pedido.asp';

    $result = CurlHelper::curlPost($url, $post_fields, $cookieFile);

    $retorno = explode('|', $result['exec']);

    return $retorno[0];
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

?>

<!--<form name="form-concluir-compra" id="form-concluir-compra" method="post" action="vo3-gera-pedido-forma-pagamento.asp">
    <input type="hidden" name="ped_desconto" id="ped_desconto" value="0.00">
    <input type="hidden" name="pontuacao_minima" id="pontuacao_minima" value="0.00">
    <input type="hidden" name="valor_pedido_minimo" id="valor_pedido_minimo" value="0.00">
    <input type="hidden" name="tipoKit" id="tipoKit" value="0">
    <input type="hidden" name="sessao_tipo_compra" id="sessao_tipo_compra" value="1">
    <input type="hidden" name="ss_pg" id="ss_pg" value="29793890718395338">
    <input type="hidden" value="00817068" name="sessao_id_cons" id="sessao_id_cons">
    <input type="hidden" name="atv_cons" id="atv_cons" value="1">
    <input type="hidden" name="atv_cons_bkp" id="atv_cons_bkp" value="1">
    <input type="hidden" name="atv_cad_cons" id="atv_cad_cons" value="1">
    <input type="hidden" name="atv_cad_cons_bkp" id="atv_cad_cons_bkp" value="1">
    <input type="hidden" name="sessao_modo_entrega" id="sessao_modo_entrega" value="2">
    <input type="hidden" name="sessao_nome_cons" id="sessao_nome_cons" value="JULIANNA  DALMA BORGES VIANNA">
    <input type="hidden" name="id_cdh_retira" id="id_cdh_retira" value="10360072">
    <input type="hidden" name="id_cdh_retira_desc" id="id_cdh_retira_desc" value="VILA VELHA ">
    <input type="hidden" name="email_ped" id="email_ped" value="julianna_dalma@hotmail.com">
    <input type="hidden" name="cdh_retira_email" id="cdh_retira_email" value="vilavelha@hinodefranquia.com.br">
    <input type="hidden" name="sessao_cep" id="sessao_cep" value="">
    <input type="hidden" name="sessao_endereco" id="sessao_endereco" value="">
    <input type="hidden" name="sessao_numero" id="sessao_numero" value="">
    <input type="hidden" name="sessao_complemento" id="sessao_complemento" value="">
    <input type="hidden" name="sessao_bairro" id="sessao_bairro" value="">
    <input type="hidden" name="sessao_cidade" id="sessao_cidade" value="">
    <input type="hidden" name="sessao_estado" id="sessao_estado" value="">
    <input type="hidden" name="vl_total_pedido_frete" id="vl_total_pedido_frete" value="0.00">
    <input type="hidden" name="vl_sub_total_pedido" id="vl_sub_total_pedido" value="60.00">
    <input type="hidden" name="vl_total_pedido" id="vl_total_pedido" value="60.00">
    <input type="hidden" name="pontos_total_pedido" id="pontos_total_pedido" value="40.00">
    <input type="hidden" name="peso_total_pedido" id="peso_total_pedido" value="0.350">
    <input type="hidden" name="forma_envio_transp_ped" id="forma_envio_transp_ped" value="235">
    <input type="hidden" name="forma_envio_transp_desc_ped" id="forma_envio_transp_desc_ped"
           value="RETIRAR CDH - Vila Velha  (grátis)">
    <input type="hidden" name="prazo_transp_ped" id="prazo_transp_ped" value="1">
    <input type="hidden" name="vl_credito" id="vl_credito" value="0.00">
    <input type="hidden" name="vl_credito_usado" id="vl_credito_usado" value="0.00">
    <input type="hidden" name="txt_tit_prod" id="txt_tit_prod" value="">
    <input type="hidden" name="txt_desc_prod" id="txt_desc_prod" value="">
    <input type="hidden" name="qtd_minima_kitxcol" id="qtd_minima_kitxcol" value="0">
    <input type="hidden" name="qtd_total_item" id="qtd_total_item" value="0">
    <input type="hidden" name="qtd_parc_auto" id="qtd_parc_auto" value="0">
    <input type="hidden" name="tel" id="tel" value="">
    <input type="hidden" name="statusATV" id="statusATV" value="4">
</form>

array(1) {
[0]=> array(32) {
    ["CdCarrinho"]=> int(134514332)
    ["Car_session"]=> string(17) "32576266025474938"
    ["Car_idConsultor"]=> string(8) "00817068"
    ["Car_idProduto"]=> int(1270)
    ["Car_prod_codigo"]=> string(6) "002328"
    ["Car_prod_nome"]=> string(43) "Traduções Gold nº 28 Masculino 100 ml"
    ["Car_prod_valor_unt"]=> int(60)
    ["Car_prod_valor_desc"]=> NULL
    ["Car_prod_qtd"]=> int(1)
    ["Car_prod_peso"]=> float(0.35)
    ["Car_prod_pontuacao"]=> int(40)
    ["Car_prod_qtd_encomendar"]=> int(0)
    ["Car_DtCriacao"]=> string(19) "13/06/2016 16:23:51"
    ["Car_DtAlteracao"]=> NULL
    ["Car_id_cdh_retira"]=> string(8) "10360072"
    ["Car_prod_atv_consultor"]=> int(0)
    ["Car_qtd_minima_kitxcol"]=> int(0)
    ["Car_prod_ponto_minimo"]=> int(0)
    ["Car_prod_desconto"]=> NULL
    ["Car_prod_valor_unt_cat"]=> int(120)
    ["Car_prod_valor_unt_con"]=> int(60)
    ["Car_class"]=> int(0)
    ["Car_tipo_ped"]=> string(16) "CONSULTOR_HINODE"
    ["Car_obs"]=> NULL
    ["Car_prod_valor_minimo"]=> int(0)
    ["Car_prod_voucher"]=> string(10) "0 "
    ["pedidoOficial"]=> NULL
    ["prodQtd"]=> NULL
    ["idLinha"]=> string(2) "27"
    ["idUso"]=> string(2) "11"
    ["kit"]=> int(0)
    ["tipoKit"]=> NULL } }

var meusItens = '';
var valor_total_item = 0;
var pontos_total_item = 0;
var peso_total_item = 0;
var valor_total_pedido_calc = 0;
var valor_total_pedido = 0;
var pontos_total_pedido = 0;
var peso_total_pedido = 0;
var encomendar_item = '';
var vatv_cons = $("#atv_cons").val();
var vatv_cad_cons = $("#atv_cad_cons").val();
var vqtd_total_item = 0;
var prodAtual="";
var prodUltimo = "";
var cpd = 0;

if (parseInt(retorno.length, 10) > 0) {
    var valorDesconto = 0;
    var valor_subtotal_pedido = 0;
    var totalMomento = 0;
    var tipoKit = 0;
    var arrayProdutos = [];
    var ativou = false;

    $.each(retorno, function (i, item) {
        if (item.kit == 1) {
            tipoKit = item.tipoKit;
            $("input[name='valor_pedido_minimo']").val(item.Car_prod_valor_minimo);

            if (tipoKit == 2) {
                listarColonias();
            } else {
                $("#list_prod").html("");
            }
        }
        $("#tipoKit").val(tipoKit);

        if (!$.isNumeric(parseInt(arrayProdutos[item.Car_prod_codigo], 10))) {
            arrayProdutos[item.Car_prod_codigo] = 1;
            var idProduto = item.Car_prod_codigo;
            var qtdProduto = arrayProdutos[item.Car_prod_codigo];
            var cdCarrinho = item.CdCarrinho;

            $.each(retorno, function (i, item) {
                if (item.Car_prod_codigo == idProduto && item.CdCarrinho != cdCarrinho) {
                    qtdProduto += 1;

                    if (parseInt(vatv_cad_cons) == 1 && parseInt(vatv_cons) == 0) {
                        if (parseInt(item.Car_prod_atv_consultor, 10) > 0) {
                            ativou = true;
                        }
                    }
                }
            });

            vqtd_total_item = qtdProduto;
            valor_subtotal_pedido += eval(vqtd_total_item * item.Car_prod_valor_unt);
            valor_total_item = eval(vqtd_total_item * item.Car_prod_valor_unt - item.Car_prod_desconto);
            valor_total_pedido += valor_total_item;
            pontos_total_item = eval(vqtd_total_item * item.Car_prod_pontuacao);
            pontos_total_pedido += pontos_total_item;

            if (item.Car_prod_peso != '') {
                peso_total_item = eval(qtdProduto * item.Car_prod_peso);
                peso_total_pedido += peso_total_item;
            }
            valorDesconto += item.Car_prod_desconto;

            if (parseInt(item.Car_prod_ponto_minimo, 10) > 0) {
                $("#pontuacao_minima").val(number_format(item.Car_prod_ponto_minimo, 2, '.', ''));
            }

            if (parseInt(item.Car_prod_atv_consultor, 10) > 0 || ativou) {
                $("#atv_cons").val('1');
                $("#atv_cad_cons").val('1');
                $('#msg_atv').html("");
            }

            if (parseInt(item.Car_prod_codigo, 10) == parseInt($("#txt_prod").val(), 10) && parseInt(qtdProduto, 10) < parseInt($("#txt_qtd").val(), 10)) {
                bootbox.alert("ATENÇÃO: QUANTIDADE SOLICITADA INDISPONÍVEL, QUANTIDADE DISPONÍVEL " + qtdProduto, function () { });
                $("#txt_prod").val("");
                $("#txt_qtd").val("");
            }

            vnome_prod = item.Car_prod_nome.replace(new RegExp("\\n", "g"), "").replace(new RegExp("\\<br>", "g"), " ");
            var bg_tr = "";
            if (parseInt(item.Car_prod_atv_consultor, 10) == 1 || ativou) {
                ativou = false;
            }

            prodAtual = item.Car_prod_codigo;
            if (prodUltimo == "") {
                prodUltimo = prodAtual;
            }

            if (prodAtual != prodUltimo) {
                prodUltimo = prodAtual;
                cpd++;
            }

            if (cpd % 2 == 1) {
                bg_tr = "";
            } else {
                bg_tr = "#FFC";
            }

            meusItens += '<tr>';
            meusItens += '	<td style="background-color:' + bg_tr + '">' + item.Car_prod_codigo + '</td>';
            meusItens += '	<td style="background-color:' + bg_tr + '">' + vnome_prod + '</td>';
            meusItens += '	<td style="background-color:' + bg_tr + '">';
            meusItens += '<div class="ace-spinner" style="width: 70px;">';
            meusItens += '	<div class="input-group">';
            meusItens += '		<input type="text" name="qtd-produto" value="' + qtdProduto + '" id="" class="input-mini spinner1 spinner-input form-control" maxlength="3" disabled="true">';
            meusItens += '		<div class="spinner-buttons input-group-btn btn-group-vertical">';
            meusItens += '			<button class="btn spinner-up btn-xs btn-info" type="button" onClick="Fc_additemcar(\'' + item.Car_prod_codigo + '\',1);">';
            meusItens += '			    <i class="icon-chevron-up"></i>	';
            meusItens += '			</button>';
            meusItens += '			<button class="btn spinner-down btn-xs btn-info" type="button" onClick="Fc_delitemcar(\'' + item.Car_prod_codigo + '\',1,' + item.kit + ');">';
            meusItens += '			    <i class="icon-chevron-down"></i>';
            meusItens += '			</button>';
            meusItens += '		</div>';
            meusItens += '	</div>';
            meusItens += '</div>';
            meusItens += '	</td>';
            meusItens += '	<td style="background-color:' + bg_tr + '">R$ ' + number_format(item.Car_prod_valor_unt, 2, ',', '.') + '</td>';
            meusItens += '	<td style="background-color:' + bg_tr + '">R$ ' + number_format(item.Car_prod_desconto, 2, ',', '.') + '</td>';
            meusItens += '	<td style="background-color:' + bg_tr + '">R$ ' + number_format(valor_total_item, 2, ',', '.') + '</td>';
            meusItens += '	<td style="background-color:' + bg_tr + '">' + number_format(item.Car_prod_pontuacao * qtdProduto, 2, ',', '.') + '</td>';
            meusItens += '	<td style="background-color:' + bg_tr + '">';
            meusItens += '		<div class="visible-md visible-lg hidden-sm hidden-xs btn-group">';
            meusItens += '			<button class="btn btn-xs btn-danger" onClick="Fc_delitemcar(\'' + item.Car_prod_codigo + '\',' + qtdProduto + ',' + item.kit + ');">';
            meusItens += '				<i class="icon-trash bigger-120"></i>';
            meusItens += '			</button>';
            meusItens += '		</div>';
            meusItens += '	</td>';
            meusItens += '</tr>';
        }
    });

    var vl_credito = $("#vl_credito").val() == "" ? "0.00" : $("#vl_credito").val();
    var valor_total_pedido_cred = 0.00;
    $("#qtd_total_item").val(vqtd_total_item - 1);
    $('#carrinho-container').show();
    $('#carrinho_lista').html(meusItens);
    $("#ped_desconto").val(number_format(valorDesconto, 2, '.', ''));

    if (vl_credito >= valor_total_pedido) {
        valor_total_pedido_cred = 0.00;
    } else {
        valor_total_pedido_cred = valor_total_pedido - eval(number_format(vl_credito, 2, '.', ''));
    }

    $('#txt_vl_total_pedido').html('R$ ' + number_format(valor_total_pedido_cred, 2, ',', '.'));
    $('#vl_total_pedido').val(number_format(valor_total_pedido_cred, 2, '.', ''));
    $('#vl_sub_total_pedido').val(number_format(valor_subtotal_pedido, 2, '.', ''));
    $('#txt_pontos_total_pedido').html('' + number_format(pontos_total_pedido, 2, ',', '.'));
    $('#txt_desconto_pedido').html('R$ ' + number_format(valorDesconto, 2, ',', '.'));
    $('#txt_vl_subtotal_pedido').html('R$ ' + number_format(valor_subtotal_pedido, 2, ',', '.'));
    $('#pontos_total_pedido').val(number_format(pontos_total_pedido, 2, '.', ''));
    $('#peso_total_pedido').val(number_format(peso_total_pedido, 3));


-->