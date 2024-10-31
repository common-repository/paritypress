<?php

use Illuminate\Container\Container;

if (!function_exists('parity_press')) {
    function parity_press($make = null, array $parameters = [])
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }
}

if (!function_exists('parity_press_is_admin')) {
    function parity_press_is_admin(): bool
    {
        return (bool) current_user_can('manage_options');
    }
}

if (!function_exists('parity_press_get_countries')) {
    function parity_press_get_countries(): array
    {
        return require __DIR__ . '/countries.php';
    }
}

if (!function_exists('parity_press_get_country_by_code')) {
    function parity_press_get_country_by_code(string $code): array
    {
        $countries = parity_press_get_countries();

        return $countries[strtoupper($code)] ?? [];
    }
}

if (!function_exists('parity_press_nanoid')) {
    function parity_press_nanoid($length = 10)
    {
        $characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
        $id = '';

        while (strlen($id) < $length) {
            $id .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $id;
    }
}
