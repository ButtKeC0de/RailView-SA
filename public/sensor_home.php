<?php

require_once '../infra/auth.php';
exigir_login();


$pesquisa = $_GET['pesquisa'] ?? '';

if ($pesquisa != '') {
    $pesquisaBanco = "%" . $pesquisa . "%";

    $stmt = $conexao->prepare("SELECT * FROM Sensor WHERE localizacao LIKE ? OR tipo_sensor LIKE ? OR status_sensor LIKE ? OR id_sensor LIKE ? ORDER BY id_sensor DESC");
    $stmt->bind_param("ssss", $pesquisaBanco, $pesquisaBanco, $pesquisaBanco, $pesquisaBanco);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conexao->query("SELECT * FROM Sensor ORDER BY id_sensor DESC");
}

?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sensores Cadastrados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/css/painel.css">
    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>

<body>

<div class="tela">

    <?php require '../infra/sidebar.php'; ?>

    <div class="conteudo">

        <div class="topo">

            <h1 class="titulo_home"> Sensores cadastrados </h1>

            <?php require '../infra/usuario_topo.php'; ?>

        </div>

        <form method="GET" action="sensor_home.php" class="busca">
            <input name="pesquisa" type="text" placeholder="Pesquisar sensor" value="<?= htmlspecialchars($pesquisa) ?>">
            <button type="submit">Pesquisar</button>
        </form>

        <?php if (isset($_SESSION['mensagem'])): ?>

            <?= $_SESSION['mensagem']; ?>

            <?php unset($_SESSION['mensagem']); ?>

        <?php endif; ?>

        <div id="trens">

            <?php if ($resultado && $resultado->num_rows > 0): ?>

                <?php while ($sensor = $resultado->fetch_assoc()): ?>

                    <a class="trem sensor" href="sensor_lista.php?id=<?= (int)$sensor['id_sensor'] ?>" >

                        <img src="../Assets/logos/icon_sensores.png"alt="Sensor" >
                        <p>ID:<?= (int)$sensor['id_sensor'] ?></p>
                        <p> <?= htmlspecialchars($sensor['localizacao'],ENT_QUOTES, 'UTF-8' ) ?></p>

                        <?php
                        $classeStatus = 'status-ativo';
                        if ($sensor['status_sensor'] === 'Inativo') {
                            $classeStatus = 'status-inativo';
                        } elseif ($sensor['status_sensor'] === 'Manutenção') {
                            $classeStatus = 'status-manutencao';
                        }
                        ?>
                        <span class="status-sensor <?= $classeStatus ?>"><?= htmlspecialchars($sensor['status_sensor']) ?></span>

                    </a>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="estado-vazio estado-vazio--escuro estado-vazio--simples" style="grid-column: 1 / -1;">
                    <h3>Nenhum sensor cadastrado</h3>
                    <?php if (pode_editar()): ?>
                        <a href="sensor_cadastro.php" class="botao_laranja text-decoration-none">+ Cadastrar primeiro sensor</a>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        </div>

        <div class="parte_baixo_home">

            <?php if (pode_editar()): ?>

            <div class="card_acao">

                <h2>
                    Cadastrar sensor
                </h2>

                <form action="sensor_cadastro.php" method="get">
                    <button type="submit" class="botao_laranja"> Cadastrar</button>
                </form>

            </div>

            <div class="card_acao">

                <h2> Gerenciar sensores </h2>

                <form action="sensor_lista.php" method="get">
                    <button type="submit" class="botao_laranja"> Gerenciar </button>
                </form>

            </div>

            <?php else: ?>

            <div class="card_acao">

                <h2>
                    Consultar sensores
                </h2>

                <form action="sensor_lista.php" method="get">
                    <button type="submit" class="botao_laranja"> Ver lista </button>
                </form>

            </div>

            <?php endif; ?>

            <div class="card_mapa">

                <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=-48.98%2C-26.43%2C-48.72%2C-26.18&layer=mapnik" title="Mapa"></iframe>

            </div>

        </div>

    </div>

</div>

</body>

</html>
