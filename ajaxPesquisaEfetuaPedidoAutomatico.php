<?php

ini_set('max_execution_time', 100000);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("ajaxIncludes.php");

//var_dump($_GET);
//exit;

if ($_GET['pesquisa_opcao'] <> 'pesquisa_automatica') {
    exit;
}

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

    if ($find_login[0]->attr['href'] == 'vo-inicio.asp') {

        //

        $db = new MySqlPDO();

        $idconsultor = HND_USER;

        $get_pedidos = '';

        foreach (explode(',', $_GET['cod_franquias']) as $franquia) {
            if (!empty($franquia)) {
                foreach ($_GET as $ind => $val) {
                    if (substr($ind, 0, 13) == 'pes_auto_qtd_') {
                        $get_pedidos .= substr($ind, 13, strlen($ind)) . '|' . $franquia . '|' . $val . ',';
                    }
                }
            }
        }

        $pedidos = explode(',', $get_pedidos);

        $pedidos_efetuados = array();

        $retorno_pedidos = array();

        $cdh_data = array();

        foreach ($pedidos as $pedido) {

            if (!empty($pedido)) {

                $pedidos_valores = explode('|', $pedido);

                $cdh = $pedidos_valores[1];

                $cdh_data[$cdh][] = array(
                    'pro_code' => $pedidos_valores[0],
                    'pro_qtd' => $pedidos_valores[2]
                );
            }
        }

        if (count($cdh_data) > 0) {

            foreach ($cdh_data as $cdh => $produtos) {

                $initDados = getInitDados($cookieFile);

                foreach ($produtos as $produto => $produto_valores) {

                    $i = 1;

                    $reservado = true;
                    $quantidade_atingida = false;

                    while ($i <= $produto_valores['pro_qtd'] && $reservado && !$quantidade_atingida) {

                        //echo $i . ' <= ' . $produto_valores['pro_qtd'] . ' && reservado == ' . $reservado . ' && quantidade_atingida == ' . $quantidade_atingida . '<br>';

                        $j = 0;

                        foreach ($pedidos_efetuados as $cdh => $pe_produtos) {
                            foreach ($pe_produtos as $pe_produto) {
                                if ($produto_valores['pro_code'] == $pe_produto['pro_code']) {
                                    $j += $pe_produto['pro_qtd'];
                                }
                            }
                        }

                        //echo $j . ' == ' . $produto_valores['pro_qtd'] . '<br>';

                        if ($j == $produto_valores['pro_qtd']) {
                            $quantidade_atingida = true;
                        } else {
                            $post = array(
                                'acao' => 'car_add_item',
                                'idconsultor' => $idconsultor,
                                'id_cdhret' => $cdh,
                                'loc_prod' => $produto_valores['pro_code'],
                                'qtd_prod' => '1',
                                'ss_pg' => $initDados['ss_pg'],
                                'regra_estoque' => '0',
                                'atv_cons' => $initDados['atv_cons'],
                                'atv_cad_cons' => $initDados['atv_cad_cons'],
                                'vl_sub_ped' => '0.00',
                                'valor_minimo_kit' => '0.00',
                                'ponto_minimo_kit' => '0.00',
                                'atv_cons_bkp' => $initDados['atv_cons_bkp'],
                                'atv_cad_cons_bkp' => $initDados['atv_cad_cons_bkp'],
                            );

                            $result = executaAcaoProdutos($post, $cookieFile, true);

                            if ($result['exec'] == '') {
                                $i++;
                            } else {
                                $reservado = false;
                            }
                        }
                    }

                    if ($i > 1) {
                        $pedidos_efetuados[$cdh][] = array(
                            'pro_code' => $produto_valores['pro_code'],
                            'pro_qtd' => ($i - 1)
                        );
                    }
                }

                if (isset($pedidos_efetuados[$cdh]) && count($pedidos_efetuados[$cdh]) > 0) {

                    $post = array(
                        'acao' => 'validar-carrinho',
                        'ss_pg' => $initDados['ss_pg'],
                    );

                    $result = executaAcaoProdutos($post, $cookieFile, true);

                    $retorno = $result['exec'];

                    if (strstr($retorno, '|')) {

                        $exp = explode('|', $retorno);

                        if ($exp[0] === '0') {

                            $pedido = efetuaPedido($cookieFile, $cdh, $initDados, $db);

                            //$pedido = '123|60.45';

                            if (!$pedido) {
                                $retorno_pedidos[$cdh] = false;
                                //$pedidos_efetuados[$cdh] = array();
                            } else {

                                $retorno_pedidos[$cdh]['numero_pedido'] = explode('|', $pedido)[0];
                                $retorno_pedidos[$cdh]['valor_total_pedido'] = explode('|', $pedido)[1];
                            }
                        }
                    } else {
                        var_dump($retorno['exec']);
                        exit;
                    }
                }
            }
        }

        echo json_encode($pedidos_efetuados, JSON_UNESCAPED_UNICODE);

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

function efetuaPedido($cookieFile, $val_cdh, $initDados, MySqlPDO $db)
{
    $post = array(
        'acao' => 'car_lista_item',
        'idconsultor' => HND_USER,
        'ss_pg' => $initDados['ss_pg'],
        'id_cdhret' => $val_cdh,
        'atv_cad_cons' => $initDados['atv_cad_cons'],
        'atv_cons' => $initDados['atv_cons'] // caso nao esteja ativo deve ser 0 / caso ativo deve ser 1
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
    $vl_credito = $initDados['vl_credito'];

    if (count($retorno) > 0) {

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

            //echo 'qtd prod: ' . $qtdProduto . ' - Pontuacao prod: ' . $val['Car_prod_pontuacao'] . '<br>';

            $vqtd_total_item = $qtdProduto;
            $valor_subtotal_pedido += $vqtd_total_item * $val['Car_prod_valor_unt'];
            $valor_total_item = $vqtd_total_item * ($val['Car_prod_valor_unt'] - $val['Car_prod_desconto']);
            $valor_total_pedido += $valor_total_item;

            $pontos_total_item = $vqtd_total_item * $val['Car_prod_pontuacao'];
            //echo 'pontos total item: ' . $pontos_total_item . '<br>';

            $pontos_total_pedido += $pontos_total_item;
            //echo 'pontos_total_pedido: ' . $pontos_total_pedido . '<br>';

            if ($val['Car_prod_peso'] != '') {
                $peso_total_item = $qtdProduto * $val['Car_prod_peso'];
                $peso_total_pedido += $peso_total_item;
            }

            $valorDesconto += $val['Car_prod_desconto'];
        }
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
        'ss_pg' => $initDados['ss_pg'],
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
        'ss_pg' => $initDados['ss_pg'],
        HND_USER => 'sessao_id_cons',
        'atv_cons' => $initDados['atv_cons'],
        'atv_cons_bkp' => $initDados['atv_cons_bkp'],
        'atv_cad_cons' => $initDados['atv_cad_cons'],
        'atv_cad_cons_bkp' => $initDados['atv_cons_bkp'],
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
        'ss_pg' => $initDados['ss_pg'],
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
        //'qtd_parcela_card'            => '1',
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
        'idlogcons' => HND_USER,
        'desc_forma_pag_verf' => '2',
    );

    $post_fields = http_build_query($post, null, '&');

    $url = 'https://vo.hinode.com.br/vo-2/vo3_ajax_consultor_gera_pedido.asp';

    $result = CurlHelper::curlPost($url, $post_fields, $cookieFile);

    //var_dump($pontos_total_pedido);
    //var_dump($result['exec']);

    $retorno = explode('|', $result['exec']);

    if ($retorno[0] == '0') {
        return false;
    }

    return $retorno[0] . '|' . $valor_total_pedido;
}

function getInitDados($cookieFile)
{
    $url = 'https://vo.hinode.com.br/vo-2/vo3-gera-pedido.asp';

    $result = CurlHelper::curl($url, false, false, $cookieFile);

    $html = new Simple_html_dom($result['exec']);

    //

    $find_ss_pg = $html->find('input[id=ss_pg]');

    if (!count($find_ss_pg) > 0) {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 3';
        exit;
    }

    $ss_pg = $find_ss_pg[0]->attr['value'];

    //

    $find_vl_credito = $html->find('input[id=vl_credito]');

    if (!count($find_vl_credito) > 0) {
        echo 'Houve um erro ao acessar o sistema da hinode. Erro 5';
        exit;
    }

    $vl_credito = $find_vl_credito[0]->attr['value'];

    //

    $post = array(
        'acao' => 'ver_atv_consultor',
        'idconsultor' => HND_USER
    );

    $ativacaoCheck = executaAcaoProdutos($post, $cookieFile, true);

    $rs_cons = explode('|', $ativacaoCheck['exec']);

    if ($rs_cons[0] == '4') {
        $atv_cad_cons = '1';
        $atv_cad_cons_bkp = '1';
        $atv_cons = '1';
        $atv_cons_bkp = '1';
    } elseif ($rs_cons[0] == '3') {
        $atv_cad_cons = '1';
        $atv_cad_cons_bkp = '1';
        $atv_cons = '0';
        $atv_cons_bkp = '0';
    } else {
        $atv_cad_cons = '0';
        $atv_cad_cons_bkp = '0';
        $atv_cons = '0';
        $atv_cons_bkp = '0';
    }

    return array(
        'ss_pg' => $ss_pg,
        'vl_credito' => $vl_credito,
        'atv_cad_cons' => $atv_cad_cons,
        'atv_cad_cons_bkp' => $atv_cad_cons_bkp,
        'atv_cons' => $atv_cons,
        'atv_cons_bkp' => $atv_cons_bkp
    );
}