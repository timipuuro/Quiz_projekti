<?php

require_once "tietokanta.php";

function haeKirjautunutKayttaja(PDO $conn): ?array
{
    $kayttajaId = (int)($_SESSION['kayttaja_id'] ?? 0);
    if ($kayttajaId <= 0) {
        return null;
    }

    $stmt = $conn->prepare("SELECT * FROM Kayttajat WHERE id = :id");
    $stmt->bindParam(':id', $kayttajaId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}