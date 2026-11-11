<?php
require_once '../infra/auth.php';
exigir_admin();

$usuarios = $conexao->query(
    "SELECT u.id_usuario, u.nome, u.email, u.id_perfil, p.nome_perfil
     FROM Usuario u
     JOIN Perfil p ON p.id_perfil = u.id_perfil
     ORDER BY u.id_perfil, u.nome"
)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../Assets/css/trem.css">
    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>

<body>

    <div class="app-container">

        <?php require '../infra/sidebar.php'; ?>
    <?php $usuarioFixo = true; require '../infra/usuario_topo.php'; ?>

        <main class="content">

            <section class="painel-cadastro">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <h1 class="titulo m-0">Usuários</h1>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="usuario_cadastro.php" class="btn btn-dark text-white rounded-pill px-4 py-2">
                            + Cadastrar usuário
                        </a>
                        <a href="Perfil.php" class="btn btn-dark text-white rounded-pill px-4 py-2">
                            ← Meu perfil
                        </a>
                    </div>
                </div>

                <?php
                if (isset($_SESSION['mensagem'])) {
                    echo $_SESSION['mensagem'];
                    unset($_SESSION['mensagem']);
                }
                ?>

                <div class="table-responsive rounded-4 shadow-sm">
                    <table class="table table-hover align-middle m-0 text-center custom-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Perfil</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <?php $ehVoce = (int)$usuario['id_usuario'] === $_SESSION['usuario_id']; ?>
                                <tr>
                                    <td class="fw-bold">
                                        <?= htmlspecialchars($usuario['nome']) ?>
                                        <?= $ehVoce ? '<span class="text-muted fw-normal">(você)</span>' : '' ?>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($usuario['nome_perfil']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="usuario_editar.php?id=<?= (int)$usuario['id_usuario'] ?>" class="btn btn-warning btn-sm text-white me-1 rounded-3" title="Editar">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <form action="usuario_excluir.php" method="POST" class="d-inline" onsubmit="return confirm(<?= htmlspecialchars(json_encode('Tem certeza que deseja excluir o usuário ' . $usuario['nome'] . '?')) ?>);">
                                            <input type="hidden" name="id_usuario" value="<?= (int)$usuario['id_usuario'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm rounded-3" title="Excluir">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </section>

        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
