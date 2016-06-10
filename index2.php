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
            <h4 class="pull-left"><i class="glyphicon glyphicon-list-alt"></i> Pesquisar Produtos</h4>

            <div class="pull-right">
                <a href="<?php echo BASE_URL ?>site/agenteIntegracao/cadastrar" class="btn btn-primary"
                   data-toggle="modal" data-target="#filter_modal">
                    <span class="glyphicon glyphicon-plus"></span> Clique aqui para selecionar os produtos
                </a>

            </div>

            <div class="clearfix"></div>
        </div>

        <div class="panel-body">

            <div class="row">
                <div class="col-md-12">

                    <form id="testeForm" onsubmit="return false">
                        <div class="form-group">
                            <label for="cod_produtos">Codigos dos Produtos</label>
                            <textarea class="form-control" id="cod_produtos" rows="8"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="cod_franquias">Codigo Franquias</label>
                            <textarea class="form-control" id="cod_franquias" rows="8">10360072,10650784,10153011,10615393,10438342</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" onclick="startProcesso()">Pesquisar</button>
                        <button class="btn btn-default" onclick="$('#cod_produtos').val('')">
                            Limpar Produtos
                        </button>
                        <button class="btn btn-default" onclick="$('#cod_franquias').val('')">
                            Limpar Franquias
                        </button>
                    </form>

                </div>
            </div>

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
                                    <input type="text" class="form-control" name="filter_prod" id="filter_prod">
                                </div>

                                <div id="filter_prod_results"></div>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_linha_produtos">

                                <br>

                                <div class="form-group">
                                    <label for="filter_linha_prod">Digite a linha de produtos que desejar
                                        pesquisar</label>
                                    <input type="text" class="form-control" name="filter_linha_prod"
                                           id="filter_linha_prod">
                                </div>

                                <div id="filter_linha_prod_results"></div>

                            </div>

                            <div role="tabpanel" class="tab-pane" id="tab_franquias">

                                <br>

                                <div class="form-group">
                                    <label for="filter_fran">Digite o nome das franquias que desejar pesquisar</label>
                                    <input type="text" class="form-control" name="filter_fran" id="filter_fran">
                                </div>

                                <div id="filter_fran_results"></div>

                            </div>
                        </div>

                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <?php include('footer_js.phtml') ?>

    <script>

        function startProcesso() {
            executaProcesso();
        }

        var startDate;
        var endDate;
        var log_duracao;
        var txtDuracao;

        function executaProcesso() {

            startDate = new Date();

            var result_loading = '';

            result_loading += '<ul class="list-group" id="result_loading">';
            result_loading += '<li id="lgi" class="list-group-item">';
            result_loading += '<div class="progress">';
            result_loading += '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100"';
            result_loading += 'aria-valuemin="0" aria-valuemax="100" style="width: 100%"><span';
            result_loading += 'class="sr-only">100% Complete</span></div>';
            result_loading += '</div>';
            result_loading += '</li>';
            result_loading += '</ul>';

            $('#result').html(result_loading);

            $.ajax({
                    type: "GET",
                    url: "<?php echo BASE_URL ?>ajaxVerificaProdutos2.php?cod_produtos=" + $("#cod_produtos").val() + "&cod_franquias=" + $("#cod_franquias").val()
                })
                .done(function (data) {

                    $('#result').html(data);

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

                    $('#result').append('<p>Tempo da pesquisa: ' + txtDuracao + '</p>');
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
        });

        function updateCodProd(val) {
            cod_prod = $("#cod_produtos").val();
            $("#cod_produtos").val(cod_prod + val + ',');
            $('#filter_prod_results').html('');
            $('#filter_prod').val('');
            $('#filter_prod').focus();
        }

        function updateCodFran(val) {
            cod_fran = $("#cod_franquias").val();
            $("#cod_franquias").val(cod_fran + val + ',');
            $('#filter_fran_results').html('');
            $('#filter_fran').val('');
            $('#filter_fran').focus();
        }

    </script>

    <?php include('footer.phtml') ?>

