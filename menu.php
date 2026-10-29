<?php require __DIR__ . '/ses_start.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLK | Início</title>
    <link rel="stylesheet" href="site.css?v=20261024-3">
</head>
<body class="dashboard-page">
    <header class="site-header">
        <a class="site-name" href="menu.php">VLK</a>
        <nav class="site-nav" aria-label="Menu principal">
            <a href="lisPes01.php">Lista de usuários</a>
            <a href="trocarSenha01.php">Trocar senha</a>
            <a class="nav-exit" href="sair.php">Sair</a>
        </nav>
    </header>
    <main class="dashboard-main">
        <div class="dashboard-cover" aria-hidden="true"></div>
        <section class="welcome-block">
            <p class="auth-eyebrow">Área da conta</p>
            <h1>Bem-vindo, <?= htmlspecialchars((string)($_SESSION['nomePessoa'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="auth-subtitle">É muito bom ter você de volta. O que vamos fazer hoje?</p>
        </section>
        <section class="dashboard-actions" aria-label="Atalhos">
            <a class="dashboard-action" href="http://10.67.120.8/phpmyadmin">
                <span class="action-kicker">Banco de dados</span>
                <strong>Abrir phpMyAdmin</strong>
                <span>Gerencie e consulte os bancos de dados.</span>
            </a>
            <a class="dashboard-action" href="http://10.67.120.8">
                <span class="action-kicker">Arquivos</span>
                <strong>Meus projetos</strong>
                <span>Acesse seus arquivos e projetos.</span>
            </a>
            <a class="dashboard-action" href="trocarSenha01.php">
                <span class="action-kicker">Conta</span>
                <strong>Alterar senha</strong>
                <span>Atualize a senha da sua conta.</span>
            </a>
        </section>
    </main>
</body>
</html>
