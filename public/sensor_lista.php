
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
$statusClass = 'etiqueta-ativo';

if ($sensor['status_sensor'] == 'Inativo') {

    $statusClass = 'etiqueta-inativo';

} elseif ($sensor['status_sensor'] == 'Manutenção') {

    $statusClass = 'etiqueta-manutencao';

}

?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalhes do Sensor</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
</head>

<body class="pagina">

    <div class="tela">

        <?php require '../infra/sidebar.php'; ?>

        <main class="conteudo">

            <header class="pagina-topo">
                <h1>Detalhes do Sensor</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <div class="pagina-corpo">

                <section class="painel">

                    <div class="painel-cabecalho">

                        <div class="acoes">
                            <h2>Sensor #<?= (int)$sensor['id_sensor'] ?></h2>
                            <span class="etiqueta <?= $statusClass ?>">
                                <?= htmlspecialchars($sensor['status_sensor'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="acoes">

                            <a href="sensor_home.php" class="botao botao-secundario">← Voltar</a>

                            <?php if (pode_editar()): ?>

                                <a
                                    href="sensor_editar.php?id=<?= (int)$sensor['id_sensor'] ?>"
                                    class="botao botao-secundario"
                                >
                                    <i class="bi bi-pencil"></i>
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

                                    <button type="submit" class="botao botao-perigo">
                                        <i class="bi bi-trash"></i>
                                        Excluir
                                    </button>

                                </form>

                            <?php endif; ?>

                        </div>

                    </div>

                    <dl class="dados">

                        <div>
                            <dt>Tipo do sensor</dt>
                            <dd><strong><?= htmlspecialchars($sensor['tipo_sensor'], ENT_QUOTES, 'UTF-8') ?></strong></dd>
                        </div>

                        <div>
                            <dt>Empresa operadora</dt>
                            <dd><?= htmlspecialchars($sensor['empresa_operadora'], ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>

                        <div>
                            <dt>Rota vinculada</dt>
                            <dd><?= htmlspecialchars($sensor['nome_rota'] ?? 'Não informada', ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>

                        <div>
                            <dt>Origem e destino da rota</dt>
                            <dd>
                                <?= htmlspecialchars($sensor['origem'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                                -
                                <?= htmlspecialchars($sensor['destino'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                            </dd>
                        </div>

                    </dl>

                </section>

            </div>

        </main>

    </div>

</body>

</html>
