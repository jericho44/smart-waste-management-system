<?php

namespace App\DTOs\Users;

readonly class UpdateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
        public ?string $role = null,
        public ?bool $isActive = null,
    ) {}

    /**
     * Create DTO from validated array.
     *
     * @param  array{name: string, email: string, password?: string|null, role?: string|null, is_active?: bool|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            email: strtolower(trim($data['email'])),
            password: ! empty($data['password']) ? $data['password'] : null,
            role: $data['role'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }
}

