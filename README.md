# Lotofácil - Gerador de Apostas

Bem-vindo ao projeto **Lotofácil**, um sistema web desenvolvido para gerar apostas inteligentes para a Lotofácil, uma das loterias mais populares do Brasil. Este projeto foi criado com PHP e MySQL (MariaDB), utilizando Bootstrap para uma interface moderna e responsiva, e é hospedado na Hostinger no domínio `lotofacil.lidera.srv.br`. O objetivo é fornecer uma ferramenta interativa para usuários gerarem jogos, analisarem a "temperatura" dos números (frequência de sorteio) e gerenciarem suas apostas, com suporte a dois tipos de perfil: **Administrador** e **Usuário**.

## Sobre o Projeto

O sistema foi projetado para atender às necessidades de apostadores da Lotofácil, oferecendo funcionalidades como:
- **Geração de Jogos**: Cria apostas personalizadas com base em estratégias como números fixos, excluídos e análise de frequência.
- **Temperatura dos Números**: Exibe uma análise visual dos números mais e menos sorteados nos últimos 50 concursos, com categorias (quentes, mornos, frios, congelados).
- **Simulação de Previsões**: Mostra o desempenho de previsões baseadas em frequência para os últimos 20 concursos.
- **Gerenciamento de Apostas**: Armazena os jogos gerados no banco de dados, associados ao perfil do usuário, com exportação em PDF e TXT.
- **Painel de Admin**: Permite configurar parâmetros do sistema e adicionar resultados de concursos.

O projeto foi desenvolvido no Visual Studio Code, versionado no GitHub e implantado automaticamente na Hostinger via integração com o repositório.

### Tecnologias Utilizadas
- **Backend**: PHP 8.x
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Bibliotecas**: Chart.js (gráficos), TCPDF (geração de PDFs)
- **Controle de Versão**: Git/GitHub
- **Hospedagem**: Hostinger

### Estrutura do Projeto
```
/lotofacil
├── index.php                # Página inicial (dashboard)
├── login.php                # Página de login
├── logout.php               # Script de logout
├── admin.php                # Painel de administração
├── gerar_jogos.php          # Geração de jogos
├── temperatura.php          # Análise de temperatura dos números
├── adicionar_resultado.php  # Adição de resultados de concursos
├── jogos_gerados.php        # Visualização de jogos gerados
├── config.php               # Configurações do sistema
├── includes/
│   ├── db.php              # Conexão com o banco de dados
│   ├── auth.php            # Funções de autenticação
│   ├── functions.php       # Funções de lógica do sistema
├── assets/
│   ├── css/
│   │   └── style.css       # Estilos personalizados
│   ├── js/
│   │   └── script.js       # Scripts JavaScript
├── vendor/
│   └── tcpdf/              # Biblioteca TCPDF para PDFs
└── README.md               # Documentação do projeto
```

## Funcionalidades

### 1. Geração de Jogos
- Permite criar jogos com 15 a 20 números.
- Suporta números fixos e excluídos definidos pelo usuário.
- Gera arquivos PDF e TXT com os jogos.
- Salva os jogos no banco de dados, associados ao usuário logado.

### 2. Temperatura dos Números
- Analisa a frequência dos números nos últimos 50 concursos.
- Classifica em:
  - **Quentes** (12 mais frequentes, com os 4 primeiros destacados).
  - **Mornos** (13º ao 18º).
  - **Frios** (19º ao 23º).
  - **Congelados** (menos frequentes ou nunca sorteados).
- Interface interativa com cores e gráfico de acertos.

### 3. Adição de Resultados
- Restrita a administradores.
- Permite inserir os 15 números sorteados de um concurso no banco de dados.

### 4. Jogos Gerados
- Exibe apenas os jogos do usuário logado.
- Oferece links para download de PDF e TXT.

### 5. Painel de Admin
- Configuração de parâmetros do sistema (ex.: estratégias de geração).
- Salva configurações no banco de dados.

## Guia de Instalação

