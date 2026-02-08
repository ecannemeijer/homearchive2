<?php

/**
 * Script to set all subscriptions to active
 * Run this once to fix subscriptions that were incorrectly set to inactive
 */

require __DIR__.'/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

$entityManager = $container->get('doctrine')->getManager();

// Get all subscriptions
$subscriptions = $entityManager->getRepository(\App\Entity\Subscription::class)->findAll();

$updated = 0;
foreach ($subscriptions as $subscription) {
    if (!$subscription->isActive()) {
        $subscription->setIsActive(true);
        $updated++;
    }
}

$entityManager->flush();

echo "✓ {$updated} abonnementen zijn actief gezet\n";
echo "✓ Totaal aantal abonnementen: " . count($subscriptions) . "\n";
