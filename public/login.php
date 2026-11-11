<?php
require_once '../infra/auth.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: home.php");
    exit();
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha o usuário e a senha.';
    } else {
        $stmt = $conexao->prepare(
            "SELECT id_usuario, nome, senha, id_perfil FROM Usuario WHERE email = ? OR nome = ?"
        );
        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();
        $encontrados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $usuario = null;
        foreach ($encontrados as $encontrado) {
            if (password_verify($senha, $encontrado['senha'])) {
                $usuario = $encontrado;
                break;
            }
        }

        if ($usuario) {
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = (int)$usuario['id_usuario'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['id_perfil'] = (int)$usuario['id_perfil'];

            header("Location: home.php");
            exit();
        }

        $erro = 'Usuário ou senha incorretos.';
    }
}

$cadastroOk = isset($_GET['cadastro']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tela de Login</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../Assets/css/login.css">
</head>

<body>

    <main>

        <div>

            <video class="video_inicial" src="../Assets/videos/video.login.mp4" autoplay loop muted playsinline></video>

            <div class="container_login">
                <img src="../Assets/logos/logo_sem_fundo.png" alt="Logo" class="logo_login">

                <form id="formLogin" action="login.php" method="POST">

                    <!-- Avisos aparecem aqui dentro do card, no lugar do alert() -->
                    <div style="margin: 0 20px;">
                        <?php if ($erro !== ''): ?>
                            <?= mensagem('danger', $erro) ?>
                        <?php elseif ($cadastroOk): ?>
                            <?= mensagem('success', 'Cadastro realizado! Agora é só entrar.') ?>
                        <?php endif; ?>
                    </div>

                    <label class="label_login" for="email">
                        Email ou usuário
                    </label>

                    <input class="form_text" type="text" id="email" placeholder="Email ou usuário" required name="email" value="<?= htmlspecialchars($email) ?>">

                    <br>

                    <label class="label_login" for="senha">
                        Senha
                    </label>

                    <input class="form_text" type="password" id="senha" placeholder="Senha" required name="senha">

                    <br>

                    <div class="login_1">
                        <a href="../public/usuario_cadastro.php" id="text_log" style="text-decoration: none; color: inherit; display: block; cursor: pointer; text-align: center;">
                            Não possui conta? Cadastrar-se
                        </a>
                    </div>

                    <br>

                    <button id="button_login" type="submit">
                        Entrar
                    </button>

                </form>

            </div>

        </div>

    </main>

    <script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous">
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
        integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y"
        crossorigin="anonymous">
    </script>

</body>

</html>