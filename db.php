<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "mma_site"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}
?>

