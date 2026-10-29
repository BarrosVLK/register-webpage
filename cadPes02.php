<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pessoa - Cadastrar</title>
    <link rel="stylesheet" href="site.css?v=20261024-3">

</head>

<body class="auth-page">
<main class="Card1">
    <div class="conteudo">

    <?php
        require("dbConnect.php");
        require("cryp2graph2.php");

        $nomeCompleto=$_POST['nomeCompleto'];
        $CPFPessoa=$_POST['CPFPessoa'];
        $senha1=$_POST['senha1'];
        $senha2=$_POST['senha2'];
        $emailPessoa = is_array($_POST['emails'] ?? null) ? $_POST['emails'] : [];
        $DDDtelPessoa = is_array($_POST['DDDPessoa'] ?? null) ? $_POST['DDDPessoa'] : [];
        $telefonePessoa = is_array($_POST['telefonePessoa'] ?? null) ? $_POST['telefonePessoa'] : [];
        $prioritarios = is_array($_POST['prioritarios'] ?? null) ? $_POST['prioritarios'] : [];

        $estadoPessoa      = is_array($_POST['estadoPessoa'] ?? null) ? $_POST['estadoPessoa'] : [];
        $cidadePessoa      = is_array($_POST['cidadePessoa'] ?? null) ? $_POST['cidadePessoa'] : [];
        $logradouroPessoa  = is_array($_POST['logradouroPessoa'] ?? null) ? $_POST['logradouroPessoa'] : [];
        $bairroPessoa      = is_array($_POST['bairroPessoa'] ?? null) ? $_POST['bairroPessoa'] : [];
        $numPessoa         = is_array($_POST['numPessoa'] ?? null) ? $_POST['numPessoa'] : [];
        $complementoPessoa = is_array($_POST['complementoPessoa'] ?? null) ? $_POST['complementoPessoa'] : [];
        $CEPPessoa         = is_array($_POST['CEPPessoa'] ?? null) ? $_POST['CEPPessoa'] : [];

        
        #if (empty($nomeCompleto) || empty($CPFPessoa) || empty($emailPessoa) || empty($senha1) || empty($senha2)) {
        
        if (empty($nomeCompleto) || empty($CPFPessoa) || empty($senha1) || empty($senha2)) {
            echo("<p class='erro'>Preencha todos os campos!</p>");
        } elseif (!preg_match('/^\d{11}$/D', (string)$CPFPessoa) || strlen((string)$nomeCompleto) > 150) {
            echo("<p class='erro'>Confira o CPF e o nome informados.</p>");
        } elseif ($senha1 !== $senha2) {
            echo("<p class='erro'>As senhas não coincidem!</p>");
        } elseif (strlen((string)$senha1) < 8 || strlen((string)$senha1) > 72) {
            echo("<p class='erro'>A senha deve ter entre 8 e 72 caracteres.</p>");
        } elseif (empty($emailPessoa) || count(array_filter($prioritarios, fn($value) => $value === '1')) !== 1) {
            echo("<p class='erro'>Informe ao menos um e-mail e escolha um e-mail prioritário.</p>");
        } elseif (count(array_filter($emailPessoa, fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)) !== count($emailPessoa)) {
            echo("<p class='erro'>Confira os endereços de e-mail informados.</p>");
        } elseif (empty($telefonePessoa) || empty($logradouroPessoa)) {
            echo("<p class='erro'>Informe ao menos um telefone e um endereço.</p>");
        } elseif (count($telefonePessoa) !== count($DDDtelPessoa) || count($logradouroPessoa) !== count($estadoPessoa) || count($logradouroPessoa) !== count($cidadePessoa)) {
            echo("<p class='erro'>Confira os dados de telefone e endereço.</p>");
        } else {

        mysqli_begin_transaction($BDconn);
        try {

            
        $senhaCrypto = FazSenha($CPFPessoa, $senha1);

        $validadeSenha = date('Y-m-d H:i:s', strtotime('+30 days'));
        $sql="INSERT INTO pessoa2DS (CPFpessoa2DS, nomePessoa, senhaPessoa, validaSenhaPessoa) 
        VALUES (?, ?, ?, ?)";

        $stmt=mysqli_prepare($BDconn, $sql);

        if (!$stmt) {
            die("Erro ao preparar BD");
        }

        $paramEntrada=mysqli_stmt_bind_param($stmt, "ssss", $CPFPessoa, $nomeCompleto, $senhaCrypto, $validadeSenha);
        if (!$paramEntrada) {
            die("Não consegui vincular parâmetros de entrada!");
        }

            try {
                mysqli_stmt_execute($stmt);
            } 
            catch (mysqli_sql_exception $e) 
            {

            if ($e->getCode() == 1062) {
                throw new Exception("Já existe um usuário com esse CPF");
            }

            throw $e;
            }
                
        // Emails --------------------------- !
        
        $sql2="INSERT INTO emailPessoa (CPFpessoa2DS, emailPessoa, prioritario) 
        VALUES (?, ?, ?)";
        
        $stmt2=mysqli_prepare($BDconn, $sql2);
        
        if (!$stmt2) 
        {
            die("Erro ao preparar BD");
        }
        
        $emailEmail="";
        $prioritario="N";

        $paramEntrada2=mysqli_stmt_bind_param($stmt2, "sss", $CPFPessoa, $emailEmail, $prioritario);
        
        if (!$paramEntrada2) 
        {
            die("Não consegui vincular parâmetros de entrada!");
        }
        foreach($emailPessoa as $key => $value)
        {
            $emailEmail = $value;

            $prioritario = (($prioritarios[$key] ?? '0') === '1') ? 'S' : 'N';

            try {
                mysqli_stmt_execute($stmt2);
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    // E-mail duplicado
                } else {
                    echo("<p class='erro'>Erro no banco</p>");
                }
            }
        }
                
        // Telefones ---------------------------- !

        $sql3="INSERT INTO telefonePessoa (CPFpessoa2DS, DDD, telefonePessoa) 
            VALUES (?, ?, ?)";

            $stmt3=mysqli_prepare($BDconn, $sql3);

            if (!$stmt3) 
            {
                die("Erro ao preparar BD");
            }
            
            $DDDpessoa="";
            $telefoneTelefone="";
            $paramEntrada3=mysqli_stmt_bind_param($stmt3, "sss", $CPFPessoa, $DDDpessoa, $telefoneTelefone);
            
            if (!$paramEntrada3) 
            {
                die("Não consegui vincular parâmetros de entrada!");
            }
            foreach ($telefonePessoa as $key => $value)
            {
                $DDDpessoa = $DDDtelPessoa[$key];
                $telefoneTelefone = $value;
                try {
                    mysqli_stmt_execute($stmt3);
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) {
                        //echo("<p class='erro'>Já existe um usuário com esse CPF</p>");
                    } else {
                        echo("<p class='erro'>Erro no banco</p>");
                    }
                }
            }


        // Endereco ---------------------------- !

        $sql4 = "INSERT INTO enderecoPessoa
        (CPFpessoa2DS, estadoPessoa, cidadePessoa, logradouroPessoa,
        bairroPessoa, numPessoa, complementoPessoa, CEPPessoa)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt4 = mysqli_prepare($BDconn, $sql4);

            if (!$stmt4) 
            {
                die("Erro ao preparar BD");
            }
            
            $estadoEstado="";
            $cidadeCidade="";
            $logradouroLogradouro="";
            $bairroBairro="";
            $numNum="";
            $complementoComplemento="";
            $CEPcep="";

            $paramEntrada4=mysqli_stmt_bind_param($stmt4, "ssssssss", $CPFPessoa, $estadoEstado, $cidadeCidade, $logradouroLogradouro, $bairroBairro, $numNum, $complementoComplemento, $CEPcep);
            
            if (!$paramEntrada4) 
            {
                die("Não consegui vincular parâmetros de entrada!");
            }
            foreach ($logradouroPessoa as $key => $value)
            {
                $estadoEstado= $estadoPessoa[$key] ?? "";
                $cidadeCidade= $cidadePessoa[$key] ?? "";
                $bairroBairro= $bairroPessoa[$key] ?? "";
                $numNum= $numPessoa[$key] ?? "";
                $complementoComplemento= $complementoPessoa[$key] ?? "";
                $CEPcep= $CEPPessoa[$key] ?? "";
                $logradouroLogradouro=$value; //principal
                try {
                    mysqli_stmt_execute($stmt4);
                } catch (mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) {
                        //echo("<p class='erro'>Já existe um usuário com esse CPF</p>");
                    } else {
                        echo("<p class='erro'>Erro no banco</p>");
                    }
                }
            }
            mysqli_commit($BDconn); //se tudo der certo vai commita!
            header('Location: cadastroConcluido.php');
            exit;
            
            //fechamento else {(se der erro!)}
            } catch(Exception $e) {
                echo("Ocorreu um erro ao realizar o cadastro. Tente novamente!");
                mysqli_rollback($BDconn);
            }
        }


    ?>

    <p><a class="button secondary" href="cadPes01.php">Voltar e revisar os dados</a></p>

    </div>
</main>

</body>
</html> 

