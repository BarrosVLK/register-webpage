<?php
header("Content-Type: application/javascript");
?>

const estados = [
    ["AC", "Acre"], ["AL", "Alagoas"], ["AP", "Amapá"], ["AM", "Amazonas"],
    ["BA", "Bahia"], ["CE", "Ceará"], ["DF", "Distrito Federal"],
    ["ES", "Espírito Santo"], ["GO", "Goiás"], ["MA", "Maranhão"],
    ["MT", "Mato Grosso"], ["MS", "Mato Grosso do Sul"], ["MG", "Minas Gerais"],
    ["PA", "Pará"], ["PB", "Paraíba"], ["PR", "Paraná"], ["PE", "Pernambuco"],
    ["PI", "Piauí"], ["RJ", "Rio de Janeiro"], ["RN", "Rio Grande do Norte"],
    ["RS", "Rio Grande do Sul"], ["RO", "Rondônia"], ["RR", "Roraima"],
    ["SC", "Santa Catarina"], ["SP", "São Paulo"], ["SE", "Sergipe"], ["TO", "Tocantins"]
];

function limpa_formulario_cep(rua, bairro, cidade, uf) {

    rua.value = "";
    bairro.value = "";
    cidade.value = "";
    uf.value = "";
}

function meu_callback(conteudo, rua, bairro, cidade, uf) {

    if (!("erro" in conteudo)) {

        rua.value = conteudo.logradouro;
        bairro.value = conteudo.bairro;
        cidade.value = conteudo.localidade;
        uf.value = conteudo.uf;

        // bloqueia os campos
        rua.readOnly = true;
        bairro.readOnly = true;
        cidade.readOnly = true;

        // select usa disabled
        uf.disabled = false;

    } else {

        rua.readOnly = false;
        bairro.readOnly = false;
        cidade.readOnly = false;
        uf.disabled = false;
        
        limpa_formulario_cep(rua, bairro, cidade, uf);
        alert("CEP não encontrado.");
    }
}

function pesquisacep(valor, rua, bairro, cidade, uf) {

    var cep = valor.replace(/\D/g, '');

    if (cep != "") {

        var validacep = /^[0-9]{8}$/;

        if (validacep.test(cep)) {

            rua.value = "...";
            bairro.value = "...";
            cidade.value = "...";
            uf.value = "...";

            fetch("https://viacep.com.br/ws/" + cep + "/json/")
                .then(response => response.json())
                .then(conteudo => {
                    meu_callback(conteudo, rua, bairro, cidade, uf);
                });

        } else {

            limpa_formulario_cep(rua, bairro, cidade, uf);
            alert("Formato de CEP inválido.");
        }

    } else {

        limpa_formulario_cep(rua, bairro, cidade, uf);
    }
}

function criarSelectEstado(name) {

    const select = document.createElement('select');
    select.name = name;

    const optDefault = document.createElement('option');
    optDefault.value = '';
    optDefault.textContent = 'Estado';

    select.appendChild(optDefault);

    estados.forEach(([sigla, nome]) => {

        const opt = document.createElement('option');

        opt.value = sigla;
        opt.textContent = sigla + ' - ' + nome;

        select.appendChild(opt);
    });

    return select;
}

function adEndereco() {

    const container = document.getElementById('enderecos');
    const novoDiv = document.createElement('div');
    novoDiv.className = 'repeatable';

    // título
    const titulo = document.createElement('p');
    titulo.textContent = "Endereço:";
    novoDiv.appendChild(titulo);

    // estado
    const pEstado = document.createElement('p');
    pEstado.textContent = "Estado: ";

    const selectEstado = criarSelectEstado('estadoPessoa[]');
    selectEstado.required = true;

    pEstado.appendChild(selectEstado);
    novoDiv.appendChild(pEstado);

    // cidade
    const pCidade = document.createElement('p');
    pCidade.textContent = "Cidade: ";

    const inputCidade = document.createElement('input');
    inputCidade.type = 'text';
    inputCidade.maxLength = '200';
    inputCidade.name = 'cidadePessoa[]';
    inputCidade.placeholder = 'Cidade';
    inputCidade.required = true;

    pCidade.appendChild(inputCidade);
    novoDiv.appendChild(pCidade);

    // logradouro
    const pLog = document.createElement('p');
    pLog.textContent = "Logradouro: ";
    
    const inputLog = document.createElement('input');
    inputLog.type = 'text';
    inputLog.maxLength = '100';
    inputLog.name = 'logradouroPessoa[]';
    inputLog.placeholder = 'Ex: Avenida, Travessa...';
    inputLog.required = true;
    
    pLog.appendChild(inputLog);
    novoDiv.appendChild(pLog);

    // bairro
    const pBairro = document.createElement('p');
    pBairro.textContent = "Bairro: ";
    const inputBairro = document.createElement('input');
    inputBairro.type = 'text';
    inputBairro.maxLength = '200';
    inputBairro.name = 'bairroPessoa[]';
    inputBairro.placeholder = 'Bairro';
    
    pBairro.appendChild(inputBairro);
    novoDiv.appendChild(pBairro);
    
    
    // número
    const pNum = document.createElement('p');
    pNum.textContent = "Número: ";
    
    const inputNum = document.createElement('input');
    inputNum.type = 'text';
    inputNum.maxLength = '5';
    inputNum.name = 'numPessoa[]';
    inputNum.placeholder = 'Nº';
    
    pNum.appendChild(inputNum);
    novoDiv.appendChild(pNum);
    
    
    // complemento
    const pComp = document.createElement('p');
    pComp.textContent = "Complemento: ";

    const inputComp = document.createElement('input');
    inputComp.type = 'text';
    inputComp.maxLength = '100';
    inputComp.name = 'complementoPessoa[]';
    inputComp.placeholder = 'Apto, Bloco...';

    pComp.appendChild(inputComp);
    novoDiv.appendChild(pComp);

    // CEP
    const pCep = document.createElement('p');
    pCep.textContent = "CEP: ";

    const inputCep = document.createElement('input');

    inputCep.type = 'text';
    inputCep.minLength = '8';
    inputCep.maxLength = '8';
    inputCep.name = 'CEPPessoa[]';
    inputCep.placeholder = '00000000';

    // busca CEP
    inputCep.onblur = function () {

        pesquisacep(
            inputCep.value,
            inputLog,
            inputBairro,
            inputCidade,
            selectEstado
        );
    };

    pCep.appendChild(inputCep);
    novoDiv.appendChild(pCep);

    // botão apagar
    const novoBotao = document.createElement('button');

    novoBotao.type = 'button';
    novoBotao.className = 'secondary';
    novoBotao.textContent = 'Apagar Endereço';

    novoBotao.onclick = () => {
        novoDiv.remove();
    };

    novoDiv.appendChild(novoBotao);

    // adiciona na tela
    container.appendChild(novoDiv);
}
