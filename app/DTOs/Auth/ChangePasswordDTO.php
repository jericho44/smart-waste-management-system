<?php

namespace App\DTOs\Auth;

readonly class ChangePasswordDTO
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array{current_password: string, new_password: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: $data['current_password'],
            newPassword: $data['new_password'],
        );
    }
}
