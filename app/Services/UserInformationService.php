<?php

declare(strict_types=1);

namespace ParityPress\Services;

use ParityPress\Services\IpDataProviders\IPApi;

class UserInformationService
{
    private const IP_HEADERS = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    public static function getUserIp(): string
    {
        foreach (self::IP_HEADERS as $header) {
            if (!isset($_SERVER[$header])) {
                continue;
            }

            $ip = self::processIpHeader($header, sanitize_text_field(wp_unslash($_SERVER[$header])));
            if ($ip) {
                return $ip;
            }
        }

        return '';
    }

    private static function processIpHeader(string $header, string $value): string
    {
        if ($header === 'HTTP_X_FORWARDED_FOR') {
            $ips = explode(',', $value);
            foreach ($ips as $ip) {
                $cleanIp = self::validateIp($ip);
                if ($cleanIp) {
                    return $cleanIp;
                }
            }
        } elseif ($header === 'HTTP_FORWARDED') {
            if (preg_match('/for=(.+?);/', $value, $matches)) {
                return self::validateIp($matches[1]);
            }
        } else {
            return self::validateIp($value);
        }

        return '';
    }

    private static function validateIp(string $ip): string
    {
        $cleanIp = trim($ip);
        return filter_var($cleanIp, FILTER_VALIDATE_IP) ? $cleanIp : '';
    }

    public static function getUserIpInformation(): ?array
    {
        $ipAddress = self::getUserIp();

        if (empty($ipAddress)) {
            return null;
        }

        if ($ipAddress === '127.0.0.1' || $ipAddress === 'localhost') {
            return [
                'country_code' => 'US',
            ];
        }

        $cacheKey = "parity_press_ip_cache_" . str_replace('.', '_', $ipAddress);
        $data = get_transient($cacheKey);

        if (!$data) {
            /** @var IPApi */
            $ipService = parity_press(IPApi::class);
            $data = $ipService->getData($ipAddress);

            set_transient($cacheKey, $data, 6 * HOUR_IN_SECONDS);
        }

        return $data;
    }
}
