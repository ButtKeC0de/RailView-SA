<?php
require_once '../infra/auth.php';
exigir_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$mensagem = '';

if (!$id || $id < 1) {
    $_SESSION['mensagem'] =
        "<div class='alert alert-danger'>ID inválido ou não informado.</div>";

    header('Location: trens_lista.php');
    exit();
}

$usuarios = [];

$resultado_usuarios = $conexao->query(
    "SELECT id_usuario, nome
     FROM Usuario
     ORDER BY nome"
);

if ($resultado_usuarios) {
    while ($usuario = $resultado_usuarios->fetch_assoc()) {
        $usuarios[] = $usuario;
    }
}

$sql_select = "SELECT
                   id_trem,
                   modelo,
                   apelido,
                   empresa_operadora AS operadora,
                   tipo_trem AS tipo,
                   numero_vagoes AS vagoes,
                   id_usuario
               FROM Trem
               WHERE id_trem = ?";

$stmt_select = $conexao->prepare($sql_select);
$stmt_select->bind_param('i', $id);
$stmt_select->execute();

$resultado = $stmt_select->get_result();
$dados_trem = $resultado->fetch_assoc();

$stmt_select->close();

if (!$dados_trem) {
    $_SESSION['mensagem'] =
        "<div class='alert alert-warning'>Trem não encontrado.</div>";

    header('Location: trens_lista.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = trim($_POST['modelo'] ?? '');
    $apelido = trim($_POST['apelido'] ?? '');
    $operadora = trim($_POST['operadora'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');

    $vagoes = filter_input(
        INPUT_POST,
        'vagoes',
        FILTER_VALIDATE_INT
    );

    $id_usuario = filter_input(
        INPUT_POST,
        'id_usuario',
        FILTER_VALIDATE_INT
    );

    $dados_trem['modelo'] = $modelo;
    $dados_trem['apelido'] = $apelido;
    $dados_trem['operadora'] = $operadora;
    $dados_trem['tipo'] = $tipo;
    $dados_trem['vagoes'] = $vagoes ?: '';
    $dados_trem['id_usuario'] = $id_usuario ?: null;

    if (
        $modelo === '' ||
        $apelido === '' ||
        $tipo === '' ||
        $vagoes === false ||
        $vagoes === null ||
        $vagoes < 1 ||
        !$id_usuario
    ) {
        $mensagem = "<div class='alert alert-danger'>
            Preencha todos os campos obrigatórios
            e selecione um responsável válido.
        </div>";
    } else {
        $stmt_usuario = $conexao->prepare(
            "SELECT id_usuario
             FROM Usuario
             WHERE id_usuario = ?"
        );

        $stmt_usuario->bind_param('i', $id_usuario);
        $stmt_usuario->execute();

        $usuario_existe =
            $stmt_usuario->get_result()->num_rows === 1;

        $stmt_usuario->close();

        if (!$usuario_existe) {
            $mensagem = "<div class='alert alert-danger'>
                O funcionário selecionado não existe.
            </div>";
        } else {
            $sql_update = "UPDATE Trem
                           SET modelo = ?,
                               apelido = ?,
                               tipo_trem = ?,
                               empresa_operadora = ?,
                               numero_vagoes = ?,
                               id_usuario = ?
                           WHERE id_trem = ?";

            $stmt_update = $conexao->prepare($sql_update);

            if ($stmt_update) {
                $stmt_update->bind_param(
                    'ssssiii',
                    $modelo,
                    $apelido,
                    $tipo,
                    $operadora,
                    $vagoes,
                    $id_usuario,
                    $id
                );

                if ($stmt_update->execute()) {
                    $_SESSION['mensagem'] =
                        "<div class='alert alert-success'>
                            Trem <strong>" .
                            htmlspecialchars(
                                $apelido,
                                ENT_QUOTES,
                                'UTF-8'
                            ) .
                            "</strong> atualizado com sucesso!
                        </div>";

                    $stmt_update->close();

                    header('Location: trens_lista.php');
                    exit();
                }

                $mensagem = "<div class='alert alert-danger'>
                    Não foi possível atualizar o trem.
                    Verifique os dados e tente novamente.
                </div>";

                $stmt_update->close();
            } else {
                $mensagem = "<div class='alert alert-danger'>
                    Não foi possível preparar a atualização do trem.
                </div>";
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Trem</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

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
                <h1 class="titulo m-0">Editar Trem</h1>

                <a href="trens_lista.php"
                   class="btn btn-dark text-white rounded-pill px-4 py-2">
                    ← Voltar para Lista
                </a>
            </div>

            <?= $mensagem ?>

            <?php if (empty($usuarios)): ?>
                <div class="alert alert-warning">
                    Não existem usuários cadastrados para selecionar.
                </div>
            <?php endif; ?>

            <?php if (empty($dados_trem['id_usuario'])): ?>
                <div class="alert alert-info">
                    Este trem ainda não possui um responsável.
                    Selecione um para salvar a alteração.
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="row g-4 align-items-center">

                    <div class="col-lg-8">
                        <div class="row g-4">

                            <div class="col-md-6">
                                <div class="card-input">
                                    <label for="modelo">Modelo do trem</label>

                                    <input
                                        type="text"
                                        id="modelo"
                                        name="modelo"
                                        value="<?= htmlspecialchars((string)$dados_trem['modelo'], ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Coloque o modelo"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card-input">
                                    <label for="apelido">Apelido do trem</label>

                                    <input
                                        type="text"
                                        id="apelido"
                                        name="apelido"
                                        value="<?= htmlspecialchars((string)$dados_trem['apelido'], ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Coloque o apelido"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card-input">
                                    <label for="operadora">Empresa operadora</label>

                                    <input
                                        type="text"
                                        id="operadora"
                                        name="operadora"
                                        value="<?= htmlspecialchars((string)$dados_trem['operadora'], ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Coloque a operadora"
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card-input">
                                    <label for="tipo">Tipo do trem</label>

                                    <input
                                        type="text"
                                        id="tipo"
                                        name="tipo"
                                        value="<?= htmlspecialchars((string)$dados_trem['tipo'], ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Coloque o tipo"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card-input">
                                    <label for="vagoes">Número de vagões</label>

                                    <input
                                        type="number"
                                        id="vagoes"
                                        name="vagoes"
                                        value="<?= htmlspecialchars((string)$dados_trem['vagoes'], ENT_QUOTES, 'UTF-8') ?>"
                                        placeholder="Coloque o número"
                                        min="1"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card-input">
                                    <label for="id_usuario">
                                        Funcionário responsável
                                    </label>

                                    <select
                                        id="id_usuario"
                                        name="id_usuario"
                                        class="form-select"
                                        required
                                    >
                                        <option value="">
                                            Selecione um responsável
                                        </option>

                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option
                                                value="<?= (int)$usuario['id_usuario'] ?>"
                                                <?= (string)($dados_trem['id_usuario'] ?? '') === (string)$usuario['id_usuario']
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                <?= htmlspecialchars(
                                                    $usuario['nome'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-4 d-flex justify-content-center align-items-center">
                        <div class="botao-container">
                            <button
                                type="submit"
                                class="btn-enviar"
                                <?= empty($usuarios) ? 'disabled' : '' ?>
                            >
                                Salvar
                            </button>
                        </div>
                    </div>

                </div>
            </form>

        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>