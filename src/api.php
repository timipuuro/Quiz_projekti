<?php

require_once "tietokanta.php";

session_start();


if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'paketit') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $stmt = $conn->prepare(
            'SELECT kp.id, kp.nimi, kp.kuvaus, a.nimi AS aihealue,
                    COUNT(DISTINCT k.id) AS kysymys_maara
             FROM kysymyspaketti kp
             LEFT JOIN aihealue a ON a.id = kp.aihealue_id
             LEFT JOIN kysymys k ON k.paketti_id = kp.id
             WHERE kp.julkaistu = 1
             GROUP BY kp.id, kp.nimi, kp.kuvaus, a.nimi
             ORDER BY kp.nimi'
        );
        $stmt->execute();

        echo json_encode(
            ['packages' => $stmt->fetchAll()],
            JSON_UNESCAPED_UNICODE
        );
    } catch (Throwable $e) {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['virhe' => 'Kysymyspaketteja ei voitu hakea.'], JSON_UNESCAPED_UNICODE);
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'kysymykset') {
    header('Content-Type: application/json; charset=utf-8');

    $pakettiId = isset($_GET['paketti_id']) ? (int)$_GET['paketti_id'] : null;

    try {
        $sql = '
            SELECT k.id, k.teksti, k.paketti_id
            FROM kysymys k
            INNER JOIN kysymyspaketti kp ON kp.id = k.paketti_id
            WHERE kp.julkaistu = 1
        ';
        $params = [];

        if ($pakettiId !== null && $pakettiId > 0) {
            $sql .= ' AND k.paketti_id = :paketti_id';
            $params['paketti_id'] = $pakettiId;
        }

        $sql .= ' ORDER BY k.jarjestys, k.id';

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $kysymykset = $stmt->fetchAll();

        $vastausStmt = $conn->prepare(
            'SELECT teksti, on_oikea
             FROM vastausvaihtoehto
             WHERE kysymys_id = ?
             ORDER BY id'
        );

        $tulos = [];
        foreach ($kysymykset as $kysymys) {
            $vastausStmt->execute([$kysymys['id']]);
            $vaihtoehdot = $vastausStmt->fetchAll();

            if (count($vaihtoehdot) !== 4) {
                continue;
            }

            $oikeaIndex = null;
            foreach ($vaihtoehdot as $index => $vaihtoehto) {
                if ((int)$vaihtoehto['on_oikea'] === 1) {
                    $oikeaIndex = $index;
                    break;
                }
            }
            if ($oikeaIndex === null) {
                continue;
            }

            $tulos[] = [
                'id' => (int)$kysymys['id'],
                'paketti_id' => (int)$kysymys['paketti_id'],
                'question' => $kysymys['teksti'],
                'options' => array_column($vaihtoehdot, 'teksti'),
                'correctOption' => 'option' . ['A', 'B', 'C', 'D'][$oikeaIndex]
            ];
        }

        echo json_encode(['questions' => $tulos], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['virhe' => 'Kysymyksia ei voitu hakea.'], JSON_UNESCAPED_UNICODE);
    }

    exit;
}


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