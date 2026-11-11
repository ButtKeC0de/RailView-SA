<?php
require_once '../infra/auth.php';
exigir_login();


$mensagem = "";
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
}

// Se vier ?id=5 na URL (clique num sensor da tela anterior), mostra só esse sensor.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$select = "SELECT
               s.id_sensor,
               s.tipo_sensor,
               s.localizacao,
               s.status_sensor,
               s.empresa_operadora,
               s.id_trem,
               t.apelido AS nome_trem,
               t.modelo AS modelo_trem
           FROM Sensor s
           LEFT JOIN Trem t ON s.id_trem = t.id_trem";

if ($id) {
    // Busca um sensor só (consulta preparada, porque usa dado da URL).
    $stmt = $conexao->prepare($select . " WHERE s.id_sensor = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
} else {
    // Busca todos os sensores.
    $resultado = $conexao->query($select . " ORDER BY s.id_sensor DESC");
}

$sensores = [];

if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $sensores[] = $row;
    }
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Sensores - Rail View</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/css/sensor.css">
    <link rel="stylesheet" href="../Assets/css/sidebar.css">
</head>

<body>

    <div class="app-container d-flex">

        <?php require '../infra/sidebar.php'; ?>
    <?php $usuarioFixo = true; require '../infra/usuario_topo.php'; ?>

        <main class="content flex-grow-1 p-4">

            <section class="painel-cadastro">

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <h1 class="titulo m-0">Sensores Cadastrados</h1>
                    <?php if (eh_admin()): ?>
                    <a href="sensor_cadastro.php" class="btn btn-dark text-white rounded-pill px-4 py-2">
                         Cadastrar Sensor
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($mensagem)) echo $mensagem; ?>

                <?php if ($id): ?>
                    <!-- Está mostrando só um sensor: oferece voltar para a lista completa -->
                    <p class="mb-3"><a href="sensor_lista.php" class="btn btn-dark btn-sm rounded-pill px-3">&larr; Ver todos os sensores</a></p>
                <?php endif; ?>

                <div class="mb-4">
                    <input type="text" id="campoBusca" class="form-control input-busca" placeholder="🔍 Pesquisar por tipo, localização, operadora ou trem...">
                </div>

                <div id="listaSensores">
                    <?php if (count($sensores) > 0): ?>
                        <div class="row g-3">
                            <?php foreach ($sensores as $sensor): ?>
                                <?php 
                                    $statusClass = 'badge-ativo';
                                    if (mb_strtolower($sensor['status_sensor']) === 'inativo') {
                                        $statusClass = 'badge-inativo';
                                    } elseif (mb_strtolower($sensor['status_sensor']) === 'manutenção' || mb_strtolower($sensor['status_sensor']) === 'manutencao') {
                                        $statusClass = 'badge-manutencao';
                                    }
                                ?>
                                <div class="col-md-6 col-lg-4 item-sensor">
                                    <div class="card-sensor">
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge badge-status <?= $statusClass ?>">
                                                    <?= htmlspecialchars($sensor['status_sensor']) ?>
                                                </span>

                                                <?php if (eh_admin()): ?>
                                                <a href="sensor_editar.php?id=<?= (int)$sensor['id_sensor'] ?>" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-2 py-0" style="font-size: 0.75rem;" title="Editar">
                                                    Editar
                                                </a>

                                                <!-- Excluir usa formulário POST: só apaga ao clicar no botão -->
                                                <form action="sensor_excluir.php" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Tem certeza que deseja excluir este sensor?');">
                                                    <input type="hidden" name="id" value="<?= (int)$sensor['id_sensor'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger text-white fw-bold rounded-pill px-2 py-0" style="font-size: 0.75rem;" title="Excluir">
                                                        Excluir
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>

                                            <small class="text-muted">#<?= (int)$sensor['id_sensor'] ?></small>
                                        </div>

                                        <div class="mb-2">
                                            <div class="label-campo">TIPO DO SENSOR</div>
                                            <div class="valor-campo fw-bold"><?= htmlspecialchars($sensor['tipo_sensor']) ?></div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="label-campo">LOCALIZAÇÃO</div>
                                            <div class="valor-campo"><?= htmlspecialchars($sensor['localizacao']) ?></div>
                                        </div>

                                        <div class="mb-2">
                                            <div class="label-campo">EMPRESA OPERADORA</div>
                                            <div class="valor-campo"><?= htmlspecialchars($sensor['empresa_operadora']) ?></div>
                                        </div>

                                        <div class="mb-0">
                                            <div class="label-campo">TREM VINCULADO</div>
                                            <div class="valor-campo text-warning">
                                                <?= !empty($sensor['nome_trem']) ? htmlspecialchars($sensor['nome_trem']) . ' (' . htmlspecialchars($sensor['modelo_trem']) . ')' : 'Não informado' ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Estado vazio: ícone, explicação e atalho para cadastrar -->
                        <div class="estado-vazio bg-white rounded-4">
                            <div class="estado-vazio-icone"><i class="bi bi-broadcast"></i></div>
                            <h3><?= $id ? 'Sensor não encontrado' : 'Nenhum sensor cadastrado' ?></h3>
                            <p>Os sensores cadastrados vão aparecer aqui.</p>
                            <?php if (eh_admin()): ?>
                                <a href="sensor_cadastro.php" class="btn btn-dark rounded-pill px-4">+ Cadastrar primeiro sensor</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </section>

        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('campoBusca').addEventListener('keyup', function() {
            const termo = this.value.toLowerCase();
            const cards = document.querySelectorAll('.item-sensor');

            cards.forEach(card => {
                const textoCard = card.textContent.toLowerCase();
                if (textoCard.includes(termo)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>