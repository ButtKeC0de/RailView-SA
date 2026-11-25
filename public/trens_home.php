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

?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Trens Cadastrados</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
</head>

<body class="pagina">

<div class="tela">

    <?php require '../infra/sidebar.php'; ?>

    <main class="conteudo">

        <header class="pagina-topo">
            <h1>Trens cadastrados</h1>
            <?php require '../infra/usuario_topo.php'; ?>
        </header>

        <div class="pagina-corpo">

            <?php if (isset($_SESSION['mensagem'])): ?>
                <?= $_SESSION['mensagem']; ?>
                <?php unset($_SESSION['mensagem']); ?>
            <?php endif; ?>

            <section class="painel">

                <div class="painel-cabecalho">
                    <form method="GET" action="trens_home.php" class="busca">
                        <input name="pesquisa" type="text" placeholder="Pesquisar trem" value="<?= htmlspecialchars($pesquisa) ?>">
                        <button type="submit" class="botao botao-secundario">Pesquisar</button>
                    </form>

                    <div class="acoes">
                        <?php if (pode_editar()): ?>
                            <a href="trens_lista.php" class="botao botao-secundario">Gerenciar trens</a>
                            <a href="trens_cadastro.php" class="botao botao-primario">Cadastrar trem</a>
                        <?php else: ?>
                            <a href="trens_lista.php" class="botao botao-secundario">Ver lista</a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($resultado && $resultado->num_rows > 0): ?>

                    <div class="tabela-area">
                        <table class="tabela">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Apelido</th>
                                    <th>Modelo</th>
                                    <th>Tipo</th>
                                    <th>Operadora</th>
                                    <th>Vagões</th>
                                    <th class="coluna-acoes"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($trem = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td class="texto-suave"><?= (int)$trem['id_trem'] ?></td>
                                        <td>
                                            <a class="link-linha" href="trens_lista.php?id=<?= (int)$trem['id_trem'] ?>">
                                                <?= htmlspecialchars($trem['apelido'], ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($trem['modelo'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="etiqueta"><?= htmlspecialchars($trem['tipo_trem'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td><?= htmlspecialchars($trem['empresa_operadora'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int)$trem['numero_vagoes'] ?></td>
                                        <td class="coluna-acoes">
                                            <a class="botao botao-secundario botao-pequeno" href="trens_lista.php?id=<?= (int)$trem['id_trem'] ?>">Ver detalhes</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <div class="estado-vazio">
                        <h3>Nenhum trem cadastrado</h3>
                        <?php if (pode_editar()): ?>
                            <a href="trens_cadastro.php" class="botao botao-primario">+ Cadastrar primeiro trem</a>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

            </section>

        </div>

    </main>

</div>

</body>

</html>
