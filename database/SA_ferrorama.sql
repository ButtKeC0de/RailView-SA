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
    operadora VARCHAR(150) NOT NULL
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

DROP TABLE IF EXISTS Alerta;
DROP TABLE IF EXISTS Leitura_Sensor;
DROP TABLE IF EXISTS Limite_Sensor;
DROP TABLE IF EXISTS Manutencao;

ALTER TABLE Rota
    DROP COLUMN IF EXISTS apelido,
    DROP COLUMN IF EXISTS inicio_previsto,
    DROP COLUMN IF EXISTS fim_previsto,
    DROP COLUMN IF EXISTS relatorio;

ALTER DATABASE rail_view_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Perfil         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Usuario        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Trem           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Sensor         CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Rota           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Historico_Rota CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

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

INSERT IGNORE INTO Usuario (id_usuario, nome, email, senha, id_perfil, cep, logradouro, bairro, cidade, uf)
VALUES
    (1,  'caio123',          'caio123@railview.com',  '$2y$10$1Ne6Q8VbsDZcU/0qcupHY.eDoISn6Bbrk3hA0tl9HcT2vf4RMceni', 1, NULL, NULL, NULL, NULL, NULL),
    (2,  'carlos123',        'carlos123@railview.com','$2y$10$xGQhv/D39jFs7005Z/qlOu.xxld9/KgakRb8ORae46bv38cqXdW.u', 2, '89201-000', 'Rua do Príncipe', 'Centro', 'Joinville', 'SC'),
    (3,  'lucas123',         'lucas123@railview.com', '$2y$10$q5m7nyt6A.zydE3kRX8Eq.Bc/DT9CItizxfHSZXYL5PM01YGo5Ocu', 3, '80010-000', 'Rua XV de Novembro', 'Centro', 'Curitiba', 'PR'),
    (4,  'Carla Mendes',     'carla@railview.com',    '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, '88010-000', 'Rua Felipe Schmidt', 'Centro', 'Florianópolis', 'SC'),
    (5,  'Diego Rocha',      'diego@railview.com',    '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 2, '89010-000', 'Rua XV de Novembro', 'Centro', 'Blumenau', 'SC'),
    (6,  'Elisa Martins',    'elisa@railview.com',    '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 2, NULL, NULL, NULL, NULL, NULL),
    (7,  'Fábio Nunes',      'fabio@railview.com',    '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 2, '83203-000', 'Rua Professor Cleto', 'Centro', 'Paranaguá', 'PR'),
    (8,  'Gabriela Alves',   'gabriela@railview.com', '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 2, NULL, NULL, NULL, NULL, NULL),
    (9,  'Henrique Costa',   'henrique@railview.com', '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 2, '89251-000', 'Rua Reinoldo Rau', 'Centro', 'Jaraguá do Sul', 'SC'),
    (10, 'Isabela Ferreira', 'isabela@railview.com',  '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 2, NULL, NULL, NULL, NULL, NULL),
    (11, 'João Pereira',     'joao@railview.com',     '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL),
    (12, 'Karina Ribeiro',   'karina@railview.com',   '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, '01001-000', 'Praça da Sé', 'Sé', 'São Paulo', 'SP'),
    (13, 'Lucas Barbosa',    'lucas@railview.com',    '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL),
    (14, 'Mariana Dias',     'mariana@railview.com',  '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL),
    (15, 'Nicolas Teixeira', 'nicolas@railview.com',  '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, '89218-000', 'Rua Blumenau', 'América', 'Joinville', 'SC'),
    (16, 'Olivia Cardoso',   'olivia@railview.com',   '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL),
    (17, 'Pedro Gomes',      'pedro@railview.com',    '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL),
    (18, 'Rafaela Moreira',  'rafaela@railview.com',  '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL),
    (19, 'Samuel Araújo',    'samuel@railview.com',   '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL),
    (20, 'Tatiane Lopes',    'tatiane@railview.com',  '$2y$10$6gX2c97wUqrHDnQ7GF89w.A.w0ogdgXo3yi.ablbjq/QBAqhX8HQe', 3, NULL, NULL, NULL, NULL, NULL);

INSERT IGNORE INTO Usuario (nome, email, senha, id_perfil)
VALUES
    ('caio123',   'caio123@railview.com',   '$2y$10$1Ne6Q8VbsDZcU/0qcupHY.eDoISn6Bbrk3hA0tl9HcT2vf4RMceni', 1),
    ('carlos123', 'carlos123@railview.com', '$2y$10$xGQhv/D39jFs7005Z/qlOu.xxld9/KgakRb8ORae46bv38cqXdW.u', 2),
    ('lucas123',  'lucas123@railview.com',  '$2y$10$q5m7nyt6A.zydE3kRX8Eq.Bc/DT9CItizxfHSZXYL5PM01YGo5Ocu', 3);

INSERT IGNORE INTO Trem (id_trem, apelido, tipo_trem, empresa_operadora, modelo, numero_vagoes, id_usuario)
VALUES
    (1,  'Expresso Sul',       'Carga',       'Rumo',          'AC44',     40, 1),
    (2,  'Litorânea',          'Passageiros', 'MRS',           'GT46',      8, 2),
    (3,  'Serra Verde',        'Passageiros', 'Serra Verde',   'G12',       6, 3),
    (4,  'Gigante do Porto',   'Carga',       'Rumo',          'SD70',     60, 4),
    (5,  'Vale Azul',          'Misto',       'VLI',           'C30',      20, 5),
    (6,  'Cargueiro Norte',    'Carga',       'VLI',           'Dash 9',   55, 6),
    (7,  'Estrela da Manhã',   'Passageiros', 'CPTM',          'Série 9500', 8, 7),
    (8,  'Trem do Café',       'Misto',       'MRS',           'U20C',     15, 8),
    (9,  'Rio Doce',           'Carga',       'Vale',          'ES58',     80, 9),
    (10, 'Expresso Capital',   'Passageiros', 'CPTM',          'Série 8000', 8, 10),
    (11, 'Granel 01',          'Carga',       'Rumo',          'AC44',     45, 1),
    (12, 'Granel 02',          'Carga',       'Rumo',          'AC44',     45, 2),
    (13, 'Bandeirante',        'Passageiros', 'TIC',           'Série 7000', 7, 3),
    (14, 'Mineiro',            'Misto',       'Vale',          'BB40',     25, 4),
    (15, 'Paraná Express',     'Carga',       'Rumo',          'SD40',     50, 5),
    (16, 'Catarinense',        'Passageiros', 'MRS',           'GT26',      6, 6),
    (17, 'Ferro Forte',        'Carga',       'VLI',           'SD70',     70, 7),
    (18, 'Turístico do Vale',  'Passageiros', 'Serra Verde',   'G22',       5, 8),
    (19, 'Litoral Norte',      'Misto',       'MRS',           'C36',      18, 9),
    (20, 'Corujão',            'Carga',       'Rumo',          'Dash 9',   65, 10);

INSERT IGNORE INTO Sensor (id_sensor, tipo_sensor, localizacao, status_sensor, empresa_operadora, id_trem)
VALUES
    (1,  'Temperatura',  'Motor A',            'Ativo',      'Rumo',        1),
    (2,  'Velocidade',   'Eixo 01',            'Ativo',      'MRS',         2),
    (3,  'Presença',     'Vagão 03',           'Ativo',      'Serra Verde', 3),
    (4,  'Vibração',     'Truque dianteiro',   'Manutenção', 'Rumo',        4),
    (5,  'Temperatura',  'Freio traseiro',     'Ativo',      'VLI',         5),
    (6,  'GPS',          'Cabine',             'Ativo',      'VLI',         6),
    (7,  'Velocidade',   'Eixo 02',            'Inativo',    'CPTM',        7),
    (8,  'Pressão',      'Sistema de freio',   'Ativo',      'MRS',         8),
    (9,  'Peso',         'Vagão 10',           'Ativo',      'Vale',        9),
    (10, 'Presença',     'Porta 02',           'Manutenção', 'CPTM',       10),
    (11, 'Temperatura',  'Motor B',            'Ativo',      'Rumo',       11),
    (12, 'Vibração',     'Eixo 04',            'Inativo',    'Rumo',       12),
    (13, 'GPS',          'Cabine',             'Ativo',      'TIC',        13),
    (14, 'Consumo',      'Tanque principal',   'Ativo',      'Vale',       14),
    (15, 'Velocidade',   'Eixo 01',            'Ativo',      'Rumo',       15),
    (16, 'Presença',     'Vagão 01',           'Ativo',      'MRS',        16),
    (17, 'Temperatura',  'Motor A',            'Manutenção', 'VLI',        17),
    (18, 'GPS',          'Cabine',             'Ativo',      'Serra Verde', 18),
    (19, 'Pressão',      'Sistema de freio',   'Inativo',    'MRS',        19),
    (20, 'Consumo',      'Tanque principal',   'Ativo',      'Rumo',       20);

INSERT IGNORE INTO Rota (id_rota, nome_rota, origem, destino, operadora)
VALUES
    (1,  'Joinville - Curitiba',           'Joinville',      'Curitiba',        'Rumo'),
    (2,  'Curitiba - Paranaguá',           'Curitiba',       'Paranaguá',       'Serra Verde'),
    (3,  'São Paulo - Santos',             'São Paulo',      'Santos',          'MRS'),
    (4,  'Belo Horizonte - Vitória',       'Belo Horizonte', 'Vitória',         'Vale'),
    (5,  'Joinville - São Francisco do Sul','Joinville',     'São Francisco do Sul', 'Rumo'),
    (6,  'Blumenau - Itajaí',              'Blumenau',       'Itajaí',          'MRS'),
    (7,  'São Paulo - Jundiaí',            'São Paulo',      'Jundiaí',         'CPTM'),
    (8,  'Campinas - Santos',              'Campinas',       'Santos',          'Rumo'),
    (9,  'Uberaba - Santos',               'Uberaba',        'Santos',          'VLI'),
    (10, 'São Paulo - Mogi das Cruzes',    'São Paulo',      'Mogi das Cruzes', 'CPTM'),
    (11, 'Ponta Grossa - Paranaguá',       'Ponta Grossa',   'Paranaguá',       'Rumo'),
    (12, 'Maringá - Paranaguá',            'Maringá',        'Paranaguá',       'Rumo'),
    (13, 'São Paulo - Campinas',           'São Paulo',      'Campinas',        'TIC'),
    (14, 'Itabira - Vitória',              'Itabira',        'Vitória',         'Vale'),
    (15, 'Cascavel - Paranaguá',           'Cascavel',       'Paranaguá',       'Rumo'),
    (16, 'Florianópolis - Joinville',      'Florianópolis',  'Joinville',       'MRS'),
    (17, 'Araguari - Santos',              'Araguari',       'Santos',          'VLI'),
    (18, 'Morretes - Curitiba',            'Morretes',       'Curitiba',        'Serra Verde'),
    (19, 'Rio de Janeiro - Juiz de Fora',  'Rio de Janeiro', 'Juiz de Fora',    'MRS'),
    (20, 'Rondonópolis - Santos',          'Rondonópolis',   'Santos',          'Rumo');

INSERT IGNORE INTO Historico_Rota (id_historico, data_inicio, data_fim, id_trem, id_rota)
VALUES
    (1,  '2026-10-01 08:00:00', NULL, 1,  1),
    (2,  '2026-10-02 09:30:00', NULL, 3,  2),
    (3,  '2026-10-03 07:15:00', NULL, 2,  3),
    (4,  '2026-10-04 06:00:00', NULL, 9,  4),
    (5,  '2026-10-05 10:00:00', NULL, 4,  5),
    (6,  '2026-10-06 11:20:00', NULL, 16, 6),
    (7,  '2026-10-07 05:45:00', NULL, 7,  7),
    (8,  '2026-10-08 13:00:00', NULL, 11, 8),
    (9,  '2026-10-09 14:30:00', NULL, 6,  9),
    (10, '2026-10-10 06:10:00', NULL, 10, 10),
    (11, '2026-10-11 08:40:00', NULL, 12, 11),
    (12, '2026-10-12 09:00:00', NULL, 15, 12),
    (13, '2026-10-13 07:00:00', NULL, 13, 13),
    (14, '2026-10-14 15:00:00', NULL, 14, 14),
    (15, '2026-10-15 16:20:00', NULL, 20, 15),
    (16, '2026-10-16 08:00:00', NULL, 19, 16),
    (17, '2026-10-17 12:00:00', NULL, 17, 17),
    (18, '2026-10-18 09:15:00', NULL, 18, 18),
    (19, '2026-10-19 10:45:00', NULL, 8,  19),
    (20, '2026-10-20 06:30:00', NULL, 5,  20);
