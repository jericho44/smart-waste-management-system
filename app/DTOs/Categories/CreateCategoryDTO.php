<?php

namespace App\DTOs\Categories;

use Illuminate\Support\Str;

readonly class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description = null,
        public bool $isActive = true,
    ) {}

    /**
     * Create DTO from validated array.
     *
     * @param  array{name: string, slug?: string|null, description?: string|null, is_active?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            slug: ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']),
            description: $data['description'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}

