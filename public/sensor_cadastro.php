<?php
require_once '../infra/auth.php';
exigir_admin();

$mensagem = "";

// Lista de trens para o <select> do formulário.
$trens = [];
$resultTrens = $conexao->query("SELECT id_trem, apelido, modelo FROM Trem ORDER BY apelido ASC");
if ($resultTrens) {
    while ($row = $resultTrens->fetch_assoc()) {
        $trens[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // trim() tira espaços sobrando no começo e no fim.
    $tipo_sensor       = trim($_POST['tipo_sensor'] ?? '');
    $localizacao       = trim($_POST['localizacao'] ?? '');
    $empresa_operadora = trim($_POST['empresa_operadora'] ?? '');
    $status_sensor     = $_POST['status_sensor'] ?? 'Ativo';
    // FILTER_VALIDATE_INT: só aceita número inteiro. Qualquer outra coisa vira false.
    $id_trem           = filter_input(INPUT_POST, 'id_trem', FILTER_VALIDATE_INT);

    // Só aceita os 3 status que existem no <select>.
    $statusPermitidos = ['Ativo', 'Inativo', 'Manutenção'];

    if ($tipo_sensor === '' || $localizacao === '' || $empresa_operadora === '' || !$id_trem) {
        $mensagem = mensagem('danger', 'Por favor, preencha todos os campos obrigatórios.');

    } elseif (!in_array($status_sensor, $statusPermitidos, true)) {
        $mensagem = mensagem('danger', 'Status do sensor inválido.');

    } else {
        // Confere se o trem escolhido existe mesmo no banco.
        $stmt = $conexao->prepare("SELECT id_trem FROM Trem WHERE id_trem = ?");
        $stmt->bind_param("i", $id_trem);
        $stmt->execute();
        $tremExiste = $stmt->get_result()->num_rows === 1;
        $stmt->close();

        if (!$tremExiste) {
            $mensagem = mensagem('danger', 'O trem selecionado não existe.');
        } else {
            $sql = "INSERT INTO Sensor (tipo_sensor, localizacao, status_sensor, empresa_operadora, id_trem)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssssi", $tipo_sensor, $localizacao, $status_sensor, $empresa_operadora, $id_trem);

            if ($stmt->execute()) {
                $_SESSION['mensagem'] = mensagem('success', "Sensor '$tipo_sensor' cadastrado com sucesso!");
                $stmt->close();
                header("Location: sensor_lista.php");
                exit();
            }

            // Mensagem genérica: não mostramos o erro do banco para o usuário.
            $mensagem = mensagem('danger', 'Não foi possível cadastrar o sensor. Tente novamente.');
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
                    <a href="sensor_lista.php" class="btn btn-dark text-white rounded-pill px-4 py-2">
                        ← Ver Lista
                    </a>
                </div>

                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <form action="" method="POST">
                    <div class="row g-4 align-items-center">

                        <div class="col-lg-8">

                            <div class="row g-4">

                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="tipo_sensor">Tipo do Sensor</label>
                                        <input type="text" id="tipo_sensor" name="tipo_sensor" placeholder="Ex: Temperatura, Presença" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="localizacao">Localização</label>
                                        <input type="text" id="localizacao" name="localizacao" placeholder="Ex: Eixo 01, Motor A" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="empresa_operadora">Empresa operadora</label>
                                        <input type="text" id="empresa_operadora" name="empresa_operadora" placeholder="Coloque a operadora" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card-input">
                                        <label for="status_sensor">Status do Sensor</label>
                                        <select id="status_sensor" name="status_sensor" class="form-select border-0 text-center" style="background-color: #df9b49; font-weight: bold; border-radius: 25px;" required>
                                            <option value="Ativo">Ativo</option>
                                            <option value="Inativo">Inativo</option>
                                            <option value="Manutenção">Manutenção</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="card-input">
                                        <label for="id_trem">Selecione o Trem</label>
                                        <select id="id_trem" name="id_trem" class="form-select border-0 text-center" style="background-color: #df9b49; font-weight: bold; border-radius: 25px;" required>
                                            <option value="">-- Selecione um Trem --</option>
                                            <?php foreach ($trens as $trem): ?>
                                                <option value="<?= $trem['id_trem'] ?>">
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
                                <button type="submit" class="btn-enviar">
                                    Enviar
                                </button>
                            </div>

                        </div>

                    </div>
                </form>

            </section>

        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>