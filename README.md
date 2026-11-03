# Application Touiteur

Application PHP 8.1+ sans framework, organisee avec namespaces et autoload PSR-4
via Composer.

## Architecture

- `App\Action` : une classe par cas d'utilisation, avec `execute()` ;
- `App\Contracts` : interfaces ;
- `App\Repository` : Repository Singleton et requetes PDO preparees ;
- `App\Database` : connexion a Oracle ;
- `App\Http` : Dispatcher, lecture des requetes et reponses JSON ;
- `views/` : vues PHP.

## Demarrage

```powershell
Copy-Item .env.example .env
composer install
composer serve
```

La page est disponible sur `http://localhost:8000`.

Les routes utilisateur sont :

- `GET /api/utilisateurs`
- `GET /api/utilisateurs/{id}`
- `POST /api/utilisateurs`
- `PUT /api/utilisateurs/{id}`

La gestion d'authentification n'est pas incluse.
