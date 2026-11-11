<?php
require_once '../infra/auth.php';
exigir_login();

$mensagem = $_SESSION['mensagem_rota'] ?? null;
unset($_SESSION['mensagem_rota']);

$rotaForm = ['id_rota' => null, 'id_trem' => '', 'origem' => '', 'destino' => '', 'operadora' => ''];

if (($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['editar'])) && !eh_admin()) {
    negar_acesso();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'excluir') {
        $id_rota = filter_input(INPUT_POST, 'id_rota', FILTER_VALIDATE_INT);

        $stmt = $conexao->prepare("DELETE FROM Rota WHERE id_rota = ?");
        $stmt->bind_param("i", $id_rota);
        $excluiu = $stmt->execute() && $stmt->affected_rows > 0;
        $stmt->close();

        $_SESSION['mensagem_rota'] = $excluiu
            ? ['texto' => 'Rota excluída.', 'tipo' => 'sucesso']
            : ['texto' => 'Rota não encontrada.', 'tipo' => 'erro'];

        header("Location: criacao_rotas.php");
        exit();
    }

    $rotaForm = [
        'id_rota' => filter_input(INPUT_POST, 'id_rota', FILTER_VALIDATE_INT) ?: null,
        'id_trem' => filter_input(INPUT_POST, 'id_trem', FILTER_VALIDATE_INT) ?: '',
        'origem' => trim($_POST['origem'] ?? ''),
        'destino' => trim($_POST['destino'] ?? ''),
        'operadora' => trim($_POST['operadora'] ?? ''),
    ];

    $erro = '';

    if ($rotaForm['id_trem'] === '' || $rotaForm['origem'] === '' || $rotaForm['destino'] === '' || $rotaForm['operadora'] === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (mb_strlen($rotaForm['origem']) > 60 || mb_strlen($rotaForm['destino']) > 60 || mb_strlen($rotaForm['operadora']) > 150) {
        $erro = 'Origem e destino aceitam até 60 caracteres, e a operadora até 150.';
    } elseif (mb_strtolower($rotaForm['origem']) === mb_strtolower($rotaForm['destino'])) {
        $erro = 'A origem e o destino precisam ser diferentes.';
    } else {
        $stmt = $conexao->prepare("SELECT id_trem FROM Trem WHERE id_trem = ?");
        $stmt->bind_param("i", $rotaForm['id_trem']);
        $stmt->execute();
        $tremExiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$tremExiste) {
            $erro = 'O trem selecionado não existe.';
        }
    }

    if ($erro === '') {
        $nome_rota = $rotaForm['origem'] . ' - ' . $rotaForm['destino'];

        $conexao->begin_transaction();

        if ($rotaForm['id_rota']) {
            $stmt = $conexao->prepare(
                "UPDATE Rota SET nome_rota = ?, origem = ?, destino = ?, operadora = ? WHERE id_rota = ?"
            );
            $stmt->bind_param("ssssi", $nome_rota, $rotaForm['origem'], $rotaForm['destino'], $rotaForm['operadora'], $rotaForm['id_rota']);
            $ok = $stmt->execute();
            $stmt->close();

            $stmt = $conexao->prepare("SELECT id_historico FROM Historico_Rota WHERE id_rota = ?");
            $stmt->bind_param("i", $rotaForm['id_rota']);
            $stmt->execute();
            $temHistorico = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            $sqlHistorico = $temHistorico
                ? "UPDATE Historico_Rota SET id_trem = ? WHERE id_rota = ?"
                : "INSERT INTO Historico_Rota (data_inicio, id_trem, id_rota) VALUES (NOW(), ?, ?)";
        } else {
            $stmt = $conexao->prepare(
                "INSERT INTO Rota (nome_rota, origem, destino, operadora) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $nome_rota, $rotaForm['origem'], $rotaForm['destino'], $rotaForm['operadora']);
            $ok = $stmt->execute();
            $rotaForm['id_rota'] = $conexao->insert_id;
            $stmt->close();

            $sqlHistorico = "INSERT INTO Historico_Rota (data_inicio, id_trem, id_rota) VALUES (NOW(), ?, ?)";
        }

        if ($ok) {
            $stmt = $conexao->prepare($sqlHistorico);
            $stmt->bind_param("ii", $rotaForm['id_trem'], $rotaForm['id_rota']);
            $ok = $stmt->execute();
            $stmt->close();
        }

        if ($ok) {
            $conexao->commit();
            $_SESSION['mensagem_rota'] = ['texto' => 'Rota salva com sucesso!', 'tipo' => 'sucesso'];
            header("Location: criacao_rotas.php");
            exit();
        }

        $conexao->rollback();
        $erro = 'Erro ao salvar a rota no banco de dados.';
    }

    $mensagem = ['texto' => $erro, 'tipo' => 'erro'];

} elseif (isset($_GET['editar'])) {
    $id_rota = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);

    $stmt = $conexao->prepare(
        "SELECT r.id_rota, r.origem, r.destino, r.operadora, h.id_trem
         FROM Rota r
         LEFT JOIN Historico_Rota h ON h.id_rota = r.id_rota
         WHERE r.id_rota = ?
         LIMIT 1"
    );
    $stmt->bind_param("i", $id_rota);
    $stmt->execute();
    $rotaEditada = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($rotaEditada) {
        $rotaForm = $rotaEditada;
    } else {
        $mensagem = ['texto' => 'Rota não encontrada.', 'tipo' => 'erro'];
    }
}

