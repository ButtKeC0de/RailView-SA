<?php
$classeUsuario = !empty($usuarioFixo) ? 'usuario-topo usuario-topo--fixo' : 'usuario-topo';
?>
<a class="<?= $classeUsuario ?>" href="Perfil.php" title="Meu perfil">
    <span class="usuario-topo-foto" aria-hidden="true">
        <svg viewBox="0 0 16 16" fill="currentColor"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>
    </span>
    <span class="usuario-topo-nome"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Entrar', ENT_QUOTES, 'UTF-8') ?></span>
</a>
