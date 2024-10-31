<?php

declare(strict_types=1);

namespace ParityPress\Data;

class CampaignData
{
    public static function sanitize($campaign): array
    {
        $campaign = (array) $campaign;

        $sanitized = [
            'id' => intval($campaign['id'] ?? 0),
            'title' => sanitize_text_field($campaign['title'] ?? ''),
            'description' => sanitize_textarea_field($campaign['description'] ?? ''),
            'discount_text' => sanitize_text_field($campaign['discount_text'] ?? ''),
            'start_date' => sanitize_text_field($campaign['start_date'] ?? ''),
            'end_date' => sanitize_text_field($campaign['end_date'] ?? ''),
            'customizations' => self::sanitizeCustomizations($campaign['customizations'] ?? []),
            'discounts' => self::sanitizeDiscounts($campaign['discounts'] ?? []),
        ];

        return $sanitized;
    }

    public static function escape($campaign): array
    {
        $campaign = (array) $campaign;

        $escaped = [
            'id' => intval($campaign['ID'] ?? 0),
            'title' => esc_html($campaign['title'] ?? ''),
            'description' => esc_textarea($campaign['description'] ?? ''),
            'discount_text' => esc_html($campaign['discount_text'] ?? ''),
            'start_date' => esc_attr($campaign['start_date'] ?? ''),
            'end_date' => esc_attr($campaign['end_date'] ?? ''),
            'customizations' => self::escapeCustomizations($campaign['customizations'] ?? []),
            'discounts' => self::escapeDiscounts($campaign['discounts'] ?? []),
        ];

        return $escaped;
    }

    private static function sanitizeCustomizations(array $customizations): array
    {
        return [
            'text_color' => sanitize_hex_color($customizations['text_color'] ?? ''),
            'background_color' => sanitize_hex_color($customizations['background_color'] ?? ''),
            'highlighted_color' => sanitize_hex_color($customizations['highlighted_color'] ?? ''),
            'padding' => intval($customizations['padding'] ?? 0),
            'font_size' => intval($customizations['font_size'] ?? 0),
        ];
    }

    private static function sanitizeDiscounts(array $discounts): array
    {
        return array_map(function ($discount) {
            return [
                'id' => sanitize_text_field($discount['id'] ?? ''),
                'countries' => array_map('sanitize_text_field', $discount['countries'] ?? []),
                'discount' => sanitize_text_field($discount['discount'] ?? ''),
                'coupon_code' => sanitize_text_field($discount['coupon_code'] ?? ''),
            ];
        }, $discounts);
    }

    private static function escapeCustomizations(array $customizations): array
    {
        return [
            'text_color' => esc_attr($customizations['text_color'] ?? ''),
            'background_color' => esc_attr($customizations['background_color'] ?? ''),
            'highlighted_color' => esc_attr($customizations['highlighted_color'] ?? ''),
            'padding' => intval($customizations['padding'] ?? 0),
            'font_size' => intval($customizations['font_size'] ?? 0),
        ];
    }

    private static function escapeDiscounts(array $discounts): array
    {
        return array_map(function ($discount) {
            return [
                'id' => esc_attr($discount['id'] ?? ''),
                'countries' => array_map('esc_attr', $discount['countries'] ?? []),
                'discount' => esc_attr($discount['discount'] ?? ''),
                'coupon_code' => esc_attr($discount['coupon_code'] ?? ''),
            ];
        }, $discounts);
    }
}
