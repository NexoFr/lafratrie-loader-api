<?php
/**
 * LaFratrie Loader - Configuration Serveur
 */

return [
    // Informations sur le loader
    'loader' => [
        'name' => 'LaFratrie Loader',
        'version' => '1.0.0',
        'author' => 'Quentin - La Fratrie',
    ],
    
    // Clés de signature (charger depuis fichier sécurisé)
    'keys' => [
        'public' => trim(@file_get_contents(__DIR__ . '/../Tools/keys/public.key') ?: ''),
        // La clé privée n'est JAMAIS exposée dans la config
    ],
    
    // Chemins
    'paths' => [
        'plugins' => __DIR__ . '/plugins',
        'manifests' => __DIR__ . '/manifests',
        'logs' => __DIR__ . '/logs',
    ],
    
    // Sécurité
    'security' => [
        'allowed_origins' => ['*'], // Restreindre en production
        'rate_limit' => 100, // Requêtes par minute par IP
        'require_api_key' => false, // true en production
    ],
    
    // Version minimum du serveur Nova Life supportée
    'min_nova_version' => '1.5.0.0',
    
    // Liste des plugins disponibles
    'plugins' => [
        'Administrator-Lafratrie' => [
            'displayName' => 'Administrator La Fratrie',
            'description' => 'Outils d\'administration pour le serveur',
            'category' => 'admin',
            'public' => false, // Nécessite licence
        ],
        'GarageVirtuel' => [
            'displayName' => 'Garage Virtuel',
            'description' => 'Système de fourrière avec ModKit',
            'category' => 'vehicules',
            'public' => false,
        ],
        'PointShop-lafratrie' => [
            'displayName' => 'Point Shop',
            'description' => 'Boutique de points avec restrictions entreprise',
            'category' => 'economie',
            'public' => false,
        ],
        'WikiCheckpoints' => [
            'displayName' => 'Wiki Checkpoints',
            'description' => 'Guide d\'intégration pour nouveaux joueurs',
            'category' => 'roleplay',
            'public' => true, // Gratuit
        ],
    ],
    
    // Discord webhook pour notifications (optionnel)
    'discord' => [
        'webhook_url' => '', // Remplir si tu veux des notifications
        'notify_downloads' => false,
        'notify_errors' => true,
    ],
];
