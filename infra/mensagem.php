<?php
function mensagem(string $tipo, string $texto): string
{
    $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

    return "<div class='alert alert-$tipo alert-dismissible fade show' role='alert'>
                $texto
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>
            </div>";
}
