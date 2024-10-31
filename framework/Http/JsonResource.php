<?php

declare(strict_types=1);

namespace ParityPress\Framework\Http;
use WP_REST_Response;

abstract class JsonResource
{
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        return is_array($this->resource)
            ? $this->resource
            : $this->resource->toArray();
    }

    /**
     * Create a new resource instance.
     */
    public static function make($resource): WP_REST_Response {
        return new WP_REST_Response([
            'data' => (new static($resource))->toArray(null)
        ], 200);
    }

    /**
     * Create new collection of resources.
     */
    public static function collection(array $collection): WP_REST_Response {
        $data = array_map(function ($item) {
            return (new static($item))->toArray(null);
        }, $collection);

        return new WP_REST_Response([
            'data' => $data,
        ], 200);
    }
}
