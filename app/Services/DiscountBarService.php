<?php

declare(strict_types=1);

namespace ParityPress\Services;

use ParityPress\Services\UserInformationService;

class DiscountBarService
{
    private static $activeCampaign;

    public static function getActiveCampaign(): ?object
    {
        if (!self::$activeCampaign) {
            $service = parity_press(CampaignService::class);

            self::$activeCampaign = $service->getActiveCampaign();
        }

        return self::$activeCampaign;
    }

    public static function getCustomization(): array
    {
        $campaign = self::getActiveCampaign();

        return $campaign ? $campaign->customizations : [];
    }

    public static function getDiscountText()
    {
        $campaign = self::getActiveCampaign();

        if (empty($campaign)) {
            return '';
        }

        $userIpInformation = UserInformationService::getUserIpInformation();

        if (empty($userIpInformation)) {
            return '';
        }

        $customizations = $campaign->customizations;
        $discounts = $campaign->discounts;

        $highlighted_color = $customizations['highlighted_color'] ?? '#000';

        $discount = self::extractDiscount($discounts, $userIpInformation);

        $country = parity_press_get_country_by_code($userIpInformation['country_code']);

        if (
            empty($discount)
            || empty($country)
            || empty($country['flag'] ?? '')
            || empty($country['name'] ?? '')
            || empty($discount['coupon_code'] ?? '')
            || empty($discount['discount'] ?? '')
        ) {
            return '';
        }

        $templateService = parity_press()->get(TemplateService::class);
        $templateService->replacements([
            'FLAG'      => $country['flag'],
            'COUNTRY'   => self::wrapHighlight($country['name'], $highlighted_color),
            'COUPON'    => self::wrapHighlight($discount['coupon_code'], $highlighted_color),
            'DISCOUNT'  => self::wrapHighlight($discount['discount'], $highlighted_color),
        ]);

        return $templateService->parse($campaign->discount_text);
    }

    public static function extractDiscount(array $discounts, array $conditions): array
    {
        $countryCode = $conditions['country_code'] ?? '';

        if (empty($discounts) || empty($countryCode)) {
            return [];
        }

        foreach ($discounts as $discount) {
            $discountCountries = $discount['countries'] ?? [];

            if (is_array($discountCountries) && (in_array($countryCode, $discountCountries) || in_array('*', $discountCountries))) {
                return $discount;
            }

            if ($discountCountries === '*') {
                return $discount;
            }
        }

        return [];
    }

    public static function wrapHighlight(string $text, $highlighted_color): string
    {
        return '<b style="color: ' . $highlighted_color . '">' . $text . '</b>';
    }
}
