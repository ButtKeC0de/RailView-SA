<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/mensagem.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const PERFIL_ADMIN = 1;
const PERFIL_FUNCIONARIO = 2;
const PERFIL_USUARIO = 3;

function eh_admin(): bool
{
    if (isset($_SESSION['id_perfil']) && $_SESSION['id_perfil'] === PERFIL_ADMIN) {
        return true;
    }

    return false;
}

function pode_editar(): bool
{
    if (!isset($_SESSION['id_perfil'])) {
        return false;
    }

    if ($_SESSION['id_perfil'] === PERFIL_ADMIN || $_SESSION['id_perfil'] === PERFIL_FUNCIONARIO) {
        return true;
    }

    return false;
}

function encerrar_sessao(): void
{
    $_SESSION = [];
    session_destroy();
}

function exigir_login(): void
{
    global $conexao;

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }

    $stmt = $conexao->prepare("SELECT nome, id_perfil FROM Usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        encerrar_sessao();
        header("Location: login.php");
        exit();
    }

    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['id_perfil'] = (int)$usuario['id_perfil'];
}

function exigir_admin(): void
{
    exigir_login();

    if (!eh_admin()) {
        negar_acesso();
    }
}

function exigir_pode_editar(): void
{
    exigir_login();

    if (!pode_editar()) {
        negar_acesso();
    }
}

function negar_acesso(): void
{
    http_response_code(403);
    exit("Acesso negado: seu perfil não tem permissão para esta página. <a href='Perfil.php'>Voltar</a>");
}

function exigir_confirmacao_exclusao(string $mensagem): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (isset($_POST['confirmar_exclusao'])) {
        return;
    }

    $acao = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
    ?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar exclusão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <section class="card shadow-sm mx-auto" style="max-width:560px">
            <div class="card-body p-4">
                <h1 class="h4">Confirmar exclusão</h1>
                <p><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
                <form method="POST" action="<?= $acao ?>">
                    <?php
                    foreach ($_POST as $chave => $valor) {
                        if (!is_array($valor)) {
                            echo '<input type="hidden" name="' . htmlspecialchars($chave, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') . '">';
                        }
                    }
                    ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-danger" type="submit" name="confirmar_exclusao" value="1">Sim, excluir</button>
                        <a class="btn btn-outline-secondary" href="home.php">Cancelar</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
<?php
    exit();
}
