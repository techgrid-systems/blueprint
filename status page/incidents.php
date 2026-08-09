<?php
// Set time to BST
date_default_timezone_set('Europe/London');
$db = new PDO('mysql:host=localhost;dbname=status_monitor', 'root', 'YourNewStrongPassword');

// Fetch all incidents, newest first
$incidents = $db->query("SELECT * FROM history ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incident Archive | TechGrid Systems</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-6 md:p-10">
<div class="mb-8">
    <a href="index.php" class="text-slate-500 hover:text-slate-800">&larr; Return to Dashboard</a>
</div>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-800 mb-8">Incident Archive</h1>
        
        <div class="space-y-8">
            <?php foreach ($incidents as $h): 
                // Fetch updates for this incident
                $uStmt = $db->prepare("SELECT * FROM incident_updates WHERE incident_id = ? ORDER BY created_at ASC");
                $uStmt->execute([$h['id']]);
                $updates = $uStmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 <?= $h['is_closed'] ? 'border-green-500' : 'border-amber-500' ?>">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($h['title']) ?></h2>
                            <p class="text-sm text-slate-500">Created: <?= date('d M Y, H:i', strtotime($h['created_at'])) ?></p>
                        </div>
                        <?php if ($h['is_closed']): ?>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Resolved</span>
                        <?php else: ?>
                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Active</span>
                        <?php endif; ?>
                    </div>

                    <!-- Initial Description -->
                    <p class="text-slate-700 mb-6 bg-slate-50 p-4 rounded"><?= nl2br(htmlspecialchars($h['description'])) ?></p>

                    <!-- Timeline -->
                    <?php if (!empty($updates)): ?>
                        <div class="ml-4 border-l-2 border-slate-200 space-y-4">
                            <?php foreach ($updates as $u): ?>
                                <div class="relative pl-6">
                                    <div class="absolute -left-[9px] top-1 w-4 h-4 bg-blue-500 rounded-full border-4 border-white"></div>
                                    <p class="text-xs text-slate-400 font-bold uppercase"><?= date('d M, H:i', strtotime($u['created_at'])) ?></p>
                                    <p class="text-slate-600"><?= htmlspecialchars($u['update_text']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Resolution Footer -->
                    <?php if ($h['is_closed']): ?>
                        <div class="mt-6 pt-4 border-t border-slate-100 text-sm text-green-700 font-medium">
                            <i class="fas fa-check-circle mr-1"></i> Incident fully resolved on <?= date('d M Y, H:i', strtotime($h['closed_at'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
</body>
</html>