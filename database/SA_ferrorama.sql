-- ============================================================
-- Banco do Rail View
--
-- ATENÇÃO: este script APAGA o banco rail_view_db e cria tudo de novo.
-- Assim, quem importar sempre fica com a versão mais atual das tabelas.
-- (Os dados cadastrados antes serão perdidos.)
--
-- Como usar: phpMyAdmin > Importar > escolher este arquivo > Executar
-- Login padrão: usuário caio123 / senha caio123 (administrador)
-- ============================================================

DROP DATABASE IF EXISTS rail_view_db;

-- utf8mb4: guarda acentos (ç, ã, é) e emojis corretamente.
CREATE DATABASE IF NOT EXISTS rail_view_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE rail_view_db;

CREATE TABLE IF NOT EXISTS Perfil (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    nome_perfil VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO Perfil (id_perfil, nome_perfil)
VALUES
    (1, 'Administrador'),
    (2, 'Funcionario'),
    (3, 'Usuario')
ON DUPLICATE KEY UPDATE
    nome_perfil = VALUES(nome_perfil);

CREATE TABLE IF NOT EXISTS Usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    id_perfil INT NOT NULL,

    -- Endereço (opcional), preenchido pela busca de CEP no cadastro
    cep VARCHAR(9) NULL,
    logradouro VARCHAR(150) NULL,
    bairro VARCHAR(100) NULL,
    cidade VARCHAR(100) NULL,
    uf CHAR(2) NULL,

    FOREIGN KEY (id_perfil)
        REFERENCES Perfil(id_perfil)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

INSERT IGNORE INTO Usuario (
    nome,
    email,
    senha,
    id_perfil
)
VALUES (
    'caio123',
    'caio123@railview.com',
    '$2y$10$1Ne6Q8VbsDZcU/0qcupHY.eDoISn6Bbrk3hA0tl9HcT2vf4RMceni',
    1
);

CREATE TABLE IF NOT EXISTS Trem (
    id_trem INT AUTO_INCREMENT PRIMARY KEY,
    apelido VARCHAR(100) NOT NULL,
    tipo_trem VARCHAR(50) NOT NULL,
    empresa_operadora VARCHAR(150),
    modelo VARCHAR(100) NOT NULL,
    numero_vagoes INT NOT NULL,
    id_usuario INT NULL,

    CONSTRAINT fk_trem_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario(id_usuario)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Sensor (
    id_sensor INT AUTO_INCREMENT PRIMARY KEY,
    tipo_sensor VARCHAR(50) NOT NULL,
    localizacao VARCHAR(100) NOT NULL,
    status_sensor VARCHAR(20) NOT NULL,
    empresa_operadora VARCHAR(100) NOT NULL,
    id_trem INT NOT NULL,

    FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Rota (
    id_rota INT AUTO_INCREMENT PRIMARY KEY,
    nome_rota VARCHAR(150) NOT NULL,
    origem VARCHAR(150) NOT NULL,
    destino VARCHAR(150) NOT NULL,
    apelido VARCHAR(100),
    operadora VARCHAR(150) NOT NULL,
    inicio_previsto DATETIME,
    fim_previsto DATETIME,
    relatorio TEXT
);

CREATE TABLE IF NOT EXISTS Historico_Rota (
    id_historico INT AUTO_INCREMENT PRIMARY KEY,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    id_trem INT NOT NULL,
    id_rota INT NOT NULL,

    FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (id_rota)
        REFERENCES Rota(id_rota)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Alerta (
    id_alerta INT AUTO_INCREMENT PRIMARY KEY,
    mensagem VARCHAR(255) NOT NULL,
    data_alerta DATETIME NOT NULL,
    nivel VARCHAR(20),
    id_sensor INT NOT NULL,

    FOREIGN KEY (id_sensor)
        REFERENCES Sensor(id_sensor)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Manutencao (
    id_manutencao INT AUTO_INCREMENT PRIMARY KEY,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    descricao TEXT,
    id_trem INT NOT NULL,

    FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);