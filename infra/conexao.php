<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "rail_view_db";
$porta = 3307;

// No PHP 8, se a conexão falhar o mysqli lança uma exceção.
// O try/catch "segura" esse erro e mostra uma mensagem amigável,
// sem expor detalhes do servidor para quem está usando o site.
try {
    $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
} catch (mysqli_sql_exception $e) {
    die("Não foi possível conectar ao banco de dados. Verifique se o MySQL está ligado no XAMPP.");
}

$conexao->set_charset("utf8mb4");
