<?php

namespace App\DTOs\Reports;

use Illuminate\Http\UploadedFile;

readonly class CreateReportDTO
{
    /**
     * @param  list<UploadedFile>  $images
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $categoryUuid,
        public float $latitude,
        public float $longitude,
        public ?string $address = null,
        public array $images = [],
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array{title: string, description: string, category_uuid: string, latitude: numeric, longitude: numeric, address?: string|null, images?: list<UploadedFile>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: trim($data['title']),
            description: trim($data['description']),
            categoryUuid: $data['category_uuid'],
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            address: isset($data['address']) ? trim($data['address']) : null,
            images: $data['images'] ?? [],
        );
    }
}
