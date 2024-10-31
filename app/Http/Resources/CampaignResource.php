<?php

declare(strict_types=1);

namespace ParityPress\Http\Resources;

use ParityPress\Data\CampaignData;
use ParityPress\Framework\Http\JsonResource;

class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = (object) CampaignData::escape($this->resource);

        return [
            'id'                => $resource->id,
            'title'             => $resource->title,
            'description'       => $resource->description ?? null,
            'discount_text'     => $resource->discount_text,
            'discounts'         => $resource->discounts,
            'customizations'    => $resource->customizations,
            'start_date'        => $resource->start_date,
            'end_date'          => $resource->end_date,
        ];
    }
}
