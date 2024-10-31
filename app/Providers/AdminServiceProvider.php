<?php

declare(strict_types=1);

namespace ParityPress\Providers;

use ParityPress\Foundation\Menu;
use ParityPress\Framework\Support\ServiceProvider;
use ParityPress\Services\UserInformationService;

class AdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!parity_press_is_admin()) {
            return;
        }

        // Register scripts and styles
        add_action('admin_enqueue_scripts', function ($hook) {
            if ('toplevel_page_parity-press' !== $hook) {
                return;
            }

            wp_enqueue_style('parity-press', PARITY_PRESS_PLUGIN_ASSETS . '/admin.css', [], time());

            wp_enqueue_script('parity-press', PARITY_PRESS_PLUGIN_ASSETS . '/admin.js', ['react', 'react-dom', 'wp-api-fetch', 'wp-dom-ready', 'wp-element', 'wp-hooks'], time(), true);

            wp_localize_script(
                'parity-press',
                'parityPressAdmin',
                [
                    'apiBase' => esc_url_raw(rest_url()),
                    'nonce' => wp_create_nonce('parity-press-admin-request'),
                    'userIpInformation' => UserInformationService::getUserIpInformation() ?? [
                        'country_code'  => 'US',
                        'timezone'      => '',
                    ]
                ]
            );
        });

        // Register admin menu
        add_action('admin_menu', function () {
            $this->app->get(Menu::class)
                ->addMenu(
                    __('ParityPress', 'parity-press'),
                    __('ParityPress', 'parity-press'),
                    'parity-press',
                    [$this, 'dashboardMarkup'],
                    [
                        'icon' => 'dashicons-tickets-alt',
                        'position' => 25
                    ]
                );
        });
    }

    public function dashboardMarkup()
    {
        echo '<div id="parity-press-admin" class="parity-press">Loading...</div>';
    }
}
