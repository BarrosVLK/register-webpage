<?php
header("Content-Type: application/javascript");
?>

const ddds = [
    "11", "12", "13", "14", "15", "16", "17", "18", "19",
    "21", "22", "24", "27", "28",
    "31", "32", "33", "34", "35", "37", "38",
    "41", "42", "43", "44", "45", "46",
    "47", "48", "49",
    "51", "53", "54", "55",
    "61", "62", "63", "64", "65", "66", "67", "68", "69",
    "71", "73", "74", "75", "77", "79",
    "81", "82", "83", "84", "85", "86", "87", "88", "89",
    "91", "92", "93", "94", "95", "96", "97", "98", "99"
];

function criarSelectDDD(name) {
    const select = document.createElement('select');
    select.name = name;
    const optDefault = document.createElement('option');
    optDefault.value = '';
    optDefault.textContent = 'DDD';
    optDefault.disabled = true;
    select.appendChild(optDefault);
    select.required = true;
    ddds.forEach(ddd => {
        const opt = document.createElement('option');
        opt.value = ddd;
        opt.textContent = ddd;
        select.appendChild(opt);
    });
    return select;
}

function adTelefone() {
    const container = document.getElementById('telefones');
    const novoP = document.createElement('p');
    novoP.className = 'repeatable';
    novoP.appendChild(document.createTextNode('Telefone'));
    
    novoP.appendChild(criarSelectDDD('DDDPessoa[]'));
    
    const novoInput = document.createElement('input');
    novoInput.type = 'tel';
    novoInput.maxLength = '20';
    novoInput.name = 'telefonePessoa[]';
    novoInput.placeholder = '00000-0000';
    novoInput.required = true;
    
    const novoBotao = document.createElement('button');
    novoBotao.type = 'button';
    novoBotao.className = 'secondary';
    novoBotao.textContent = 'X';
    novoBotao.onclick = () => {
        novoP.remove();
    };

    novoP.appendChild(novoInput);
    novoP.appendChild(novoBotao);
    container.appendChild(novoP);
}
