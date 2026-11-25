<?php
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'rail_view_db';
$porta = 3307;

try {
    $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
    $conexao->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    exit('Não foi possível conectar ao banco de dados. Verifique o MySQL e a configuração local.');
}
