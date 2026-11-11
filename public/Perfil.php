<?php
require_once '../infra/auth.php';
exigir_login();

$stmt = $conexao->prepare(
    "SELECT u.id_usuario, u.nome, u.email, p.nome_perfil,
            u.logradouro, u.bairro, u.cidade, u.uf, u.cep
     FROM Usuario u
     JOIN Perfil p ON p.id_perfil = u.id_perfil
     WHERE u.id_usuario = ?"
);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Monta o endereço em uma linha só, ignorando as partes vazias.
// array_filter tira os itens vazios; implode junta com ", ".
$partesEndereco = array_filter([
    $usuario['logradouro'],
    $usuario['bairro'],
    $usuario['cidade'],
    $usuario['uf'],
    $usuario['cep'],
]);
$endereco = $partesEndereco ? implode(', ', $partesEndereco) : 'Não informado';
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil</title>

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
                    <h1 class="titulo m-0">Meu Perfil</h1>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if (eh_admin()): ?>
                            <a href="usuario_lista.php" class="btn btn-dark text-white rounded-pill px-4 py-2">
                                <i class="bi bi-people-fill"></i> Gerenciar usuários
                            </a>
                        <?php endif; ?>
                        <a href="usuario_editar.php?id=<?= (int)$usuario['id_usuario'] ?>" class="btn btn-dark text-white rounded-pill px-4 py-2">
                            <i class="bi bi-pencil-fill"></i> Editar meus dados
                        </a>
                    </div>
                </div>

                <?php
                if (isset($_SESSION['mensagem'])) {
                    echo $_SESSION['mensagem'];
                    unset($_SESSION['mensagem']);
                }
                ?>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card-input">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card-input">
                            <label for="email">Email</label>
                            <input type="text" id="email" value="<?= htmlspecialchars($usuario['email']) ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card-input">
                            <label for="perfil">Perfil</label>
                            <input type="text" id="perfil" value="<?= htmlspecialchars($usuario['nome_perfil']) ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card-input">
                            <label for="endereco">Endereço</label>
                            <input type="text" id="endereco" value="<?= htmlspecialchars($endereco) ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6 d-flex justify-content-center align-items-center">
                        <form action="usuario_excluir.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Essa ação não pode ser desfeita.');">
                            <input type="hidden" name="id_usuario" value="<?= (int)$usuario['id_usuario'] ?>">
                            <button type="submit" class="btn btn-danger rounded-pill px-4 py-2">
                                <i class="bi bi-trash-fill"></i> Excluir minha conta
                            </button>
                        </form>
                    </div>
                </div>

            </section>

        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
