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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../Assets/css/trem.css">
    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>
<body>

<div class="app-container">
    <?php require '../infra/sidebar.php'; ?>
    <?php $usuarioFixo = true; require '../infra/usuario_topo.php'; ?>

    <main class="content">
        <section class="painel-cadastro">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="titulo m-0">Trens Cadastrados</h1>

                <?php if (eh_admin()): ?>
                    <a href="trens_cadastro.php"
                       class="btn btn-dark text-white rounded-pill px-4 py-2">
                        + Cadastrar Novo
                    </a>
                <?php endif; ?>
            </div>

            <?php
            if (isset($_SESSION['mensagem'])) {
                echo $_SESSION['mensagem'];
                unset($_SESSION['mensagem']);
            }
            ?>

            <?php if ($id): ?>
                <!-- Está mostrando só um trem: oferece voltar para a lista completa -->
                <p class="mb-3"><a href="trens_lista.php" class="btn btn-dark btn-sm rounded-pill px-3">&larr; Ver todos os trens</a></p>
            <?php endif; ?>

            <div class="table-responsive rounded-4 shadow-sm">
                <table class="table table-hover align-middle m-0 text-center custom-table">

                    <thead>
                        <tr>
                            <th>Modelo</th>
                            <th>Apelido</th>
                            <th>Operadora</th>
                            <th>Tipo</th>
                            <th>Vagões</th>
                            <th>Responsável</th>

                            <?php if (eh_admin()): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($trens)): ?>

                            <?php foreach ($trens as $trem): ?>
                                <tr>
                                    <td class="fw-bold">
                                        <?= htmlspecialchars(
                                            $trem['modelo'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $trem['apelido'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $trem['empresa_operadora'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <?php
                                    // Escolhe a cor da etiqueta pelo tipo do trem.
                                    // mb_strtolower deixa tudo minúsculo; str_contains procura a palavra.
                                    $tipoMinusculo = mb_strtolower($trem['tipo_trem']);

                                    if (str_contains($tipoMinusculo, 'carga')) {
                                        $classeTipo = 'tipo-carga';
                                    } elseif (str_contains($tipoMinusculo, 'passag')) {
                                        $classeTipo = 'tipo-passageiros';
                                    } elseif (str_contains($tipoMinusculo, 'misto')) {
                                        $classeTipo = 'tipo-misto';
                                    } else {
                                        $classeTipo = 'tipo-outro';
                                    }
                                    ?>

                                    <td>
                                        <span class="badge badge-tipo <?= $classeTipo ?>">
                                            <?= htmlspecialchars(
                                                $trem['tipo_trem'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= (int)$trem['numero_vagoes'] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $trem['responsavel'] ?? 'Não definido',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <?php if (eh_admin()): ?>
                                        <td>
                                            <a
                                                href="trens_editar.php?id=<?= (int)$trem['id_trem'] ?>"
                                                class="btn btn-warning btn-sm text-white me-1 rounded-3"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>

                                            <!-- Excluir usa formulário POST: só apaga ao clicar no botão -->
                                            <form action="trens_excluir.php" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir este trem?');">
                                                <input type="hidden" name="id" value="<?= (int)$trem['id_trem'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm rounded-3" title="Excluir">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td
                                    colspan="<?= eh_admin() ? 7 : 6 ?>"
                                    class="p-0"
                                >
                                    <!-- Estado vazio: ícone, explicação e atalho para cadastrar -->
                                    <div class="estado-vazio">
                                        <div class="estado-vazio-icone"><i class="bi bi-train-front"></i></div>
                                        <h3><?= $id ? 'Trem não encontrado' : 'Nenhum trem cadastrado' ?></h3>
                                        <p>Os trens cadastrados vão aparecer aqui.</p>
                                        <?php if (eh_admin()): ?>
                                            <a href="trens_cadastro.php" class="btn btn-dark rounded-pill px-4">+ Cadastrar primeiro trem</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>