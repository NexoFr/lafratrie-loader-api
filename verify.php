<?php
/**
 * LaFratrie Loader - Vérification de Licence
 * 
 * POST /verify
 * Body: {
 *   "productId": "MonPlugin",
 *   "serverId": "server-unique-id",
 *   "steamId": "76561198xxxxxxxxx",
 *   "machineFp": "fingerprint-hash",
 *   "timestamp": 1234567890
 * }
 */

$config = require __DIR__ . '/config.php';

// Lire le body JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    errorResponse('Body JSON invalide', 400);
}

$productId = $input['productId'] ?? null;
$serverId = $input['serverId'] ?? null;
$steamId = $input['steamId'] ?? null;
$machineFp = $input['machineFp'] ?? null;
$timestamp = $input['timestamp'] ?? 0;

// Validation basique
if (!$productId) {
    errorResponse('productId requis', 400);
}

// Vérifier que le timestamp n'est pas trop vieux (5 minutes max)
if (abs(time() - $timestamp) > 300) {
    errorResponse('Timestamp expiré', 400);
}

// Vérifier que le plugin existe
$pluginConfig = $config['plugins'][$productId] ?? null;
if (!$pluginConfig) {
    errorResponse("Plugin '{$productId}' inconnu", 404);
}

// Si le plugin est public, toujours valide
if ($pluginConfig['public'] ?? false) {
    logRequest("Verify OK (public): {$productId} from {$serverId}");
    jsonResponse([
        'valid' => true,
        'productId' => $productId,
        'type' => 'public',
        'message' => 'Plugin public - aucune licence requise',
    ]);
}

// TODO: Implémenter ta logique de vérification de licence ici
// Par exemple, vérifier dans une base de données

/*
// Exemple avec SQLite
$db = new PDO('sqlite:' . __DIR__ . '/licenses.db');
$stmt = $db->prepare('SELECT * FROM licenses WHERE product_id = ? AND server_id = ? AND active = 1');
$stmt->execute([$productId, $serverId]);
$license = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$license) {
    errorResponse('Licence non trouvée', 403);
}

if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
    errorResponse('Licence expirée', 403);
}
*/

// Pour l'instant, on simule une licence valide
$response = [
    'valid' => true,
    'productId' => $productId,
    'serverId' => $serverId,
    'type' => 'licensed',
    'expiresAt' => date('c', strtotime('+1 year')), // Expire dans 1 an
    'features' => [
        'updates' => true,
        'support' => true,
    ],
];

logRequest("Verify OK: {$productId} for server {$serverId}");

jsonResponse($response);
