<?php
/**
 * LaFratrie Loader - Réception Télémétrie
 * 
 * POST /telemetry
 * Reçoit des stats d'utilisation (optionnel)
 */

$config = require __DIR__ . '/config.php';

// Lire le body JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    errorResponse('Body JSON invalide', 400);
}

// Extraire les données
$data = [
    'timestamp' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'serverId' => $input['serverId'] ?? null,
    'serverName' => $input['serverName'] ?? null,
    'novaVersion' => $input['novaVersion'] ?? null,
    'loaderVersion' => $input['loaderVersion'] ?? null,
    'loadedPlugins' => $input['loadedPlugins'] ?? [],
    'playerCount' => $input['playerCount'] ?? 0,
    'uptime' => $input['uptime'] ?? 0,
    'os' => $input['os'] ?? null,
    'event' => $input['event'] ?? 'heartbeat',
];

// Sauvegarder dans un fichier de log
$logsDir = $config['paths']['logs'] ?? __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

$logFile = $logsDir . '/telemetry_' . date('Y-m-d') . '.jsonl';
file_put_contents($logFile, json_encode($data) . "\n", FILE_APPEND);

logRequest("Telemetry from {$data['serverName']} ({$data['event']})");

// Notification Discord si configuré
if (!empty($config['discord']['webhook_url']) && $data['event'] === 'startup') {
    $discordPayload = [
        'username' => 'LaFratrie Loader',
        'embeds' => [[
            'title' => '🟢 Serveur Démarré',
            'color' => 0x00ff00,
            'fields' => [
                ['name' => 'Serveur', 'value' => $data['serverName'] ?? 'Inconnu', 'inline' => true],
                ['name' => 'Nova Version', 'value' => $data['novaVersion'] ?? '?', 'inline' => true],
                ['name' => 'Plugins', 'value' => count($data['loadedPlugins']), 'inline' => true],
            ],
            'timestamp' => date('c'),
        ]],
    ];
    
    // Envoyer en async (fire and forget)
    $ch = curl_init($config['discord']['webhook_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($discordPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    curl_close($ch);
}

jsonResponse([
    'received' => true,
    'timestamp' => time(),
]);
