<?php
/**
 * LaFratrie Loader - Génération du Manifest
 * 
 * Retourne la liste des plugins disponibles avec leurs signatures
 */

$config = require __DIR__ . '/config.php';
$pluginsDir = $config['paths']['plugins'];

$manifest = [
    'version' => '1.0',
    'generatedAt' => date('c'),
    'loader' => [
        'minVersion' => '1.0.0',
        'currentVersion' => $config['loader']['version'],
    ],
    'server' => [
        'minNovaVersion' => $config['min_nova_version'],
    ],
    'publicKey' => $config['keys']['public'],
    'plugins' => [],
];

// Parcourir les plugins configurés
foreach ($config['plugins'] as $productId => $pluginConfig) {
    $dllPath = $pluginsDir . '/' . $productId . '.dll';
    $sigPath = $dllPath . '.sig.json';
    
    // Vérifier que le DLL et sa signature existent
    if (!file_exists($dllPath)) {
        continue; // Plugin pas encore uploadé
    }
    
    $pluginEntry = [
        'productId' => $productId,
        'displayName' => $pluginConfig['displayName'],
        'description' => $pluginConfig['description'] ?? '',
        'category' => $pluginConfig['category'] ?? 'general',
        'public' => $pluginConfig['public'] ?? false,
        'available' => true,
    ];
    
    // Charger les infos de signature si disponibles
    if (file_exists($sigPath)) {
        $sigData = json_decode(file_get_contents($sigPath), true);
        if ($sigData) {
            $pluginEntry['version'] = $sigData['version'] ?? '1.0.0';
            $pluginEntry['sha256'] = $sigData['sha256'];
            $pluginEntry['signature'] = $sigData['signature'];
            $pluginEntry['size'] = $sigData['size'];
            $pluginEntry['updatedAt'] = $sigData['signedAt'] ?? date('c');
        }
    } else {
        // Calculer à la volée (moins sécurisé, pour dev seulement)
        $content = file_get_contents($dllPath);
        $pluginEntry['version'] = '1.0.0';
        $pluginEntry['sha256'] = hash('sha256', $content);
        $pluginEntry['signature'] = null; // Non signé !
        $pluginEntry['size'] = strlen($content);
        $pluginEntry['updatedAt'] = date('c', filemtime($dllPath));
        $pluginEntry['warning'] = 'Plugin non signé - utilisez sign-plugin.php';
    }
    
    // URL de téléchargement
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
               . '://' . $_SERVER['HTTP_HOST'];
    $pluginEntry['downloadUrl'] = $baseUrl . '/plugin/' . $productId;
    
    $manifest['plugins'][] = $pluginEntry;
}

// Trier par nom
usort($manifest['plugins'], function($a, $b) {
    return strcasecmp($a['displayName'], $b['displayName']);
});

// Stats
$manifest['stats'] = [
    'totalPlugins' => count($manifest['plugins']),
    'publicPlugins' => count(array_filter($manifest['plugins'], fn($p) => $p['public'])),
    'signedPlugins' => count(array_filter($manifest['plugins'], fn($p) => !empty($p['signature']))),
];

logRequest("Manifest requested - {$manifest['stats']['totalPlugins']} plugins");

jsonResponse($manifest);
