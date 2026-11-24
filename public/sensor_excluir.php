<?php
require_once '../infra/auth.php';
exigir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sensor_lista.php");
    exit();
}

exigir_confirmacao_exclusao('Deseja realmente excluir este sensor?');

$id_sensor = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id_sensor) {
    $_SESSION['mensagem'] = "<div class='alert alert-danger' role='alert'>
                                Nenhum ID válido foi fornecido para exclusão.
                            </div>";
    header("Location: sensor_lista.php");
    exit();
}

$stmt = $conexao->prepare("DELETE FROM Sensor WHERE id_sensor = ?");
$stmt->bind_param("i", $id_sensor);
$stmt->execute();
$excluiu = $stmt->affected_rows > 0;
$stmt->close();

if ($excluiu) {
    $_SESSION['mensagem'] = "<div class='alert alert-success' role='alert'>
                                Sensor excluído com sucesso!
                            </div>";
} else {
    $_SESSION['mensagem'] = "<div class='alert alert-warning' role='alert'>
                                Nenhum sensor foi encontrado com esse ID.
                            </div>";
}

header("Location: sensor_lista.php");
exit();
