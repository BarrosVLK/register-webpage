<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a senha</title>
    <link rel="stylesheet" href="site.css?v=20261024-3">
</head>
<body class="auth-page">
    <main class="auth-card">
        <p class="auth-eyebrow">Recuperação de acesso</p>
    <?php
        ini_set('display_errors', 1);
        $CPFPessoa = $_POST['CPFPessoa'];

        if (empty($CPFPessoa)) {
            die("Preencha o campo de CPF");
        }

        require("dbConnect.php");
        require("cryp2graph2.php");
        require("enviaEmail.php");

        $sql = "select CPFpessoa2DS from pessoa2DS where CPFpessoa2DS like ?";
        $stmt = mysqli_prepare($BDconn, $sql);
        if (!$stmt) {
            die("Não consegui preparar consulta no BD!");
        }
        $paramEntrada = mysqli_stmt_bind_param($stmt, "s", $CPFPessoa);
        if (!$paramEntrada) {
            die("Não consegui vincular paramêtros de entrada!");
        }
        $exec = mysqli_stmt_execute($stmt);
        if (!$exec) {
            die("Não consegui executar consulta no BD!");
        }
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 0) {
            die("CPF não encontrado.");
        }

        $senhaTemporaria = ResetaSenha($BDconn, $CPFPessoa);

        if ($senhaTemporaria === false) {
            die("Não consegui resetar sua senha.");
        }

        if (!EnviarEmailReset($BDconn, $CPFPessoa, $senhaTemporaria)) {

        die("A senha foi resetada, mas não consegui enviar o e-mail.");
    }


        echo "Uma nova senha foi enviada para o seu e-mail prioritário.";

    ?>
    <p><a class="auth-link" href="tela01.php">Voltar para o login</a></p>
    </main>
</body>
</html>
