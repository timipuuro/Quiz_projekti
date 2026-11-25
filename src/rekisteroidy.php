<?php
require_once "tietokanta.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['kayttaja_id'])) {
    header('Location: index.php');
    exit;
}

$virhe = '';
$nimi = '';
$sahkoposti = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nimi = trim((string)($_POST['nimi'] ?? ''));
    $sahkoposti = strtolower(trim((string)($_POST['sahkoposti'] ?? '')));
    $salasana = (string)($_POST['salasana'] ?? '');

    if ($nimi === '' || $sahkoposti === '' || $salasana === '') {
        $virhe = 'Täytä kaikki kentät.';
    } elseif (!filter_var($sahkoposti, FILTER_VALIDATE_EMAIL)) {
        $virhe = 'Sähköposti ei ole kelvollinen.';
    } elseif (strlen($salasana) < 8) {
        $virhe = 'Salasanan tulee olla vähintään 8 merkkiä.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM kayttaja WHERE sahkoposti = ? LIMIT 1');
        $stmt->execute([$sahkoposti]);

        if ($stmt->fetch()) {
            $virhe = 'Sähköpostilla on jo käyttäjä.';
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO kayttaja (nimi, rooli, sahkoposti, salasana_hash)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([
                $nimi,
                'opiskelija',
                $sahkoposti,
                password_hash($salasana, PASSWORD_DEFAULT)
            ]);

            session_regenerate_id(true);
            $_SESSION['kayttaja_id'] = (int)$conn->lastInsertId();
            $_SESSION['nimi'] = $nimi;
            $_SESSION['rooli'] = 'opiskelija';
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekisteröidy</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>
    <?php include "header.php"; ?>
    <main class="auth-wrap">
        <section class="auth-card" aria-labelledby="register-title">
            <h1 id="register-title">Rekisteröidy</h1>
            <?php if ($virhe !== ''): ?>
                <p class="auth-error" role="alert"><?= htmlspecialchars($virhe) ?></p>
            <?php endif; ?>
            <form method="post" class="auth-form">
                <label for="nimi">Nimi</label>
                <input id="nimi" name="nimi" autocomplete="name" required maxlength="100"
                       value="<?= htmlspecialchars($nimi) ?>">
                <label for="sahkoposti">Sähköposti</label>
                <input id="sahkoposti" name="sahkoposti" type="email" autocomplete="email" required
                       value="<?= htmlspecialchars($sahkoposti) ?>">
                <label for="salasana">Salasana</label>
                <input id="salasana" name="salasana" type="password" autocomplete="new-password" required minlength="8">
                <button class="button button-primary" type="submit">Luo tunnus</button>
            </form>
            <p>Onko sinulla jo tunnus? <a class="text-link" href="kirjaudu.php">Kirjaudu sisään</a></p>
        </section>
    </main>
</body>
</html>
