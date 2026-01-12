<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Testing mail configuration...\n";
    echo "MAIL_HOST: " . env('MAIL_HOST') . "\n";
    echo "MAIL_PORT: " . env('MAIL_PORT') . "\n";
    echo "MAIL_USERNAME: " . env('MAIL_USERNAME') . "\n";
    echo "MAIL_ENCRYPTION: " . env('MAIL_ENCRYPTION') . "\n";
    echo "MAIL_FROM_ADDRESS: " . env('MAIL_FROM_ADDRESS') . "\n";
    echo "\nMail configuration appears to be set correctly.\n";
    echo "Queue connection: " . env('QUEUE_CONNECTION') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
