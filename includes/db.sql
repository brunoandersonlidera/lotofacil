-- Tabela de usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    perfil ENUM('admin', 'user') NOT NULL
);

-- Tabela de jogos gerados
CREATE TABLE jogos_gerados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    lote_id VARCHAR(14) NOT NULL,
    jogos JSON NOT NULL,
    data_geracao VARCHAR(20) NOT NULL,
    concurso INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de resultados
CREATE TABLE resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concurso INT UNIQUE NOT NULL,
    numeros JSON NOT NULL
);

-- Tabela de configurações (para o admin)
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL
);


INSERT INTO usuarios (email, senha, nome, perfil) VALUES
('admin@dominio.com.br', '$2a$12$Vf4jvuGHgbu7CRhEnCyhYOUMSsMwyDBPV9o3dV2qAoB7FJBFQFO7G', 'Administrador', 'admin'),
('usuario@dominio.com', '$2a$12$Vf4jvuGHgbu7CRhEnCyhYOUMSsMwyDBPV9o3dV2qAoB7FJBFQFO7G', 'Usuario', 'user');