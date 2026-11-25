<?php
require_once '../infra/auth.php';
exigir_admin();

$sql = "SELECT u.id_usuario, u.nome, u.email, u.id_perfil, p.nome_perfil
        FROM Usuario u
        JOIN Perfil p ON p.id_perfil = u.id_perfil
        ORDER BY u.id_perfil, u.nome";
$resultado = $conexao->query($sql);
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
</head>

<body class="pagina">

    <div class="tela">

        <?php require '../infra/sidebar.php'; ?>

        <main class="conteudo">

            <header class="pagina-topo">
                <h1>Usuários</h1>
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
                        <a href="home.php" class="botao botao-secundario">← Início</a>
                        <a href="usuario_cadastro.php" class="botao botao-primario">+ Cadastrar usuário</a>
                    </div>

                    <div class="tabela-area">
                        <table class="tabela">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Perfil</th>
                                    <th class="coluna-acoes">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($usuario = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
                                            <?php if ($usuario['id_usuario'] == $_SESSION['usuario_id']): ?>
                                                <span class="texto-suave">(você)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                                        <td>
                                            <span class="etiqueta">
                                                <?= htmlspecialchars($usuario['nome_perfil']) ?>
                                            </span>
                                        </td>
                                        <td class="coluna-acoes">
                                            <a href="usuario_editar.php?id=<?= (int)$usuario['id_usuario'] ?>" class="botao botao-secundario botao-pequeno" title="Editar">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>

                                            <form action="usuario_excluir.php" method="POST">
                                                <input type="hidden" name="id_usuario" value="<?= (int)$usuario['id_usuario'] ?>">
                                                <button type="submit" class="botao botao-perigo botao-pequeno" title="Excluir">
                                                    <i class="bi bi-trash"></i> Excluir
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                </section>

            </div>

        </main>

    </div>
</body>

</html>
