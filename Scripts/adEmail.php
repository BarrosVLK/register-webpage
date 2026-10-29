<?php
header('Content-Type: application/javascript; charset=utf-8');
?>
function adEmail() {
    const container = document.getElementById('emails');
    const index = container.querySelectorAll('input[type="email"]').length + 1;
    const row = document.createElement('div');
    row.className = 'email-entry';

    const label = document.createElement('label');
    label.textContent = 'E-mail adicional';
    const email = document.createElement('input');
    email.type = 'email';
    email.name = 'emails[]';
    email.maxLength = 150;
    email.placeholder = 'outro@exemplo.com';
    email.autocomplete = 'email';
    email.required = true;

    const priorityLabel = document.createElement('label');
    priorityLabel.className = 'email-priority';
    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = 'emailPrioritario';
    radio.value = String(index);
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'prioritarios[]';
    hidden.value = '0';
    radio.addEventListener('change', () => {
        container.querySelectorAll('input[name="prioritarios[]"]').forEach(item => item.value = '0');
        hidden.value = '1';
    });
    priorityLabel.append(radio, document.createTextNode(' Usar como e-mail prioritário'));

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'secondary';
    remove.textContent = 'Remover';
    remove.addEventListener('click', () => {
        const wasPriority = hidden.value === '1';
        row.remove();
        if (wasPriority) {
            const firstRadio = container.querySelector('input[type="radio"]');
            const firstPriority = container.querySelector('input[name="prioritarios[]"]');
            if (firstRadio && firstPriority) {
                firstRadio.checked = true;
                firstPriority.value = '1';
            }
        }
    });

    label.appendChild(email);
    row.append(label, priorityLabel, hidden, remove);
    container.appendChild(row);
}
