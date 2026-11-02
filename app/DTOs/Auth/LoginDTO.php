<?php

namespace App\DTOs\Auth;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $deviceName = null,
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array{email: string, password: string, device_name?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: strtolower(trim($data['email'])),
            password: $data['password'],
            deviceName: $data['device_name'] ?? 'web',
        );
    }
}
