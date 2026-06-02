<?php
// Secret lu depuis .env.local sur le serveur (jamais dans git)
define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') ?: '');

// Vérifie la signature GitHub
$payload = file_get_contents('php://input');
$signature = 'sha256=' . hash_hmac('sha256', $payload, WEBHOOK_SECRET);

if (!hash_equals($signature, $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '')) {
    http_response_code(403);
    die('Signature invalide');
}

$data = json_decode($payload, true);
$branch = $data['ref'] ?? '';

// Ne déploie que depuis la branche main
if ($branch !== 'refs/heads/main') {
    http_response_code(200);
    die('Branche ignorée : ' . $branch);
}

$deployPath = '/home/fizu3884/sitesProjets/ravignon-enzo.fr';
$logFile    = $deployPath . '/var/deploy.log';

$commands = [
    "cd $deployPath && git pull origin main 2>&1",
    "cd $deployPath && php /opt/cpanel/composer/bin/composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts 2>&1",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console assets:install public --symlink --relative 2>&1",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console doctrine:migrations:migrate --no-interaction --env=prod 2>&1",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --env=prod 2>&1",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup --env=prod 2>&1",
    "chmod -R 775 $deployPath/var/ 2>&1",
];

$log = date('[Y-m-d H:i:s]') . " Déploiement déclenché\n";

foreach ($commands as $cmd) {
    $output = shell_exec($cmd);
    $log .= "$ $cmd\n$output\n";
}

$log .= date('[Y-m-d H:i:s]') . " Déploiement terminé\n\n";
file_put_contents($logFile, $log, FILE_APPEND);

http_response_code(200);
echo 'OK';
