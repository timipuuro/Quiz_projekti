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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nimi = trim((string)($_POST['nimi'] ?? ''));
    $salasana = (string)($_POST['salasana'] ?? '');

    if ($nimi === '' || $salasana === '') {
        $virhe = 'Täytä nimi ja salasana';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, nimi, rooli, salasana_hash
             FROM kayttaja
             WHERE nimi = ?
             LIMIT 1'
        );
        $stmt->execute([$nimi]);
        $kayttaja = $stmt->fetch();

        if (!$kayttaja || !password_verify($salasana, (string)$kayttaja['salasana_hash'])) {
            $virhe = 'Nimi tai salasana on väärin';
        } else {
            session_regenerate_id(true);
            $_SESSION['kayttaja_id'] = (int)$kayttaja['id'];
            $_SESSION['nimi'] = $kayttaja['nimi'];
            $_SESSION['rooli'] = $kayttaja['rooli'];
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
    <title>Kirjaudu sisään</title>
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>
    <?php include "header.php"; ?>
    <main class="auth-wrap">
        <section class="auth-card" aria-labelledby="login-title">
            <h1 id="login-title">Kirjaudu sisään</h1>
            <?php if ($virhe !== ''): ?>
                <p class="auth-error" role="alert"><?= htmlspecialchars($virhe) ?></p>
            <?php endif; ?>
            <form method="post" class="auth-form">
                <label for="nimi">Nimi</label>
                <input id="nimi" name="nimi" autocomplete="username" required
                       value="<?= htmlspecialchars($nimi ?? '') ?>">
                <label for="salasana">Salasana</label>
                <input id="salasana" name="salasana" type="password" autocomplete="current-password" required>
                <button class="button button-primary" type="submit">Kirjaudu sisään</button>
            </form>
            <p>Ei tunnuksia? <a class="text-link" href="rekisteroidy.php">Rekisteröidy</a></p>
        </section>
    </main>
    
</body>
</html>