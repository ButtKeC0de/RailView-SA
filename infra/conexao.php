<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "rail_view_db";
$porta = 3306;


try {
    $conexao = new mysqli($host, $usuario, $senha, $banco, $porta);
} catch (mysqli_sql_exception $e) {
    die("Não foi possível conectar ao banco de dados. Verifique se o MySQL está ligado no XAMPP.");
}

$conexao->set_charset("utf8mb4");
