<?php

namespace App\Http\Requests\Reports;

use App\DTOs\Reports\TransitionStatusDTO;
use App\Enums\ReportStatus;
use App\Models\WasteReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WasteReport|null $report */
        $report = $this->route('report');

        return $report && $this->user()?->can('updateStatus', $report);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(ReportStatus::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDTO(): TransitionStatusDTO
    {
        return TransitionStatusDTO::fromArray($this->validated());
    }
}
