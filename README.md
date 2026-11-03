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
