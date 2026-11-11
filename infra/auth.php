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
    return ($_SESSION['id_perfil'] ?? null) === PERFIL_ADMIN;
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
    $usuario = $stmt->get_result()->fetch_assoc();
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

function negar_acesso(): void
{
    http_response_code(403);
    exit("Acesso negado: seu perfil não tem permissão para esta página. <a href='Perfil.php'>Voltar</a>");
}
