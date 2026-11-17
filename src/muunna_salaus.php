<?php

require_once "tietokanta.php";

try {
    $conn->beginTransaction();

    $stmt = $conn->query(
        "SELECT Kayttaja_id, Salasana FROM kayttaja"
    );

    $paivitys = $conn->prepare(
        "UPDATE kayttaja
         SET Salasana = :salasana
         WHERE Kayttaja_id = :id"
    );

    while ($kayttaja = $stmt->fetch()) {

        // Älä hashää jo valmiiksi hashattua salasanaa uudelleen.
        if (password_get_info($kayttaja["Salasana"])["algo"] !== null) {
            continue;
        }

        $hash = password_hash(
            $kayttaja["Salasana"],
            PASSWORD_DEFAULT
        );

        $paivitys->execute([
            ":salasana" => $hash,
            ":id" => $kayttaja["Kayttaja_id"]
        ]);
    }

    $conn->commit();

    echo "Salasanat muunnettu onnistuneesti!";

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo "Salasanojen muuntaminen epäonnistui.";
}
