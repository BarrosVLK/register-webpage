<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$cpfPrefill = (string)($_SESSION['login_cpf_prefill'] ?? '');
?>
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
        <h1>Esqueceu a senha?</h1>
        <p class="auth-subtitle">Informe o CPF cadastrado. Enviaremos uma senha temporária para seu e-mail prioritário.</p>
        <form name="esqueciSenha01" action="esqueciSenha02.php" method="POST" class="form-stack">
            <label for="cpfRecuperacao">CPF</label>
            <input id="cpfRecuperacao" type="text" placeholder="Digite seu CPF" name="CPFPessoa" value="<?= htmlspecialchars($cpfPrefill, ENT_QUOTES, 'UTF-8') ?>" inputmode="numeric" maxlength="11" autocomplete="username" required>
            <input type="submit" value="Enviar instruções">
            <a class="auth-link" href="tela01.php">Voltar para o login</a>
        </form>
    </main>
</body>
</html>
