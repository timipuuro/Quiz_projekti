<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Testaa tietosi, opi uutta ja haasta itsesi tietovisoissa.">
    <title>Kysymys kuuluu</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>
    <?php include "header.php"; ?>

    <main>
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-content">
                <h1 id="hero-title">Tietovisoja<br><span>Kysymyspaketteja</span></h1>
                <p class="hero-copy">Onko tylsää? Haasta itseäsi tekemällä alla olevia kysymyspaketteja.</p>
                <?php if (empty($_SESSION['kayttaja_id'])): ?>
                <br> <p class="hero-copya"> (Varmista, että olet sisäänkirjautunut tai jos ei ole tiliä. Rekisteröidy.)</p>
                <?php endif ?>
                
                
            </div>
            <div class="hero-art" aria-hidden="true">
                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>
                <span class="spark spark-one">✳</span>
                <span class="spark spark-two">✦</span>
            </div>
        </section>

        <?php if (strtolower((string)($_SESSION['rooli'] ?? '')) === 'opettaja'): ?>
            <?php include "ohjauspaneeli.php"; ?>
        <?php endif; ?>
        <section class="topics section-wrap" id="tietovisat" aria-labelledby="topics-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">ALOITA TÄSTÄ</p>
                    <h2 id="topics-title">Valitse kategoria</h2>
                </div>
                <p>Valitse aihe</p>
            </div>
            <div id="haetutpaketit">
                <form id="paketti-haku-lomake">
                    <input type="search" id="paketti-haku-kenttä" placeholder="Hae tunnuksella" aria-label="Hae kysymyspaketti tunnuksella">
                    <button type="submit">Hae</button>
                    <button id="tyhjenna" type="button" onclick="hakutyhjennys()">Tyhjennä lomake</button>
                </form>
                <ul id="haku-paketti">
                    
                </ul>
            </div>
            <div class="topic-grid" id="package-grid">
                <p>Haetaan kysymyspaketteja...</p>
            </div>
        </section>
    </main>

    <footer class="site-footer">
    
    </footer>

    <script src="paketti.js"></script>
</body>
</html>
