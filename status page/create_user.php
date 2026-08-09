<?php
// Set up the database connection
$db = new PDO('mysql:host=localhost;dbname=status_monitor', 'root', 'YourNewStrongPassword');

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        $message = "<div class='bg-green-100 text-green-700 p-3 rounded'>User '$username' created successfully!</div>";
    } catch (PDOException $e) {
        $message = "<div class='bg-red-100 text-red-700 p-3 rounded'>Error: Could not create user. (Username might already exist)</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-10">
    <div class="max-w-md mx-auto bg-white p-8 rounded shadow">
        <h2 class="text-xl font-bold mb-6">Create New Admin User</h2>
        <?= $message ?>
        <form method="POST" class="mt-4">
            <input type="text" name="username" placeholder="Username" required class="w-full border p-2 mb-4 rounded">
            <input type="password" name="password" placeholder="Password" required class="w-full border p-2 mb-4 rounded">
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Create User</button>
        </form>
        <a href="admin_incidents.php" class="block mt-4 text-center text-blue-600 underline">Back to Login</a>
    </div>
</body>
</html>