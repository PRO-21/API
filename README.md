# API

L'API utilise [Laravel Lumen v8.2.3](https://lumen.laravel.com/docs/8.x).

Technologies de sécurités utilisées :

- Authentification : [JSON Web Token](https://jwt.io) & [library Firebase](https://firebase.google.com/docs/auth/admin/create-custom-tokens)
- Hash des mots de passes : SHA-512
- Chiffrement des champs des certificats : AES-256-CBC

Documentation : https://pro.simeunovic.ch:8022/api-doc/

## Installation

1. Installer Composer https://getcomposer.org/download/
2. Cloner le projet et charger les dépendances :
`composer i`
3. À la racine du projet, créer un fichier nommé ".env" et y placer ces informations remplit avec vos paramètres :

```
APP_NAME=PDFAuth-API
APP_ENV=local
APP_KEY=<your key> // Utilisé (entre autres) pour le chiffrement des champs des certificats
APP_DEBUG=true
APP_URL=<your URL>
APP_TIMEZONE=UTC
APP_VERSION=1.2

LOG_CHANNEL=stack
LOG_SLACK_WEBHOOK_URL=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_DRIVER=file
QUEUE_CONNECTION=sync

JWT_SECRET=<your secret> // Utilisé pour la signature des token JWT
```
4. Démarrer un serveur lumen en local: `php -S localhost:8000 -t public`
