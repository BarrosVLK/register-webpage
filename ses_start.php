<?php
if (session_status() !== PHP_SESSION_ACTIVE && !session_start()) {
    http_response_code(500);
    exit('Não foi possível iniciar a sessão.');
}

if (!isset($_SESSION['CPFPessoa'])) {
    header('Location: tela01.php');
    exit;
}

$paginaAtual = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
$paginasPermitidasNaTroca = ['trocarSenha01.php', 'trocarSenha02.php', 'sair.php'];

if ($paginaAtual !== 'sair.php') {
    require_once __DIR__ . '/dbConnect.php';
    $stmtSenha = mysqli_prepare(
        $BDconn,
        'SELECT tipoSenhaPessoa, validaSenhaPessoa FROM pessoa2DS WHERE CPFpessoa2DS = ?'
    );
    if (!$stmtSenha) {
        http_response_code(503);
        exit('Não foi possível validar a situação da senha. Tente novamente.');
    }
    $cpfSessao = (string)$_SESSION['CPFPessoa'];
    mysqli_stmt_bind_param($stmtSenha, 's', $cpfSessao);
    if (!mysqli_stmt_execute($stmtSenha)) {
        mysqli_stmt_close($stmtSenha);
        http_response_code(503);
        exit('Não foi possível validar a situação da senha. Tente novamente.');
    }
    $resultadoSenha = mysqli_stmt_get_result($stmtSenha);
    $dadosSenha = mysqli_fetch_assoc($resultadoSenha) ?: null;
    mysqli_stmt_close($stmtSenha);

    if ($dadosSenha === null) {
        $_SESSION = [];
        session_destroy();
        header('Location: tela01.php');
        exit;
    }

    $_SESSION['senhaTrocaObrigatoria'] = (string)$dadosSenha['tipoSenhaPessoa'] !== 'U'
        || (string)$dadosSenha['validaSenhaPessoa'] < date('Y-m-d');
}

if (!empty($_SESSION['senhaTrocaObrigatoria']) && !in_array($paginaAtual, $paginasPermitidasNaTroca, true)) {
    header('Location: trocarSenha01.php');
    exit;
}

$CPF = (string)$_SESSION['CPFPessoa'];
$nomePessoa = (string)($_SESSION['nomePessoa'] ?? '');
$emailPessoa = (string)($_SESSION['emailPessoa'] ?? '');
if (empty($_SESSION['csrf_senha'])) {
    $_SESSION['csrf_senha'] = bin2hex(random_bytes(32));
}
