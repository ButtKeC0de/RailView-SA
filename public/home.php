<?php
require_once '../infra/auth.php';
exigir_login();

$temperaturaClima = 'Clima indisponível';
$urlClima = 'https://api.open-meteo.com/v1/forecast?latitude=-26.3044&longitude=-48.8487&current=temperature_2m&temperature_unit=celsius&timezone=America%2FSao_Paulo';
$respostaClima = @file_get_contents($urlClima);
if ($respostaClima !== false) {
    $dadosClima = json_decode($respostaClima, true);
    $valorTemperatura = $dadosClima['current']['temperature_2m'] ?? null;
    if (is_numeric($valorTemperatura)) {
        $temperaturaClima = number_format((float)$valorTemperatura, 0, ',', '.') . ' °C';
    }
}

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
                    <h1 class="titulo_home">Início</h1>
                </div>

                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <section class="atalhos" aria-label="Módulos do sistema">

                <a class="atalho" href="criacao_rotas.php">
                    <img src="../Assets/logos/icon_rotas.png" alt="">
                    <div>
                        <h2>Rotas</h2>
                    </div>
                    <span class="atalho-seta" aria-hidden="true">&rarr;</span>
                </a>

                <a class="atalho" href="trens_home.php">
                    <img src="../Assets/logos/icon_trem.png" alt="">
                    <div>
                        <h2>Trens</h2>
                    </div>
                    <span class="atalho-seta" aria-hidden="true">&rarr;</span>
                </a>

                <a class="atalho" href="sensor_home.php">
                    <img src="../Assets/logos/icon_sensores.png" alt="">
                    <div>
                        <h2>Sensores</h2>
                    </div>
                    <span class="atalho-seta" aria-hidden="true">&rarr;</span>
                </a>

            </section>

            <section class="clima-card" aria-labelledby="titulo-clima">
                <div>
                    <h2 id="titulo-clima">Condições meteorológicas</h2>
                    <p id="clima-local">Joinville, SC</p>
                </div>

                <div class="clima-info" role="status" aria-live="polite" aria-busy="false">
                    <strong><?= htmlspecialchars($temperaturaClima, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>

            </section>

        </main>
    </div>
</body>

</html>

