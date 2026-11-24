<?php
function mensagem(string $tipo, string $texto): string
{
    $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

    return "<div class='alert alert-$tipo' role='alert'>$texto</div>";
}
