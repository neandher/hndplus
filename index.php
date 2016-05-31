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
<body onload="startProcesso()">

<style type="text/css" scoped>
    .progress {
        margin-bottom: 0;
    }
</style>

<div class="container">

    <div class="page-header">
        <h1>Script para verifica&ccedil;&atilde;o de produtos</h1>
    </div>

    <ul class="list-group">

    </ul>

</div>

<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<script>

    var executou = 0;

    function startProcesso() {

        executaProcesso();

        var timeoutID = window.setInterval(executaProcesso, 3600000);
    }

    function executaProcesso() {

        $.ajax({

            type: "GET",

            url: "<?php echo BASE_URL ?>ajaxVerificaProdutos.php?number=" + executou,

            //timeout: 1000000000,

            beforeSend: function () {
                $('.list-group').append('<li id="lgi-' + executou + '" class="list-group-item"><div class="progress"><div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"><span class="sr-only">100% Complete</span></div></div></li>');
            }
        })

            .done(function (data) {

                myDate = new Date();

                if (data == 'success') {

                    $('#lgi-' + executou)
                        .addClass('list-group-item-success')
                        .html('Produto Disponivel. ' + myDate.getHours() + ':' + myDate.getMinutes() + ':' + myDate.getSeconds());
                }
                else {

                    $('#lgi-' + executou)
                        .addClass('list-group-item-danger')
                        .html('Nenhum produto disponivel. ' + myDate.getHours() + ':' + myDate.getMinutes() + ':' + myDate.getSeconds());
                        //.html(data);
                }

                executou++;

                //if (executou < 3) {
                //    executaProcesso();
                //}

            })

            .fail(function (jqXHR, textStatus) {

                $('#lgi-' + executou)
                    .addClass('list-group-item-danger')
                    .html('Fail! jqXHR: ' + jqXHR + ' - textStatus: ' + textStatus);

                executou++;
            })
    }

</script>

</body>
</html>

