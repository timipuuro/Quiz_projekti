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
              
                
            </div>
            <div class="hero-art" aria-hidden="true">
                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>
                <div class="quiz-card">
                    <span class="quiz-card-label">PÄIVÄN KYSYMYS</span>
                    <span class="quiz-card-icon">?</span>
                    <span class="quiz-card-line"></span>
                    <span class="quiz-card-line quiz-card-line-short"></span>
                    <div class="quiz-card-footer"><span></span><span></span><span></span></div>
                </div>
                <span class="spark spark-one">✳</span>
                <span class="spark spark-two">✦</span>
            </div>
        </section>

        <section class="topics section-wrap" id="tietovisat" aria-labelledby="topics-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">ALOITA TÄSTÄ</p>
                    <h2 id="topics-title">Mikä kiinnostaa tänään?</h2>
                </div>
                <p>Valitse aihe ja anna uteliaisuuden viedä.</p>
            </div>
            <div class="topic-grid">
                <a href="perusteet.php">
                <article class="topic-card">
                    <span class="topic-icon topic-icon-purple" aria-hidden="true">✦</span>
                    <h3>Koodikielen perusteet</h3>
                    <p>Tiedätkö varmasti kaikki koodikielen perusteet? Kokeile tätä!</p>
                    <span class="topic-arrow" aria-hidden="true">↗</span>
                </article>
                </a>
                 <a href="language.php">
                <article class="topic-card">
                    <span class="topic-icon topic-icon-green" aria-hidden="true">⌘</span>
                    <h3>SQL tietovisa</h3>
                    <p>Tiedäthän SQL komentoja?</p>
                    <span class="topic-arrow" aria-hidden="true">↗</span>
                </article>
                </a>
                <a href="historia.php">
                <article class="topic-card">
                    <span class="topic-icon topic-icon-green" aria-hidden="true">⌘</span>
                    <h3>Historia</h3>
                    <p>Suomen historia. Muistatko vielä historian tunneilta?</p>
                    <span class="topic-arrow" aria-hidden="true">↗</span>
                </article>
                </a>
                <a href="haastavat.php">
                <article class="topic-card">
                    <span class="topic-icon topic-icon-orange" aria-hidden="true">♫</span>
                    <h3>Haastava</h3>
                    <p>Tämä sisältää matemaattisia pulmia, uskallatko yrittää?</p>
                    <span class="topic-arrow" aria-hidden="true">↗</span>
                </article>
                </a>
            </div>
        </section>

        <section class="how-to section-wrap" id="miten-pelataan" aria-labelledby="how-to-title">
            <div>
                <p class="eyebrow">HELPPO ALOITTAA</p>
                <h2 id="how-to-title">Pelaa, opi,<br>pelaa uudestaan.</h2>
            </div>
            <p>Valitse aihe, vastaa kysymyksiin ja katso, mitä kaikkea jo tiedät. Jokainen kierros on tilaisuus oppia jotain uutta.</p>
            <a class="text-link" href="#tietovisat">Löydä oma aiheesi <span aria-hidden="true">→</span></a>
        </section>
    </main>

    <footer class="site-footer">
        <a class="brand footer-brand" href="index.php"><span class="brand-mark">?</span> tietovisa</a>
    </footer>
</body>
</html>
