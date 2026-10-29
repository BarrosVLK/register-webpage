<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar conta</title>
    <link rel="stylesheet" href="site.css?v=20261024-3">
    <script src="Scripts/adEmail.php" defer></script>
    <script src="Scripts/adTel.php" defer></script>
    <script src="Scripts/adEndereco.php" defer></script>
</head>
<body class="auth-page">
<main class="auth-card auth-card--wide">
    <p class="auth-eyebrow">Vamos começar</p>
    <h1>Criar sua conta</h1>
    <p class="auth-subtitle">Preencha seus dados para acessar o sistema.</p>
    <p id="stepCaption" class="step-caption">Etapa 1 de 2 · Dados da conta</p>

    <form id="cadastro" method="POST" action="cadPes02.php" class="form-stack">
        <section id="stepAccount" class="step-panel">
            <label for="cpf">CPF</label>
            <input id="cpf" type="text" name="CPFPessoa" inputmode="numeric" autocomplete="off" maxlength="11" pattern="[0-9]{11}" placeholder="11 números, sem pontuação" required>

            <label for="nome">Nome completo</label>
            <input id="nome" type="text" name="nomeCompleto" maxlength="150" autocomplete="name" required>

            <label for="emailPrincipal">E-mail</label>
            <div id="emails">
                <div class="email-entry">
                    <input id="emailPrincipal" type="email" name="emails[]" maxlength="150" autocomplete="email" placeholder="voce@exemplo.com" required>
                    <label class="email-priority"><input id="emailPrimaryRadio" type="radio" name="emailPrioritario" value="principal" checked> Usar como e-mail prioritário</label>
                    <input type="hidden" name="prioritarios[]" value="1">
                </div>
            </div>
            <button type="button" class="secondary" id="addEmail">Adicionar outro e-mail?</button>

            <label for="senha1">Senha</label>
            <input id="senha1" type="password" name="senha1" minlength="8" maxlength="72" autocomplete="new-password" required>

            <label for="senha2">Confirmar senha</label>
            <input id="senha2" type="password" name="senha2" minlength="8" maxlength="72" autocomplete="new-password" required>

            <div class="form-actions">
                <button type="button" id="nextStep">Continuar</button>
            </div>
            <p class="muted">Já tem conta? <a href="tela01.php">Entrar</a></p>
        </section>

        <section id="stepContact" class="step-panel" hidden>
            <h2>Contato</h2>
            <p class="muted">Informe um telefone e um endereço. Você pode adicionar outros se precisar.</p>
            <label>Telefone</label>
            <div id="telefones"></div>
            <button type="button" class="secondary" id="addPhone">Adicionar outro telefone</button>

            <label>Endereço</label>
            <div id="enderecos"></div>
            <button type="button" class="secondary" id="addAddress">Adicionar outro endereço</button>

            <div class="form-actions">
                <button type="button" class="secondary" id="previousStep">Voltar</button>
                <button type="submit">Concluir cadastro</button>
            </div>
        </section>
    </form>
</main>
<script>
const form = document.getElementById('cadastro');
const accountStep = document.getElementById('stepAccount');
const contactStep = document.getElementById('stepContact');
const caption = document.getElementById('stepCaption');
const draftKey = 'cadastro-local-draft-v1';

function salvarRascunhoCadastro() {
    const draft = { fields: {}, step: contactStep.hidden ? 'account' : 'contact' };
    for (const field of form.elements) {
        if (!field.name || ['password', 'submit', 'button', 'reset'].includes(field.type)) continue;
        draft.fields[field.name] ??= [];
        draft.fields[field.name].push({ value: field.value, checked: field.checked });
    }
    try {
        sessionStorage.setItem(draftKey, JSON.stringify(draft));
    } catch (_) {
        // O formulário continua funcionando mesmo se o navegador bloquear o armazenamento da sessão.
    }
}

function restaurarRascunhoCadastro() {
    let draft;
    try {
        draft = JSON.parse(sessionStorage.getItem(draftKey) || 'null');
    } catch (_) {
        return;
    }
    if (!draft || !draft.fields) return;

    const count = name => (draft.fields[name] || []).length;
    for (let i = 1; i < count('emails[]'); i++) adEmail();
    for (let i = 0; i < Math.max(1, count('telefonePessoa[]')); i++) adTelefone();
    for (let i = 0; i < Math.max(1, count('logradouroPessoa[]')); i++) adEndereco();

    for (const [name, values] of Object.entries(draft.fields)) {
        const fields = Array.from(form.elements).filter(field => field.name === name);
        fields.forEach((field, index) => {
            const saved = values[index];
            if (!saved) return;
            field.value = saved.value;
            if (field.type === 'radio' || field.type === 'checkbox') field.checked = saved.checked;
        });
    }

    if (draft.step === 'contact') {
        accountStep.hidden = true;
        contactStep.hidden = false;
        caption.textContent = 'Etapa 2 de 2 · Contato';
    }
}

form.addEventListener('input', salvarRascunhoCadastro);
form.addEventListener('change', salvarRascunhoCadastro);
form.addEventListener('submit', salvarRascunhoCadastro);
form.addEventListener('click', () => window.setTimeout(salvarRascunhoCadastro, 0));
window.addEventListener('beforeunload', salvarRascunhoCadastro);

document.getElementById('emailPrimaryRadio').addEventListener('change', () => {
    const priorities = document.querySelectorAll('input[name="prioritarios[]"]');
    priorities.forEach(item => item.value = '0');
    priorities[0].value = '1';
});

document.getElementById('addEmail').addEventListener('click', () => {
    adEmail();
    const newEmail = document.querySelector('#emails .email-entry:last-child input[type="email"]');
    if (newEmail) newEmail.focus();
});
document.getElementById('addPhone').addEventListener('click', () => adTelefone());
document.getElementById('addAddress').addEventListener('click', () => adEndereco());

document.getElementById('nextStep').addEventListener('click', () => {
    const required = accountStep.querySelectorAll('[required]');
    for (const field of required) {
        if (!field.reportValidity()) return;
    }
    if (document.getElementById('senha1').value !== document.getElementById('senha2').value) {
        document.getElementById('senha2').setCustomValidity('As senhas não coincidem.');
        document.getElementById('senha2').reportValidity();
        return;
    }
    document.getElementById('senha2').setCustomValidity('');
    accountStep.hidden = true;
    contactStep.hidden = false;
    caption.textContent = 'Etapa 2 de 2 · Contato';
    contactStep.querySelector('select, input')?.focus();
});

document.getElementById('previousStep').addEventListener('click', () => {
    contactStep.hidden = true;
    accountStep.hidden = false;
    caption.textContent = 'Etapa 1 de 2 · Dados da conta';
    document.getElementById('cpf').focus();
});

document.getElementById('senha2').addEventListener('input', event => event.target.setCustomValidity(''));

document.addEventListener('DOMContentLoaded', () => {
    restaurarRascunhoCadastro();
    if (!document.getElementById('telefones').children.length) adTelefone();
    if (!document.getElementById('enderecos').children.length) adEndereco();
    salvarRascunhoCadastro();
});
</script>
</body>
</html>
