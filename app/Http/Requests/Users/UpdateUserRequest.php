<?php

namespace App\Http\Requests\Users;

use App\DTOs\Users\UpdateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $targetUser */
        $targetUser = $this->route('user');

        return $targetUser && $this->user()?->can('update', $targetUser);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User|null $targetUser */
        $targetUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($targetUser?->id),
            ],
            'password' => ['nullable', 'string', Password::min(8)],
            'role' => ['nullable', 'string', Rule::in(UserRole::values())],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function toDTO(): UpdateUserDTO
    {
        return UpdateUserDTO::fromArray($this->validated());
    }
}
