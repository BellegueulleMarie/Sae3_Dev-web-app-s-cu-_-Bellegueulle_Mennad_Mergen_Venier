# Base d'application web securisee

Cette base utilise PHP 8.1+ sans framework afin de garder une structure simple et
facile a faire evoluer.

## Organisation

```text
config/                 Configuration de l'application (jamais accessible par HTTP)
public/                 Seul dossier expose par le serveur web
  index.php             Point d'entree unique (front controller)
src/
  Http/                 Routage et traitement des requetes
  Security/             Sessions, CSRF et en-tetes de securite
  Bootstrap.php         Initialisation de l'application
templates/              Vues PHP, hors du dossier public
storage/
  cache/                Cache local (non versionne)
  logs/                 Journaux (non versionnes)
```

## Demarrage

```powershell
Copy-Item .env.example .env
composer install
composer serve
```

Le site est ensuite disponible sur `http://localhost:8000`.

## Base de donnees

Le schema Oracle est disponible dans `database/schema.sql`. Il cree les tables
des utilisateurs, touites, images, tags et leurs associations.

Le code est sépare en packages PHP correspondant aux namespaces `App\` :

- `App\Contracts` : interfaces des dependances ;
- `App\Dao` : acces aux tables Oracle ;
- `App\Service` : validation et regles applicatives ;
- `App\Http` : contrôleurs, routeur et réponses HTTP ;
- `App\Database` : connexion PDO ;
- `App\Security` : protections de session et de requêtes.

Les endpoints JSON suivants
sont disponibles lorsque `DB_DSN`, `DB_USERNAME` et `DB_PASSWORD` sont
configures dans `.env` :

- `GET|POST /api/utilisateurs`
- `PUT /api/utilisateurs/{id}`
- `GET|POST /api/tags`
- `GET|POST /api/touites`

## Regles de securite integrees

- point d'entree unique et aucun code applicatif dans `public/`;
- cookies de session `HttpOnly`, `SameSite=Lax` et `Secure` sous HTTPS;
- en-tetes CSP, anti-clickjacking, MIME sniffing et politique de referer;
- protection CSRF via `App\Security\Csrf` pour les formulaires qui modifient des donnees;
- affichage des erreurs desactive par defaut et journalisation des exceptions;
- echappement HTML obligatoire avec `htmlspecialchars()` lors du rendu.

Avant la mise en production, utiliser HTTPS, une vraie base de donnees avec des
requêtes preparees, une gestion d'authentification robuste, des migrations,
des sauvegardes et une rotation des journaux.