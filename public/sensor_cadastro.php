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

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar Sensor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/css/sensor.css">
    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>

<body>

<div class="app-container d-flex">

    <?php require '../infra/sidebar.php'; ?>

    <?php $usuarioFixo = true; require '../infra/usuario_topo.php'; ?>

    <main class="content flex-grow-1 p-4">

        <section class="painel-cadastro">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h1 class="titulo m-0">Cadastrar sensor</h1>

                <a href="sensor_home.php" class="btn btn-dark text-white rounded-pill px-4 py-2">
                    ← Voltar
                </a>

            </div>

            <?php if (!empty($mensagem)) echo $mensagem; ?>

            <?php if ($rotas && $rotas->num_rows > 0): ?>

                <form action="" method="POST">

                    <div class="row g-4 align-items-center">

                        <div class="col-lg-8">

                            <div class="row g-4">

                                <div class="col-md-6">

                                    <div class="card-input">

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

                                </div>

                                <div class="col-md-6">

                                    <div class="card-input">

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

                                </div>

                                <div class="col-md-6">

                                    <div class="card-input">

                                        <label for="status_sensor">Status do Sensor</label>

                                        <select
                                            id="status_sensor"
                                            name="status_sensor"
                                            class="form-select border-0 text-center"
                                            style="background-color: #df9b49; font-weight: bold; border-radius: 25px;"
                                            required
                                        >

                                            <option value="Ativo">Ativo</option>
                                            <option value="Inativo">Inativo</option>
                                            <option value="Manutenção">Manutenção</option>

                                        </select>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="card-input">

                                        <label for="id_rota">Rota vinculada</label>

                                        <select
                                            id="id_rota"
                                            name="id_rota"
                                            class="form-select border-0 text-center"
                                            style="background-color: #df9b49; font-weight: bold; border-radius: 25px;"
                                            required
                                        >

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

                            </div>

                        </div>

                        <div class="col-lg-4 d-flex justify-content-center align-items-center">

                            <div class="botao-container">

                                <button type="submit" class="btn-enviar">
                                    Cadastrar
                                </button>

                            </div>

                        </div>

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
