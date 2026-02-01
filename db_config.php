<?php
// 1. Definizione delle credenziali
$host = "localhost";
$username = "root";
$password = "";
$nome_db = "cooking_webbers";

// 2. Creazione dell'oggetto connessione
$conn = new mysqli($host, $username, $password, $nome_db);

// 3. Controllo errori
if ($conn->connect_error) {
    die("Attenzione, connessione fallita: " . $conn->connect_error);
}
?>