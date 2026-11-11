<?php
require_once '../infra/auth.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: Perfil.php");
    exit();
}

$id = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
$excluindoASiMesmo = $id === $_SESSION['usuario_id'];

if (!$excluindoASiMesmo && !eh_admin()) {
    negar_acesso();
}

$voltarPara = $excluindoASiMesmo ? 'Perfil.php' : 'usuario_lista.php';

$stmt = $conexao->prepare("SELECT nome, id_perfil FROM Usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION['mensagem'] = "<div class='alert alert-warning'>Usuário não encontrado.</div>";
    header("Location: $voltarPara");
    exit();
}

if ((int)$usuario['id_perfil'] === PERFIL_ADMIN) {
    $totalAdmins = $conexao->query("SELECT COUNT(*) FROM Usuario WHERE id_perfil = " . PERFIL_ADMIN)->fetch_row()[0];

    if ($totalAdmins <= 1) {
        $_SESSION['mensagem'] = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                    Não é possível excluir o único administrador do sistema. Promova outro usuário a administrador antes.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                </div>";
        header("Location: $voltarPara");
        exit();
    }
}

// Os trens deste usuário NÃO são apagados: eles só ficam sem responsável.
// A transação garante que as duas etapas acontecem juntas (ou nenhuma acontece).
$conexao->begin_transaction();

try {
    // 1) Tira o usuário como responsável dos trens dele.
    $stmt = $conexao->prepare("UPDATE Trem SET id_usuario = NULL WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 2) Agora o usuário pode ser excluído sem quebrar nenhum vínculo.
    $stmt = $conexao->prepare("DELETE FROM Usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conexao->commit();
} catch (mysqli_sql_exception $e) {
    // Deu erro em alguma etapa: desfaz tudo e avisa, sem mostrar detalhes do banco.
    $conexao->rollback();
    $_SESSION['mensagem'] = "<div class='alert alert-danger'>Não foi possível excluir o usuário. Tente novamente.</div>";
    header("Location: $voltarPara");
    exit();
}

if ($excluindoASiMesmo) {
    encerrar_sessao();
    header("Location: login.php");
    exit();
}

$_SESSION['mensagem'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            Usuário '<strong>" . htmlspecialchars($usuario['nome']) . "</strong>' excluído com sucesso!
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
header("Location: usuario_lista.php");
exit();
