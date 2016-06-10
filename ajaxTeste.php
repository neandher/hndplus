<?php

ini_set('max_execution_time', 100000);

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('ajaxIncludes.php');

$cookieFile = TEMP_PATH . session_id() . '.txt';

$url = 'https://vo.hinode.com.br/vo-2/rede_login.asp';

$result = CurlHelper::curl($url, false, false, $cookieFile);

$html = new Simple_html_dom($result['exec']);

$captcha = $html->find('div[id=divQuadro]');

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

    $ss_pg = $html->find('input[id=ss_pg]')[0]->attr['value'];

    $idconsultor = HND_USER;

    //todosProdutos($cookieFile);
    //todasFranquias($cookieFile);
    todosPedidos($cookieFile);
    exit;

    // lista todas as franquias
    /*$post = array(
        'acao'   => 'obter_cdh',
        'estado' => '',
    );*/

    // Lista categorias ( USO )
    /*$post = array(
        'acao'  => 'lista_cat_subcat',
        'idcat' => '0',
    );*/

    // Lista produtos de uma subcategoria
    $post = array(
        'acao'  => 'lista_prod_subcat',
        'idcat' => '27',
    );

    $result = executaAcaoProdutos($post, $cookieFile, true);

    var_dump(json_decode($result['exec'], true));
}

unlink($cookieFile);

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

function executaAcaoPedidos($post, $cookieFile, $isPost = false)
{
    $url = 'https://vo.hinode.com.br/ajax/vo3_ajax_consultor_gera_pedido_historico.asp';

    $post_fields = http_build_query($post, null, '&');

    return $isPost ? CurlHelper::curlPost($url, $post_fields, $cookieFile) : CurlHelper::curl(
        $url,
        false,
        false,
        $cookieFile
    );
}

function todosProdutos($cookieFile)
{
    $db = new MySqlPDO();

    $post = array(
        'acao'  => 'lista_cat_subcat',
        'idcat' => '0',
    );

    $result = executaAcaoProdutos($post, $cookieFile, true);

    $categorias = json_decode($result['exec'], true);

    $catCount = 0;

    $select = new SelectSqlHelper();

    $checkSub = array();
    $checkPro = array();

    foreach ($categorias as $cat) {

        if (!empty($cat['Uso'])) {

            $insertCat['code'] = $cat['idUso'];
            $insertCat['name'] = $cat['Uso'];
            $insertCat['sequence'] = $cat['ordem'];

            //insert($insertCat, $db, 'hnd_categoria');

            $postSubCat = array(
                'acao'  => 'lista_cat_subcat',
                'idcat' => $cat['idUso'],
            );

            $result = executaAcaoProdutos($postSubCat, $cookieFile, true);

            $subCategorias = json_decode($result['exec'], true);

            $subCatCount = 0;

            foreach ($subCategorias as $sub) {

                if (!empty($sub)) {

                    if (!empty($sub['Linha'])) {

                        if (!in_array($sub['Linha'], $checkSub)) {

                            $checkSub[] = $sub['Linha'];

                            $insertSub['code'] = $sub['idLinha'];
                            $insertSub['name'] = $sub['Linha'];

                            //insert($insertSub, $db, 'hnd_subcategoria');
                        }

                        $insertSubCat['sca_id'] = $sub['idLinha'];
                        $insertSubCat['cat_id'] = $cat['idUso'];

                        //insert($insertSubCat, $db, 'hnd_sca_cat');

                        $postProd = array(
                            'acao'  => 'lista_prod_subcat',
                            'idcat' => $sub['idLinha'],
                        );

                        $result = executaAcaoProdutos($postProd, $cookieFile, true);

                        $produtos = json_decode($result['exec'], true);
                        //$produtos = array();

                        foreach ($produtos as $pro) {

                            if (!empty($pro)) {

                                if (!empty($pro['Nome'])) {

                                    if (!in_array($pro['Codigo'], $checkPro)) {

                                        $checkPro[] = $pro['Codigo'];

                                        $insert['code'] = $pro['Codigo'];
                                        $insert['name'] = $pro['Nome'];
                                        $insert['description'] = $pro['Descricao'];
                                        $insert['sca_id'] = $sub['idLinha'];

                                        insert($insert, $db, 'hnd_produto');
                                    }
                                }
                            }
                        }

                        $subCatCount++;
                    }
                }
            }

            $catCount++;
        }
    }
}

function todasFranquias($cookieFile)
{
    $post = array(
        'acao'   => 'obter_cdh',
        'estado' => '',
    );

    $result = executaAcaoProdutos($post, $cookieFile, true);

    $franquias = json_decode($result['exec'], true);

    //var_dump($franquias);

    $db = new MySqlPDO();

    foreach ($franquias as $franquia) {

        $insert['code'] = $franquia['destinatario'];
        $insert['description'] = utf8_decode($franquia['descricao']);
        $insert['state'] = $franquia['estado'];
        $insert['email'] = $franquia['email'];
        $insert['district'] = utf8_decode($franquia['bairro']);
        $insert['city'] = utf8_decode($franquia['cidade']);

        //insert($insert, $db, 'hnd_franquia');
    }
}

function todosPedidos($cookieFile)
{
    $post = array(
        'acao'   => 'lista_pedido_consulta',
        'idconsultor' => HND_USER,
    );

    $result = executaAcaoPedidos($post, $cookieFile, true);

    $pedidos = json_decode($result['exec'], true);

    var_dump($pedidos);
}

function insert(Array $dados, $db, $tabela)
{

    $campos = implode(", ", array_keys($dados));

    foreach ($dados as $ind => $val) {
        $expVal[] = "?";
    }

    $expVal = implode(", ", $expVal);

    $con = " INSERT INTO {$tabela} ({$campos}) VALUES ({$expVal}) ";

    $valores = array_values($dados);

    $db->prepareExecute($con, $valores);
}

/*

Campos Categoria

'idUso' / 'Uso' / 'ordem'

Campos SubCategorias

'idLinha' / 'Linha'

Campos Produtos

'Codigo' / 'Nome' / 'Descricao'

*/


