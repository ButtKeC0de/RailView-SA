SET NAMES utf8mb4;

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

CREATE TABLE IF NOT EXISTS Limite_Sensor (
    id_sensor INT PRIMARY KEY,
    maximo DOUBLE NULL,
    unidade VARCHAR(20) NOT NULL DEFAULT '',
    CONSTRAINT limite_sensor_fk FOREIGN KEY (id_sensor)
        REFERENCES Sensor(id_sensor) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS Leitura_Sensor (
    id_leitura INT AUTO_INCREMENT PRIMARY KEY,
    id_sensor INT NOT NULL,
    valor DOUBLE NULL,
    falha TINYINT(1) NOT NULL DEFAULT 0,
    unidade VARCHAR(20) NOT NULL DEFAULT '',
    data_leitura DATETIME NOT NULL,
    CONSTRAINT leitura_sensor_fk FOREIGN KEY (id_sensor)
        REFERENCES Sensor(id_sensor) ON DELETE CASCADE ON UPDATE CASCADE
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
ALTER TABLE Limite_Sensor  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE Leitura_Sensor CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
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

-- Contas exclusivamente fictícias. Senha pública de demonstração: password
-- O hash bcrypt é validado pelo script scripts/verificar_demo.php.
START TRANSACTION;
INSERT INTO Usuario (nome, email, senha, id_perfil) VALUES
('Admin Demo', 'admin@demo.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 1),
('Funcionario Demo', 'funcionario@demo.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 2),
('Usuario Demo', 'usuario@demo.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 3)
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Localiza as contas por email, sem presumir IDs de uma instalação anterior.
SET @admin_demo = (SELECT id_usuario FROM Usuario WHERE email = 'admin@demo.test');
INSERT INTO Trem (apelido, tipo_trem, empresa_operadora, modelo, numero_vagoes, id_usuario)
SELECT 'Expresso Demo Norte', 'Passageiros', 'Ferrovia Demo', 'Modelo Demo A', 8, @admin_demo
WHERE NOT EXISTS (SELECT 1 FROM Trem WHERE apelido = 'Expresso Demo Norte');
INSERT INTO Trem (apelido, tipo_trem, empresa_operadora, modelo, numero_vagoes, id_usuario)
SELECT 'Carga Demo Sul', 'Carga', 'Ferrovia Demo', 'Modelo Demo B', 20, @admin_demo
WHERE NOT EXISTS (SELECT 1 FROM Trem WHERE apelido = 'Carga Demo Sul');
SET @trem_demo = (SELECT MIN(id_trem) FROM Trem WHERE apelido = 'Expresso Demo Norte');
SET @carga_demo = (SELECT MIN(id_trem) FROM Trem WHERE apelido = 'Carga Demo Sul');

INSERT INTO Sensor (tipo_sensor, localizacao, status_sensor, empresa_operadora, id_trem)
SELECT 'Temperatura', 'Motor Demo A', 'Ativo', 'Ferrovia Demo', @trem_demo
WHERE NOT EXISTS (SELECT 1 FROM Sensor WHERE id_trem = @trem_demo AND localizacao = 'Motor Demo A');
INSERT INTO Sensor (tipo_sensor, localizacao, status_sensor, empresa_operadora, id_trem)
SELECT 'Velocidade', 'Cabine Demo B', 'Manutenção', 'Ferrovia Demo', @carga_demo
WHERE NOT EXISTS (SELECT 1 FROM Sensor WHERE id_trem = @carga_demo AND localizacao = 'Cabine Demo B');

INSERT INTO Rota (nome_rota, origem, destino, operadora)
SELECT 'Rota Demo Norte', 'Joinville', 'Curitiba', 'Ferrovia Demo'
WHERE NOT EXISTS (SELECT 1 FROM Rota WHERE nome_rota = 'Rota Demo Norte');
INSERT INTO Rota (nome_rota, origem, destino, operadora)
SELECT 'Rota Demo Sul', 'Joinville', 'Florianópolis', 'Ferrovia Demo'
WHERE NOT EXISTS (SELECT 1 FROM Rota WHERE nome_rota = 'Rota Demo Sul');
SET @rota_demo = (SELECT MIN(id_rota) FROM Rota WHERE nome_rota = 'Rota Demo Norte');
SET @rota_carga = (SELECT MIN(id_rota) FROM Rota WHERE nome_rota = 'Rota Demo Sul');
INSERT INTO Historico_Rota (data_inicio, id_trem, id_rota)
SELECT '2026-11-15 08:00:00', @trem_demo, @rota_demo
WHERE NOT EXISTS (SELECT 1 FROM Historico_Rota WHERE id_trem = @trem_demo AND id_rota = @rota_demo);
INSERT INTO Historico_Rota (data_inicio, id_trem, id_rota)
SELECT '2026-11-15 09:00:00', @carga_demo, @rota_carga
WHERE NOT EXISTS (SELECT 1 FROM Historico_Rota WHERE id_trem = @carga_demo AND id_rota = @rota_carga);
INSERT INTO Limite_Sensor (id_sensor, maximo, unidade)
SELECT s.id_sensor, 80, CASE WHEN s.tipo_sensor = 'Temperatura' THEN '°C' ELSE 'km/h' END
FROM Sensor s
WHERE (s.id_trem = @trem_demo AND s.localizacao = 'Motor Demo A')
   OR (s.id_trem = @carga_demo AND s.localizacao = 'Cabine Demo B')
ON DUPLICATE KEY UPDATE id_sensor = VALUES(id_sensor);
COMMIT;

