<?php
require_once '../infra/auth.php';
exigir_login();

if (isset($_SESSION['clima']) && time() - $_SESSION['clima']['hora'] < 600) {
    $temperaturaClima = $_SESSION['clima']['texto'];
} else {
    $temperaturaClima = 'Clima indisponível';
    $urlClima = 'https://api.open-meteo.com/v1/forecast?latitude=-26.3044&longitude=-48.8487&current=temperature_2m&temperature_unit=celsius&timezone=America%2FSao_Paulo';

    $opcoes = [
        'http' => [
            'timeout' => 3
        ]
    ];
    $contexto = stream_context_create($opcoes);
    $respostaClima = @file_get_contents($urlClima, false, $contexto);

    if ($respostaClima !== false) {
        $dadosClima = json_decode($respostaClima, true);
        $valorTemperatura = $dadosClima['current']['temperature_2m'] ?? null;
        if (is_numeric($valorTemperatura)) {
            $temperaturaClima = number_format((float)$valorTemperatura, 0, ',', '.') . ' °C';
        }
    }

    $_SESSION['clima'] = [
        'texto' => $temperaturaClima,
        'hora' => time()
    ];
}

$sql = "SELECT
            (SELECT COUNT(*) FROM Rota) AS rotas,
            (SELECT COUNT(*) FROM Trem) AS trens,
            (SELECT COUNT(*) FROM Sensor) AS sensores,
            (SELECT COUNT(*) FROM Usuario) AS usuarios";
$resultado = $conexao->query($sql);
$totais = $resultado->fetch_assoc();

?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Início | Rail View</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
    <link rel="stylesheet" href="../Assets/css/inicio.css?v=2">
</head>

<body class="pagina">
    <div class="tela">

        <?php require '../infra/sidebar.php'; ?>

        <main class="conteudo">

            <header class="pagina-topo">
                <h1>Início</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <div class="pagina-corpo">

                <section class="painel" aria-labelledby="titulo-modulos">

                    <div class="painel-cabecalho">
                        <h2 id="titulo-modulos">Módulos</h2>
                    </div>

                    <ul class="modulos">
                        <li>
                            <a href="criacao_rotas.php">
                                <img src="../Assets/logos/icon_rotas.png" alt="">
                                <span class="modulo-nome">Rotas</span>
                                <span class="modulo-total"><?= (int)$totais['rotas'] ?> cadastradas</span>
                                <span class="modulo-acessar">Acessar</span>
                            </a>
                        </li>
                        <li>
                            <a href="trens_home.php">
                                <img src="../Assets/logos/icon_trem.png" alt="">
                                <span class="modulo-nome">Trens</span>
                                <span class="modulo-total"><?= (int)$totais['trens'] ?> cadastrados</span>
                                <span class="modulo-acessar">Acessar</span>
                            </a>
                        </li>
                        <li>
                            <a href="sensor_home.php">
                                <img src="../Assets/logos/icon_sensores.png" alt="">
                                <span class="modulo-nome">Sensores</span>
                                <span class="modulo-total"><?= (int)$totais['sensores'] ?> cadastrados</span>
                                <span class="modulo-acessar">Acessar</span>
                            </a>
                        </li>
                        <?php if (eh_admin()): ?>
                        <li>
                            <a href="usuario_lista.php">
                                <img class="icone-usuarios" src="../Assets/logos/icon_usuarios_contorno.png" alt="">
                                <span class="modulo-nome">Usuários</span>
                                <span class="modulo-total"><?= (int)$totais['usuarios'] ?> cadastrados</span>
                                <span class="modulo-acessar">Acessar</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>

                </section>

                <section class="painel clima" aria-labelledby="titulo-clima">
                    <div>
                        <h2 id="titulo-clima">Condições meteorológicas</h2>
                        <p id="clima-local">Joinville, SC</p>
                    </div>
                    <strong role="status"><?= htmlspecialchars($temperaturaClima, ENT_QUOTES, 'UTF-8') ?></strong>
                </section>

            </div>

        </main>
    </div>
</body>

</html>
