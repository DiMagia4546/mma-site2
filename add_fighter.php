<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$role = $conn->query("SELECT role FROM users WHERE id=$user_id")->fetch_assoc()['role'];
if ($role !== 'admin') die("Acesso negado.");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST['name'];
    $weight = $_POST['weight_class'];
    $wins = $_POST['wins'];
    $losses = $_POST['losses'];
    $age = $_POST['age'];
    $height = $_POST['height'];
    $reach = $_POST['reach'];
    $nationality = $_POST['nationality'];

    $image = "uploads/default_fighter.webp";

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $newName = "fighter_" . time() . "." . $ext;
        $path = "uploads/" . $newName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
            $image = $path;
        }
    }

    $stmt = $conn->prepare("INSERT INTO fighters (name, weight_class, wins, losses, age, height, reach, nationality, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiidiss", $name, $weight, $wins, $losses, $age, $height, $reach, $nationality, $image);

    if ($stmt->execute()) $success = "Lutador criado com sucesso!";
    else $error = "Erro ao criar lutador.";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Novo Lutador</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-3xl mx-auto px-6">

<h1 class="text-5xl font-bold text-red-500 mb-6">Criar Novo Lutador</h1>

<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= $success ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= $error ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">

<label>Nome</label>
<input type="text" name="name" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Peso</label>
<input type="text" name="weight_class" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Vitórias</label>
<input type="number" name="wins" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Derrotas</label>
<input type="number" name="losses" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Idade</label>
<input type="number" name="age" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Altura (m)</label>
<input type="text" name="height" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Alcance (cm)</label>
<input type="number" name="reach" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Nacionalidade</label>
<input type="text" name="nationality" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Imagem</label>
<input type="file" name="image" class="mb-4">

<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Criar</button>

</form>

<a href="admin_fighters.php" class="text-red-500 mt-6 inline-block">← Voltar</a>

</div>
</body>
</html>
