<?php
require_once '../infra/auth.php';
exigir_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: $_SESSION['usuario_id'];
$editandoASiMesmo = $id === $_SESSION['usuario_id'];

if (!$editandoASiMesmo && !eh_admin()) {
    negar_acesso();
}

$voltarPara = (eh_admin() && !$editandoASiMesmo) ? 'usuario_lista.php' : 'Perfil.php';

$stmt = $conexao->prepare("SELECT id_usuario, nome, email, id_perfil FROM Usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION['mensagem'] = "<div class='alert alert-warning'>Usuário não encontrado.</div>";
    header("Location: $voltarPara");
    exit();
}

$perfis = $conexao->query("SELECT id_perfil, nome_perfil FROM Perfil ORDER BY id_perfil")->fetch_all(MYSQLI_ASSOC);
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmaSenha = $_POST['confirma_senha'] ?? '';

    $id_perfil = eh_admin()
        ? filter_input(INPUT_POST, 'id_perfil', FILTER_VALIDATE_INT)
        : (int)$usuario['id_perfil'];

    $usuario['nome'] = $nome;
    $usuario['email'] = $email;

    $erro = '';

    if ($nome === '' || $email === '') {
        $erro = 'Preencha nome e email.';
    } elseif (mb_strlen($nome) > 100 || mb_strlen($email) > 100) {
        $erro = 'Nome e email podem ter no máximo 100 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um email válido.';
    } elseif (!in_array($id_perfil, [PERFIL_ADMIN, PERFIL_FUNCIONARIO, PERFIL_USUARIO], true)) {
        $erro = 'Perfil inválido.';
    } elseif ($novaSenha !== '' && mb_strlen($novaSenha) < 8) {
        $erro = 'A nova senha deve ter no mínimo 8 caracteres.';
    } elseif ($novaSenha !== $confirmaSenha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ? AND id_usuario <> ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $erro = 'Este email já está sendo usado por outro usuário.';
        }
        $stmt->close();
    }

    if ($erro === '' && (int)$usuario['id_perfil'] === PERFIL_ADMIN && $id_perfil !== PERFIL_ADMIN) {
        $totalAdmins = $conexao->query("SELECT COUNT(*) FROM Usuario WHERE id_perfil = " . PERFIL_ADMIN)->fetch_row()[0];
        if ($totalAdmins <= 1) {
            $erro = 'Este é o único administrador. Promova outro usuário a administrador antes de mudar este perfil.';
        }
    }

    if ($erro === '') {
        if ($novaSenha !== '') {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("UPDATE Usuario SET nome = ?, email = ?, id_perfil = ?, senha = ? WHERE id_usuario = ?");
            $stmt->bind_param("ssisi", $nome, $email, $id_perfil, $senhaHash, $id);
        } else {
            $stmt = $conexao->prepare("UPDATE Usuario SET nome = ?, email = ?, id_perfil = ? WHERE id_usuario = ?");
            $stmt->bind_param("ssii", $nome, $email, $id_perfil, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                        Usuário '<strong>" . htmlspecialchars($nome) . "</strong>' atualizado com sucesso!
                                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                    </div>";

            if ($editandoASiMesmo) {
                $voltarPara = 'Perfil.php';
            }

            header("Location: $voltarPara");
            exit();
        }

        $erro = 'Não foi possível atualizar o usuário. Tente novamente.';
        $stmt->close();
    }

    $usuario['id_perfil'] = $id_perfil ?: $usuario['id_perfil'];
    $mensagem = "<div class='alert alert-danger'>" . htmlspecialchars($erro) . "</div>";
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/css/conta.css">
    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>

<body class="conta">
    <div class="tela">
        <?php require '../infra/sidebar.php'; ?>
        <main class="conteudo">
            <header class="conta-topo">
                <h1><?= $editandoASiMesmo ? 'Editar meus dados' : 'Editar usuário' ?></h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>
            <section class="conta-painel" aria-labelledby="editar-dados">
                <div class="conta-cabecalho">
                    <h2 id="editar-dados">Dados de acesso</h2>
                    <a href="<?= $voltarPara ?>" class="conta-link">← Voltar</a>
                </div>
                <?php if (!empty($mensagem)): ?>
                    <div role="alert"><?= $mensagem ?></div>
                <?php endif; ?>
                <form action="" method="POST">
                    <div class="conta-campos">
                        <div class="conta-campo">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($usuario['nome']) ?>" autocomplete="name" required>
                        </div>
                        <div class="conta-campo">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($usuario['email']) ?>" autocomplete="email" required>
                        </div>
                        <div class="conta-campo">
                            <label for="nova_senha">Nova senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" minlength="8" autocomplete="new-password" aria-describedby="ajuda-senha">
                            <span id="ajuda-senha" class="conta-ajuda">Deixe em branco para manter a senha atual. Mínimo de 8 caracteres para uma nova senha.</span>
                        </div>
                        <div class="conta-campo">
                            <label for="confirma_senha">Confirmar nova senha</label>
                            <input type="password" id="confirma_senha" name="confirma_senha" minlength="8" autocomplete="new-password">
                        </div>
                        <?php if (eh_admin()): ?>
                        <div class="conta-campo">
                            <label for="id_perfil">Perfil de acesso</label>
                            <select id="id_perfil" name="id_perfil" required>
                                <?php foreach ($perfis as $perfil): ?>
                                    <option value="<?= (int)$perfil['id_perfil'] ?>" <?= (int)$usuario['id_perfil'] === (int)$perfil['id_perfil'] ? 'selected' : '' ?>><?= htmlspecialchars($perfil['nome_perfil']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="conta-rodape">
                        <a href="<?= $voltarPara ?>" class="conta-botao conta-secundario">Cancelar</a>
                        <button type="submit" class="conta-botao conta-primario">Salvar alterações</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