### Pré-requisitos
- **Servidor Web**: Apache ou Nginx com PHP 8.x instalado.
- **Banco de Dados**: MySQL ou MariaDB.
- **Git**: Para clonar o repositório.
- **Composer** (opcional): Para instalar a biblioteca TCPDF, se não for baixada manualmente.
- **Hospedagem**: Configurada para implantar repositórios Git (ex.: Hostinger).

### Passo a Passo

#### 1. Clonar o Repositório
Clone o repositório do GitHub para seu ambiente local ou diretamente na hospedagem:
```bash
git clone https://github.com/seu-usuario/lotofacil.git
cd lotofacil
```

#### 2. Configurar o Banco de Dados
1. Crie o banco de dados na sua hospedagem (ex.: Hostinger):
   - Nome: `lotofacil`
   - Usuário: `lotofacil`
   - Senha: `lotofacil`
2. Execute o script SQL abaixo para criar as tabelas:
```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    perfil ENUM('admin', 'user') NOT NULL
);

CREATE TABLE jogos_gerados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    lote_id VARCHAR(14) NOT NULL,
    jogos JSON NOT NULL,
    data_geracao VARCHAR(20) NOT NULL,
    concurso INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concurso INT UNIQUE NOT NULL,
    numeros JSON NOT NULL
);

CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL
);

-- Inserir usuários iniciais
INSERT INTO usuarios (email, senha, nome, perfil) VALUES
('admin@provedor.com.br', '$2y$10$[HASH]', 'Administrador', 'admin'),
('usuario@gmail.com', '$2y$10$[HASH]', 'Usuario', 'user');
```
**Nota**: Substitua `[HASH]` pelo hash gerado com `password_hash('lotofacil', PASSWORD_DEFAULT)` em PHP.

#### 3. Configurar o Arquivo `config.php`
Edite `config.php` com as credenciais do banco de dados:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lotofacil');
define('DB_USER', 'lotofacil');
define('DB_PASS', 'lotofacil');
define('BASE_URL', '');
?>
```

#### 4. Instalar Dependências
- **TCPDF**: Baixe manualmente e coloque em `vendor/tcpdf/` ou use Composer:
```bash
composer require tecnickcom/tcpdf
```
- Bootstrap e Chart.js são incluídos via CDN nos arquivos PHP.

#### 5. Fazer Upload para a Hostinger
1. Suba os arquivos para o GitHub:
   - Use o Visual Studio Code para commitar e fazer push.
2. Configure a implantação na Hostinger:
   - No painel da Hostinger, vá para "Gerenciar" > "Git".
   - Conecte o repositório GitHub e defina o diretório como `/home/u700101648/domains/lidera.srv.br/public_html/lotofacil`.
   - Implante o branch principal.

#### 6. Ajustar Permissões
- Certifique-se de que os diretórios têm permissão 755 e os arquivos 644:
```bash
chmod -R 755 .
find . -type f -exec chmod 644 {} \;
```

#### 7. Testar o Sistema
- Acesse `http://lotofacil.lidera.srv.br` no navegador.
- Faça login com as credenciais de administrador ou usuário.
- Verifique todas as funcionalidades (geração de jogos, temperatura, etc.).

### Resolução de Problemas
- **Erro de Conexão ao Banco**: Verifique as credenciais em `config.php`.
- **Página em Branco**: Ative o modo de depuração no PHP adicionando ao início de `config.php`:
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```
- **Permissões**: Confirme as permissões dos arquivos na Hostinger.

## Contribuição
1. Faça um fork do repositório.
2. Crie uma branch para sua feature:
```bash
git checkout -b minha-feature
```
3. Commit suas mudanças e envie um pull request.

## Licença
Este projeto é de uso privado e não possui licença pública. Todos os direitos reservados a Bruno Anderson.

## Contato

Para dúvidas ou suporte, entre em contato com:
- **Email**: bruno@lideratecnologia.com.br
- **Responsável**: Bruno Anderson

---
