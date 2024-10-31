<?php

defined('ABSPATH') || exit;

require __DIR__ . '/vendor/autoload.php';

// Create the application
$app = new \ParityPress\Framework\Application(
    dirname(PARITY_PRESS_PLUGIN_FILE)
);

// Register the service providers
$app->register(\ParityPress\Providers\AppServiceProvider::class);
$app->register(\ParityPress\Providers\AdminServiceProvider::class);
$app->register(\ParityPress\Providers\RouteServiceProvider::class);

// Register the activation hook
register_activation_hook(PARITY_PRESS_PLUGIN_FILE, function () use ($app) {
    $app->make(\ParityPress\Hooks\Activator::class)->boot();
});

return $app;
