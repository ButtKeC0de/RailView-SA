<?php

require_once '../infra/auth.php';
exigir_login();

$pesquisa = $_GET['pesquisa'] ?? '';

if ($pesquisa != '') {
    $pesquisaBanco = "%" . $pesquisa . "%";

    $stmt = $conexao->prepare("SELECT * FROM Trem WHERE apelido LIKE ? OR id_trem LIKE ? ORDER BY id_trem DESC");
    $stmt->bind_param("ss", $pesquisaBanco, $pesquisaBanco);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conexao->query("SELECT * FROM Trem ORDER BY id_trem DESC");
}

$temRotas = $conexao->query("SELECT 1 FROM Rota LIMIT 1")->num_rows > 0;

?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Trens Cadastrados</title>

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

            <h1 class="titulo_home"> Trens cadastrados</h1>

            <?php require '../infra/usuario_topo.php'; ?>

        </div>

        <form method="GET" action="trens_home.php" class="busca">
            <input name="pesquisa" type="text" placeholder="Pesquisar trem" value="<?= htmlspecialchars($pesquisa) ?>">
            <button type="submit">Pesquisar</button>
        </form>

        <?php if (isset($_SESSION['mensagem'])): ?>

            <?= $_SESSION['mensagem']; ?>

            <?php unset($_SESSION['mensagem']); ?>

        <?php endif; ?>

        <div id="trens">

            <?php if ($resultado && $resultado->num_rows > 0): ?>

                <?php while ($trem = $resultado->fetch_assoc()): ?>

                    <a class="trem" href="trens_lista.php?id=<?= (int)$trem['id_trem'] ?>">
                        <img src="../Assets/logos/Logo.png" alt="Imagem do trem" >
                        <p>ID:<?= (int)$trem['id_trem'] ?></p>
                        <p><?= htmlspecialchars( $trem['apelido'],ENT_QUOTES,'UTF-8') ?></p>
                    </a>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="estado-vazio estado-vazio--escuro estado-vazio--simples" style="grid-column: 1 / -1;">
                    <h3>Nenhum trem cadastrado</h3>
                    <?php if (pode_editar()): ?>
                        <a href="trens_cadastro.php" class="botao_laranja text-decoration-none">+ Cadastrar primeiro trem</a>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

        </div>

        <div class="parte_baixo_home">

            <?php if (pode_editar()): ?>

            <div class="card_acao">

                <h2> Cadastrar trem </h2>

                <form action="trens_cadastro.php"method="get">
                    <button type="submit" class="botao_laranja" > Cadastrar </button>
                </form>

            </div>

            <div class="card_acao">

                <h2> Gerenciar trens </h2>

                <form action="trens_lista.php" method="get">
                    <button type="submit" class="botao_laranja"> Gerenciar </button>
                </form>

            </div>

            <?php else: ?>

            <div class="card_acao">

                <h2> Consultar trens </h2>

                <form action="trens_lista.php" method="get">
                    <button type="submit" class="botao_laranja"> Ver lista </button>
                </form>

            </div>

            <?php endif; ?>

            <?php if ($temRotas): ?>
            <div class="card_mapa">

                <iframe
                    src="https://www.openstreetmap.org/export/embed.html?bbox=-48.98%2C-26.43%2C-48.72%2C-26.18&layer=mapnik"
                    title="Mapa das rotas"
                ></iframe>

            </div>
            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
