-- ============================================================
-- Banco do Rail View (MariaDB do XAMPP)
--
-- Como usar: phpMyAdmin > Importar > escolher este arquivo > Executar
-- Login padrão: usuário caio123 / senha caio123 (administrador)
--
-- Este script NÃO apaga nada e pode ser executado quantas vezes quiser:
--   * Banco novo  -> a PARTE 1 cria tudo.
--   * Banco antigo -> a PARTE 2 completa o que estiver faltando,
--                     mantendo todos os dados já cadastrados.
-- ============================================================


-- ============================================================
-- PARTE 1: criação do banco e das tabelas
-- ============================================================

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

    CONSTRAINT usuario_ibfk_1
        FOREIGN KEY (id_perfil)
        REFERENCES Perfil(id_perfil)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Usuário administrador padrão (senha: caio123)
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

    -- Se o usuário responsável for excluído, o trem fica sem responsável (NULL)
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

    -- RESTRICT: não deixa excluir um trem que ainda tem sensores
    CONSTRAINT sensor_ibfk_1
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

    -- RESTRICT: não deixa excluir um trem que está em alguma rota
    CONSTRAINT historico_rota_ibfk_1
        FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    -- CASCADE: excluir a rota apaga só o vínculo dela com o trem
    CONSTRAINT historico_rota_ibfk_2
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

    CONSTRAINT alerta_ibfk_1
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

    CONSTRAINT manutencao_ibfk_1
        FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);


-- ============================================================
-- PARTE 2: atualização de bancos antigos
-- Em um banco novo, estes comandos não mudam nada.
-- Em um banco antigo, completam o que falta SEM apagar dados.
-- ============================================================

-- 1) Acentos: converte o banco e as tabelas antigas (latin1) para utf8mb4.
--    Os textos já gravados são convertidos junto, sem perda.
ALTER DATABASE rail_view_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Perfil         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Usuario        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Trem           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Sensor         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Rota           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Historico_Rota CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Alerta         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Manutencao     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2) Endereço do usuário (usado no cadastro com CEP e no Perfil).
--    IF NOT EXISTS: só cria a coluna se ela ainda não existir.
ALTER TABLE Usuario
    ADD COLUMN IF NOT EXISTS cep VARCHAR(9) NULL AFTER id_perfil,
    ADD COLUMN IF NOT EXISTS logradouro VARCHAR(150) NULL AFTER cep,
    ADD COLUMN IF NOT EXISTS bairro VARCHAR(100) NULL AFTER logradouro,
    ADD COLUMN IF NOT EXISTS cidade VARCHAR(100) NULL AFTER bairro,
    ADD COLUMN IF NOT EXISTS uf CHAR(2) NULL AFTER cidade;

-- 3) Responsável pelo trem (usado na lista, no cadastro e na edição de trens).
ALTER TABLE Trem
    ADD COLUMN IF NOT EXISTS id_usuario INT NULL AFTER numero_vagoes;

-- Se algum trem apontar para um usuário que não existe mais, fica sem responsável.
-- (Sem isso, a chave estrangeira abaixo não poderia ser criada.)
UPDATE Trem
SET id_usuario = NULL
WHERE id_usuario IS NOT NULL
  AND id_usuario NOT IN (SELECT id_usuario FROM Usuario);

-- 4) Regras de exclusão das chaves estrangeiras.
--    Para mudar a regra, removemos a chave (se existir) e criamos de novo.
ALTER TABLE Trem DROP FOREIGN KEY IF EXISTS fk_trem_usuario;
ALTER TABLE Trem
    ADD CONSTRAINT fk_trem_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario(id_usuario)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE Sensor DROP FOREIGN KEY IF EXISTS sensor_ibfk_1;
ALTER TABLE Sensor
    ADD CONSTRAINT sensor_ibfk_1
        FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE RESTRICT
        ON UPDATE CASCADE;

ALTER TABLE Historico_Rota DROP FOREIGN KEY IF EXISTS historico_rota_ibfk_1;
ALTER TABLE Historico_Rota
    ADD CONSTRAINT historico_rota_ibfk_1
        FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE RESTRICT
        ON UPDATE CASCADE;
