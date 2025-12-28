# Déploiement sur O2Switch (hébergement mutualisé)

Puisque O2Switch ne supporte pas Docker, voici la procédure :

## 1. Prérequis
- PHP 8.4+ ✓ (O2Switch fournit)
- MariaDB 10.6.22+ ✓ (O2Switch fournit)
- Composer ✓ (présent sur O2Switch)
- SSH access ✓ (demande à O2Switch l'activation)
- Git ✓ (optionnel mais recommandé)

## 2. Préparation locale

Assure-toi que tout est commité et pushé :
```bash
git add .
git commit -m "Migration PostgreSQL → MariaDB + fix routing"
git push origin modif_projet
```

## 3. Déploiement initial sur O2Switch

Se connecter en SSH (remplace avec tes identifiants O2Switch) :
```bash
ssh user@ravignon-enzo.fr
cd public_html  # ou le dossier d'accueil de ton projet
```

Cloner le projet (ou pull si déjà présent) :
```bash
git clone https://github.com/TON_USERNAME/Porfolio.git .
# ou si le dossier existe déjà :
git pull origin modif_projet
```

Créer le fichier `.env.local` avec tes paramètres O2Switch :
```bash
cat > .env.local << 'EOF'
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=ta_clé_secrète_prod_ici
DATABASE_URL="mysql://user_o2switch:password_o2switch@localhost:3306/db_o2switch"
SERVER_NAME=ravignon-enzo.fr
EOF
```

## 4. Installation et configuration

```bash
# Installer les dépendances (sans dev)
composer install --no-dev --optimize-autoloader --no-interaction

# Exécuter les migrations pour créer les tables
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Importer les données de base (languages, logiciels, admin)
mysql -u user_o2switch -p db_o2switch < data.sql

# Vider et préchauffer le cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Permissions (important sur O2Switch)
chmod -R 777 var/
chmod -R 755 public/
```

## 5. Configuration du serveur web O2Switch

Sur O2Switch, configure le Document Root pour qu'il pointe vers `/public` du projet.

Crée un fichier `.htaccess` à la racine si besoin :
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

## 6. Mises à jour futures

Après chaque modification locale + push :
```bash
ssh user@ravignon-enzo.fr
cd public_html
git pull origin modif_projet
composer install --no-dev --optimize-autoloader --no-interaction
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true
```

## 7. Base de données O2Switch

- Accède via phpMyAdmin (fourni par O2Switch)
- Les identifiants sont dans le panel O2Switch
- Importe `data.sql` via phpMyAdmin pour les données initiales
- USER: `admin@admin.fr`
- PASSWORD: `Bomecou69!`

## Notes importantes

- ❌ Ne pousse jamais `.env.local` (contient les secrets prod)
- ✅ Ajoute `.env.local` à `.gitignore`
- 🔐 Change `APP_SECRET` par une clé forte et unique
- 📧 Configure `MAILER_DSN` si tu veux utiliser le formulaire de contact
- 🗂️ Assure-toi que le dossier `var/` est writable par le serveur web
