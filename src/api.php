<?php

require_once "tietokanta.php";

session_start();


// Haetaan kirjautunut käyttäjä tietokannasta kirjautumista varten. Jos käyttäjää ei löydy, sessio tuhotaan. tee loppuun.
function haeKirjautunutKayttaja(PDO $conn): ?array
{
    $kayttajaId = (int)($_SESSION['kayttaja_id'] ?? 0);
    if ($kayttajaId <= 0) {
        return null;
    }

    $stmt = $conn->prepare(
        'SELECT Kayttaja_id, Nimi, Rooli FROM kayttaja WHERE Kayttaja_id = ? LIMIT 1'
    );
    $stmt->execute([$kayttajaId]);
    $kayttaja = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kayttaja) {
        session_unset();
        session_destroy();
        return null;
    }

    $_SESSION['nimi'] = $kayttaja['Nimi'];
    $_SESSION['rooli'] = $kayttaja['Rooli'];
    return $kayttaja;
}