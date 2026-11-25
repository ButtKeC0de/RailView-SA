<?php

require_once '../infra/auth.php';
exigir_admin();

// Só permite excluir através do método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: sensor_home.php");
    exit();

}

// Recebe o ID do sensor
$id_sensor = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id_sensor) {

    $_SESSION['mensagem'] = mensagem('danger', 'Sensor inválido.');

    header("Location: sensor_home.php");
    exit();

}

// Exclui o sensor do banco de dados
$stmt = $conexao->prepare("
    DELETE FROM Sensor
    WHERE id_sensor = ?
");

$stmt->bind_param("i", $id_sensor);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {

        $_SESSION['mensagem'] = mensagem(
            'success',
            'Sensor excluído com sucesso!'
        );

    } else {

        $_SESSION['mensagem'] = mensagem(
            'warning',
            'Sensor não encontrado.'
        );

    }

} else {

    $_SESSION['mensagem'] = mensagem(
        'danger',
        'Não foi possível excluir o sensor. Verifique se ele possui registros relacionados.'
    );

}

$stmt->close();

// Retorna para a página inicial
header("Location: sensor_home.php");
exit();

?>