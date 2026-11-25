<?php
require_once '../infra/auth.php';
exigir_login();

$mensagem = $_SESSION['mensagem_rota'] ?? null;
unset($_SESSION['mensagem_rota']);

$rotaForm = ['id_rota' => null, 'id_trem' => '', 'origem' => '', 'destino' => '', 'operadora' => ''];
$pesquisa = trim($_GET['pesquisa'] ?? '');

if (($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['editar'])) && !pode_editar()) {
    negar_acesso();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'excluir') {
        exigir_confirmacao_exclusao('Deseja realmente excluir esta rota?');
        if (!eh_admin()) {
            negar_acesso();
        }
        $id_rota = filter_input(INPUT_POST, 'id_rota', FILTER_VALIDATE_INT);

        $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM Sensor WHERE id_rota = ?");
        $stmt->bind_param("i", $id_rota);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $linha = $resultado->fetch_assoc();
        $totalSensores = (int)$linha['total'];
        $stmt->close();

        if ($totalSensores > 0) {
            $_SESSION['mensagem_rota'] = [
                'texto' => "A rota não pode ser excluída porque possui $totalSensores sensor(es) vinculado(s). Exclua ou troque a rota desses sensores primeiro.",
                'tipo' => 'erro'
            ];
            header("Location: criacao_rotas.php");
            exit();
        }

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

$sqlRotas = "SELECT r.id_rota, r.origem, r.destino, r.operadora, h.id_trem, t.apelido
     FROM Rota r
     LEFT JOIN Historico_Rota h ON h.id_historico = (
         SELECT MAX(id_historico) FROM Historico_Rota WHERE id_rota = r.id_rota
     )
     LEFT JOIN Trem t ON t.id_trem = h.id_trem";
if ($pesquisa !== '') {
    $like = '%' . $pesquisa . '%';
    $stmt = $conexao->prepare($sqlRotas . " WHERE r.origem LIKE ? OR r.destino LIKE ? OR r.operadora LIKE ? OR t.apelido LIKE ? OR CAST(r.id_rota AS CHAR) LIKE ? ORDER BY r.id_rota DESC");
    $stmt->bind_param("sssss", $like, $like, $like, $like, $like);
    $stmt->execute();
    $rotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $rotas = $conexao->query($sqlRotas . " ORDER BY r.id_rota DESC")->fetch_all(MYSQLI_ASSOC);
}

$editando = !empty($rotaForm['id_rota']);
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cadastro e gerenciamento de rotas ferroviárias">
    <title>Cadastro de Rota | Rail View</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/gestao.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
    <link rel="stylesheet" href="../Assets/css/rotas.css?v=2">
</head>

<body class="cadastro-usuarios pagina">
    <div class="tela">

        <?php require '../infra/sidebar.php'; ?>

        <main class="conteudo">

            <header class="pagina-topo">
                <h1>Cadastro de Rota</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <div class="pagina-corpo">

                <?php if (!empty($mensagem['texto'])): ?>
                    <div id="mensagemFormulario" class="alert <?= ($mensagem['tipo'] ?? '') === 'sucesso' ? 'alert-success' : 'alert-danger' ?>" role="status" aria-live="polite">
                        <?= htmlspecialchars($mensagem['texto']) ?>
                    </div>
                <?php endif; ?>

                <?php if (pode_editar()): ?>
                <section class="painel" aria-labelledby="tituloFormulario">
                    <form id="formRota" method="POST" action="criacao_rotas.php" autocomplete="off">
                        <input type="hidden" name="acao" value="salvar">
                        <?php if ($editando): ?>
                            <input type="hidden" name="id_rota" value="<?= (int)$rotaForm['id_rota'] ?>">
                        <?php endif; ?>

                        <fieldset>
                            <legend id="tituloFormulario"><?= $editando ? 'Editar Rota #' . (int)$rotaForm['id_rota'] : 'Cadastrar Rota' ?></legend>
                            <div class="cadastro-campos">
                                <div class="cadastro-campo">
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

                                <div class="cadastro-campo">
                                    <label for="operadora">Operadora</label>
                                    <input type="text" id="operadora" name="operadora" maxlength="150" placeholder="Ex.: Rumo" value="<?= htmlspecialchars($rotaForm['operadora']) ?>" required>
                                </div>

                                <div class="cadastro-campo">
                                    <label for="origem">Origem</label>
                                    <input type="text" id="origem" name="origem" maxlength="60" placeholder="Ex.: Joinville" value="<?= htmlspecialchars($rotaForm['origem']) ?>" required>
                                </div>

                                <div class="cadastro-campo">
                                    <label for="destino">Destino</label>
                                    <input type="text" id="destino" name="destino" maxlength="60" placeholder="Ex.: Curitiba" value="<?= htmlspecialchars($rotaForm['destino']) ?>" required>
                                </div>
                            </div>
                        </fieldset>

                        <div class="cadastro-acoes">
                            <?php if ($editando): ?>
                                <a class="cadastro-botao cadastro-voltar" href="criacao_rotas.php">Cancelar edição</a>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <button class="cadastro-botao cadastro-salvar" type="submit">
                                <?= $editando ? 'Salvar alterações' : 'Cadastrar rota' ?>
                            </button>
                        </div>
                    </form>
                </section>
                <?php endif; ?>

                <section class="painel" aria-labelledby="tituloLista">

                    <div class="painel-cabecalho">
                        <h2 id="tituloLista">Rotas Cadastradas</h2>

                        <form class="busca" method="GET" action="criacao_rotas.php">
                            <input type="search" id="pesquisarRota" name="pesquisa" value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>" placeholder="Pesquisar rota" aria-label="Pesquisar rota por trem, origem ou destino">
                            <button type="submit" class="botao botao-secundario">Pesquisar</button>
                        </form>
                    </div>

                    <?php if ($rotas): ?>
                    <div class="tabela-area" id="listaRotas" aria-live="polite">
                        <table class="tabela">
                            <thead>
                                <tr>
                                    <th>Rota</th>
                                    <th>Trajeto</th>
                                    <th>Trem</th>
                                    <th>Operadora</th>
                                    <?php if (pode_editar()): ?>
                                        <th class="coluna-acoes">Ações</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rotas as $rota): ?>
                                    <tr>
                                        <td class="texto-suave">#<?= (int)$rota['id_rota'] ?></td>
                                        <td><strong><?= htmlspecialchars($rota['origem']) ?> → <?= htmlspecialchars($rota['destino']) ?></strong></td>
                                        <td>
                                            <?php if ($rota['id_trem']): ?>
                                                <span class="etiqueta">Trem <?= (int)$rota['id_trem'] ?></span>
                                                <?= $rota['apelido'] ? htmlspecialchars($rota['apelido']) : '' ?>
                                            <?php else: ?>
                                                <span class="texto-suave">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($rota['operadora']) ?></td>
                                        <?php if (pode_editar()): ?>
                                        <td class="coluna-acoes">
                                            <a class="botao botao-secundario botao-pequeno" href="criacao_rotas.php?editar=<?= (int)$rota['id_rota'] ?>" title="Editar rota" aria-label="Editar rota de <?= htmlspecialchars($rota['origem']) ?> para <?= htmlspecialchars($rota['destino']) ?>">
                                                <i class="bi bi-pencil" aria-hidden="true"></i> Editar
                                            </a>
                                            <?php if (eh_admin()): ?>
                                            <form method="POST" action="criacao_rotas.php">
                                                <input type="hidden" name="acao" value="excluir">
                                                <input type="hidden" name="id_rota" value="<?= (int)$rota['id_rota'] ?>">
                                                <button class="botao botao-perigo botao-pequeno" type="submit" aria-label="Excluir rota de <?= htmlspecialchars($rota['origem']) ?> para <?= htmlspecialchars($rota['destino']) ?>" title="Excluir rota">
                                                    <i class="bi bi-trash" aria-hidden="true"></i> Excluir
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div id="estadoVazio" class="estado-vazio">
                        <h3><?= $pesquisa !== '' ? 'Nenhuma rota encontrada' : 'Nenhuma rota cadastrada' ?></h3>
                    </div>
                    <?php endif; ?>

                </section>

                <section class="painel" aria-labelledby="tituloMapa">
                    <div class="painel-cabecalho">
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

            </div>

        </main>
    </div>
</body>

</html>
