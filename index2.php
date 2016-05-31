<?php

require("config.php");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Script Hinode</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>

<style type="text/css" scoped>
    .progress {
        margin-bottom: 0;
    }
</style>

<div class="container">

    <div class="page-header">
        <h1>Script</h1>
    </div>

    <div class="row">
        <div class="col-md-6">

            <form id="testeForm" onsubmit="return false">
                <div class="form-group">
                    <label for="cod_produtos">Codigos dos Produtos</label>
                    <textarea class="form-control" id="cod_produtos" placeholder="Ex: 002361,002362"
                              value="002362,000273,016003,016070,016087,045001,045014,045011" rows="8"></textarea>
                </div>
                <div class="form-group">
                    <label for="cod_franquias">Codigo Franquias</label>
                    <textarea class="form-control" id="cod_franquias" placeholder="Ex: 10360072,10650784"
                              value="10360072,10650784,10153011,10615393,10438342,10930066,10488217,10774033,10843260,10790939"
                              rows="8"></textarea>
                </div>
                <button type="submit" class="btn btn-default" onclick="startProcesso()">Submit</button>
            </form>

        </div>

        <div class="col-md-6">

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#tab_produtos" aria-controls="tab_produtos" role="tab" data-toggle="tab">Produtos</a>
                </li>
                <li role="presentation">
                    <a href="#tab_linha_produtos" aria-controls="tab_linha_produtos" role="tab" data-toggle="tab">
                        Linha Produtos
                    </a>
                </li>
                <li role="presentation">
                    <a href="#tab_franquias" aria-controls="tab_franquias" role="tab" data-toggle="tab">Franquias</a>
                </li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="tab_produtos">

                    <br>

                    <div class="form-group">
                        <label for="filter_prod">Digite o código ou o nome dos produtos que desejar pesquisar</label>
                        <input type="text" class="form-control" name="filter_prod" id="filter_prod">
                    </div>

                    <div id="filter_prod_results"></div>

                </div>

                <div role="tabpanel" class="tab-pane" id="tab_linha_produtos">

                    <br>

                    <div class="form-group">
                        <label for="filter_linha_prod">Digite a linha de produtos que desejar pesquisar</label>
                        <input type="text" class="form-control" name="filter_linha_prod" id="filter_linha_prod">
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

            <!--<table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Franquia</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row">10360072</th>
                    <td>Vila Velha - Praia da Costa</td>
                </tr>
                <tr>
                    <th scope="row">10650784</th>
                    <td>Vitoria - Praia do Canto</td>
                </tr>
                <tr>
                    <th scope="row">10153011</th>
                    <td>Vitoria - Santa Lucia</td>
                </tr>
                <tr>
                    <th scope="row">10615393</th>
                    <td>CARIACICA - JARDIM AMERICA</td>
                </tr>
                <tr>
                    <th scope="row">10438342</th>
                    <td>SERRA - PQ RESIDENCIAL LARANJEIRAS</td>
                </tr>
                <tr>
                    <th scope="row">10930066</th>
                    <td>GUARAPARI</td>
                </tr>
                <tr>
                    <th scope="row">10488217</th>
                    <td>Cachoeiro de Itapemirim - CENTRO</td>
                </tr>
                <tr>
                    <th scope="row">10774033</th>
                    <td>COLATINA - ESPLANADA</td>
                </tr>
                <tr>
                    <th scope="row">10843260</th>
                    <td>LINHARES - CENTRO</td>
                </tr>
                <tr>
                    <th scope="row">10790939</th>
                    <td>SAO MATEUS - SERNAMBY</td>
                </tr>
                </tbody>
            </table>-->

        </div>
    </div>

    <br>

    <div id="result"></div>

</div>

<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<script>

    function startProcesso() {
        executaProcesso();
    }

    function executaProcesso() {

        var result_loading = `<ul class="list-group" id="result_loading">
            <li id="lgi" class="list-group-item">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100"
                         aria-valuemin="0" aria-valuemax="100" style="width: 100%"><span
                            class="sr-only">100% Complete</span></div>
                </div>
            </li>
        </ul>`;

        $('#result').html(result_loading);

        $.ajax({
                type: "GET",
                url: "<?php echo BASE_URL ?>ajaxVerificaProdutos2.php?cod_produtos=" + $("#cod_produtos").val() + "&cod_franquias=" + $("#cod_franquias").val()
            })
            .done(function (data) {

                $('#result').html(data);
            })
    }

    $(document).ready(function () {

        $('#filter_prod').keyup(function (event) {

            if (event.which == 13) {
                event.preventDefault();
            }

            if ($(this).val().length >= 3) {

                $('#filter_prod_results').html('carregando...');

                $.ajax({
                        type: "GET",
                        url: "<?php echo BASE_URL ?>ajaxFilterProd.php?filter=" + $("#filter_prod").val()
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

            if ($(this).val().length >= 3) {

                $('#filter_linha_prod_results').html('carregando...');

                $.ajax({
                        type: "GET",
                        url: "<?php echo BASE_URL ?>ajaxFilterLinhaProd.php?filter=" + $("#filter_linha_prod").val()
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

            if ($(this).val().length >= 3) {

                $('#filter_fran_results').html('carregando...');

                $.ajax({
                        type: "GET",
                        url: "<?php echo BASE_URL ?>ajaxFilterFran.php?filter=" + $("#filter_fran").val()
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

</body>
</html>

