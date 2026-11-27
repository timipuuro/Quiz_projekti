<?php

require_once "tietokanta.php";

session_start();

$teacherActions = ['omat_paketit', 'luo_paketti', 'vaihda_julkisuus', 'poista_paketti'];
$action = $_GET['action'] ?? '';

if (in_array($action, $teacherActions, true)) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $opettajaId = haeOpettajaId($conn);
        if ($opettajaId === null) {
            http_response_code(403);
            echo json_encode(['virhe' => 'Vain opettaja voi hallita kysymyspaketteja.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'omat_paketit') {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            $stmt = $conn->prepare(
                'SELECT kp.id, kp.nimi, kp.kuvaus, kp.julkaistu, a.nimi AS aihealue,
                        COUNT(DISTINCT k.id) AS kysymys_maara
                 FROM kysymyspaketti kp
                 LEFT JOIN aihealue a ON a.id = kp.aihealue_id
                 LEFT JOIN kysymys k ON k.paketti_id = kp.id
                 WHERE kp.opettaja_id = ?
                 GROUP BY kp.id, kp.nimi, kp.kuvaus, kp.julkaistu, a.nimi
                 ORDER BY kp.luotu DESC, kp.id DESC'
            );
            $stmt->execute([$opettajaId]);

            $aihealueet = $conn->query('SELECT id, nimi FROM aihealue ORDER BY nimi')->fetchAll();
            echo json_encode([
                'packages' => $stmt->fetchAll(),
                'aihealueet' => $aihealueet,
                'csrf_token' => $_SESSION['csrf_token']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: GET, POST');
            echo json_encode(['virhe' => 'Virheellinen HTTP-metodi.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            http_response_code(403);
            echo json_encode(['virhe' => 'Pyyntöä ei voitu vahvistaa. Lataa sivu uudelleen.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'luo_paketti') {
            $nimi = trim((string)($_POST['nimi'] ?? ''));
            $kuvaus = trim((string)($_POST['kuvaus'] ?? ''));
            $aihealueId = (int)($_POST['aihealue_id'] ?? 0);
            $julkaistu = isset($_POST['julkaistu']) ? 1 : 0;
            $kysymykset = json_decode((string)($_POST['kysymykset'] ?? '[]'), true);

            if ($nimi === '' || preg_match('/\A.{1,150}\z/us', $nimi) !== 1) {
                http_response_code(400);
                echo json_encode(['virhe' => 'Anna paketin nimi (enintään 150 merkkiä).'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if ($aihealueId > 0) {
                $topicStmt = $conn->prepare('SELECT id FROM aihealue WHERE id = ?');
                $topicStmt->execute([$aihealueId]);
                if (!$topicStmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['virhe' => 'Valittua aihealuetta ei löydy.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } else {
                $aihealueId = null;
            }

            if (!is_array($kysymykset) || count($kysymykset) === 0) {
                http_response_code(400);
                echo json_encode(['virhe' => 'Lisää vähintään yksi kysymys.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            if (count($kysymykset) > 50) {
                http_response_code(400);
                echo json_encode(['virhe' => 'Paketissa voi olla enintään 50 kysymystä.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            foreach ($kysymykset as $kysymys) {
                $teksti = trim((string)($kysymys['teksti'] ?? ''));
                $selite = isset($kysymys['selite']) ? trim((string)$kysymys['selite']) : '';
                $vaihtoehdot = $kysymys['vaihtoehdot'] ?? null;
                $oikeaIndeksi = $kysymys['oikea_indeksi'] ?? null;

                if ($teksti === '' || preg_match('/\A.{1,500}\z/us', $teksti) !== 1) {
                    http_response_code(400);
                    echo json_encode(['virhe' => 'Jokaisella kysymyksellä on oltava teksti (enintään 500 merkkiä).'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if ($selite !== '' && preg_match('/\A.{0,1000}\z/us', $selite) !== 1) {
                    http_response_code(400);
                    echo json_encode(['virhe' => 'Kysymyksen selite voi olla enintään 1000 merkkiä.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                if (!is_array($vaihtoehdot) || count($vaihtoehdot) !== 4) {
                    http_response_code(400);
                    echo json_encode(['virhe' => 'Jokaisella kysymyksellä on oltava neljä vastausvaihtoehtoa.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                foreach ($vaihtoehdot as $vaihtoehto) {
                    $vaihtoehto = trim((string)$vaihtoehto);
                    if ($vaihtoehto === '' || preg_match('/\A.{1,300}\z/us', $vaihtoehto) !== 1) {
                        http_response_code(400);
                        echo json_encode(['virhe' => 'Jokaisella vastausvaihtoehdolla on oltava teksti (enintään 300 merkkiä).'], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
                if (!is_int($oikeaIndeksi) || $oikeaIndeksi < 0 || $oikeaIndeksi > 3) {
                    http_response_code(400);
                    echo json_encode(['virhe' => 'Valitse jokaiselle kysymykselle oikea vastausvaihtoehto.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            $conn->beginTransaction();
            $packageStmt = $conn->prepare(
                'INSERT INTO kysymyspaketti (opettaja_id, aihealue_id, nimi, kuvaus, julkaistu)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $packageStmt->execute([$opettajaId, $aihealueId, $nimi, $kuvaus !== '' ? $kuvaus : null, $julkaistu]);
            $pakettiId = (int)$conn->lastInsertId();

            $questionStmt = $conn->prepare(
                'INSERT INTO kysymys (paketti_id, teksti, selite, jarjestys)
                 VALUES (?, ?, ?, ?)'
            );
            $optionStmt = $conn->prepare(
                'INSERT INTO vastausvaihtoehto (kysymys_id, teksti, on_oikea)
                 VALUES (?, ?, ?)'
            );

            foreach ($kysymykset as $jarjestys => $kysymys) {
                $teksti = trim((string)$kysymys['teksti']);
                $selite = trim((string)($kysymys['selite'] ?? ''));
                $vaihtoehdot = $kysymys['vaihtoehdot'];
                $oikeaIndeksi = (int)$kysymys['oikea_indeksi'];

                $questionStmt->execute([
                    $pakettiId,
                    $teksti,
                    $selite !== '' ? $selite : null,
                    $jarjestys + 1
                ]);
                $kysymysId = (int)$conn->lastInsertId();

                foreach ($vaihtoehdot as $vaihtoehtoIndeksi => $vaihtoehto) {
                    $optionStmt->execute([
                        $kysymysId,
                        trim((string)$vaihtoehto),
                        $vaihtoehtoIndeksi === $oikeaIndeksi ? 1 : 0
                    ]);
                }
            }

            $conn->commit();
            http_response_code(201);
            echo json_encode(['id' => $pakettiId], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $pakettiId = (int)($_POST['paketti_id'] ?? 0);
        if ($pakettiId <= 0) {
            http_response_code(400);
            echo json_encode(['virhe' => 'Virheellinen kysymyspaketin tunniste.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'vaihda_julkisuus') {
            $julkaistu = (int)($_POST['julkaistu'] ?? -1);
            if ($julkaistu !== 0 && $julkaistu !== 1) {
                http_response_code(400);
                echo json_encode(['virhe' => 'Valitse julkinen tai yksityinen tila.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $stmt = $conn->prepare(
                'UPDATE kysymyspaketti
                 SET julkaistu = ?
                 WHERE id = ? AND opettaja_id = ?'
            );
            $stmt->execute([$julkaistu, $pakettiId, $opettajaId]);
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['virhe' => 'Kysymyspakettia ei löydy tai se ei ole sinun.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            echo json_encode([
                'julkaistu' => $julkaistu,
                'tila' => $julkaistu === 1 ? 'Julkinen' : 'Yksityinen'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $conn->prepare('DELETE FROM kysymyspaketti WHERE id = ? AND opettaja_id = ?');
        $stmt->execute([$pakettiId, $opettajaId]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['virhe' => 'Kysymyspakettia ei löydy tai se ei ole sinun.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['poistettu' => true], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        http_response_code(500);
        error_log($e->getMessage());
        echo json_encode(['virhe' => 'Kysymyspaketin käsittely epäonnistui.'], JSON_UNESCAPED_UNICODE);
    }

    exit;
}


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
