<?php
session_start();
date_default_timezone_set('Europe/London');
$db = new PDO('mysql:host=localhost;dbname=status_monitor', 'root', 'YourNewStrongPassword');

// --- LOGIN LOGIC ---
if (isset($_POST['login'])) {
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        $_SESSION['loggedin'] = true;
    } else {
        $error = "Invalid credentials.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_incidents.php");
    exit;
}

if (!isset($_SESSION['loggedin'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-100 flex items-center justify-center h-screen">
    <form method="POST" class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="text-xl font-bold mb-4">Admin Login</h2>
        <?php if (isset($error)) echo "<p class='text-red-500 mb-4'>$error</p>"; ?>
        <input type="text" name="username" placeholder="Username" required class="w-full border p-2 mb-4 rounded">
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2 mb-4 rounded">
        <button type="submit" name="login" class="w-full bg-blue-600 text-white p-2 rounded">Sign In</button>
    </form>
</body>
</html>
<?php exit; } 

// --- INCIDENT MANAGEMENT LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['close_incident'])) {
        $stmt = $db->prepare("UPDATE history SET is_closed=1, closed_at=NOW() WHERE id=?");
        $stmt->execute([$_POST['id']]);
    } elseif (isset($_POST['add_update'])) {
        $stmt = $db->prepare("INSERT INTO incident_updates (incident_id, update_text) VALUES (?, ?)");
        $stmt->execute([$_POST['id'], $_POST['update_text']]);
    } elseif (isset($_POST['title'])) {
        $stmt = $db->prepare("INSERT INTO history (title, description) VALUES (?, ?)");
        $stmt->execute([$_POST['title'], $_POST['description']]);
    }
    header("Location: admin_incidents.php" . (isset($_POST['id']) ? "?edit=".$_POST['id'] : ""));
    exit;
}

$incidents = $db->query("SELECT * FROM history ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$edit = null;
$updates = [];
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM history WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
    $uStmt = $db->prepare("SELECT * FROM incident_updates WHERE incident_id=? ORDER BY created_at DESC");
    $uStmt->execute([$_GET['edit']]);
    $updates = $uStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incident Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-10">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Manage Incidents</h1>
            <a href="?logout=1" class="text-red-600 text-sm">Logout</a>
        </div>

        <?php if (!$edit): ?>
            <form method="POST" class="bg-white p-6 rounded shadow mb-10">
                <h2 class="font-bold mb-4">Start New Incident</h2>
                <input type="text" name="title" placeholder="Incident Title" required class="w-full border p-2 mb-4 rounded">
                <textarea name="description" placeholder="Initial Description" required class="w-full border p-2 mb-4 rounded"></textarea>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Create Incident</button>
            </form>
        <?php else: ?>
            <div class="bg-white p-6 rounded shadow mb-10">
                <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($edit['title']) ?></h2>
                <p class="text-gray-600 mb-6"><?= htmlspecialchars($edit['description']) ?></p>
                
                <?php if ($edit['is_closed']): ?>
                    <div class="bg-green-100 text-green-800 p-4 rounded mb-6 font-bold border border-green-200">
                        Incident Closed on: <?= $edit['closed_at'] ?>
                    </div>
                <?php else: ?>
                    <form method="POST" class="mb-6">
                        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                        <button type="submit" name="close_incident" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Close Incident</button>
                    </form>
                <?php endif; ?>

                <h3 class="font-bold mb-4 border-b pb-2">Timeline</h3>
                <?php foreach ($updates as $u): ?>
                    <div class="mb-4 p-3 bg-slate-50 rounded border-l-4 border-blue-500">
                        <p class="text-xs text-gray-500 font-bold"><?= $u['created_at'] ?></p>
                        <p><?= htmlspecialchars($u['update_text']) ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (!$edit['is_closed']): ?>
                    <form method="POST" class="mt-6 pt-4 border-t">
                        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                        <input type="hidden" name="add_update" value="1">
                        <textarea name="update_text" placeholder="Add update..." required class="w-full border p-2 rounded"></textarea>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 mt-2 rounded">Post Update</button>
                        <a href="admin_incidents.php" class="ml-4 text-slate-400 underline">Close View</a>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h2 class="text-xl font-bold mb-4">Recent Incidents</h2>
        <div class="bg-white rounded shadow divide-y">
            <?php foreach ($incidents as $i): ?>
                <div class="p-4 flex justify-between items-center">
                    <span><?= htmlspecialchars($i['title']) ?> <?= $i['is_closed'] ? '<span class="text-xs text-green-600 font-bold">(CLOSED)</span>' : '' ?></span>
                    <a href="?edit=<?= $i['id'] ?>" class="text-blue-600 underline">View/Update Timeline</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>