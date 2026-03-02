<?php
$host = "localhost";
$user = "root";
$pass = "";
<<<<<<< HEAD
$db   = "mma_site";
=======
$dbname = "mma_site"; 
>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}
<<<<<<< HEAD

$conn->set_charset("utf8mb4");
=======
?>

>>>>>>> bb0e1c37f01ca30bb9c897503cc0cf8c0a0a5224
