<?php
// Executar somente pela CLI, sem depender de conexão com o banco.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$sql = file_get_contents(__DIR__ . '/../database/SA_ferrorama.sql');
preg_match_all('/\$2y\$10\$[.\/A-Za-z0-9]{53}/', $sql, $hashes);
if (count($hashes[0]) !== 3) {
    fwrite(STDERR, "Esperados três hashes de demonstração.\n");
    exit(1);
}
foreach ($hashes[0] as $hash) {
    if (!password_verify('password', $hash) || password_verify('senha-incorreta', $hash)) {
        fwrite(STDERR, "Hash de demonstração inválido.\n");
        exit(1);
    }
}
echo "Hashes de demonstração válidos.\n";
