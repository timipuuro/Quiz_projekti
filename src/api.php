<?php

require_once "tietokanta.php";

session_start();


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
        'SELECT id, nimi, rooli FROM kayttaja WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$kayttajaId]);
    $kayttaja = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kayttaja) {
        session_unset();
        session_destroy();
        return null;
    }

    $_SESSION['nimi'] = $kayttaja['nimi'];
    $_SESSION['rooli'] = $kayttaja['rooli'];
    return $kayttaja;
}

function jsonVastaus(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function vaadiOpettaja(PDO $conn): array
{
    $kayttaja = haeKirjautunutKayttaja($conn);
    if (!$kayttaja || ($kayttaja['rooli'] ?? '') !== 'opettaja') {
        jsonVastaus(['virhe' => 'Toiminto vaatii opettajan kirjautumisen.'], 403);
    }
    return $kayttaja;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function vaadiCsrf(): void
{
    $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($token === '' || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
        jsonVastaus(['virhe' => 'Istunto on vanhentunut. Päivitä sivu.'], 403);
    }
}

$action = (string)($_GET['action'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'omat_paketit') {
    $kayttaja = vaadiOpettaja($conn);

    try {
        $stmt = $conn->prepare(
            'SELECT kp.id, kp.nimi, kp.kuvaus, kp.julkaistu,
                    a.nimi AS aihealue,
                    COUNT(k.id) AS kysymys_maara
             FROM kysymyspaketti kp
             LEFT JOIN aihealue a ON a.id = kp.aihealue_id
             LEFT JOIN kysymys k ON k.paketti_id = kp.id
             WHERE kp.opettaja_id = ?
             GROUP BY kp.id, kp.nimi, kp.kuvaus, kp.julkaistu, a.nimi
             ORDER BY kp.luotu DESC'
        );
        $stmt->execute([(int)$kayttaja['id']]);
        $packages = $stmt->fetchAll();

        $aihealueet = $conn->query(
            'SELECT id, nimi FROM aihealue ORDER BY nimi'
        )->fetchAll();

        jsonVastaus([
            'packages' => $packages,
            'aihealueet' => $aihealueet,
            'csrf_token' => csrfToken()
        ]);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        jsonVastaus(['virhe' => 'Paketteja ei voitu hakea.'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'luo_paketti') {
    vaadiCsrf();
    $kayttaja = vaadiOpettaja($conn);

    $nimi = trim((string)($_POST['nimi'] ?? ''));
    $kuvaus = trim((string)($_POST['kuvaus'] ?? ''));
    $aihealueId = ($_POST['aihealue_id'] ?? '') !== ''
        ? (int)$_POST['aihealue_id']
        : null;
    $julkaistu = isset($_POST['julkaistu']) ? 1 : 0;

    if ($nimi === '') {
        jsonVastaus(['virhe' => 'Paketin nimi on pakollinen.'], 422);
    }

    if ($aihealueId !== null) {
        $stmt = $conn->prepare('SELECT id FROM aihealue WHERE id = ?');
        $stmt->execute([$aihealueId]);
        if (!$stmt->fetch()) {
            jsonVastaus(['virhe' => 'Virheellinen aihealue.'], 422);
        }
    }

    try {
        $stmt = $conn->prepare(
            'INSERT INTO kysymyspaketti
             (opettaja_id, aihealue_id, nimi, kuvaus, julkaistu)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int)$kayttaja['id'],
            $aihealueId,
            $nimi,
            $kuvaus,
            $julkaistu
        ]);
        jsonVastaus([
            'viesti' => 'Kysymyspaketti luotiin.',
            'id' => (int)$conn->lastInsertId()
        ], 201);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        jsonVastaus(['virhe' => 'Pakettia ei voitu luoda.'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'poista_paketti') {
    vaadiCsrf();
    $kayttaja = vaadiOpettaja($conn);
    $pakettiId = (int)($_POST['paketti_id'] ?? 0);

    if ($pakettiId <= 0) {
        jsonVastaus(['virhe' => 'Virheellinen pakettitunnus.'], 422);
    }

    try {
        $stmt = $conn->prepare(
            'DELETE FROM kysymyspaketti
             WHERE id = ? AND opettaja_id = ?'
        );
        $stmt->execute([$pakettiId, (int)$kayttaja['id']]);

        if ($stmt->rowCount() === 0) {
            jsonVastaus(['virhe' => 'Pakettia ei löytynyt tai sinulla ei ole oikeutta poistaa sitä.'], 404);
        }
        jsonVastaus(['viesti' => 'Kysymyspaketti poistettiin.']);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        jsonVastaus(['virhe' => 'Pakettia ei voitu poistaa.'], 500);
    }
}

jsonVastaus(['virhe' => 'Tuntematon toiminto.'], 404);