<?php require("config.php"); ?>

<?php include('cabecalho.phtml') ?>
<?php include('navbar.phtml') ?>

<style type="text/css" scoped>
    .progress {
        margin-bottom: 0;
    }
</style>

<div class="container">

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

            <form id="formSearch" class="form-horizontal" onsubmit="return false">

                <input type="hidden" name="pesquisa_opcao" id="pesquisa_opcao">

                <div class="form-group" id="produtos_input">
                    <label for="cod_produtos" class="col-sm-1 control-label">Produtos</label>
                    <div class="col-sm-11">
                        <!--<textarea class="form-control" name="cod_produtos" id="cod_produtos" rows="8" ></textarea>-->
                        <input type="text" name="cod_produtos" id="cod_produtos" class="form-control">
                    </div>
                </div>
                <div class="form-group" id="franquias_input">
                    <label for="cod_franquias" class="col-sm-1 control-label">Franquias</label>
                    <div class="col-sm-11">
                        <!--<textarea class="form-control" name="cod_franquias" id="cod_franquias" rows="8">10360072,10650784,10153011,10615393,10438342</textarea>-->
                        <input type="text" name="cod_franquias" id="cod_franquias" class="form-control">
                    </div>
                </div>

                <div class="form-group hide" id="pedidos_input">
                    <label for="cod_pedidos" class="col-sm-1 control-label">Pedido</label>
                    <div class="col-sm-11">
                        <input type="text" name="cod_pedidos" id="cod_pedidos" class="form-control">
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <div class="col-sm-offset-1 col-sm-11">
                        <a href="#" class="btn btn-primary"
                           onclick="$('#pesquisa_opcao').val('pesquisar_visualizar');startProcesso()" id="btn_pesquisar_visualizar">
                            <i class="glyphicon glyphicon-search"></i> Pesquisar e Visualizar
                        </a>

                        <a href="#" class="btn btn-success" id="btn_efetuar_pedido" disabled="disabled"
                           data-toggle="modal"
                           data-target="#confirma_pedido_modal">
                            <i class="glyphicon glyphicon-ok"></i> Efetuar Pedido
                        </a>

                        <a href="#" class="btn btn-info hide" id="btn_nova_pesquisa">
                            <i class="glyphicon glyphicon-plus"></i> Nova Pesquisa
                        </a>

                        <a href="#" class="btn btn-danger" id="btn_rmv_prod"
                           onclick="$('#cod_produtos').tagsinput('removeAll')">
                            <i class="glyphicon glyphicon-remove-circle"></i> Limpar Produtos
                        </a>

                        <a href="#" class="btn btn-danger" id="btn_rmv_fran"
                           onclick="$('#cod_franquias').tagsinput('removeAll')">
                            <i class="glyphicon glyphicon-remove-circle"></i> Limpar Franquias
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <br>

    <div id="result"></div>

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

                            <div role="tabpanel" class="tab-pane" id="tab_franquias"
                                 onclick="add($('#cod_produtos<?php echo $val['code'] ?>').val())">

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

            switch ($('#pesquisa_opcao').val()) {

                case 'pesquisar_visualizar':
                    $('#produtos_input, #franquias_input, #btn_pesquisar_visualizar, #btn_rmv_prod, #btn_rmv_fran').hide();
                    $('#pedidos_input, #btn_nova_pesquisa').removeClass('hide');
                    executaProcesso("<?php echo BASE_URL ?>ajaxVerificaProdutos2.php");
                    break;

                case 'pesquisar_pedido':
                    executaProcesso("<?php echo BASE_URL ?>ajaxEfetuaPedido.php");
                    break;
            }
        }

        var startDate;
        var endDate;
        var log_duracao;
        var txtDuracao;

        function executaProcesso(urlAjax) {

            startDate = new Date();

            $('html, body').animate({scrollTop: $('#result').offset().top}, 2000);

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

            $('#result').html(result_loading);

            $.ajax({
                    type: "GET",
                    data: $('#formSearch').serialize(),
                    url: urlAjax
                })
                .done(function (data) {

                    $('#result').html(data);

                    $('#btn_efetuar_pedido').attr('disabled', false);

                    $('html, body').animate({scrollTop: $('#result').offset().top}, 2000);

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

            $('#cod_produtos, #cod_franquias, #cod_pedidos').tagsinput({
                tagClass: 'label label-info',
                freeInput: true,
                itemValue: 'id',
                itemText: 'label'
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
                $('#produtos_input, #franquias_input, #btn_pesquisar_visualizar, #btn_rmv_prod, #btn_rmv_fran').show();
                $('#pedidos_input, #btn_nova_pesquisa').addClass('hide');
                $('#btn_efetuar_pedido').attr('disabled', true);
                $('#cod_pedidos, #cod_produtos').tagsinput('removeAll');
            });
        });

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
            $("#cod_pedidos").tagsinput(
                'add', {
                    id: cod_prod + '|' + cod_fran + '|' + qtd + '|',
                    label: $('#txt_fra_' + cod_fran).html() + ' - ' + $('#txt_pro_' + cod_prod).html() + ' ('+cod_prod+') - Quantidade: ' + qtd
                }
            );

            $('#input_qnt_pedido_' + cod_prod + '_' + cod_fran).html('<h4><span class="label label-warning"><i class="glyphicon glyphicon-ok"></i> Adicionado ' + qtd + ' itens</span></h4>');
        }

    </script>

    <?php include('footer.phtml') ?>

