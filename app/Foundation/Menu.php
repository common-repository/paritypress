<?php

declare(strict_types=1);

namespace ParityPress\Foundation;

class Menu
{
    public function addMenu(
        string $pageTitle,
        string $menuTitle,
        string $slug,
        $callback,
        array $options = []
    ): void {
        \add_menu_page(
            $pageTitle,
            $menuTitle,
            $options['capability'] ?? 'manage_options',
            $slug,
            $callback,
            $options['icon'] ?? '',
            $options['position'] ?? 100
        );
    }

    public function addSubMenu(
        string $parentSlug,
        string $pageTitle,
        string $menuTitle,
        string $slug,
        $callback,
        array $options = []
    ): void {
        \add_submenu_page(
            $parentSlug,
            $pageTitle,
            $menuTitle,
            $options['capability'] ?? 'manage_options',
            $slug,
            $callback,
            $options['position'] ?? 10
        );

    }
}
