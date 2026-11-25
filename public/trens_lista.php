
<?php
require_once '../infra/auth.php';
exigir_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: trens_home.php");
    exit();
}

$sql = "
    SELECT
        t.id_trem,
        t.modelo,
        t.apelido,
        t.empresa_operadora,
        t.tipo_trem,
        t.numero_vagoes,
        t.id_usuario,
        u.nome AS responsavel
    FROM Trem t
    LEFT JOIN Usuario u ON u.id_usuario = t.id_usuario
    WHERE t.id_trem = ?
";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$trem = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$trem) {
    $_SESSION['mensagem'] = mensagem('warning', 'Trem não encontrado.');
    header("Location: trens_home.php");
    exit();
}
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detalhes do Trem</title>

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
                <h1>Detalhes do Trem</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <div class="pagina-corpo">

                <section class="painel">

                    <div class="painel-cabecalho">

                        <div class="acoes">
                            <h2>Trem #<?= (int)$trem['id_trem'] ?></h2>

                            <span class="etiqueta">
                                <?= htmlspecialchars($trem['tipo_trem'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="acoes">

                            <a href="trens_home.php" class="botao botao-secundario">
                                ← Voltar
                            </a>

                            <?php if (pode_editar()): ?>

                                <a
                                    href="trens_editar.php?id=<?= (int)$trem['id_trem'] ?>"
                                    class="botao botao-secundario"
                                >
                                    <i class="bi bi-pencil"></i>
                                    Editar
                                </a>

                                <?php if (eh_admin()): ?>

                                    <form
                                        action="trens_excluir.php"
                                        method="POST"
                                        onsubmit="return confirm('Deseja realmente excluir este trem?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$trem['id_trem'] ?>"
                                        >

                                        <button type="submit" class="botao botao-perigo">
                                            <i class="bi bi-trash"></i>
                                            Excluir
                                        </button>

                                    </form>

                                <?php endif; ?>

                            <?php endif; ?>

                        </div>

                    </div>

                    <dl class="dados">

                        <div>
                            <dt>Modelo do trem</dt>
                            <dd>
                                <strong>
                                    <?= htmlspecialchars($trem['modelo'], ENT_QUOTES, 'UTF-8') ?>
                                </strong>
                            </dd>
                        </div>

                        <div>
                            <dt>Apelido</dt>
                            <dd>
                                <?= htmlspecialchars($trem['apelido'], ENT_QUOTES, 'UTF-8') ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Empresa operadora</dt>
                            <dd>
                                <?= htmlspecialchars($trem['empresa_operadora'] ?? 'Não informada', ENT_QUOTES, 'UTF-8') ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Tipo do trem</dt>
                            <dd>
                                <?= htmlspecialchars($trem['tipo_trem'], ENT_QUOTES, 'UTF-8') ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Número de vagões</dt>
                            <dd>
                                <?= (int)$trem['numero_vagoes'] ?>
                            </dd>
                        </div>

                        <div>
                            <dt>Responsável</dt>
                            <dd>
                                <?= htmlspecialchars($trem['responsavel'] ?? 'Não definido', ENT_QUOTES, 'UTF-8') ?>
                            </dd>
                        </div>

                    </dl>

                </section>

            </div>

        </main>

    </div>

</body>

</html>
