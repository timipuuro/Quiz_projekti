<?php

$servername = "db";
$username   = "user";
$password   = "pass";
$dbname     = "Luokka_Tietokanta";

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
       
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode(["virhe" => "Tietokantayhteys epäonnistui."]));
}

?>