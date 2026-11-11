<?php
require_once '../infra/auth.php';
exigir_login();

function contar_registros(mysqli $conexao, string $tabela): int
{
    $resultado = $conexao->query("SELECT COUNT(*) AS total FROM $tabela");
    return $resultado ? (int)$resultado->fetch_assoc()['total'] : 0;
}

$totalRotas = contar_registros($conexao, 'Rota');
$totalTrens = contar_registros($conexao, 'Trem');
$totalSensores = contar_registros($conexao, 'Sensor');
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Início | Rail View</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/css/inicio.css">
    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>

<body>
    <div class="tela">

        <?php require '../infra/sidebar.php'; ?>

        <main class="conteudo">

            <header class="topo">
                <div>
                    <p class="subtitulo">Painel Rail View</p>
                    <h1 class="titulo_home">Início</h1>
                </div>

                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <section class="atalhos" aria-label="Módulos do sistema">

                <a class="atalho" href="criacao_rotas.php">
                    <img src="../Assets/logos/icon_rotas.png" alt="">
                    <div>
                        <h2>Rotas</h2>
                        <p><?= $totalRotas ?> cadastrada<?= $totalRotas === 1 ? '' : 's' ?></p>
                    </div>
                    <span class="atalho-seta" aria-hidden="true">&rarr;</span>
                </a>

                <a class="atalho" href="trens_home.php">
                    <img src="../Assets/logos/icon_trem.png" alt="">
                    <div>
                        <h2>Trens</h2>
                        <p><?= $totalTrens ?> cadastrado<?= $totalTrens === 1 ? '' : 's' ?></p>
                    </div>
                    <span class="atalho-seta" aria-hidden="true">&rarr;</span>
                </a>

                <a class="atalho" href="sensor_home.php">
                    <img src="../Assets/logos/icon_sensores.png" alt="">
                    <div>
                        <h2>Sensores</h2>
                        <p><?= $totalSensores ?> cadastrado<?= $totalSensores === 1 ? '' : 's' ?></p>
                    </div>
                    <span class="atalho-seta" aria-hidden="true">&rarr;</span>
                </a>

            </section>

            <section class="clima-card">
                <div>
                    <p class="clima-label">MONITORAMENTO CLIMÁTICO</p>
                    <h2>Condições meteorológicas</h2>
                    <p id="clima-local">Joinville, SC</p>
                </div>

                <div class="clima-info">
                    <span id="clima-icone">🌤️</span>
                    <strong id="clima-temperatura">-- °C</strong>
                    <span id="clima-condicao">Consultando clima...</span>
                </div>

                <p id="clima-atualizacao">Aguardando dados da API...</p>
            </section>

        </main>
    </div>

    <script src="../scripts/clima.js"></script>
</body>

</html>
