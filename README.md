# France Étude — Plateforme de candidature

Site de candidature pour étudiants souhaitant postuler dans les écoles du réseau **FIGS Education** (17 établissements, 15+ campus en France).

- **Page d'accueil** : sections avec navigation ancres (Accueil, FIGS, Écoles, Campus, Procédure, Candidature)
- **Formulaire** : enregistrement des candidatures en base
- **Administration** : liste, recherche, filtres, détail, export CSV, gestion des statuts

## Prérequis

- PHP **8.1+** avec extensions `pdo`, `pdo_sqlite` (et `pdo_mysql` si MySQL)
- Serveur web Apache ou Nginx, ou PHP built-in pour tests locaux

## Installation rapide (local)

```bash
cd d:\france-etude
php -S localhost:8080
```

1. Ouvrez **http://localhost:8080/install.php**
2. Créez le compte administrateur
3. **Supprimez ou renommez `install.php`** après installation
4. Site public : **http://localhost:8080/**
5. Admin : **http://localhost:8080/admin/**

La base SQLite est créée automatiquement dans `data/france_etude.sqlite`.

## Déploiement sur Vercel (recommandé)

Guide complet : **[DEPLOY-VERCEL.md](DEPLOY-VERCEL.md)**

Résumé : importez le repo GitHub sur Vercel, ajoutez **Postgres** (Storage), définissez `DB_DRIVER=pgsql` et `SECRET_KEY`, puis ouvrez `/install.php`.

## Déploiement production (hébergement mutualisé / VPS)

### 1. Fichiers

Uploadez tout le dossier sur votre hébergement (racine du site ou sous-dossier).

### 2. Permissions

- Dossier `data/` : **écriture** par le serveur web (755 ou 775)
- Ne pas exposer `data/` publiquement (`.htaccess` inclus)

### 3. MySQL (recommandé en production)

1. Créez une base MySQL et un utilisateur
2. Copiez `config/config.local.example.php` → `config/config.local.php`
3. Décommentez et renseignez les constantes MySQL + `DB_DRIVER` = `mysql`
4. Exécutez une fois `install.php` pour créer les tables et l'admin

### 4. Sécurité

- Supprimez **install.php** après la première installation
- Utilisez **HTTPS** (Let's Encrypt)
- Mot de passe admin fort (12+ caractères)
- Changez l'email de contact dans `includes/footer.php`

### 5. Apache

Le fichier `.htaccess` à la racine active `index.php` et des en-têtes de sécurité de base.

### 6. Nginx (extrait)

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
location ^~ /data/ {
    deny all;
}
```

## Structure

```
├── index.php              # Page d'accueil + formulaire
├── install.php            # Installation (à retirer)
├── api/submit.php         # API candidature (JSON)
├── admin/                 # Connexion + tableau de bord + export CSV
├── assets/css|js/         # Styles et scripts
├── config/                # Configuration
├── includes/              # DB, helpers, données écoles FIGS
└── data/                  # Base SQLite (ignorée par git)
```

## Partenariat FIGS

Les listes d'écoles et campus sont alignées sur [figs-education.com](https://www.figs-education.com/ecoles). Mettez à jour `includes/data-schools.php` si le réseau évolue.

## Support

Email affiché sur le site : modifiez dans `includes/footer.php`.
