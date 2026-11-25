<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$kirjautunut = !empty($_SESSION['kayttaja_id']);
?>
<header class="site-header">
    <a class="brand" href="index.php"> Kysymyspaketteja</a>
    <nav class="nav" aria-label="Päänavigaatio">
        <a class="nav-link" href="index.php">Etusivu</a>
        <?php if ($kirjautunut): ?>
            <a class="nav-cta" href="ulos.php">Kirjaudu Ulos</a>
        <?php else: ?>
            <a class="nav-cta" href="kirjaudu.php">Kirjaudu Sisään</a>
            <a class="nav-cta" href="rekisteroidy.php">Rekisteröidy <span aria-hidden="true">→</span></a>
        <?php endif; ?>
    </nav>
</header>
