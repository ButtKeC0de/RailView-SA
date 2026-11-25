<?php

require_once '../infra/auth.php';
exigir_login();

$pesquisa = $_GET['pesquisa'] ?? '';

if ($pesquisa != '') {

    $pesquisaBanco = "%" . $pesquisa . "%";

    $stmt = $conexao->prepare("
        SELECT
            s.*,
            r.nome_rota,
            r.origem,
            r.destino
        FROM Sensor s
        LEFT JOIN Rota r ON s.id_rota = r.id_rota
        WHERE
            r.nome_rota LIKE ?
            OR r.origem LIKE ?
            OR r.destino LIKE ?
            OR s.tipo_sensor LIKE ?
            OR s.status_sensor LIKE ?
            OR s.id_sensor LIKE ?
        ORDER BY s.id_sensor DESC
    ");

    $stmt->bind_param(
        "ssssss",
        $pesquisaBanco,
        $pesquisaBanco,
        $pesquisaBanco,
        $pesquisaBanco,
        $pesquisaBanco,
        $pesquisaBanco
    );

    $stmt->execute();
    $resultado = $stmt->get_result();

} else {

    $resultado = $conexao->query("
        SELECT
            s.*,
            r.nome_rota,
            r.origem,
            r.destino
        FROM Sensor s
        LEFT JOIN Rota r ON s.id_rota = r.id_rota
        ORDER BY s.id_sensor DESC
    ");

}

?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sensores Cadastrados</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">

</head>

<body class="pagina">

<div class="tela">

    <?php require '../infra/sidebar.php'; ?>

    <main class="conteudo">

        <header class="pagina-topo">
            <h1>Sensores cadastrados</h1>
            <?php require '../infra/usuario_topo.php'; ?>
        </header>

        <div class="pagina-corpo">

            <?php if (isset($_SESSION['mensagem'])): ?>
                <?= $_SESSION['mensagem']; ?>
                <?php unset($_SESSION['mensagem']); ?>
            <?php endif; ?>

            <section class="painel">

                <div class="painel-cabecalho">
                    <form method="GET" action="sensor_home.php" class="busca">
                        <input
                            name="pesquisa"
                            type="text"
                            placeholder="Pesquisar sensor"
                            value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                        >
                        <button type="submit" class="botao botao-secundario">Pesquisar</button>
                    </form>

                    <?php if (pode_editar()): ?>
                        <div class="acoes">
                            <a href="sensor_cadastro.php" class="botao botao-primario">Cadastrar sensor</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($resultado && $resultado->num_rows > 0): ?>

                    <div class="tabela-area">
                        <table class="tabela">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Rota</th>
                                    <th>Origem e destino</th>
                                    <th>Status</th>
                                    <th class="coluna-acoes"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($sensor = $resultado->fetch_assoc()): ?>

                                    <?php

                                    $classeStatus = 'etiqueta-ativo';

                                    if ($sensor['status_sensor'] === 'Inativo') {

                                        $classeStatus = 'etiqueta-inativo';

                                    } elseif ($sensor['status_sensor'] === 'Manutenção') {

                                        $classeStatus = 'etiqueta-manutencao';

                                    }

                                    ?>

                                    <tr>
                                        <td class="texto-suave"><?= (int)$sensor['id_sensor'] ?></td>
                                        <td>
                                            <a class="link-linha" href="sensor_lista.php?id=<?= (int)$sensor['id_sensor'] ?>">
                                                <?= htmlspecialchars($sensor['tipo_sensor'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(
                                                $sensor['nome_rota'] ?? 'Rota não vinculada',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>
                                        <td class="texto-suave">
                                            <?php if ($sensor['origem'] !== null): ?>
                                                <?= htmlspecialchars($sensor['origem'], ENT_QUOTES, 'UTF-8') ?>
                                                →
                                                <?= htmlspecialchars($sensor['destino'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="etiqueta <?= $classeStatus ?>">
                                                <?= htmlspecialchars($sensor['status_sensor']) ?>
                                            </span>
                                        </td>
                                        <td class="coluna-acoes">
                                            <a class="botao botao-secundario botao-pequeno" href="sensor_lista.php?id=<?= (int)$sensor['id_sensor'] ?>">Ver detalhes</a>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <div class="estado-vazio">

                        <h3>Nenhum sensor cadastrado</h3>

                        <?php if (pode_editar()): ?>
                            <a href="sensor_cadastro.php" class="botao botao-primario">
                                + Cadastrar primeiro sensor
                            </a>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </section>

        </div>

    </main>

</div>

</body>

</html>
