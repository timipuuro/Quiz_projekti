<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<header class="site-header">
    <a class="brand" href="index.php"> Kysymyspaketteja</a>
    <nav class="nav" aria-label="Päänavigaatio">
        <a class="nav-link" href="index.php">Etusivu</a>
        <?php if (!empty($_SESSION['kayttaja_id'])): ?>
            <span class="nav-user"><?= htmlspecialchars($_SESSION['nimi'] ?? 'Käyttäjä') ?></span>
            <a class="nav-cta" href="ulos.php">Kirjaudu ulos <span aria-hidden="true">→</span></a>
        <?php else: ?>
            <a class="nav-link" href="kirjaudu.php">Kirjaudu sisään</a>
            <a class="nav-cta" href="rekisteroidy.php">Rekisteröidy <span aria-hidden="true">→</span></a>
        <?php endif; ?>
    </nav>
</header>
