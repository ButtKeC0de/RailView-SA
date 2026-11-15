<?php
// Regra pura, compartilhada pela gravação e pela verificação CLI.
function avaliar_leitura_sensor(?float $valor, ?float $limite, bool $falha): ?array
{
    if ($falha) {
        return ['mensagem' => 'Falha sinalizada pelo sensor.', 'nivel' => 'Alto'];
    }
    if ($valor !== null && $limite !== null && $valor > $limite) {
        return ['mensagem' => 'Leitura acima do limite configurado.', 'nivel' => 'Alto'];
    }
    return null;
}
