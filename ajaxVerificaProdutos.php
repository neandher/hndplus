<?php

ini_set('max_execution_time', 100000);

error_reporting(E_ALL);
ini_set('display_errors', 1);

require("config.php");
require("Helpers/CurlHelper.php");
require("Helpers/EmailHelper.php");
require("Helpers/phpmailer/PHPMailer.php");
require('Helpers/Simple_html_dom.php');

$cookieFile = TEMP_PATH . session_id() . '-' . $_GET['number'] . '.txt';

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

if (strstr($result['exec'], '<a HREF="index.asp">here</a>')) {

    $url = 'https://vo.hinode.com.br/vo-2/vo3-gera-pedido.asp';

    $result = CurlHelper::curl($url, false, false, $cookieFile);

    $html = new Simple_html_dom($result['exec']);

    $ss_pg = $html->find('input[id=ss_pg]')[0]->attr['value'];

    $cdh = '10360072';

    $idconsultor = HND_USER;

    $searchProdutos = array(

        '010100' => 'ETERNA HINODE',
        '010102' => 'EMPIRE HINODE',
        '010103' => 'ETERNA BLUE HINODE',
        '010104' => 'EMPIRE INTENSE HINODE',

        '010108' => 'GRAND NOIR HINODE',
        '010106' => 'GRAND HINODE',

        '010105' => 'GRACE HINODE',
        '010107' => 'GRACE MIDNIGHT HINODE',

        '002301'  => '01 AZZARO',
        '002302'  => '02 KOUROS FRAICHEUR',
        '002303'  => '03 POLO',
        '002304'  => '04 DOLCE & GABANNA MASCULINO',
        '002305'  => '05 CHANEL',
        '002306'  => '06 LE MALE',
        '002307'  => '07 POLO BLACK',
        '002308'  => '08 DOLCE & GABANNA FEMININO',
        '002309'  => '09 GABRIELA SABATINI',
        '002310'  => '10 ANGEL',
        '002312'  => '12 CAROLINA HERRERA',
        '002313'  => '13 FANTASY',
        '002314'  => '14 LADY MILLION',
        '002316'  => '16 BOMBSHELL',
        '002317'  => '17 ABERCROMBIE FIERCE',
        '002318'  => '18 CH 212',
        '002319'  => '19 1 MILION',
        '002320'  => '20 FLOWER BY KENZO',
        '002321'  => '21 HYPNOSE',
        '002322'  => '22 VERY IRRESISTIBLE',
        '002323'  => '23 JPG CLASSIQUE',
        '002324'  => '24 J ADORE',
        '002326'  => '26 ANGE OU DEMON',
        '002328'  => '28 FERRAI BLACK',
        '002329'  => '29 POLO BLUE',
        '002330'  => '30 DIESEL FUEL FOR LIFE',
        '002331'  => '31 LAPIDUS',
        '002332'  => '32 ANIMALE',
        '002335'  => '35 LEAU DISSEY',
        '002337'  => '37 TRESOR',
        '002342'  => '42 COOL WATER',
        '002343'  => '43 JOOP! HOME',
        '002345'  => '45 FAHRENHEIT',
        '002346'  => '46 212 SEXY',
        '002347'  => '47 AZZARO SILVER BLACK',
        '002351'  => '51 EUPHORIA',
        '002353'  => '53 BLACK XS',
        '002355'  => '55 CH RED CAROLINA HERRERA',
        '002356'  => '56 LADY GAGA',
        '002357'  => '57 FAHRENHEIT SUMMER',
        '002358'  => '58 212 SEXY MEN',
        '002359'  => '59 ETERNITY MEN',
        '002360'  => '60 FERRARI RED',
        '002361'  => '61 HUGO BOSS',
        '002362'  => '62 212 VIP MEN',
        '002363'  => '63 212 VIP FEMININO',
        '002364'  => '64 D&G LIGHT BLUE',

        //kit top

        '007253'  => 'KIT TOP',

        //hidratante

        //'000580'  => '580 HINODE SENSA��ES HIDRAT. DES. CORP. TERNURA - 300ml',
        //'000581'  => '581 HINODE SENSA��ES HIDRAT. DES. CORP. SUBLIME - 300ml',
        //'000582'  => '582 HINODE SENSA��ES HIDRAT. DES. CORP. SEDU��O - 300ml',
        //'000583'  => '583 HINODE SENSA��ES HIDRAT. DES. CORP. DREAM VANILLA',
        //'000584'  => '584 HINODE SENSA��ES HIDRAT. DES. CORP. PO�SIE - 300ml',
        //'000585'  => '585 HINODE SENSA��ES HIDRAT. DES. CORP. ENVOLVENTE - 300ml',
        //'000586'  => '586 HINODE SENSA��ES HIDRAT. DES. CORP. �CLAT - 300ml',

        //'000870'  => 'FRASQUEIRA HINODE	000870',
        //'000655'  => 'KIT AMOSTRAS PERFUMES TRADU��ES GOLD	000655',
        //'000309'  => 'Corps Lignea Gel Massageador Refrescante 500g	',

        //'030004' => 'DIFUSOR',
    );

    $searchProdutos = array(

        //'000870'  => 'FRASQUEIRA HINODE	000870',
        //'000655'  => 'KIT AMOSTRAS PERFUMES TRADU��ES GOLD	000655',
        //'000309'  => 'Corps Lignea Gel Massageador Refrescante 500g	',
        //'000273'  => 'Joli Cupua�u - �leo em Creme Desodorante Corporal - 140g - 000273',
        //'000291'  => 'Toques Suits Creme para Massagem 220g',
        //'002310'  => '10',
        //'002308'  => '08',

        //'000312'  => 'Wonderful Gold �leo para as Pernas 140ml',

        //'002324'  => '24 J ADORE',
        //'002310'  => '10 ANGEL',
        //'002329'  => '29 POLO BLUE',
        //'010102' => 'EMPIRE HINODE',
        //'010104' => 'EMPIRE INTENSE HINODE',

        //'000277'  => 'Joli �leo Perfumado Desodorante Corporal Cupua�u 140ml',

        '16016'  => 'LINEA OCCHI L�pis para os olhos',
        '321'  => 'Esfoliante',
        '16005'  => '16005',
        '16018'  => '16018',

        '000273' => '000273', // praia da costa
        '002362' => '002362',
        '016003' => '016003', // nao tem
        '016070' => '016070', // nao tem
        '016087' => '016087', // tem
        '045001' => '045001', // tem
        '045014' => '045014', // tem
        '045011' => '045011',

        //'16000' => '16000',
        //'016088' => '016088'
    );

    $searchCDH = array(
        '10360072' => 'Vila Velha - Praia da Costa',
        '10650784' => 'Vitoria - Praia do Canto',
        '10153011' => 'Vitoria - Santa Lucia',
        '10615393' => 'CARIACICA - JARDIM AMERICA',
        '10438342' => 'SERRA - PQ RESIDENCIAL LARANJEIRAS',
        '10930066' => 'GUARAPARI',

        '10488217' => 'Cachoeiro de Itapemirim - CENTRO',
        '10774033' => 'COLATINA - ESPLANADA',
        '10843260' => 'LINHARES - CENTRO',
        '10790939' => 'S�O MATEUS - SERNAMBY',

        //'10164744' => ' Franquia - RJ - RIO DE JANEIRO - BANGU',
        //'11430407' => ' RIO DAS OSTRAS | riodasostras@hinodefranquia.com.br'
    );

    // *************** Verifica/Adiciona Produto ****************** //

    $success = false;

    foreach ($searchCDH as $ind_cdh => $val_cdh) {
        foreach ($searchProdutos as $ind_prod => $val_prod) {

            $post = array(
                'acao'             => 'car_add_item',
                'idconsultor'      => $idconsultor,
                'id_cdhret'        => $ind_cdh,
                'loc_prod'         => $ind_prod,
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
                enviaEmail($val_prod, $val_cdh);
                $success = true;
            }
        }
    }

    if ($success) {
        echo 'success';
    }

    // **************** Lista Carrinho *******************

//    $post = array(
//        'acao'         => 'car_lista_item',
//        'id_cdhret'    => $cdh,
//        'ss_pg'        => $ss_pg,
//        'atv_cons'     => '0',
//        'atv_cad_cons' => '1'
//    );
//
//    $result = executaAcaoProdutos($post, $cookieFile, true);
//
//    $objectCar = json_decode($result['exec']);

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

function enviaEmail($prod, $cdh)
{
    //log

    $logFile = LOG_PATH . date('d') . '_' . date('H') . '.txt';

    $log = $prod . ' disponivel no CDH ' . $cdh . ': ' . date('d/m/Y H:i') . PHP_EOL;

    file_put_contents($logFile, $log, FILE_APPEND);

    //Email

    $enviaEmail = new EmailHelper();

    $enviaEmail->setEmail('neandher89@gmail.com');

    $enviaEmail->setAssunto($prod . " disponivel");

    $msg = '
    <br>
    <b>Produto ' . $prod . ' disponivel no CDH ' . $cdh . ': </b> ' . date('d/m/Y H:i') . '<br><br>
    <br><br>
    ';

    $enviaEmail->setMensagem($msg);
    //$check = $enviaEmail->enviaEmail();
}