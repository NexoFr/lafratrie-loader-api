# LaFratrie Loader - API Serveur

## Déploiement sur Railway.app

### Étapes :

1. **Créer un compte sur Railway.app** (gratuit, sans carte bancaire)
   - https://railway.app/

2. **Créer un nouveau projet**
   - "New Project" → "Deploy from GitHub repo"
   - Ou "Empty Project" puis upload les fichiers

3. **Variables d'environnement** (optionnel)
   - Aucune variable requise pour l'instant

4. **Déploiement automatique**
   - Railway détecte PHP automatiquement grâce à `nixpacks.toml`
   - L'API sera accessible via une URL HTTPS

### Structure des dossiers :

```
Server/
├── index.php          # Point d'entrée
├── config.php         # Configuration
├── manifest.php       # Liste des plugins
├── download.php       # Téléchargement
├── verify.php         # Vérification licence
├── telemetry.php      # Télémétrie
├── nixpacks.toml      # Config Railway
├── .htaccess          # Config Apache
├── plugins/           # Tes DLL signées (à créer)
├── logs/              # Logs (créé auto)
└── Tools/             # Clés de signature
```

### Après déploiement :

1. Note l'URL fournie par Railway (ex: `https://lafratrie-api.up.railway.app`)
2. Modifie `LaFratrieLoader.cs` ligne 26 avec cette URL
3. Recompile le client C#
4. Upload tes plugins DLL dans le dossier `plugins/`

### Test :

```bash
curl https://ton-url-railway.app/
# Doit retourner : {"name":"LaFratrie Loader","version":"1.0.0","status":"online"}
```
