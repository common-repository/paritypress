<?php

declare(strict_types=1);

namespace ParityPress\Providers;

use ParityPress\Framework\Support\ServiceProvider;
use ParityPress\Services\DiscountBarService;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register scripts and styles
        add_action('wp_enqueue_scripts', function () {
            if (!is_front_page() && !is_single()) {
                return;
            }

            wp_enqueue_style('parity-press', PARITY_PRESS_PLUGIN_ASSETS . '/app.css', [], PARITY_PRESS_VERSION);

            wp_enqueue_script('parity-press', PARITY_PRESS_PLUGIN_ASSETS . '/app.js', [], PARITY_PRESS_VERSION, [
                'in_footer' => true,
                'strategy' => 'defer',
            ]);

            wp_localize_script(
                'parity-press',
                'parityPress',
                [
                    'discountText'  => DiscountBarService::getDiscountText(),
                    'customization' => DiscountBarService::getCustomization(),
                ]
            );
        });

        // Register post type
        add_action('init', function () {
            register_post_type(
                'parity_campaign',
                [
                    'labels' => [
                        'name'          => __('Campaigns', 'parity-press'),
                        'singular_name' => __('Campaign', 'parity-press'),
                    ],
                    'public'            => false,
                    'show_ui'           => false,
                    'show_in_menu'      => false,
                    'show_in_nav_menus' => false,
                    'show_in_admin_bar' => false,
                    'has_archive'       => false,
                    'show_in_rest'      => false,
                ]
            );
        });
    }
}
