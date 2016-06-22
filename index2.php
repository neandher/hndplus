<?php require("config.php"); ?>

<?php include('cabecalho.phtml') ?>
<?php include('navbar.phtml') ?>

    <style type="text/css" scoped>
        .progress {
            margin-bottom: 0;
        }
    </style>

    <div class="container">

    <form id="formSearch" class="form-horizontal" onsubmit="return false">

        <div class="panel panel-default">

            <div class="panel-heading">
                <h4 class="pull-left"><i class="glyphicon glyphicon-list-alt"></i> Pesquisar Produtos Dispon&iacute;veis
                    para Pedido</h4>

                <div class="pull-right">
                    <a href="#" class="btn btn-primary"
                       data-toggle="modal" data-target="#filter_modal">
                        <span class="glyphicon glyphicon-th-list"></span> Clique aqui para selecionar os produtos
                    </a>
                </div>

                <div class="clearfix"></div>
            </div>

            <div class="panel-body">


                <input type="hidden" name="pesquisa_opcao" id="pesquisa_opcao">

                <div class="form-group" id="produtos_input">
                    <label for="cod_produtos" class="col-md-1 control-label">Produtos</label>
                    <div class="col-md-10">
                        <input type="text" name="cod_produtos" id="cod_produtos" class="form-control">
                    </div>
                    <div class="col-md-1">
                        <span class="label label-danger" style="cursor:pointer;"
                              onclick="$('#cod_produtos').tagsinput('removeAll')">Limpar
                        </span>
                    </div>
                </div>
                <div class="form-group" id="franquias_input">
                    <label for="cod_franquias" class="col-md-1 control-label">Franquias</label>
                    <div class="col-md-10">
                        <input type="text" name="cod_franquias" id="cod_franquias" class="form-control">
                    </div>
                    <div class="col-md-1">
                        <span class="label label-danger" style="cursor: pointer"
                              onclick="$('#cod_franquias').tagsinput('removeAll')">Limpar
                        </span>
                    </div>
                </div>

                <div class="form-group hide" id="pedidos_input">
                    <label for="cod_pedidos" class="col-md-1 control-label">Pedido</label>
                    <div class="col-md-10">
                        <input type="text" name="cod_pedidos" id="cod_pedidos" class="form-control">
                    </div>
                </div>

                <hr>

                <div class="form-group" id="bnts_options">
                    <div class="col-md-offset-1 col-md-11">
                        <a href="javascript:void(0)" class="btn btn-primary"
                           onclick="$('#pesquisa_opcao').val('pesquisar_visualizar');startProcesso()"
                           id="btn_pesquisar_visualizar">
                            <i class="glyphicon glyphicon-search"></i> Pesquisar e Visualizar
                        </a>

                        <a href="javascript:void(0)" class="btn btn-success hide" id="btn_efetuar_pedido"
                           data-toggle="modal"
                           data-target="#confirma_pedido_modal">
                            <i class="glyphicon glyphicon-ok"></i> Efetuar Pedido
                        </a>

                        <a href="javascript:void(0)" class="btn btn-info hide" id="btn_nova_pesquisa">
                            <i class="glyphicon glyphicon-plus"></i> Nova Pesquisa
                        </a>

                        <a href="javascript:void(0)" class="btn btn-default" id="btn_pesquisa_automatica">
                            <i class="glyphicon glyphicon-dashboard"></i> Pesquisa Autom&aacute;tica
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <br>

        <div id="result"></div>

        <div id="pesquisa_automatica_opcoes"></div>

    </form>

    <div class="modal fade" tabindex="-1" role="dialog" id="filter_modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Use os filtros abaixo para selecionar os produtos e franquias que
                        deseja pesquisar</h4>
                </div>
                <div class="modal-body">

                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab_produtos" aria-controls="tab_produtos" role="tab"
                               data-toggle="tab">Produtos</a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_linha_produtos" aria-controls="tab_linha_produtos" role="tab"
                               data-toggle="tab">
                                Linha Produtos
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_franquias" aria-controls="tab_franquias" role="tab"
                               data-toggle="tab">Franquias</a>
                        </li>
                    </ul>

                    <form id="formFilter">

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="tab_produtos">

                                <br>

                                <div class="form-group">
                                    <label for="filter_prod">Digite o código ou o nome dos produtos que desejar
                                        pesquisar</label>
                                    <input type="text" class="form-control" name="filter_prod" id="filter_prod"
                                           autocomplete="off">
                                </div>

                                <div id="filter_prod_results"></div>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_linha_produtos">

                                <br>

                                <div class="form-group">
                                    <label for="filter_linha_prod">Digite a linha de produtos que desejar
                                        pesquisar</label>
                                    <input type="text" class="form-control" name="filter_linha_prod"
                                           id="filter_linha_prod" autocomplete="off">
                                </div>

                                <div id="filter_linha_prod_results"></div>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_franquias">

                                <br>

                                <div class="form-group">
                                    <label for="filter_fran">Digite o nome das franquias que desejar pesquisar</label>
                                    <input type="text" class="form-control" name="filter_fran" id="filter_fran"
                                           autocomplete="off">
                                </div>

                                <div id="filter_fran_results"></div>

                            </div>
                        </div>

                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Concluido</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal fade" tabindex="-1" role="dialog" id="confirma_pedido_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Voce deseja realmente pesquisar e efetuar os pedidos?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal"
                            onclick="$('#pesquisa_opcao').val('pesquisar_pedido');startProcesso()">Prosseguir
                    </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <?php include('footer_js.phtml') ?>

    <script>

        function startProcesso() {

            executaProcesso();

            //var timeoutID = window.setInterval(executaProcesso, 900000)
        }

        var startDate;
        var endDate;
        var log_duracao;
        var txtDuracao;

        function executaProcesso(urlAjax) {

            pesquisa_opcao = $('#pesquisa_opcao');

            switch (pesquisa_opcao.val()) {

                case 'pesquisar_visualizar':
                    $('#btn_nova_pesquisa').removeClass('hide');
                    urlAjax = '<?php echo BASE_URL ?>ajaxVerificaProdutos2.php';
                    break;

                case 'pesquisar_pedido':
                    urlAjax = '<?php echo BASE_URL ?>ajaxEfetuaPedido.php';
                    break;

                case 'pesquisa_automatica':
                    urlAjax = '<?php echo BASE_URL ?>ajaxPesquisaEfetuaPedidoAutomatico.php';
                    break;
            }

            var result = $('#result');

            startDate = new Date();

            $('html, body').animate({scrollTop: result.offset().top}, 2000);

            var result_loading = '';

            result_loading += '<ul class="list-group" id="result_loading">';
            result_loading += '<li id="lgi" class="list-group-item">';
            result_loading += '<div class="progress">';
            result_loading += '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100"';
            result_loading += 'aria-valuemin="0" aria-valuemax="100" style="width: 100%"><span';
            result_loading += 'class="sr-only">Aguarde...</span></div>';
            result_loading += '</div>';
            result_loading += '</li>';
            result_loading += '</ul>';

            cod_produtos_tag = $('#cod_produtos').tagsinput('items');
            cod_franquias_tag = $('#cod_franquias').tagsinput('items');

            $('#pesquisa_automatica_opcoes').hide();

            if (cod_produtos_tag.length > 0 && cod_franquias_tag.length > 0) {

                $('#bnts_options').hide();
                result.html(result_loading);
                result.show();

                if (pesquisa_opcao.val() == 'pesquisa_automatica') {

                    console.log(urlAjax)

                    $.ajax({
                            type: "GET",
                            data: $('#formSearch').serialize(),
                            dataType: 'json',
                            url: '<?php echo BASE_URL ?>ajaxPesquisaEfetuaPedidoAutomatico.php'
                        })
                        .done(function (data) {

                                console.log(data);

                                $.each(data, function (i, item) {
                                    
                                    //aqui alterar a quantidade atual para a quantidade que ja foi pedida
                                })
                            }
                        );
                    
                }
                else {
                    $.ajax({
                            type: "GET",
                            data: $('#formSearch').serialize(),
                            url: urlAjax
                        })
                        .done(function (data) {

                            $('#bnts_options').show();

                            if (data == 'false') {
                                result.html('<div class="alert alert-danger" role="alert">Nenhum produto encontrado!</div>');
                                $('#btn_efetuar_pedido').addClass('hide');
                                $('#pedidos_input').addClass('hide');
                            }
                            else {
                                result.html(data);
                                $('#pedidos_input').removeClass('hide');
                                $('#btn_efetuar_pedido').removeClass('hide');
                            }

                            $('html, body').animate({scrollTop: result.offset().top}, 2000);

                            endDate = new Date();

                            ms = endDate.getTime() - startDate.getTime();

                            s = Math.round((ms / 1000) % 60);
                            m = Math.round((ms / (1000 * 60)) % 60);
                            h = Math.round((ms / (1000 * 60 * 60)) % 24);

                            if (s == 60) {

                                s = s - 1;
                            }

                            if (m == 60) {

                                m = m - 1;
                            }

                            txtDuracao = h + 'h ' + m + 'm ' + s + 's';

                            log_duracao = h + ':' + m + ':' + s;

                            $('#result').append('<div class="alert alert-info" role="alert"><p>Tempo de execucao: ' + txtDuracao + '</p></div>');
                        })
                }
            }
            else {

            }
        }

        $(document).ready(function () {

            $('#filter_prod').keyup(function (event) {

                if (event.which == 13) {
                    event.preventDefault();
                }

                if ($(this).val().length >= 2) {

                    $('#filter_prod_results').html('carregando...');

                    $.ajax({
                            type: "GET",
                            data: $('#formFilter').serialize(),
                            url: "<?php echo BASE_URL ?>ajaxFilterProd.php"
                        })
                        .done(function (data) {

                            $('#filter_prod_results').html(data);

                        })
                }
                else if ($('#filter_prod').val().trim() === '') {
                    $('#filter_prod_results').html('');
                }
            });

            $('#filter_linha_prod').keyup(function (event) {

                if (event.which == 13) {
                    event.preventDefault();
                }

                if ($(this).val().length >= 2) {

                    $('#filter_linha_prod_results').html('carregando...');

                    $.ajax({
                            type: "GET",
                            data: $('#formFilter').serialize(),
                            url: "<?php echo BASE_URL ?>ajaxFilterLinhaProd.php"
                        })
                        .done(function (data) {

                            $('#filter_linha_prod_results').html(data);

                        })
                }
                else if ($('#filter_linha_prod').val().trim() === '') {
                    $('#filter_linha_prod_results').html('');
                }
            });

            $('#filter_fran').keyup(function (event) {

                if (event.which == 13) {
                    event.preventDefault();
                }

                if ($(this).val().length >= 2) {

                    $('#filter_fran_results').html('carregando...');

                    $.ajax({
                            type: "GET",
                            data: $('#formFilter').serialize(),
                            url: "<?php echo BASE_URL ?>ajaxFilterFran.php"
                        })
                        .done(function (data) {

                            $('#filter_fran_results').html(data);

                        })
                }
                else if ($('#filter_fran').val().trim() === '') {
                    $('#filter_fran_results').html('');
                }
            });

            $('#cod_produtos, #cod_franquias').tagsinput({
                tagClass: 'label label-info',
                itemValue: 'id',
                itemText: 'label'
            });

            var cod_pedidos = $('#cod_pedidos');

            cod_pedidos.tagsinput({
                tagClass: 'label label-success',
                itemValue: 'id',
                itemText: 'label'
            });

            cod_pedidos.on('beforeItemRemove', function (event) {
                var tag = event.item;
                id = tag.id.split('|');

                str_html = '<input type="number" class="form-control" placeholder="quantidade" id="quantidade_' + id[0] + '_' + id[1] + '">';

                $('#input_qnt_pedido_' + id[0] + '_' + id[1]).html(str_html);

                str_add_pedido = '<a href="javascript:void(0)" class="btn btn-info" ';
                str_add_pedido += 'onclick=addProdPedido($(quantidade_' + id[0] + '_' + id[1] + ').val(),\'' + id[0] + '\',\'' + id[1] + '\')>';
                str_add_pedido += 'Adicionar pedido</a>';

                $('#btn_add_pedido_' + id[0] + '_' + id[1]).html(str_add_pedido);
            });

            $.getJSON("<?php echo BASE_URL ?>ajaxGetFranFav.php", function (data) {

                $.each(data, function (i, item) {
                    $("#cod_franquias").tagsinput('add', {
                        id: item.code,
                        label: item.description + ' - ' + item.district
                    })
                })
            });

            $('#btn_nova_pesquisa').click(function () {
                $('#pedidos_input, #btn_nova_pesquisa').addClass('hide');
                $('#btn_efetuar_pedido').addClass('hide');
                $('#cod_pedidos, #cod_produtos').tagsinput('removeAll');
                $('#result').html('');
            });


            $('#btn_pesquisa_automatica').click(function () {

                $('#pedidos_input').addClass('hide');
                $('#cod_pedidos').tagsinput('removeAll');

                result = $('#result');
                result.hide();

                cod_produtos_tag = $('#cod_produtos').tagsinput('items');

                str_pes_auto =
                    '<div class="page-header">' +
                    '<h2>Pesquisa Automatica</h2>' +
                    '</div>' +
                    '<div class="panel panel-primary"><div class="panel-heading">' +
                    '<strong>Informe a quantidade de cada produto que deseja efetuar o pedido apos estiver disponivel</strong>' +
                    '</div><div class="table-responsive">' +
                    '<table class="table table-hover">' +
                    '<tr>' +
                    '<th>Produto</th> ' +
                    '<th>Quantidade</th> ' +
                    '</tr> ' +
                    '<tbody>';

                for (i = 0; i < cod_produtos_tag.length; i++) {

                    str_pes_auto +=
                        '<tr>' +
                        '<th>' + cod_produtos_tag[i].label + '</th>' +
                        '<td>' +
                        '<div class="col-md-6" style="padding-left:0">' +
                        '<input type="number" id="pes_auto_qtd_' + cod_produtos_tag[i].id + '" name="pes_auto_qtd_' + cod_produtos_tag[i].id + '" class="form-control">' +
                        '</div>' +
                        '</td>' +
                        '</tr>';
                }

                str_pes_auto +=
                    '</tbody>' +
                    '</table>' +
                    '</div></div>' +
                    '<hr>' +
                    '<a href="javascript:void(0)" class="btn btn-primary" onclick="startPesquisaAutomatica()">' +
                    '<i class="glyphicon glyphicon-dashboard"></i> Iniciar Pesquisa Automatica</a> <hr>';

                if (cod_produtos_tag.length > 0) {
                    $('#pesquisa_automatica_opcoes').show().html(str_pes_auto);
                }
                else {
                    result.show().html('<div class="alert alert-danger" role="alert">Nenhum produto selecionado!</div>');
                }

            });
        });

        function startPesquisaAutomatica() {

            $('#pesquisa_opcao').val('pesquisa_automatica');
            $('#pesquisa_automatica_opcoes').hide();

            startProcesso();
        }

        function updateCodProd(val) {

            prod_split = val.split(',');

            for (i = 0; i < prod_split.length; i++) {
                if (prod_split[i] != "" && prod_split[i] != null) {
                    $("#cod_produtos").tagsinput('add', {
                        id: prod_split[i].split('|')[0],
                        label: prod_split[i].split('|')[1]
                    })
                }
            }

            $('#filter_prod_results').html('');
            $('#filter_prod').val('').focus();
        }

        function updateCodFran(val) {

            fran_split = val.split(',');

            for (i = 0; i < fran_split.length; i++) {
                if (fran_split[i] != "" && fran_split[i] != null) {
                    $("#cod_franquias").tagsinput(
                        'add', {id: fran_split[i].split('|')[0], label: fran_split[i].split('|')[1]}
                    )
                }
            }

            $('#filter_fran_results').html('');
            $('#filter_fran').val('').focus();
        }

        function addFranFav(franquia) {

            $('#fran_fav_' + franquia).html('aguarde...');

            $.ajax({
                    type: "GET",
                    data: $('#formFilter').serialize(),
                    url: "<?php echo BASE_URL ?>ajaxAddFranFav.php?franquia=" + franquia
                })
                .done(function (data) {

                    var_franquia = $('#fran_fav_' + franquia);

                    var_franquia.removeClass('btn-info');
                    var_franquia.addClass('btn-danger');
                    var_franquia.html('<span class="glyphicon glyphicon-remove"></span> Remover dos Favoritos');
                    var_franquia.attr('onclick', 'delFranFav(\'' + franquia + '\')');
                })
        }

        function delFranFav(franquia) {

            $('#fran_fav_' + franquia).html('aguarde...');

            $.ajax({
                    type: "GET",
                    data: $('#formFilter').serialize(),
                    url: "<?php echo BASE_URL ?>ajaxDelFranFav.php?franquia=" + franquia
                })
                .done(function (data) {

                    var_franquia = $('#fran_fav_' + franquia);

                    var_franquia.removeClass('btn-danger');
                    var_franquia.addClass('btn-warning');
                    var_franquia.html('<span class="glyphicon glyphicon-star"></span> Adicionar aos Favoritos');
                    var_franquia.attr('onclick', 'addFranFav(\'' + franquia + '\')');
                })
        }

        function addProdPedido(qtd, cod_prod, cod_fran) {

            tag_id = cod_prod + '|' + cod_fran + '|' + qtd + '|';
            tag_label = $('#txt_fra_' + cod_fran).html() + ' - ' + $('#txt_pro_' + cod_prod).html() + ' (' + cod_prod + ') - Quantidade: ' + qtd;

            $("#cod_pedidos").tagsinput(
                'add', {
                    id: tag_id,
                    label: tag_label
                }
            );

            $('#input_qnt_pedido_' + cod_prod + '_' + cod_fran).html('<h4><span class="label label-warning"><i class="glyphicon glyphicon-ok"></i> Adicionado ' + qtd + ' itens</span></h4>');

            str_rmv_pedido = '<a href="javascript:void(0)" class="btn btn-danger"';
            str_rmv_pedido += 'onclick=$(\'#cod_pedidos\').tagsinput(\'remove\',{id:\'' + tag_id + '\'})>';
            str_rmv_pedido += 'Remover pedido</a>';

            $('#btn_add_pedido_' + cod_prod + '_' + cod_fran).html(str_rmv_pedido);
        }

    </script>

<?php include('footer.phtml') ?>