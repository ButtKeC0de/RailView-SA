
<?php
require_once '../infra/auth.php';
exigir_login();

// Recebe o ID do sensor
$id_sensor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_sensor) {
    header("Location: sensor_home.php");
    exit();
}

// Busca os dados do sensor e sua rota
$sql = "
    SELECT
        s.id_sensor,
        s.tipo_sensor,
        s.status_sensor,
        s.empresa_operadora,
        s.id_rota,
        r.nome_rota,
        r.origem,
        r.destino
    FROM Sensor s
    LEFT JOIN Rota r ON s.id_rota = r.id_rota
    WHERE s.id_sensor = ?
";

$stmt = $conexao->prepare($sql);
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

// Define a cor do status
$statusClass = 'badge-ativo';

if ($sensor['status_sensor'] == 'Inativo') {

    $statusClass = 'badge-inativo';

} elseif ($sensor['status_sensor'] == 'Manutenção') {

    $statusClass = 'badge-manutencao';

}

?>

<!doctype html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalhes do Sensor</title>

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

                    <h1 class="titulo m-0">
                        Detalhes do Sensor
                    </h1>

                    <a href="sensor_home.php" class="btn btn-dark text-white rounded-pill px-4 py-2">
                        ← Voltar
                    </a>

                </div>

                <div class="card-sensor">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h4 class="m-0">
                            Sensor #<?= (int)$sensor['id_sensor'] ?>
                        </h4>

                        <span class="badge badge-status <?= $statusClass ?>">

                            <?= htmlspecialchars($sensor['status_sensor'], ENT_QUOTES, 'UTF-8') ?>

                        </span>

                    </div>

                    <div class="mb-4">

                        <div class="label-campo">TIPO DO SENSOR</div>

                        <div class="valor-campo fw-bold">

                            <?= htmlspecialchars($sensor['tipo_sensor'], ENT_QUOTES, 'UTF-8') ?>

                        </div>

                    </div>

                    <div class="mb-4">

                        <div class="label-campo">EMPRESA OPERADORA</div>

                        <div class="valor-campo">

                            <?= htmlspecialchars($sensor['empresa_operadora'], ENT_QUOTES, 'UTF-8') ?>

                        </div>

                    </div>

                    <div class="mb-4">

                        <div class="label-campo">ROTA VINCULADA</div>

                        <div class="valor-campo text-warning">

                            <?= htmlspecialchars($sensor['nome_rota'] ?? 'Não informada', ENT_QUOTES, 'UTF-8') ?>

                        </div>

                    </div>

                    <div class="mb-4">

                        <div class="label-campo">ORIGEM E DESTINO DA ROTA</div>

                        <div class="valor-campo">

                            <?= htmlspecialchars($sensor['origem'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>

                            -

                            <?= htmlspecialchars($sensor['destino'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>

                        </div>

                    </div>

                    <?php if (pode_editar()): ?>

                        <div class="d-flex gap-2 justify-content-end mt-4">

                            <a
                                href="sensor_editar.php?id=<?= (int)$sensor['id_sensor'] ?>"
                                class="btn btn-warning rounded-pill px-4"
                            >
                                <i class="bi bi-pencil-square"></i>
                                Editar
                            </a>

                            <form
                                action="sensor_excluir.php"
                                method="POST"
                                onsubmit="return confirm('Deseja realmente excluir este sensor?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$sensor['id_sensor'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-danger rounded-pill px-4"
                                >
                                    <i class="bi bi-trash"></i>
                                    Excluir
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </main>

    </div>

</body>

</html>
