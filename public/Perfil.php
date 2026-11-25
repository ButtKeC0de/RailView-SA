<?php
require_once '../infra/auth.php';
exigir_login();

$sql = "SELECT u.id_usuario, u.nome, u.email, p.nome_perfil,
               u.logradouro, u.bairro, u.cidade, u.uf, u.cep
        FROM Usuario u
        JOIN Perfil p ON p.id_perfil = u.id_perfil
        WHERE u.id_usuario = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

$camposEndereco = [
    $usuario['logradouro'],
    $usuario['bairro'],
    $usuario['cidade'],
    $usuario['uf'],
    $usuario['cep']
];

$partesEndereco = [];
foreach ($camposEndereco as $campo) {
    if (!empty($campo)) {
        $partesEndereco[] = $campo;
    }
}

if (count($partesEndereco) > 0) {
    $endereco = implode(', ', $partesEndereco);
} else {
    $endereco = 'Não informado';
}
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

    <link rel="stylesheet" href="../Assets/css/conta.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sidebar.css?v=2">
    <link rel="stylesheet" href="../Assets/css/sistema.css?v=2">
</head>

<body class="conta pagina">
    <div class="tela">
        <?php require '../infra/sidebar.php'; ?>
        <main class="conteudo">
            <header class="conta-topo">
                <h1>Meu Perfil</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>

            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="conta-mensagem" role="status">
                    <?= $_SESSION['mensagem']; ?>
                    <?php unset($_SESSION['mensagem']); ?>
                </div>
            <?php endif; ?>

            <section class="conta-painel" aria-labelledby="dados-conta">
                <div class="conta-cabecalho">
                    <h2 id="dados-conta">Dados da conta</h2>
                    <div class="conta-acoes">
                        <?php if (eh_admin()): ?>
                            <a class="conta-botao conta-secundario" href="usuario_lista.php">Gerenciar usuários</a>
                        <?php endif; ?>
                        <a class="conta-botao conta-primario" href="usuario_editar.php?id=<?= (int)$usuario['id_usuario'] ?>">Editar meus dados</a>
                    </div>
                </div>
                <dl class="conta-dados">
                    <div><dt>Nome</dt><dd><?= htmlspecialchars($usuario['nome']) ?></dd></div>
                    <div><dt>E-mail</dt><dd><?= htmlspecialchars($usuario['email']) ?></dd></div>
                    <div><dt>Perfil de acesso</dt><dd><span class="conta-etiqueta"><?= htmlspecialchars($usuario['nome_perfil']) ?></span></dd></div>
                    <div><dt>Endereço</dt><dd><?= htmlspecialchars($endereco) ?></dd></div>
                </dl>
            </section>

            <section class="conta-painel conta-exclusao" aria-labelledby="excluir-conta">
                <div>
                    <h2 id="excluir-conta">Excluir minha conta</h2>
                    <p>Esta ação remove permanentemente seus dados de acesso.</p>
                </div>
                <form action="usuario_excluir.php" method="POST">
                    <input type="hidden" name="id_usuario" value="<?= (int)$usuario['id_usuario'] ?>">
                    <button type="submit" class="conta-botao conta-perigo">Excluir minha conta</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
