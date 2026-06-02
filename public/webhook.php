<?php
$deployPath = '/home/fizu3884/sitesProjets/ravignon-enzo.fr';

// Lit le secret depuis .env.local (PHP web ne charge pas les variables d'env Symfony)
$secret = '';
$envFile = $deployPath . '/.env.local';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, 'WEBHOOK_SECRET=') === 0) {
            $secret = trim(substr($line, strlen('WEBHOOK_SECRET=')));
            break;
        }
    }
}

if (empty($secret)) {
    http_response_code(500);
    die('WEBHOOK_SECRET non configuré dans .env.local');
}

// Vérifie la signature GitHub
$payload = file_get_contents('php://input');
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($signature, $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '')) {
    http_response_code(403);
    die('Signature invalide');
}

$data = json_decode($payload, true);
$branch = $data['ref'] ?? '';

if ($branch !== 'refs/heads/main') {
    http_response_code(200);
    die('Branche ignorée : ' . $branch);
}

$logFile = $deployPath . '/var/deploy.log';
$logDir  = $deployPath . '/var';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

$log = date('[Y-m-d H:i:s]') . " Déploiement déclenché (push sur main)\n";

$commands = [
    "cd $deployPath && git -C $deployPath pull origin main",
    "cd $deployPath && php /opt/cpanel/composer/bin/composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console assets:install public --symlink --relative",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console doctrine:migrations:migrate --no-interaction --env=prod",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --env=prod",
    "cd $deployPath && APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup --env=prod",
    "chmod -R 775 $deployPath/var/",
];

foreach ($commands as $cmd) {
    $output = shell_exec($cmd . ' 2>&1');
    $log .= "\n$ $cmd\n" . ($output ?? '(pas de sortie)') . "\n";
}

$log .= "\n" . date('[Y-m-d H:i:s]') . " Terminé\n" . str_repeat('-', 60) . "\n";
file_put_contents($logFile, $log, FILE_APPEND);

http_response_code(200);
echo 'OK';
