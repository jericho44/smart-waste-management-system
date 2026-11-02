<?php

namespace App\Http\Requests\Categories;

use App\DTOs\Categories\UpdateCategoryDTO;
use App\Models\WasteCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WasteCategory|null $category */
        $category = $this->route('category');

        return $category && $this->user()?->can('update', $category);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var WasteCategory|null $category */
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('waste_categories', 'slug')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function toDTO(): UpdateCategoryDTO
    {
        return UpdateCategoryDTO::fromArray($this->validated());
    }
}
