<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "mma_site";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}
?>
