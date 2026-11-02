<?php

namespace App\Http\Requests\Categories;

use App\Models\WasteCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ToggleCategoryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WasteCategory|null $category */
        $category = $this->route('category');

        return $category && $this->user()?->can('toggleStatus', $category);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
