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
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active') && $this->is_active !== null) {
            $val = filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($val !== null) {
                $this->merge(['is_active' => $val]);
            }
        }
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
