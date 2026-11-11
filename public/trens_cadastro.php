<?php
require_once '../infra/auth.php';
exigir_admin();

$mensagem = '';
$modelo = '';
$apelido = '';
$operadora = '';
$tipo = '';
$vagoes = '';
$id_usuario = '';

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
            Preencha os campos obrigatórios e selecione um responsável válido.
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
            $sql = "INSERT INTO Trem (
                        modelo,
                        apelido,
                        tipo_trem,
                        empresa_operadora,
                        numero_vagoes,
                        id_usuario
                    ) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            if ($stmt) {
                $stmt->bind_param(
                    'ssssii',
                    $modelo,
                    $apelido,
                    $tipo,
                    $operadora,
                    $vagoes,
                    $id_usuario
                );

                if ($stmt->execute()) {
                    $_SESSION['mensagem'] =
                        "<div class='alert alert-success'>
                            Trem <strong>" .
                            htmlspecialchars(
                                $apelido,
                                ENT_QUOTES,
                                'UTF-8'
                            ) .
                            "</strong> cadastrado com sucesso!
                        </div>";

                    $stmt->close();

                    header('Location: trens_lista.php');
                    exit();
                }

                $mensagem = "<div class='alert alert-danger'>
                    Não foi possível cadastrar o trem.
                    Verifique os dados e tente novamente.
                </div>";

                $stmt->close();
            } else {
                $mensagem = "<div class='alert alert-danger'>
                    Não foi possível preparar o cadastro.
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
    <title>Cadastrar Trem</title>

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
                <h1 class="titulo m-0">Cadastrar trem</h1>

                <a href="trens_lista.php"
                   class="btn btn-dark text-white rounded-pill px-4 py-2">
                    ← Ver Lista
                </a>
            </div>

            <?= $mensagem ?>

            <?php if (empty($usuarios)): ?>
                <div class="alert alert-warning">
                    Não existem usuários cadastrados.
                    Cadastre um usuário antes de registrar um trem.
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
                                        value="<?= htmlspecialchars($modelo, ENT_QUOTES, 'UTF-8') ?>"
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
                                        value="<?= htmlspecialchars($apelido, ENT_QUOTES, 'UTF-8') ?>"
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
                                        value="<?= htmlspecialchars($operadora, ENT_QUOTES, 'UTF-8') ?>"
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
                                        value="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>"
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
                                        value="<?= htmlspecialchars((string)($vagoes ?: ''), ENT_QUOTES, 'UTF-8') ?>"
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
                                                <?= (string)$id_usuario === (string)$usuario['id_usuario']
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
                                Enviar
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