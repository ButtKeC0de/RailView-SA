<?php
require_once '../infra/auth.php';
exigir_pode_editar();

$mensagem = "";

// Recebe o ID do sensor
$id_sensor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_sensor) {
    header("Location: sensor_home.php");
    exit();
}

// Busca os dados do sensor
$stmt = $conexao->prepare("
    SELECT *
    FROM Sensor
    WHERE id_sensor = ?
");

$stmt->bind_param("i", $id_sensor);
$stmt->execute();

$sensor = $stmt->get_result()->fetch_assoc();

$stmt->close();

// Verifica se o sensor existe
if (!$sensor) {

    $_SESSION['mensagem'] = mensagem('warning', 'Sensor não encontrado.');

    header("Location: sensor_home.php");
    exit();
}

// Preenche os campos com os dados atuais
$tipo_sensor = $sensor['tipo_sensor'];
$empresa_operadora = $sensor['empresa_operadora'];
$status_sensor = $sensor['status_sensor'];
$id_rota = $sensor['id_rota'];

// Busca as rotas cadastradas
$rotas = $conexao->query("
    SELECT id_rota, nome_rota, origem, destino
    FROM Rota
    ORDER BY nome_rota ASC
");

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tipo_sensor = trim($_POST['tipo_sensor'] ?? '');
    $empresa_operadora = trim($_POST['empresa_operadora'] ?? '');
    $status_sensor = $_POST['status_sensor'] ?? '';
    $id_rota = filter_input(INPUT_POST, 'id_rota', FILTER_VALIDATE_INT);

    if (
        $tipo_sensor == '' ||
        $empresa_operadora == '' ||
        !$id_rota
    ) {

        $mensagem = mensagem('danger', 'Preencha todos os campos.');

    } elseif (!in_array($status_sensor, ['Ativo', 'Inativo', 'Manutenção'], true)) {

        $mensagem = mensagem('danger', 'Status inválido.');

    } else {

        // Verifica se a rota selecionada existe
        $stmt = $conexao->prepare("
            SELECT id_rota
            FROM Rota
            WHERE id_rota = ?
        ");

        $stmt->bind_param("i", $id_rota);
        $stmt->execute();

        $rotaExiste = $stmt->get_result()->num_rows > 0;

        $stmt->close();

        if (!$rotaExiste) {

            $mensagem = mensagem('danger', 'Rota não encontrada.');

        } else {

            // Atualiza os dados do sensor
            $stmt = $conexao->prepare("
                UPDATE Sensor
                SET
                    tipo_sensor = ?,
                    empresa_operadora = ?,
                    status_sensor = ?,
                    id_rota = ?
                WHERE id_sensor = ?
            ");

            $stmt->bind_param(
                "sssii",
                $tipo_sensor,
                $empresa_operadora,
                $status_sensor,
                $id_rota,
                $id_sensor
            );

            if ($stmt->execute()) {

                $_SESSION['mensagem'] = mensagem(
                    'success',
                    'Sensor atualizado com sucesso!'
                );

                $stmt->close();

                header("Location: sensor_lista.php?id=" . $id_sensor);
                exit();

            } else {

                $mensagem = mensagem('danger', 'Erro ao atualizar o sensor.');

            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Sensor</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/gestao.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
</head>

<body class="cadastro-usuarios pagina">
    <div class="tela">
        <?php require '../infra/sidebar.php'; ?>
        <main class="conteudo">
            <header class="cadastro-topo">
                <h1>Editar Sensor</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>
            <section class="cadastro-painel" aria-label="Dados do sensor">
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <?php if ($rotas && $rotas->num_rows > 0): ?>

                <form action="sensor_editar.php?id=<?= (int)$id_sensor ?>" method="POST">
                    <fieldset>
                        <legend>Dados do sensor</legend>
                        <div class="cadastro-campos">
                            <div class="cadastro-campo">
                                <label for="tipo_sensor">Tipo do Sensor</label>
                                <input
                                    type="text"
                                    id="tipo_sensor"
                                    name="tipo_sensor"
                                    placeholder="Ex: Temperatura"
                                    value="<?= htmlspecialchars($tipo_sensor, ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <div class="cadastro-campo">
                                <label for="empresa_operadora">Empresa operadora</label>
                                <input
                                    type="text"
                                    id="empresa_operadora"
                                    name="empresa_operadora"
                                    placeholder="Nome da operadora"
                                    value="<?= htmlspecialchars($empresa_operadora, ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <div class="cadastro-campo">
                                <label for="status_sensor">Status do Sensor</label>
                                <select id="status_sensor" name="status_sensor" required>
                                    <option value="Ativo" <?= $status_sensor == 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="Inativo" <?= $status_sensor == 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                                    <option value="Manutenção" <?= $status_sensor == 'Manutenção' ? 'selected' : '' ?>>Manutenção</option>
                                </select>
                            </div>

                            <div class="cadastro-campo">
                                <label for="id_rota">Rota vinculada</label>
                                <select id="id_rota" name="id_rota" required>
                                    <option value="">Selecione uma rota</option>

                                    <?php while ($rota = $rotas->fetch_assoc()): ?>
                                        <option value="<?= (int)$rota['id_rota'] ?>"
                                            <?= (int)$id_rota == (int)$rota['id_rota'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($rota['nome_rota'], ENT_QUOTES, 'UTF-8') ?>
                                            (<?= htmlspecialchars($rota['origem'], ENT_QUOTES, 'UTF-8') ?> -
                                            <?= htmlspecialchars($rota['destino'], ENT_QUOTES, 'UTF-8') ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="cadastro-acoes">
                        <a href="sensor_lista.php?id=<?= (int)$id_sensor ?>" class="cadastro-botao cadastro-voltar">← Voltar</a>
                        <button type="submit" class="cadastro-botao cadastro-salvar">Salvar alterações</button>
                    </div>
                </form>

                <?php else: ?>

                <div class="alert alert-warning">
                    Não existem rotas cadastradas.
                </div>

                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
