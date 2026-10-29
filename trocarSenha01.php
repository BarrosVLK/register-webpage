<?php
require __DIR__ . '/ses_start.php';
$erroTroca = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals((string)$_SESSION['csrf_senha'], (string)($_POST['csrf'] ?? ''))) {
        $erroTroca = 'A sessão expirou. Atualize a página e tente novamente.';
    } else {
        $senha1 = (string)($_POST['senha1'] ?? '');
        $senha2 = (string)($_POST['senha2'] ?? '');
        if (strlen($senha1) < 8 || strlen($senha1) > 72) {
            $erroTroca = 'A senha deve ter entre 8 e 72 caracteres.';
        } elseif (!hash_equals($senha1, $senha2)) {
            $erroTroca = 'As senhas não coincidem. Tente novamente.';
        } else {
            require_once __DIR__ . '/dbConnect.php';
            require_once __DIR__ . '/cryp2graph2.php';
            $hash = FazSenha($CPF, $senha1);
            $validade = date('Y-m-d H:i:s', strtotime('+30 days'));
            $stmt = mysqli_prepare(
                $BDconn,
                "UPDATE pessoa2DS
                 SET senhaPessoa = ?, tipoSenhaPessoa = 'U', validaSenhaPessoa = ?, errosLoginPessoa = 0
                 WHERE CPFpessoa2DS = ?"
            );
            if (!$stmt) {
                $erroTroca = 'Não foi possível preparar a troca de senha.';
            } else {
                mysqli_stmt_bind_param($stmt, 'sss', $hash, $validade, $CPF);
                if (!mysqli_stmt_execute($stmt)) {
                    error_log('Falha ao atualizar senha: ' . mysqli_stmt_error($stmt));
                    $erroTroca = 'Não foi possível salvar a nova senha.';
                } else {
                    mysqli_stmt_close($stmt);
                    $_SESSION = [];
                    if (ini_get('session.use_cookies')) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
                    }
                    session_destroy();
                    header('Location: tela01.php?senha=alterada');
                    exit;
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar senha</title>
    <link rel="stylesheet" href="site.css?v=20261024-3">

</head>
<body class="auth-page">
    <main class="auth-card">
        <p class="auth-eyebrow">Segurança da conta</p>
        <h1>Crie uma nova senha</h1>
        <p class="auth-subtitle">Senhas temporárias ou expiradas precisam ser alteradas para continuar.</p>
        <form name="trocarSenha01" action="trocarSenha01.php" method="POST" class="form-stack">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars((string)$_SESSION['csrf_senha'], ENT_QUOTES, 'UTF-8') ?>">
            <label for="novaSenha">Nova senha</label>
            <input id="novaSenha" type="password" name="senha1" minlength="8" maxlength="72" required autocomplete="new-password" placeholder="Pelo menos 8 caracteres">
            <label for="confirmaSenha">Confirme a nova senha</label>
            <input id="confirmaSenha" type="password" name="senha2" minlength="8" maxlength="72" required autocomplete="new-password" placeholder="Digite novamente">
            <?php if ($erroTroca !== ''): ?>
                <p class="erro message" role="alert"><?= htmlspecialchars($erroTroca, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <input type="submit" value="Salvar nova senha">
        </form>
    </main>
</body>
</html>
