<?php

namespace App\Http\Requests\Reports;

use App\DTOs\Reports\UpdateReportDTO;
use App\Models\WasteReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WasteReport|null $report */
        $report = $this->route('report');

        return $report && $this->user()?->can('update', $report);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_uuid' => ['required', 'string', 'exists:waste_categories,uuid'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toDTO(): UpdateReportDTO
    {
        return UpdateReportDTO::fromArray($this->validated());
    }
}
