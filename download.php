<?php
/**
 * LaFratrie Loader - Téléchargement de Plugin
 */

function handlePluginRequest($productId, $infoOnly, $config) {
    $pluginsDir = $config['paths']['plugins'];
    $dllPath = $pluginsDir . '/' . $productId . '.dll';
    $sigPath = $dllPath . '.sig.json';
    
    // Vérifier que le plugin existe
    if (!file_exists($dllPath)) {
        errorResponse("Plugin '{$productId}' non trouvé", 404);
    }
    
    // Charger la config du plugin
    $pluginConfig = $config['plugins'][$productId] ?? null;
    if (!$pluginConfig) {
        errorResponse("Plugin '{$productId}' non configuré", 404);
    }
    
    // Charger les infos de signature
    $sigData = null;
    if (file_exists($sigPath)) {
        $sigData = json_decode(file_get_contents($sigPath), true);
    }
    
    // Mode info seulement
    if ($infoOnly) {
        $info = [
            'productId' => $productId,
            'displayName' => $pluginConfig['displayName'],
            'description' => $pluginConfig['description'] ?? '',
            'category' => $pluginConfig['category'] ?? 'general',
            'public' => $pluginConfig['public'] ?? false,
            'version' => $sigData['version'] ?? '1.0.0',
            'sha256' => $sigData['sha256'] ?? hash_file('sha256', $dllPath),
            'signature' => $sigData['signature'] ?? null,
            'size' => filesize($dllPath),
            'signed' => !empty($sigData['signature']),
        ];
        
        logRequest("Plugin info: {$productId}");
        jsonResponse($info);
        return;
    }
    
    // Vérifier la licence si plugin non public
    if (!($pluginConfig['public'] ?? false)) {
        $serverId = $_SERVER['HTTP_X_SERVER_ID'] ?? null;
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;
        
        // TODO: Implémenter la vérification de licence ici
        // Pour l'instant, on laisse passer
        
        /*
        if (!verifyLicense($productId, $serverId, $apiKey)) {
            errorResponse("Licence invalide pour '{$productId}'", 403);
        }
        */
    }
    
    // Envoyer le fichier
    $content = file_get_contents($dllPath);
    $sha256 = hash('sha256', $content);
    
    logRequest("Plugin download: {$productId} (v{$sigData['version']}) - " . strlen($content) . " bytes");
    
    // Headers pour le téléchargement
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $productId . '.dll"');
    header('Content-Length: ' . strlen($content));
    header('X-Plugin-Id: ' . $productId);
    header('X-Plugin-Version: ' . ($sigData['version'] ?? '1.0.0'));
    header('X-Plugin-SHA256: ' . $sha256);
    header('X-Plugin-Signature: ' . ($sigData['signature'] ?? 'unsigned'));
    header('Cache-Control: no-cache, must-revalidate');
    
    echo $content;
    exit;
}
