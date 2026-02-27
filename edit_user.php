<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) exit("Acesso negado.");

$user_id = $_SESSION['user_id'];
$role = $conn->query("SELECT role FROM users WHERE id=$user_id")->fetch_assoc()['role'];
if ($role !== 'admin') exit("Acesso negado.");

if (!isset($_GET['id'])) exit("Utilizador não encontrado.");

$id = intval($_GET['id']);
$u = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $id);

    if ($stmt->execute()) $success = "Utilizador atualizado!";
    else $error = "Erro ao atualizar.";
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Editar Utilizador</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 text-neutral-100">

<div class="pt-20 max-w-3xl mx-auto px-6">

<h1 class="text-5xl font-bold text-red-500 mb-6">Editar Utilizador</h1>

<?php if ($success): ?><p class="bg-green-600 p-3 rounded mb-4"><?= $success ?></p><?php endif; ?>
<?php if ($error): ?><p class="bg-red-600 p-3 rounded mb-4"><?= $error ?></p><?php endif; ?>

<form method="POST" class="bg-neutral-800 p-6 rounded-xl border border-neutral-700">

<label>Nome</label>
<input type="text" name="name" value="<?= $u['name'] ?>" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Email</label>
<input type="email" name="email" value="<?= $u['email'] ?>" class="w-full bg-neutral-900 p-2 rounded mb-4">

<label>Role</label>
<select name="role" class="w-full bg-neutral-900 p-2 rounded mb-4">
    <option value="user" <?= $u['role']=="user"?"selected":"" ?>>User</option>
    <option value="admin" <?= $u['role']=="admin"?"selected":"" ?>>Admin</option>
</select>

<button class="bg-red-600 px-6 py-3 rounded hover:bg-red-700">Guardar</button>

</form>

<a href="admin_users.php" class="text-red-500 mt-6 inline-block">← Voltar</a>

</div>
</body>
</html>
