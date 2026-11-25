<?php
 include "api.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['kayttaja_id'])) {
    header('Location: index.php');
    exit;
}

$virhe = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sahkoposti = strtolower(trim((string)($_POST['sahkoposti'] ?? '')));
    $salasana = (string)($_POST['salasana'] ?? '');

    if ($sahkoposti === '' || $salasana === '') {
        $virhe = 'Täytä sähköposti ja salasana.';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, nimi, rooli, salasana_hash
             FROM kayttaja
             WHERE sahkoposti = ?
             LIMIT 1'
        );
        $stmt->execute([$sahkoposti]);
        $kayttaja = $stmt->fetch();

        if (!$kayttaja || !password_verify($salasana, (string)$kayttaja['salasana_hash'])) {
            $virhe = 'Sähköposti tai salasana on väärin.';
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
                <label for="sahkoposti">Sähköposti</label>
                <input id="sahkoposti" name="sahkoposti" type="email" autocomplete="email" required
                       value="<?= htmlspecialchars($sahkoposti ?? '') ?>">
                <label for="salasana">Salasana</label>
                <input id="salasana" name="salasana" type="password" autocomplete="current-password" required>
                <button class="button button-primary" type="submit">Kirjaudu sisään</button>
            </form>
            <p>Ei tunnuksia? <a class="text-link" href="rekisteroidy.php">Rekisteröidy</a></p>
        </section>
    </main>
    
</body>
</html>