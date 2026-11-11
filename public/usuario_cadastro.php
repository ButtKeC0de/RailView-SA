<?php
require_once '../infra/auth.php';

if (isset($_SESSION['usuario_id'])) {
    exigir_login();
}
$adminCadastrando = eh_admin();

// Variáveis do formulário (começam vazias).
$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmaSenha = $_POST['confirmaSenha'] ?? '';

    // Endereço (opcional). Se vier vazio, guardamos NULL no banco.
    $cep = trim($_POST['cep'] ?? '');
    $logradouro = trim($_POST['logradouro'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $uf = strtoupper(trim($_POST['uf'] ?? ''));

    $id_perfil = $adminCadastrando
        ? filter_input(INPUT_POST, 'id_perfil', FILTER_VALIDATE_INT)
        : PERFIL_USUARIO;

    // Validações: a primeira que falhar define a mensagem de erro.
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
        // Aceita 00000-000 ou 00000000
        $erro = 'CEP inválido. Use o formato 00000-000.';

    } elseif ($uf !== '' && !preg_match('/^[A-Z]{2}$/', $uf)) {
        $erro = 'Estado (UF) deve ter 2 letras, ex: SC.';

    } elseif (!in_array($id_perfil, [PERFIL_ADMIN, PERFIL_FUNCIONARIO, PERFIL_USUARIO], true)) {
        $erro = 'Perfil inválido.';

    } else {
        // Confere se o email já está em uso.
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
        // Nunca salvamos a senha pura: password_hash gera uma versão criptografada.
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Campo vazio vira NULL (o operador ?: troca '' por null).
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
            if ($adminCadastrando) {
                $_SESSION['mensagem'] = mensagem('success', "Usuário '$nome' cadastrado com sucesso!");
                header("Location: usuario_lista.php");
            } else {
                header("Location: login.php?cadastro=ok");
            }
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

    <link rel="stylesheet" href="../Assets/css/cadastro.css">
</head>

<body>

    <main>

        <div>

            <video
                class="video_inicial"
                src="../Assets/videos/video.login.mp4"
                autoplay
                loop
                muted
                playsinline>
            </video>

            <div class="container_login_2">

                <img
                    src="../Assets/logos/logo_sem_fundo.png"
                    alt="Logo"
                    class="logo_login">

                <div id="form">

                    <form id="formCadastro" method="POST">

                        <?php if ($erro !== ''): ?>
                            <!-- Mostra o erro aqui dentro do card, no lugar do alert() -->
                            <div style="margin: 0 20px;"><?= mensagem('danger', $erro) ?></div>
                        <?php endif; ?>

                        <label class="label_login" for="nomeCadastro">
                            Nome completo
                        </label>

                        <input
                            class="form_text"
                            type="text"
                            id="nomeCadastro"
                            name="nome"
                            placeholder="Digite seu nome completo"
                            value="<?= htmlspecialchars($nome) ?>"
                            required>

                        <label class="label_login" for="emailCadastro">
                            Email
                        </label>

                        <input
                            class="form_text"
                            type="email"
                            id="emailCadastro"
                            placeholder="Digite seu email"
                            required
                            name="email"
                            value="<?= htmlspecialchars($email) ?>">

                        <br>

                        <label class="label_login" for="senhaCadastro">
                            Senha
                        </label>

                        <input
                            class="form_text"
                            type="password"
                            id="senhaCadastro"
                            minlength="8"
                            placeholder="Digite sua senha"
                            required
                            name="senha">

                        <br>

                        <label class="label_login" for="confirmaSenha">
                            Confirmar Senha
                        </label>

                        <input
                            class="form_text"
                            type="password"
                            id="confirmaSenha"
                            minlength="8"
                            placeholder="Confirme sua senha"
                            required
                            name="confirmaSenha">

                        <br>
                        
<label class="label_login" for="cep">CEP</label>
<input
    class="form_text"
    type="text"
    id="cep"
    name="cep"
    placeholder="00000-000"
    maxlength="9"
    autocomplete="postal-code"
>

<label class="label_login" for="logradouro">Logradouro</label>
<input
    class="form_text"
    type="text"
    id="logradouro"
    name="logradouro"
    placeholder="Rua ou avenida"
    autocomplete="address-line1"
>

<label class="label_login" for="bairro">Bairro</label>
<input
    class="form_text"
    type="text"
    id="bairro"
    name="bairro"
>

<label class="label_login" for="cidade">Cidade</label>
<input
    class="form_text"
    type="text"
    id="cidade"
    name="cidade"
    autocomplete="address-level2"
>

<label class="label_login" for="uf">Estado (UF)</label>
<input
    class="form_text"
    type="text"
    id="uf"
    name="uf"
    placeholder="SC"
    maxlength="2"
    autocomplete="address-level1"
>

<p id="mensagem-cep" role="status"></p>

                        <?php if ($adminCadastrando): ?>

                        <label class="label_login" for="perfilCadastro">
                            Perfil
                        </label>

                        <select class="form_text" id="perfilCadastro" name="id_perfil" required>
                            <option value="<?= PERFIL_USUARIO ?>">Usuário</option>
                            <option value="<?= PERFIL_FUNCIONARIO ?>">Funcionário</option>
                            <option value="<?= PERFIL_ADMIN ?>">Administrador</option>
                        </select>

                        <br>

                        <div class="login_1">
                            <a href="usuario_lista.php" style="text-decoration: none; color: inherit; display: block; text-align: center;">
                                ← Voltar para a lista de usuários
                            </a>
                        </div>

                        <?php else: ?>

                        <div class="login_1">

                            <input
                                class="checkbox_login"
                                type="checkbox"
                                name="termos"
                                id="termos"
                                required>

                            <label for="termos">
                                Aceito os
                            </label>

                            <a
                                href="../public/termo_de_uso.html"
                                target="_blank"
                                id="linkTermos">
                                Termos de Uso
                            </a>

                        </div>

                        <div class="login_1">

                            <a
                                href="../public/login.php"
                                id="text_log"
                                style="
                                    text-decoration: none;
                                    color: inherit;
                                    display: block;
                                    cursor: pointer;
                                    text-align: center;
                                ">
                                Já possui conta? Voltar para o Login.
                            </a>

                        </div>

                        <?php endif; ?>

                        <br>

                        <button id="button_login" type="submit">
                            Cadastrar
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </main>

    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous">
    </script>
    <script src="../scripts/cep.js"></script>
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y"
        crossorigin="anonymous">
    </script>

    

</body>

</html>