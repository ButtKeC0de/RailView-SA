<?php
require_once '../infra/auth.php';
exigir_pode_editar();

$mensagem = "";

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

        // Verifica se a rota existe
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

            // Cadastra o sensor
            $stmt = $conexao->prepare("
                INSERT INTO Sensor
                (tipo_sensor, empresa_operadora, status_sensor, id_rota)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssi",
                $tipo_sensor,
                $empresa_operadora,
                $status_sensor,
                $id_rota
            );

            if ($stmt->execute()) {

                $_SESSION['mensagem'] = mensagem(
                    'success',
                    'Sensor cadastrado com sucesso!'
                );

                $stmt->close();

                header("Location: sensor_home.php");
                exit();

            } else {

                $mensagem = mensagem('danger', 'Erro ao cadastrar sensor.');

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

    <title>Cadastrar Sensor</title>

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
                <h1>Cadastrar sensor</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>
            <section class="cadastro-painel" aria-label="Dados do novo sensor">
                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <?php if ($rotas && $rotas->num_rows > 0): ?>

                <form action="" method="POST">
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
                                    value="<?= htmlspecialchars($_POST['tipo_sensor'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($_POST['empresa_operadora'] ?? '') ?>"
                                    required
                                >
                            </div>

                            <div class="cadastro-campo">
                                <label for="status_sensor">Status do Sensor</label>
                                <select id="status_sensor" name="status_sensor" required>
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                    <option value="Manutenção">Manutenção</option>
                                </select>
                            </div>

                            <div class="cadastro-campo">
                                <label for="id_rota">Rota vinculada</label>
                                <select id="id_rota" name="id_rota" required>
                                    <option value="">Selecione uma rota</option>

                                    <?php while ($rota = $rotas->fetch_assoc()): ?>
                                        <option value="<?= (int)$rota['id_rota'] ?>">
                                            <?= htmlspecialchars($rota['nome_rota']) ?>
                                            (<?= htmlspecialchars($rota['origem']) ?> -
                                            <?= htmlspecialchars($rota['destino']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="cadastro-acoes">
                        <a href="sensor_home.php" class="cadastro-botao cadastro-voltar">← Voltar</a>
                        <button type="submit" class="cadastro-botao cadastro-salvar">Cadastrar</button>
                    </div>
                </form>

                <?php else: ?>

                <div class="alert alert-warning">
                    Não existem rotas cadastradas. Cadastre uma rota antes de cadastrar um sensor.
                </div>

                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
