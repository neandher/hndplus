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
        <h1>Script para Testes</h1>
    </div>

    <ul class="list-group">
        <li id="lgi" class="list-group-item">
            <div class="progress">
                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100"
                     aria-valuemin="0" aria-valuemax="100" style="width: 100%"><span
                        class="sr-only">100% Complete</span></div>
            </div>
        </li>
    </ul>

</div>

<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<script>

    function startProcesso() {

        executaProcesso();
    }

    function executaProcesso() {

        $.ajax({
                type: "GET",
                url: "<?php echo BASE_URL ?>ajaxTeste.php"
            })
            .done(function (data) {

                $('#lgi')
                    .addClass('list-group-item-success')
                    .html(data);

            })
    }

</script>

</body>
</html>

