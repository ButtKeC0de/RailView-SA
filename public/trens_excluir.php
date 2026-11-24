<?php
require_once '../infra/auth.php';
exigir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: trens_lista.php");
    exit();
}

exigir_confirmacao_exclusao('Deseja realmente excluir este trem?');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['mensagem'] = "<div class='alert alert-danger' role='alert'>
        Nenhum ID válido foi fornecido para exclusão.
    </div>";
    header("Location: trens_lista.php");
    exit();
}

$stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM Sensor WHERE id_trem = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$totalSensores = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM Historico_Rota WHERE id_trem = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$totalRotas = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

if ($totalSensores > 0 || $totalRotas > 0) {
    $_SESSION['mensagem'] = "<div class='alert alert-warning' role='alert'>
        O trem ID <strong>{$id}</strong> não pode ser excluído porque possui
        <strong>{$totalSensores}</strong> sensor(es) e <strong>{$totalRotas}</strong> rota(s) vinculados.
        Exclua ou troque o trem desses sensores e rotas primeiro.
    </div>";
    header("Location: trens_lista.php");
    exit();
}

$stmt = $conexao->prepare("DELETE FROM Trem WHERE id_trem = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$excluiu = $stmt->affected_rows > 0;
$stmt->close();

if ($excluiu) {
    $_SESSION['mensagem'] = "<div class='alert alert-success' role='alert'>
        Trem ID <strong>{$id}</strong> foi excluído com sucesso!
    </div>";
} else {
    $_SESSION['mensagem'] = "<div class='alert alert-warning' role='alert'>
        Nenhum trem foi encontrado com o ID <strong>{$id}</strong>.
    </div>";
}

header("Location: trens_lista.php");
exit();
