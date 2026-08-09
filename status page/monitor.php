<?php
// Set the timezone to match your location
date_default_timezone_set('Europe/London');

// Database Connection
$db = new PDO('mysql:host=localhost;dbname=status_monitor', 'root', 'YourNewStrongPassword');

// List of Nodes to monitor
$nodes = [
    ['id' => 0, 'name' => 'Main Website', 'url' => 'https://www.techgrid-systems.org.uk', 'icon' => 'globe'],
    ['id' => 1, 'name' => 'CPanel Website', 'url' => 'https://tgs-cpanel.techgrid-systems.org.uk', 'icon' => 'server'],
    ['id' => 2, 'name' => 'Helpdesk Website', 'url' => 'https://helpdesk.techgrid-systems.org.uk', 'icon' => 'headset'],
    ['id' => 3, 'name' => 'Support Portal', 'url' => 'https://support.techgrid-systems.org.uk', 'icon' => 'life-ring'],
    ['id' => 4, 'name' => 'End User Portal', 'url' => 'https://portal.techgrid-systems.org.uk', 'icon' => 'user-circle'],
    ['id' => 5, 'name' => 'Global Domain', 'url' => 'https://techgrid-systems.org.uk', 'icon' => 'link'],
    ['id' => 6, 'name' => 'Status Portal', 'url' => 'https://status.techgrid-systems.org.uk', 'icon' => 'signal'],
    ['id' => 7, 'name' => 'AI Portal', 'url' => 'https://ai.techgrid-systems.org.uk', 'icon' => 'robot'],
    ['id' => 8, 'name' => 'SharePoint', 'url' => 'https://sharepoint.techgrid-systems.org.uk', 'icon' => 'folder-open'],
    ['id' => 9, 'name' => 'Training', 'url' => 'https://training.techgrid-systems.org.uk', 'icon' => 'graduation-cap'],
    ['id' => 10, 'name' => 'Core Network - London', 'url' => 'https://techgrid-systems.org.uk', 'icon' => 'city'],
    ['id' => 11, 'name' => 'Primary DNS', 'url' => '8.8.8.8', 'icon' => 'database'],
    ['id' => 12, 'name' => 'Secondary Network - London', 'url' => 'https://techgrid-systems.org.uk', 'icon' => 'file-import']
];

foreach ($nodes as $n) {
    $status = 'down';
    
    // Check if the URL is an IP address
    if (filter_var($n['url'], FILTER_VALIDATE_IP)) {
        // Use fsockopen for IP (using port 53 for DNS/Core Server check)
        $connection = @fsockopen($n['url'], 53, $errno, $errstr, 2);
        if (is_resource($connection)) {
            $status = 'up';
            fclose($connection);
        }
    } else {
        // Use cURL for standard HTTPS URLs
        $ch = curl_init($n['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 200 && $httpCode < 400) {
            $status = 'up';
        }
        curl_close($ch);
    }

    // Update Database
    $stmt = $db->prepare("INSERT INTO node_status (id, name, url, icon, status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=?");
    $stmt->execute([$n['id'], $n['name'], $n['url'], $n['icon'], $status, $status]);
}
?>