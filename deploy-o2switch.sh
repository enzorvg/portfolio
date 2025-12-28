#!/bin/bash
# Script de déploiement Symfony classique pour O2Switch (sans Docker)

set -e

echo "🚀 Déploiement en production O2Switch..."

# 1. Installation des dépendances
echo "📦 Installation des dépendances Composer..."
composer install --no-dev --optimize-autoloader

# 2. Variables d'environnement
echo "⚙️ Configuration de l'environnement..."
# Créer/mettre à jour le fichier .env.local avec tes paramètres
# Les variables à définir :
# DATABASE_URL=mysql://user:password@host:3306/db_name
# APP_ENV=prod
# APP_SECRET=ton_secret_prod
# etc...

# 3. Vider le cache
echo "🧹 Nettoyage du cache..."
php bin/console cache:clear --env=prod --no-warmup

# 4. Warmup cache
echo "🔥 Préchauffage du cache..."
php bin/console cache:warmup --env=prod

# 5. Exécuter les migrations (si nécessaire)
echo "🗂️ Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod || true

# 6. Permissions
echo "🔐 Configuration des permissions..."
chmod -R 777 var/

echo "✅ Déploiement terminé!"
echo "📋 Vérifie que ces variables d'environnement sont définies dans .env.local :"
echo "   - DATABASE_URL"
echo "   - APP_ENV=prod"
echo "   - APP_SECRET"
echo "   - SERVER_NAME=ravignon-enzo.fr"
