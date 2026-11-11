<?php
// Monta um aviso padrão do Bootstrap (verde, vermelho, amarelo...).
//
// $tipo  -> 'success' (sucesso), 'danger' (erro), 'warning' (atenção) ou 'info'
// $texto -> o que vai aparecer para o usuário
//
// O htmlspecialchars transforma < > " em texto comum.
// Assim, se alguém digitar <script> num campo, ele aparece escrito na tela
// em vez de ser executado (proteção contra XSS).
//
// Exemplo de uso:
//   $_SESSION['mensagem'] = mensagem('success', 'Trem salvo com sucesso!');
function mensagem(string $tipo, string $texto): string
{
    $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

    return "<div class='alert alert-$tipo alert-dismissible fade show' role='alert'>
                $texto
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>
            </div>";
}
