<?php

namespace App\DTOs\Reports;

readonly class UpdateReportDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public string $categoryUuid,
        public float $latitude,
        public float $longitude,
        public ?string $address = null,
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array{title: string, description: string, category_uuid: string, latitude: numeric, longitude: numeric, address?: string|null}  $data
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
        );
    }
}

