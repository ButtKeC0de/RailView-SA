<?php
require_once '../infra/auth.php';
exigir_admin();

$mensagem = "";

$trens = [];
$resultTrens = $conexao->query("SELECT id_trem, apelido, modelo FROM Trem ORDER BY apelido ASC");
if ($resultTrens) {
    while ($row = $resultTrens->fetch_assoc()) {
        $trens[] = $row;
    }
}

$id_sensor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_sensor) {
    header("Location: sensor_lista.php");
    exit();
}

$stmt = $conexao->prepare("SELECT * FROM Sensor WHERE id_sensor = ?");
$stmt->bind_param("i", $id_sensor);
$stmt->execute();
$sensor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sensor) {
    $_SESSION['mensagem'] = mensagem('warning', 'Sensor não encontrado.');
    header("Location: sensor_lista.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_sensor       = trim($_POST['tipo_sensor'] ?? '');
    $localizacao       = trim($_POST['localizacao'] ?? '');
    $empresa_operadora = trim($_POST['empresa_operadora'] ?? '');
    $status_sensor     = $_POST['status_sensor'] ?? 'Ativo';
    $id_trem           = filter_input(INPUT_POST, 'id_trem', FILTER_VALIDATE_INT);

    $statusPermitidos = ['Ativo', 'Inativo', 'Manutenção'];

    if ($tipo_sensor === '' || $localizacao === '' || $empresa_operadora === '' || !$id_trem) {
        $mensagem = mensagem('danger', 'Por favor, preencha todos os campos obrigatórios.');

    } elseif (!in_array($status_sensor, $statusPermitidos, true)) {
        $mensagem = mensagem('danger', 'Status do sensor inválido.');

    } else {
        $stmt = $conexao->prepare("SELECT id_trem FROM Trem WHERE id_trem = ?");
        $stmt->bind_param("i", $id_trem);
        $stmt->execute();
        $tremExiste = $stmt->get_result()->num_rows === 1;
        $stmt->close();

        if (!$tremExiste) {
            $mensagem = mensagem('danger', 'O trem selecionado não existe.');
        } else {
            $sql = "UPDATE Sensor
                    SET tipo_sensor = ?, localizacao = ?, status_sensor = ?, empresa_operadora = ?, id_trem = ?
                    WHERE id_sensor = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssssii", $tipo_sensor, $localizacao, $status_sensor, $empresa_operadora, $id_trem, $id_sensor);

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = mensagem('success', 'Sensor atualizado com sucesso!');
                $stmt->close();
                header("Location: sensor_lista.php");
                exit();
            }

            $mensagem = mensagem('danger', 'Não foi possível atualizar o sensor. Tente novamente.');
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
    <title>Editar Sensor</title>
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
                    <h1 class="titulo m-0">Editar Sensor #<?= (int)$sensor['id_sensor'] ?></h1>
                    <a href="sensor_lista.php" class="btn btn-dark text-white rounded-pill px-4 py-2">← Voltar</a>
                </div>

                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <form action="" method="POST">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-8">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="tipo_sensor">Tipo do Sensor</label>
                                        <input type="text" id="tipo_sensor" name="tipo_sensor" value="<?= htmlspecialchars($sensor['tipo_sensor']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="localizacao">Localização</label>
                                        <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($sensor['localizacao']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="empresa_operadora">Empresa operadora</label>
                                        <input type="text" id="empresa_operadora" name="empresa_operadora" value="<?= htmlspecialchars($sensor['empresa_operadora']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="status_sensor">Status do Sensor</label>
                                        <select id="status_sensor" name="status_sensor" class="form-select border-0 text-center" style="background-color: #df9b49; font-weight: bold; border-radius: 25px;" required>
                                            <option value="Ativo" <?= $sensor['status_sensor'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                            <option value="Inativo" <?= $sensor['status_sensor'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                                            <option value="Manutenção" <?= $sensor['status_sensor'] === 'Manutenção' ? 'selected' : '' ?>>Manutenção</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="card-input">
                                        <label for="id_trem">Selecione o Trem</label>
                                        <select id="id_trem" name="id_trem" class="form-select border-0 text-center" style="background-color: #df9b49; font-weight: bold; border-radius: 25px;" required>
                                            <?php foreach ($trens as $trem): ?>
                                                <option value="<?= $trem['id_trem'] ?>" <?= $sensor['id_trem'] == $trem['id_trem'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($trem['apelido']) ?> (<?= htmlspecialchars($trem['modelo']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 d-flex justify-content-center align-items-center">
                            <div class="botao-container">
                                <button type="submit" class="btn-enviar">Salvar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </main>
    </div>
</body>
</html>