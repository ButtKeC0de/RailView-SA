<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../infra/regras_sensor.php';
$casos = [
    [79.0, 80.0, false, false],
    [80.0, 80.0, false, false],
    [81.0, 80.0, false, true],
    [81.0, null, false, false],
    [null, 80.0, true, true],
    [0.0, 0.0, false, false],
    [-2.0, -3.0, false, true],
];
foreach ($casos as [$valor, $limite, $falha, $esperado]) {
    if ((avaliar_leitura_sensor($valor, $limite, $falha) !== null) !== $esperado) {
        fwrite(STDERR, "Falha na regra de alertas.\n");
        exit(1);
    }
}
echo "Regras de alertas válidas.\n";
