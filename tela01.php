<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

$erroLogin = '';
$mensagemSucesso = '';
$avisoSenhaTemporaria = '';
$cpfPrefill = (string)($_SESSION['login_cpf_prefill'] ?? '');
if (($_GET['cadastro'] ?? '') === 'sucesso') {
    $mensagemSucesso = 'Cadastro realizado. Entre com seu CPF e senha.';
} elseif (($_GET['senha'] ?? '') === 'alterada') {
    $mensagemSucesso = 'Senha alterada. Entre novamente com a nova senha.';
} elseif (($_GET['saiu'] ?? '') === '1') {
    $mensagemSucesso = 'Você saiu da sua conta.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = trim((string)($_POST['CPF'] ?? ''));
    $cpfPrefill = $cpf;
    $_SESSION['login_cpf_prefill'] = $cpf;
    $csrf = (string)($_POST['csrf'] ?? '');
    if (!hash_equals((string)$_SESSION['csrf_login'], $csrf)) {
        $erroLogin = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
    } else {
        $senha = (string)($_POST['senha'] ?? '');

        // (testando rate limit): limita a 10 envios de login por IP em uma janela de 60 segundos nesta sessão.
        $agora = time();
        $chaveRateLimit = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
        $tentativasRecentes = $_SESSION['login_rate_limit'][$chaveRateLimit] ?? [];
        $tentativasRecentes = array_values(array_filter(
            $tentativasRecentes,
            static fn($momento): bool => is_int($momento) && $momento > $agora - 60
        ));
        $rateLimitAtivo = count($tentativasRecentes) >= 10;
        if (!$rateLimitAtivo) {
            $tentativasRecentes[] = $agora;
        }
        $_SESSION['login_rate_limit'][$chaveRateLimit] = $tentativasRecentes;

        if ($rateLimitAtivo) {
            $erroLogin = 'Usuário ou senha incorretos.';
        } elseif (!preg_match('/^\d{11}$/D', $cpf) || $senha === '') {
            $erroLogin = 'Usuário ou senha incorretos.';
        } else {
            try {
                require __DIR__ . '/dbConnect.php';
                require_once __DIR__ . '/cryp2graph2.php';
                require_once __DIR__ . '/enviaEmail.php';

                $stmt = mysqli_prepare(
                    $BDconn,
                    'SELECT nomePessoa, senhaPessoa, tipoSenhaPessoa, validaSenhaPessoa, errosLoginPessoa
                     FROM pessoa2DS WHERE CPFpessoa2DS = ?'
                );
                if (!$stmt) {
                    throw new RuntimeException('Falha ao preparar consulta de login.');
                }
                mysqli_stmt_bind_param($stmt, 's', $cpf);
                if (!mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    throw new RuntimeException('Falha ao consultar credenciais.');
                }
                $result = mysqli_stmt_get_result($stmt);
                $pessoa = mysqli_fetch_assoc($result) ?: null;
                mysqli_stmt_close($stmt);

                $senhaCorreta = $pessoa !== null
                    && ChecaSenha($senha, (string)$pessoa['senhaPessoa']);
                // A expiração da senha temporária está pausada enquanto validamos o fluxo.
                if ($senhaCorreta) {
                    $reset = mysqli_prepare($BDconn, 'UPDATE pessoa2DS SET errosLoginPessoa = 0 WHERE CPFpessoa2DS = ?');
                    if ($reset) {
                        mysqli_stmt_bind_param($reset, 's', $cpf);
                        mysqli_stmt_execute($reset);
                        mysqli_stmt_close($reset);
                    }

                    session_regenerate_id(true);
                    unset($_SESSION['login_cpf_prefill'], $_SESSION['login_rate_limit']);
                    $_SESSION['CPFPessoa'] = $cpf;
                    $_SESSION['nomePessoa'] = (string)$pessoa['nomePessoa'];
                    $_SESSION['senhaTrocaObrigatoria'] = (
                        (string)$pessoa['tipoSenhaPessoa'] !== 'U'
                        || (string)$pessoa['validaSenhaPessoa'] < date('Y-m-d')
                    );
                    $_SESSION['csrf_senha'] = bin2hex(random_bytes(32));

                    header('Location: ' . ($_SESSION['senhaTrocaObrigatoria'] ? 'trocarSenha01.php' : 'menu.php'));
                    exit;
                }

                if ($pessoa !== null) {
                    $incrementa = mysqli_prepare(
                        $BDconn,
                        'UPDATE pessoa2DS SET errosLoginPessoa = LEAST(errosLoginPessoa + 1, 3) WHERE CPFpessoa2DS = ?'
                    );
                    if ($incrementa) {
                        mysqli_stmt_bind_param($incrementa, 's', $cpf);
                        mysqli_stmt_execute($incrementa);
                        mysqli_stmt_close($incrementa);
                    }

                    $consultaErros = mysqli_prepare(
                        $BDconn,
                        'SELECT errosLoginPessoa FROM pessoa2DS WHERE CPFpessoa2DS = ?'
                    );
                    if ($consultaErros) {
                        mysqli_stmt_bind_param($consultaErros, 's', $cpf);
                        mysqli_stmt_execute($consultaErros);
                        $errosResult = mysqli_stmt_get_result($consultaErros);
                        $errosRow = mysqli_fetch_assoc($errosResult) ?: [];
                        mysqli_stmt_close($consultaErros);

                        // Sem expiração temporária, uma senha R continua sendo a senha de recuperação ativa.
                        $temporariaAtiva = (string)$pessoa['tipoSenhaPessoa'] === 'R';
                        if ((int)($errosRow['errosLoginPessoa'] ?? 0) >= 3) {
                            $senhaTemporaria = ResetaSenha($BDconn, $cpf);
                            if ($senhaTemporaria !== false) {
                                if (EnviarEmailReset($BDconn, $cpf, $senhaTemporaria)) {
                                    $avisoSenhaTemporaria = 'Uma nova senha temporária foi enviada para seu e-mail prioritário.';
                                } else {
                                    error_log('Falha ao enviar senha temporaria para a conta solicitada.');
                                }
                            }
                        }
                    }
                }
                $erroLogin = 'Usuário ou senha incorretos.';
            } catch (Throwable $e) {
                error_log('Falha no fluxo de login: ' . $e->getMessage());
                $erroLogin = 'Não foi possível entrar agora. Tente novamente mais tarde.';
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
    <title>Entrar</title>
    <link rel="stylesheet" href="site.css?v=20261024-3">

</head>
<body class="auth-page auth-page--no-brand">
    <main class="auth-card">
        <p class="auth-eyebrow">Acesse sua conta</p>
        <h1>Bem-vindo de volta</h1>
        <p class="auth-subtitle">Entre com seu CPF e sua senha para continuar.</p>
        <form name="form1" action="tela01.php" method="POST" class="form-stack">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars((string)$_SESSION['csrf_login'], ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($avisoSenhaTemporaria !== ''): ?>
                <p class="erro message" role="status"><?= htmlspecialchars($avisoSenhaTemporaria, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <label for="cpfLogin">CPF</label>
            <input id="cpfLogin" type="text" name="CPF" value="<?= htmlspecialchars($cpfPrefill, ENT_QUOTES, 'UTF-8') ?>" inputmode="numeric" maxlength="11" autocomplete="username" placeholder="Digite seu CPF" required>
            <label for="senhaLogin">Senha</label>
            <input id="senhaLogin" type="password" name="senha" autocomplete="current-password" placeholder="Digite sua senha" required>
            <?php if ($erroLogin !== ''): ?>
                <p class="erro message" role="alert"><?= htmlspecialchars($erroLogin, ENT_QUOTES, 'UTF-8') ?></p>
            <?php elseif ($mensagemSucesso !== ''): ?>
                <p class="sucesso message" role="status"><?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <div class="form-actions"><input type="submit" value="Entrar"></div>
            <a class="auth-link" href="esqueciSenha01.php">Esqueci minha senha</a>
        </form>
        <p class="auth-footer">Ainda não tem conta?<a href="cadPes01.php">Criar conta</a></p>
    </main>
</body>
</html>
