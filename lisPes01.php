<?php require("ses_start.php"); ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lista de Usuários Cadastrados</title>
    <link rel="stylesheet" href="site.css?v=20261024-3">

    <script>
        function VaiPara(num) {
            document.lisPes01.numPag.value = num;
            document.lisPes01.submit();
        }
    </script>
</head>

<body class="auth-page">
<form class="auth-card" name="lisPes01" action="lisPes01.php" method="POST">
    <p class="auth-eyebrow">Área da conta</p>
    <h1>Usuários cadastrados</h1>
    <p class="auth-subtitle">Consulte os registros disponíveis.</p>
    <p><a class="auth-link" href="menu.php">Voltar ao menu</a></p>

    <input type="hidden" name="numPag" value="0">

    <?php

    // Quantidade de registros por página
    if (isset($_POST['qtdeReg'])) {
        $qtdeReg = (int) $_POST['qtdeReg'];
    } else {
        $qtdeReg = 5;
    }

    // Número da página
    if (isset($_POST['numPag'])) {
        $numPag = (int) $_POST['numPag'];
    } else {
        $numPag = 0;
    }

    // Primeiro registro que será mostrado
    $regInicial = $numPag * $qtdeReg;

    ?>

    <select name="qtdeReg" onchange="this.form.submit()">

        <option value="5" <?php if ($qtdeReg == 5) echo 'selected'; ?>>
            5
        </option>

        <option value="10" <?php if ($qtdeReg == 10) echo 'selected'; ?>>
            10
        </option>

        <option value="15" <?php if ($qtdeReg == 15) echo 'selected'; ?>>
            15
        </option>

        <option value="20" <?php if ($qtdeReg == 20) echo 'selected'; ?>>
            20
        </option>

        <option value="25" <?php if ($qtdeReg == 25) echo 'selected'; ?>>
            25
        </option>

        <option value="50" <?php if ($qtdeReg == 50) echo 'selected'; ?>>
            50
        </option>

    </select>

    <?php

    require_once("dbConnect.php");

    /*
     * Conta quantos usuários existem
     */
    $sqlTotal = "SELECT CPFpessoa2DS FROM pessoa2DS";

    $dataset = mysqli_query($BDconn, $sqlTotal);

    if (!$dataset) {
        die("Não foi possível consultar a quantidade de usuários.");
    }

    $qtdeTotal = mysqli_num_rows($dataset);

    /*
     * Calcula a quantidade de páginas
     */
    $qtdePag = ceil($qtdeTotal / $qtdeReg);


    /*
     * Busca os usuários da página atual
     */
    $sql = "SELECT CPFpessoa2DS, nomePessoa
            FROM pessoa2DS
            LIMIT $regInicial, $qtdeReg";

    $stmt = mysqli_prepare($BDconn, $sql);

    if (!$stmt) {
        die("Não foi possível preparar o BD.");
    }


    /*
     * Executa a consulta
     */
    $exec = mysqli_stmt_execute($stmt);

    if (!$exec) {
        die("Não foi possível executar a pesquisa.");
    }


    /*
     * Liga os resultados às variáveis
     */
    $result = mysqli_stmt_bind_result(
        $stmt,
        $CPFPessoa,
        $nomePessoa
    );

    if (!$result) {
        die("Não foi possível obter os resultados.");
    }


    echo("<hr>");


    /*
     * Mostra os usuários
     */
    while (mysqli_stmt_fetch($stmt)) {

        echo("CPF: $CPFPessoa - Nome: $nomePessoa");
        echo("<br>");
    }


    /*
     * Mostra a paginação
     */
    echo("<br>");

    for ($i = 0; $i < $qtdePag; $i++) {

        echo(
            "<a href='#' onclick='VaiPara($i)'>" .
            ($i + 1) .
            "</a>"
        );

        echo(" ");
    }

    ?>

</form>

</body>
</html>
