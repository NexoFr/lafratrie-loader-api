<?php
/**
 * LaFratrie Loader - API Principale
 * 
 * Points d'entrée:
 *   GET  /                     → Info API
 *   GET  /manifest             → Liste des plugins
 *   GET  /plugin/{id}          → Télécharger un plugin
 *   GET  /plugin/{id}/info     → Info sur un plugin
 *   POST /verify               → Vérifier une licence
 *   POST /telemetry            → Recevoir télémétrie
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Loader-Version: 1.0.0');

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Server-Id');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Charger la configuration
$config = require __DIR__ . '/config.php';

// Router simple
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Logging
function logRequest($message, $level = 'INFO') {
    global $config;
    $logFile = ($config['paths']['logs'] ?? __DIR__ . '/logs') . '/api.log';
    $line = sprintf("[%s] [%s] [%s] %s\n", 
        date('Y-m-d H:i:s'), 
        $level, 
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $message
    );
    @file_put_contents($logFile, $line, FILE_APPEND);
}

// Réponse JSON
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Erreur
function errorResponse($message, $code = 400) {
    logRequest("ERROR: {$message}", 'ERROR');
    jsonResponse(['error' => true, 'message' => $message], $code);
}

// Routes
try {
    // GET / - Info API
    if ($uri === '' || $uri === '/') {
        jsonResponse([
            'name' => $config['loader']['name'],
            'version' => $config['loader']['version'],
            'status' => 'online',
            'timestamp' => time(),
            'endpoints' => [
                'manifest' => '/manifest',
                'plugin' => '/plugin/{productId}',
                'verify' => '/verify',
            ]
        ]);
    }
    
    // GET /manifest - Liste des plugins
    if ($uri === '/manifest') {
        require __DIR__ . '/manifest.php';
        exit;
    }
    
    // GET /plugin/{id} - Télécharger ou info
    if (preg_match('#^/plugin/([a-zA-Z0-9_-]+)(/info)?$#', $uri, $matches)) {
        $productId = $matches[1];
        $infoOnly = isset($matches[2]);
        
        require __DIR__ . '/download.php';
        handlePluginRequest($productId, $infoOnly, $config);
        exit;
    }
    
    // POST /verify - Vérification licence
    if ($uri === '/verify' && $method === 'POST') {
        require __DIR__ . '/verify.php';
        exit;
    }
    
    // POST /telemetry - Télémétrie
    if ($uri === '/telemetry' && $method === 'POST') {
        require __DIR__ . '/telemetry.php';
        exit;
    }
    
    // 404
    errorResponse('Endpoint non trouvé', 404);
    
} catch (Exception $e) {
    logRequest("Exception: " . $e->getMessage(), 'ERROR');
    errorResponse('Erreur interne du serveur', 500);
}
