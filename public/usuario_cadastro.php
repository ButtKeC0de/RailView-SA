<?php
require_once '../infra/auth.php';

exigir_admin();

$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmaSenha = $_POST['confirmaSenha'] ?? '';

    $cep = trim($_POST['cep'] ?? '');
    $logradouro = trim($_POST['logradouro'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $uf = strtoupper(trim($_POST['uf'] ?? ''));

    $id_perfil = filter_input(INPUT_POST, 'id_perfil', FILTER_VALIDATE_INT);

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';

    } elseif (mb_strlen($nome) > 100 || mb_strlen($email) > 100) {
        $erro = 'Nome e email podem ter no máximo 100 caracteres.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um email válido.';

    } elseif (mb_strlen($senha) < 8) {
        $erro = 'A senha deve ter no mínimo 8 caracteres.';

    } elseif ($senha !== $confirmaSenha) {
        $erro = 'As senhas não coincidem.';

    } elseif ($cep !== '' && !preg_match('/^\d{5}-?\d{3}$/', $cep)) {
        $erro = 'CEP inválido. Use o formato 00000-000.';

    } elseif ($uf !== '' && !preg_match('/^[A-Z]{2}$/', $uf)) {
        $erro = 'Estado (UF) deve ter 2 letras, ex: SC.';

    } elseif (!in_array($id_perfil, [PERFIL_ADMIN, PERFIL_FUNCIONARIO, PERFIL_USUARIO], true)) {
        $erro = 'Perfil inválido.';

    } else {
        $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $emailEmUso = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($emailEmUso) {
            $erro = 'Este email já está cadastrado.';
        }
    }

    if ($erro === '') {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $cep = $cep ?: null;
        $logradouro = $logradouro ?: null;
        $bairro = $bairro ?: null;
        $cidade = $cidade ?: null;
        $uf = $uf ?: null;

        $stmt = $conexao->prepare(
            "INSERT INTO Usuario (nome, email, senha, id_perfil, cep, logradouro, bairro, cidade, uf)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssisssss", $nome, $email, $senhaHash, $id_perfil, $cep, $logradouro, $bairro, $cidade, $uf);
        $cadastrou = $stmt->execute();
        $stmt->close();

        if ($cadastrou) {
            $_SESSION['mensagem'] = mensagem('success', "Usuário '$nome' cadastrado com sucesso!");
            header("Location: usuario_lista.php");
            exit();
        }

        $erro = 'Erro ao cadastrar usuário. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/css/sidebar.css">
    <link rel="stylesheet" href="../Assets/css/gestao.css">
</head>

<body class="cadastro-usuarios">
    <div class="tela">
        <?php require '../infra/sidebar.php'; ?>
        <main class="conteudo">
            <header class="cadastro-topo">
                <h1>Cadastrar usuário</h1>
                <?php require '../infra/usuario_topo.php'; ?>
            </header>
            <section class="cadastro-painel" aria-label="Dados do novo usuário">
                <form id="formCadastro" method="POST">
                    <?php if ($erro !== ''): ?>
                        <div class="cadastro-alerta" role="alert"><?= mensagem('danger', $erro) ?></div>
                    <?php endif; ?>
                    <fieldset>
                        <legend>Dados de acesso</legend>
                        <div class="cadastro-campos">
                            <div class="cadastro-campo">
                                <label for="nomeCadastro">Nome completo</label>
                                <input type="text" id="nomeCadastro" name="nome" value="<?= htmlspecialchars($nome) ?>" maxlength="100" autocomplete="name" required>
                            </div>
                            <div class="cadastro-campo">
                                <label for="emailCadastro">E-mail</label>
                                <input type="email" id="emailCadastro" name="email" value="<?= htmlspecialchars($email) ?>" maxlength="100" autocomplete="email" required>
                            </div>
                            <div class="cadastro-campo">
                                <label for="senhaCadastro">Senha</label>
                                <input type="password" id="senhaCadastro" name="senha" minlength="8" autocomplete="new-password" aria-describedby="ajuda-senha" required>
                                <span id="ajuda-senha" class="cadastro-ajuda">Mínimo de 8 caracteres.</span>
                            </div>
                            <div class="cadastro-campo">
                                <label for="confirmaSenha">Confirmar senha</label>
                                <input type="password" id="confirmaSenha" name="confirmaSenha" minlength="8" autocomplete="new-password" required>
                            </div>
                            <div class="cadastro-campo">
                                <label for="perfilCadastro">Perfil de acesso</label>
                                <select id="perfilCadastro" name="id_perfil" required>
                                    <option value="<?= PERFIL_USUARIO ?>" <?= ($id_perfil ?? PERFIL_USUARIO) === PERFIL_USUARIO ? 'selected' : '' ?>>Usuário</option>
                                    <option value="<?= PERFIL_FUNCIONARIO ?>" <?= ($id_perfil ?? PERFIL_USUARIO) === PERFIL_FUNCIONARIO ? 'selected' : '' ?>>Funcionário</option>
                                    <option value="<?= PERFIL_ADMIN ?>" <?= ($id_perfil ?? PERFIL_USUARIO) === PERFIL_ADMIN ? 'selected' : '' ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Endereço <span class="cadastro-opcional">Opcional</span></legend>
                        <div class="cadastro-campos">
                            <div class="cadastro-campo">
                                <label for="cep">CEP</label>
                                <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" autocomplete="postal-code" aria-describedby="mensagem-cep" value="<?= htmlspecialchars($cep ?? '') ?>">
                            </div>
                            <div class="cadastro-campo">
                                <label for="logradouro">Logradouro</label>
                                <input type="text" id="logradouro" name="logradouro" autocomplete="address-line1" value="<?= htmlspecialchars($logradouro ?? '') ?>">
                            </div>
                            <div class="cadastro-campo">
                                <label for="bairro">Bairro</label>
                                <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($bairro ?? '') ?>">
                            </div>
                            <div class="cadastro-campo">
                                <label for="cidade">Cidade</label>
                                <input type="text" id="cidade" name="cidade" autocomplete="address-level2" value="<?= htmlspecialchars($cidade ?? '') ?>">
                            </div>
                            <div class="cadastro-campo">
                                <label for="uf">Estado (UF)</label>
                                <input type="text" id="uf" name="uf" placeholder="SC" maxlength="2" autocomplete="address-level1" value="<?= htmlspecialchars($uf ?? '') ?>">
                            </div>
                        </div>
                        <p id="mensagem-cep" class="cadastro-ajuda" role="status" aria-live="polite"></p>
                    </fieldset>
                    <div class="cadastro-acoes">
                        <a href="usuario_lista.php" class="cadastro-botao cadastro-voltar">Voltar para usuários</a>
                        <button id="button_login" class="cadastro-botao cadastro-salvar" type="submit">Cadastrar usuário</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <script src="../scripts/cep.js"></script>
</body>
</html>
