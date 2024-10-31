<?php

declare(strict_types=1);

namespace ParityPress\Providers;

use ParityPress\Framework\Support\ServiceProvider;
use ParityPress\Http\Controllers\CampaignController;
use WP_REST_Server;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register rest routes
        add_action('rest_api_init', function () {
            $controller = $this->app->get(CampaignController::class);

            register_rest_route('parity-press/v1', '/campaigns/', [
                'methods'               => WP_REST_Server::READABLE,
                'callback'              => [$controller, 'index'],
                'permission_callback'   => [$controller, 'middleware'],
            ]);

            register_rest_route('parity-press/v1', '/campaigns/', [
                'methods'               => WP_REST_Server::CREATABLE,
                'callback'              => [$controller, 'store'],
                'permission_callback'   => [$controller, 'middleware'],
            ]);

            register_rest_route('parity-press/v1', '/campaigns/(?P<id>\d+)', [
                'methods'               => WP_REST_Server::READABLE,
                'callback'              => [$controller, 'show'],
                'permission_callback'   => [$controller, 'middleware'],
            ]);

            register_rest_route('parity-press/v1', '/campaigns/(?P<id>\d+)', [
                'methods'               => WP_REST_Server::EDITABLE,
                'callback'              => [$controller, 'update'],
                'permission_callback'   => [$controller, 'middleware'],
            ]);

            register_rest_route('parity-press/v1', '/campaigns/(?P<id>\d+)', [
                'methods'               => WP_REST_Server::DELETABLE,
                'callback'              => [$controller, 'destroy'],
                'permission_callback'   => [$controller, 'middleware'],
            ]);
        });
    }
}
