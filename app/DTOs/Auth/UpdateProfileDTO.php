<?php

namespace App\DTOs\Auth;

readonly class UpdateProfileDTO
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array{name: string, email: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            email: strtolower(trim($data['email'])),
        );
    }
}

