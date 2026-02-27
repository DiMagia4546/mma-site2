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
    $date = $_POST['date'];
    $location = $_POST['location'];
    $main_event = $_POST['main_event'];

    $banner = "uploads/default_banner.webp";

    if (!empty($_FILES['banner']['name'])) {
        $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        $newName = "event_" . time() . "." . $ext;
        $path = "uploads/" . $newName;

        if (move_uploaded_file($_FILES['banner']['tmp_name'], $path)) {
            $banner = $path;
        }
    }

    $stmt = $conn->prepare("INSERT INTO events (name, date, location, main_event, banner) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $date, $location, $main_event, $banner);

    if ($stmt->execute()) $success = "Evento criado com sucesso!";
    else $error = "Erro ao criar evento.";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Novo Evento</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-3xl mx-auto px-6">

<h1 class="text-5xl font-bold text-red-500 mb-6">Criar Novo Evento</h1>

<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= $success ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= $error ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">

<label>Nome</label>
<input type="text" name="name" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Data</label>
<input type="date" name="date" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Local</label>
<input type="text" name="location" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Main Event</label>
<input type="text" name="main_event" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Banner</label>
<input type="file" name="banner" class="mb-4">

<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Criar</button>

</form>

<a href="admin_events.php" class="text-red-500 mt-6 inline-block">← Voltar</a>

</div>
</body>
</html>
