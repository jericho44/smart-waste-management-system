<?php

namespace App\Http\Requests\Reports;

use App\Models\WasteReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadReportImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WasteReport|null $report */
        $report = $this->route('report');

        return $report && $this->user()?->can('uploadImages', $report);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }
}
