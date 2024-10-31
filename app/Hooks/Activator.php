<?php

declare(strict_types=1);

namespace ParityPress\Hooks;

use ParityPress\Services\CampaignService;

class Activator
{
    public function boot(): void
    {
        $service = parity_press(CampaignService::class);

        if (count($service->all()) > 0) {
            return;
        }

        $data = [
            'title'         => __('Default Campaign', 'parity-press'),
            'description'   => __('This is the default campaign.', 'parity-press'),
            'discount_text' => "{FLAG} It's looks like you are from {COUNTRY}. Use coupon code {COUPON} to get {DISCOUNT} off on your purchase.",
            'start_date'    => null,
            'end_date'      => null,
            'customizations'        => ([
                'text_color'        => '#000000',
                'background_color'  => '#ffda75',
                'highlighted_color' => '#B21515',
                'padding'           => 10,
                'font_size'         => 15,
            ]),
            'discounts' => ([
                [
                    'id'            => parity_press_nanoid(),
                    'countries'     => ['US', 'CA', 'DE', 'FR', 'JP', 'AU', 'GB', 'IT', 'ES', 'KR', 'SG', 'HK', 'NZ', 'NL', 'CH'],
                    'discount'      => '10%',
                    'coupon_code'   => 'OFF10',
                ],
                [
                    'id'            => parity_press_nanoid(),
                    'countries'     => ['CN', 'BR', 'RU', 'ZA', 'MX', 'TR', 'MY', 'TH', 'PL', 'RO', 'AR', 'CL', 'HU', 'PT', 'GR'],
                    'discount'      => '20%',
                    'coupon_code'   => 'OFF20',
                ],
                [
                    'id'            => parity_press_nanoid(),
                    'countries'     => ['IN', 'PH', 'ID', 'VN', 'NG', 'EG', 'UA', 'PK', 'BD', 'KE', 'MA', 'TN', 'DZ', 'LK', 'MM'],
                    'discount'      => '30%',
                    'coupon_code'   => 'OFF30',
                ],
                [
                    'id'            => parity_press_nanoid(),
                    'countries'     => ['AF', 'BI', 'ET', 'HT', 'KP', 'ML', 'MO', 'NE', 'RW', 'SD', 'SL', 'SO', 'SS', 'SY', 'YE'],
                    'discount'      => '40%',
                    'coupon_code'   => 'OFF40',
                ],
                [
                    'id'            => parity_press_nanoid(),
                    'countries'     => ['SY', 'SO', 'YE', 'SS', 'CF', 'CD', 'ER', 'MG', 'ZM', 'ZW'],
                    'discount'      => '50%',
                    'coupon_code'   => 'OFF50',
                ],
            ]),
        ];

        $service->create($data);
    }
}
