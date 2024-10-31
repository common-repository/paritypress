<?php

namespace ParityPress\Services\IpDataProviders;

use ParityPress\Contracts\IpInformationProvider;

class IPApi implements IpInformationProvider
{
    private function get(string $url): ?array
    {
        $response = wp_remote_get($url);

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            return json_decode($body, true);
        }

        return null;
    }

    public function getData(string $ip): ?array
    {
        $url = "http://ip-api.com/json/{$ip}";
        $response = $this->get($url);

        if (!$response || $response['status'] !== 'success') {
            return null;
        }

        return [
            'country_code' => $response['countryCode'],
            'timezone' => $response['timezone'],
        ];
    }
}