$trens = $conexao->query("SELECT id_trem, apelido FROM Trem ORDER BY apelido")->fetch_all(MYSQLI_ASSOC);

$rotas = $conexao->query(
    "SELECT r.id_rota, r.origem, r.destino, r.operadora, h.id_trem, t.apelido
     FROM Rota r
     LEFT JOIN Historico_Rota h ON h.id_historico = (
         SELECT MAX(id_historico) FROM Historico_Rota WHERE id_rota = r.id_rota
     )
     LEFT JOIN Trem t ON t.id_trem = h.id_trem
     ORDER BY r.id_rota DESC"
)->fetch_all(MYSQLI_ASSOC);

$editando = !empty($rotaForm['id_rota']);
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cadastro e gerenciamento de rotas ferroviárias">

    <title>Cadastro de Rotas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/css/rotas.css">

    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>

<body>
    <div class="tela">


        <?php require '../infra/sidebar.php'; ?>


        <main class="conteudo">
            <header class="topo">
                <div>
                    <h1 class="titulo_home">Cadastro de Rota</h1>
                </div>

                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <section id="rotas_cadastradas" aria-label="Cadastro e listagem de rotas">
                <article id="trens" class="card">
                    <div class="card-cabecalho">
                        <div class="icone-card" aria-hidden="true">
                            <i class="bi bi-signpost-split-fill"></i>
                        </div>
                        <div>
                            <?php if (eh_admin()): ?>
                            <h2><?= $editando ? 'Editar Rota #' . (int)$rotaForm['id_rota'] : 'Cadastrar Rota' ?></h2>
                            <p>Informe os dados do <?= $editando ? 'trajeto' : 'novo trajeto' ?>.</p>
                            <?php else: ?>
                            <h2>Rotas</h2>
                            <p>Você pode consultar as rotas. Apenas administradores cadastram, editam e excluem.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (eh_admin()): ?>
                    <form class="formrota" id="formRota" method="POST" action="criacao_rotas.php" autocomplete="off">
                        <input type="hidden" name="acao" value="salvar">
                        <?php if ($editando): ?>
                            <input type="hidden" name="id_rota" value="<?= (int)$rotaForm['id_rota'] ?>">
                        <?php endif; ?>

                        <div class="campo">
                            <label for="idTrem">Trem</label>
                            <select id="idTrem" name="id_trem" required>
                                <option value=""><?= $trens ? 'Selecione um trem' : 'Cadastre um trem primeiro' ?></option>
                                <?php foreach ($trens as $trem): ?>
                                    <option value="<?= (int)$trem['id_trem'] ?>" <?= (int)$rotaForm['id_trem'] === (int)$trem['id_trem'] ? 'selected' : '' ?>>
                                        #<?= (int)$trem['id_trem'] ?> - <?= htmlspecialchars($trem['apelido']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="campo">
                            <label for="origem">Origem</label>
                            <input type="text" id="origem" name="origem" maxlength="60" placeholder="Ex.: Joinville" value="<?= htmlspecialchars($rotaForm['origem']) ?>" required>
                        </div>

                        <div class="campo">
                            <label for="destino">Destino</label>
                            <input type="text" id="destino" name="destino" maxlength="60" placeholder="Ex.: Curitiba" value="<?= htmlspecialchars($rotaForm['destino']) ?>" required>
                        </div>

                        <div class="campo">
                            <label for="operadora">Operadora</label>
                            <input type="text" id="operadora" name="operadora" maxlength="150" placeholder="Ex.: Rumo" value="<?= htmlspecialchars($rotaForm['operadora']) ?>" required>
                        </div>

                        <p id="mensagemFormulario" class="mensagem <?= htmlspecialchars($mensagem['tipo'] ?? '') ?>" role="status" aria-live="polite"><?= htmlspecialchars($mensagem['texto'] ?? '') ?></p>

                        <button class="btn-cadastrar" type="submit">
                            <i class="bi <?= $editando ? 'bi-check-lg' : 'bi-plus-lg' ?>" aria-hidden="true"></i>
                            <?= $editando ? 'Salvar alterações' : 'Cadastrar rota' ?>
                        </button>

                        <?php if ($editando): ?>
                            <a class="link-cancelar" href="criacao_rotas.php">Cancelar edição</a>
                        <?php endif; ?>
                    </form>
                    <?php endif; ?>
                </article>

                <article id="trens_2" class="card">
                    <div class="lista-topo">
                        <div>
                            <h2>Rotas Cadastradas</h2>
                            <p id="contadorRotas">Nenhuma rota cadastrada</p>
                        </div>

                        <label class="pesquisa" for="pesquisarRota">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="search" id="pesquisarRota" placeholder="Pesquisar rota">
                        </label>
                    </div>

                    <div id="listaRotas" aria-live="polite">
                        <?php foreach ($rotas as $rota): ?>
                            <article class="rota">
                                <div class="rota-id">Trem<br><?= $rota['id_trem'] ? (int)$rota['id_trem'] : '—' ?></div>
                                <div class="rota-trajeto">
                                    <strong><?= htmlspecialchars($rota['origem']) ?> → <?= htmlspecialchars($rota['destino']) ?></strong>
                                    <span>
                                        Rota #<?= (int)$rota['id_rota'] ?>
                                        · <?= htmlspecialchars($rota['operadora']) ?>
                                        <?= $rota['apelido'] ? '· ' . htmlspecialchars($rota['apelido']) : '' ?>
                                    </span>
                                </div>
                                <?php if (eh_admin()): ?>
                                <div class="rota-acoes">
                                    <a class="btn-editar" href="criacao_rotas.php?editar=<?= (int)$rota['id_rota'] ?>" title="Editar rota" aria-label="Editar rota de <?= htmlspecialchars($rota['origem']) ?> para <?= htmlspecialchars($rota['destino']) ?>">
                                        <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="criacao_rotas.php" class="form-excluir" data-trajeto="<?= htmlspecialchars($rota['origem'] . ' → ' . $rota['destino']) ?>">
                                        <input type="hidden" name="acao" value="excluir">
                                        <input type="hidden" name="id_rota" value="<?= (int)$rota['id_rota'] ?>">
                                        <button class="btn-excluir" type="submit" aria-label="Excluir rota de <?= htmlspecialchars($rota['origem']) ?> para <?= htmlspecialchars($rota['destino']) ?>" title="Excluir rota">
                                            <i class="bi bi-trash3-fill" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div id="estadoVazio" class="estado-vazio <?= $rotas ? 'oculto' : '' ?>">
                        <i class="bi bi-map" aria-hidden="true"></i>
                        <h3>Nenhuma rota cadastrada</h3>
                        <p>As novas rotas aparecerão aqui.</p>
                    </div>
                </article>
            </section>

            <section class="parte_baixo_home" aria-labelledby="tituloMapa">
                <div class="mapa-cabecalho">
                    <h2 id="tituloMapa">Mapa das rotas</h2>
                </div>

                <div class="card_mapa">
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox=-49.42%2C-25.62%2C-49.16%2C-25.36&amp;layer=mapnik"
                        title="Mapa das rotas"
                        loading="lazy">
                    </iframe>
                </div>
            </section>
        </main>
    </div>

    <script src="../scripts/rota.js"></script>
</body>

</html>