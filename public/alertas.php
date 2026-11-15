<?php
require_once '../infra/auth.php';
require_once '../infra/regras_sensor.php';
exigir_login();

if (empty($_SESSION['csrf_alertas'])) {
    $_SESSION['csrf_alertas'] = bin2hex(random_bytes(32));
}
$aviso = $_SESSION['aviso_alertas'] ?? '';
unset($_SESSION['aviso_alertas']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!eh_admin()) {
        negar_acesso();
    }
    if (!hash_equals($_SESSION['csrf_alertas'], (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Formulário expirado. Recarregue a página.');
    }
    $id = filter_input(INPUT_POST, 'id_sensor', FILTER_VALIDATE_INT);
    $acao = $_POST['acao'] ?? '';
    $valorTexto = trim($_POST['valor'] ?? '');
    $valor = $valorTexto === '' ? null : filter_var($valorTexto, FILTER_VALIDATE_FLOAT);
    $falha = isset($_POST['falha']);
    $unidade = trim($_POST['unidade'] ?? '');
    $erro = '';
    if (!$id || $id < 1 || !in_array($acao, ['limite', 'leitura'], true)) {
        $erro = 'Selecione um sensor e uma ação válidos.';
    } elseif ($valor === false || ($valor !== null && (!is_finite($valor) || abs($valor) > 999999999))) {
        $erro = 'Informe um número válido entre -999999999 e 999999999.';
    } elseif ($acao === 'leitura' && $valor === null && !$falha) {
        $erro = 'Informe uma leitura ou sinalize uma falha.';
    } elseif ($acao === 'limite' && mb_strlen($unidade) > 20) {
        $erro = 'A unidade aceita até 20 caracteres.';
    } elseif ($acao === 'limite' && $valor !== null && $unidade === '') {
        $erro = 'Informe a unidade do limite.';
    }
    if ($erro === '') {
        $conexao->begin_transaction();
        try {
            // Serializa configuração e leitura do mesmo sensor.
            $stmt = $conexao->prepare('SELECT id_sensor FROM Sensor WHERE id_sensor = ? FOR UPDATE');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $existe = $stmt->get_result()->num_rows === 1;
            $stmt->close();
            if (!$existe) {
                throw new RuntimeException('Sensor inexistente.');
            }
            if ($acao === 'limite') {
                // Limite vazio remove o disparo numérico; falhas continuam gerando alertas.
                $stmt = $conexao->prepare('INSERT INTO Limite_Sensor (id_sensor, maximo, unidade) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE maximo = VALUES(maximo), unidade = VALUES(unidade)');
                $stmt->bind_param('ids', $id, $valor, $unidade);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conexao->prepare('SELECT maximo, unidade FROM Limite_Sensor WHERE id_sensor = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $config = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $maximo = isset($config['maximo']) ? (float)$config['maximo'] : null;
                $unidadeLeitura = $config['unidade'] ?? '';
                $flagFalha = (int)$falha;
                $stmt = $conexao->prepare('INSERT INTO Leitura_Sensor (id_sensor, valor, falha, unidade, data_leitura) VALUES (?, ?, ?, ?, NOW())');
                $stmt->bind_param('idis', $id, $valor, $flagFalha, $unidadeLeitura);
                $stmt->execute();
                $stmt->close();
                $alerta = avaliar_leitura_sensor($valor, $maximo, $falha);
                if ($alerta !== null) {
                    $texto = $alerta['mensagem'];
                    if ($valor !== null) {
                        $texto .= ' Valor: ' . $valor . ' ' . $unidadeLeitura . '.';
                    }
                    if (!$falha && $maximo !== null) {
                        $texto .= ' Limite: ' . $maximo . '.';
                    }
                    $nivel = $alerta['nivel'];
                    $stmt = $conexao->prepare('INSERT INTO Alerta (mensagem, data_alerta, nivel, id_sensor) VALUES (?, NOW(), ?, ?)');
                    $stmt->bind_param('ssi', $texto, $nivel, $id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $conexao->commit();
            $_SESSION['aviso_alertas'] = 'Registro salvo.';
            header('Location: alertas.php');
            exit();
        } catch (Throwable $e) {
            $conexao->rollback();
            $erro = 'Não foi possível salvar. Confira o sensor e a importação do SQL atualizado.';
        }
    }
    $aviso = $erro;
}
$sensores = $conexao->query('SELECT s.id_sensor, s.tipo_sensor, s.localizacao, t.apelido, l.maximo, l.unidade FROM Sensor s JOIN Trem t ON t.id_trem = s.id_trem LEFT JOIN Limite_Sensor l ON l.id_sensor = s.id_sensor ORDER BY s.id_sensor')->fetch_all(MYSQLI_ASSOC);
$alertas = $conexao->query('SELECT a.*, s.tipo_sensor FROM Alerta a JOIN Sensor s ON s.id_sensor = a.id_sensor ORDER BY a.data_alerta DESC, a.id_alerta DESC LIMIT 100')->fetch_all(MYSQLI_ASSOC);
$leituras = $conexao->query('SELECT * FROM Leitura_Sensor ORDER BY data_leitura DESC, id_leitura DESC LIMIT 100')->fetch_all(MYSQLI_ASSOC);
function escapar_alerta($texto): string
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leituras e alertas — Rail View</title>
    <link rel="stylesheet" href="../Assets/css/alertas.css">
</head>
<body>
<main>
    <nav><a href="home.php">← Início</a> · <a href="sensor_lista.php">Sensores</a> · <a href="logout.php">Sair</a></nav>
    <h1>Leituras e alertas</h1>
    <p>Leituras registradas nesta tela são demonstrações manuais. Alertas são gerados automaticamente quando uma leitura ultrapassa o máximo configurado ou sinaliza falha.</p>
    <?php if ($aviso !== ''): ?><p role="status"><?= escapar_alerta($aviso) ?></p><?php endif; ?>
    <?php if (eh_admin()): ?>
    <section>
        <h2>Configurar limite ou registrar leitura</h2>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= escapar_alerta($_SESSION['csrf_alertas']) ?>">
            <label>Sensor <select name="id_sensor" required>
                <option value="">Selecione</option>
                <?php foreach ($sensores as $sensor): ?>
                <option value="<?= (int)$sensor['id_sensor'] ?>">#<?= (int)$sensor['id_sensor'] ?> — <?= escapar_alerta($sensor['tipo_sensor'] . ' / ' . $sensor['localizacao'] . ' / ' . $sensor['apelido']) ?></option>
                <?php endforeach; ?>
            </select></label>
            <label>Ação <select name="acao"><option value="leitura">Registrar leitura</option><option value="limite">Salvar limite máximo</option></select></label>
            <label>Valor <input name="valor" type="number" step="any" min="-999999999" max="999999999"></label>
            <label>Unidade (para configurar limite) <input name="unidade" maxlength="20" placeholder="Ex.: km/h ou °C"></label>
            <label><input type="checkbox" name="falha" value="1"> Sinalizar falha na leitura</label>
            <p>Informe leituras na unidade configurada. Limite vazio desativa somente a comparação numérica. Igualar o máximo não dispara alerta. Uma nova leitura fora do limite gera um novo registro.</p>
            <button type="submit">Salvar</button>
        </form>
    </section>
    <?php endif; ?>
    <h2>Limites configurados</h2>
    <div class="tabela"><table><thead><tr><th>Sensor</th><th>Tipo</th><th>Máximo</th><th>Unidade</th></tr></thead><tbody>
    <?php foreach ($sensores as $sensor): ?><tr><td><?= (int)$sensor['id_sensor'] ?></td><td><?= escapar_alerta($sensor['tipo_sensor']) ?></td><td><?= escapar_alerta($sensor['maximo'] ?? 'Sem limite') ?></td><td><?= escapar_alerta($sensor['unidade'] ?? '') ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <h2>Últimos 100 alertas</h2>
    <?php if (!$alertas): ?><p>Nenhum alerta registrado.</p><?php endif; ?>
    <div class="tabela"><table><thead><tr><th>Data</th><th>Sensor</th><th>Nível</th><th>Mensagem</th></tr></thead><tbody>
    <?php foreach ($alertas as $alerta): ?><tr><td><?= escapar_alerta($alerta['data_alerta']) ?></td><td>#<?= (int)$alerta['id_sensor'] ?> <?= escapar_alerta($alerta['tipo_sensor']) ?></td><td><?= escapar_alerta($alerta['nivel']) ?></td><td><?= escapar_alerta($alerta['mensagem']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <h2>Últimas 100 leituras</h2>
    <div class="tabela"><table><thead><tr><th>Data</th><th>Sensor</th><th>Valor</th><th>Unidade</th><th>Falha</th></tr></thead><tbody>
    <?php foreach ($leituras as $leitura): ?><tr><td><?= escapar_alerta($leitura['data_leitura']) ?></td><td><?= (int)$leitura['id_sensor'] ?></td><td><?= escapar_alerta($leitura['valor'] ?? 'Sem valor') ?></td><td><?= escapar_alerta($leitura['unidade']) ?></td><td><?= $leitura['falha'] ? 'Sim' : 'Não' ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
</main>
</body>
</html>
