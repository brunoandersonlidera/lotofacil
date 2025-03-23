// Função para inicializar eventos quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function () {
    console.log('Projeto Lotofácil carregado com sucesso!');

    // Identifica a página atual pelo nome do arquivo no URL
    const currentPage = window.location.pathname.split('/').pop();

    // Inicializa funcionalidades específicas por página
    switch (currentPage) {
        case 'index.php':
            initIndexPage();
            break;
        case 'temperatura.php':
            initTemperaturaPage();
            break;
        case 'adicionar_resultado.php':
            initAdicionarResultadoPage();
            break;
        case 'gerar_jogos.php':
            initGerarJogosPage();
            break;
        case 'jogos_gerados.php':
            initJogosGeradosPage();
            break;
        case 'admin.php':
            initAdminPage();
            break;
        case 'login.php':
            initLoginPage();
            break;
    }
});

// Funções específicas para cada página
function initIndexPage() {
    // Nada específico por enquanto, mas pode adicionar interatividade aos cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('click', function () {
            const link = this.querySelector('a');
            if (link) {
                window.location.href = link.href;
            }
        });
    });
}

function initTemperaturaPage() {
    // A inicialização do Chart.js já está inline no temperatura.php
    // Adiciona evento de clique nos números para exibir frequência (já implementado no PHP)
    const tempNumbers = document.querySelectorAll('.temp-number');
    tempNumbers.forEach(number => {
        number.addEventListener('mouseover', function () {
            this.style.transform = 'scale(1.1)';
        });
        number.addEventListener('mouseout', function () {
            this.style.transform = 'scale(1)';
        });
    });
}

function initAdicionarResultadoPage() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const numerosInput = document.getElementById('numeros').value;
            const numeros = numerosInput.split(',').map(num => parseInt(num.trim())).filter(num => !isNaN(num));
            if (numeros.length !== 15 || Math.max(...numeros) > 25 || Math.min(...numeros) < 1) {
                e.preventDefault();
                alert('Por favor, insira exatamente 15 números entre 1 e 25, separados por vírgula.');
            }
        });
    }
}

function initGerarJogosPage() {
    // Validação do formulário de geração de jogos (assumindo que existe)
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const qtdNumeros = parseInt(document.getElementById('quantidade_numeros').value);
            const qtdJogos = parseInt(document.getElementById('quantidade_jogos').value);
            const fixos = document.getElementById('numeros_fixos').value.split(',').map(num => parseInt(num.trim())).filter(num => !isNaN(num));
            const excluidos = document.getElementById('numeros_excluidos').value.split(',').map(num => parseInt(num.trim())).filter(num => !isNaN(num));

            if (qtdNumeros < 15 || qtdNumeros > 20) {
                e.preventDefault();
                alert('A quantidade de números deve estar entre 15 e 20.');
                return;
            }
            if (qtdJogos < 1) {
                e.preventDefault();
                alert('A quantidade de jogos deve ser pelo menos 1.');
                return;
            }
            if (fixos.length > qtdNumeros) {
                e.preventDefault();
                alert('O número de fixos não pode exceder a quantidade de números por jogo.');
                return;
            }
            if (fixos.some(num => num < 1 || num > 25) || excluidos.some(num => num < 1 || num > 25)) {
                e.preventDefault();
                alert('Os números fixos e excluídos devem estar entre 1 e 25.');
                return;
            }
        });
    }
}

function initJogosGeradosPage() {
    // Pode adicionar interatividade para expandir detalhes dos jogos
    console.log('Página de jogos gerados carregada.');
}

function initAdminPage() {
    // Validação simples para o formulário de configurações
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const chave = document.getElementById('chave').value.trim();
            const valor = document.getElementById('valor').value.trim();
            if (!chave || !valor) {
                e.preventDefault();
                alert('Por favor, preencha ambos os campos: Chave e Valor.');
            }
        });
    }
}

function initLoginPage() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value.trim();
            if (!email || !senha) {
                e.preventDefault();
                alert('Por favor, preencha o email e a senha.');
            }
        });
    }
}

// Função reutilizável para exibir mensagens (opcional)
function showMessage(message, type = 'info') {
    const msgDiv = document.createElement('div');
    msgDiv.className = `alert alert-${type} alert-dismissible fade show`;
    msgDiv.role = 'alert';
    msgDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.insertBefore(msgDiv, document.body.firstChild);
    setTimeout(() => msgDiv.remove(), 5000);
}

console.log('Script carregado');

function toggleFixo(numero) {
    console.log('Toggle fixo: ' + numero);
    try {
        const el = document.getElementById('fixo-' + numero);
        if (el && !el.classList.contains('excluido')) {
            el.classList.toggle('fixo');
            updateNumeros('numeros_fixos', '.fixo');
        }
    } catch (e) {
        console.error('Erro em toggleFixo: ' + e.message);
    }
}

function toggleExcluido(numero) {
    console.log('Toggle excluido: ' + numero);
    try {
        const el = document.getElementById('excluido-' + numero);
        if (el && !el.classList.contains('fixo')) {
            el.classList.toggle('excluido');
            updateNumeros('numeros_excluidos', '.excluido');
        }
    } catch (e) {
        console.error('Erro em toggleExcluido: ' + e.message);
    }
}

function updateNumeros(inputId, selector) {
    console.log('Atualizando ' + inputId);
    try {
        const nums = Array.from(document.querySelectorAll(selector)).map(el => el.textContent.trim());
        const input = document.getElementById(inputId);
        if (input) {
            input.value = nums.join(', ');
            console.log(inputId + ' atualizado para: ' + input.value);
        }
    } catch (e) {
        console.error('Erro em updateNumeros: ' + e.message);
    }
}

function toggleEstrategia(estrategia) {
    console.log('Toggle estrategia: ' + estrategia);
    try {
        const btn = document.getElementById('btn-' + estrategia);
        const input = document.getElementById('estrategia-' + estrategia);
        if (btn && input) {
            if (btn.classList.contains('off')) {
                btn.classList.remove('off');
                btn.classList.add('on');
                input.value = estrategia;
            } else {
                btn.classList.remove('on');
                btn.classList.add('off');
                input.value = '';
            }
            console.log('Estrategia ' + estrategia + ' agora é: ' + input.value);
        }
    } catch (e) {
        console.error('Erro em toggleEstrategia: ' + e.message);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado');
    try {
        const gerarTab = document.getElementById('gerar-tab');
        if (gerarTab) gerarTab.click(); // Ativa a aba "Gerar Jogos"
        toggleEstrategia('frequencia');
        toggleEstrategia('sequencias');
        toggleEstrategia('soma');
    } catch (e) {
        console.error('Erro no DOMContentLoaded: ' + e.message);
    }
});