<?php
require_once '../infra/auth.php';
exigir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sensor_lista.php");
    exit();
}

$id_sensor = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id_sensor) {
    $_SESSION['mensagem'] = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                Nenhum ID válido foi fornecido para exclusão.
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
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
    $_SESSION['mensagem'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                Sensor excluído com sucesso!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
} else {
    $_SESSION['mensagem'] = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                                Nenhum sensor foi encontrado com esse ID.
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                            </div>";
}

header("Location: sensor_lista.php");
exit();
