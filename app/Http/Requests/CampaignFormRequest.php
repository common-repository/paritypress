<?php

declare(strict_types=1);

namespace ParityPress\Http\Requests;

use ParityPress\Framework\Http\Requests\FormRequest;

class CampaignFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'         => ['required'],
            'description'   => ['required'],
            'discount_text' => ['required'],
            'start_date'    => ['date'],
            'end_date'      => ['date'],

            // Customizations
            'customizations'                    => ['array'],
            'customizations.text_color'         => ['hexColor'],
            'customizations.background_color'   => ['hexColor'],
            'customizations.highlighted_color'  => ['hexColor'],
            'customizations.padding'            => ['integer', 'min:0'],
            'customizations.font_size'          => ['integer', 'min:0'],

            // Discounts
            'discounts'                 => ['array'],
            'discounts.*.id'            => ['required', 'string'],
            'discounts.*.countries'     => ['required', 'array'],
            'discounts.*.discount'      => ['required'],
            'discounts.*.coupon_code'   => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('The campaign title is required.', 'parity-press'),
            'end_date.date' => __('The end date must be a valid date after the start date.', 'parity-press'),
        ];
    }
}
