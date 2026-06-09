# Déploiement Vercel — France Étude

## 1. Pousser le code sur GitHub

```bash
cd D:\france-etude
git add .
git commit -m "Configuration Vercel : PHP serverless, PostgreSQL, routes"
git push origin main
```

## 2. Créer le projet Vercel

1. Allez sur [vercel.com/new](https://vercel.com/new)
2. Importez le repo **Serge173/france_etude**
3. Framework Preset : **Other**
4. Ne modifiez pas le dossier racine

## 3. Base PostgreSQL (obligatoire)

Dans le projet Vercel :

1. Onglet **Storage** → **Create Database** → **Postgres** (Neon)
2. Nom : `france-etude-db`
3. Région : la plus proche (ex. `Frankfurt`)
4. Connectez la base au projet → `POSTGRES_URL` est ajouté automatiquement

## 4. Variables d'environnement

**Settings → Environment Variables** (Production + Preview) :

| Nom | Valeur |
|-----|--------|
| `DB_DRIVER` | `pgsql` |
| `SECRET_KEY` | clé aléatoire 32+ caractères (ex. générateur de mot de passe) |

`POSTGRES_URL` est déjà fourni par Storage.

## 5. Déployer

Cliquez **Deploy** ou :

```bash
npx vercel login
npx vercel --prod
```

## 6. Vérifier

- Site : `https://votre-projet.vercel.app/`
- Santé : `https://votre-projet.vercel.app/api/health.php` → `{"ok":true,"db_ok":true,...}`

## 7. Créer l'administrateur

1. Ouvrez `https://votre-projet.vercel.app/install.php`
2. Créez le compte admin
3. Connectez-vous sur `/admin/`
4. Supprimez ou renommez `install.php` après installation

## Dépannage

| Problème | Solution |
|----------|----------|
| `POSTGRES_URL requis` | Ajoutez Postgres dans Storage Vercel |
| Formulaire « session expirée » | Vérifiez `SECRET_KEY` définie |
| CSS/images absents | Vérifiez que `assets/` est bien dans le repo Git |
| 404 sur `/admin` | Redéployez après push du `vercel.json` |

## Local (développement)

```bash
php -S localhost:8080
```

SQLite est utilisé automatiquement en local.
