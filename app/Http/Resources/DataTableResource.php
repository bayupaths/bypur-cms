<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DataTableResource extends JsonResource
{
    /**
     * Gunakan DataTableCollection sebagai wrapper saat ::collection() dipanggil.
     */
    public static function newCollection(mixed $resource): ResourceCollection
    {
        return new DataTableCollection($resource, static::class);
    }

    /**
     * Override di subclass untuk mendefinisikan field yang di-expose.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
