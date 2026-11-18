<?php
$host = getenv('RAILVIEW_DB_HOST') ?: 'localhost';
$usuario = getenv('RAILVIEW_DB_USER') ?: 'root';
$senha = getenv('RAILVIEW_DB_PASSWORD') !== false ? getenv('RAILVIEW_DB_PASSWORD') : '';
$banco = getenv('RAILVIEW_DB_NAME') ?: 'rail_view_db';
$porta = (int)(getenv('RAILVIEW_DB_PORT') ?: 3310);

try {
    $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
    $conexao->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    exit('Não foi possível conectar ao banco de dados. Verifique o MySQL e a configuração local.');
}

