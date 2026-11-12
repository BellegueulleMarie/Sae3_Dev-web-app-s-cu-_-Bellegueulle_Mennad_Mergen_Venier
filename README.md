# Application Touiteur

Application PHP simple avec Composer et autoload PSR-4.

## Architecture

- `App\Action` : action de création d'un utilisateur ;
- `App\Contracts` : interfaces ;
- `App\Repository` : repository utilisateur avec PDO ;
- `App\Database` : connexion Oracle ;
- `App\Http` : Dispatcher ;
- `views/` : page PHP.

## Demarrage

```powershell
Copy-Item .env.example .env
composer install
composer serve
```

La page de création est disponible sur `http://localhost:8000`.

Les images de touites peuvent être téléversées depuis le formulaire de publication
(JPEG, PNG, GIF ou WebP, 5 Mo maximum). Elles sont stockées hors du répertoire servi
par le serveur et ne sont affichées que dans le détail d'un touite. Le répertoire
par défaut est `touiteur-uploads` à côté du projet ; il peut être remplacé avec la
variable d'environnement `IMAGE_UPLOAD_DIRECTORY`.
