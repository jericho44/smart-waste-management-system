<?php

namespace App\DTOs\Users;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role = 'user',
        public bool $isActive = true,
    ) {}

    /**
     * Create DTO from validated array.
     *
     * @param  array{name: string, email: string, password: string, role?: string, is_active?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            email: strtolower(trim($data['email'])),
            password: $data['password'],
            role: $data['role'] ?? 'user',
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
