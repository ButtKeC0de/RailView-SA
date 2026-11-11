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


    CONSTRAINT historico_rota_ibfk_1
        FOREIGN KEY (id_trem)
        REFERENCES Trem(id_trem)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,


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


ALTER DATABASE rail_view_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Perfil         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Usuario        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Trem           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Sensor         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Rota           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Historico_Rota CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Alerta         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Manutencao     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


ALTER TABLE Usuario
    ADD COLUMN IF NOT EXISTS cep VARCHAR(9) NULL AFTER id_perfil,
    ADD COLUMN IF NOT EXISTS logradouro VARCHAR(150) NULL AFTER cep,
    ADD COLUMN IF NOT EXISTS bairro VARCHAR(100) NULL AFTER logradouro,
    ADD COLUMN IF NOT EXISTS cidade VARCHAR(100) NULL AFTER bairro,
    ADD COLUMN IF NOT EXISTS uf CHAR(2) NULL AFTER cidade;

ALTER TABLE Trem
    ADD COLUMN IF NOT EXISTS id_usuario INT NULL AFTER numero_vagoes;


UPDATE Trem
SET id_usuario = NULL
WHERE id_usuario IS NOT NULL
  AND id_usuario NOT IN (SELECT id_usuario FROM Usuario);


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



USE rail_view_db;


INSERT INTO Usuario (nome, email, senha, id_perfil, cep, logradouro, bairro, cidade, uf) VALUES
('Joao Silva', 'joao@railview.com', '123456', 2, '89201000', 'Rua das Flores', 'Centro', 'Joinville', 'SC'),
('Maria Oliveira', 'maria@email.com', '123456', 3, '89202000', 'Rua Brasil', 'América', 'Joinville', 'SC'),
('Pedro Santos', 'pedro@email.com', '123456', 3, '89203000', 'Rua XV de Novembro', 'Atiradores', 'Joinville', 'SC'),
('Ana Costa', 'ana@railview.com', '123456', 2, '89204000', 'Rua Blumenau', 'Centro', 'Joinville', 'SC'),
('Lucas Ferreira', 'lucas@email.com', '123456', 3, '89205000', 'Rua São Paulo', 'Bucarein', 'Joinville', 'SC');


INSERT INTO Trem (apelido, tipo_trem, empresa_operadora, modelo, numero_vagoes, id_usuario) VALUES
('Expresso Norte', 'Passageiros', 'Rail Brasil', 'Siemens Vectron', 8, 2),
('Carga Pesada', 'Carga', 'TransLog', 'GE Evolution', 20, 2),
('Expresso Sul', 'Passageiros', 'Rail Brasil', 'Alstom Coradia', 6, 3),
('Trem Regional', 'Passageiros', 'Ferrovia Sul', 'CAF Civity', 5, 4),
('Carga Express', 'Carga', 'TransLog', 'EMD SD70', 18, 5);


INSERT INTO Sensor (tipo_sensor, localizacao, status_sensor, empresa_operadora, id_trem) VALUES
('Velocidade', 'Vagao 01', 'Ativo', 'Rail Brasil', 1),
('Temperatura', 'Motor principal', 'Ativo', 'Rail Brasil', 1),
('GPS', 'Cabine', 'Ativo', 'Rail Brasil', 1),
('Pressao', 'Sistema de freios', 'Manutencao', 'TransLog', 2),
('Velocidade', 'Vagao 05', 'Ativo', 'TransLog', 2),
('GPS', 'Cabine', 'Ativo', 'Rail Brasil', 3),
('Temperatura', 'Motor principal', 'Inativo', 'Ferrovia Sul', 4),
('Vibracao', 'Eixo traseiro', 'Ativo', 'TransLog', 5);

INSERT INTO Rota (nome_rota, origem, destino, apelido, operadora, inicio_previsto, fim_previsto, relatorio) VALUES
('Rota Norte', 'Joinville', 'Curitiba', 'Linha Norte', 'Rail Brasil', '2026-11-11 08:00:00', '2026-11-11 11:00:00', 'Viagem dentro da normalidade.'),
('Rota Sul', 'Joinville', 'Florianopolis', 'Linha Sul', 'Ferrovia Sul', '2026-11-11 09:00:00', '2026-11-11 14:00:00', 'Operacao sem ocorrencias.'),
('Rota Industrial', 'Curitiba', 'Sao Paulo', 'Linha Industrial', 'TransLog', '2026-11-11 10:00:00', '2026-11-11 18:00:00', 'Transporte de carga industrial.'),
('Rota Litoranea', 'Itajai', 'Joinville', 'Linha Litoranea', 'Rail Brasil', '2026-11-12 07:00:00', '2026-11-12 10:00:00', 'Rota com monitoramento ativo.'),
('Rota Central', 'Sao Paulo', 'Campinas', 'Linha Central', 'TransLog', '2026-11-12 13:00:00', '2026-11-12 16:00:00', 'Transporte realizado normalmente.');

INSERT INTO Historico_Rota (data_inicio, data_fim, id_trem, id_rota) VALUES
('2026-11-10 08:00:00', '2026-11-10 11:00:00', 1, 1),
('2026-11-10 09:00:00', '2026-11-10 14:00:00', 3, 2),
('2026-11-10 10:00:00', '2026-11-10 18:00:00', 2, 3),
('2026-11-10 07:00:00', '2026-11-10 10:00:00', 4, 4),
('2026-11-10 13:00:00', '2026-11-10 16:00:00', 5, 5);


INSERT INTO Alerta (mensagem, data_alerta, nivel, id_sensor) VALUES
('Velocidade acima do limite permitido.', '2026-11-11 08:30:00', 'Alto', 1),
('Temperatura elevada no motor.', '2026-11-11 08:45:00', 'Medio', 2),
('Falha na comunicacao GPS.', '2026-11-11 09:00:00', 'Alto', 3),
('Pressao baixa no sistema de freios.', '2026-11-11 09:15:00', 'Critico', 4),
('Vibracao acima do padrao.', '2026-11-11 09:30:00', 'Medio', 8),
('Sensor de temperatura inativo.', '2026-11-11 10:00:00', 'Baixo', 7);


INSERT INTO Manutencao (data_inicio, data_fim, descricao, id_trem) VALUES
('2026-10-01', '2026-10-03', 'Revisao geral do motor.', 1),
('2026-10-05', '2026-10-06', 'Substituicao do sistema de freios.', 2),
('2026-10-08', '2026-10-09', 'Inspecao dos sistemas eletricos.', 3),
('2026-10-12', '2026-10-14', 'Manutencao preventiva dos vagoes.', 4),
('2026-10-15', '2026-10-17', 'Reparo no sistema de transmissao.', 5),
('2026-11-11', NULL, 'Manutencao corretiva em andamento.', 2);
