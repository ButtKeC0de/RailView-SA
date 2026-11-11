<?php
require_once '../infra/auth.php';
exigir_admin();

// Exclusão só acontece por formulário (POST).
// Se alguém só abrir o link no navegador (GET), nada é apagado.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: trens_lista.php");
    exit();
}

// 1) Pega o ID enviado pelo formulário e garante que é um número inteiro.
//    Se vier qualquer outra coisa (texto, vazio, código), $id fica false/null.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['mensagem'] = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        Nenhum ID válido foi fornecido para exclusão.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    header("Location: trens_lista.php");
    exit();
}

// 2) Antes de excluir, conta quantos sensores estão instalados neste trem.
$stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM Sensor WHERE id_trem = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$totalSensores = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// 3) Conta também em quantas rotas este trem está vinculado.
$stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM Historico_Rota WHERE id_trem = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$totalRotas = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// 4) Regra de negócio: trem com sensores ou rotas NÃO pode ser excluído.
//    Assim nenhum sensor ou rota some "sem querer" junto com o trem.
if ($totalSensores > 0 || $totalRotas > 0) {
    $_SESSION['mensagem'] = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
        O trem ID <strong>{$id}</strong> não pode ser excluído porque possui
        <strong>{$totalSensores}</strong> sensor(es) e <strong>{$totalRotas}</strong> rota(s) vinculados.
        Exclua ou troque o trem desses sensores e rotas primeiro.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    header("Location: trens_lista.php");
    exit();
}

// 5) Sem vínculos: pode excluir com segurança.
$stmt = $conexao->prepare("DELETE FROM Trem WHERE id_trem = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$excluiu = $stmt->affected_rows > 0;
$stmt->close();

if ($excluiu) {
    $_SESSION['mensagem'] = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        Trem ID <strong>{$id}</strong> foi excluído com sucesso!
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
} else {
    $_SESSION['mensagem'] = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
        Nenhum trem foi encontrado com o ID <strong>{$id}</strong>.
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}

header("Location: trens_lista.php");
exit();
