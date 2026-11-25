<?php
require_once '../infra/auth.php';
exigir_login();

$trens = [];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$select = "SELECT
               t.id_trem,
               t.modelo,
               t.apelido,
               t.empresa_operadora,
               t.tipo_trem,
               t.numero_vagoes,
               t.id_usuario,
               u.nome AS responsavel
           FROM Trem t
           LEFT JOIN Usuario u
               ON u.id_usuario = t.id_usuario";

if ($id) {
    $sql = $select . " WHERE t.id_trem = ?";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $resultado = $stmt->get_result();
} else {
    $sql = $select . " ORDER BY t.id_trem DESC";
    $resultado = $conexao->query($sql);
}

if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $trens[] = $row;
    }
}

if (isset($stmt)) {
    $stmt->close();
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Trens</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
</head>
<body class="pagina">

<div class="tela">
    <?php require '../infra/sidebar.php'; ?>

    <main class="conteudo">

        <header class="pagina-topo">
            <h1>Trens Cadastrados</h1>
            <?php require '../infra/usuario_topo.php'; ?>
        </header>

        <div class="pagina-corpo">

            <?php
            if (isset($_SESSION['mensagem'])) {
                echo $_SESSION['mensagem'];
                unset($_SESSION['mensagem']);
            }
            ?>

            <section class="painel">

                <div class="painel-cabecalho">
                    <div class="acoes">
                        <a href="trens_home.php" class="botao botao-secundario">← Voltar</a>
                        <?php if ($id): ?>
                            <a href="trens_lista.php" class="botao botao-secundario">Ver todos os trens</a>
                        <?php endif; ?>
                    </div>

                    <?php if (pode_editar()): ?>
                        <a href="trens_cadastro.php" class="botao botao-primario">
                            + Cadastrar Novo
                        </a>
                    <?php endif; ?>
                </div>

                <div class="tabela-area">
                    <table class="tabela">

                        <thead>
                            <tr>
                                <th>Modelo</th>
                                <th>Apelido</th>
                                <th>Operadora</th>
                                <th>Tipo</th>
                                <th>Vagões</th>
                                <th>Responsável</th>

                                <?php if (pode_editar()): ?>
                                    <th class="coluna-acoes">Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($trens)): ?>

                                <?php foreach ($trens as $trem): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($trem['modelo'], ENT_QUOTES, 'UTF-8') ?></strong></td>

                                        <td><?= htmlspecialchars($trem['apelido'], ENT_QUOTES, 'UTF-8') ?></td>

                                        <td><?= htmlspecialchars($trem['empresa_operadora'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>

                                        <td>
                                            <span class="etiqueta">
                                                <?= htmlspecialchars($trem['tipo_trem'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>

                                        <td><?= (int)$trem['numero_vagoes'] ?></td>

                                        <td><?= htmlspecialchars($trem['responsavel'] ?? 'Não definido', ENT_QUOTES, 'UTF-8') ?></td>

                                        <?php if (pode_editar()): ?>
                                            <td class="coluna-acoes">
                                                <a
                                                    href="trens_editar.php?id=<?= (int)$trem['id_trem'] ?>"
                                                    class="botao botao-secundario botao-pequeno"
                                                    title="Editar"
                                                >
                                                    <i class="bi bi-pencil"></i> Editar
                                                </a>

                                                <?php if (eh_admin()): ?>
                                                <form action="trens_excluir.php" method="POST">
                                                    <input type="hidden" name="id" value="<?= (int)$trem['id_trem'] ?>">
                                                    <button type="submit" class="botao botao-perigo botao-pequeno" title="Excluir">
                                                        <i class="bi bi-trash"></i> Excluir
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="<?= pode_editar() ? 7 : 6 ?>">
                                        <div class="estado-vazio">
                                            <h3><?= $id ? 'Trem não encontrado' : 'Nenhum trem cadastrado' ?></h3>
                                            <?php if (pode_editar()): ?>
                                                <a href="trens_cadastro.php" class="botao botao-primario">+ Cadastrar primeiro trem</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

            </section>

        </div>

    </main>
</div>
</body>
</html>
