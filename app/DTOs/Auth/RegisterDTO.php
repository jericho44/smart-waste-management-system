<?php

namespace App\DTOs\Auth;

readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: trim($data['name']),
            email: strtolower(trim($data['email'])),
            password: $data['password'],
        );
    }
}
